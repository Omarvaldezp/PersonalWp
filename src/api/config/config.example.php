<?php
/**
 * Configuración de ejemplo para la base de datos PostgreSQL
 *
 * INSTRUCCIONES:
 * 1. Copia este archivo a config.php
 * 2. Actualiza los valores con tus credenciales de SiteGround
 * 3. NUNCA subas config.php a GitHub (está en .gitignore)
 */

// ============================================
// CONFIGURACIÓN DE BASE DE DATOS POSTGRESQL
// ============================================

// Obtener credenciales de PostgreSQL en SiteGround:
// 1. Ir a Site Tools > PostgreSQL > Databases
// 2. Crear una nueva base de datos o usar una existente
// 3. Anotar: hostname, database name, username, password

define('DB_HOST', 'localhost');              // Usualmente 'localhost' en SiteGround
define('DB_PORT', '5432');                   // Puerto por defecto de PostgreSQL
define('DB_NAME', 'tu_nombre_de_base_de_datos'); // Nombre de tu base de datos
define('DB_USER', 'tu_usuario_postgresql');  // Usuario de PostgreSQL
define('DB_PASSWORD', 'tu_password_aqui');   // Password de PostgreSQL
define('DB_CHARSET', 'utf8');

// ============================================
// CONFIGURACIÓN DE LA APLICACIÓN
// ============================================

define('APP_ENV', 'production');  // development | production
define('APP_DEBUG', false);       // true en desarrollo, false en producción
define('APP_URL', 'https://omarvaldez.com');

// ============================================
// CONFIGURACIÓN DE SEGURIDAD
// ============================================

// Genera una clave secreta única con: openssl rand -base64 32
define('JWT_SECRET_KEY', 'CAMBIA_ESTO_POR_UNA_CLAVE_SECRETA_UNICA_GENERADA');
define('SESSION_LIFETIME', 86400); // 24 horas en segundos
define('PASSWORD_MIN_LENGTH', 8);

// ============================================
// CONFIGURACIÓN DE API
// ============================================

define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 100);  // Requests por minuto
define('CORS_ALLOWED_ORIGINS', '*'); // En producción, especifica tu dominio

// ============================================
// CONFIGURACIÓN DE UPLOADS
// ============================================

define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB en bytes
define('UPLOAD_ALLOWED_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);
define('UPLOAD_PATH', __DIR__ . '/../../uploads/');

// ============================================
// CONFIGURACIÓN DE EMAIL
// ============================================

define('MAIL_FROM_ADDRESS', 'noreply@omarvaldez.com');
define('MAIL_FROM_NAME', 'Dr. Omar Valdez');
define('MAIL_ADMIN_ADDRESS', 'omar@omarvaldez.com');

// ============================================
// CONFIGURACIÓN DE TIMEZONE
// ============================================

date_default_timezone_set('America/Mexico_City');

// ============================================
// ERROR REPORTING
// ============================================

if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/../../logs/php-errors.log');
}
