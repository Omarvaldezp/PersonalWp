-- ============================================
-- Datos de ejemplo para el sitio académico
-- Dr. Omar Valdez Palazuelos
-- ============================================

-- Nota: Ejecutar este archivo DESPUÉS de schema.sql

-- ============================================
-- Blog Posts de Ejemplo
-- ============================================

INSERT INTO blog_posts (titulo, slug, extracto, contenido, categorias, etiquetas, estado, fecha_publicacion, autor_id) VALUES

('Bitcoin y el futuro de las finanzas digitales', 'bitcoin-futuro-finanzas-digitales',
'Análisis profundo sobre cómo Bitcoin está transformando el sistema financiero global y las implicaciones para México.',
'<p>Bitcoin representa una revolución en el concepto de dinero y finanzas. En este artículo exploramos...</p>
<h2>El nacimiento de Bitcoin</h2>
<p>En 2009, Satoshi Nakamoto introdujo Bitcoin como un sistema de efectivo electrónico peer-to-peer...</p>',
'{"Bitcoin", "Finanzas"}', '{"criptomonedas", "blockchain", "bitcoin"}', 'publicado', '2024-11-01', 1),

('Inteligencia Artificial en la educación moderna', 'ia-educacion-moderna',
'Cómo la IA está revolucionando los métodos de enseñanza y aprendizaje en las universidades.',
'<p>La Inteligencia Artificial está transformando radicalmente el panorama educativo...</p>',
'{"IA", "Educación"}', '{"inteligencia-artificial", "educacion", "tecnologia"}', 'publicado', '2024-10-28', 1),

('Blockchain más allá de las criptomonedas', 'blockchain-mas-alla-criptomonedas',
'Explorando las aplicaciones de blockchain en cadenas de suministro, identidad digital y más.',
'<p>Blockchain es mucho más que la tecnología detrás de Bitcoin...</p>',
'{"Blockchain", "Tecnología"}', '{"blockchain", "smart-contracts", "dlt"}', 'publicado', '2024-10-20', 1),

('Fintech en México: Oportunidades y desafíos', 'fintech-mexico-oportunidades',
'El ecosistema fintech mexicano está creciendo rápidamente. Analizamos las oportunidades.',
'<p>México se ha convertido en un hub de innovación fintech en Latinoamérica...</p>',
'{"Fintech", "México"}', '{"fintech", "mexico", "innovacion"}', 'publicado', '2024-10-15', 1);

-- ============================================
-- Cursos de Ejemplo
-- ============================================

INSERT INTO cursos (titulo, slug, descripcion_corta, descripcion_completa, nivel, duracion_horas, precio, modalidad, categorias, habilidades_aprendidas, requisitos, destacado, activo, cupo_maximo, fecha_inicio, instructor_id) VALUES

('Introducción a Bitcoin y Blockchain', 'intro-bitcoin-blockchain',
'Aprende los fundamentos de Bitcoin, blockchain y criptomonedas desde cero.',
'<p>Este curso te introduce al mundo de las criptomonedas y blockchain. Aprenderás:</p>
<ul>
<li>Qué es Bitcoin y cómo funciona</li>
<li>Tecnología blockchain</li>
<li>Wallets y seguridad</li>
<li>Trading básico</li>
</ul>',
'principiante', 20, 2500.00, 'online',
'{"Bitcoin", "Blockchain"}',
'{"Comprender blockchain", "Usar wallets", "Análisis de mercado"}',
'{"Conocimientos básicos de computación", "Interés en tecnología"}',
true, true, 30, '2024-12-01', 1),

('Desarrollo de Smart Contracts con Solidity', 'smart-contracts-solidity',
'Aprende a programar contratos inteligentes en Ethereum.',
'<p>Curso avanzado sobre desarrollo de smart contracts...</p>',
'avanzado', 40, 5000.00, 'online',
'{"Blockchain", "Programación"}',
'{"Solidity", "Ethereum", "DApps"}',
'{"Conocimientos de programación", "Comprensión de blockchain"}',
true, true, 20, '2024-12-15', 1),

('IA para Negocios y Toma de Decisiones', 'ia-negocios-decisiones',
'Aplica Inteligencia Artificial para mejorar la toma de decisiones empresariales.',
'<p>Aprende a usar IA en contextos empresariales...</p>',
'intermedio', 30, 3500.00, 'hibrido',
'{"IA", "Negocios"}',
'{"Machine Learning", "Análisis de datos", "IA aplicada"}',
'{"Conocimientos básicos de estadística"}',
false, true, 25, '2025-01-10', 1),

