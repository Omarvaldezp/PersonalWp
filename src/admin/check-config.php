<?php
// Listar archivos en el directorio de config del servidor
$configDir = __DIR__ . '/../api/config/';

echo "<h2>Directorio de config:</h2>";
echo "<p>Path: $configDir</p>";
echo "<p>Existe: " . (is_dir($configDir) ? 'SI' : 'NO') . "</p>";

if (is_dir($configDir)) {
    echo "<h3>Archivos encontrados:</h3>";
    echo "<ul>";
    $files = scandir($configDir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $fullPath = $configDir . $file;
        $size = filesize($fullPath);
        $modified = date('Y-m-d H:i:s', filemtime($fullPath));
        echo "<li><strong>$file</strong> - $size bytes - Modificado: $modified</li>";
    }
    echo "</ul>";
}

echo "<h2>Directorio api completo:</h2>";
$apiDir = __DIR__ . '/../api/';
if (is_dir($apiDir)) {
    echo "<ul>";
    $files = scandir($apiDir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        echo "<li>$file</li>";
    }
    echo "</ul>";
}

echo "<h2>Buscando cualquier archivo .php con 'DB_' o 'config':</h2>";
$publicHtml = dirname(__DIR__);
echo "<p>Buscando en: $publicHtml</p>";

function searchFiles($dir, $pattern, $depth = 0) {
    if ($depth > 3) return [];
    $found = [];
    if (!is_dir($dir)) return $found;

    $files = @scandir($dir);
    if (!$files) return $found;

    foreach ($files as $file) {
        if ($file === '.' || $file === '..' || $file === 'node_modules') continue;
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            $found = array_merge($found, searchFiles($path, $pattern, $depth + 1));
        } elseif (preg_match($pattern, $file)) {
            $found[] = $path;
        }
    }
    return $found;
}

$configFiles = searchFiles($publicHtml, '/config\.php$/i');
echo "<p>Archivos config.php encontrados:</p><ul>";
foreach ($configFiles as $f) {
    echo "<li>$f - " . filesize($f) . " bytes</li>";
}
echo "</ul>";

echo "<h2>Buscando .env files:</h2>";
$envFiles = searchFiles($publicHtml, '/^\.env/');
echo "<ul>";
foreach ($envFiles as $f) {
    echo "<li>$f</li>";
}
echo "</ul>";
