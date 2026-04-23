<?php
/**
 * Test de conexión con credenciales específicas
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

$db_host = 'localhost';
$db_port = '5432';
$db_name = 'dbs6hrd5zqj1pt';
$db_user = 'ur59a0toszauz';
$db_password = 'Omarvaldez2022.';

echo "<h1>Test de conexión PostgreSQL</h1>";
echo "<p>Host: $db_host | DB: $db_name | User: $db_user</p>";

try {
    $dsn = "pgsql:host=$db_host;port=$db_port;dbname=$db_name";
    $pdo = new PDO($dsn, $db_user, $db_password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);

    echo "<p style='color:green;font-size:20px'>✓ CONEXIÓN EXITOSA</p>";

    // Listar tablas
    echo "<h2>Tablas en la base de datos:</h2>";
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($tables)) {
        echo "<p style='color:orange'>⚠ La DB está vacía - necesita el schema</p>";
    } else {
        echo "<ul>";
        foreach ($tables as $t) {
            // Contar registros
            try {
                $count = $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
                echo "<li><strong>$t</strong> ($count registros)</li>";
            } catch (Exception $e) {
                echo "<li><strong>$t</strong></li>";
            }
        }
        echo "</ul>";

        // Verificar si es el proyecto correcto
        $expectedTables = ['usuarios', 'blog_posts', 'cursos', 'investigaciones'];
        $matches = array_intersect($expectedTables, $tables);
        if (count($matches) >= 3) {
            echo "<p style='color:green;font-size:18px'>✓ ¡Esta ES la base de datos del proyecto!</p>";
        } else {
            echo "<p style='color:orange'>⚠ Tiene tablas pero no coinciden con el schema esperado</p>";
        }
    }

    // Versión de PostgreSQL
    $version = $pdo->query("SELECT version()")->fetchColumn();
    echo "<h3>Versión PostgreSQL:</h3><p>$version</p>";

} catch (PDOException $e) {
    echo "<p style='color:red;font-size:18px'>✗ ERROR DE CONEXIÓN</p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
