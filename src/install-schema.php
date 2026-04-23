<?php
/**
 * Instalador de Schema PostgreSQL
 * Lee credenciales desde config.php (no duplica passwords)
 *
 * INSTRUCCIONES:
 * 1. Visita: https://omarvaldez.com/install-schema.php?password=admin123
 * 2. ¡BORRA este archivo después de ejecutarlo!
 */

// Seguridad básica
$SETUP_PASSWORD = 'admin123';
if (!isset($_GET['password']) || $_GET['password'] !== $SETUP_PASSWORD) {
    die('❌ Acceso denegado. Agrega ?password=admin123 a la URL');
}

// Cargar configuración
require_once __DIR__ . '/api/config/config.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Instalador de Schema</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #16a34a; background: #dcfce7; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { color: #dc2626; background: #fee2e2; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { color: #0284c7; background: #e0f2fe; padding: 15px; border-radius: 5px; margin: 10px 0; }
        button { background: #2563eb; color: white; border: none; padding: 12px 24px; border-radius: 5px; cursor: pointer; margin: 10px 5px; }
        button:hover { background: #1e40af; }
        button.secondary { background: #6b7280; }
        pre { background: #1f2937; color: #f3f4f6; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Instalación de Schema PostgreSQL</h1>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            try {
                $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
                $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);

                echo '<div class="success">✅ Conexión exitosa a PostgreSQL</div>';

                // Instalar Schema
                if ($_POST['action'] === 'install_schema') {
                    $schemaFile = __DIR__ . '/database/schema.sql';

                    if (!file_exists($schemaFile)) {
                        throw new Exception('Archivo schema.sql no encontrado en /database/');
                    }

                    $sql = file_get_contents($schemaFile);
                    $pdo->exec($sql);

                    echo '<div class="success">';
                    echo '<h3>✅ Schema instalado exitosamente</h3>';
                    echo '</div>';

                    // Verificar tablas creadas
                    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
                    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

                    echo '<div class="info"><h4>📊 Tablas creadas:</h4><ul>';
                    foreach ($tables as $table) {
                        echo "<li>✓ $table</li>";
                    }
                    echo '</ul></div>';
                }

                // Instalar Seed Data
                if ($_POST['action'] === 'install_seed') {
                    $seedFile = __DIR__ . '/database/seed_data.sql';

                    if (!file_exists($seedFile)) {
                        throw new Exception('Archivo seed_data.sql no encontrado en /database/');
                    }

                    $sql = file_get_contents($seedFile);
                    $pdo->exec($sql);

                    echo '<div class="success">';
                    echo '<h3>✅ Datos de ejemplo instalados</h3>';
                    echo '<ul>';
                    echo '<li>✓ 4 Blog posts</li>';
                    echo '<li>✓ 5 Cursos</li>';
                    echo '<li>✓ 4 Investigaciones</li>';
                    echo '</ul>';
                    echo '</div>';
                }

            } catch (PDOException $e) {
                echo '<div class="error">';
                echo '<h3>❌ Error de conexión</h3>';
                echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '</div>';
            } catch (Exception $e) {
                echo '<div class="error">';
                echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '</div>';
            }
        }
        ?>

        <div class="info">
            <h3>Configuración Actual:</h3>
            <pre>Host: <?= DB_HOST ?>

Port: <?= DB_PORT ?>

Database: <?= DB_NAME ?>

User: <?= DB_USER ?></pre>
        </div>

        <h3>Paso 1: Instalar Schema (Tablas)</h3>
        <form method="POST">
            <button type="submit" name="action" value="install_schema">
                📊 Instalar Schema
            </button>
        </form>

        <h3>Paso 2: Instalar Datos de Ejemplo (Opcional)</h3>
        <form method="POST">
            <button type="submit" name="action" value="install_seed" class="secondary">
                🌱 Instalar Datos de Ejemplo
            </button>
        </form>

        <div class="error" style="margin-top: 30px;">
            <h3>🗑️ IMPORTANTE: Eliminar este archivo</h3>
            <p>Una vez completada la instalación, <strong>ELIMINA</strong> este archivo por seguridad.</p>
        </div>

        <div class="info">
            <h4>📝 Siguientes pasos:</h4>
            <ol>
                <li>Eliminar este archivo (<code>install-schema.php</code>)</li>
                <li>Acceder al panel: <code>/admin/login.php</code></li>
                <li>Login: <code>admin / admin123</code></li>
                <li>Cambiar la contraseña del admin</li>
            </ol>
        </div>
    </div>
</body>
</html>
