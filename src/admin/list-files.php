<?php
/**
 * Diagnóstico: Listar todos los archivos en /admin/
 */
echo "<h2>📁 Archivos en /admin/</h2>";

$adminDir = __DIR__;
$files = scandir($adminDir);

echo "<table border='1' cellpadding='10' style='border-collapse:collapse;width:100%;font-family:monospace;font-size:14px;'>";
echo "<tr style='background:#333;color:white;'><th>Archivo</th><th>Tamaño</th><th>Última modificación</th></tr>";

foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;

    $path = $adminDir . '/' . $file;
    $size = is_file($path) ? filesize($path) : '-';
    $mtime = filemtime($path);
    $type = is_dir($path) ? '📁 DIR' : '📄 FILE';

    echo "<tr>";
    echo "<td><strong>$type</strong> $file</td>";
    echo "<td>" . ($size !== '-' ? number_format($size) . ' bytes' : 'DIR') . "</td>";
    echo "<td>" . date('Y-m-d H:i:s', $mtime) . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<h2>🔍 Contenido de index.php (primeras 30 líneas)</h2>";
if (file_exists($adminDir . '/index.php')) {
    $content = file($adminDir . '/index.php');
    echo "<pre style='background:#f5f5f5;padding:15px;border:1px solid #ccc;'>";
    echo htmlspecialchars(implode('', array_slice($content, 0, 30)));
    echo "</pre>";
}

echo "<h2>🔍 Contenido de login.php (primeras 30 líneas)</h2>";
if (file_exists($adminDir . '/login.php')) {
    $content = file($adminDir . '/login.php');
    echo "<pre style='background:#f5f5f5;padding:15px;border:1px solid #ccc;'>";
    echo htmlspecialchars(implode('', array_slice($content, 0, 30)));
    echo "</pre>";
}

echo "<h2>📊 Archivos PHP en /admin/</h2>";
$phpFiles = glob($adminDir . '/*.php');
echo "<ul>";
foreach ($phpFiles as $php) {
    echo "<li>" . basename($php) . "</li>";
}
echo "</ul>";
?>
