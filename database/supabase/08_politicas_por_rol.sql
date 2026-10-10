-- ============================================================================
-- Políticas por rol en las tablas del blog y en las imágenes
--
-- CÓRRELO COMPLETO. Depende de 00_roles.sql (tiene_rol y perfiles).
--
-- De dónde sale:
--   Revisión del 10 de octubre de 2026. Las políticas originales del blog
--   ("Admin full access ...") decían auth.role() = 'authenticated': cualquiera
--   con sesión iniciada podía todo. Y el registro de Supabase Auth estaba
--   abierto, así que bastaba crearse una cuenta con la llave anónima —pública
--   en /js/supabase-config.js— y confirmar el correo para leer los contactos y
--   suscriptores, o borrar y reescribir los posts. Lo mismo con las imágenes.
--
--   Además, crear_perfil_al_registrarse() le da a toda cuenta nueva el rol
--   'editor' y la deja activa. Con eso, aun corrigiendo las políticas, un
--   desconocido que se registrara quedaba como editor.
--
-- Qué hace:
--   1. Las cuentas nuevas nacen INACTIVAS. tiene_rol() ya exige activo = true,
--      así que una cuenta nueva no puede nada hasta que un admin la active:
--          update perfiles set activo = true, rol = 'editor' where id = '...';
--   2. Contenido (posts, courses, research, site_stats, newsletters, imágenes):
--      admin y editor.
--   3. Datos personales (contacts, subscribers): sólo admin.
--   4. Las políticas públicas (leer lo publicado, enviar el formulario de
--      contacto, suscribirse) no se tocan.
--   5. Cierra dos funciones que no tienen por qué llamarse desde fuera.
--
-- Por qué (select public.tiene_rol(...)) y no tiene_rol(...) a secas:
--   con el select, Postgres evalúa la función una vez por consulta y no una vez
--   por renglón. Es lo que pide el aviso auth_rls_initplan de Supabase.
--
-- Además, desde el panel de Supabase: Authentication > Sign In / Providers >
-- desactivar "Allow new users to sign up". Las cuentas del panel las creas tú
-- con "Invite user". Este archivo no puede cambiar ese ajuste.
-- ============================================================================


-- 1. Cuentas nuevas inactivas -------------------------------------------------
alter table public.perfiles alter column activo set default false;


-- 2. Contenido: admin y editor ------------------------------------------------
drop policy if exists "Admin full access posts" on public.posts;
drop policy if exists contenido_editores on public.posts;
create policy contenido_editores on public.posts
    for all to authenticated
    using ((select public.tiene_rol('admin', 'editor')))
    with check ((select public.tiene_rol('admin', 'editor')));

drop policy if exists "Admin full access courses" on public.courses;
drop policy if exists contenido_editores on public.courses;
create policy contenido_editores on public.courses
    for all to authenticated
    using ((select public.tiene_rol('admin', 'editor')))
    with check ((select public.tiene_rol('admin', 'editor')));

drop policy if exists "Admin full access research" on public.research;
drop policy if exists contenido_editores on public.research;
create policy contenido_editores on public.research
    for all to authenticated
    using ((select public.tiene_rol('admin', 'editor')))
    with check ((select public.tiene_rol('admin', 'editor')));

drop policy if exists "Admin full access stats" on public.site_stats;
drop policy if exists contenido_editores on public.site_stats;
create policy contenido_editores on public.site_stats
    for all to authenticated
    using ((select public.tiene_rol('admin', 'editor')))
    with check ((select public.tiene_rol('admin', 'editor')));

drop policy if exists "Authenticated users can manage newsletters" on public.newsletters;
drop policy if exists contenido_editores on public.newsletters;
create policy contenido_editores on public.newsletters
    for all to authenticated
    using ((select public.tiene_rol('admin', 'editor')))
    with check ((select public.tiene_rol('admin', 'editor')));


-- 3. Datos personales: sólo admin ---------------------------------------------
drop policy if exists "Admin full access contacts" on public.contacts;
drop policy if exists datos_personales_admin on public.contacts;
create policy datos_personales_admin on public.contacts
    for all to authenticated
    using ((select public.tiene_rol('admin')))
    with check ((select public.tiene_rol('admin')));

drop policy if exists "Admin full access subscribers" on public.subscribers;
drop policy if exists datos_personales_admin on public.subscribers;
create policy datos_personales_admin on public.subscribers
    for all to authenticated
    using ((select public.tiene_rol('admin')))
    with check ((select public.tiene_rol('admin')));


-- 4. Imágenes del blog: admin y editor suben, cambian y borran ----------------
--    "Imágenes públicas" (lectura) se queda como está.
drop policy if exists "Admin sube imágenes" on storage.objects;
drop policy if exists "Admin actualiza imágenes" on storage.objects;
drop policy if exists "Admin elimina imágenes" on storage.objects;
drop policy if exists imagenes_editores on storage.objects;
create policy imagenes_editores on storage.objects
    for all to authenticated
    using (bucket_id = 'images' and (select public.tiene_rol('admin', 'editor')))
    with check (bucket_id = 'images' and (select public.tiene_rol('admin', 'editor')));


-- 5. Funciones que no se llaman desde fuera -----------------------------------
--    crear_perfil_al_registrarse() sólo la usa el trigger de auth.users, y
--    tiene_rol() sólo tiene sentido con sesión. Las públicas a propósito
--    (reaccionar_post, baja_suscriptor, guardar_diagnostico_seminario) no se
--    tocan.
revoke execute on function public.crear_perfil_al_registrarse() from public, anon, authenticated;
revoke execute on function public.tiene_rol(text[]) from public, anon;

alter function public.jsonb_a_texto_array(jsonb) set search_path = public;


-- ============================================================================
-- Comprobación
--
--   select tablename, policyname, roles, qual
--   from pg_policies
--   where qual like '%auth.role()%';
--
-- debe devolver cero renglones.
-- ============================================================================
