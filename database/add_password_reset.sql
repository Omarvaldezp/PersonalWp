-- Agregar campos para recuperación de contraseña
ALTER TABLE usuarios
ADD COLUMN IF NOT EXISTS reset_token VARCHAR(255),
ADD COLUMN IF NOT EXISTS reset_token_expires TIMESTAMP;

-- Índice para búsqueda rápida de tokens
CREATE INDEX IF NOT EXISTS idx_usuarios_reset_token ON usuarios(reset_token);
