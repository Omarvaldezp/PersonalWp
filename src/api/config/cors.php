<?php
/**
 * Configuración CORS (Cross-Origin Resource Sharing)
 * Permite que el frontend consuma las APIs
 */

// Headers para CORS
header('Access-Control-Allow-Origin: *'); // En producción, cambia * por tu dominio específico
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 3600');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
