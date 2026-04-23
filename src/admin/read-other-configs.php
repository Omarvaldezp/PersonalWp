<?php
echo "<h2>Leyendo /dof/database.php</h2>";
$dofDb = '/home/customer/www/omarvaldez.com/public_html/dof/database.php';
if (file_exists($dofDb)) {
    echo "<pre style='background:#f5f5f5;padding:15px;border:1px solid #ccc;'>";
    echo htmlspecialchars(file_get_contents($dofDb));
    echo "</pre>";
} else {
    echo "<p>No existe</p>";
}

echo "<h2>Extrayendo SOLO los nombres de BD de /bll/config.php</h2>";
$bllConfig = '/home/customer/www/omarvaldez.com/public_html/bll/config.php';
if (file_exists($bllConfig)) {
    $content = file_get_contents($bllConfig);
    // Buscar solo DB_NAME o database name
    if (preg_match_all('/(DB_NAME|database|dbname|pg_dbname)\s*[=:]\s*[\'\"]([^\'\"]+)/i', $content, $matches)) {
        echo "<p>Nombres de base de datos encontrados:</p><ul>";
        foreach ($matches[2] as $dbname) {
            echo "<li><code>" . htmlspecialchars($dbname) . "</code></li>";
        }
        echo "</ul>";
    }

    // Buscar patrón específico de PostgreSQL
    if (preg_match_all('/(?:pgsql:|postgresql:)[^\'"]*dbname=([^;\'"]+)/i', $content, $matches)) {
        echo "<p>DSN PostgreSQL encontrados:</p><ul>";
        foreach ($matches[0] as $dsn) {
            echo "<li><code>" . htmlspecialchars($dsn) . "</code></li>";
        }
        echo "</ul>";
    }
}

echo "<h2>Buscando en .ftp-deploy-sync-state.json</h2>";
$ftpState = '/home/customer/www/omarvaldez.com/public_html/.ftp-deploy-sync-state.json';
if (file_exists($ftpState)) {
    $json = file_get_contents($ftpState);
    echo "<p>Archivo existe (" . strlen($json) . " bytes)</p>";
    // Buscar referencias a config.php
    if (stripos($json, 'config.php') !== false) {
        echo "<p style='color:green'>✓ Contiene referencias a config.php</p>";
        // Extraer timestamp de config.php
        $decoded = json_decode($json, true);
        if ($decoded && isset($decoded['generatedTime'])) {
            echo "<p>Último deploy: " . date('Y-m-d H:i:s', $decoded['generatedTime'] / 1000) . "</p>";
        }
    }
}
