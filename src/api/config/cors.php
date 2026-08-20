<?php
/**
 * Cabeceras de la API
 *
 * Antes esto declaraba Access-Control-Allow-Origin: * , lo que permitía que
 * cualquier página de cualquier dominio leyera las respuestas desde el
 * navegador de un visitante.
 *
 * Todo lo que consume esta API vive en el mismo dominio: el panel en /admin/
 * y el formulario público de diagnóstico. Las peticiones del mismo origen no
 * necesitan CORS, así que no se emite ninguna cabecera de origen permitido.
 *
 * Si algún día hiciera falta consumir la API desde otro dominio, agrégalo a
 * API_ALLOWED_ORIGINS en config.php como arreglo de orígenes exactos. Nunca
 * vuelvas a poner el comodín.
 */

header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

$allowedOrigins = defined('API_ALLOWED_ORIGINS') ? API_ALLOWED_ORIGINS : [];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($origin !== '' && in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
    header('Vary: Origin');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
    header('Access-Control-Max-Age: 3600');
}

// Preflight: solo tiene sentido para orígenes cruzados autorizados.
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}
