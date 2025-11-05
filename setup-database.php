<?php
/**
 * Script de instalación de base de datos PostgreSQL
 *
 * INSTRUCCIONES:
 * 1. Edita las credenciales de PostgreSQL abajo
 * 2. Sube este archivo a /public_html/
 * 3. Visita: https://omarvaldez.com/setup-database.php
 * 4. ¡BORRA este archivo después de ejecutarlo!
 */

// ============================================
// CONFIGURA TUS CREDENCIALES AQUÍ
// ============================================
$db_host = 'localhost';
$db_port = '5432';
$db_name = 'nombre_de_tu_base_de_datos'; // ← CAMBIAR
$db_user = 'tu_usuario_postgresql';       // ← CAMBIAR
$db_password = 'tu_password_aqui';        // ← CAMBIAR

// ============================================
// NO EDITAR ABAJO DE ESTA LÍNEA
// ============================================

// Seguridad básica - opcional: agregar password para acceder al script
$SETUP_PASSWORD = 'admin123'; // Cambia esto por algo seguro

if (!isset($_GET['password']) || $_GET['password'] !== $SETUP_PASSWORD) {
    die('❌ Acceso denegado. Agrega ?password=admin123 a la URL');
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Base de Datos</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #2563eb; }
        .success { color: #16a34a; background: #dcfce7; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { color: #dc2626; background: #fee2e2; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { color: #ea580c; background: #ffedd5; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { color: #0284c7; background: #e0f2fe; padding: 15px; border-radius: 5px; margin: 10px 0; }
        button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin: 10px 5px;
        }
        button:hover { background: #1e40af; }
        button.secondary { background: #6b7280; }
        button.danger { background: #dc2626; }
        pre {
            background: #1f2937;
            color: #f3f4f6;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 12px;
        }
        .step { margin: 20px 0; padding: 15px; border-left: 4px solid #2563eb; background: #f9fafb; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Instalación de Base de Datos PostgreSQL</h1>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

            // Intentar conectar
            try {
                $dsn = "pgsql:host=$db_host;port=$db_port;dbname=$db_name";
                $pdo = new PDO($dsn, $db_user, $db_password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]);

                echo '<div class="success">✅ Conexión exitosa a PostgreSQL</div>';

                // Ejecutar Schema
                if ($_POST['action'] === 'install_schema') {
                    $schemaFile = __DIR__ . '/database/schema.sql';

                    if (!file_exists($schemaFile)) {
                        throw new Exception('Archivo schema.sql no encontrado en /database/');
                    }

                    $sql = file_get_contents($schemaFile);

                    // Ejecutar el SQL completo
                    $pdo->exec($sql);

                    echo '<div class="success">';
                    echo '<h3>✅ Schema instalado exitosamente</h3>';
                    echo '<p>Se han creado todas las tablas necesarias.</p>';
                    echo '</div>';

                    // Verificar tablas creadas
                    $stmt = $pdo->query("
                        SELECT table_name
                        FROM information_schema.tables
                        WHERE table_schema = 'public'
                        ORDER BY table_name
                    ");
                    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

                    echo '<div class="info">';
                    echo '<h4>📊 Tablas creadas:</h4>';
                    echo '<ul>';
                    foreach ($tables as $table) {
                        echo "<li>✓ $table</li>";
                    }
                    echo '</ul>';
                    echo '</div>';
                }

                // Ejecutar Seed Data
                if ($_POST['action'] === 'install_seed') {
                    $seedFile = __DIR__ . '/database/seed_data.sql';

                    if (!file_exists($seedFile)) {
                        throw new Exception('Archivo seed_data.sql no encontrado en /database/');
                    }

                    $sql = file_get_contents($seedFile);
                    $pdo->exec($sql);

                    echo '<div class="success">';
                    echo '<h3>✅ Datos de ejemplo instalados exitosamente</h3>';
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
                echo '<p><strong>Mensaje:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '<h4>Verifica:</h4>';
                echo '<ul>';
                echo '<li>Que las credenciales sean correctas</li>';
                echo '<li>Que la base de datos exista</li>';
                echo '<li>Que el usuario tenga permisos</li>';
                echo '</ul>';
                echo '</div>';
            } catch (Exception $e) {
                echo '<div class="error">';
                echo '<h3>❌ Error</h3>';
                echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '</div>';
            }
        }
        ?>

        <div class="warning">
            <strong>⚠️ IMPORTANTE:</strong> Asegúrate de haber editado las credenciales en la parte superior de este archivo antes de continuar.
        </div>

        <div class="step">
            <h3>Configuración Actual:</h3>
            <pre>Host: <?= htmlspecialchars($db_host) ?>
Port: <?= htmlspecialchars($db_port) ?>
Database: <?= htmlspecialchars($db_name) ?>
User: <?= htmlspecialchars($db_user) ?>
Password: <?= str_repeat('*', strlen($db_password)) ?></pre>
        </div>

        <div class="step">
            <h3>Paso 1: Instalar Schema (Tablas)</h3>
            <p>Crea todas las tablas necesarias para el sistema.</p>
            <form method="POST">
                <button type="submit" name="action" value="install_schema">
                    📊 Instalar Schema
                </button>
            </form>
        </div>

        <div class="step">
            <h3>Paso 2: Instalar Datos de Ejemplo (Opcional)</h3>
            <p>Agrega contenido de ejemplo para probar el sistema.</p>
            <form method="POST">
                <button type="submit" name="action" value="install_seed" class="secondary">
                    🌱 Instalar Datos de Ejemplo
                </button>
            </form>
        </div>

        <div class="error">
            <h3>🗑️ IMPORTANTE: Eliminar este archivo</h3>
            <p>Una vez completada la instalación, <strong>ELIMINA</strong> este archivo por seguridad:</p>
            <ol>
                <li>Ve a File Manager en SiteGround</li>
                <li>Busca y borra: <code>setup-database.php</code></li>
            </ol>
        </div>

        <div class="info">
            <h4>📝 Siguientes pasos después de la instalación:</h4>
            <ol>
                <li>Eliminar este archivo (<code>setup-database.php</code>)</li>
                <li>Crear <code>src/api/config/config.php</code> con tus credenciales</li>
                <li>Acceder al panel admin: <code>/admin/login.php</code></li>
                <li>Login: <code>admin / admin123</code></li>
                <li>Cambiar la contraseña del admin</li>
            </ol>
        </div>
    </div>
</body>
</html>
