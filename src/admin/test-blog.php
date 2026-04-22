<?php
// Test de dependencias de blog.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "1. Verificando Auth.php...<br>";
$auth_path = __DIR__ . '/../api/auth/Auth.php';
if (file_exists($auth_path)) {
    echo "✓ Auth.php existe en: $auth_path<br>";
    require_once $auth_path;
    echo "✓ Auth.php cargado sin errores<br>";
} else {
    echo "✗ Auth.php NO encontrado en: $auth_path<br>";
}

echo "<br>2. Verificando header.php...<br>";
if (file_exists('includes/header.php')) {
    echo "✓ header.php existe<br>";
} else {
    echo "✗ header.php NO encontrado<br>";
}

echo "<br>3. Test completado";
