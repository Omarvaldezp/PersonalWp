# PersonalWp — sitio de Dr. Omar Valdez Palazuelos

Contexto del proyecto para sesiones de Claude Code. Léelo completo antes de
tocar nada: este repo NO es la fuente de verdad del sitio en producción, y hay
varias trampas que pueden tumbar el sitio si se ignoran.

## Lo más importante en tres líneas

1. El sitio en vivo (omarvaldez.com) corre con **Supabase**. Desde octubre de
   2026 **todos sus archivos están en `src/`**, copiados byte por byte del
   servidor: el repo es la fuente de verdad.
2. El deploy **borra en el servidor lo que desaparezca del repo**. Borrar
   `src/index.html` tumba la portada.
3. Cualquier cambio hecho a mano en el servidor (Administrador de archivos de
   SiteGround) se pierde en cuanto el repo cambie ese archivo. Edita aquí.

---

## Un solo sistema: Supabase

Hasta octubre de 2026 convivía un segundo sistema en PHP + PostgreSQL (panel
de `/admin/index.php`, API en `src/api/`, diagnóstico DISDE y gestión de
estudiantes). Se retiró completo, junto con los datos, por decisión de Omar.
Ver "Retiro del PHP" más abajo. **No lo reconstruyas** ni agregues PHP nuevo:
todo lo que necesite servidor va en Supabase (tablas con RLS, funciones
`SECURITY DEFINER` o Edge Functions).

### Sitio público + blog (Supabase)

Frontend estático que carga el SDK de Supabase desde CDN. El contenido del blog,
cursos, investigaciones y stats vive en Supabase, no en el repo.

Archivos del sitio público (todos en `src/`, el deploy manda sobre ellos):

| Ruta | Qué es |
|---|---|
| `index.html` | Portada, con carga dinámica desde Supabase |
| `post.html` | Artículo de blog individual (`?slug=...`) |
| `css/custom.css` | Estilos de todo el sitio |
| `js/supabase-config.js` | Cliente de Supabase y funciones de datos (`window.API`) |
| `js/app.js` | Lógica de la portada (formularios, filtros, animaciones) |
| `privacidad.html` | Aviso de privacidad |
| `unsubscribe.html` | Baja del newsletter (`?e=` correo en base64) |
| `img/`, `favicon.ico`, `robots.txt`, `sitemap.xml` | Imágenes y archivos para buscadores |

Se sincronizaron del servidor el 10 de octubre de 2026, comprobando con dos
descargas sin caché (`?nc=...`) que el contenido fuera idéntico. Ojo: la caché de
SiteGround (`x-proxy-cache: HIT`) puede servir una copia vieja; para bajar o
verificar un archivo, agrega siempre un parámetro aleatorio.

**Caché del navegador:** `src/.htaccess` le dice al navegador que guarde CSS y
JS **un año**. Por eso las páginas cargan `custom.css`, `app.js` y
`supabase-config.js` con `?v=AAAAMMDD`. **Cada vez que cambies uno de esos tres
archivos, sube la fecha de `?v=` en todas las páginas que lo cargan**
(`grep -rn "?v=2" src`); si no, los visitantes que ya entraron seguirán viendo
la versión vieja hasta un año.

Lo mismo con `css/trama.css` y `css/seminario.css`, que también llevan `?v=`.

**Imágenes del blog:** las cuatro que había se convirtieron a WebP (de 2.6–6.3
MB en PNG a 180–290 KB) y viven en `src/img/blog/`; `posts.image_url` apunta ahí.
El panel comprime en el navegador cada imagen nueva antes de subirla
(`comprimirImagen()`: lado mayor de 1600 px, WebP), así que las nuevas van a
Storage ya ligeras.

**Imagen para compartir:** todas las páginas llevan `og:image` y
`twitter:image`. Por omisión es `img/og-default.jpg`; TRAMA tiene la suya,
`img/trama/og-trama.jpg`. Las dos se generaron con Pillow (1200×630).

**`.htaccess` de la raíz:** redirige `www.` al dominio sin `www`, manda HSTS
(sin `includeSubDomains`) y `Referrer-Policy`. `admin/.htaccess` repite la
redirección de `www` porque tiene su propio `RewriteEngine` y no hereda las
reglas de la raíz. Probado en Apache local antes de publicar.

