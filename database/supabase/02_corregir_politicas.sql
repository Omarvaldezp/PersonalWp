-- ============================================================================
-- Correcciones a las políticas RLS existentes
--
-- Diagnóstico a partir de pg_policies:
--
--   Lo que está bien: ninguna tabla filtra datos al rol anónimo. No puede leer
--   contacts ni subscribers, y no puede escribir en posts, courses ni research.
--   El temor de que el blog estuviera abierto no se confirmó.
--
--   Problema 1 — Los like y dislike no funcionan. Falta política de UPDATE
--   para el rol anónimo en posts, así que reactToPost() no escribe nada. El
--   visitante ve "Gracias por tu opinión" y su voto no se registra.
--
--   Problema 2 — La baja de suscriptores es demasiado permisiva. La política
--   "Allow anonymous unsubscribe" tiene USING (true), de modo que cualquiera
--   puede modificar CUALQUIER fila de subscribers con tal de dejar active en
--   false. Puede darlos de baja a todos, y de paso reescribir el correo o el
--   nombre de cualquier suscriptor en la misma operación.
--
-- La solución para ambos es la misma idea: no se le da permiso de UPDATE al
-- visitante, se le da una función concreta que hace una sola cosa. Las
-- funciones son SECURITY DEFINER, es decir corren con los permisos de quien
-- las creó, saltándose RLS de forma controlada y acotada.
--
-- ORDEN: ejecuta primero el paso 0, y ajusta el paso 1 según lo que devuelva.
-- ============================================================================


-- ---------------------------------------------------------------------------
-- PASO 0 · El tipo de posts.id ya está confirmado: uuid
--
-- Este archivo pedía averiguarlo a mano porque cuando lo escribí no podía
-- consultar el proyecto. Ya se comprobó, el 24 de agosto de 2026: posts.id es
-- uuid. El paso 1 quedó corregido y funciona tal cual.
--
-- Si algún día lo corres contra otro proyecto, verifica primero:
--
--     select column_name, data_type
--     from information_schema.columns
--     where table_schema = 'public' and table_name = 'posts' and column_name = 'id';
--
-- y ajusta el tipo del argumento si no coincide.


-- ---------------------------------------------------------------------------
-- PASO 1 · Reparar los like y dislike
--
-- Suma uno al contador de forma atómica. Al hacerlo dentro de la base se
-- corrige de paso un segundo defecto del código actual: reactToPost() leía el
-- valor y luego escribía valor+1, así que dos votos simultáneos perdían uno.
-- ---------------------------------------------------------------------------
create or replace function public.reaccionar_post(post_id uuid, tipo text)
returns table (likes integer, dislikes integer)
language plpgsql
security definer
set search_path = public
as $$
begin
    if tipo not in ('like', 'dislike') then
        raise exception 'tipo debe ser like o dislike';
    end if;

    if tipo = 'like' then
        update posts p set likes = coalesce(p.likes, 0) + 1
        where p.id = post_id and p.published = true;
    else
        update posts p set dislikes = coalesce(p.dislikes, 0) + 1
        where p.id = post_id and p.published = true;
    end if;

    return query
    select coalesce(p.likes, 0), coalesce(p.dislikes, 0)
    from posts p
    where p.id = post_id and p.published = true;
end;
$$;

revoke all on function public.reaccionar_post(uuid, text) from public;
grant execute on function public.reaccionar_post(uuid, text) to anon, authenticated;

comment on function public.reaccionar_post is
    'Suma una reacción a un post publicado. Es la única vía por la que un '
    'visitante puede modificar la tabla posts, y solo toca los contadores.';


-- ---------------------------------------------------------------------------
-- PASO 2 · Acotar la baja de suscriptores
--
-- Sustituye la política abierta por una función que solo puede poner active
-- en false, y solo en la fila cuyo correo coincide.
--
-- No devuelve si el correo existía o no, a propósito: si respondiera, la
-- función serviría para averiguar quién está suscrito probando correos.
-- ---------------------------------------------------------------------------
create or replace function public.baja_suscriptor(correo text)
returns void
language plpgsql
security definer
set search_path = public
as $$
begin
    update subscribers
    set active = false
    where lower(email) = lower(trim(correo));
end;
$$;

revoke all on function public.baja_suscriptor(text) from public;
grant execute on function public.baja_suscriptor(text) to anon, authenticated;

comment on function public.baja_suscriptor is
    'Da de baja a un suscriptor por su correo. Sustituye a la política '
    '"Allow anonymous unsubscribe", que permitía modificar cualquier fila.';

-- OJO: no ejecutes este DROP hasta haber actualizado unsubscribe.html.
-- Ver la nota del final.
--
-- drop policy if exists "Allow anonymous unsubscribe" on public.subscribers;


-- ---------------------------------------------------------------------------
-- COMPROBACIÓN
--
-- Después de correr los pasos 1 y 2, esto debe devolver dos filas.
-- ---------------------------------------------------------------------------
select routine_name, security_type
from information_schema.routines
where routine_schema = 'public'
  and routine_name in ('reaccionar_post', 'baja_suscriptor');


-- ============================================================================
-- LO QUE FALTA HACER EN EL CÓDIGO DEL SITIO
--
-- Las funciones no sirven de nada hasta que el frontend las llame. Son dos
-- cambios pequeños:
--
-- 1. js/supabase-config.js — reemplazar el cuerpo de reactToPost por:
--
--        async function reactToPost(postId, type) {
--            const { data, error } = await db.rpc('reaccionar_post', {
--                post_id: postId,
--                tipo: type
--            });
--            if (error) { console.error('Error en la reacción:', error); return null; }
--            return data && data[0] ? data[0] : null;
--        }
--
--    Queda más corto que el actual, y hace una sola llamada en vez de dos.
--
-- 2. unsubscribe.html — donde hoy haga un update directo sobre subscribers,
--    debe llamar a:
--
--        await db.rpc('baja_suscriptor', { correo: email });
--
--    Ese archivo no está en el repositorio. Hasta que se actualice, deja la
--    política "Allow anonymous unsubscribe" en su sitio: quitarla antes
--    rompería el enlace de cancelación de todos los correos ya enviados.
-- ============================================================================
