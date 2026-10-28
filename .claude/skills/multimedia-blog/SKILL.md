---
name: multimedia-blog
description: >
  Convierte las entradas del blog de omarvaldez.com que Omar aprobó en el panel
  ("Generar multimedia") en podcast para Spotify, video para YouTube, infografía
  y carrusel PDF con Gemini Notebook (antes NotebookLM); hace las miniaturas en
  ChatGPT con la foto de Omar; sube el audio a Spotify for Creators y el video a
  YouTube Studio desde Chrome, y guarda los enlaces en el panel para que el
  artículo muestre la sección "Escucha y mira". Úsala cuando Omar diga
  "multimedia del blog", "/multimedia-blog", "genera el podcast y el video del
  artículo", "procesa los pendientes de multimedia" o algo equivalente.
---

# Multimedia del blog

Corre en la computadora de Omar, no en la nube: Gemini Notebook, ChatGPT,
Spotify for Creators y YouTube Studio se manejan con **sus sesiones abiertas
en Chrome**, porque ninguno tiene una API utilizable para cuentas personales
(YouTube sí, pero sin auditoría de Google los videos subidos por API quedan
privados). Sólo la base de datos se toca por conector.

Todo lo que se publique va en **español de México** y sin emojis.

## Configuración

Llena estos valores la primera vez (pregúntaselos a Omar si siguen así) y
guárdalos editando este archivo:

| Dato | Valor |
|---|---|
| Nombre del podcast en Spotify | `[POR DEFINIR]` |
| Nombre del canal de YouTube | `[POR DEFINIR]` |
| Lista de reproducción de YouTube para el blog | `[POR DEFINIR, opcional]` |
| Proyecto de ChatGPT con la foto de Omar | `[POR DEFINIR: nombre del proyecto o GPT]` |
| Proyecto de Supabase | `mogmgtnkyazpsgslucrx` |

## Lo que necesitas antes de empezar

1. **Conector de Supabase** (herramientas `execute_sql`) con el proyecto de
   arriba. Sólo para leer las entradas pendientes y marcar su estado.
2. **Gemini Notebook.** Si tienes las herramientas del servidor de NotebookLM
   (`notebook_create`, `notebook_add_text`, `audio_overview_create`,
   `video_overview_create`, `slide_deck_create`, `infographic_create`,
   `studio_status`), úsalas: es lo mismo que hace la skill
   `moodle-content-creator`. Si no, hazlo en Chrome en
   `notebooklm.google.com`.
3. **Claude in Chrome** con sesión abierta en `chatgpt.com`,
   `creators.spotify.com`, `studio.youtube.com` y `omarvaldez.com/admin/`.
   Si alguna pide iniciar sesión, detente y pídele a Omar que entre; **nunca
   escribas contraseñas**.

## Reglas

- **Una entrada a la vez**, la más antigua en `pendiente`.
- **Una sola confirmación antes de generar** y **otra antes de publicar**: le
  muestras a Omar títulos, descripciones y miniaturas, y esperas su "sí". Nada
  sale a Spotify o YouTube sin esa segunda confirmación.
- No inventes datos: el podcast y el video se basan sólo en el artículo.
- Si algo falla y no se puede seguir, marca el error (paso 9) y explícalo.

## Flujo

### 1. Tomar la entrada pendiente

```sql
select id, slug, title, excerpt, category, image_url, published, content
from posts
where multimedia_estado = 'pendiente'
order by created_at
limit 1;
```

Si no hay ninguna, dile a Omar que no hay pendientes (se aprueban en el panel:
Blog → botón de multimedia → "Generar multimedia") y termina.

Márcala para que no la tome otra corrida:

```sql
update posts set multimedia_estado = 'en_proceso', multimedia_error = null,
       multimedia_actualizado = now()
where id = '<id>' and multimedia_estado = 'pendiente'
returning slug;
```

`posts.id` es `uuid`. Si el `returning` viene vacío, otra corrida ya la tomó:
termina.

### 2. Preparar la fuente

Convierte `content` (HTML del editor Quill) a texto plano conservando los
subtítulos (`<h2>` → línea propia) y las listas. La fuente para Gemini
Notebook es:

```
{title}

{excerpt}

{texto del artículo}

Autor: Dr. Omar Valdez Palazuelos, profesor-investigador de la Facultad de
Contaduría y Administración de la Universidad Autónoma de Sinaloa.
Artículo completo: https://omarvaldez.com/post.html?slug={slug}
```

### 3. Confirmar y generar en Gemini Notebook

Pregunta una sola vez:

> Voy a generar para "{title}": podcast, video explicativo, presentación e
> infografía en Gemini Notebook. ¿Confirmas?

Con el sí:

