# Perfil de Google Académico: propuesta y pasos por etapas

Perfil: https://scholar.google.com/citations?user=OXXlj1IAAAAJ

Google Académico no tiene API y bloquea la lectura automática, así que no pude
revisar cómo está hoy tu perfil. Los cambios los hace Claude **en tu
computadora** con Claude in Chrome y tu sesión de Google abierta, por etapas y
con tu confirmación.

Lo marcado **[POR CONFIRMAR]** necesita un dato tuyo. No lo inventé.

---

## Etapa 1. Datos del perfil

Perfil → ícono de lápiz junto a tu nombre:

- **Nombre:** Omar Valdez Palazuelos
- **Afiliación:** Profesor-Investigador, Universidad Autónoma de Sinaloa
- **Correo para verificar:** `omar.valdez@uas.edu.mx`. Google manda un enlace;
  al confirmarlo, tu perfil muestra "Correo verificado de uas.edu.mx". Sin eso,
  el perfil pesa menos y Google lo muestra menos en los resultados.
- **Áreas de interés** (máximo 5; cada una es un enlace que agrupa a
  investigadores del mismo tema, por eso en inglés):
  1. Digital Transformation
  2. SMEs
  3. Artificial Intelligence
  4. Blockchain
  5. Digital Marketing
- **Página principal:** `https://omarvaldez.com`
- **Foto:** la misma de LinkedIn, ORCID y ResearchGate.
- **Perfil público:** activado.

## Etapa 2. Revisar las obras que ya tiene

Antes de agregar nada, Claude revisa la lista completa y te dice:
- Qué obras **no son tuyas** (pasa seguido con apellidos comunes). Se quitan
  con "Eliminar", sólo con tu visto bueno.
- Qué obras aparecen **duplicadas** (por ejemplo, el artículo y su versión en
  un repositorio). Se juntan con "Combinar", que suma sus citas.
- Qué obras tienen **datos incompletos o mal escritos** (año, revista,
  acentos, autores). Se corrigen abriendo la obra y con el lápiz.

## Etapa 3. Agregar lo que falte

**Agregar artículos** (botón +):
1. **Buscar artículos:** busca por título; si aparece, se agrega con sus citas.
   Los 7 artículos con DOI:
   - Validación de un instrumento de medición para validar el grado de
     inteligencia estratégica en organizaciones del sector salud (Brazilian
     Journal of Business, 2025)
   - Economía circular digital: aprovechamiento de tecnologías accesibles para
     impulsar la sostenibilidad en MiPyMES y emprendimientos locales (RITI, 2025)
   - Presencia digital en las MiPyMES con establecimiento físico en México
     (Revista Venezolana de Gerencia, 2023)
   - Liderazgo transformacional y su relación con la felicidad en el trabajo
     (Revista de Ciencias Sociales, 2023)
   - Aprovechamiento del marketing digital como estrategia para generar ventaja
     competitiva en la pequeña empresa de Sinaloa (RITI, 2019)
   - La exportación de software como estrategia de internacionalización de las
     PyMES (RITI, 2018)
   - El comercio electrónico como estrategia de internacionalización de las
     PyMES (RITI, 2017)
2. **Agregar manualmente:** sólo lo que la búsqueda no encuentre (sobre todo
   capítulos de libro), con los datos de `orcid-capitulos.bib`. Tipo:
   "Capítulo de libro".

Google sólo cuenta citas de obras que encuentra en internet. Si tus capítulos
están publicados en línea (repositorio de la UAS, la editorial), dale a Claude
la dirección para agregarla a la obra.

## Etapa 4. Configuración que evita sorpresas

- **Actualizaciones de artículos:** elige **"No actualizar automáticamente;
  enviarme un correo para revisar las actualizaciones"**. Con la opción
  automática, Google puede agregarte obras de otra persona con un nombre
  parecido, y eso se nota en evaluaciones como el SNII.
- **Alertas:** activa "Artículos nuevos relacionados con mi investigación" y
  "Citas nuevas de mis artículos".
- **Coautores:** agrega a tus coautores que tengan perfil (Ovalles Toledo,
  Sandoval Barraza, Bueno Camacho y otros); aparecen en tu columna derecha.

## Etapa 5. Cerrar el ciclo

- Pon el enlace de tu perfil en ORCID, ResearchGate, LinkedIn y tu sitio (en
  tu sitio ya está).
- Revisa que tu perfil aparezca al buscar "Omar Valdez Palazuelos" en Google
  Académico. Un perfil nuevo o recién verificado puede tardar días en salir.

---

## Mensaje para Claude de escritorio (con Claude in Chrome)

Adjunta este archivo y `docs/orcid-capitulos.bib`, abre tu perfil de Google
Académico en Chrome con tu sesión de Google iniciada, y pega:

```
Vas a actualizar mi perfil de Google Académico
(scholar.google.com/citations?user=OXXlj1IAAAAJ) con Claude in Chrome, usando
el archivo perfil-google-academico.md y el BibTeX orcid-capitulos.bib que te
adjunto.

Reglas:
- Trabaja por las 5 etapas del archivo. Al terminar cada una, detente, dime
  qué cambiaste y espera mi "sí" antes de seguir.
- En la etapa 2 sólo revisas y me presentas una lista: obras que no parecen
  mías, duplicadas y con datos incompletos. No elimines, combines ni corrijas
  nada hasta que yo te diga cuáles.
- Antes de guardar cada dato o agregar cada obra, muéstrame el texto exacto.
- No inventes datos: lo que falte me lo preguntas.
- Si te pide iniciar sesión, un código o verificar el correo, detente y
  avísame. Nunca escribas contraseñas.

Empieza por la etapa 1.
```
