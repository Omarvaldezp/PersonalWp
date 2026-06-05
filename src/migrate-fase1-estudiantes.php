<?php
/**
 * Migración Fase 1: Sistema de Estudiantes y Cursos
 * Ejecutar UNA SOLA VEZ: https://omarvaldez.com/migrate-fase1-estudiantes.php?key=migrate123
 * BORRAR después de ejecutar
 */

$MIGRATION_KEY = 'migrate123';
if (!isset($_GET['key']) || $_GET['key'] !== $MIGRATION_KEY) {
    die('❌ Acceso denegado. Agrega ?key=migrate123 a la URL');
}

require_once __DIR__ . '/api/config/config.php';

try {
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "<style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .success { color: #16a34a; }
        .error { color: #dc2626; }
        .info { background: #e0f2fe; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .warning { background: #fef3c7; padding: 15px; border-radius: 5px; margin: 15px 0; }
        h2 { color: #1B365D; }
        h3 { color: #2E5A9C; margin-top: 25px; }
        pre { background: #1e1e1e; color: #d4d4d4; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>";

    echo "<h2>🔄 Migración Fase 1: Sistema de Estudiantes y Cursos</h2>";

    // PASO 1: Crear tabla estudiantes
    echo "<h3>Paso 1: Crear tabla 'estudiantes'</h3>";
    $sql1 = "
-- Tabla central de estudiantes (perfiles)
CREATE TABLE IF NOT EXISTS estudiantes (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    correo VARCHAR(255) NOT NULL UNIQUE,
    foto VARCHAR(500),
    matricula VARCHAR(100) UNIQUE,
    telefono VARCHAR(20),
    grado_academico VARCHAR(100),
    carrera_licenciatura VARCHAR(255),
    area_formacion VARCHAR(255),
    institucion_licenciatura VARCHAR(255),
    institucion_maestria VARCHAR(255),
    diagnostico_respuesta_id INTEGER REFERENCES diagnostico_disde_respuestas(id),
    activo BOOLEAN DEFAULT true,
    notas TEXT,
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_estudiantes_correo ON estudiantes(correo);
CREATE INDEX IF NOT EXISTS idx_estudiantes_matricula ON estudiantes(matricula) WHERE matricula IS NOT NULL;
CREATE INDEX IF NOT EXISTS idx_estudiantes_activo ON estudiantes(activo);
CREATE INDEX IF NOT EXISTS idx_estudiantes_nombre ON estudiantes USING GIN(to_tsvector('spanish', nombre));

CREATE OR REPLACE FUNCTION update_estudiantes_updated_at()
RETURNS TRIGGER AS \$\$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trigger_estudiantes_updated_at ON estudiantes;
CREATE TRIGGER trigger_estudiantes_updated_at
    BEFORE UPDATE ON estudiantes
    FOR EACH ROW
    EXECUTE FUNCTION update_estudiantes_updated_at();
    ";

    $pdo->exec($sql1);
    echo "<p class='success'>✅ Tabla 'estudiantes' creada exitosamente</p>";

    // PASO 2: Crear tabla cursos_instancias
    echo "<h3>Paso 2: Crear tabla 'cursos_instancias'</h3>";
    $sql2 = "
CREATE TABLE IF NOT EXISTS cursos_instancias (
    id SERIAL PRIMARY KEY,
    curso_base_id INTEGER REFERENCES cursos(id) ON DELETE CASCADE,
    nombre VARCHAR(255) NOT NULL,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    descripcion TEXT,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    periodo VARCHAR(50),
    cupo_maximo INTEGER,
    instructores INTEGER[] DEFAULT '{}',
    estado VARCHAR(20) DEFAULT 'activo' CHECK (estado IN ('planificado', 'activo', 'finalizado', 'cancelado')),
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_cursos_instancias_curso ON cursos_instancias(curso_base_id);
CREATE INDEX IF NOT EXISTS idx_cursos_instancias_codigo ON cursos_instancias(codigo);
CREATE INDEX IF NOT EXISTS idx_cursos_instancias_estado ON cursos_instancias(estado);
CREATE INDEX IF NOT EXISTS idx_cursos_instancias_periodo ON cursos_instancias(periodo);

CREATE OR REPLACE FUNCTION update_cursos_instancias_updated_at()
RETURNS TRIGGER AS \$\$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trigger_cursos_instancias_updated_at ON cursos_instancias;
CREATE TRIGGER trigger_cursos_instancias_updated_at
    BEFORE UPDATE ON cursos_instancias
    FOR EACH ROW
    EXECUTE FUNCTION update_cursos_instancias_updated_at();
    ";

    $pdo->exec($sql2);
    echo "<p class='success'>✅ Tabla 'cursos_instancias' creada exitosamente</p>";

    // PASO 3: Crear tabla inscripciones
    echo "<h3>Paso 3: Crear tabla 'inscripciones'</h3>";
    $sql3 = "
CREATE TABLE IF NOT EXISTS inscripciones (
    id SERIAL PRIMARY KEY,
    estudiante_id INTEGER NOT NULL REFERENCES estudiantes(id) ON DELETE CASCADE,
    curso_instancia_id INTEGER NOT NULL REFERENCES cursos_instancias(id) ON DELETE CASCADE,
    fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(20) DEFAULT 'activo' CHECK (estado IN ('activo', 'abandonado', 'completado', 'suspendido')),
    calificacion_final DECIMAL(5, 2),
    aprobado BOOLEAN,
    notas TEXT,
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(estudiante_id, curso_instancia_id)
);

CREATE INDEX IF NOT EXISTS idx_inscripciones_estudiante ON inscripciones(estudiante_id);
CREATE INDEX IF NOT EXISTS idx_inscripciones_curso ON inscripciones(curso_instancia_id);
CREATE INDEX IF NOT EXISTS idx_inscripciones_estado ON inscripciones(estado);

CREATE OR REPLACE FUNCTION update_inscripciones_updated_at()
RETURNS TRIGGER AS \$\$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
\$\$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trigger_inscripciones_updated_at ON inscripciones;
CREATE TRIGGER trigger_inscripciones_updated_at
    BEFORE UPDATE ON inscripciones
    FOR EACH ROW
    EXECUTE FUNCTION update_inscripciones_updated_at();
    ";

    $pdo->exec($sql3);
    echo "<p class='success'>✅ Tabla 'inscripciones' creada exitosamente</p>";

    // PASO 4: Migrar datos del diagnóstico a estudiantes
    echo "<h3>Paso 4: Migrar datos del diagnóstico a estudiantes</h3>";

    $sqlMigrate = "
INSERT INTO estudiantes (
    nombre,
    correo,
    grado_academico,
    carrera_licenciatura,
    area_formacion,
    institucion_licenciatura,
    institucion_maestria,
    diagnostico_respuesta_id,
    created_at
)
SELECT DISTINCT ON (correo)
    nombre,
    correo,
    grado_academico,
    carrera_licenciatura,
    area_formacion,
    institucion_licenciatura,
    institucion_maestria,
    id as diagnostico_respuesta_id,
    created_at
FROM diagnostico_disde_respuestas
WHERE correo IS NOT NULL
ON CONFLICT (correo) DO UPDATE SET
    nombre = EXCLUDED.nombre,
    grado_academico = EXCLUDED.grado_academico,
    carrera_licenciatura = EXCLUDED.carrera_licenciatura,
    area_formacion = EXCLUDED.area_formacion,
    institucion_licenciatura = EXCLUDED.institucion_licenciatura,
    institucion_maestria = EXCLUDED.institucion_maestria,
    updated_at = CURRENT_TIMESTAMP
RETURNING id;
    ";

    $stmt = $pdo->query($sqlMigrate);
    $migrated = $stmt->rowCount();

    echo "<p class='success'>✅ Estudiantes migrados: <strong>$migrated</strong></p>";

    // Contar total de estudiantes
    $totalEstudiantes = $pdo->query("SELECT COUNT(*) FROM estudiantes")->fetchColumn();
    echo "<p class='info'>Total de estudiantes en la base de datos: <strong>$totalEstudiantes</strong></p>";

    // Mostrar estudiantes migrados
    echo "<h3>Estudiantes importados:</h3>";
    $estudiantes = $pdo->query("
        SELECT id, nombre, correo, grado_academico, created_at
        FROM estudiantes
        ORDER BY created_at DESC
        LIMIT 20
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo "<table style='width:100%;background:white;border-collapse:collapse;'>";
    echo "<tr style='background:#1B365D;color:white;'>";
    echo "<th style='padding:10px;border:1px solid #ddd;'>ID</th>";
    echo "<th style='padding:10px;border:1px solid #ddd;'>Nombre</th>";
    echo "<th style='padding:10px;border:1px solid #ddd;'>Email</th>";
    echo "<th style='padding:10px;border:1px solid #ddd;'>Grado</th>";
    echo "<th style='padding:10px;border:1px solid #ddd;'>Fecha</th>";
    echo "</tr>";

    foreach ($estudiantes as $e) {
        echo "<tr>";
        echo "<td style='padding:8px;border:1px solid #ddd;'>{$e['id']}</td>";
        echo "<td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($e['nombre']) . "</td>";
        echo "<td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($e['correo']) . "</td>";
        echo "<td style='padding:8px;border:1px solid #ddd;'>" . htmlspecialchars($e['grado_academico'] ?? '-') . "</td>";
        echo "<td style='padding:8px;border:1px solid #ddd;'>" . date('d/m/Y', strtotime($e['created_at'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Resumen final
    echo "<div style='background:#dcfce7;padding:20px;margin:30px 0;border-radius:8px;border-left:4px solid #16a34a;'>";
    echo "<h2 style='color:#16a34a;margin-top:0;'>✅ Migración Fase 1 Completada Exitosamente</h2>";
    echo "<h3>Tablas creadas:</h3>";
    echo "<ul>";
    echo "<li>✅ <strong>estudiantes</strong> - Perfiles de estudiantes</li>";
    echo "<li>✅ <strong>cursos_instancias</strong> - Instancias de cursos</li>";
    echo "<li>✅ <strong>inscripciones</strong> - Matrículas estudiante-curso</li>";
    echo "</ul>";
    echo "<h3>Datos migrados:</h3>";
    echo "<ul>";
    echo "<li>✅ <strong>$totalEstudiantes estudiantes</strong> importados desde el diagnóstico</li>";
    echo "</ul>";
    echo "</div>";

    echo "<div class='warning'>";
    echo "<h3>⚠️ IMPORTANTE - Próximos pasos:</h3>";
    echo "<ol>";
    echo "<li><strong>Elimina este archivo ahora por seguridad:</strong> <code>/public_html/migrate-fase1-estudiantes.php</code></li>";
    echo "<li><strong>Ve al panel de estudiantes:</strong> <a href='/admin/estudiantes/index.php'>/admin/estudiantes/index.php</a></li>";
    echo "<li><strong>Crea tu primer curso:</strong> <a href='/admin/cursos-academicos/crear.php'>/admin/cursos-academicos/crear.php</a></li>";
    echo "<li><strong>Inscribe estudiantes:</strong> Una vez creado el curso, inscribe a los estudiantes</li>";
    echo "</ol>";
    echo "<p><em>Nota: Los archivos del panel admin se están generando ahora. Espera el deployment o súbelos manualmente.</em></p>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
