<?php
/**
 * API Controller: Inscripciones
 *
 * GET    /api/controllers/inscripciones.php?curso_instancia_id=X - List enrollments by course
 * GET    /api/controllers/inscripciones.php?curso_instancia_id=X&stats=true - Get stats
 * POST   /api/controllers/inscripciones.php - Create enrollment (single or batch)
 * PUT    /api/controllers/inscripciones.php?id=X - Update enrollment
 * DELETE /api/controllers/inscripciones.php?id=X - Delete enrollment
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../models/Inscripcion.php';
require_once __DIR__ . '/../auth/guard.php';

api_require_auth();

try {
    $db = Database::getInstance();
    $model = new Inscripcion($db);
    $method = $_SERVER['REQUEST_METHOD'];

    // GET
    if ($method === 'GET') {
        // Estadísticas por curso
        if (isset($_GET['curso_instancia_id']) && isset($_GET['stats'])) {
            $stats = $model->getStatsByCurso($_GET['curso_instancia_id']);
            Response::success($stats);
        }

        // Inscripciones por curso
        if (isset($_GET['curso_instancia_id'])) {
            $inscripciones = $model->getByCurso($_GET['curso_instancia_id']);
            Response::success($inscripciones);
        }

        // Inscripción específica
        if (isset($_GET['id'])) {
            $inscripcion = $model->getById($_GET['id']);

            if (!$inscripcion) {
                Response::error('Inscripción no encontrada', 404);
            }

            Response::success($inscripcion);
        }

        Response::error('Parámetros insuficientes', 400);
    }

    // POST - Crear inscripción(es)
    if ($method === 'POST') {
        $data = Response::getRequestData();

        // Validar curso_instancia_id
        if (empty($data['curso_instancia_id'])) {
            Response::error('El curso_instancia_id es requerido', 400);
        }

        // Inscripción por lotes (múltiples estudiantes)
        if (isset($data['estudiante_ids']) && is_array($data['estudiante_ids'])) {
            $result = $model->createBatch($data['estudiante_ids'], $data['curso_instancia_id']);
            Response::success($result, "Inscripciones procesadas: {$result['inscritos']} exitosas");
        }

        // Inscripción individual
        if (empty($data['estudiante_id'])) {
            Response::error('El estudiante_id es requerido', 400);
        }

        $id = $model->create($data['estudiante_id'], $data['curso_instancia_id']);
        Response::success(['id' => $id], 'Estudiante inscrito exitosamente', 201);
    }

    // PUT - Actualizar inscripción
    if ($method === 'PUT') {
        if (!isset($_GET['id'])) {
            Response::error('ID de inscripción requerido', 400);
        }

        $data = Response::getRequestData();

        // Actualizar estado
        if (isset($data['estado'])) {
            $model->updateEstado($_GET['id'], $data['estado']);
        }

        // Actualizar calificación
        if (isset($data['calificacion_final'])) {
            $aprobado = isset($data['aprobado']) ? $data['aprobado'] : ($data['calificacion_final'] >= 6);
            $model->updateCalificacion($_GET['id'], $data['calificacion_final'], $aprobado);
        }

        Response::success(['id' => $_GET['id']], 'Inscripción actualizada exitosamente');
    }

    // DELETE - Eliminar inscripción
    if ($method === 'DELETE') {
        if (!isset($_GET['id'])) {
            Response::error('ID de inscripción requerido', 400);
        }

        $model->delete($_GET['id']);
        Response::success(null, 'Inscripción eliminada exitosamente');
    }

} catch (Exception $e) {
    error_log("Inscripciones API error: " . $e->getMessage());
    Response::serverError();
}
