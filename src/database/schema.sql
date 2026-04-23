-- ============================================
-- Schema PostgreSQL para sitio académico
-- Dr. Omar Valdez Palazuelos
-- ============================================

-- Extensiones necesarias
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm"; -- Para búsqueda de similitud

-- ============================================
-- Tabla: usuarios (admin)
-- ============================================
CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(100),
    rol VARCHAR(20) DEFAULT 'admin' CHECK (rol IN ('admin', 'editor')),
    activo BOOLEAN DEFAULT true,
    ultimo_acceso TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Crear índice para búsqueda rápida
CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_usuarios_username ON usuarios(username);

-- ============================================
-- Tabla: blog_posts
-- ============================================
CREATE TABLE IF NOT EXISTS blog_posts (
    id SERIAL PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    extracto TEXT,
    contenido TEXT NOT NULL,
    imagen_portada VARCHAR(500),
    autor_id INTEGER REFERENCES usuarios(id) ON DELETE SET NULL,
    categorias TEXT[] DEFAULT '{}', -- Array nativo para categorías
    etiquetas TEXT[] DEFAULT '{}',  -- Tags
    metadata JSONB DEFAULT '{}',    -- Metadata flexible
    estado VARCHAR(20) DEFAULT 'borrador' CHECK (estado IN ('borrador', 'publicado', 'archivado')),
    fecha_publicacion TIMESTAMP,
    visitas INTEGER DEFAULT 0,
    likes INTEGER DEFAULT 0,
    -- Full-text search
    search_vector tsvector,
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índices para optimización
CREATE INDEX idx_blog_slug ON blog_posts(slug);
CREATE INDEX idx_blog_estado ON blog_posts(estado);
CREATE INDEX idx_blog_fecha_publicacion ON blog_posts(fecha_publicacion DESC);
CREATE INDEX idx_blog_categorias ON blog_posts USING GIN(categorias);
CREATE INDEX idx_blog_etiquetas ON blog_posts USING GIN(etiquetas);
CREATE INDEX idx_blog_metadata ON blog_posts USING GIN(metadata);
CREATE INDEX idx_blog_search ON blog_posts USING GIN(search_vector);

-- Trigger para actualizar search_vector automáticamente
CREATE OR REPLACE FUNCTION blog_posts_search_trigger() RETURNS trigger AS $$
BEGIN
    NEW.search_vector :=
        setweight(to_tsvector('spanish', coalesce(NEW.titulo, '')), 'A') ||
        setweight(to_tsvector('spanish', coalesce(NEW.extracto, '')), 'B') ||
        setweight(to_tsvector('spanish', coalesce(NEW.contenido, '')), 'C');
    RETURN NEW;
END
$$ LANGUAGE plpgsql;

CREATE TRIGGER tsvector_update_blog BEFORE INSERT OR UPDATE
ON blog_posts FOR EACH ROW EXECUTE FUNCTION blog_posts_search_trigger();

-- ============================================
-- Tabla: cursos
-- ============================================
CREATE TABLE IF NOT EXISTS cursos (
    id SERIAL PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    descripcion_corta TEXT,
    descripcion_completa TEXT,
    imagen_portada VARCHAR(500),
    nivel VARCHAR(20) CHECK (nivel IN ('principiante', 'intermedio', 'avanzado')),
    duracion_horas INTEGER,
    precio DECIMAL(10, 2) DEFAULT 0.00,
    moneda VARCHAR(3) DEFAULT 'MXN',
    modalidad VARCHAR(20) CHECK (modalidad IN ('presencial', 'online', 'hibrido')),
    categorias TEXT[] DEFAULT '{}',
    habilidades_aprendidas TEXT[] DEFAULT '{}',
    requisitos TEXT[] DEFAULT '{}',
    temario JSONB DEFAULT '[]', -- Array de objetos con módulos y temas
    instructor_id INTEGER REFERENCES usuarios(id),
    destacado BOOLEAN DEFAULT false,
    activo BOOLEAN DEFAULT true,
    cupo_maximo INTEGER,
    inscritos INTEGER DEFAULT 0,
    calificacion DECIMAL(3, 2) DEFAULT 0.00,
    numero_resenas INTEGER DEFAULT 0,
    metadata JSONB DEFAULT '{}',
    -- Fechas
    fecha_inicio DATE,
    fecha_fin DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índices
CREATE INDEX idx_cursos_slug ON cursos(slug);
CREATE INDEX idx_cursos_nivel ON cursos(nivel);
CREATE INDEX idx_cursos_modalidad ON cursos(modalidad);
CREATE INDEX idx_cursos_destacado ON cursos(destacado);
CREATE INDEX idx_cursos_activo ON cursos(activo);
CREATE INDEX idx_cursos_categorias ON cursos USING GIN(categorias);
CREATE INDEX idx_cursos_calificacion ON cursos(calificacion DESC);

-- ============================================
-- Tabla: investigaciones
-- ============================================
CREATE TABLE IF NOT EXISTS investigaciones (
    id SERIAL PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    autores TEXT[] NOT NULL, -- Array de autores
    abstract TEXT,
    contenido_completo TEXT,
    tipo VARCHAR(50) CHECK (tipo IN ('articulo', 'paper', 'libro', 'capitulo', 'conferencia', 'tesis')),
    categorias TEXT[] DEFAULT '{}',
    palabras_clave TEXT[] DEFAULT '{}',
    -- Información de publicación
    revista VARCHAR(255),
    editorial VARCHAR(255),
    isbn VARCHAR(20),
    doi VARCHAR(100),
    url_publicacion VARCHAR(500),
    pdf_url VARCHAR(500),
    -- Fechas y citación
    fecha_publicacion DATE,
    ano_publicacion INTEGER,
    volumen VARCHAR(20),
    numero VARCHAR(20),
    paginas VARCHAR(20),
    citaciones INTEGER DEFAULT 0,
    -- Metadata académica
    metadata JSONB DEFAULT '{}',
    idioma VARCHAR(5) DEFAULT 'es',
    acceso VARCHAR(20) CHECK (acceso IN ('abierto', 'restringido', 'suscripcion')) DEFAULT 'abierto',
    destacado BOOLEAN DEFAULT false,
    -- Full-text search
    search_vector tsvector,
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índices
CREATE INDEX idx_investigaciones_slug ON investigaciones(slug);
CREATE INDEX idx_investigaciones_tipo ON investigaciones(tipo);
CREATE INDEX idx_investigaciones_categorias ON investigaciones USING GIN(categorias);
CREATE INDEX idx_investigaciones_palabras_clave ON investigaciones USING GIN(palabras_clave);
CREATE INDEX idx_investigaciones_autores ON investigaciones USING GIN(autores);
CREATE INDEX idx_investigaciones_fecha ON investigaciones(fecha_publicacion DESC);
CREATE INDEX idx_investigaciones_ano ON investigaciones(ano_publicacion DESC);
CREATE INDEX idx_investigaciones_destacado ON investigaciones(destacado);
CREATE INDEX idx_investigaciones_search ON investigaciones USING GIN(search_vector);

-- Trigger para search_vector
CREATE OR REPLACE FUNCTION investigaciones_search_trigger() RETURNS trigger AS $$
BEGIN
    NEW.search_vector :=
        setweight(to_tsvector('spanish', coalesce(NEW.titulo, '')), 'A') ||
        setweight(to_tsvector('spanish', coalesce(NEW.abstract, '')), 'B') ||
        setweight(to_tsvector('spanish', coalesce(array_to_string(NEW.palabras_clave, ' '), '')), 'B');
    RETURN NEW;
END
$$ LANGUAGE plpgsql;

CREATE TRIGGER tsvector_update_investigaciones BEFORE INSERT OR UPDATE
ON investigaciones FOR EACH ROW EXECUTE FUNCTION investigaciones_search_trigger();

-- ============================================
-- Tabla: contactos
-- ============================================
CREATE TABLE IF NOT EXISTS contactos (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    empresa VARCHAR(100),
    asunto VARCHAR(255),
    mensaje TEXT NOT NULL,
    tipo VARCHAR(50) CHECK (tipo IN ('consultoria', 'curso', 'investigacion', 'otro')) DEFAULT 'otro',
    estado VARCHAR(20) DEFAULT 'nuevo' CHECK (estado IN ('nuevo', 'leido', 'respondido', 'archivado')),
    notas_admin TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    respondido_por INTEGER REFERENCES usuarios(id),
    fecha_respuesta TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índices
CREATE INDEX idx_contactos_email ON contactos(email);
CREATE INDEX idx_contactos_estado ON contactos(estado);
CREATE INDEX idx_contactos_tipo ON contactos(tipo);
CREATE INDEX idx_contactos_fecha ON contactos(created_at DESC);

-- ============================================
-- Tabla: newsletter_suscriptores
-- ============================================
CREATE TABLE IF NOT EXISTS newsletter_suscriptores (
    id SERIAL PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    nombre VARCHAR(100),
    activo BOOLEAN DEFAULT true,
    intereses TEXT[] DEFAULT '{}', -- blockchain, ai, fintech, etc.
    fuente VARCHAR(50), -- De dónde se suscribió
    token_confirmacion VARCHAR(100) UNIQUE,
    confirmado BOOLEAN DEFAULT false,
    fecha_confirmacion TIMESTAMP,
    ip_suscripcion VARCHAR(45),
    fecha_baja TIMESTAMP,
    razon_baja VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índices
CREATE INDEX idx_newsletter_email ON newsletter_suscriptores(email);
CREATE INDEX idx_newsletter_activo ON newsletter_suscriptores(activo);
CREATE INDEX idx_newsletter_confirmado ON newsletter_suscriptores(confirmado);
CREATE INDEX idx_newsletter_intereses ON newsletter_suscriptores USING GIN(intereses);

-- ============================================
-- Tabla: sesiones (para auth)
-- ============================================
CREATE TABLE IF NOT EXISTS sesiones (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    usuario_id INTEGER REFERENCES usuarios(id) ON DELETE CASCADE,
    token VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índices
CREATE INDEX idx_sesiones_token ON sesiones(token);
CREATE INDEX idx_sesiones_usuario ON sesiones(usuario_id);
CREATE INDEX idx_sesiones_expires ON sesiones(expires_at);

-- ============================================
-- Tabla: configuracion (settings del sitio)
-- ============================================
CREATE TABLE IF NOT EXISTS configuracion (
    clave VARCHAR(100) PRIMARY KEY,
    valor TEXT,
    tipo VARCHAR(20) CHECK (tipo IN ('string', 'number', 'boolean', 'json')) DEFAULT 'string',
    descripcion TEXT,
    categoria VARCHAR(50),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- Tabla: analytics (estadísticas básicas)
-- ============================================
CREATE TABLE IF NOT EXISTS analytics (
    id SERIAL PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL, -- page_view, blog_view, course_view, download, etc.
    referencia_id INTEGER, -- ID del blog, curso, etc.
    referencia_tipo VARCHAR(50), -- blog, curso, investigacion
    ip_address VARCHAR(45),
    user_agent TEXT,
    referer VARCHAR(500),
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Índices para analytics
CREATE INDEX idx_analytics_tipo ON analytics(tipo);
CREATE INDEX idx_analytics_referencia ON analytics(referencia_tipo, referencia_id);
CREATE INDEX idx_analytics_fecha ON analytics(created_at DESC);

-- ============================================
-- Función para actualizar updated_at automáticamente
-- ============================================
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Aplicar trigger a todas las tablas relevantes
CREATE TRIGGER update_usuarios_updated_at BEFORE UPDATE ON usuarios
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_blog_posts_updated_at BEFORE UPDATE ON blog_posts
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_cursos_updated_at BEFORE UPDATE ON cursos
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_investigaciones_updated_at BEFORE UPDATE ON investigaciones
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_newsletter_updated_at BEFORE UPDATE ON newsletter_suscriptores
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- ============================================
-- Datos iniciales (admin por defecto)
-- ============================================
-- Password por defecto: "admin123" (CAMBIAR EN PRODUCCIÓN)
-- Hash generado con password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO usuarios (username, email, password_hash, nombre_completo, rol)
VALUES (
    'admin',
    'omar@omarvaldez.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- admin123
    'Omar Valdez Palazuelos',
    'admin'
) ON CONFLICT (username) DO NOTHING;

-- ============================================
-- Configuración inicial del sitio
-- ============================================
INSERT INTO configuracion (clave, valor, tipo, descripcion, categoria) VALUES
('site_name', 'Dr. Omar Valdez Palazuelos', 'string', 'Nombre del sitio', 'general'),
('site_description', 'Profesor, Investigador y Consultor en Tecnologías Emergentes', 'string', 'Descripción del sitio', 'general'),
('contact_email', 'contacto@omarvaldez.com', 'string', 'Email de contacto principal', 'general'),
('blog_posts_per_page', '12', 'number', 'Posts por página en el blog', 'blog'),
('cursos_destacados_limite', '5', 'number', 'Número de cursos destacados a mostrar', 'cursos'),
('investigaciones_por_pagina', '10', 'number', 'Investigaciones por página', 'investigaciones'),
('analytics_enabled', 'true', 'boolean', 'Habilitar analytics básico', 'analytics')
ON CONFLICT (clave) DO NOTHING;

-- ============================================
-- Comentarios finales
-- ============================================
-- Para ejecutar este schema:
-- psql -U tu_usuario -d tu_database -f schema.sql
--
-- O desde PHP con PDO
