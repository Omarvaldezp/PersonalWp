# PersonalWp — sitio de Dr. Omar Valdez Palazuelos

Contexto del proyecto para sesiones de Claude Code. Léelo completo antes de
tocar nada: este repo NO es la fuente de verdad del sitio en producción, y hay
varias trampas que pueden tumbar el sitio si se ignoran.

## Lo más importante en tres líneas

1. El sitio en vivo (omarvaldez.com) corre con **Supabase**, y la mayoría de sus
   archivos **no están en este repo**.
2. El deploy **borra en el servidor lo que desaparezca del repo**. Nunca borres
   `src/index.html`.
3. `src/index.html` es una **versión vieja y obsoleta**. Si se llega a subir,
   reemplaza la landing real. Ver "Bomba de tiempo".

---

## Los dos sistemas

Conviven dos sistemas independientes. No los mezcles.

### A. Sitio público + blog (Supabase) — EL QUE ESTÁ EN VIVO

Frontend estático que carga el SDK de Supabase desde CDN. El contenido del blog,
cursos, investigaciones y stats vive en Supabase, no en el repo.

Archivos que **solo existen en el servidor** (`/public_html/`), no en git:

| Ruta | Qué es |
|---|---|
| `index.html` | Landing real, con carga dinámica desde Supabase |
| `post.html` | Artículo de blog individual (`?slug=...`) |
| `css/custom.css` | Estilos de todo el sitio |
| ~~`js/supabase-config.js`~~ | **Ya está en el repo**, en `src/js/`. El deploy manda sobre él |
| `js/app.js` | Lógica del landing (formularios, filtros, animaciones) |
| `admin/index.html` | **Panel de admin del blog**, el que usa Omar para publicar |
| `privacidad.html`, `img/` | Aviso de privacidad y assets |

Colecciones/tablas en Supabase: posts de blog (con `slug`, `category`,
`excerpt`, `content`, `image_url`, `likes`, `dislikes`), cursos,
investigaciones y stats.

### B. Sistema académico (PHP + PostgreSQL) — EN ESTE REPO

Diagnóstico DISDE y gestión de estudiantes. Vive en `src/api/` y `src/admin/`.
Fase 1 terminada: `estudiantes`, `cursos_instancias`, `inscripciones`.

### Conflicto conocido entre A y B

`src/.htaccess` declara `DirectoryIndex index.html index.php`, pero
`src/admin/.htaccess` declara `DirectoryIndex index.php`, y el del subdirectorio
manda. **Gana el panel PHP**: entrar a `/admin/` redirige a `/admin/login.php`.
El panel del blog solo responde en `/admin/index.html`. Ver el pendiente 1. El panel PHP solo es alcanzable por nombre
explícito (`/admin/index.php`, `/admin/estudiantes/index.php`). No es un bug que
haya que "arreglar" sin consultar a Omar: el panel del blog es el que él usa a
diario.

### Código muerto — NO es el blog actual

`src/index-firebase.html`, `src/firebase/*` y `src/main.js` son de un intento
anterior con **Firebase**, abandonado. El blog actual es **Supabase**. No los
confundas ni los uses como referencia. Pendiente de limpieza (ver abajo el
riesgo de borrar archivos).

---

## Deploy

`.github/workflows/deploy.yml` → `SamKirkland/FTP-Deploy-Action` → FTPS a
SiteGround.

- `local-dir: ./src/` → `server-dir: /public_html/`
- Solo se publica lo que está dentro de `src/`
- Dispara en `main`, `master` y la rama de trabajo `claude/analyze-omarvaldez-reference-*`
- `dangerous-clean-slate: false`

**Cuidado:** `dangerous-clean-slate: false` NO significa que no borre nada. La
acción lleva un archivo de estado en el servidor y **sí borra remotamente los
archivos que quites del repo**. Los archivos que nunca han estado en el repo
(los de la sección A) no los toca.

### Bomba de tiempo: `src/index.html`

El `src/index.html` del repo es la landing estática vieja (mayo), no la de
producción. Hoy no se sube porque no ha cambiado y la acción solo envía lo
modificado. Pero:

- Si alguien lo **edita**, el deploy pisa la landing real.
- Si alguien lo **borra**, el deploy borra la landing real.

La solución correcta es **reemplazar su contenido por el de producción, byte por
byte**, no borrarlo. Requiere bajar el archivo del servidor.

### Protección pendiente en el workflow

Falta agregar a la sección `exclude:` del workflow (requiere permiso
`workflows` en la GitHub App, que la sesión normalmente no tiene):

```yaml
exclude: |
  index.html
  index-firebase.html
  main.js
  # ...resto de las exclusiones existentes
```

---

## Restricciones del entorno

- **Hosting:** SiteGround. No se puede cambiar el DNS ni migrar de proveedor.
- **Red de la sesión:** el environment `Default` está en nivel `Trusted`, así que
  `omarvaldez.com` devuelve `403 host_not_allowed`. No se puede verificar el
  sitio ni bajar archivos del servidor hasta que se ponga en `Custom` con
  `omarvaldez.com`, `*.omarvaldez.com` y `*.supabase.co`.
- **FTP directo desde la sesión no sirve:** todo el egress pasa por un proxy
  HTTP/HTTPS. El camino de publicación es siempre commit → push → Action.