Colecciones/tablas en Supabase: posts de blog (con `slug`, `category`,
`excerpt`, `content`, `image_url`, `likes`, `dislikes`), cursos,
investigaciones y stats.

### Panel de administración

Uno solo: `src/admin/index.html`, que responde en `https://omarvaldez.com/admin/`.
Entra con Supabase Auth y los permisos salen de `perfiles` / `tiene_rol()`
(`database/supabase/00_roles.sql`). Es un archivo autocontenido: estilos y
scripts van dentro, y las librerías (Supabase, Quill, Chart.js) vienen de CDN.

`src/admin/.htaccess` declara `DirectoryIndex index.html` y redirige cualquier
`.php` de `/admin/` al panel, para que los marcadores viejos del panel PHP no
terminen en un 404.

### Micro sitios de curso

Tres secciones estáticas dentro de `src/`, independientes del sitio principal:

| Ruta | Qué es |
|---|---|
| `src/trama/` | Curso-taller de docencia bimodal para la FCA-UAS |
| `src/seminario/` | Seminario de Investigación Teórico, DISDE, ciclo 2026-2 |
| `src/manual-claude/` | Manual de Claude para investigadores, documento de consulta |

`src/seminario/` tiene dos páginas: `index.html` con la información del curso y
`diagnostico.html` con el cuestionario inicial. El formulario **no escribe en
ninguna tabla**: llama a la función `guardar_diagnostico_seminario()` de
Supabase, que es la única con permiso de escritura. Por eso las tablas del
diagnóstico no tienen ninguna política para `anon`, ni siquiera de INSERT: la
llave anónima que está a la vista en `/js/supabase-config.js` no sirve para leer
ni escribir nada de ahí. El SQL está en
`database/supabase/04_diagnostico_seminario.sql`.

Las respuestas se ven en el panel del blog, sección **Seminario**, que lee de
dos vistas (`v_seminario_alumnos` y `v_seminario_reactivos`). Las dos están
declaradas con `security_invoker = true`; sin eso una vista corre con los
permisos de quien la creó y **se salta el RLS** de las tablas que consulta, que
con PostgREST publicando las vistas dejaría los datos de los alumnos al alcance
de cualquiera. Si algún día se agregan vistas sobre datos personales, tienen que
llevar lo mismo.

`src/css/seminario.css` sigue el lenguaje visual de la landing —Inter, el
degradado azul-cian, el hero oscuro sobre gris pizarra, radios de 12 y 16— pero
es una **hoja independiente a propósito**: no hereda de `custom.css`, que no
está en el repo, así que un cambio allá no puede romper el micro sitio sin que
nadie se entere. Los tokens del principio son lo único que hay que tocar si
cambia la paleta del sitio.

`src/trama/`, en cambio, sí carga `custom.css` y usa la paleta azul marino y oro
del deck del curso-taller. Los dos micro sitios no comparten estilos.

### Por qué los documentos largos NO van en `posts`

El campo `content` de `posts` guarda **HTML semántico plano** —salida del editor
Quill del panel: `<h1>`, `<p>`, `<strong>`— y el blog lo inyecta dentro de la
plantilla del sitio. Un documento con su propio `<style>` no cabe ahí: reglas como
`* { margin: 0 }` y `body { background: ... }` se aplican a la página entera y
rompen el layout del sitio completo.

Además, el panel edita los posts con Quill. Si alguien abre en el editor un post
que trae HTML y CSS propios, Quill los descarta al guardar y el documento se
pierde sin aviso.

Por eso los documentos largos y con diseño propio van como página estática bajo
`src/`, igual que `/trama/`, `/seminario/` y `/manual-claude/`, y el blog lleva
una entrada corta que enlaza a la página. La entrada sí aparece en el listado;
el documento conserva su diseño y sus estilos de impresión.

### Firebase: retirado

Hubo un intento anterior con **Firebase** (`src/index-firebase.html`,
`src/firebase/`, `firebase.json`, `firestore.*`). Se borró en
octubre de 2026. Si encuentras menciones a Firebase en guías viejas de la raíz
o en `package.json`, son restos sin efecto: el sitio no lo usa.

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
no los toca (ver el truco de los dos commits en "Retiro del PHP").

### La portada vieja ya no existe

Hasta octubre de 2026, `src/index.html` era una portada vieja de mayo, distinta
de la de producción, y editarla habría reemplazado la portada real. Ahora es la
portada real. `src/main.js` y `src/styles/main.css`, que eran de esa portada
vieja, se borraron. El paso `npm run build` del workflow compila
`src/index.html` con Vite; su resultado (`dist/`) no se publica, pero si el
build falla el deploy no corre.

