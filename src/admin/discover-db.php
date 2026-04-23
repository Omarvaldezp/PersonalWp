<?php
/**
 * Script de descubrimiento agresivo de credenciales PostgreSQL
 * Busca en múltiples ubicaciones del servidor SiteGround
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Descubrimiento de Configuración PostgreSQL</h1>";

// ============================================
// 1. Variables de entorno del servidor
// ============================================
echo "<h2>1. Variables de entorno</h2>";
echo "<pre>";
$envVars = ['PGHOST', 'PGPORT', 'PGDATABASE', 'PGUSER', 'PGPASSWORD',
            'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD',
            'DATABASE_URL', 'POSTGRES_URL'];
foreach ($envVars as $var) {
    $val = getenv($var);
    if ($val !== false) {
        echo "$var = " . (stripos($var, 'pass') !== false ? '***' : $val) . "\n";
    }
}
echo "</pre>";

// ============================================
// 2. Buscar archivos de configuración en todo el servidor
// ============================================
echo "<h2>2. Buscando archivos de configuración</h2>";
$homePath = '/home/customer/www/omarvaldez.com';
$searchPatterns = [
    'config.php', '.env', 'wp-config.php', 'database.php',
    'db.php', 'connection.php', 'settings.php', 'app.php'
];

foreach ($searchPatterns as $pattern) {
    echo "<h3>Archivos '$pattern':</h3><ul>";
    $cmd = "find $homePath -name '$pattern' -type f 2>/dev/null | head -20";
    $results = shell_exec($cmd);
    if ($results) {
        foreach (explode("\n", trim($results)) as $file) {
            if (empty($file)) continue;
            $size = filesize($file);
            if ($size > 0) {
                echo "<li><strong>$file</strong> ($size bytes)";
                // Si el archivo no es muy grande, buscar credenciales DB
                if ($size < 50000) {
                    $content = @file_get_contents($file);
                    if ($content) {
                        $matches = [];
                        // Buscar patrones de credenciales
                        if (preg_match_all('/(?:DB_|db_|pg_|POSTGRES_)(HOST|NAME|USER|PASS(?:WORD)?|PORT|DATABASE)\s*[=:]\s*[\'\"]?([^\'";\s\n]+)/i', $content, $matches)) {
                            echo "<br><em>Posibles credenciales encontradas:</em>";
                            foreach ($matches[0] as $match) {
                                // Ocultar passwords
                                $display = preg_replace('/(pass\w*\s*[=:]\s*[\'\"]?)[^\'";\s]+/i', '$1***', $match);
                                echo "<br>&nbsp;&nbsp;<code>" . htmlspecialchars($display) . "</code>";
                            }
                        }
                    }
                }
                echo "</li>";
            }
        }
    } else {
        echo "<li>No encontrado</li>";
    }
    echo "</ul>";
}

// ============================================
// 3. Intentar conectar a PostgreSQL sin password (peer auth)
// ============================================
echo "<h2>3. Intentos de conexión a PostgreSQL</h2>";

// Obtener el usuario actual del sistema
$currentUser = get_current_user();
echo "<p>Usuario PHP actual: <code>$currentUser</code></p>";

// Intentar listar databases usando diferentes métodos
$connections = [
    ['dsn' => 'pgsql:host=localhost;port=5432', 'user' => $currentUser, 'pass' => ''],
    ['dsn' => 'pgsql:host=/var/run/postgresql;port=5432', 'user' => $currentUser, 'pass' => ''],
    ['dsn' => 'pgsql:host=localhost;port=5432;dbname=dbs6hrd5zqj1pt', 'user' => $currentUser, 'pass' => ''],
    ['dsn' => 'pgsql:host=localhost;port=5432;dbname=dbguny4ziwyjef', 'user' => $currentUser, 'pass' => ''],
];

foreach ($connections as $i => $conn) {
    echo "<h3>Intento " . ($i+1) . "</h3>";
    echo "<p>DSN: <code>" . htmlspecialchars($conn['dsn']) . "</code></p>";
    try {
        $pdo = new PDO($conn['dsn'], $conn['user'], $conn['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 3
        ]);
        echo "<p style='color:green'>✓ CONECTADO!</p>";

        // Intentar listar tablas
        try {
            $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($tables)) {
                echo "<p>Tablas:</p><ul>";
                foreach ($tables as $t) echo "<li>$t</li>";
                echo "</ul>";
            }
        } catch (Exception $e) {
            echo "<p>No se pudieron listar tablas</p>";
        }

        // Intentar listar databases
        try {
            $stmt = $pdo->query("SELECT datname FROM pg_database WHERE datistemplate = false");
            $dbs = $stmt->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($dbs)) {
                echo "<p>Bases de datos disponibles:</p><ul>";
                foreach ($dbs as $d) echo "<li>$d</li>";
                echo "</ul>";
            }
        } catch (Exception $e) {
            echo "<p>No se pudieron listar DBs</p>";
        }

    } catch (Exception $e) {
        echo "<p style='color:red'>✗ Falló: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// ============================================
// 4. Buscar archivos con extensiones comunes de config
// ============================================
echo "<h2>4. Otros archivos con posibles credenciales</h2>";
$extSearch = "find $homePath -type f \\( -name '*.env*' -o -name '*.conf' -o -name '*.ini' -o -name '*.yml' -o -name '*.yaml' -o -name '*.json' \\) 2>/dev/null | grep -v node_modules | head -30";
$results = shell_exec($extSearch);
echo "<pre>" . htmlspecialchars($results ?: "No encontrados") . "</pre>";
