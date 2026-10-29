# Perfil de ResearchGate: propuesta y pasos por etapas

Perfil: https://www.researchgate.net/profile/Omar-Palazuelos

ResearchGate no tiene API ni deja leer perfiles desde un programa, así que no
pude revisar cómo está hoy el tuyo. Igual que con LinkedIn y ORCID, los
cambios los hace Claude **en tu computadora** con Claude in Chrome y tu sesión
abierta, por etapas y con tu confirmación.

Lo marcado **[POR CONFIRMAR]** necesita un dato tuyo. No lo inventé.

---

## Etapa 1. Nombre, foto e institución

- **Nombre:** tu dirección dice "Omar-Palazuelos", así que es probable que el
  perfil muestre sólo un apellido. Debe decir **Omar Valdez Palazuelos**, igual
  que en ORCID y en tus artículos (Configuración → Cuenta → Nombre). Cambiar el
  nombre puede cambiar también la dirección del perfil: si pasa, actualiza el
  enlace en tu sitio y en ORCID.
- **Foto:** la misma de LinkedIn y ORCID.
- **Institución actual:** Universidad Autónoma de Sinaloa (elígela de la lista,
  no la escribas a mano). Departamento: Facultad de Contaduría y
  Administración. Puesto: Profesor-Investigador.

## Etapa 2. Acerca de, disciplinas y temas

**Introducción** (ResearchGate es internacional: va en inglés y español):

> Research Professor at the School of Accounting and Administration, Autonomous University of Sinaloa (Mexico), and member of Mexico's National System of Researchers (Level I). I study the digital transformation of micro, small and medium-sized enterprises: digital presence, digital marketing and e-commerce; artificial intelligence in management; blockchain and digital finance in Latin America; frugal innovation and strategic intelligence. Open to research collaborations.
>
> Profesor-Investigador de la Universidad Autónoma de Sinaloa (SNII Nivel I). Investigo la transformación digital de las MiPyMES, la inteligencia artificial aplicada a la gestión, el blockchain y las finanzas digitales en América Latina, y la innovación frugal. Abierto a colaborar en investigación.

**Disciplinas:** elige de la lista de ResearchGate las más cercanas a
Administración de empresas (Business Administration), Gestión (Management) y
Sistemas de información (Information Systems).

**Habilidades y experiencia** (en inglés, para que te encuentren colegas de
otros países): Digital Transformation, SMEs, Digital Marketing, E-commerce,
Artificial Intelligence, Blockchain, Fintech, Strategic Management, Frugal
Innovation, Survey Research, Structural Equation Modeling **[POR CONFIRMAR si
lo usas]**, Bibliometrics.

## Etapa 3. Experiencia, educación y premios

Los mismos datos que en LinkedIn y ORCID (en `perfil-linkedin.md`):
- Universidad Autónoma de Sinaloa, Profesor-Investigador de Tiempo Completo.
  **[POR CONFIRMAR: desde]**
- Universidad Nacional de Colombia, estancia de investigación.
  **[POR CONFIRMAR: fechas]**
- Doctorado en Administración, Maestría en Informática Administrativa,
  Licenciatura en Informática. **[POR CONFIRMAR: institución y años]**
- Premio o distinción: Investigador Nacional Nivel I (SNII, SECIHTI).
  **[POR CONFIRMAR: vigencia]**

## Etapa 4. Publicaciones: reclamar, agregar y quitar duplicados

1. **Reclamar autoría.** ResearchGate crea páginas de autor para obras que
   aún no son de nadie (por ejemplo "O. Valdez Palazuelos" u "Omar Valdez").
   Revisa las sugerencias de "¿Eres tú el autor?" y confirma sólo las tuyas.
2. **Agregar lo que falte.** Agregar investigación → Publicación → buscar por
   DOI. Los 7 artículos con DOI:
   - 10.34140/BJBV7N3-029 (Brazilian Journal of Business, 2025)
   - 10.36825/RITI.13.30.008 (RITI, 2025)
   - 10.52080/RVGLUZ.28.102.10 (Revista Venezolana de Gerencia, 2023)
   - 10.31876/rcs.v29i1.39736 (Revista de Ciencias Sociales, 2023)
   - 10.36825/RITI.07.14.023 (RITI, 2019)
   - 10.36825/RITI.06.11.013 (RITI, 2018)
   - 10.36825/RITI.05.10.017 (RITI, 2017)

   Los 8 capítulos sin DOI se agregan como "Capítulo" con los datos de
   `orcid-capitulos.bib`.
3. **Duplicados.** Si una obra aparece dos veces, ResearchGate permite
   fusionarlas. Que Claude te las muestre antes de tocarlas.
4. **Textos completos.** Subir el PDF hace que te lean y citen más, pero
   **sólo cuando la licencia lo permite**:
   - Revistas de acceso abierto con licencia Creative Commons (revisa la página
     del artículo): se puede subir la versión publicada.
   - Revistas con suscripción y capítulos de libro: sube como "privado" (sólo lo
     ves tú y lo compartes si te lo piden) o no lo subas.
   Que Claude revise la licencia de cada artículo en la página de la revista
   antes de subirlo.

## Etapa 5. Ajustes finales

- Enlaza tu ORCID (Configuración → ORCID) para que tus obras nuevas se
  sincronicen.
- Sigue a tus coautores y a colegas de tus líneas: en ResearchGate, tus
  publicaciones llegan a quienes te siguen.
- Revisa que el correo de avisos sea el que lees.

---

## Mensaje para Claude de escritorio (con Claude in Chrome)

Adjunta este archivo y `docs/orcid-capitulos.bib`, abre tu perfil de
ResearchGate en Chrome con la sesión iniciada, y pega:

```
Vas a actualizar mi perfil de ResearchGate
(researchgate.net/profile/Omar-Palazuelos) con Claude in Chrome, usando el
archivo perfil-researchgate.md y el BibTeX orcid-capitulos.bib que te adjunto.

Reglas:
- Antes de empezar, revisa mi perfil actual y dime qué hay, qué falta, qué
  obras están duplicadas y qué sugerencias de autoría tengo pendientes. No
  cambies nada en ese primer paso.
- Trabaja por las 5 etapas del archivo. Al terminar cada una, detente, dime
  qué cambiaste y espera mi "sí" antes de seguir.
- Antes de guardar cada elemento, muéstrame el texto exacto.
- Nunca borres, fusiones ni rechaces una obra sin preguntarme.
- Sólo reclama autoría de obras que estén en mi lista o que yo confirme.
- Antes de subir el texto completo de un artículo, revisa su licencia en la
  página de la revista y dime qué encontraste. Si no es Creative Commons, no
  lo subas público.
- No inventes datos: lo marcado "POR CONFIRMAR" me lo preguntas.
- Si te pide iniciar sesión o un código, detente y avísame. Nunca escribas
  contraseñas.
- No publiques ni envíes mensajes a nadie en mi nombre.

Empieza por revisar el perfil actual.
```