---

## Restricciones del entorno

- **Hosting:** SiteGround. No se puede cambiar el DNS ni migrar de proveedor.
- **Supabase:** la sesión tiene el conector oficial (herramientas
  `mcp__Supabase__*`). Úsalo para consultar y migrar en lugar de pedirle a Omar
  que pegue SQL. Antes de cualquier cambio destructivo, muéstrale el SQL.
- **Red de la sesión:** para verificar el sitio o bajar archivos del servidor,
  el environment necesita permitir `omarvaldez.com`, `*.omarvaldez.com` y
  `*.supabase.co`. En octubre de 2026 ya estaban permitidos.
- **FTP directo desde la sesión no sirve:** todo el egress pasa por un proxy
  HTTP/HTTPS. El camino de publicación es siempre commit → push → Action.
- Los archivos en la raíz del repo (`*.md`, `database/`) **no se despliegan**.

## Cómo correr migraciones

El SQL de Supabase vive en `database/supabase/` y **no se despliega**. Pégalo
en el SQL Editor del proyecto de Supabase, en el orden de su número.

**Nunca** pongas un script de migración o de diagnóstico dentro de `src/`. Todo
lo que esté ahí queda publicado en `https://omarvaldez.com/` y es alcanzable
por cualquiera. En 2026 hubo scripts PHP así, con llaves `?key=migrate123` en
texto plano; uno de ellos imprimía nombres y correos de estudiantes.

---

## Estado del trabajo

### Hecho

- Sección `/trama`: curso-taller de docencia bimodal para la FCA-UAS
  (`src/trama/index.html`, `src/css/trama.css`, kit descargable)
- Micro sitio del Seminario de Investigación Teórico (`src/seminario/`), con la
  información del curso y el diagnóstico inicial conectado a Supabase. Las
  respuestas se consultan desde el panel, sección Seminario, con promedios por
  alumno, panorama del grupo por reactivo y exportación a CSV.
- `src/admin/index.html`, el panel, entró al repo. Antes vivía sólo en el
  servidor. **El deploy manda sobre él**: cualquier cambio hecho a mano en el
  servidor se pierde en el siguiente push.
- El panel puede borrar una respuesta del seminario, con confirmación que dice
  el nombre. El desglose por reactivo se va solo, por la llave foránea con
  ON DELETE CASCADE.
- `esc()` del panel ahora escapa también las comillas. `textContent` ->
  `innerHTML` no las toca, y la función se usa dentro de atributos; con un
  formulario público alimentando la tabla del seminario, un nombre con
  comillas dobles rompía el renglón entero.
- Reparado el buscador del panel. `filterTable()` leía el caché con
  `window['cachedPosts']`, pero esas variables se declaran con `let` en el nivel
  superior del script y `let` **no** crea una propiedad de `window`. Devolvía
  `undefined` siempre, así que escribir en cualquier caja de búsqueda vaciaba la
  tabla en lugar de filtrarla.

### Retiro del PHP (octubre de 2026)

Omar decidió dejar un solo panel, el de Supabase, y **no conservar** los datos
del diagnóstico DISDE ni de estudiantes. Con eso se borró del repo, y por lo
tanto del servidor en el siguiente deploy:

- El panel PHP: `src/admin/*.php`, `includes/`, `estudiantes/`,
  `cursos-academicos/` y `assets/` (el panel de Supabase no usa nada de ahí).
- La API PHP completa (`src/api/`), salvo su `.htaccess`, que ahora niega todo.
- El formulario público `src/diagnosticoDISDE.php` y `src/bienvenida-disde.php`.
- El SQL del PostgreSQL de SiteGround (`database/*.sql`, `database/migrations/`)
  y la Edge Function `diagnostico-correo`, que nunca llegó a publicarse.
- De paso, el código muerto de Firebase.

`api/config/config.php` (credenciales del PostgreSQL) nunca estuvo en git, así
que el deploy no podía borrarlo. Se resolvió en dos commits: el primero lo subió
vacío para sobrescribirlo y el segundo lo quitó del repo, con lo que el deploy
lo eliminó del servidor. Sirve igual para cualquier otro archivo que sólo exista
en el servidor y haya que borrar sin entrar a SiteGround.

