<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "TEST BLOG.PHP - Línea por línea<br><br>";

try {
    echo "1. Requiriendo Auth.php...<br>";
    require_once __DIR__ . '/../api/auth/Auth.php';
    echo "✓ Auth.php OK<br>";

    echo "2. Creando Auth...<br>";
    $auth = new Auth();
    echo "✓ Auth creado<br>";

    echo "3. Probando requireAuthPage()...<br>";
    // En vez de llamarlo, solo verificamos que exista
    if (method_exists($auth, 'requireAuthPage')) {
        echo "✓ Método requireAuthPage() existe<br>";
    } else {
        echo "❌ Método requireAuthPage() NO EXISTE<br>";
        echo "Métodos disponibles: " . implode(', ', get_class_methods($auth)) . "<br>";
    }

    echo "4. Obteniendo user...<br>";
    $user = $auth->user();
    echo "✓ user() ejecutado. Resultado: " . ($user ? 'usuario encontrado' : 'null') . "<br>";

    echo "5. Definiendo variables...<br>";
    $page_title = 'Blog Posts';
    $extra_js = ['assets/blog.js'];
    echo "✓ Variables definidas<br>";

    echo "<br>✅ Todas las líneas principales de blog.php funcionan<br>";
    echo "<br>Intentando incluir header.php...<br>";

    include 'includes/header.php';
    echo "<br>✓ Header incluido exitosamente<br>";

} catch (Exception $e) {
    echo "<br>❌ EXCEPTION: " . $e->getMessage();
    echo "<br>Archivo: " . $e->getFile() . ":" . $e->getLine();
} catch (Error $e) {
    echo "<br>❌ FATAL ERROR: " . $e->getMessage();
    echo "<br>Archivo: " . $e->getFile() . ":" . $e->getLine();
}
