# Sitio Web Académico - Dr. Omar Valdez Palazuelos

Sitio profesional de profesor universitario, investigador y consultor en
**Blockchain**, **Bitcoin**, **Inteligencia Artificial** y **Fintech**.

En vivo: https://omarvaldez.com

> Antes de modificar algo, lee [CLAUDE.md](CLAUDE.md). Explica qué archivos
> viven sólo en el servidor y qué cambios pueden tumbar el sitio.

---

## Stack

- **Frontend:** HTML, CSS y JavaScript estáticos, sin compilación
- **Backend:** [Supabase](https://supabase.com) (PostgreSQL con RLS, Auth y Storage)
- **Hosting:** SiteGround, con deploy por FTPS desde GitHub Actions

## Panel de administración

Un solo panel: **https://omarvaldez.com/admin/**

- Archivo: `src/admin/index.html`, autocontenido
- Acceso con usuario de Supabase Auth; los permisos salen de la tabla
  `perfiles` (`database/supabase/00_roles.sql`)
- Secciones: Dashboard, Blog, Cursos, Investigación, Seminario, Contactos,
  Suscriptores, Newsletter, Analytics y Estadísticas

## Estructura

```
PersonalWp/
├── src/                     # Lo único que se publica (→ /public_html/)
│   ├── admin/index.html     # Panel de administración
│   ├── js/supabase-config.js
│   ├── seminario/           # Micro sitio del Seminario de Investigación
│   ├── trama/               # Curso-taller de docencia bimodal
│   └── manual-claude/       # Manual de Claude para investigadores
├── database/supabase/       # SQL para el SQL Editor de Supabase, en orden
└── .github/workflows/       # Deploy a SiteGround
```

## Deploy

Cada push a `main` o a la rama de trabajo configurada en
`.github/workflows/deploy.yml` publica `src/` en SiteGround. El deploy **borra
del servidor** lo que se quite del repo.

---

MIT License - Omar Valdez Palazuelos