`src/api/.htaccess` se queda, negando todo, por si en `/public_html/api/` queda
algún otro archivo que nunca estuvo en git.

Del lado de Supabase, la secuencia de archivos queda así:

1. `00_roles.sql` — tabla `perfiles` y función `tiene_rol()`. Se queda: el
   panel la usa para los roles.
2. `01_academico.sql` — creó las cuatro tablas académicas. **Obsoleto**: lo
   deshace `07_retirar_sistema_academico.sql`. No lo vuelvas a correr.
3. `02_corregir_politicas.sql` — repara los like y acota la baja de suscriptores.
4. `04_diagnostico_seminario.sql` — micro sitio del seminario.
5. `05_corregir_reaccionar_post.sql`, `06_cerrar_tablas_del_blog.sql`.
6. `07_retirar_sistema_academico.sql` — borra las cuatro tablas académicas, que
   nunca se usaron, y con ellas el INSERT anónimo que seguía abierto en
   `diagnostico_disde_respuestas`.
7. `08_politicas_por_rol.sql` — **seguridad, urgente**. Las políticas
   originales del blog decían `auth.role() = 'authenticated'` y el registro de
   Supabase Auth estaba abierto: cualquiera que se creara una cuenta podía leer
   contactos y suscriptores o borrar posts. Pasa el contenido a admin/editor,
   los datos personales a sólo admin, y hace que las cuentas nuevas nazcan
   inactivas. Va acompañado de desactivar "Allow new users to sign up" en
   Authentication del panel de Supabase.
8. `09_limite_de_envios.sql` — tope de envíos para contacto y newsletter. Ver
   "Límite de envíos" más abajo.
9. `10_redes_sociales.sql` — borradores y conexión con LinkedIn. Ver "Redes
   sociales".
10. `11_multimedia_posts.sql` — columnas de multimedia en `posts`. Ver
   "Multimedia del blog".
11. `12_instagram.sql` — columna `imagenes` (láminas del carrusel) en
   `redes_publicaciones`. Ver "Instagram".

Aplicado el 10 de octubre de 2026: `07` y `08` corridos por Omar desde el SQL
Editor (el conector no puede: las sentencias de DDL se quedan esperando una
confirmación del usuario y vencen a los 60 s; las lecturas y los INSERT sí
funcionan). Comprobado después: cero políticas con `auth.role()`, las cuatro
tablas académicas ya no existen y la llave anónima sigue leyendo los posts
publicados, cursos y estadísticas.

Los avisos de Supabase que quedan sobre funciones `SECURITY DEFINER` que `anon`
puede ejecutar (`reaccionar_post`, `baja_suscriptor`,
`guardar_diagnostico_seminario`) son intencionales: son las únicas puertas de
escritura del público.

Pendiente fuera del repo:

- ~~Desactivar "Allow new users to sign up"~~. Hecho; comprobado el 11 de
  octubre de 2026: `/auth/v1/signup` responde 422 `signup_disabled`. Las
  cuentas del panel se crean con "Invite user". Ojo: `/auth/v1/settings` tardó
  en reflejar el cambio; la prueba fiable es intentar el registro.
- La protección contra contraseñas filtradas sólo existe en el plan Pro de
  Supabase; el proyecto está en el gratuito, así que no aplica.
- Eliminar la base PostgreSQL y su usuario desde Site Tools de SiteGround.
  SiteGround no tiene API pública, así que esto sólo lo puede hacer Omar.

### Trampa comprobada: `posts.id` es `uuid`, no `bigint`

`02_corregir_politicas.sql` se corrió con el valor por omisión y `reaccionar_post`
quedó creada esperando `bigint`. Al llamarla, Postgres responde
`operator does not exist: uuid = bigint`, y como `src/js/supabase-config.js` ya
usa la función, **los like del blog quedan rotos**. Lo repara
`05_corregir_reaccionar_post.sql`, que además cierra por permisos las cuatro
tablas de `01_academico.sql`.

Confirmado el 24 de agosto de 2026 contra el proyecto: los posts publicados
traen ids como `cee4f047-8cd1-4995-9154-23d1a44baf92`. Antes de escribir
cualquier función que reciba un id de `posts`, el tipo es `uuid`.

### Trampa comprobada: un UPDATE con WHERE necesita política de SELECT

