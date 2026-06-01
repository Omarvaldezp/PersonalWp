<?php
/**
 * Migración: Agregar campos de instituciones (licenciatura y maestría)
 * Ejecutar UNA SOLA VEZ: https://omarvaldez.com/migrate-add-instituciones.php?key=migrate123
 * BORRAR después de ejecutar
 */

$MIGRATION_KEY = 'migrate123';
if (!isset($_GET['key']) || $_GET['key'] !== $MIGRATION_KEY) {
    die('❌ Acceso denegado. Agrega ?key=migrate123 a la URL');
}

require_once __DIR__ . '/api/config/config.php';

try {
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "<h2>🔄 Migración: Agregar campos de instituciones</h2>";

    // SQL para agregar las nuevas columnas
    $sql = "
-- Agregar columna para institución de licenciatura
ALTER TABLE diagnostico_disde_respuestas
ADD COLUMN IF NOT EXISTS institucion_licenciatura VARCHAR(255);

-- Agregar columna para institución de maestría
ALTER TABLE diagnostico_disde_respuestas
ADD COLUMN IF NOT EXISTS institucion_maestria VARCHAR(255);

-- Copiar datos existentes de 'institucion' a 'institucion_licenciatura' si existen
UPDATE diagnostico_disde_respuestas
SET institucion_licenciatura = institucion
WHERE institucion IS NOT NULL AND institucion_licenciatura IS NULL;
    ";

    // Ejecutar
    $pdo->exec($sql);

    echo "<p style='color:green'>✅ Columnas agregadas exitosamente</p>";

    // Verificar
    $result = $pdo->query("SELECT column_name, data_type
                           FROM information_schema.columns
                           WHERE table_name = 'diagnostico_disde_respuestas'
                           AND column_name IN ('institucion', 'institucion_licenciatura', 'institucion_maestria')
                           ORDER BY ordinal_position");

    $columns = $result->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Columnas de institución:</h3><ul>";
    foreach ($columns as $col) {
        echo "<li><strong>{$col['column_name']}</strong>: {$col['data_type']}</li>";
    }
    echo "</ul>";

    echo "<div style='background:#dcfce7;padding:15px;margin:20px 0;border-radius:5px;'>";
    echo "<h3 style='color:#16a34a'>✅ Migración completada exitosamente</h3>";
    echo "<p><strong>IMPORTANTE:</strong> Elimina este archivo ahora por seguridad:</p>";
    echo "<code>/public_html/migrate-add-instituciones.php</code>";
    echo "</div>";

    echo "<p><strong>Nota:</strong> La columna 'institucion' original se mantiene por compatibilidad. Los datos existentes se copiaron a 'institucion_licenciatura'.</p>";

} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
