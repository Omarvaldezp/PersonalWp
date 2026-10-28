# Miniaturas con ChatGPT

Se hacen en el proyecto de ChatGPT que tiene la foto de Omar como referencia
(ver la configuración de `SKILL.md`). Si ChatGPT no tiene la foto a la mano,
detente y pídele a Omar que la suba al proyecto; no uses otra cara.

Antes de llenar el prompt, decide:

- `{texto_miniatura}`: 2 a 5 palabras, la idea más llamativa del artículo. No
  es el título completo. Ejemplos: "Buen Fin sin tienda en línea",
  "¿48% u 8%?", "46 horas en enero".
- `{escena}`: un objeto o lugar concreto del tema, fácil de reconocer en
  pequeño (una cortina de local con el celular en primer plano, una gráfica
  que sube, una moneda de bitcoin sobre una mesa de trabajo...). Nada de
  logotipos de marcas reales.
- `{expresion}`: la que pida el tema (curiosidad, sorpresa contenida,
  seriedad, confianza).

## YouTube (horizontal 16:9)

```
Crea la miniatura de YouTube para un video sobre "{title}".

- Formato horizontal 16:9.
- Usa mi foto de referencia: que sea claramente yo, de medio cuerpo, en el
  tercio derecho, mirando a cámara con expresión de {expresion}. Ropa formal
  casual.
- A la izquierda, el texto «{texto_miniatura}» en letras muy grandes,
  gruesas y blancas con contorno oscuro, en máximo dos renglones. Escríbelo
  exactamente así, con sus acentos.
- Fondo: {escena}, con degradado de azul (#2563eb) a cian (#06b6d4) sobre
  azul marino (#0f172a). Alto contraste, que se lea en un celular.
- Deja un margen libre en las orillas y la esquina inferior derecha vacía
  (ahí YouTube pone la duración).
- Sin logotipos, sin marcas de agua y sin ningún texto adicional.
```

## Spotify (cuadrada)

```
Ahora la imagen del episodio para Spotify, con el mismo estilo.

- Formato cuadrado 1:1.
- Mi rostro centrado o ligeramente a un lado, más cercano que en la de
  YouTube.
- El texto «{texto_miniatura}» arriba o abajo, grande y legible en un
  cuadro pequeño. Escríbelo exactamente así.
- Mismo fondo y paleta. Sin logotipos ni texto adicional.
```

## Revisión antes de descargar

- El texto está escrito exactamente igual que en el prompt.
- Se lee a tamaño de miniatura (aléjate o reduce la vista).
- El rostro se parece a Omar y no está deformado.
- No hay logotipos de marcas ni texto inventado.

Si falla algo, pide la corrección puntual en el mismo chat ("corrige la
palabra X", "el texto más grande"). Máximo tres intentos por imagen.
