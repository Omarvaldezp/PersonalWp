<?php
/**
 * Página de reset de contraseña (con token)
 */

require_once __DIR__ . '/../api/auth/Auth.php';
require_once __DIR__ . '/../api/config/Database.php';

$auth = new Auth();

// Si ya está autenticado, redirigir al dashboard
if ($auth->check()) {
    header('Location: index.php');
    exit;
}

$token = $_GET['token'] ?? '';
$error = '';
$message = '';
$tokenValid = false;

// Validar token
if (!empty($token)) {
    try {
        $db = Database::getInstance();
        $sql = "SELECT id, username, email, reset_token_expires
                FROM usuarios
                WHERE reset_token = :token
                AND reset_token_expires > CURRENT_TIMESTAMP
                AND activo = true";

        $user = $db->selectOne($sql, [':token' => $token]);

        if ($user) {
            $tokenValid = true;
        } else {
            $error = 'El enlace de recuperación es inválido o ha expirado. Solicita uno nuevo.';
        }
    } catch (Exception $e) {
        $error = 'Error al validar el token. Intenta nuevamente.';
    }
}

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid) {
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($newPassword)) {
        $error = 'La contraseña no puede estar vacía';
    } elseif (strlen($newPassword) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Las contraseñas no coinciden';
    } else {
        try {
            $db = Database::getInstance();
            $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);

            $sql = "UPDATE usuarios
                    SET password_hash = :hash,
                        reset_token = NULL,
                        reset_token_expires = NULL
                    WHERE reset_token = :token";

            $db->update($sql, [
                ':hash' => $passwordHash,
                ':token' => $token
            ]);

            $message = '¡Contraseña actualizada exitosamente! Redirigiendo al login...';

            // Redirigir después de 2 segundos
            header('Refresh: 2; url=login.php');
        } catch (Exception $e) {
            $error = 'Error al actualizar la contraseña. Intenta nuevamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Admin Panel</title>
    <link rel="stylesheet" href="assets/admin.css">
    <style>
        .password-requirements {
            background: #e0f2fe;
            border-left: 4px solid #0284c7;
            padding: 12px;
            margin: 15px 0;
            font-size: 14px;
        }
        .password-requirements ul {
            margin: 5px 0 0 20px;
            padding: 0;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Restablecer Contraseña</h1>
                <p>Ingresa tu nueva contraseña</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($tokenValid && !$message): ?>
                <div class="password-requirements">
                    <strong>Requisitos de contraseña:</strong>
                    <ul>
                        <li>Mínimo 8 caracteres</li>
                        <li>Se recomienda usar mayúsculas, minúsculas y números</li>
                    </ul>
                </div>

                <form method="POST" class="login-form">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <div class="form-group">
                        <label for="password">Nueva Contraseña</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autofocus
                            minlength="8"
                            placeholder="Mínimo 8 caracteres"
                        >
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmar Contraseña</label>
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            required
                            minlength="8"
                            placeholder="Repite la contraseña"
                        >
                    </div>

                    <button type="submit" class="btn-primary">
                        Actualizar Contraseña
                    </button>
                </form>
            <?php elseif (!$tokenValid && empty($token)): ?>
                <div class="alert alert-error">
                    No se proporcionó un token válido. Por favor usa el enlace del correo.
                </div>
            <?php endif; ?>

            <div class="login-footer">
                <p>
                    <a href="login.php">← Volver al login</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
