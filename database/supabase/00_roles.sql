-- ============================================================================
-- Roles del panel
--
-- CÓRRELO ANTES QUE 01_academico.sql. Ese archivo depende de la función
-- tiene_rol() que se define aquí.
--
-- Por qué existe:
--   Hoy eres el único que entra, y las políticas de tu blog dicen simplemente
--   "authenticated": cualquiera con sesión puede todo. Eso funciona con un solo
--   usuario y deja de funcionar en cuanto entra el segundo, porque quien
--   ayude con el blog no tiene por qué ver correos, teléfonos y matrículas de
--   estudiantes.
--
--   El mecanismo se monta ahora para no tener que rehacer las políticas
--   después. Mientras seas el único usuario no cambia nada en la práctica.
--
-- Roles previstos:
--   admin    todo, incluidos los datos personales
--   editor   contenido del sitio: blog, cursos, investigación, estadísticas
--            y campañas de newsletter. NO ve contactos, suscriptores ni
--            nada del sistema académico.
--
-- Si más adelante hace falta un rol que solo toque lo académico, se agrega a
-- la restricción CHECK de abajo y se ajustan las políticas de 01_academico.sql.
-- ============================================================================


-- ---------------------------------------------------------------------------
-- Tabla de perfiles, atada a los usuarios de Supabase Auth
-- ---------------------------------------------------------------------------
create table if not exists public.perfiles (
    id          uuid primary key references auth.users (id) on delete cascade,
    nombre      text,
    rol         text not null default 'editor'
                check (rol in ('admin', 'editor')),
    activo      boolean not null default true,
    created_at  timestamptz default now()
);

comment on table public.perfiles
    is 'Rol de cada usuario del panel. La fila se crea sola al registrarse.';


-- ---------------------------------------------------------------------------
-- Alta automática del perfil al crearse un usuario
--
-- Sin esto habría que insertar la fila a mano cada vez que das de alta a
-- alguien, y olvidarlo dejaría a esa persona sin ningún permiso.
-- ---------------------------------------------------------------------------
create or replace function public.crear_perfil_al_registrarse()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
    insert into public.perfiles (id, nombre)
    values (new.id, coalesce(new.raw_user_meta_data ->> 'nombre', new.email))
    on conflict (id) do nothing;
    return new;
end;
$$;

drop trigger if exists trg_crear_perfil on auth.users;
create trigger trg_crear_perfil
    after insert on auth.users
    for each row execute function public.crear_perfil_al_registrarse();


-- ---------------------------------------------------------------------------
-- La función que usan todas las políticas
--
-- SECURITY DEFINER a propósito: si consultara perfiles con los permisos de
-- quien llama, la política de perfiles tendría que consultar perfiles para
-- decidir si puede consultar perfiles. Recursión infinita. Al saltarse RLS
-- aquí dentro, se corta el círculo.
--
-- STABLE permite a Postgres llamarla una vez por consulta en vez de una vez
-- por fila.
-- ---------------------------------------------------------------------------
create or replace function public.tiene_rol(variadic roles_permitidos text[])
returns boolean
language sql
stable
security definer
set search_path = public
as $$
    select exists (
        select 1
        from public.perfiles p
        where p.id = auth.uid()
          and p.activo = true
          and p.rol = any(roles_permitidos)
    );
$$;

revoke all on function public.tiene_rol(text[]) from public;
grant execute on function public.tiene_rol(text[]) to authenticated;

comment on function public.tiene_rol
    is 'Devuelve true si el usuario de la sesión tiene alguno de los roles '
       'indicados y está activo. Úsala en las políticas: tiene_rol(''admin'').';


-- ---------------------------------------------------------------------------
-- Políticas sobre la propia tabla de perfiles
-- ---------------------------------------------------------------------------
alter table public.perfiles enable row level security;

-- Cualquiera con sesión puede ver su propia fila. La necesita el panel para
-- saber qué secciones mostrar.
drop policy if exists perfil_propio_lectura on public.perfiles;
create policy perfil_propio_lectura on public.perfiles
    for select to authenticated
    using (id = auth.uid());

-- Solo admin ve y administra a los demás. Nadie puede cambiarse el rol a sí
-- mismo: la política de escritura exige ser admin de entrada.
drop policy if exists perfil_admin_todo on public.perfiles;
create policy perfil_admin_todo on public.perfiles
    for all to authenticated
    using (public.tiene_rol('admin'))
    with check (public.tiene_rol('admin'));


-- ---------------------------------------------------------------------------
-- Cerrar la tabla también a nivel de permisos, no sólo de RLS
--
-- Sin esto, la llave anónima puede consultar perfiles: RLS le devuelve cero
-- filas, así que hoy no se filtra nada. Pero eso depende de que las políticas
-- estén bien para siempre. Con el REVOKE, aunque alguien agregue mañana una
-- política permisiva por descuido, anon sigue sin poder tocar la tabla.
--
-- Es el mismo patrón que usan las tablas de 04_diagnostico_seminario.sql.
-- ---------------------------------------------------------------------------
revoke all on table public.perfiles from anon;


-- ============================================================================
-- PASO OBLIGATORIO: date a ti mismo el rol admin
--
-- El trigger crea los perfiles nuevos como 'editor'. Tu usuario ya existe, así
-- que no tiene fila, y aunque la tuviera no sería admin. Sin este paso te
-- quedas sin acceso a lo académico.
--
-- 1. Averigua tu id:
--        select id, email from auth.users order by created_at;
--
-- 2. Inserta tu perfil como admin, sustituyendo el uuid:
--
-- insert into public.perfiles (id, nombre, rol)
-- values ('TU-UUID-AQUI', 'Omar Valdez', 'admin')
-- on conflict (id) do update set rol = 'admin', activo = true;
--
-- 3. Comprueba que quedó:
--        select p.rol, u.email
--        from public.perfiles p join auth.users u on u.id = p.id;
-- ============================================================================


-- ============================================================================
-- LO QUE NO TOCA ESTE ARCHIVO, A PROPÓSITO
--
-- Las políticas actuales de posts, courses, research, contacts, subscribers,
-- newsletters y site_stats siguen diciendo "authenticated", así que hoy
-- cualquier usuario con sesión puede administrarlas. Mientras seas el único,
-- da igual.
--
-- El día que des de alta a la segunda persona, hay que cambiarlas para que
-- distingan admin de editor. Ese cambio toca tu blog en producción, así que
-- se hace cuando haga falta y no antes. El archivo 03_roles_blog.sql lo deja
-- preparado.
-- ============================================================================
