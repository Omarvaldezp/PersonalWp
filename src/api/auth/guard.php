<?php
/**
 * Guardián de la API
 *
 * Los controladores de src/api/ atendían a cualquiera. Este archivo centraliza
 * la comprobación de sesión para que cerrar un endpoint sea una sola línea.
 *
 * Uso:
 *   require_once __DIR__ . '/../auth/guard.php';
 *   api_require_auth();            // cualquier usuario con sesión
 *   api_require_admin();           // solo rol admin
 *
 * Quien consume estos endpoints es el panel (/admin/) por fetch del mismo
 * origen, así que la cookie de sesión viaja sola y no hace falta nada más.
 *
 * La única excepción pública es el POST de diagnostico-disde.php, que recibe
 * el formulario abierto de diagnosticoDISDE.php. Ese controlador guarda
 * únicamente su GET.
 */

require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/Auth.php';

/**
 * Construye el Auth una sola vez por petición.
 *
 * El guardián corre antes del try/catch del controlador, así que si la base de
 * datos no responde el fallo saldría como error fatal de PHP en vez de JSON.
 * Por eso se atrapa aquí y se responde 503 en el formato de siempre.
 */
function api_auth() {
    static $auth = null;

    if ($auth === null) {
        try {
            $auth = new Auth();
        } catch (Throwable $e) {
            error_log('API guard: no se pudo iniciar Auth: ' . $e->getMessage());
            Response::error('Servicio no disponible', 503);
        }
    }

    return $auth;
}

/**
 * Exige sesión iniciada. Corta con 401 si no la hay.
 */
function api_require_auth() {
    $auth = api_auth();
    $auth->requireAuth();
    return $auth;
}

/**
 * Exige sesión iniciada con rol admin. Corta con 401 o 403.
 */
function api_require_admin() {
    $auth = api_auth();
    $auth->requireAdmin();
    return $auth;
}