('Fintech: Innovación Financiera Digital', 'fintech-innovacion-digital',
'Explora el ecosistema fintech y las nuevas tecnologías financieras.',
'<p>Curso completo sobre el ecosistema fintech...</p>',
'intermedio', 25, 3000.00, 'online',
'{"Fintech", "Innovación"}',
'{"APIs financieras", "Pagos digitales", "Open Banking"}',
'{"Conocimientos básicos de finanzas"}',
true, true, 30, '2025-01-20', 1),

('Consultoría Blockchain para Empresas', 'consultoria-blockchain-empresas',
'Aprende a asesorar empresas en la implementación de blockchain.',
'<p>Conviértete en consultor blockchain empresarial...</p>',
'avanzado', 35, 4500.00, 'presencial',
'{"Blockchain", "Consultoría"}',
'{"Análisis de casos de uso", "Arquitectura blockchain", "ROI blockchain"}',
'{"Experiencia en negocios", "Conocimientos de blockchain"}',
false, true, 15, '2025-02-01', 1);

-- ============================================
-- Investigaciones de Ejemplo
-- ============================================

INSERT INTO investigaciones (titulo, slug, autores, abstract, tipo, categorias, palabras_clave, revista, doi, fecha_publicacion, ano_publicacion, destacado) VALUES

('Blockchain aplicado a cadenas de suministro en México', 'blockchain-cadenas-suministro-mexico',
'{"Omar Valdez Palazuelos", "María García López"}',
'Este estudio analiza la aplicación de tecnología blockchain en la optimización de cadenas de suministro en el contexto mexicano, evaluando casos de uso en industrias manufactureras y agrícolas.',
'articulo',
'{"Blockchain", "Logística"}',
'{"blockchain", "supply-chain", "mexico", "logistica"}',
'Revista Mexicana de Tecnología',
'10.1234/rmt.2024.001',
'2024-09-15', 2024, true),

('Inteligencia Artificial en la educación superior mexicana', 'ia-educacion-superior-mexico',
'{"Omar Valdez Palazuelos"}',
'Investigación sobre la adopción y efectividad de herramientas de IA en instituciones de educación superior en México.',
'paper',
'{"IA", "Educación"}',
'{"inteligencia-artificial", "educacion-superior", "mexico", "machine-learning"}',
'Journal of Educational Technology',
'10.1234/jet.2024.042',
'2024-08-20', 2024, true),

('Bitcoin como reserva de valor en economías emergentes', 'bitcoin-reserva-valor-emergentes',
'{"Omar Valdez Palazuelos", "Juan Pérez Sánchez", "Ana Torres"}',
'Análisis comparativo del comportamiento de Bitcoin como activo de reserva en países con alta inflación.',
'articulo',
'{"Bitcoin", "Economía"}',
'{"bitcoin", "store-of-value", "emerging-markets", "inflation"}',
'Cryptocurrency Research Quarterly',
'10.1234/crq.2024.018',
'2024-07-10', 2024, false),

('Impacto del Fintech en la inclusión financiera', 'fintech-inclusion-financiera',
'{"Omar Valdez Palazuelos", "Laura Martínez"}',
'Estudio sobre cómo las soluciones fintech están democratizando el acceso a servicios financieros en poblaciones no bancarizadas.',
'paper',
'{"Fintech", "Inclusión"}',
'{"fintech", "financial-inclusion", "unbanked", "digital-payments"}',
'Latin American Finance Review',
'10.1234/lafr.2024.025',
'2024-06-05', 2024, true);

-- ============================================
-- Configuración adicional del sitio
-- ============================================

-- Actualizar algunas configuraciones
UPDATE configuracion SET valor = '6' WHERE clave = 'blog_posts_per_page';
UPDATE configuracion SET valor = '5' WHERE clave = 'cursos_destacados_limite';

-- ============================================
-- Comentarios finales
-- ============================================

-- Total insertado:
-- - 4 Blog posts
-- - 5 Cursos
-- - 4 Investigaciones
--
-- Todos los contenidos están vinculados al usuario admin (ID: 1)
-- Para agregar más contenido, usa el panel de administración o las APIs
