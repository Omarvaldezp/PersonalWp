# Perfil de ORCID: propuesta y pasos por etapas

Registro: https://orcid.org/0000-0001-8585-9305

ORCID no tiene una forma de editar un registro personal desde un programa: su
API de escritura es sólo para instituciones miembro. Igual que con LinkedIn,
los cambios los hace Claude **en tu computadora** con Claude in Chrome y tu
sesión de ORCID abierta, por etapas y con tu confirmación.

Lo marcado **[POR CONFIRMAR]** necesita un dato tuyo. No lo inventé.

---

## Etapa 1. Datos personales y visibilidad

- **Nombre publicado:** Omar Valdez Palazuelos
- **También conocido como** (ayuda a que te encuentren y a juntar tus obras):
  - Omar Valdez-Palazuelos
  - O. Valdez Palazuelos
  - Valdez Palazuelos, Omar
- **País:** México
- **Visibilidad:** todo en "Cualquiera" (Everyone), salvo tu correo.
- **Correo:** agrega `omar.valdez@uas.edu.mx` y verifícalo. Puede quedar
  privado; sirve para que la UAS y las editoriales te asocien.

## Etapa 2. Biografía y palabras clave

**Biografía** (en español y en inglés; ORCID lo leen editores de todo el mundo):

> Profesor-Investigador de la Facultad de Contaduría y Administración de la Universidad Autónoma de Sinaloa (México) y miembro del Sistema Nacional de Investigadoras e Investigadores (Nivel I). Lidero el cuerpo académico de Gestión Estratégica. Investigo la transformación digital de las micro, pequeñas y medianas empresas: presencia digital, marketing digital y comercio electrónico; inteligencia artificial aplicada a la gestión; blockchain, Bitcoin y finanzas digitales, y su impacto social en América Latina; innovación frugal, economía circular digital e inteligencia estratégica. Realicé una estancia de investigación en la Universidad Nacional de Colombia.
>
> Research Professor at the School of Accounting and Administration, Autonomous University of Sinaloa (Mexico), and member of Mexico's National System of Researchers (Level I). I lead the Strategic Management research group. My research examines the digital transformation of micro, small and medium-sized enterprises: digital presence, digital marketing and e-commerce; artificial intelligence in management; blockchain, Bitcoin and digital finance and their social impact in Latin America; frugal innovation, digital circular economy and strategic intelligence.

**Palabras clave** (una por renglón en ORCID):
- Transformación digital / Digital transformation
- MiPyMES / SMEs
- Inteligencia artificial / Artificial intelligence
- Blockchain
- Fintech
- Marketing digital / Digital marketing
- Comercio electrónico / E-commerce
- Innovación frugal / Frugal innovation
- Gestión estratégica / Strategic management

## Etapa 3. Sitios web y otros identificadores

**Sitios web y redes:**
- Sitio personal: https://omarvaldez.com
- Google Académico: https://scholar.google.com/citations?user=OXXlj1IAAAAJ
- ResearchGate: https://www.researchgate.net/profile/Omar-Palazuelos
- LinkedIn: https://www.linkedin.com/in/phd-omarvaldez (cámbialo si actualizas la URL)

**Otros identificadores:** conecta los que tengas con el asistente oficial
(Identificadores personales → Agregar → vincular):
- Scopus Author ID **[POR CONFIRMAR si tienes]**
- ResearcherID / Web of Science **[POR CONFIRMAR si tienes]**
- CVU de SECIHTI: no tiene asistente, pero puedes mencionarlo en la biografía si quieres.

## Etapa 4. Empleo y educación

Usa siempre la organización del buscador de ORCID (la que trae identificador
ROR), no la escribas a mano: así cuenta como la misma institución para todos.

**Empleo:**
- Universidad Autónoma de Sinaloa, Culiacán, México. Facultad de Contaduría y
  Administración. Profesor-Investigador de Tiempo Completo.
  **[POR CONFIRMAR: fecha de inicio]** – actualidad.
- Universidad Nacional de Colombia. Estancia de investigación (en la sección
  "Invited positions", si aparece; si no, en Empleo).
  **[POR CONFIRMAR: ciudad y fechas]**

**Educación y calificaciones:** **[POR CONFIRMAR: institución y años]**
- Doctorado en Administración
- Maestría en Informática Administrativa
- Licenciatura en Informática

## Etapa 5. Distinciones, membresías y servicios

- **Distinción:** Investigador Nacional Nivel I, Sistema Nacional de
  Investigadoras e Investigadores, SECIHTI. **[POR CONFIRMAR: vigencia]**
- **Membresía:** IEEE, miembro. **[POR CONFIRMAR: desde]**
- **Membresía:** IIDISE. **[POR CONFIRMAR: nombre completo y desde]**
- **Servicio:** líder del cuerpo académico de Gestión Estratégica, UAS.
- **Servicio:** si eres revisor de revistas o comités, agrégalo aquí.
  **[POR CONFIRMAR]**

## Etapa 6. Obras

**Artículos con DOI:** agrégalos con **Agregar obras → Agregar DOI**, uno por
uno. ORCID trae los datos completos del registro de la revista. Si alguno ya
está, ORCID lo agrupa y no lo duplica.

1. 10.34140/BJBV7N3-029 (Brazilian Journal of Business, 2025)
2. 10.36825/RITI.13.30.008 (RITI, 2025)
3. 10.52080/RVGLUZ.28.102.10 (Revista Venezolana de Gerencia, 2023)
4. 10.31876/rcs.v29i1.39736 (Revista de Ciencias Sociales, 2023)
5. 10.36825/RITI.07.14.023 (RITI, 2019)
6. 10.36825/RITI.06.11.013 (RITI, 2018)
7. 10.36825/RITI.05.10.017 (RITI, 2017)

**Capítulos de libro sin DOI:** importa el archivo `docs/orcid-capitulos.bib`
con **Agregar obras → Importar BibTeX**. Revisa cada uno antes de guardarlo:
faltan los títulos de varios libros y los ISBN. **[POR CONFIRMAR]**

**Después de agregar todo:**
- Activa **Crossref** como organización de confianza para que tus artículos
  nuevos lleguen solos cuando la revista registre tu ORCID.
- Busca **Google Académico** y **ResearchGate**: no se conectan directo a
  ORCID, pero puedes exportar desde Google Académico en BibTeX lo que falte.

---

## Mensaje para Claude de escritorio (con Claude in Chrome)

Adjunta este archivo y `docs/orcid-capitulos.bib`, abre tu registro de ORCID
en Chrome con la sesión iniciada, y pega:

```
Vas a actualizar mi registro de ORCID (orcid.org/0000-0001-8585-9305) con
Claude in Chrome, usando el archivo perfil-orcid.md y el BibTeX
orcid-capitulos.bib que te adjunto.

Reglas:
- Trabaja por las 6 etapas del archivo. Al terminar cada una, detente, dime
  qué cambiaste y espera mi "sí" antes de seguir.
- Antes de empezar, revisa lo que ya tiene mi registro y dime qué hay, qué
  falta y qué está duplicado. No cambies nada en ese primer paso.
- Antes de guardar cada elemento, muéstrame el texto exacto.
- Nunca borres nada sin preguntarme. Si hay obras duplicadas, enséñamelas y
  yo decido cuál se queda.
- No inventes datos: lo marcado "POR CONFIRMAR" me lo preguntas; si no lo sé,
  sáltalo.
- Para instituciones, usa siempre la sugerencia del buscador de ORCID.
- Si te pide iniciar sesión o un código, detente y avísame. Nunca escribas
  contraseñas.

Empieza por revisar el registro actual.
```