`unsubscribe.html` da de baja con `update subscribers ... where email = ...` y
**no funciona**. Reproducido el 24 de agosto de 2026 con la configuración exacta
de producción: devuelve `UPDATE 0` y el suscriptor sigue activo.

En Postgres, un UPDATE cuyo WHERE lee una columna necesita ver esa fila, y con
RLS activo eso lo decide la política de **SELECT**. `anon` no tiene ninguna, así
que la fila es invisible para el filtro. La política de UPDATE dice
`using(true)`, pero nunca llega a evaluarse.

Mismo defecto que tenían los like: falla en silencio y la página muestra un
éxito que no corresponde a nada. Lo resuelve `baja_suscriptor()`, que es
SECURITY DEFINER. **Reparado el 10 de octubre de 2026**: `src/unsubscribe.html`
ya llama a `db.rpc('baja_suscriptor', { correo })`. Comprobado con un suscriptor
de prueba y la llave anónima: el UPDATE directo respondía 204 y lo dejaba
activo; la función lo da de baja aunque el correo traiga otras mayúsculas o
espacios.


### Límite de envíos de los formularios públicos

`09_limite_de_envios.sql` pone un trigger `BEFORE INSERT` en `contacts` y
`subscribers` que sólo aplica al rol `anon`: 3 mensajes y 5 suscripciones por
hora por IP, 30 y 60 por hora en total, más validación de correo y largo del
mensaje. La IP sale de `request.headers` (PostgREST) y se guarda como SHA-256 en
`limite_envios`, que nadie lee por la API y se vacía sola a las 24 horas.

Trampa: dentro de una función `SECURITY DEFINER`, `current_user` es el dueño de
la función, no `anon`. El rol de la petición se lee con
`current_setting('role', true)`.

Probado en un PostgreSQL 16 local simulando `anon` con encabezados: el cuarto
mensaje desde la misma IP falla, otra IP sí entra, el panel no tiene tope, el
duplicado de suscriptor sigue dando 23505 (que el sitio trata como éxito) y la
IP nunca queda en claro.

### Newsletter

Los correos del dominio salen por SiteGround, **no por Resend** (decisión de
Omar, octubre de 2026). El panel envía con la Edge Function `send-newsletter`
(`supabase/functions/send-newsletter/index.ts`), que manda por SMTP con
`nodemailer` a través del **puerto 465**: Supabase bloquea en sus Edge Functions
la salida a los puertos 25 y 587. Uno por segundo, por una sola conexión, para
no topar con el límite por hora de SiteGround. Con el límite de tiempo de una
Edge Function, eso alcanza para unos cientos de suscriptores por envío; si la
lista crece mucho más, habrá que repartir el envío.

Secretos (Supabase > Edge Functions > Secrets): `SMTP_HOST`, `SMTP_USER`,
`SMTP_PASS`, y opcionales `SMTP_PORT` y `NEWSLETTER_FROM`. Quedó un
`RESEND_API_KEY` de un intento anterior que ya no se usa.

La función se publica con el conector de Supabase (`deploy_edge_function`), que
sí funciona. Exige la sesión de un usuario de `perfiles` con rol admin o editor
activo; `verify_jwt` sigue en `false` porque la validación la hace la propia
función, y la llave anónima también es un JWT válido, así que activarlo no
protegería nada. Comprobado: con la llave anónima responde 401. Hasta octubre
de 2026 no revisaba quién la llamaba.

El enlace de baja lleva el correo en base64 de sus bytes UTF-8 y pasado por
`encodeURIComponent`: sin eso, un `+` del base64 llegaba como espacio a
`unsubscribe.html` y la baja fallaba.

### Redes sociales (LinkedIn; Instagram en su propia sección)

Decisiones de Omar (27 de octubre de 2026): el sitio es el centro; cada
entrada del blog se convierte en un borrador para redes que **Omar aprueba uno
por uno** (nada se publica solo) en su **perfil personal** de LinkedIn. Fase 2:
Instagram.

- `10_redes_sociales.sql`: `redes_publicaciones` (borradores, admin/editor),
  `redes_conexiones` (tokens; sin políticas, sólo la llave de servicio) y
  `redes_oauth_estados` (state del OAuth).
- Edge Functions `linkedin-auth` (conectar: OAuth con `openid profile
  w_member_social`, callback en la propia función, redirige a
  `/admin/#redes?linkedin=...`) y `linkedin-publicar` (Posts API `/rest/posts`,
  versión en `LINKEDIN_API_VERSION`, 202609 por omisión). Las dos con
  `verify_jwt = false` y validación de rol dentro; con la llave anónima
  responden 401.
