-- Respaldo del sistema académico antes de retirar el panel y la API PHP
--
-- Ejecútalo en phpPgAdmin (o psql) desde el panel de SiteGround ANTES de borrar
-- nada. Cada consulta devuelve una tabla completa; expórtala a CSV con el botón
-- de exportar de phpPgAdmin y guarda los cuatro archivos fuera del servidor.
--
-- Por qué importa: al retirar la API, estas tablas siguen existiendo en
-- PostgreSQL pero ya no habrá ninguna interfaz para leerlas. Contienen datos
-- personales de estudiantes (nombre, correo, matrícula, teléfono), así que el
-- respaldo no es solo por comodidad: es lo que te permite conservarlos de forma
-- controlada o borrarlos a conciencia.

-- 1. Estudiantes
SELECT *
FROM estudiantes
ORDER BY id;

-- 2. Instancias de curso
SELECT *
FROM cursos_instancias
ORDER BY id;

-- 3. Inscripciones, con el nombre del estudiante y del curso ya resueltos
--    para que el CSV se entienda sin necesidad de cruzar tablas después.
SELECT i.*,
       e.nombre   AS estudiante_nombre,
       e.correo   AS estudiante_correo,
       c.nombre   AS curso_nombre
FROM inscripciones i
LEFT JOIN estudiantes       e ON e.id = i.estudiante_id
LEFT JOIN cursos_instancias c ON c.id = i.curso_instancia_id
ORDER BY i.id;

-- 4. Respuestas del diagnóstico DISDE
SELECT *
FROM diagnostico_disde_respuestas
ORDER BY id;

-- ---------------------------------------------------------------------------
-- Cuántos registros hay en juego. Córrelo primero: si sale todo en cero,
-- no hay nada que respaldar y el retiro es trivial.
-- ---------------------------------------------------------------------------
SELECT 'estudiantes'                  AS tabla, COUNT(*) AS registros FROM estudiantes
UNION ALL
SELECT 'cursos_instancias',                     COUNT(*) FROM cursos_instancias
UNION ALL
SELECT 'inscripciones',                         COUNT(*) FROM inscripciones
UNION ALL
SELECT 'diagnostico_disde_respuestas',          COUNT(*) FROM diagnostico_disde_respuestas
ORDER BY tabla;
