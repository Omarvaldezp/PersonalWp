-- ============================================================================
-- Multimedia de cada entrada del blog: podcast, video, infografía y carrusel
--
-- CÓRRELO COMPLETO. Depende de la tabla posts.
--
-- Flujo (octubre de 2026, decisión de Omar):
--   1. Omar aprueba la entrada en el panel con "Generar multimedia": el estado
--      pasa a 'pendiente'. Puede hacerlo antes de publicar la entrada.
--   2. Claude, en la computadora de Omar (skill multimedia-blog), toma las
--      pendientes, genera en Gemini Notebook el podcast, el video, la
--      infografía y el carrusel, hace las miniaturas en ChatGPT y sube el audio
--      a Spotify for Creators y el video a YouTube desde el navegador. Ninguno
--      de esos servicios tiene API para cuentas personales que sirva aquí.
--   3. Guarda los enlaces en estas columnas y deja el estado en 'listo'.
--      post.html muestra sola la sección "Escucha y mira".
--
-- Son columnas de posts y no una tabla aparte porque todo lo que guardan es
-- público una vez que la entrada se publica: la política "Posts públicos"
-- (published = true) ya cubre la lectura y contenido_editores la escritura.
-- ============================================================================

begin;

alter table public.posts
    add column if not exists multimedia_estado text not null default 'ninguno',
    add column if not exists multimedia_error  text,
    add column if not exists multimedia_actualizado timestamptz,
    add column if not exists podcast_url       text,
    add column if not exists video_url         text,
    add column if not exists infografia_url    text,
    add column if not exists carrusel_url      text;

alter table public.posts drop constraint if exists posts_multimedia_estado_check;
alter table public.posts add constraint posts_multimedia_estado_check
    check (multimedia_estado in ('ninguno', 'pendiente', 'en_proceso', 'listo', 'error'));

-- Sólo se aceptan enlaces de los servicios que la página sabe incrustar; así
-- un enlace mal pegado no termina como iframe de cualquier sitio.
alter table public.posts drop constraint if exists posts_podcast_url_check;
alter table public.posts add constraint posts_podcast_url_check
    check (podcast_url is null or podcast_url ~ '^https://open\.spotify\.com/(episode|show)/[A-Za-z0-9]+');

alter table public.posts drop constraint if exists posts_video_url_check;
alter table public.posts add constraint posts_video_url_check
    check (video_url is null or video_url ~ '^https://(www\.youtube\.com/(watch\?v=|shorts/)|youtu\.be/)[A-Za-z0-9_-]{11}');

-- La infografía y el carrusel los sube el panel al bucket images.
alter table public.posts drop constraint if exists posts_infografia_url_check;
alter table public.posts add constraint posts_infografia_url_check
    check (infografia_url is null or infografia_url like 'https://mogmgtnkyazpsgslucrx.supabase.co/storage/v1/object/public/images/%');

alter table public.posts drop constraint if exists posts_carrusel_url_check;
alter table public.posts add constraint posts_carrusel_url_check
    check (carrusel_url is null or carrusel_url like 'https://mogmgtnkyazpsgslucrx.supabase.co/storage/v1/object/public/images/%');

create index if not exists idx_posts_multimedia_pendiente
    on public.posts (created_at) where multimedia_estado in ('pendiente', 'en_proceso');

commit;


-- ============================================================================
-- Comprobación
--
--   select slug, multimedia_estado from posts order by created_at desc;
--   -- todas en 'ninguno'
--
--   update posts set video_url = 'https://ejemplo.com/x' where false;
--   -- con un valor real, debe fallar por posts_video_url_check
-- ============================================================================
