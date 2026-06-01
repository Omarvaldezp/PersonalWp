<?php
/**
 * Script de diagnóstico - Verificar datos en tabla diagnostico_disde_respuestas
 * Acceder: https://omarvaldez.com/check-diagnostico-data.php?key=check123
 */

$CHECK_KEY = 'check123';
if (!isset($_GET['key']) || $_GET['key'] !== $CHECK_KEY) {
    die('❌ Acceso denegado. Agrega ?key=check123 a la URL');
}

require_once __DIR__ . '/api/config/config.php';

try {
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "<h2>🔍 Diagnóstico: Tabla diagnostico_disde_respuestas</h2>";
    echo "<style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        table { border-collapse: collapse; width: 100%; background: white; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #1B365D; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .success { color: #16a34a; }
        .error { color: #dc2626; }
        .info { background: #e0f2fe; padding: 15px; border-radius: 5px; margin: 15px 0; }
    </style>";

    // Verificar que la tabla existe
    $tableExists = $pdo->query("SELECT EXISTS (
        SELECT FROM information_schema.tables
        WHERE table_name = 'diagnostico_disde_respuestas'
    )")->fetchColumn();

    if (!$tableExists) {
        echo "<p class='error'>❌ La tabla 'diagnostico_disde_respuestas' NO EXISTE</p>";
        echo "<p>Necesitas ejecutar primero: /migrate-diagnostico-disde.php</p>";
        exit;
    }

    echo "<p class='success'>✅ La tabla existe</p>";

    // Contar registros totales
    $total = $pdo->query("SELECT COUNT(*) FROM diagnostico_disde_respuestas")->fetchColumn();
    echo "<div class='info'><strong>Total de registros:</strong> $total</div>";

    if ($total == 0) {
        echo "<p class='error'>⚠️ No hay registros en la tabla. Los datos NO se están guardando.</p>";
        echo "<h3>Posibles causas:</h3>";
        echo "<ul>";
        echo "<li>El formulario no está enviando correctamente a la API</li>";
        echo "<li>La API está fallando al guardar (revisa logs del servidor)</li>";
        echo "<li>Hay un error en la validación que no se muestra</li>";
        echo "</ul>";
        exit;
    }

    // Mostrar estructura de columnas
    echo "<h3>Estructura de la tabla:</h3>";
    $columns = $pdo->query("SELECT column_name, data_type, is_nullable
                            FROM information_schema.columns
                            WHERE table_name = 'diagnostico_disde_respuestas'
                            ORDER BY ordinal_position")->fetchAll(PDO::FETCH_ASSOC);

    echo "<table><tr><th>Columna</th><th>Tipo</th><th>Nullable</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td><strong>{$col['column_name']}</strong></td>";
        echo "<td>{$col['data_type']}</td>";
        echo "<td>{$col['is_nullable']}</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Verificar columnas nuevas
    $hasNewColumns = $pdo->query("SELECT EXISTS (
        SELECT FROM information_schema.columns
        WHERE table_name = 'diagnostico_disde_respuestas'
        AND column_name = 'institucion_licenciatura'
    )")->fetchColumn();

    if (!$hasNewColumns) {
        echo "<p class='error'>⚠️ Las columnas nuevas (institucion_licenciatura, institucion_maestria) NO EXISTEN</p>";
        echo "<p>Ejecuta: /migrate-add-instituciones.php?key=migrate123</p>";
    } else {
        echo "<p class='success'>✅ Las columnas nuevas existen</p>";
    }

    // Mostrar últimos 10 registros
    echo "<h3>Últimos 10 registros:</h3>";
    $registros = $pdo->query("SELECT id, nombre, correo, grado_academico, conocimiento_total,
                                     created_at, email_enviado, ip_address
                              FROM diagnostico_disde_respuestas
                              ORDER BY created_at DESC
                              LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

    if (count($registros) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Email</th><th>Grado</th><th>Puntaje</th><th>Fecha</th><th>Email</th><th>IP</th></tr>";
        foreach ($registros as $r) {
            echo "<tr>";
            echo "<td>{$r['id']}</td>";
            echo "<td>" . htmlspecialchars($r['nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($r['correo']) . "</td>";
            echo "<td>" . htmlspecialchars($r['grado_academico'] ?? '-') . "</td>";
            echo "<td><strong>{$r['conocimiento_total']}/12</strong></td>";
            echo "<td>" . date('d/m/Y H:i', strtotime($r['created_at'])) . "</td>";
            echo "<td>" . ($r['email_enviado'] ? '✅' : '❌') . "</td>";
            echo "<td>" . htmlspecialchars($r['ip_address'] ?? '-') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // Estadísticas
    echo "<h3>Estadísticas:</h3>";
    $stats = $pdo->query("SELECT
        COUNT(*) as total,
        AVG(conocimiento_total) as promedio,
        MIN(conocimiento_total) as minimo,
        MAX(conocimiento_total) as maximo,
        COUNT(CASE WHEN email_enviado = true THEN 1 END) as emails_enviados
        FROM diagnostico_disde_respuestas")->fetch(PDO::FETCH_ASSOC);

    echo "<ul>";
    echo "<li><strong>Total respuestas:</strong> {$stats['total']}</li>";
    echo "<li><strong>Promedio conocimiento:</strong> " . round($stats['promedio'], 2) . "/12</li>";
    echo "<li><strong>Puntaje mínimo:</strong> {$stats['minimo']}/12</li>";
    echo "<li><strong>Puntaje máximo:</strong> {$stats['maximo']}/12</li>";
    echo "<li><strong>Emails enviados:</strong> {$stats['emails_enviados']}</li>";
    echo "</ul>";

    echo "<div class='info'>";
    echo "<strong>✅ Los datos SÍ se están guardando en la base de datos</strong><br>";
    echo "Si el panel de admin no muestra nada, el problema está en:<br>";
    echo "1. La consulta GET del API (/api/controllers/diagnostico-disde.php)<br>";
    echo "2. El JavaScript del panel admin (/admin/diagnostico-disde.php)<br>";
    echo "3. Revisa la consola del navegador en la página del admin para ver errores.";
    echo "</div>";

} catch (PDOException $e) {
    echo "<p class='error'>❌ Error de base de datos: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
