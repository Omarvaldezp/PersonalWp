<?php
/**
 * Test database connection - Compare direct PDO vs Database class
 * Access: https://omarvaldez.com/test-db-connection.php?key=test123
 */

$TEST_KEY = 'test123';
if (!isset($_GET['key']) || $_GET['key'] !== $TEST_KEY) {
    die('❌ Access denied. Add ?key=test123 to the URL');
}

header('Content-Type: text/html; charset=UTF-8');

echo "<h2>🔍 Database Connection Test</h2>";
echo "<style>
    body { font-family: monospace; padding: 20px; background: #f5f5f5; }
    .success { color: #16a34a; font-weight: bold; }
    .error { color: #dc2626; font-weight: bold; }
    .section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; }
    pre { background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 5px; overflow-x: auto; }
</style>";

require_once __DIR__ . '/api/config/config.php';

// TEST 1: Direct PDO Connection
echo "<div class='section'>";
echo "<h3>Test 1: Direct PDO Connection</h3>";
try {
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    echo "<p class='success'>✅ Connected successfully</p>";
    echo "<p>Host: " . DB_HOST . "</p>";
    echo "<p>Database: " . DB_NAME . "</p>";

    $count = $pdo->query("SELECT COUNT(*) as count FROM diagnostico_disde_respuestas")->fetch();
    echo "<p class='success'>Record count: {$count['count']}</p>";

    $records = $pdo->query("SELECT id, nombre, correo, conocimiento_total FROM diagnostico_disde_respuestas ORDER BY id DESC LIMIT 3")->fetchAll();
    echo "<p>Last 3 records:</p>";
    echo "<pre>" . print_r($records, true) . "</pre>";

} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// TEST 2: Database Class
echo "<div class='section'>";
echo "<h3>Test 2: Database Class (used by API)</h3>";
try {
    require_once __DIR__ . '/api/config/Database.php';
    $db = Database::getInstance();

    echo "<p class='success'>✅ Database instance created</p>";

    $count = $db->selectOne("SELECT COUNT(*) as count FROM diagnostico_disde_respuestas");
    echo "<p class='success'>Record count: {$count['count']}</p>";

    $records = $db->select("SELECT id, nombre, correo, conocimiento_total FROM diagnostico_disde_respuestas ORDER BY id DESC LIMIT 3");
    echo "<p>Last 3 records:</p>";
    echo "<pre>" . print_r($records, true) . "</pre>";

} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// TEST 3: Database Class with LIMIT/OFFSET parameters (like the API uses)
echo "<div class='section'>";
echo "<h3>Test 3: Database Class with LIMIT/OFFSET (like API)</h3>";
try {
    $page = 1;
    $limit = 20;
    $offset = ($page - 1) * $limit;

    $total = $db->selectOne("SELECT COUNT(*) as count FROM diagnostico_disde_respuestas")['count'];
    echo "<p>Total from COUNT query: $total</p>";

    $sql = "SELECT id, nombre, correo, grado_academico, conocimiento_total,
                   created_at, email_enviado
            FROM diagnostico_disde_respuestas
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset";

    echo "<p>SQL Query:</p>";
    echo "<pre>" . htmlspecialchars($sql) . "</pre>";
    echo "<p>Parameters: limit=$limit, offset=$offset</p>";

    $records = $db->select($sql, [
        ':limit' => $limit,
        ':offset' => $offset
    ]);

    echo "<p class='success'>Records returned: " . count($records) . "</p>";
    echo "<pre>" . print_r($records, true) . "</pre>";

} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
echo "</div>";

// TEST 4: Check what the API actually returns
echo "<div class='section'>";
echo "<h3>Test 4: What API Returns</h3>";
$apiUrl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/api/controllers/diagnostico-disde.php?page=1&limit=20';
echo "<p>API URL: <a href='$apiUrl' target='_blank'>$apiUrl</a></p>";
try {
    $response = file_get_contents($apiUrl);
    $data = json_decode($response, true);
    echo "<p>API Response:</p>";
    echo "<pre>" . print_r($data, true) . "</pre>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

echo "<p style='margin-top: 30px; padding: 15px; background: #fff3cd; border-radius: 5px;'><strong>⚠️ IMPORTANT:</strong> Delete this file after debugging for security reasons.</p>";
?>
