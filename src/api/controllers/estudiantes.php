<?php
/**
 * API Controller: Estudiantes
 *
 * GET    /api/controllers/estudiantes.php - List students
 * GET    /api/controllers/estudiantes.php?id=X - Get student detail
 * POST   /api/controllers/estudiantes.php - Create student
 * PUT    /api/controllers/estudiantes.php?id=X - Update student
 * DELETE /api/controllers/estudiantes.php?id=X - Delete student
 * GET    /api/controllers/estudiantes.php?stats=true - Get stats
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../models/Estudiante.php';

try {
    $db = Database::getInstance();
    $model = new Estudiante($db);
    $method = $_SERVER['REQUEST_METHOD'];

    // GET - Obtener estudiantes o estadísticas
    if ($method === 'GET') {
        // Estadísticas
        if (isset($_GET['stats'])) {
            $stats = $model->getStats();
            Response::success($stats);
        }

        // Estudiante específico
        if (isset($_GET['id'])) {
            $estudiante = $model->getById($_GET['id']);

            if (!$estudiante) {
                Response::error('Estudiante no encontrado', 404);
            }

            Response::success($estudiante);
        }

        // Lista de estudiantes
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 20;

        $filters = [];
        if (isset($_GET['search'])) $filters['search'] = $_GET['search'];
        if (isset($_GET['grado'])) $filters['grado'] = $_GET['grado'];
        if (isset($_GET['curso_instancia_id'])) $filters['curso_instancia_id'] = $_GET['curso_instancia_id'];

        $result = $model->getAll($filters, $page, $limit);
        Response::success($result);
    }

    // POST - Crear estudiante
    if ($method === 'POST') {
        $data = Response::getRequestData();

        // Validar campos requeridos
        if (empty($data['nombre'])) {
            Response::error('El nombre es requerido', 400);
        }
        if (empty($data['correo'])) {
            Response::error('El correo es requerido', 400);
        }
        if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
            Response::error('Email inválido', 400);
        }

        $id = $model->create($data);
        Response::success(['id' => $id], 'Estudiante creado exitosamente', 201);
    }

    // PUT - Actualizar estudiante
    if ($method === 'PUT') {
        if (!isset($_GET['id'])) {
            Response::error('ID de estudiante requerido', 400);
        }

        $data = Response::getRequestData();

        // Validar campos requeridos
        if (empty($data['nombre'])) {
            Response::error('El nombre es requerido', 400);
        }
        if (empty($data['correo'])) {
            Response::error('El correo es requerido', 400);
        }
        if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
            Response::error('Email inválido', 400);
        }

        $model->update($_GET['id'], $data);
        Response::success(['id' => $_GET['id']], 'Estudiante actualizado exitosamente');
    }

    // DELETE - Eliminar estudiante (soft delete)
    if ($method === 'DELETE') {
        if (!isset($_GET['id'])) {
            Response::error('ID de estudiante requerido', 400);
        }

        $model->delete($_GET['id']);
        Response::success(null, 'Estudiante eliminado exitosamente');
    }

} catch (Exception $e) {
    error_log("Estudiantes API error: " . $e->getMessage());
    Response::serverError($e->getMessage());
}
