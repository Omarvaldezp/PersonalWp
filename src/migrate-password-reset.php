<?php
/**
 * Migración: Agregar campos de reset de contraseña
 * Ejecutar UNA SOLA VEZ: https://omarvaldez.com/migrate-password-reset.php?key=migrate123
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

    echo "<h2>🔄 Migración: Campos de recuperación de contraseña</h2>";

    // Agregar columnas
    $sql = "ALTER TABLE usuarios
            ADD COLUMN IF NOT EXISTS reset_token VARCHAR(255),
            ADD COLUMN IF NOT EXISTS reset_token_expires TIMESTAMP";

    $pdo->exec($sql);
    echo "<p style='color:green'>✅ Columnas agregadas a tabla usuarios</p>";

    // Crear índice
    $sql = "CREATE INDEX IF NOT EXISTS idx_usuarios_reset_token ON usuarios(reset_token)";
    $pdo->exec($sql);
    echo "<p style='color:green'>✅ Índice creado</p>";

    // Verificar
    $result = $pdo->query("SELECT column_name, data_type
                           FROM information_schema.columns
                           WHERE table_name = 'usuarios'
                           AND column_name IN ('reset_token', 'reset_token_expires')
                           ORDER BY column_name");

    $columns = $result->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Columnas verificadas:</h3><ul>";
    foreach ($columns as $col) {
        echo "<li><strong>{$col['column_name']}</strong>: {$col['data_type']}</li>";
    }
    echo "</ul>";

    echo "<div style='background:#dcfce7;padding:15px;margin:20px 0;border-radius:5px;'>";
    echo "<h3 style='color:#16a34a'>✅ Migración completada exitosamente</h3>";
    echo "<p><strong>IMPORTANTE:</strong> Elimina este archivo ahora por seguridad:</p>";
    echo "<code>/public_html/migrate-password-reset.php</code>";
    echo "</div>";

    echo "<h3>Prueba el sistema:</h3>";
    echo "<ol>";
    echo "<li>Ve a <a href='/admin/login.php'>/admin/login.php</a></li>";
    echo "<li>Click en '¿Olvidaste tu contraseña?'</li>";
    echo "<li>Ingresa el email del admin</li>";
    echo "<li>Revisa tu correo para el enlace de reset</li>";
    echo "</ol>";

} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
