-- ============================================================================
-- Instagram: carruseles en redes_publicaciones
--
-- CÓRRELO COMPLETO. Depende de 10_redes_sociales.sql.
--
-- Decisión de Omar (28 de octubre de 2026): cada entrada del blog puede ir a
-- Instagram como carrusel con las láminas de su presentación (Gemini
-- Notebook). Omar aprueba cada publicación; nada sale solo.
--
-- Las tablas de 10_redes_sociales.sql ya aceptan red = 'instagram' y guardan
-- el token en redes_conexiones. Sólo falta dónde guardar las láminas: el panel
-- convierte cada página del PDF a JPEG (Instagram no acepta WebP ni PDF), las
-- sube al bucket images y guarda aquí sus direcciones, en orden.
-- ============================================================================

begin;

alter table public.redes_publicaciones
    add column if not exists imagenes text[];

-- Instagram acepta de 2 a 10 elementos por carrusel, y la Edge Function sólo
-- publica imágenes del bucket del proyecto.
alter table public.redes_publicaciones drop constraint if exists redes_pub_imagenes_check;
alter table public.redes_publicaciones add constraint redes_pub_imagenes_check
    check (imagenes is null or cardinality(imagenes) between 1 and 10);

commit;
