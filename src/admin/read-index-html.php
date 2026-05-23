<?php
/**
 * Leer index.html del admin anterior
 */
$indexHtml = __DIR__ . '/index.html';

if (file_exists($indexHtml)) {
    $content = file_get_contents($indexHtml);
    $size = filesize($indexHtml);

    echo "<h2>📄 index.html - " . number_format($size) . " bytes</h2>";

    // Mostrar primeras 100 líneas
    $lines = explode("\n", $content);
    echo "<h3>Primeras 100 líneas:</h3>";
    echo "<pre style='background:#f5f5f5;padding:15px;border:1px solid #ccc;overflow-x:auto;max-height:600px;'>";
    echo htmlspecialchars(implode("\n", array_slice($lines, 0, 100)));
    echo "</pre>";

    // Buscar referencias a APIs o endpoints
    echo "<h3>🔍 Referencias a API/backend encontradas:</h3>";
    preg_match_all('/(fetch|axios|XMLHttpRequest|api|endpoint|database|\.php|\.json)/i', $content, $matches);
    $unique = array_unique($matches[0]);
    echo "<pre>" . htmlspecialchars(implode(", ", $unique)) . "</pre>";

    // Buscar scripts externos
    echo "<h3>📦 Scripts/librerías cargados:</h3>";
    preg_match_all('/<script[^>]+src=["\']([^"\']+)["\']/', $content, $scripts);
    if (!empty($scripts[1])) {
        echo "<ul>";
        foreach (array_unique($scripts[1]) as $script) {
            echo "<li>" . htmlspecialchars($script) . "</li>";
        }
        echo "</ul>";
    }

    // Buscar configuración o inicialización
    echo "<h3>⚙️ Buscar configuración (primeros 50 matches):</h3>";
    preg_match_all('/(config|API_URL|BASE_URL|endpoint|database)/i', $content, $configs);
    $configMatches = array_slice(array_unique($configs[0]), 0, 50);
    echo "<pre>" . htmlspecialchars(implode(", ", $configMatches)) . "</pre>";

} else {
    echo "<p>❌ index.html no existe</p>";
}
?>
