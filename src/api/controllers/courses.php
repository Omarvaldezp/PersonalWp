<?php
/**
 * API Controller para Cursos
 *
 * Endpoints:
 * GET    /api/courses.php                    - Listar cursos
 * GET    /api/courses.php?id=123             - Obtener curso por ID
 * GET    /api/courses.php?slug=titulo        - Obtener curso por slug
 * GET    /api/courses.php?action=featured    - Obtener cursos destacados
 * GET    /api/courses.php?action=categories  - Obtener categorías
 * POST   /api/courses.php                    - Crear curso (auth)
 * PUT    /api/courses.php?id=123             - Actualizar curso (auth)
 * DELETE /api/courses.php?id=123             - Eliminar curso (auth)
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../models/Course.php';

try {
    $course = new Course();
    $method = $_SERVER['REQUEST_METHOD'];

    // GET
    if ($method === 'GET') {
        // Obtener categorías
        if (isset($_GET['action']) && $_GET['action'] === 'categories') {
            $categories = $course->getCategories();
            Response::success($categories);
        }

        // Obtener cursos destacados
        if (isset($_GET['action']) && $_GET['action'] === 'featured') {
            $limit = (int)($_GET['limit'] ?? 5);
            $featured = $course->getFeatured($limit);
            Response::success($featured);
        }

        // Obtener por ID
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $curso = $course->getById($id);

            if (!$curso) {
                Response::notFound('Curso no encontrado');
            }

            Response::success($curso);
        }

        // Obtener por slug
        if (isset($_GET['slug'])) {
            $slug = $_GET['slug'];
            $curso = $course->getBySlug($slug);

            if (!$curso) {
                Response::notFound('Curso no encontrado');
            }

            Response::success($curso);
        }

        // Listar cursos con filtros
        $options = [
            'nivel' => $_GET['nivel'] ?? null,
            'modalidad' => $_GET['modalidad'] ?? null,
            'categoria' => $_GET['categoria'] ?? null,
            'destacado' => isset($_GET['destacado']) ? filter_var($_GET['destacado'], FILTER_VALIDATE_BOOLEAN) : null,
            'activo' => isset($_GET['activo']) ? filter_var($_GET['activo'], FILTER_VALIDATE_BOOLEAN) : true,
            'order_by' => $_GET['order_by'] ?? 'created_at',
            'order_dir' => strtoupper($_GET['order_dir'] ?? 'DESC'),
        ];

        $cursos = $course->getAll($options);
        Response::success($cursos);
    }

    // POST - Crear curso
    if ($method === 'POST') {
        $data = Response::getRequestData();

        $required = ['titulo', 'descripcion_corta'];
        $errors = Response::validateRequired($data, $required);

        if ($errors) {
            Response::validationError($errors);
        }

        // Generar slug
        if (empty($data['slug'])) {
            $data['slug'] = Response::generateSlug($data['titulo']);
        }

        $id = $course->create($data);

        Response::success(['id' => $id], 'Curso creado exitosamente', 201);
    }

    // PUT - Actualizar curso
    if ($method === 'PUT') {
        if (!isset($_GET['id'])) {
            Response::error('ID del curso es requerido', 400);
        }

        $id = (int)$_GET['id'];
        $data = Response::getRequestData();

        $required = ['titulo', 'descripcion_corta'];
        $errors = Response::validateRequired($data, $required);

        if ($errors) {
            Response::validationError($errors);
        }

        $existing = $course->getById($id);
        if (!$existing) {
            Response::notFound('Curso no encontrado');
        }

        $course->update($id, $data);

        Response::success(null, 'Curso actualizado exitosamente');
    }

    // DELETE - Eliminar curso
    if ($method === 'DELETE') {
        if (!isset($_GET['id'])) {
            Response::error('ID del curso es requerido', 400);
        }

        $id = (int)$_GET['id'];

        $existing = $course->getById($id);
        if (!$existing) {
            Response::notFound('Curso no encontrado');
        }

        $course->delete($id);

        Response::success(null, 'Curso eliminado exitosamente');
    }

} catch (Exception $e) {
    Response::serverError($e->getMessage());
}
