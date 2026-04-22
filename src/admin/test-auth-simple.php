<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "1. Iniciando test...<br>";

try {
    echo "2. Cargando Auth.php...<br>";
    require_once __DIR__ . '/../api/auth/Auth.php';
    echo "✓ Auth.php cargado<br>";

    echo "3. Creando instancia de Auth...<br>";
    $auth = new Auth();
    echo "✓ Auth instanciado<br>";

    echo "4. Verificando check()...<br>";
    $isAuth = $auth->check();
    echo "✓ check() ejecutado. Resultado: " . ($isAuth ? 'autenticado' : 'no autenticado') . "<br>";

    echo "<br>✅ Todo funciona. El problema debe ser específico de blog.php";

} catch (Exception $e) {
    echo "<br>❌ ERROR: " . $e->getMessage();
    echo "<br>Archivo: " . $e->getFile();
    echo "<br>Línea: " . $e->getLine();
    echo "<br>Trace: <pre>" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<br>❌ ERROR FATAL: " . $e->getMessage();
    echo "<br>Archivo: " . $e->getFile();
    echo "<br>Línea: " . $e->getLine();
    echo "<br>Trace: <pre>" . $e->getTraceAsString() . "</pre>";
}
