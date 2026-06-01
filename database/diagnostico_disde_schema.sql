-- Tabla para almacenar respuestas del Diagnóstico DISDE
CREATE TABLE IF NOT EXISTS diagnostico_disde_respuestas (
    id SERIAL PRIMARY KEY,

    -- Datos del participante
    nombre VARCHAR(255) NOT NULL,
    correo VARCHAR(255) NOT NULL,
    grado_academico VARCHAR(100),
    carrera_licenciatura VARCHAR(255),
    area_formacion VARCHAR(255),
    institucion VARCHAR(255),

    -- Puntajes
    conocimiento_total INTEGER, -- Sobre 12

    -- Respuestas por área (JSON para flexibilidad)
    respuestas_area1 JSONB, -- Programa DISDE
    respuestas_area2 JSONB, -- Epistemología
    respuestas_area3 JSONB, -- Metodología
    respuestas_area4 JSONB, -- Estadística
    respuestas_area5 JSONB, -- Redacción y APA
    respuestas_area6 JSONB, -- TIC e IA

    -- Todas las respuestas individuales
    respuestas_completas JSONB,

    -- Resultados calculados por área
    resultados_por_area JSONB,

    -- Metadatos
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    email_enviado BOOLEAN DEFAULT FALSE,
    email_enviado_at TIMESTAMP
);

-- Índices para búsquedas eficientes
CREATE INDEX IF NOT EXISTS idx_diagnostico_correo ON diagnostico_disde_respuestas(correo);
CREATE INDEX IF NOT EXISTS idx_diagnostico_fecha ON diagnostico_disde_respuestas(created_at);
CREATE INDEX IF NOT EXISTS idx_diagnostico_conocimiento ON diagnostico_disde_respuestas(conocimiento_total);

-- Comentarios
COMMENT ON TABLE diagnostico_disde_respuestas IS 'Respuestas del cuestionario diagnóstico DISDE';
COMMENT ON COLUMN diagnostico_disde_respuestas.conocimiento_total IS 'Puntaje total de preguntas de conocimiento (max 12)';
COMMENT ON COLUMN diagnostico_disde_respuestas.respuestas_completas IS 'JSON con todas las respuestas A1-F4';
COMMENT ON COLUMN diagnostico_disde_respuestas.resultados_por_area IS 'JSON con puntajes calculados por cada área';
