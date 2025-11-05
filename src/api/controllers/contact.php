<?php
/**
 * API Controller para Formulario de Contacto
 *
 * POST /api/contact.php - Enviar mensaje de contacto
 * GET  /api/contact.php - Listar contactos (admin only)
 * PUT  /api/contact.php?id=123&action=mark_read - Marcar como leído
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../config/Database.php';

try {
    $db = Database::getInstance();
    $method = $_SERVER['REQUEST_METHOD'];

    // POST - Recibir mensaje de contacto
    if ($method === 'POST') {
        $data = Response::getRequestData();

        // Validar campos requeridos
        $required = ['nombre', 'email', 'mensaje'];
        $errors = Response::validateRequired($data, $required);

        if ($errors) {
            Response::validationError($errors);
        }

        // Validar email
        if (!Response::validateEmail($data['email'])) {
            Response::error('Email inválido', 400);
        }

        // Sanitizar datos
        $nombre = Response::sanitize($data['nombre']);
        $email = Response::sanitize($data['email']);
        $telefono = Response::sanitize($data['telefono'] ?? '');
        $empresa = Response::sanitize($data['empresa'] ?? '');
        $asunto = Response::sanitize($data['asunto'] ?? '');
        $mensaje = Response::sanitize($data['mensaje']);
        $tipo = Response::sanitize($data['tipo'] ?? 'otro');

        // Guardar en base de datos
        $sql = "INSERT INTO contactos (
                    nombre, email, telefono, empresa, asunto, mensaje, tipo,
                    ip_address, user_agent
                ) VALUES (
                    :nombre, :email, :telefono, :empresa, :asunto, :mensaje, :tipo,
                    :ip, :user_agent
                ) RETURNING id";

        $params = [
            ':nombre' => $nombre,
            ':email' => $email,
            ':telefono' => $telefono,
            ':empresa' => $empresa,
            ':asunto' => $asunto,
            ':mensaje' => $mensaje,
            ':tipo' => $tipo,
            ':ip' => Response::getClientIP(),
            ':user_agent' => Response::getUserAgent(),
        ];

        $result = $db->selectOne($sql, $params);

        // TODO: Enviar notificación por email al administrador

        Response::success(
            ['id' => $result['id']],
            'Mensaje enviado correctamente. Nos pondremos en contacto pronto.',
            201
        );
    }

    // GET - Listar contactos (solo admin)
    if ($method === 'GET') {
        // TODO: Verificar autenticación admin

        $estado = $_GET['estado'] ?? null;
        $tipo = $_GET['tipo'] ?? null;
        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 20);
        $offset = ($page - 1) * $perPage;

        $whereClauses = [];
        $params = [];

        if ($estado) {
            $whereClauses[] = "estado = :estado";
            $params[':estado'] = $estado;
        }

        if ($tipo) {
            $whereClauses[] = "tipo = :tipo";
            $params[':tipo'] = $tipo;
        }

        $whereSQL = empty($whereClauses) ? '' : 'WHERE ' . implode(' AND ', $whereClauses);

        $sql = "SELECT
                    id, nombre, email, telefono, empresa, asunto, mensaje,
                    tipo, estado, created_at
                FROM contactos
                $whereSQL
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";

        $params[':limit'] = $perPage;
        $params[':offset'] = $offset;

        $contactos = $db->select($sql, $params);

        // Total para paginación
        $countSQL = "SELECT COUNT(*) as total FROM contactos $whereSQL";
        $countParams = array_diff_key($params, [':limit' => '', ':offset' => '']);
        $totalResult = $db->selectOne($countSQL, $countParams);

        Response::paginated(
            $contactos,
            $totalResult['total'],
            $page,
            $perPage
        );
    }

    // PUT - Actualizar estado
    if ($method === 'PUT') {
        if (!isset($_GET['id']) || !isset($_GET['action'])) {
            Response::error('ID y action son requeridos', 400);
        }

        $id = (int)$_GET['id'];
        $action = $_GET['action'];

        if ($action === 'mark_read') {
            $sql = "UPDATE contactos SET estado = 'leido' WHERE id = :id";
            $db->update($sql, [':id' => $id]);
            Response::success(null, 'Contacto marcado como leído');
        }

        if ($action === 'mark_responded') {
            $sql = "UPDATE contactos SET
                        estado = 'respondido',
                        fecha_respuesta = CURRENT_TIMESTAMP
                    WHERE id = :id";
            $db->update($sql, [':id' => $id]);
            Response::success(null, 'Contacto marcado como respondido');
        }

        Response::error('Acción no reconocida', 400);
    }

} catch (Exception $e) {
    Response::serverError($e->getMessage());
}
