-- ============================================================================
-- Límite de envíos para los formularios públicos de contacto y newsletter
--
-- CÓRRELO COMPLETO. No depende de ningún otro archivo.
--
-- De dónde sale:
--   El formulario de contacto y el del newsletter insertan directo en contacts
--   y subscribers con la llave anónima, que es pública. No había ningún tope:
--   un bot podía mandar miles de mensajes por minuto y llenar el panel. Un
--   límite en el navegador no sirve, porque basta con llamar a la API sin pasar
--   por la página. Por eso va aquí, en un trigger que corre en la base.
--
-- Topes:
--                   por IP, por hora    global, por hora
--   contacts               3                  30
--   subscribers            5                  60
--
--   El tope global frena un ataque desde muchas IP distintas. Si algún día el
--   sitio recibe tráfico legítimo que lo rebase, se sube en las constantes de
--   la función.
--
-- Privacidad:
--   La IP no se guarda: se guarda su SHA-256 en limite_envios, una tabla que
--   nadie puede leer desde la API y que se vacía sola de todo lo que tenga más
--   de 24 horas.
--
-- De dónde sale la IP:
--   PostgREST publica los encabezados de la petición en
--   current_setting('request.headers'). Se toma cf-connecting-ip, luego
--   x-real-ip y luego el primer valor de x-forwarded-for. Si no hay ninguno
--   (por ejemplo, un INSERT desde el SQL Editor), sólo aplica el tope global.
--
-- Qué ve quien rebasa el tope:
--   El INSERT falla con un error. El formulario muestra su mensaje de error
--   genérico ("Error al enviar. Intenta de nuevo.").
--
-- Los INSERT del panel o del SQL Editor (rol authenticated o postgres) no
-- pasan por el límite.
-- ============================================================================


create table if not exists public.limite_envios (
    id        bigint generated always as identity primary key,
    tipo      text        not null,
    ip_hash   text,
    creado    timestamptz not null default now()
);

create index if not exists idx_limite_envios_busqueda
    on public.limite_envios (tipo, ip_hash, creado);
create index if not exists idx_limite_envios_creado
    on public.limite_envios (creado);

alter table public.limite_envios enable row level security;
revoke all on table public.limite_envios from anon, authenticated;
comment on table public.limite_envios
    is 'Registro de envíos de los formularios públicos para limitar su frecuencia. '
       'Guarda el hash de la IP, no la IP, y se vacía sola a las 24 horas.';


create or replace function public.limitar_envios()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
declare
    -- Topes por hora. Ver la tabla del encabezado.
    tope_ip      integer := case tg_table_name when 'contacts' then 3  else 5  end;
    tope_global  integer := case tg_table_name when 'contacts' then 30 else 60 end;

    encabezados  json;
    ip           text;
    huella       text;
    recientes    integer;
begin
    -- Sólo se limita al público. El panel y el SQL Editor pasan directo.
    -- Ojo: dentro de una función SECURITY DEFINER, current_user es el dueño
    -- de la función. El rol con el que entró la petición lo dice el ajuste
    -- 'role', que PostgREST fija con SET LOCAL ROLE.
    if coalesce(current_setting('role', true), '') <> 'anon' then
        return new;
    end if;

    -- Validación mínima de lo que llega del formulario.
    if new.email is null
       or length(new.email) > 254
       or new.email !~ '^[^@\s]+@[^@\s]+\.[^@\s]+$' then
        raise exception 'Correo no válido' using errcode = '22023';
    end if;

    if tg_table_name = 'contacts' then
        if coalesce(length(trim(new.message)), 0) = 0
           or length(new.message) > 5000
           or length(coalesce(new.name, '')) > 120 then
            raise exception 'Mensaje vacío o demasiado largo' using errcode = '22023';
        end if;
    end if;

    -- Limpieza de lo que ya no cuenta.
    delete from limite_envios where creado < now() - interval '24 hours';

    -- Tope global.
    select count(*) into recientes
    from limite_envios
    where tipo = tg_table_name
      and creado > now() - interval '1 hour';

    if recientes >= tope_global then
        raise exception 'Demasiados envíos. Intenta más tarde.' using errcode = '54000';
    end if;

    -- Tope por IP.
    encabezados := nullif(current_setting('request.headers', true), '')::json;
    ip := coalesce(
        encabezados ->> 'cf-connecting-ip',
        encabezados ->> 'x-real-ip',
        trim(split_part(encabezados ->> 'x-forwarded-for', ',', 1))
    );

    if nullif(ip, '') is not null then
        huella := encode(sha256(convert_to(ip, 'UTF8')), 'hex');

        select count(*) into recientes
        from limite_envios
        where tipo = tg_table_name
          and ip_hash = huella
          and creado > now() - interval '1 hour';

        if recientes >= tope_ip then
            raise exception 'Demasiados envíos. Intenta más tarde.' using errcode = '54000';
        end if;
    end if;

    insert into limite_envios (tipo, ip_hash) values (tg_table_name, huella);
    return new;
end;
$$;

revoke execute on function public.limitar_envios() from public, anon, authenticated;


drop trigger if exists trg_limitar_envios on public.contacts;
create trigger trg_limitar_envios
    before insert on public.contacts
    for each row execute function public.limitar_envios();

drop trigger if exists trg_limitar_envios on public.subscribers;
create trigger trg_limitar_envios
    before insert on public.subscribers
    for each row execute function public.limitar_envios();


-- ============================================================================
-- Comprobación
--
-- Con la llave anónima, el cuarto mensaje de contacto desde la misma IP dentro
-- de una hora debe fallar con «Demasiados envíos». Para ver el registro:
--
--   select tipo, count(*) from limite_envios group by tipo;
-- ============================================================================
