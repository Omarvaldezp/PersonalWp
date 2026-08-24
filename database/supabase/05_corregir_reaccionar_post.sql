-- ============================================================================
-- Corrección: reaccionar_post esperaba bigint y posts.id es uuid
--
-- CÓRRELO COMPLETO. Es corto y arregla dos cosas independientes.
--
-- Qué pasó:
--   02_corregir_politicas.sql abría con un PASO 0 que pedía averiguar el tipo
--   de posts.id antes de seguir, porque yo no podía consultarlo entonces. El
--   archivo se corrió con el valor por omisión, bigint, y el id resultó uuid.
--   La función quedó creada, pero al llamarla Postgres responde:
--
--       operator does not exist: uuid = bigint
--
--   Comprobado contra el proyecto el 24 de agosto de 2026. Los dos primeros
--   posts publicados tienen id uuid, por ejemplo
--   'cee4f047-8cd1-4995-9154-23d1a44baf92'.
--
-- Consecuencia mientras no se corra esto:
--   Los like y dislike del blog NO funcionan. Peor que antes, incluso: antes
--   fallaban en silencio y el lector veía un agradecimiento falso; ahora
--   /js/supabase-config.js llama a la función y recibe un error.
--
-- Ya no hay nada que adivinar: el tipo está confirmado.
-- ============================================================================


-- ---------------------------------------------------------------------------
-- 1. Fuera la versión con el tipo equivocado
--
-- Va un DROP y no un CREATE OR REPLACE porque cambiar el tipo de un argumento
-- crea una función nueva en lugar de sustituir la anterior: quedarían las dos,
-- y una llamada ambigua fallaría por otro motivo.
-- ---------------------------------------------------------------------------
drop function if exists public.reaccionar_post(bigint, text);


-- ---------------------------------------------------------------------------
-- 2. La versión correcta, con uuid
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


-- ============================================================================
-- 3. Cerrar por permisos las tablas con datos personales
--
-- Comprobado también el 24 de agosto: con la llave anónima, las cuatro tablas
-- de 01_academico.sql responden 200 con lista vacía. Hoy no se filtra nada
-- —el RLS hace su trabajo— pero se sostienen en una sola capa.
--
-- Las tablas del seminario, en cambio, responden «permission denied», porque
-- además tienen REVOKE. Esto las iguala: si algún día alguien agrega una
-- política permisiva por descuido, anon sigue sin poder tocarlas.
--
-- Es el mismo cambio que ya corriste para perfiles.
-- ============================================================================

revoke all on table public.estudiantes       from anon;
revoke all on table public.cursos_instancias from anon;
revoke all on table public.inscripciones     from anon;

-- Esta lleva un trato distinto. La política formulario_publico_inserta existe
-- para cuando el diagnóstico DISDE deje el PHP y escriba directo a Supabase.
-- Un REVOKE a secas la dejaría inservible antes de estrenarla, así que se
-- devuelve el INSERT y nada más: sin SELECT, sin UPDATE, sin DELETE.
revoke all    on table public.diagnostico_disde_respuestas from anon;
grant  insert on table public.diagnostico_disde_respuestas to   anon;


-- ============================================================================
-- CÓMO COMPROBAR QUE QUEDÓ
--
-- 1. Que la función quedó con uuid y solo hay una:
--        select p.oid::regprocedure
--        from pg_proc p join pg_namespace n on n.oid = p.pronamespace
--        where n.nspname = 'public' and p.proname = 'reaccionar_post';
--    Debe devolver UN renglón: reaccionar_post(uuid,text)
--
-- 2. Que suma de verdad. Toma un post publicado y prueba:
--        select id, title, likes from public.posts where published limit 1;
--        select * from public.reaccionar_post('PEGA-EL-UUID-AQUI', 'like');
--    Devuelve los contadores ya sumados. Para dejarlo como estaba:
--        update public.posts set likes = likes - 1 where id = 'PEGA-EL-UUID-AQUI';
--
--    O más fácil: entra a un artículo del blog y dale al pulgar arriba.
--
-- 3. Que anon ya no alcanza las tablas académicas. Desde el navegador, con la
--    llave anónima, un select a estudiantes debe responder «permission denied»
--    en lugar de una lista vacía.
-- ============================================================================
