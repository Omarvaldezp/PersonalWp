<?php
/**
 * Página de recuperación de contraseña
 */

require_once __DIR__ . '/../api/auth/Auth.php';

$auth = new Auth();

// Si ya está autenticado, redirigir al dashboard
if ($auth->check()) {
    header('Location: index.php');
    exit;
}

$message = '';
$error = '';

// Procesar solicitud de reset
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $error = 'Por favor ingresa tu correo electrónico';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Por favor ingresa un correo válido';
    } else {
        require_once __DIR__ . '/../api/config/Database.php';

        try {
            $db = Database::getInstance();

            // Buscar usuario por email
            $sql = "SELECT id, username, email, nombre_completo
                    FROM usuarios
                    WHERE email = :email
                    AND activo = true";

            $user = $db->selectOne($sql, [':email' => $email]);

            // Por seguridad, SIEMPRE respondemos éxito
            if ($user) {
                // Generar token seguro
                $token = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hora

                // Guardar token
                $updateSql = "UPDATE usuarios
                              SET reset_token = :token,
                                  reset_token_expires = :expires
                              WHERE id = :id";

                $db->update($updateSql, [
                    ':token' => $token,
                    ':expires' => $expiresAt,
                    ':id' => $user['id']
                ]);

                // Generar enlace de reset
                $resetLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                    . '://' . $_SERVER['HTTP_HOST']
                    . '/admin/reset-password.php?token=' . $token;

                // Preparar email
                $to = $user['email'];
                $subject = 'Recuperación de contraseña - Panel Admin';

                $emailMessage = "Hola " . ($user['nombre_completo'] ?: $user['username']) . ",\n\n";
                $emailMessage .= "Recibimos una solicitud para restablecer la contraseña de tu cuenta.\n\n";
                $emailMessage .= "Haz clic en el siguiente enlace para crear una nueva contraseña:\n";
                $emailMessage .= $resetLink . "\n\n";
                $emailMessage .= "Este enlace expira en 1 hora.\n\n";
                $emailMessage .= "Si no solicitaste este cambio, puedes ignorar este correo.\n\n";
                $emailMessage .= "---\n";
                $emailMessage .= "Panel de Administración\n";
                $emailMessage .= "Dr. Omar Valdez Palazuelos\n";

                $headers = [
                    'From: noreply@omarvaldez.com',
                    'Reply-To: noreply@omarvaldez.com',
                    'X-Mailer: PHP/' . phpversion(),
                    'Content-Type: text/plain; charset=UTF-8'
                ];

                // Enviar email
                mail($to, $subject, $emailMessage, implode("\r\n", $headers));
            } else {
                // Simular procesamiento
                usleep(500000);
            }

            // SIEMPRE mostrar este mensaje (seguridad)
            $message = 'Si el correo existe en nuestro sistema, recibirás instrucciones para recuperar tu contraseña.';

        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            $message = 'Si el correo existe en nuestro sistema, recibirás instrucciones para recuperar tu contraseña.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Admin Panel</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>Recuperar Contraseña</h1>
                <p>Ingresa tu correo electrónico</p>
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

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        autofocus
                        placeholder="tu@email.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    >
                </div>

                <button type="submit" class="btn-primary">
                    Enviar Instrucciones
                </button>
            </form>

            <div class="login-footer">
                <p>
                    <a href="login.php">← Volver al login</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
