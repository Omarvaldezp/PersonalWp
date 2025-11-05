<?php
/**
 * API Controller para Newsletter
 *
 * POST /api/newsletter.php - Suscribirse al newsletter
 * GET  /api/newsletter.php - Listar suscriptores (admin only)
 * DELETE /api/newsletter.php?email=xxx - Darse de baja
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../config/Database.php';

try {
    $db = Database::getInstance();
    $method = $_SERVER['REQUEST_METHOD'];

    // POST - Suscribirse
    if ($method === 'POST') {
        $data = Response::getRequestData();

        // Validar email
        if (empty($data['email'])) {
            Response::error('El email es requerido', 400);
        }

        if (!Response::validateEmail($data['email'])) {
            Response::error('Email inválido', 400);
        }

        $email = Response::sanitize($data['email']);
        $nombre = Response::sanitize($data['nombre'] ?? '');
        $intereses = $data['intereses'] ?? [];

        // Verificar si ya está suscrito
        $checkSQL = "SELECT id, activo FROM newsletter_suscriptores WHERE email = :email";
        $existing = $db->selectOne($checkSQL, [':email' => $email]);

        if ($existing) {
            if ($existing['activo']) {
                Response::error('Este email ya está suscrito al newsletter', 400);
            } else {
                // Reactivar suscripción
                $updateSQL = "UPDATE newsletter_suscriptores SET
                                activo = true,
                                updated_at = CURRENT_TIMESTAMP
                              WHERE email = :email";
                $db->update($updateSQL, [':email' => $email]);
                Response::success(null, 'Suscripción reactivada exitosamente');
            }
        }

        // Generar token de confirmación
        $token = bin2hex(random_bytes(32));

        // Convertir intereses a array PostgreSQL
        $interesesPg = '{' . implode(',', array_map(function($i) {
            return '"' . str_replace('"', '\\"', $i) . '"';
        }, $intereses)) . '}';

        // Insertar nuevo suscriptor
        $sql = "INSERT INTO newsletter_suscriptores (
                    email, nombre, intereses, token_confirmacion, ip_suscripcion
                ) VALUES (
                    :email, :nombre, :intereses, :token, :ip
                ) RETURNING id";

        $params = [
            ':email' => $email,
            ':nombre' => $nombre,
            ':intereses' => $interesesPg,
            ':token' => $token,
            ':ip' => Response::getClientIP(),
        ];

        $result = $db->selectOne($sql, $params);

        // TODO: Enviar email de confirmación con el token

        Response::success(
            ['id' => $result['id']],
            'Suscripción exitosa. Revisa tu email para confirmar.',
            201
        );
    }

    // GET - Listar suscriptores (admin only)
    if ($method === 'GET') {
        // TODO: Verificar autenticación admin

        $activo = isset($_GET['activo']) ? filter_var($_GET['activo'], FILTER_VALIDATE_BOOLEAN) : true;
        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 50);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT
                    id, email, nombre, activo, intereses, confirmado,
                    fecha_confirmacion, created_at
                FROM newsletter_suscriptores
                WHERE activo = :activo
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";

        $params = [
            ':activo' => $activo ? 'true' : 'false',
            ':limit' => $perPage,
            ':offset' => $offset,
        ];

        $suscriptores = $db->select($sql, $params);

        // Convertir arrays PostgreSQL a PHP
        foreach ($suscriptores as &$sub) {
            $intereses = trim($sub['intereses'], '{}');
            $sub['intereses'] = empty($intereses) ? [] : explode(',', str_replace('"', '', $intereses));
        }

        // Total
        $countSQL = "SELECT COUNT(*) as total FROM newsletter_suscriptores WHERE activo = :activo";
        $totalResult = $db->selectOne($countSQL, [':activo' => $activo ? 'true' : 'false']);

        Response::paginated(
            $suscriptores,
            $totalResult['total'],
            $page,
            $perPage
        );
    }

    // DELETE - Darse de baja
    if ($method === 'DELETE') {
        if (!isset($_GET['email'])) {
            Response::error('Email es requerido', 400);
        }

        $email = $_GET['email'];

        $sql = "UPDATE newsletter_suscriptores SET
                    activo = false,
                    fecha_baja = CURRENT_TIMESTAMP,
                    razon_baja = 'Usuario se dio de baja'
                WHERE email = :email AND activo = true";

        $rowsAffected = $db->update($sql, [':email' => $email]);

        if ($rowsAffected === 0) {
            Response::error('Email no encontrado o ya está dado de baja', 404);
        }

        Response::success(null, 'Te has dado de baja del newsletter exitosamente');
    }

    // PUT - Confirmar suscripción
    if ($method === 'PUT') {
        if (!isset($_GET['token'])) {
            Response::error('Token es requerido', 400);
        }

        $token = $_GET['token'];

        $sql = "UPDATE newsletter_suscriptores SET
                    confirmado = true,
                    fecha_confirmacion = CURRENT_TIMESTAMP
                WHERE token_confirmacion = :token AND activo = true";

        $rowsAffected = $db->update($sql, [':token' => $token]);

        if ($rowsAffected === 0) {
            Response::error('Token inválido o ya confirmado', 400);
        }

        Response::success(null, 'Email confirmado exitosamente');
    }

} catch (Exception $e) {
    Response::serverError($e->getMessage());
}
