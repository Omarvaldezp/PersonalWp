-- ============================================================================
-- Cerrar por permisos las tablas del blog con datos de personas
--
-- CÓRRELO COMPLETO. Es corto y no cambia nada de lo que el sitio hace hoy.
--
-- De dónde sale:
--   Barrido del 24 de agosto de 2026 con la llave anónima —la que está a la
--   vista en /js/supabase-config.js— sobre las diecisiete tablas y vistas del
--   proyecto. Resultado:
--
--     posts, courses, research, site_stats   devuelven datos  <- correcto, es
--                                                                el contenido
--                                                                público
--     estudiantes, inscripciones, las del
--     seminario y las dos vistas             permission denied <- dos capas
--
--     contacts, subscribers, newsletters,
--     perfiles                               lista vacía       <- UNA capa
--
--   Las últimas cuatro no filtran nada: RLS hace su trabajo. Pero se sostienen
--   sólo en que las políticas estén bien para siempre. Las demás, además,
--   tienen REVOKE. Esto las iguala.
--
-- Qué NO cambia:
--   El formulario de contacto sigue pudiendo enviar y el alta al newsletter
--   sigue funcionando. Lo que se quita es lo que nadie usa —leer y borrar— más
--   el UPDATE de subscribers, que resultó estar roto de antes. Ver el punto 2.
-- ============================================================================


-- ---------------------------------------------------------------------------
-- 1. Contactos
--
-- submitContact() en /js/supabase-config.js inserta y nada más: nunca lee de
-- vuelta. Se le deja el INSERT y se le quita el resto, así que quien mande el
-- formulario no puede leer los mensajes de los demás.
-- ---------------------------------------------------------------------------
revoke all    on table public.contacts from anon;
grant  insert on table public.contacts to   anon;


-- ---------------------------------------------------------------------------
-- 2. Suscriptores
--
-- Aquí apareció algo que conviene leer completo.
--
-- Pensaba conservarle el UPDATE, porque unsubscribe.html da de baja
-- escribiendo directo en la tabla y esa página no está en el repo. Al probarlo
-- resultó que ese UPDATE no sirve de nada: ya está roto.
--
-- Reproducido el 24 de agosto de 2026 con la configuración exacta de
-- producción —anon con permisos completos de tabla, RLS activo, la política
-- "Allow anonymous unsubscribe" con using(true), y ninguna política de SELECT
-- para anon:
--
--     set role anon;
--     update subscribers set active = false where email = 'real@ejemplo.mx';
--     -- UPDATE 0        <- no dio de baja a nadie
--
-- El motivo: en Postgres, un UPDATE cuyo WHERE lee una columna necesita ver
-- esa fila, y con RLS activo eso lo decide la política de SELECT. Como anon no
-- tiene ninguna, la fila es invisible para el filtro y el UPDATE no encuentra
-- a quién aplicar. La política de UPDATE dice using(true), pero nunca llega a
-- evaluarse.
--
-- Es el mismo defecto que tenían los like: falla en silencio y la página
-- muestra un mensaje de éxito que no corresponde a nada.
--
-- Por eso se revoca también el UPDATE: no se pierde nada que hoy funcione, y
-- el hueco se cierra. La baja tiene que pasar por baja_suscriptor(), la
-- función SECURITY DEFINER que dejó 02_corregir_politicas.sql —comprobada en
-- la misma prueba: por ahí sí queda active = false—. Falta cambiar una línea
-- en unsubscribe.html para que la llame en lugar de escribir directo.
-- ---------------------------------------------------------------------------
revoke all    on table public.subscribers from anon;
grant  insert on table public.subscribers to   anon;

-- La política de UPDATE queda sin efecto al no haber permiso detrás. Se puede
-- borrar cuando unsubscribe.html ya use la función:
--
-- drop policy if exists "Allow anonymous unsubscribe" on public.subscribers;


-- ---------------------------------------------------------------------------
-- 3. Campañas de newsletter
--
-- El sitio público no las toca: sólo el panel, y el panel entra con sesión.
-- Aquí no hay nada que conservar.
-- ---------------------------------------------------------------------------
revoke all on table public.newsletters from anon;


-- ---------------------------------------------------------------------------
-- 4. Perfiles
--
-- Esta línea ya venía en 00_roles.sql, pero el barrido muestra que no quedó
-- aplicada: perfiles sigue respondiendo lista vacía en lugar de permission
-- denied. Se repite aquí para que no se pierda otra vez.
-- ---------------------------------------------------------------------------
revoke all on table public.perfiles from anon;


-- ============================================================================
-- CÓMO COMPROBAR QUE QUEDÓ
--
-- Desde el SQL Editor, poniéndote en los zapatos de un visitante:
--
--     set role anon;
--     select count(*) from public.contacts;     -- permission denied
--     select count(*) from public.subscribers;  -- permission denied
--     select count(*) from public.newsletters;  -- permission denied
--     select count(*) from public.perfiles;     -- permission denied
--     select count(*) from public.posts;        -- SÍ debe funcionar
--     reset role;
--
-- Y que lo que el sitio necesita sigue vivo:
--
--     set role anon;
--     insert into public.contacts (name, email, message)
--     values ('Prueba', 'prueba@ejemplo.mx', 'Prueba de permisos');
--     reset role;
--     delete from public.contacts where email = 'prueba@ejemplo.mx';
--
-- O más fácil: manda el formulario de contacto desde el sitio y revisa que
-- llegue a la sección Contactos del panel.
-- ============================================================================
