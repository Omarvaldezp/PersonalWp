<?php
/**
 * API Controller para Investigaciones
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../models/Research.php';

try {
    $research = new Research();
    $method = $_SERVER['REQUEST_METHOD'];

    // GET
    if ($method === 'GET') {
        // Búsqueda
        if (isset($_GET['action']) && $_GET['action'] === 'search') {
            $query = $_GET['q'] ?? '';
            if (empty($query)) {
                Response::error('El parámetro q es requerido', 400);
            }
            $results = $research->search($query);
            Response::success($results);
        }

        // Categorías
        if (isset($_GET['action']) && $_GET['action'] === 'categories') {
            $categories = $research->getCategories();
            Response::success($categories);
        }

        // Por ID
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $inv = $research->getById($id);
            if (!$inv) Response::notFound('Investigación no encontrada');
            Response::success($inv);
        }

        // Por slug
        if (isset($_GET['slug'])) {
            $slug = $_GET['slug'];
            $inv = $research->getBySlug($slug);
            if (!$inv) Response::notFound('Investigación no encontrada');
            Response::success($inv);
        }

        // Listar con filtros
        $options = [
            'tipo' => $_GET['tipo'] ?? null,
            'categoria' => $_GET['categoria'] ?? null,
            'ano' => isset($_GET['ano']) ? (int)$_GET['ano'] : null,
            'destacado' => isset($_GET['destacado']) ? filter_var($_GET['destacado'], FILTER_VALIDATE_BOOLEAN) : null,
            'order_by' => $_GET['order_by'] ?? 'fecha_publicacion',
            'order_dir' => strtoupper($_GET['order_dir'] ?? 'DESC'),
        ];

        $investigaciones = $research->getAll($options);
        Response::success($investigaciones);
    }

    // POST - Crear
    if ($method === 'POST') {
        $data = Response::getRequestData();
        $required = ['titulo', 'autores'];
        $errors = Response::validateRequired($data, $required);
        if ($errors) Response::validationError($errors);

        if (empty($data['slug'])) {
            $data['slug'] = Response::generateSlug($data['titulo']);
        }

        $id = $research->create($data);
        Response::success(['id' => $id], 'Investigación creada exitosamente', 201);
    }

    // PUT - Actualizar
    if ($method === 'PUT') {
        if (!isset($_GET['id'])) {
            Response::error('ID es requerido', 400);
        }
        $id = (int)$_GET['id'];
        $data = Response::getRequestData();

        $existing = $research->getById($id);
        if (!$existing) Response::notFound('Investigación no encontrada');

        $research->update($id, $data);
        Response::success(null, 'Investigación actualizada exitosamente');
    }

    // DELETE
    if ($method === 'DELETE') {
        if (!isset($_GET['id'])) {
            Response::error('ID es requerido', 400);
        }
        $id = (int)$_GET['id'];

        $existing = $research->getById($id);
        if (!$existing) Response::notFound('Investigación no encontrada');

        $research->delete($id);
        Response::success(null, 'Investigación eliminada exitosamente');
    }

} catch (Exception $e) {
    Response::serverError($e->getMessage());
}
