<?php
/**
 * Migración: Crear tabla para Diagnóstico DISDE
 * Ejecutar UNA SOLA VEZ: https://omarvaldez.com/migrate-diagnostico-disde.php?key=migrate123
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

    echo "<h2>🔄 Migración: Tabla Diagnóstico DISDE</h2>";

    // SQL del schema (incluido directamente)
    $sql = "
-- Tabla para almacenar respuestas del Diagnóstico DISDE
CREATE TABLE IF NOT EXISTS diagnostico_disde_respuestas (
    id SERIAL PRIMARY KEY,

    -- Datos del participante
    nombre VARCHAR(255) NOT NULL,
    correo VARCHAR(255) NOT NULL,
    grado_academico VARCHAR(100),
    carrera_licenciatura VARCHAR(255),
    area_formacion VARCHAR(255),
    institucion VARCHAR(255),

    -- Puntajes
    conocimiento_total INTEGER,

    -- Respuestas por área (JSON para flexibilidad)
    respuestas_area1 JSONB,
    respuestas_area2 JSONB,
    respuestas_area3 JSONB,
    respuestas_area4 JSONB,
    respuestas_area5 JSONB,
    respuestas_area6 JSONB,

    -- Todas las respuestas individuales
    respuestas_completas JSONB,

    -- Resultados calculados por área
    resultados_por_area JSONB,

    -- Metadatos
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    email_enviado BOOLEAN DEFAULT FALSE,
    email_enviado_at TIMESTAMP
);

-- Índices para búsquedas eficientes
CREATE INDEX IF NOT EXISTS idx_diagnostico_correo ON diagnostico_disde_respuestas(correo);
CREATE INDEX IF NOT EXISTS idx_diagnostico_fecha ON diagnostico_disde_respuestas(created_at);
CREATE INDEX IF NOT EXISTS idx_diagnostico_conocimiento ON diagnostico_disde_respuestas(conocimiento_total);
    ";

    // Ejecutar
    $pdo->exec($sql);

    echo "<p style='color:green'>✅ Tabla creada exitosamente</p>";

    // Verificar
    $result = $pdo->query("SELECT column_name, data_type
                           FROM information_schema.columns
                           WHERE table_name = 'diagnostico_disde_respuestas'
                           ORDER BY ordinal_position");

    $columns = $result->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Columnas creadas:</h3><ul>";
    foreach ($columns as $col) {
        echo "<li><strong>{$col['column_name']}</strong>: {$col['data_type']}</li>";
    }
    echo "</ul>";

    echo "<div style='background:#dcfce7;padding:15px;margin:20px 0;border-radius:5px;'>";
    echo "<h3 style='color:#16a34a'>✅ Migración completada exitosamente</h3>";
    echo "<p><strong>IMPORTANTE:</strong> Elimina este archivo ahora por seguridad:</p>";
    echo "<code>/public_html/migrate-diagnostico-disde.php</code>";
    echo "</div>";

    echo "<h3>Próximos pasos:</h3>";
    echo "<ol>";
    echo "<li>Sube el PDF a: <code>/public_html/uploads/diagnostico-disde-info.pdf</code></li>";
    echo "<li>Prueba el cuestionario en: <a href='/diagnosticoDISDE.php'>/diagnosticoDISDE.php</a></li>";
    echo "<li>Ve las respuestas en: <a href='/admin/diagnostico-disde.php'>/admin/diagnostico-disde.php</a></li>";
    echo "</ol>";

} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
