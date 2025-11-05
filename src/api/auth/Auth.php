<?php
/**
 * Clase Auth - Manejo de autenticación y sesiones
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../utils/Response.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();

        // Iniciar sesión PHP
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Login de usuario
     *
     * @param string $username
     * @param string $password
     * @return array|false Usuario si es exitoso, false si falla
     */
    public function login($username, $password) {
        // Buscar usuario
        $sql = "SELECT id, username, email, password_hash, nombre_completo, rol, activo
                FROM usuarios
                WHERE (username = :username OR email = :username)
                AND activo = true";

        $user = $this->db->selectOne($sql, [':username' => $username]);

        if (!$user) {
            return false;
        }

        // Verificar password
        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        // Crear sesión
        $this->createSession($user);

        // Actualizar último acceso
        $updateSQL = "UPDATE usuarios SET ultimo_acceso = CURRENT_TIMESTAMP WHERE id = :id";
        $this->db->update($updateSQL, [':id' => $user['id']]);

        // No retornar el password hash
        unset($user['password_hash']);

        return $user;
    }

    /**
     * Logout
     */
    public function logout() {
        // Destruir sesión en DB si existe token
        if (isset($_SESSION['auth_token'])) {
            $sql = "DELETE FROM sesiones WHERE token = :token";
            $this->db->delete($sql, [':token' => $_SESSION['auth_token']]);
        }

        // Limpiar sesión PHP
        session_unset();
        session_destroy();

        return true;
    }

    /**
     * Verificar si el usuario está autenticado
     *
     * @return bool
     */
    public function check() {
        return isset($_SESSION['user_id']) && isset($_SESSION['auth_token']);
    }

    /**
     * Obtener usuario autenticado actual
     *
     * @return array|null
     */
    public function user() {
        if (!$this->check()) {
            return null;
        }

        $sql = "SELECT id, username, email, nombre_completo, rol
                FROM usuarios
                WHERE id = :id AND activo = true";

        return $this->db->selectOne($sql, [':id' => $_SESSION['user_id']]);
    }

    /**
     * Verificar si el usuario tiene un rol específico
     *
     * @param string $rol
     * @return bool
     */
    public function hasRole($rol) {
        $user = $this->user();
        return $user && $user['rol'] === $rol;
    }

    /**
     * Middleware para requerir autenticación
     */
    public function requireAuth() {
        if (!$this->check()) {
            Response::unauthorized('Debes iniciar sesión para acceder a este recurso');
        }
    }

    /**
     * Middleware para requerir rol admin
     */
    public function requireAdmin() {
        $this->requireAuth();

        if (!$this->hasRole('admin')) {
            Response::forbidden('No tienes permisos para acceder a este recurso');
        }
    }

    /**
     * Crear sesión para un usuario
     *
     * @param array $user
     */
    private function createSession($user) {
        // Generar token único
        $token = bin2hex(random_bytes(32));

        // Guardar en sesión PHP
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['rol'] = $user['rol'];
        $_SESSION['auth_token'] = $token;

        // Guardar en base de datos
        $sql = "INSERT INTO sesiones (usuario_id, token, ip_address, user_agent, expires_at)
                VALUES (:usuario_id, :token, :ip, :user_agent, :expires_at)";

        $params = [
            ':usuario_id' => $user['id'],
            ':token' => $token,
            ':ip' => Response::getClientIP(),
            ':user_agent' => Response::getUserAgent(),
            ':expires_at' => date('Y-m-d H:i:s', time() + (defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 86400)),
        ];

        $this->db->query($sql, $params);

        // Limpiar sesiones expiradas
        $this->cleanExpiredSessions();
    }

    /**
     * Limpiar sesiones expiradas
     */
    private function cleanExpiredSessions() {
        $sql = "DELETE FROM sesiones WHERE expires_at < CURRENT_TIMESTAMP";
        $this->db->delete($sql);
    }

    /**
     * Registrar nuevo usuario (solo admin puede crear usuarios)
     *
     * @param array $data
     * @return int ID del usuario creado
     */
    public function registerUser($data) {
        // Validar que username y email no existan
        $checkSQL = "SELECT id FROM usuarios WHERE username = :username OR email = :email";
        $existing = $this->db->selectOne($checkSQL, [
            ':username' => $data['username'],
            ':email' => $data['email']
        ]);

        if ($existing) {
            throw new Exception('El username o email ya está en uso');
        }

        // Hash del password
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);

        $sql = "INSERT INTO usuarios (
                    username, email, password_hash, nombre_completo, rol, activo
                ) VALUES (
                    :username, :email, :password_hash, :nombre_completo, :rol, :activo
                ) RETURNING id";

        $params = [
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':password_hash' => $passwordHash,
            ':nombre_completo' => $data['nombre_completo'] ?? '',
            ':rol' => $data['rol'] ?? 'editor',
            ':activo' => $data['activo'] ?? true,
        ];

        $result = $this->db->selectOne($sql, $params);
        return $result['id'];
    }

    /**
     * Cambiar password
     *
     * @param int $userId
     * @param string $newPassword
     */
    public function changePassword($userId, $newPassword) {
        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);

        $sql = "UPDATE usuarios SET password_hash = :password_hash WHERE id = :id";

        $this->db->update($sql, [
            ':password_hash' => $passwordHash,
            ':id' => $userId
        ]);

        return true;
    }

    /**
     * Verificar sesión por token (para APIs con token)
     *
     * @param string $token
     * @return array|null Usuario o null
     */
    public function verifyToken($token) {
        $sql = "SELECT u.id, u.username, u.email, u.nombre_completo, u.rol
                FROM sesiones s
                JOIN usuarios u ON s.usuario_id = u.id
                WHERE s.token = :token
                AND s.expires_at > CURRENT_TIMESTAMP
                AND u.activo = true";

        return $this->db->selectOne($sql, [':token' => $token]);
    }
}