- Secretos: `LINKEDIN_CLIENT_ID`, `LINKEDIN_CLIENT_SECRET`. La app de LinkedIn
  lleva los productos "Share on LinkedIn" y "Sign In with LinkedIn using OpenID
  Connect" (ambos sin revisión) y el redirect
  `https://mogmgtnkyazpsgslucrx.supabase.co/functions/v1/linkedin-auth`.
- El token dura ~60 días y no hay refresh token sin aprobación de LinkedIn: el
  panel muestra cuándo vence y "Reconectar".
- **Trampa:** el texto va en formato "little text". Los caracteres
  `\ | { } @ [ ] ( ) < > # * _ ~` se escapan con `\`; si no, LinkedIn responde
  201 y publica el texto **cortado** sin avisar. Los hashtags van como
  `{hashtag|\#|Tag}`. Lo hace `aLittleText()` en `linkedin-publicar`.
- LinkedIn no acepta WebP: el panel convierte la imagen a JPEG en el navegador
  (`imagenAJpegBase64`) y la manda a la función.
- LinkedIn no permite leer las publicaciones del perfil con estos permisos, así
  que "de LinkedIn al sitio" no se puede automatizar; Instagram sí lo permite
  (cuenta profesional, API con Instagram Login, sin página de Facebook).

### Instagram

Decisiones de Omar (28 de octubre de 2026): su cuenta ya es profesional; cada
entrada va como **carrusel con las láminas de su presentación** (Gemini
Notebook), aprobado uno por uno desde el panel, sección Redes. Por ahora
**no** se muestra su Instagram en el sitio.

- API: "Instagram API with Instagram Login" (`graph.instagram.com`, sin
  página de Facebook), permisos `instagram_business_basic` e
  `instagram_business_content_publish`. La app de Meta puede quedarse en modo
  desarrollo mientras sólo publique en la cuenta de Omar (agregada como
  evaluador de Instagram en Roles de la app).
- Edge Functions `instagram-auth` (OAuth, callback en la propia función,
  regresa a `/admin/#redes?instagram=...`) e `instagram-publicar`. Las dos con
  `verify_jwt = false` y validación de rol dentro; con la llave anónima
  responden 401. Secretos: `INSTAGRAM_APP_ID` e `INSTAGRAM_APP_SECRET` (los
  "de la app de Instagram", distintos del id de la app de Meta) y opcional
  `INSTAGRAM_API_VERSION` (`v23.0` por omisión).
- Redirect que va en la app de Meta:
  `https://mogmgtnkyazpsgslucrx.supabase.co/functions/v1/instagram-auth`.
- **El token sí se renueva** (a diferencia de LinkedIn): dura 60 días y
  `instagram-auth` lo refresca en la acción `estado` cuando le quedan menos de
  30 días y tiene más de 24 horas. El panel llama `estado` cada vez que se abre
  Redes. Si pasan 60 días sin abrirla, vence y hay que volver a conectar.
- Instagram sólo acepta **JPEG por URL pública**, con proporción entre 4:5 y
  1.91:1, y de 2 a 10 elementos por carrusel (todos toman la proporción del
  primero). El panel dibuja cada página del PDF con PDF.js, la recorta a ese
  rango si hace falta, la sube como JPEG de 1080 px a
  `images/multimedia/instagram/{slug}-{ts}/NN.jpg` y guarda la lista en
  `redes_publicaciones.imagenes`. Sin presentación, usa la imagen del
  artículo. `instagram-publicar` sólo acepta JPEG del bucket del proyecto.
- Los enlaces del pie de foto no se pueden abrir en Instagram: el texto dice
  "en el enlace de mi perfil", así que el perfil de Omar debe llevar
  omarvaldez.com.
- Publicar es asíncrono: un contenedor por lámina, uno CAROUSEL, esperar
  `status_code = FINISHED` y luego `media_publish`. Puede tardar hasta un
  minuto.

### Multimedia del blog (podcast, video, infografía y carrusel)

Decisión de Omar (28 de octubre de 2026): cada entrada aprobada se convierte
en podcast para su canal de Spotify, video para YouTube, infografía y carrusel
PDF, con su cuenta de Gemini Notebook (antes NotebookLM); las miniaturas las
hace ChatGPT con su foto como referencia.

- **No hay servidor que lo haga solo.** Gemini Notebook (cuentas personales),
  ChatGPT (la suscripción no da API) y Spotify for Creators no tienen API para
  esto, y los videos que se suben por la API de YouTube quedan privados hasta
  pasar una auditoría de Google. Por eso lo hace Claude **en la computadora de
  Omar**, con Claude in Chrome y sus sesiones abiertas: skill
  `.claude/skills/multimedia-blog/` (también se sube a claude.ai como skill).
- Flujo: panel → Blog → botón de multimedia → "Generar multimedia" deja
  `posts.multimedia_estado = 'pendiente'` → la skill la toma (`en_proceso`),
  genera, pide confirmación antes de publicar, sube a Spotify y YouTube, y
  guarda enlaces y archivos **desde el mismo diálogo del panel** (`listo`) →
  `post.html` muestra solo la sección "Escucha y mira" (`#multimedia`).
- La infografía y el PDF van al bucket `images`, carpeta `multimedia/`. En el
  artículo **nunca se enlazan directo** (Omar no quiere que se vea la dirección
  de Supabase, y un dominio propio para Storage es de paga): se abren en un
  visor dentro de la página (la presentación con PDF.js 3.11.174 de cdnjs,
  cargado sólo al abrirla) y se descargan como blob con nombre
  `{slug}-infografia.webp` / `{slug}-presentacion.pdf`.
- `11_multimedia_posts.sql` limita los enlaces: Spotify sólo
  `https://open.spotify.com/episode|show/...`, YouTube sólo `youtube.com/watch`,
  `/shorts` o `youtu.be`, y los archivos sólo del bucket del proyecto.
  `post.html` vuelve a extraer el id antes de armar cada iframe.
- El audio y el video salen del texto: si el artículo se corrige después, ya
  no coinciden. El panel lo advierte.
- YouTube: la skill marca "contenido alterado o sintético: Sí" y las
  descripciones dicen que el material se generó con IA.

### Caché de SiteGround para HTML

SiteGround guardaba en caché las páginas HTML: después de un deploy, quien
entraba sin parámetros seguía viendo la versión anterior (pasó con la portada
el 10 y 11 de octubre de 2026). Se resolvió con `Cache-Control: no-cache` para
`*.html` en `src/.htaccess`: comprobado que, tras un deploy, la URL sin
parámetros ya entrega la versión nueva sin vaciar nada. Si alguna vez vuelve a
pasar, Site Tools > Speed > Caching > Flush Cache. Para verificar un deploy,
compara con y sin `?nc=` aleatorio.

### Accesos a los micro sitios

La portada enlaza a `/trama/` y `/manual-claude/` desde el pie (HTML fijo, para
que los buscadores lo sigan) y TRAMA tiene una tarjeta en Cursos (tabla
`courses`, `sort_order` 0, editable desde el panel). `courses.icon` es
`varchar(10)`: sólo cabe un emoji, no HTML de un ícono.

### Sitemap

`src/sitemap.xml` lista las páginas fijas y los posts publicados. **No se
actualiza solo**: al publicar un post nuevo, agrégalo. La entrada del Manual de
Claude en el blog (`manual-claude-investigadores`) la borró Omar el 11 de
octubre de 2026 para rehacerla; cuando se vuelva a publicar, hay que agregarla
otra vez.

Aplicado por Omar el 10 de octubre de 2026. Comprobado en producción con la
llave anónima: un correo inválido y un mensaje vacío responden 400 sin guardar
nada, y una suscripción de prueba quedó registrada con la huella de la IP, así
que Supabase sí entrega los encabezados que el tope por IP necesita. Quedó un
suscriptor de prueba inactivo, `prueba-limite-claude@ejemplo.invalid`, que se
puede borrar sin riesgo.

---

## Convenciones

- **Idioma:** todo el contenido de cara al usuario va en **español de México**.
  No neutro, no de España.
- El sitio no usa emojis en el contenido publicado.
- Nada de PHP nuevo. Lo que necesite lógica de servidor va en Supabase.
- Toda tabla con datos de personas lleva RLS y `REVOKE` para `anon`; toda vista
  sobre ellas, `security_invoker = true`.
- Todo el sitio público está en `src/`. Si sospechas que alguien cambió algo
  directo en el servidor, compáralo antes de editar (descarga con `?nc=` para
  saltarte la caché).
