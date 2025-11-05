<?php
/**
 * Clase Response - Utilidad para respuestas JSON estandarizadas
 */

class Response {
    /**
     * Enviar respuesta JSON exitosa
     *
     * @param mixed $data Datos a enviar
     * @param string $message Mensaje opcional
     * @param int $statusCode Código HTTP
     */
    public static function success($data = null, $message = 'Success', $statusCode = 200) {
        http_response_code($statusCode);

        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }

    /**
     * Enviar respuesta JSON de error
     *
     * @param string $message Mensaje de error
     * @param int $statusCode Código HTTP
     * @param array $errors Errores detallados (opcional)
     */
    public static function error($message = 'Error', $statusCode = 400, $errors = null) {
        http_response_code($statusCode);

        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        // En desarrollo, incluir más información
        if (defined('APP_ENV') && APP_ENV === 'development') {
            $response['debug'] = [
                'file' => debug_backtrace()[0]['file'] ?? 'unknown',
                'line' => debug_backtrace()[0]['line'] ?? 0,
            ];
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }

    /**
     * Respuesta de validación fallida
     *
     * @param array $errors Array de errores de validación
     */
    public static function validationError($errors) {
        self::error('Errores de validación', 422, $errors);
    }

    /**
     * Respuesta de no autorizado
     */
    public static function unauthorized($message = 'No autorizado') {
        self::error($message, 401);
    }

    /**
     * Respuesta de prohibido
     */
    public static function forbidden($message = 'Acceso prohibido') {
        self::error($message, 403);
    }

    /**
     * Respuesta de no encontrado
     */
    public static function notFound($message = 'Recurso no encontrado') {
        self::error($message, 404);
    }

    /**
     * Respuesta de error del servidor
     */
    public static function serverError($message = 'Error interno del servidor') {
        self::error($message, 500);
    }

    /**
     * Respuesta paginada
     *
     * @param array $items Items de la página actual
     * @param int $total Total de items
     * @param int $page Página actual
     * @param int $perPage Items por página
     */
    public static function paginated($items, $total, $page, $perPage) {
        $totalPages = ceil($total / $perPage);

        self::success([
            'items' => $items,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1,
            ]
        ]);
    }

    /**
     * Obtener datos del cuerpo de la petición (JSON)
     *
     * @return array
     */
    public static function getRequestData() {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        return $data ?? [];
    }

    /**
     * Validar campos requeridos
     *
     * @param array $data Datos a validar
     * @param array $required Campos requeridos
     * @return array|null Array de errores o null si todo es válido
     */
    public static function validateRequired($data, $required) {
        $errors = [];

        foreach ($required as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $errors[$field] = "El campo $field es requerido";
            }
        }

        return empty($errors) ? null : $errors;
    }

    /**
     * Validar email
     *
     * @param string $email
     * @return bool
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Sanitizar string
     *
     * @param string $string
     * @return string
     */
    public static function sanitize($string) {
        return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitizar array de datos
     *
     * @param array $data
     * @return array
     */
    public static function sanitizeArray($data) {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = self::sanitizeArray($value);
            } else {
                $sanitized[$key] = self::sanitize($value);
            }
        }

        return $sanitized;
    }

    /**
     * Generar slug desde un string
     *
     * @param string $string
     * @return string
     */
    public static function generateSlug($string) {
        // Convertir a minúsculas
        $slug = mb_strtolower($string, 'UTF-8');

        // Reemplazar caracteres especiales
        $slug = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'],
            ['a', 'e', 'i', 'o', 'u', 'n', 'u'],
            $slug
        );

        // Remover caracteres no alfanuméricos
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        // Remover guiones al inicio y final
        $slug = trim($slug, '-');

        return $slug;
    }

    /**
     * Verificar método HTTP
     *
     * @param string $method Método esperado (GET, POST, PUT, DELETE)
     */
    public static function requireMethod($method) {
        if ($_SERVER['REQUEST_METHOD'] !== $method) {
            self::error("Método HTTP no permitido. Se esperaba $method", 405);
        }
    }

    /**
     * Obtener IP del cliente
     *
     * @return string
     */
    public static function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
    }

    /**
     * Obtener User Agent
     *
     * @return string
     */
    public static function getUserAgent() {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    }
}
