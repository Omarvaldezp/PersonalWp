-- Tabla de inscripciones (relación estudiante-curso)
CREATE TABLE IF NOT EXISTS inscripciones (
    id SERIAL PRIMARY KEY,
    estudiante_id INTEGER NOT NULL REFERENCES estudiantes(id) ON DELETE CASCADE,
    curso_instancia_id INTEGER NOT NULL REFERENCES cursos_instancias(id) ON DELETE CASCADE,

    -- Información de inscripción
    fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(20) DEFAULT 'activo' CHECK (estado IN ('activo', 'abandonado', 'completado', 'suspendido')),

    -- Calificación final (se calcula automáticamente desde evaluaciones)
    calificacion_final DECIMAL(5, 2),
    aprobado BOOLEAN,

    -- Notas adicionales
    notas TEXT,
    metadata JSONB DEFAULT '{}',

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Un estudiante solo puede estar inscrito una vez por curso
    UNIQUE(estudiante_id, curso_instancia_id)
);

-- Índices
CREATE INDEX IF NOT EXISTS idx_inscripciones_estudiante ON inscripciones(estudiante_id);
CREATE INDEX IF NOT EXISTS idx_inscripciones_curso ON inscripciones(curso_instancia_id);
CREATE INDEX IF NOT EXISTS idx_inscripciones_estado ON inscripciones(estado);
CREATE INDEX IF NOT EXISTS idx_inscripciones_calificacion ON inscripciones(calificacion_final) WHERE calificacion_final IS NOT NULL;

-- Trigger para updated_at
CREATE OR REPLACE FUNCTION update_inscripciones_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_inscripciones_updated_at
    BEFORE UPDATE ON inscripciones
    FOR EACH ROW
    EXECUTE FUNCTION update_inscripciones_updated_at();
