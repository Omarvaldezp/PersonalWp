-- Tabla central de estudiantes (perfiles)
CREATE TABLE IF NOT EXISTS estudiantes (
    id SERIAL PRIMARY KEY,

    -- Campos requeridos
    nombre VARCHAR(255) NOT NULL,
    correo VARCHAR(255) NOT NULL UNIQUE,

    -- Campos opcionales
    foto VARCHAR(500), -- URL a foto de perfil
    matricula VARCHAR(100) UNIQUE,
    telefono VARCHAR(20),

    -- Información académica
    grado_academico VARCHAR(100),
    carrera_licenciatura VARCHAR(255),
    area_formacion VARCHAR(255),
    institucion_licenciatura VARCHAR(255),
    institucion_maestria VARCHAR(255),

    -- Vinculación con diagnóstico original
    diagnostico_respuesta_id INTEGER REFERENCES diagnostico_disde_respuestas(id),

    -- Estado
    activo BOOLEAN DEFAULT true,

    -- Notas adicionales
    notas TEXT,
    metadata JSONB DEFAULT '{}',

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índices para búsquedas eficientes
CREATE INDEX IF NOT EXISTS idx_estudiantes_correo ON estudiantes(correo);
CREATE INDEX IF NOT EXISTS idx_estudiantes_matricula ON estudiantes(matricula) WHERE matricula IS NOT NULL;
CREATE INDEX IF NOT EXISTS idx_estudiantes_activo ON estudiantes(activo);
CREATE INDEX IF NOT EXISTS idx_estudiantes_nombre ON estudiantes USING GIN(to_tsvector('spanish', nombre));

-- Trigger para actualizar updated_at automáticamente
CREATE OR REPLACE FUNCTION update_estudiantes_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_estudiantes_updated_at
    BEFORE UPDATE ON estudiantes
    FOR EACH ROW
    EXECUTE FUNCTION update_estudiantes_updated_at();
