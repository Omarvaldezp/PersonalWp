<?php
/**
 * API Controller para Login
 *
 * POST /api/auth/login.php - Login
 * POST /api/auth/login.php?action=logout - Logout
 * GET  /api/auth/login.php?action=me - Obtener usuario actual
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/Auth.php';

try {
    $auth = new Auth();
    $method = $_SERVER['REQUEST_METHOD'];

    // POST - Login o Logout
    if ($method === 'POST') {
        $action = $_GET['action'] ?? 'login';

        if ($action === 'logout') {
            $auth->logout();
            Response::success(null, 'Sesión cerrada exitosamente');
        }

        // Login
        $data = Response::getRequestData();

        // Validar campos
        if (empty($data['username']) || empty($data['password'])) {
            Response::error('Username y password son requeridos', 400);
        }

        $user = $auth->login($data['username'], $data['password']);

        if (!$user) {
            Response::error('Credenciales inválidas', 401);
        }

        Response::success($user, 'Login exitoso');
    }

    // GET - Verificar sesión actual
    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'me';

        if ($action === 'me') {
            if (!$auth->check()) {
                Response::unauthorized('No autenticado');
            }

            $user = $auth->user();
            Response::success($user);
        }

        if ($action === 'check') {
            Response::success(['authenticated' => $auth->check()]);
        }
    }

} catch (Exception $e) {
    Response::serverError($e->getMessage());
}
