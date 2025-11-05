<?php
/**
 * API Controller para Blog Posts
 *
 * Endpoints:
 * GET    /api/blog.php              - Listar posts (con paginación y filtros)
 * GET    /api/blog.php?id=123       - Obtener post por ID
 * GET    /api/blog.php?slug=titulo  - Obtener post por slug
 * POST   /api/blog.php              - Crear nuevo post (requiere auth)
 * PUT    /api/blog.php?id=123       - Actualizar post (requiere auth)
 * DELETE /api/blog.php?id=123       - Eliminar post (requiere auth)
 * GET    /api/blog.php?action=categories - Obtener categorías
 * GET    /api/blog.php?action=search&q=blockchain - Búsqueda full-text
 */

// CORS y configuración
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../models/Blog.php';

// Manejo de errores
try {
    $blog = new Blog();
    $method = $_SERVER['REQUEST_METHOD'];

    // GET - Listar, obtener por ID, slug, o acciones especiales
    if ($method === 'GET') {
        // Acción de búsqueda
        if (isset($_GET['action']) && $_GET['action'] === 'search') {
            $query = $_GET['q'] ?? '';

            if (empty($query)) {
                Response::error('El parámetro q (query) es requerido para búsqueda', 400);
            }

            $results = $blog->search($query);
            Response::success($results);
        }

        // Acción de obtener categorías
        if (isset($_GET['action']) && $_GET['action'] === 'categories') {
            $categories = $blog->getCategories();
            Response::success($categories);
        }

        // Acción de posts relacionados
        if (isset($_GET['action']) && $_GET['action'] === 'related' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $limit = (int)($_GET['limit'] ?? 3);
            $related = $blog->getRelated($id, $limit);
            Response::success($related);
        }

        // Obtener por ID
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $post = $blog->getById($id);

            if (!$post) {
                Response::notFound('Post no encontrado');
            }

            Response::success($post);
        }

        // Obtener por slug
        if (isset($_GET['slug'])) {
            $slug = $_GET['slug'];
            $post = $blog->getBySlug($slug);

            if (!$post) {
                Response::notFound('Post no encontrado');
            }

            Response::success($post);
        }

        // Listar posts con paginación y filtros
        $options = [
            'page' => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 12),
            'categoria' => $_GET['categoria'] ?? null,
            'estado' => $_GET['estado'] ?? 'publicado',
            'search' => $_GET['search'] ?? null,
            'order_by' => $_GET['order_by'] ?? 'fecha_publicacion',
            'order_dir' => strtoupper($_GET['order_dir'] ?? 'DESC'),
        ];

        $result = $blog->getAll($options);
        Response::paginated(
            $result['posts'],
            $result['total'],
            $result['page'],
            $result['per_page']
        );
    }

    // POST - Crear nuevo post (requiere autenticación)
    if ($method === 'POST') {
        // TODO: Verificar autenticación
        // Por ahora, simulamos que el usuario admin tiene ID 1
        $data = Response::getRequestData();

        // Validar campos requeridos
        $required = ['titulo', 'contenido'];
        $errors = Response::validateRequired($data, $required);

        if ($errors) {
            Response::validationError($errors);
        }

        // Generar slug si no se proporciona
        if (empty($data['slug'])) {
            $data['slug'] = Response::generateSlug($data['titulo']);
        }

        // Verificar que el slug sea único
        $existingPost = $blog->getBySlug($data['slug']);
        if ($existingPost) {
            $data['slug'] = $data['slug'] . '-' . time();
        }

        // Establecer autor (provisional)
        $data['autor_id'] = 1; // TODO: Obtener de la sesión autenticada

        // Fecha de publicación automática si se publica
        if ($data['estado'] === 'publicado' && empty($data['fecha_publicacion'])) {
            $data['fecha_publicacion'] = date('Y-m-d H:i:s');
        }

        $id = $blog->create($data);

        Response::success(
            ['id' => $id],
            'Post creado exitosamente',
            201
        );
    }

    // PUT - Actualizar post
    if ($method === 'PUT') {
        if (!isset($_GET['id'])) {
            Response::error('ID del post es requerido', 400);
        }

        $id = (int)$_GET['id'];
        $data = Response::getRequestData();

        // Validar campos requeridos
        $required = ['titulo', 'contenido'];
        $errors = Response::validateRequired($data, $required);

        if ($errors) {
            Response::validationError($errors);
        }

        // Verificar que el post existe
        $existingPost = $blog->getById($id);
        if (!$existingPost) {
            Response::notFound('Post no encontrado');
        }

        // Si cambió el slug, verificar que sea único
        if ($data['slug'] !== $existingPost['slug']) {
            $slugCheck = $blog->getBySlug($data['slug']);
            if ($slugCheck && $slugCheck['id'] !== $id) {
                Response::error('El slug ya está en uso', 400);
            }
        }

        $blog->update($id, $data);

        Response::success(null, 'Post actualizado exitosamente');
    }

    // DELETE - Eliminar post
    if ($method === 'DELETE') {
        if (!isset($_GET['id'])) {
            Response::error('ID del post es requerido', 400);
        }

        $id = (int)$_GET['id'];

        // Verificar que el post existe
        $post = $blog->getById($id);
        if (!$post) {
            Response::notFound('Post no encontrado');
        }

        $blog->delete($id);

        Response::success(null, 'Post eliminado exitosamente');
    }

    // PATCH - Acciones parciales (incrementar likes)
    if ($method === 'PATCH') {
        if (!isset($_GET['id']) || !isset($_GET['action'])) {
            Response::error('ID y action son requeridos', 400);
        }

        $id = (int)$_GET['id'];
        $action = $_GET['action'];

        if ($action === 'like') {
            $blog->incrementLikes($id);
            Response::success(null, 'Like registrado');
        }

        Response::error('Acción no reconocida', 400);
    }

} catch (Exception $e) {
    Response::serverError($e->getMessage());
}