- **Base de datos del sistema académico:** PostgreSQL, **no MySQL**. Usa JSONB.
- Los archivos en la raíz del repo (`*.md`, `database/`) **no se despliegan**.
  Antes eso se resolvía con scripts PHP dentro de `src/` que llevaban el SQL
  embebido; se eliminaron por inseguros. Ver "Cómo correr migraciones".

## Cómo correr migraciones

El SQL vive en `database/migrations/` y `database/*.sql`, y **no se despliega**.
Ejecútalo desde el panel de SiteGround (phpPgAdmin o acceso `psql`), pegando el
archivo correspondiente.

**Nunca** vuelvas a poner un script de migración o de diagnóstico dentro de
`src/`. Todo lo que esté ahí queda publicado en `https://omarvaldez.com/` y es
alcanzable por cualquiera. Los scripts que se borraron usaban llaves
`?key=migrate123`, `?key=test123` y `?key=check123` en texto plano; uno de ellos
imprimía nombres y correos de estudiantes.

Si en algún momento hace falta un script de mantenimiento vía web, debe ir detrás
de la sesión de admin (`Auth`), no de una llave en el código fuente.

---

## Estado del trabajo

### Hecho

- Diagnóstico DISDE con institución separada de licenciatura y maestría
- Fase 1 del sistema académico: estudiantes, cursos, inscripciones (API + panel)
- Sección `/trama`: curso-taller de docencia bimodal para la FCA-UAS
  (`src/trama/index.html`, `src/css/trama.css`, kit descargable)
- Eliminados de `src/` los scripts expuestos con llaves débiles (`migrate-*.php`,
  `test-db-connection.php`, `check-diagnostico-data.php`). El deploy los borra
  también del servidor, que es lo que se buscaba. Ver "Cómo correr migraciones".
- API cerrada. Los nueve controladores de `src/api/controllers/` exigen sesión
  vía `api_require_auth()` (`src/api/auth/guard.php`). Única excepción: el
  **POST** de `diagnostico-disde.php`, que recibe el formulario público de
  `src/diagnosticoDISDE.php`; su GET sí está protegido.
- CORS cerrado. `src/api/config/cors.php` ya no emite el comodín. Todo lo que
  consume la API es del mismo origen y no necesita CORS. Para autorizar un
  dominio externo, define `API_ALLOWED_ORIGINS` en `config.php`.
- Los controladores dejaron de devolver `$e->getMessage()` al cliente; el
  detalle va a `error_log` y el cliente recibe un mensaje genérico.
- `config/`, `models/` y `utils/` se bloquean por HTTP con un `.htaccess` propio
  dentro de cada uno. **Nunca metas reglas de `mod_rewrite` bajo `/api/`**: la
  primera versión de `src/api/.htaccess` las traía y dejó `/api/auth/login.php`
  respondiendo 404, con lo que el panel no podía cerrar sesión. La redirección
  a HTTPS ya la hace el `.htaccess` de la raíz.
- **`auth/` queda accesible a propósito**: el panel cierra sesión llamando a
  `/api/auth/login.php?action=logout` desde el navegador.

### Retiro del PHP, en curso

El panel y la API PHP se retiran; todo queda en el panel del blog (Supabase).
Los archivos preparados van en `database/supabase/` y `supabase/functions/`.
El orden importa:

1. `00_roles.sql` — tabla `perfiles` y función `tiene_rol()`. **Va primero**,
   porque `01_academico.sql` depende de ella. Incluye el paso obligatorio de
   darte a ti mismo el rol `admin`.
2. `01_academico.sql` — las cuatro tablas académicas con RLS por rol.
3. `02_corregir_politicas.sql` — repara los like y acota la baja de suscriptores.
4. `supabase/functions/diagnostico-correo/` — Edge Function que sustituye al
   `mail()` del PHP. **Sin ella no se puede apagar el PHP** sin que el
   estudiante deje de recibir su correo.

Decisiones ya tomadas por Omar: habrá más usuarios del panel (por eso los
roles), y el correo del diagnóstico se conserva (por eso la Edge Function).

### Pendiente, por prioridad

1. **Decidir qué debe servir `/admin/`.** Comprobado en agosto de 2026: entrar
   a `/admin/` lleva a `/admin/login.php`, o sea al **panel PHP**, no al del
   blog. Lo causa el `DirectoryIndex index.php` de `src/admin/.htaccess`, que
   desde mayo de 2026 pisa el del directorio padre. El panel del blog solo es
   alcanzable escribiendo `/admin/index.html`. Falta que Omar decida cuál de
   los dos debe quedarse en `/admin/`.
2. Sincronizar los archivos de producción hacia el repo (sección A)
3. Limitar la frecuencia de los `POST` de contacto y newsletter (hoy sin tope)
4. Sustituir los `alert()` del panel PHP por toasts
5. Fases 2 a 5 del sistema académico: actividades, calificaciones, asistencia,
   cuestionarios múltiples, reportes

---

## Convenciones

- **Idioma:** todo el contenido de cara al usuario va en **español de México**.
  No neutro, no de España.
- El sitio no usa emojis en el contenido publicado.
- PHP con PDO y sentencias preparadas. `Database` es singleton; las respuestas
  de la API pasan por la clase `Response`.
- Las páginas del panel en subcarpetas necesitan rutas **absolutas** para assets
  (`/admin/assets/...`) y `../../` para los `require_once` hacia `src/api/`.
- Antes de proponer cambios al sitio público, recuerda que muchos archivos no
  están aquí: pídelos o baja del servidor, no los reconstruyas de memoria.
