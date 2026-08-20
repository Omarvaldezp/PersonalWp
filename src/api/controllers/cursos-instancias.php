<?php
/**
 * API Controller: Cursos Instancias
 *
 * GET    /api/controllers/cursos-instancias.php - List course instances
 * GET    /api/controllers/cursos-instancias.php?id=X - Get instance detail
 * GET    /api/controllers/cursos-instancias.php?id=X&estudiantes=true - Get enrolled students
 * POST   /api/controllers/cursos-instancias.php - Create instance
 * PUT    /api/controllers/cursos-instancias.php?id=X - Update instance
 * DELETE /api/controllers/cursos-instancias.php?id=X - Delete instance
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../models/CursoInstancia.php';
require_once __DIR__ . '/../auth/guard.php';

api_require_auth();

try {
    $db = Database::getInstance();
    $model = new CursoInstancia($db);
    $method = $_SERVER['REQUEST_METHOD'];

    // GET
    if ($method === 'GET') {
        // Instancia específica
        if (isset($_GET['id'])) {
            $curso = $model->getById($_GET['id']);

            if (!$curso) {
                Response::error('Curso no encontrado', 404);
            }

            // Si solicitan estudiantes inscritos
            if (isset($_GET['estudiantes']) && $_GET['estudiantes'] === 'true') {
                $curso['estudiantes'] = $model->getEstudiantes($_GET['id']);
            }

            Response::success($curso);
        }

        // Períodos disponibles
        if (isset($_GET['periodos'])) {
            $periodos = $model->getPeriodos();
            Response::success($periodos);
        }

        // Lista de instancias
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 20;

        $filters = [];
        if (isset($_GET['estado'])) $filters['estado'] = $_GET['estado'];
        if (isset($_GET['periodo'])) $filters['periodo'] = $_GET['periodo'];
        if (isset($_GET['search'])) $filters['search'] = $_GET['search'];

        $result = $model->getAll($filters, $page, $limit);
        Response::success($result);
    }

    // POST - Crear instancia
    if ($method === 'POST') {
        $data = Response::getRequestData();

        // Validar campos requeridos
        $required = ['nombre', 'codigo', 'fecha_inicio', 'fecha_fin'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                Response::error("El campo '$field' es requerido", 400);
            }
        }

        $id = $model->create($data);
        Response::success(['id' => $id], 'Curso creado exitosamente', 201);
    }

    // PUT - Actualizar instancia
    if ($method === 'PUT') {
        if (!isset($_GET['id'])) {
            Response::error('ID de curso requerido', 400);
        }

        $data = Response::getRequestData();

        // Validar campos requeridos
        $required = ['nombre', 'codigo', 'fecha_inicio', 'fecha_fin'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                Response::error("El campo '$field' es requerido", 400);
            }
        }

        $model->update($_GET['id'], $data);
        Response::success(['id' => $_GET['id']], 'Curso actualizado exitosamente');
    }

    // DELETE - Eliminar instancia
    if ($method === 'DELETE') {
        if (!isset($_GET['id'])) {
            Response::error('ID de curso requerido', 400);
        }

        $model->delete($_GET['id']);
        Response::success(null, 'Curso eliminado exitosamente');
    }

} catch (Exception $e) {
    error_log("Cursos Instancias API error: " . $e->getMessage());
    Response::serverError();
}
