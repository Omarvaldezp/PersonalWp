-- ============================================================================
-- Redes sociales: borradores y conexión con LinkedIn (después Instagram)
--
-- CÓRRELO COMPLETO. Depende de 00_roles.sql (tiene_rol) y de la tabla posts.
--
-- Flujo (octubre de 2026, decisión de Omar):
--   El panel prepara un borrador por red a partir de una entrada del blog; Omar
--   lo revisa, lo edita y lo publica con un botón. Nada sale sin su aprobación.
--   Publica en su perfil personal de LinkedIn (permiso w_member_social del
--   producto "Share on LinkedIn", que no requiere revisión de LinkedIn).
--
-- Tres tablas:
--   redes_publicaciones   los borradores y lo ya publicado. La lee y escribe el
--                         panel (admin y editor).
--   redes_conexiones      el token de acceso a cada red. NADIE la lee desde la
--                         API: sólo las Edge Functions, con la llave de
--                         servicio. Un token de LinkedIn permite publicar en
--                         nombre de Omar; no puede quedar al alcance del
--                         navegador, ni siquiera del panel.
--   redes_oauth_estados   el "state" de cada intento de conexión, para que
--                         nadie pueda colar su propia cuenta en el callback.
-- ============================================================================

begin;

create table if not exists public.redes_publicaciones (
    id                  uuid primary key default gen_random_uuid(),
    red                 text not null check (red in ('linkedin', 'instagram')),
    post_id             uuid references public.posts (id) on delete set null,
    texto               text not null default '',
    enlace              text,
    enlace_titulo       text,
    enlace_descripcion  text,
    imagen_url          text,
    estado              text not null default 'borrador'
                        check (estado in ('borrador', 'publicando', 'publicado', 'error')),
    id_externo          text,
    url_externa         text,
    error               text,
    publicado_en        timestamptz,
    created_at          timestamptz not null default now(),
    updated_at          timestamptz not null default now()
);

create index if not exists idx_redes_pub_post   on public.redes_publicaciones (post_id);
create index if not exists idx_redes_pub_estado on public.redes_publicaciones (red, estado, created_at desc);

alter table public.redes_publicaciones enable row level security;
revoke all on table public.redes_publicaciones from anon;

drop policy if exists contenido_editores on public.redes_publicaciones;
create policy contenido_editores on public.redes_publicaciones
    for all to authenticated
    using ((select public.tiene_rol('admin', 'editor')))
    with check ((select public.tiene_rol('admin', 'editor')));


create table if not exists public.redes_conexiones (
    red            text primary key check (red in ('linkedin', 'instagram')),
    access_token   text not null,
    refresh_token  text,
    expira         timestamptz not null,
    cuenta_id      text not null,
    cuenta_nombre  text,
    conectado_por  uuid,
    actualizado    timestamptz not null default now()
);

alter table public.redes_conexiones enable row level security;
revoke all on table public.redes_conexiones from anon, authenticated;
comment on table public.redes_conexiones
    is 'Tokens de acceso a redes sociales. Sin políticas a propósito: sólo la '
       'llave de servicio (Edge Functions) puede leerla.';


create table if not exists public.redes_oauth_estados (
    estado    text primary key,
    red       text not null,
    usuario   uuid not null,
    creado    timestamptz not null default now()
);

alter table public.redes_oauth_estados enable row level security;
revoke all on table public.redes_oauth_estados from anon, authenticated;

commit;


-- ============================================================================
-- Comprobación
--
-- Con la llave anónima, las tres tablas deben responder «permission denied».
-- Desde el panel (admin), redes_publicaciones se lee y se escribe; las otras
-- dos siguen cerradas.
-- ============================================================================
