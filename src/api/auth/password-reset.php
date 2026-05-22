<?php
/**
 * API para recuperación de contraseña
 *
 * POST /api/auth/password-reset.php - Solicitar reset (genera token y envía email)
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../utils/Response.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        Response::error('Método no permitido', 405);
    }

    $data = Response::getRequestData();
    $email = trim($data['email'] ?? '');

    // Validar email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        Response::error('Correo electrónico inválido', 400);
    }

    $db = Database::getInstance();

    // Buscar usuario por email
    $sql = "SELECT id, username, email, nombre_completo
            FROM usuarios
            WHERE email = :email
            AND activo = true";

    $user = $db->selectOne($sql, [':email' => $email]);

    // Por seguridad, SIEMPRE respondemos éxito (aunque el email no exista)
    // Esto evita que atacantes puedan enumerar usuarios válidos
    if (!$user) {
        // Esperar un poco para simular procesamiento
        usleep(500000); // 0.5 segundos
        Response::success(null, 'Si el correo existe, recibirás instrucciones');
    }

    // Generar token seguro (64 caracteres hex)
    $token = bin2hex(random_bytes(32));

    // Token expira en 1 hora
    $expiresAt = date('Y-m-d H:i:s', time() + 3600);

    // Guardar token en DB
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

    $message = "Hola " . ($user['nombre_completo'] ?: $user['username']) . ",\n\n";
    $message .= "Recibimos una solicitud para restablecer la contraseña de tu cuenta.\n\n";
    $message .= "Haz clic en el siguiente enlace para crear una nueva contraseña:\n";
    $message .= $resetLink . "\n\n";
    $message .= "Este enlace expira en 1 hora.\n\n";
    $message .= "Si no solicitaste este cambio, puedes ignorar este correo.\n\n";
    $message .= "---\n";
    $message .= "Panel de Administración\n";
    $message .= "Dr. Omar Valdez Palazuelos\n";

    $headers = [
        'From: noreply@omarvaldez.com',
        'Reply-To: noreply@omarvaldez.com',
        'X-Mailer: PHP/' . phpversion(),
        'Content-Type: text/plain; charset=UTF-8'
    ];

    // Enviar email
    $emailSent = mail($to, $subject, $message, implode("\r\n", $headers));

    // Log el resultado (en producción, usar un logger apropiado)
    if (!$emailSent) {
        error_log("Failed to send password reset email to: $email");
    }

    // SIEMPRE responder éxito por seguridad
    Response::success(null, 'Si el correo existe, recibirás instrucciones');

} catch (Exception $e) {
    error_log("Password reset error: " . $e->getMessage());
    // Por seguridad, no revelar detalles del error
    Response::success(null, 'Si el correo existe, recibirás instrucciones');
}
