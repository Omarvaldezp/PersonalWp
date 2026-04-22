<?php
// Leer el config.php del sitio anterior para extraer credenciales
$bllConfigPath = dirname(__DIR__) . '/bll/config.php';

echo "<h2>Leyendo config del sitio anterior:</h2>";
echo "<p>Path: $bllConfigPath</p>";
echo "<p>Existe: " . (file_exists($bllConfigPath) ? 'SI' : 'NO') . "</p>";

if (file_exists($bllConfigPath)) {
    echo "<h3>Contenido completo:</h3>";
    echo "<pre style='background:#f5f5f5;padding:15px;border:1px solid #ccc;'>";
    echo htmlspecialchars(file_get_contents($bllConfigPath));
    echo "</pre>";
}

echo "<h2>Listando /bll/:</h2>";
$bllDir = dirname(__DIR__) . '/bll/';
if (is_dir($bllDir)) {
    echo "<ul>";
    $files = scandir($bllDir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $fullPath = $bllDir . $file;
        $size = is_file($fullPath) ? filesize($fullPath) . ' bytes' : 'DIR';
        echo "<li><strong>$file</strong> - $size</li>";
    }
    echo "</ul>";
}
