-- ============================================================================
-- Retirar el sistema académico de Supabase
--
-- CÓRRELO COMPLETO en el SQL Editor de Supabase.
--
-- De dónde sale:
--   En octubre de 2026 se retiró el panel PHP y, con él, el diagnóstico DISDE
--   y la gestión de estudiantes. Omar decidió no conservar esos datos. Las
--   cuatro tablas que creó 01_academico.sql nunca llegaron a usarse: el panel
--   de Supabase no tiene secciones para ellas y el formulario del diagnóstico
--   seguía escribiendo en el PostgreSQL de SiteGround.
--
-- Por qué no basta con dejarlas vacías:
--   diagnostico_disde_respuestas conserva la política formulario_publico_inserta
--   y el GRANT INSERT a anon. Con la llave anónima, que está a la vista en
--   /js/supabase-config.js, cualquiera puede seguir metiendo renglones en una
--   tabla que nadie revisa.
--
-- Qué NO toca:
--   perfiles y tiene_rol() (00_roles.sql) se quedan: el panel los usa para los
--   roles y la sección Seminario depende de ellos. Tampoco toca nada del
--   seminario (04_diagnostico_seminario.sql) ni del blog.
--
-- Sin CASCADE a propósito: si algo que no está en el repo depende de estas
-- tablas, el script se detiene con un error que dice qué es, en lugar de
-- borrarlo en silencio. Al ir dentro de una transacción, en ese caso no se
-- borra nada.
-- ============================================================================

begin;

-- inscripciones apunta a estudiantes y a cursos_instancias; estudiantes apunta
-- a diagnostico_disde_respuestas. Se borran en ese orden.
drop table if exists public.inscripciones;
drop table if exists public.cursos_instancias;
drop table if exists public.estudiantes;
drop table if exists public.diagnostico_disde_respuestas;

-- Sólo la usaban los triggers de las tablas anteriores.
drop function if exists public.tocar_updated_at();

commit;


-- ============================================================================
-- Comprobación
--
-- Con la llave anónima, cualquiera de las cuatro tablas debe responder ahora
-- «Could not find the table» (código PGRST205) en lugar de «permission denied».
-- Desde el SQL Editor, esta consulta debe devolver cero renglones:
--
--   select table_name
--   from information_schema.tables
--   where table_schema = 'public'
--     and table_name in ('inscripciones', 'cursos_instancias',
--                        'estudiantes', 'diagnostico_disde_respuestas');
-- ============================================================================
