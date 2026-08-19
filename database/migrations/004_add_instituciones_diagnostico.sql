-- 004: Separar institución de licenciatura y maestría en el diagnóstico DISDE
--
-- Preservado desde src/migrate-add-instituciones.php, que se eliminó del repo
-- por exponer la base de datos en https://omarvaldez.com/ con una llave débil.
--
-- Ejecutar con psql o phpPgAdmin desde el panel de SiteGround, no por HTTP.

ALTER TABLE diagnostico_disde_respuestas
ADD COLUMN IF NOT EXISTS institucion_licenciatura VARCHAR(255);

ALTER TABLE diagnostico_disde_respuestas
ADD COLUMN IF NOT EXISTS institucion_maestria VARCHAR(255);

-- Migrar los datos de la columna original hacia la de licenciatura
UPDATE diagnostico_disde_respuestas
SET institucion_licenciatura = institucion
WHERE institucion IS NOT NULL AND institucion_licenciatura IS NULL;
