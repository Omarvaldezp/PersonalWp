<?php
/**
 * Verificar qué hay en los otros sistemas (/dof y /bll)
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Verificando otros sistemas en el servidor</h1>";

// ============================================
// 1. Sistema /dof
// ============================================
echo "<h2>1. Sistema /dof/database.php</h2>";
$dofDb = '/home/customer/www/omarvaldez.com/public_html/dof/database.php';
if (file_exists($dofDb)) {
    echo "<p>✓ Archivo existe (" . filesize($dofDb) . " bytes)</p>";
    echo "<h3>Contenido completo:</h3>";
    echo "<pre style='background:#f5f5f5;padding:15px;border:1px solid #ccc;overflow-x:auto'>";
    echo htmlspecialchars(file_get_contents($dofDb));
    echo "</pre>";
} else {
    echo "<p>✗ No existe</p>";
}

// ============================================
// 2. Sistema /bll
// ============================================
echo "<h2>2. Sistema /bll/config.php</h2>";
$bllConfig = '/home/customer/www/omarvaldez.com/public_html/bll/config.php';
if (file_exists($bllConfig)) {
    $content = file_get_contents($bllConfig);
    echo "<p>✓ Archivo existe (" . strlen($content) . " bytes)</p>";

    // Buscar SOLO credenciales de base de datos (sin mostrar passwords completos)
    echo "<h3>Credenciales de base de datos encontradas:</h3>";

    // Buscar todos los patrones posibles
    $patterns = [
        '/(?:DB_|db_|pg_|POSTGRES_)HOST\s*[=:]\s*[\'\"]?([^\'";\s\n]+)/i',
        '/(?:DB_|db_|pg_|POSTGRES_)NAME\s*[=:]\s*[\'\"]?([^\'";\s\n]+)/i',
        '/(?:DB_|db_|pg_|POSTGRES_)USER\s*[=:]\s*[\'\"]?([^\'";\s\n]+)/i',
        '/(?:dbname|database)\s*[=:]\s*[\'\"]?([^\'";\s\n]+)/i',
    ];

    echo "<ul>";
    foreach ($patterns as $pattern) {
        if (preg_match_all($pattern, $content, $matches)) {
            foreach ($matches[0] as $i => $fullMatch) {
                echo "<li><code>" . htmlspecialchars($fullMatch) . "</code></li>";
            }
        }
    }
    echo "</ul>";

    // Verificar tipo de base de datos
    if (stripos($content, 'mysql') !== false || stripos($content, 'mysqli') !== false) {
        echo "<p style='color:orange'>⚠️ Este sistema usa <strong>MySQL/MariaDB</strong>, no PostgreSQL</p>";
    } elseif (stripos($content, 'pgsql') !== false || stripos($content, 'postgres') !== false) {
        echo "<p style='color:blue'>ℹ️ Este sistema usa <strong>PostgreSQL</strong></p>";
    }
} else {
    echo "<p>✗ No existe</p>";
}

// ============================================
// 3. Buscar MySQL databases
// ============================================
echo "<h2>3. Bases de datos MySQL disponibles</h2>";
echo "<p>Intentando listar bases de datos MySQL (si existen)...</p>";

// Las credenciales de MySQL suelen estar en diferentes archivos
$mysqlAttempts = [
    ['host' => 'localhost', 'user' => 'u1886', 'pass' => 'Omarvaldez2022.'],
    ['host' => 'localhost', 'user' => 'u1886z3kml7koopdl', 'pass' => 'Omarvaldez2022.'],
];

foreach ($mysqlAttempts as $i => $creds) {
    echo "<h3>Intento " . ($i+1) . " - Usuario: {$creds['user']}</h3>";
    try {
        if (!function_exists('mysqli_connect')) {
            echo "<p>mysqli no está disponible</p>";
            continue;
        }

        $mysqli = @new mysqli($creds['host'], $creds['user'], $creds['pass']);

        if ($mysqli->connect_error) {
            echo "<p style='color:red'>✗ Error: " . htmlspecialchars($mysqli->connect_error) . "</p>";
        } else {
            echo "<p style='color:green'>✓ Conexión exitosa a MySQL</p>";

            // Listar bases de datos
            $result = $mysqli->query("SHOW DATABASES");
            if ($result) {
                echo "<p>Bases de datos MySQL:</p><ul>";
                while ($row = $result->fetch_assoc()) {
                    $dbname = $row['Database'];
                    // Ignorar DBs del sistema
                    if (!in_array($dbname, ['information_schema', 'mysql', 'performance_schema', 'sys'])) {
                        echo "<li><strong>$dbname</strong>";

                        // Intentar contar tablas
                        $mysqli->select_db($dbname);
                        $tables = $mysqli->query("SHOW TABLES");
                        if ($tables) {
                            $count = $tables->num_rows;
                            echo " ($count tablas)";
                        }
                        echo "</li>";
                    }
                }
                echo "</ul>";
            }

            $mysqli->close();
        }
    } catch (Exception $e) {
        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// ============================================
// 4. Verificar si src/api/config/config.php existe en el servidor
// ============================================
echo "<h2>4. Verificar config.php del proyecto actual</h2>";
$projectConfig = '/home/customer/www/omarvaldez.com/public_html/src/api/config/config.php';
if (file_exists($projectConfig)) {
    echo "<p style='color:green'>✓ El archivo config.php SÍ existe en el servidor</p>";
    echo "<p>Tamaño: " . filesize($projectConfig) . " bytes</p>";

    if (filesize($projectConfig) > 0) {
        echo "<h3>Contenido (SIN passwords):</h3>";
        $content = file_get_contents($projectConfig);
        // Ocultar passwords pero mostrar estructura
        $safe = preg_replace('/(PASSWORD[\'"]?\s*=\s*[\'"])[^\'"]+/', '$1***', $content);
        echo "<pre style='background:#f5f5f5;padding:15px;border:1px solid #ccc;'>";
        echo htmlspecialchars($safe);
        echo "</pre>";
    } else {
        echo "<p style='color:red'>⚠️ El archivo está VACÍO</p>";
    }
} else {
    echo "<p style='color:red'>✗ El archivo config.php NO existe en el servidor</p>";
    echo "<p>Esto confirma que el proyecto nunca se ha instalado/configurado</p>";
}
