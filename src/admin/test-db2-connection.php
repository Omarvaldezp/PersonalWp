<?php
/**
 * Test de la SEGUNDA base de datos
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

$db_host = 'localhost';
$db_port = '5432';
$db_name = 'dbguny4ziwyjef';
$db_user = 'uqkt4rfs4dnou';
$db_password = 'Omarvaldez2022.';

echo "<h1>Test de conexión - Segunda DB</h1>";
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
        echo "<p style='color:orange'>⚠ Esta DB también está vacía</p>";
    } else {
        echo "<ul>";
        foreach ($tables as $t) {
            try {
                $count = $pdo->query("SELECT COUNT(*) FROM \"$t\"")->fetchColumn();
                echo "<li><strong>$t</strong> ($count registros)</li>";
            } catch (Exception $e) {
                echo "<li><strong>$t</strong> (error contando: " . $e->getMessage() . ")</li>";
            }
        }
        echo "</ul>";

        // Verificar si es el proyecto correcto
        $expectedTables = ['usuarios', 'blog_posts', 'cursos', 'investigaciones', 'contactos'];
        $matches = array_intersect($expectedTables, $tables);
        if (count($matches) >= 3) {
            echo "<p style='color:green;font-size:18px'>✓ ¡Esta ES la DB del proyecto académico!</p>";
            echo "<p>Tablas coincidentes: " . implode(', ', $matches) . "</p>";
        } else {
            echo "<p style='color:orange;font-size:18px'>⚠ Esta DB tiene OTRO sistema (no es el proyecto académico)</p>";
            echo "<p><strong>NO TOCAR ESTA DB</strong> - Usar la primera (dbs6hrd5zqj1pt) que está vacía</p>";
        }
    }

    // Versión de PostgreSQL
    $version = $pdo->query("SELECT version()")->fetchColumn();
    echo "<h3>Versión PostgreSQL:</h3><p>$version</p>";

} catch (PDOException $e) {
    echo "<p style='color:red;font-size:18px'>✗ ERROR DE CONEXIÓN</p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<p>Esto puede significar que el usuario ur59a0toszauz no tiene acceso a dbguny4ziwyjef</p>";
}