1. Crea el cuaderno `Blog: {title}` y agrega la fuente del paso 2 como texto.
2. Lanza las cuatro generaciones en español, con los prompts de
   `references/prompts.md`:
   - Audio: formato conversación a fondo (`deep_dive`), prompt **Podcast**.
   - Video: formato explicativo (`explainer`), prompt **Video**.
   - Presentación: detallada, prompt **Presentación**.
   - Infografía: orientación **vertical** (sirve para el blog y para
     Instagram), detalle alto, prompt **Infografía**.
3. Mientras se generan (tardan varios minutos), avanza con los pasos 4 y 5.
4. Cuando estén listos, descárgalos a una carpeta
   `multimedia-blog/{slug}/` en Descargas: `podcast.*`, `video.mp4`,
   `presentacion.pdf`, `infografia.png`.

Si una generación falla, reintenta esa sola una vez. Si vuelve a fallar, sigue
con las demás y avísale a Omar al final.

### 4. Títulos y descripciones

Usa las plantillas de `references/textos.md` para el episodio de Spotify y el
video de YouTube. Títulos de 60 a 70 caracteres como máximo, con la idea
principal al inicio.

### 5. Miniaturas en ChatGPT

1. Abre `chatgpt.com` y entra al proyecto con la foto de Omar (Configuración).
2. Llena el prompt de `references/miniaturas.md` y pide primero la de
   **YouTube (horizontal 16:9)** y luego la de **Spotify (cuadrada)**.
3. Revisa cada imagen antes de descargarla: que el texto esté **bien escrito**
   (acentos y eñes incluidos), que se lea en tamaño pequeño y que el rostro se
   parezca a Omar. Si algo falla, pide la corrección en el mismo chat; máximo
   tres intentos por imagen.
4. Descárgalas a la carpeta de la entrada: `miniatura-youtube.png` y
   `miniatura-spotify.png`.

YouTube pide 16:9 y como máximo 2 MB. Si ChatGPT entrega otra proporción o la
imagen pesa más, dile a Omar en lugar de deformarla.

### 6. Revisión de Omar antes de publicar

Muéstrale en un solo mensaje: los dos títulos, las dos descripciones, las dos
miniaturas y qué archivos se generaron. Pregunta:

> ¿Publico el episodio en Spotify y el video en YouTube así?

Si la entrada todavía es borrador (`published = false`), agrega: "El artículo
aún no está publicado; el enlace de las descripciones dará error hasta que lo
publiques. ¿Publico ya o subo el video como No listado?". Haz lo que diga.

### 7. Publicar en Spotify for Creators

En `creators.spotify.com`, en el podcast de la configuración:

1. Nuevo episodio → sube `podcast.*`.
2. Título y descripción del paso 4. Imagen del episodio:
   `miniatura-spotify.png`.
3. Publicar ahora.
4. Espera a que el episodio aparezca publicado y copia su enlace público
   (Compartir → Copiar enlace). Debe empezar con
   `https://open.spotify.com/episode/`. Los enlaces de
   `creators.spotify.com` **no** sirven para el artículo.

### 8. Publicar en YouTube Studio

En `studio.youtube.com`:

1. Crear → Subir video → `video.mp4`.
2. Título y descripción del paso 4. Miniatura: `miniatura-youtube.png`.
3. Lista de reproducción de la configuración, si hay.
4. Público: "No, no es contenido para niños".
5. **Contenido alterado o sintético: Sí.** Las voces son generadas con IA y
   suenan reales; la descripción también lo dice. Es la política de YouTube y
   cuida la reputación académica de Omar.
6. Visibilidad: Pública (o No listado, si así lo pidió en el paso 6).
7. Copia el enlace del video: `https://youtu.be/...` o
   `https://www.youtube.com/watch?v=...`.

### 9. Guardar todo en el panel

En `omarvaldez.com/admin/` → Blog → botón de multimedia (ícono de película)
de la entrada:

1. Pega el enlace de Spotify y el de YouTube.
2. Sube `infografia.png` en "Infografía" y `presentacion.pdf` en
   "Presentación o carrusel".
3. Guardar enlaces. El panel valida los enlaces, sube los archivos y deja la
   entrada en `listo`.

Comprueba con el conector:

```sql
select multimedia_estado, podcast_url, video_url, infografia_url, carrusel_url
from posts where id = '<id>';
```

Si algo no se pudo terminar, deja constancia para que Omar lo vea en el panel:

```sql
update posts set multimedia_estado = 'error',
       multimedia_error = '<qué falló, en una línea>',
       multimedia_actualizado = now()
where id = '<id>';
```

### 10. Cierre

Dile a Omar, en pocas líneas:

- Los enlaces de Spotify y YouTube.
- Que el artículo ya muestra "Escucha y mira" (si está publicado:
  `https://omarvaldez.com/post.html?slug={slug}#multimedia`).
- Que `presentacion.pdf` puede subirlo a LinkedIn como documento (carrusel):
  la API de LinkedIn que usa el panel no lo publica.
- Que `infografia.png` queda lista para Instagram.
- Lo que haya fallado.

Si hay más entradas pendientes, pregunta si sigue con la siguiente.
