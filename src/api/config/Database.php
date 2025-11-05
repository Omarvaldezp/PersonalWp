<?php
/**
 * Clase Database - Manejo de conexión a PostgreSQL
 *
 * Singleton pattern para asegurar una sola conexión a la base de datos
 * Usa PDO para mejor seguridad y compatibilidad
 */

class Database {
    private static $instance = null;
    private $connection;
    private $host;
    private $port;
    private $dbname;
    private $username;
    private $password;

    /**
     * Constructor privado (Singleton pattern)
     */
    private function __construct() {
        // Cargar configuración
        $configFile = __DIR__ . '/config.php';

        if (!file_exists($configFile)) {
            throw new Exception(
                'Archivo de configuración no encontrado. ' .
                'Copia config.example.php a config.php y configura tus credenciales.'
            );
        }

        require_once $configFile;

        $this->host = DB_HOST;
        $this->port = DB_PORT;
        $this->dbname = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASSWORD;

        $this->connect();
    }

    /**
     * Prevenir clonación del objeto (Singleton)
     */
    private function __clone() {}

    /**
     * Prevenir deserialización (Singleton)
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * Obtener instancia única de la base de datos
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Establecer conexión a PostgreSQL
     */
    private function connect() {
        try {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname}";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true, // Conexiones persistentes para mejor rendimiento
            ];

            $this->connection = new PDO($dsn, $this->username, $this->password, $options);

            // Configurar esquema de búsqueda y codificación
            $this->connection->exec("SET NAMES 'UTF8'");

        } catch (PDOException $e) {
            $this->handleConnectionError($e);
        }
    }

    /**
     * Manejar errores de conexión
     */
    private function handleConnectionError($e) {
        if (defined('APP_ENV') && APP_ENV === 'development') {
            throw new Exception(
                "Error de conexión a PostgreSQL: " . $e->getMessage()
            );
        } else {
            // En producción, no revelar detalles de la conexión
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception(
                "No se pudo conectar a la base de datos. Por favor, contacta al administrador."
            );
        }
    }

    /**
     * Obtener la conexión PDO
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Ejecutar una consulta preparada
     *
     * @param string $sql SQL query
     * @param array $params Parámetros para bind
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->handleQueryError($e, $sql);
        }
    }

    /**
     * Ejecutar SELECT y retornar todas las filas
     */
    public function select($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Ejecutar SELECT y retornar una sola fila
     */
    public function selectOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Ejecutar INSERT y retornar el ID insertado
     */
    public function insert($sql, $params = []) {
        $this->query($sql, $params);
        return $this->connection->lastInsertId();
    }

    /**
     * Ejecutar UPDATE
     */
    public function update($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Ejecutar DELETE
     */
    public function delete($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Iniciar transacción
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    /**
     * Confirmar transacción
     */
    public function commit() {
        return $this->connection->commit();
    }

    /**
     * Revertir transacción
     */
    public function rollback() {
        return $this->connection->rollBack();
    }

    /**
     * Verificar si hay una transacción activa
     */
    public function inTransaction() {
        return $this->connection->inTransaction();
    }

    /**
     * Manejar errores de consulta
     */
    private function handleQueryError($e, $sql) {
        $errorMsg = "Database query error: " . $e->getMessage();

        if (defined('APP_ENV') && APP_ENV === 'development') {
            $errorMsg .= "\nSQL: " . $sql;
        }

        error_log($errorMsg);

        throw new Exception(
            APP_ENV === 'development'
                ? $errorMsg
                : "Error al ejecutar la consulta. Por favor, intenta nuevamente."
        );
    }

    /**
     * Escapar valores para LIKE queries
     */
    public function escapeLike($value) {
        return str_replace(['%', '_'], ['\\%', '\\_'], $value);
    }

    /**
     * Construir cláusula WHERE desde un array de condiciones
     *
     * @param array $conditions ['columna' => 'valor', ...]
     * @return array ['WHERE columna = :columna AND ...', [':columna' => 'valor', ...]]
     */
    public function buildWhere($conditions) {
        if (empty($conditions)) {
            return ['', []];
        }

        $whereClauses = [];
        $params = [];

        foreach ($conditions as $column => $value) {
            $paramName = ':' . str_replace('.', '_', $column);

            if (is_array($value)) {
                // Para condiciones IN
                $placeholders = [];
                $i = 0;
                foreach ($value as $v) {
                    $placeholder = $paramName . '_' . $i;
                    $placeholders[] = $placeholder;
                    $params[$placeholder] = $v;
                    $i++;
                }
                $whereClauses[] = "$column IN (" . implode(', ', $placeholders) . ")";
            } elseif ($value === null) {
                $whereClauses[] = "$column IS NULL";
            } else {
                $whereClauses[] = "$column = $paramName";
                $params[$paramName] = $value;
            }
        }

        $whereSQL = 'WHERE ' . implode(' AND ', $whereClauses);

        return [$whereSQL, $params];
    }

    /**
     * Verificar conexión
     */
    public function isConnected() {
        try {
            $this->connection->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Obtener versión de PostgreSQL
     */
    public function getVersion() {
        $result = $this->selectOne('SELECT version()');
        return $result['version'] ?? 'Unknown';
    }

    /**
     * Cerrar conexión (útil para testing)
     */
    public function close() {
        $this->connection = null;
    }

    /**
     * Destructor
     */
    public function __destruct() {
        $this->connection = null;
    }
}
