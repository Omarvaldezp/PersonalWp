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

Archivos que **solo existen en el servidor** (`/public_html/`), no en git:

| Ruta | Qué es |
|---|---|
| `index.html` | Landing real, con carga dinámica desde Supabase |
| `post.html` | Artículo de blog individual (`?slug=...`) |
| `css/custom.css` | Estilos de todo el sitio |
| ~~`js/supabase-config.js`~~ | **Ya está en el repo**, en `src/js/`. El deploy manda sobre él |
| `js/app.js` | Lógica del landing (formularios, filtros, animaciones) |
| ~~`admin/index.html`~~ | **Ya está en el repo**, en `src/admin/`. El deploy manda sobre él |
| `privacidad.html`, `img/` | Aviso de privacidad y assets |

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
(los del sitio público que solo viven en el servidor, y `api/config/config.php`)
no los toca.

### Bomba de tiempo: `src/index.html`

El `src/index.html` del repo es la landing estática vieja (mayo), no la de
producción. Hoy no se sube porque no ha cambiado y la acción solo envía lo
modificado. Pero:

- Si alguien lo **edita**, el deploy pisa la landing real.
- Si alguien lo **borra**, el deploy borra la landing real.

`src/main.js` y `src/styles/main.css` van con él: son el JavaScript y los
estilos de esa landing vieja, y el paso `npm run build` del workflow falla si
faltan (Vite compila `src/index.html`). Tampoco los borres por separado.

La solución correcta es **reemplazar su contenido por el de producción, byte por
byte**, no borrarlo. Requiere bajar el archivo del servidor.

### Protección pendiente en el workflow

Falta agregar a la sección `exclude:` del workflow (requiere permiso
`workflows` en la GitHub App, que la sesión normalmente no tiene):

```yaml
exclude: |
  index.html
  # ...resto de las exclusiones existentes
```

---

## Restricciones del entorno

- **Hosting:** SiteGround. No se puede cambiar el DNS ni migrar de proveedor.
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

`src/api/.htaccess` se queda a propósito: `api/config/config.php` (credenciales
del PostgreSQL) nunca estuvo en git, así que el deploy no lo borra. Mientras
alguien no lo elimine a mano, ese `.htaccess` impide alcanzarlo por HTTP.

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

Pasos manuales que el repo no puede hacer (los hace Omar):

- Correr `07_retirar_sistema_academico.sql` en el SQL Editor de Supabase.
- Borrar desde el Administrador de archivos de SiteGround la carpeta
  `/public_html/api/` completa (incluye `config/config.php`).
- Eliminar la base PostgreSQL y su usuario desde Site Tools de SiteGround.

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
SECURITY DEFINER; falta cambiar la línea en `unsubscribe.html`, que no está en
el repo.


### Pendiente, por prioridad

1. Sincronizar los archivos de producción hacia el repo (`index.html`,
   `post.html`, `css/custom.css`, `js/app.js`, `privacidad.html`,
   `unsubscribe.html`, `img/`). Desactiva además la bomba de `src/index.html`.
2. **Reparar la baja del newsletter.** `unsubscribe.html` debe llamar a
   `baja_suscriptor()` en vez de escribir directo. Requiere bajar el archivo
   del servidor. Ver la trampa comprobada más arriba.
3. Limitar la frecuencia de los `POST` de contacto y newsletter (hoy sin tope)

---

## Convenciones

- **Idioma:** todo el contenido de cara al usuario va en **español de México**.
  No neutro, no de España.
- El sitio no usa emojis en el contenido publicado.
- Nada de PHP nuevo. Lo que necesite lógica de servidor va en Supabase.
- Toda tabla con datos de personas lleva RLS y `REVOKE` para `anon`; toda vista
  sobre ellas, `security_invoker = true`.
- Antes de proponer cambios al sitio público, recuerda que muchos archivos no
  están aquí: pídelos o baja del servidor, no los reconstruyas de memoria.
