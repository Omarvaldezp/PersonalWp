<?php
/**
 * Verificar estado del usuario admin
 */
require_once __DIR__ . '/api/config/config.php';

try {
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "<h2>🔍 Diagnóstico Usuario Admin</h2>";

    // Verificar si existe tabla usuarios
    $tables = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname='public'")->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Tablas: " . implode(', ', $tables) . "</p>";

    // Buscar usuario admin
    $admin = $pdo->query("SELECT id, username, email, password_hash, activo FROM usuarios WHERE username = 'admin'")->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        echo "<p style='color:red'>❌ Usuario 'admin' NO existe en la tabla usuarios</p>";

        // Mostrar todos los usuarios
        $users = $pdo->query("SELECT username, email, activo FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);
        echo "<p>Usuarios en la DB:</p><pre>" . print_r($users, true) . "</pre>";
    } else {
        echo "<p style='color:green'>✓ Usuario admin encontrado</p>";
        echo "<ul>";
        echo "<li>ID: " . $admin['id'] . "</li>";
        echo "<li>Username: " . $admin['username'] . "</li>";
        echo "<li>Email: " . $admin['email'] . "</li>";
        echo "<li>Activo: " . ($admin['activo'] ? 'Sí' : 'No') . "</li>";
        echo "<li>Hash (primeros 20 chars): " . substr($admin['password_hash'], 0, 20) . "...</li>";
        echo "</ul>";

        // Probar password_verify
        $testPassword = 'admin123';
        $verify = password_verify($testPassword, $admin['password_hash']);

        echo "<p>Test password_verify('admin123', hash): ";
        if ($verify) {
            echo "<strong style='color:green'>✅ CORRECTO</strong></p>";
        } else {
            echo "<strong style='color:red'>❌ FALLA</strong></p>";

            // Generar hash correcto
            $correctHash = password_hash($testPassword, PASSWORD_BCRYPT);
            echo "<p>Hash correcto para 'admin123': <code>$correctHash</code></p>";

            echo "<form method='POST'>";
            echo "<button type='submit' name='fix_password'>Corregir password ahora</button>";
            echo "</form>";

            if (isset($_POST['fix_password'])) {
                $stmt = $pdo->prepare("UPDATE usuarios SET password_hash = :hash WHERE username = 'admin'");
                $stmt->execute([':hash' => $correctHash]);
                echo "<p style='color:green'>✅ Password actualizado. <a href='?'>Recargar página</a></p>";
            }
        }
    }

} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
