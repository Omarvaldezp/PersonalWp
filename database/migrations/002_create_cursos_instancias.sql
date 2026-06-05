-- Tabla de instancias de cursos (ofertas específicas)
-- Ejemplo: "DISDE Propedéutico 2026-A" es una instancia del curso base "DISDE"
CREATE TABLE IF NOT EXISTS cursos_instancias (
    id SERIAL PRIMARY KEY,
    curso_base_id INTEGER REFERENCES cursos(id) ON DELETE CASCADE,

    -- Detalles de la instancia
    nombre VARCHAR(255) NOT NULL, -- "DISDE Propedéutico 2026-A"
    codigo VARCHAR(50) UNIQUE NOT NULL, -- "DISDE-PROP-2026A"
    descripcion TEXT,

    -- Calendario
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    periodo VARCHAR(50), -- "2026-A", "Verano 2026"

    -- Capacidad
    cupo_maximo INTEGER,

    -- Instructores (profesores y asistentes)
    instructores INTEGER[] DEFAULT '{}', -- Array de usuario_id

    -- Estado
    estado VARCHAR(20) DEFAULT 'activo' CHECK (estado IN ('planificado', 'activo', 'finalizado', 'cancelado')),

    -- Configuración adicional
    metadata JSONB DEFAULT '{}',

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índices
CREATE INDEX IF NOT EXISTS idx_cursos_instancias_curso ON cursos_instancias(curso_base_id);
CREATE INDEX IF NOT EXISTS idx_cursos_instancias_codigo ON cursos_instancias(codigo);
CREATE INDEX IF NOT EXISTS idx_cursos_instancias_estado ON cursos_instancias(estado);
CREATE INDEX IF NOT EXISTS idx_cursos_instancias_periodo ON cursos_instancias(periodo);
CREATE INDEX IF NOT EXISTS idx_cursos_instancias_fechas ON cursos_instancias(fecha_inicio, fecha_fin);

-- Trigger para updated_at
CREATE OR REPLACE FUNCTION update_cursos_instancias_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_cursos_instancias_updated_at
    BEFORE UPDATE ON cursos_instancias
    FOR EACH ROW
    EXECUTE FUNCTION update_cursos_instancias_updated_at();
