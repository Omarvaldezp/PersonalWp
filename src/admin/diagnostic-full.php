<?php
/**
 * Diagnóstico Completo - Admin Antiguo vs Nuevo
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<style>
body { font-family: monospace; padding: 20px; background: #f5f5f5; }
.section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
h2 { color: #2563eb; border-bottom: 2px solid #2563eb; padding-bottom: 10px; }
.success { color: #16a34a; }
.error { color: #dc2626; }
.warning { color: #ea580c; }
pre { background: #1f2937; color: #f3f4f6; padding: 15px; border-radius: 5px; overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
th { background: #333; color: white; }
</style>";

echo "<h1>🔍 Diagnóstico Completo: Admin Antiguo vs Nuevo</h1>";

// ============================================
// 1. ANALIZAR INDEX.HTML (Admin antiguo)
// ============================================
echo "<div class='section'>";
echo "<h2>1️⃣ Admin Antiguo (index.html)</h2>";

$indexHtml = __DIR__ . '/index.html';
if (file_exists($indexHtml)) {
    $content = file_get_contents($indexHtml);
    $size = filesize($indexHtml);
    echo "<p><strong>Tamaño:</strong> " . number_format($size) . " bytes</p>";

    // Detectar framework/tecnología
    if (stripos($content, 'react') !== false || stripos($content, 'React') !== false) {
        echo "<p class='success'>✓ Detectado: <strong>React</strong></p>";
    } elseif (stripos($content, 'vue') !== false || stripos($content, 'Vue') !== false) {
        echo "<p class='success'>✓ Detectado: <strong>Vue.js</strong></p>";
    } elseif (stripos($content, 'angular') !== false) {
        echo "<p class='success'>✓ Detectado: <strong>Angular</strong></p>";
    } elseif (stripos($content, 'firebase') !== false) {
        echo "<p class='success'>✓ Detectado: <strong>Firebase</strong></p>";
    }

    // Buscar referencias a API/endpoints
    echo "<h3>🔗 APIs y endpoints detectados:</h3>";
    preg_match_all('/(https?:\/\/[^\s\'"<>]+|\/api\/[^\s\'"<>]+)/', $content, $urls);
    if (!empty($urls[0])) {
        $uniqueUrls = array_unique($urls[0]);
        echo "<ul>";
        foreach (array_slice($uniqueUrls, 0, 20) as $url) {
            echo "<li>" . htmlspecialchars($url) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='warning'>⚠ No se detectaron endpoints API explícitos</p>";
    }

    // Buscar configuración de Firebase
    if (stripos($content, 'firebase') !== false) {
        echo "<h3>🔥 Configuración Firebase:</h3>";
        if (preg_match('/apiKey["\']?\s*:\s*["\']([^"\']+)/', $content, $match)) {
            echo "<p>API Key encontrado (parcial): " . substr($match[1], 0, 10) . "...</p>";
        }
        if (preg_match('/projectId["\']?\s*:\s*["\']([^"\']+)/', $content, $match)) {
            echo "<p class='success'>✓ Project ID: <strong>{$match[1]}</strong></p>";
        }
    }

    // Mostrar primeras 50 líneas
    echo "<h3>📄 Primeras 50 líneas de código:</h3>";
    $lines = explode("\n", $content);
    echo "<pre>" . htmlspecialchars(implode("\n", array_slice($lines, 0, 50))) . "</pre>";

} else {
    echo "<p class='error'>❌ index.html no existe</p>";
}
echo "</div>";

// ============================================
// 2. REVISAR BASES DE DATOS
// ============================================
echo "<div class='section'>";
echo "<h2>2️⃣ Bases de Datos PostgreSQL</h2>";

require_once __DIR__ . '/../api/config/config.php';

$databases = [
    ['name' => 'dbs6hrd5zqj1pt (configurada actualmente)', 'dbname' => DB_NAME, 'user' => DB_USER, 'pass' => DB_PASSWORD],
    ['name' => 'dbguny4ziwyjef (otra DB)', 'dbname' => 'dbguny4ziwyjef', 'user' => 'uqkt4rfs4dnou', 'pass' => 'Omarvaldez2022.']
];

foreach ($databases as $dbInfo) {
    echo "<h3>📊 {$dbInfo['name']}</h3>";
    try {
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname={$dbInfo['dbname']}";
        $pdo = new PDO($dsn, $dbInfo['user'], $dbInfo['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        echo "<p class='success'>✓ Conexión exitosa</p>";

        // Listar tablas y contar registros
        $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($tables)) {
            echo "<p class='warning'>⚠ Base de datos VACÍA (sin tablas)</p>";
        } else {
            echo "<table>";
            echo "<tr><th>Tabla</th><th>Registros</th><th>Última modificación</th></tr>";
            $hasData = false;
            foreach ($tables as $table) {
                try {
                    $count = $pdo->query("SELECT COUNT(*) FROM \"$table\"")->fetchColumn();
                    $lastMod = $pdo->query("SELECT MAX(created_at) FROM \"$table\"")->fetchColumn();

                    $style = $count > 0 ? "style='background:#dcfce7;font-weight:bold;'" : "";
                    echo "<tr $style>";
                    echo "<td>$table</td>";
                    echo "<td>$count</td>";
                    echo "<td>" . ($lastMod ?: 'N/A') . "</td>";
                    echo "</tr>";

                    if ($count > 0) $hasData = true;
                } catch (Exception $e) {
                    echo "<tr><td>$table</td><td colspan='2'>Error</td></tr>";
                }
            }
            echo "</table>";

            if ($hasData) {
                echo "<p class='success'>✅ <strong>Esta DB tiene DATOS</strong></p>";
            } else {
                echo "<p class='warning'>⚠ Tablas creadas pero SIN datos</p>";
            }
        }

    } catch (PDOException $e) {
        echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
echo "</div>";

// ============================================
// 3. BUSCAR ARCHIVOS API DEL SISTEMA VIEJO
// ============================================
echo "<div class='section'>";
echo "<h2>3️⃣ Archivos API/Backend</h2>";

$apiDir = __DIR__ . '/../api';
if (is_dir($apiDir)) {
    echo "<p>Buscando archivos en <code>/api/</code>...</p>";
    $phpFiles = glob($apiDir . '/{,*/,*/*/}*.php', GLOB_BRACE);
    echo "<ul>";
    foreach (array_slice($phpFiles, 0, 30) as $file) {
        $relative = str_replace($apiDir, '/api', $file);
        echo "<li>" . htmlspecialchars($relative) . "</li>";
    }
    echo "</ul>";
}
echo "</div>";

// ============================================
// 4. CONCLUSIÓN Y RECOMENDACIÓN
// ============================================
echo "<div class='section'>";
echo "<h2>4️⃣ Conclusión</h2>";
echo "<p>Con esta información podré determinar:</p>";
echo "<ul>";
echo "<li>✓ Qué tecnología usa el admin antiguo</li>";
echo "<li>✓ Dónde están los datos existentes</li>";
echo "<li>✓ Cómo integrar o migrar el contenido</li>";
echo "</ul>";
echo "</div>";
?>
