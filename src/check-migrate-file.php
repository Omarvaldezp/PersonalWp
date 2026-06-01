<?php
echo "<h2>Verificación de archivos</h2>";
echo "<p>Archivo actual: " . __FILE__ . "</p>";
echo "<p>Directorio: " . __DIR__ . "</p>";

echo "<h3>Contenido del archivo migrate-diagnostico-disde.php:</h3>";
$migrateFile = __DIR__ . '/migrate-diagnostico-disde.php';
if (file_exists($migrateFile)) {
    echo "<p>✓ Archivo existe</p>";
    echo "<p>Tamaño: " . filesize($migrateFile) . " bytes</p>";
    echo "<p>Última modificación: " . date('Y-m-d H:i:s', filemtime($migrateFile)) . "</p>";

    $content = file_get_contents($migrateFile);

    if (strpos($content, 'CREATE TABLE IF NOT EXISTS') !== false) {
        echo "<p style='color:green;'>✓ El SQL está incluido en el archivo</p>";
    } else {
        echo "<p style='color:red;'>✗ El SQL NO está en el archivo</p>";
    }

    if (strpos($content, 'file_get_contents($schemaFile)') !== false) {
        echo "<p style='color:red;'>✗ Aún tiene el código viejo (file_get_contents)</p>";
    } else {
        echo "<p style='color:green;'>✓ Ya no tiene file_get_contents</p>";
    }

    echo "<h3>Primeras 100 líneas:</h3>";
    $lines = explode("\n", $content);
    echo "<pre style='background:#f5f5f5;padding:15px;border:1px solid #ccc;max-height:400px;overflow:auto;'>";
    echo htmlspecialchars(implode("\n", array_slice($lines, 0, 100)));
    echo "</pre>";
} else {
    echo "<p style='color:red;'>✗ Archivo NO existe</p>";
}
?>
