<?php
/**
 * Página de Login del Admin
 */

require_once __DIR__ . '/../api/auth/Auth.php';

$auth = new Auth();

// Si ya está autenticado, redirigir al dashboard
if ($auth->check()) {
    header('Location: index.php');
    exit;
}

$error = '';

// Procesar login si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $user = $auth->login($username, $password);

    if ($user) {
        header('Location: index.php');
        exit;
    } else {
        $error = 'Credenciales inválidas';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin Panel</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Panel de Administración</h1>
                <p>Dr. Omar Valdez Palazuelos</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Usuario o Email</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        required
                        autofocus
                        placeholder="Ingresa tu usuario o email"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        placeholder="Ingresa tu contraseña"
                    >
                </div>

                <div style="text-align: right; margin-bottom: 15px;">
                    <a href="forgot-password.php" style="font-size: 14px; color: #2563eb;">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <button type="submit" class="btn-primary">
                    Iniciar Sesión
                </button>
            </form>

<?php /*
    Aquí se anunciaban las credenciales por defecto en una página pública.
    Nunca vuelvas a poner credenciales, usuarios de ejemplo ni pistas de
    acceso en esta pantalla: los comentarios HTML también se sirven al
    navegador, así que esta nota va en un comentario de PHP.
*/ ?>
        </div>
    </div>
</body>
</html>
