// =============================================
// Supabase Edge Function: instagram-publicar
// =============================================
// Publica en la cuenta profesional de Instagram de Omar un borrador de
// redes_publicaciones (red = 'instagram'). La llama el panel, sección Redes,
// cuando Omar pulsa "Publicar":
//
//   POST { publicacion_id }
//
// Las imágenes ya están en el bucket images como JPEG: el panel convierte las
// láminas del PDF (o la imagen del artículo) antes de crear el borrador.
// Instagram las descarga de esas direcciones, así que tienen que ser públicas.
//
// Pasos de la API (Instagram API with Instagram Login):
//   1. Un contenedor por imagen (is_carousel_item=true si son varias).
//   2. Si son varias, un contenedor CAROUSEL con los hijos y el texto.
//   3. Esperar a que el contenedor esté FINISHED.
//   4. media_publish, y leer el permalink.
// =============================================

import { createClient } from 'https://esm.sh/@supabase/supabase-js@2.45.4'

const ORIGENES = ['https://omarvaldez.com', 'https://www.omarvaldez.com']
const VERSION = Deno.env.get('INSTAGRAM_API_VERSION') ?? 'v23.0'
const GRAPH = `https://graph.instagram.com/${VERSION}`
const BUCKET = `${Deno.env.get('SUPABASE_URL')}/storage/v1/object/public/images/`
const MAX_TEXTO = 2200
const MAX_HASHTAGS = 30

function cabeceras(req: Request) {
  const origen = req.headers.get('Origin') ?? ''
  return {
    'Access-Control-Allow-Origin': ORIGENES.includes(origen) ? origen : ORIGENES[0],
    'Access-Control-Allow-Headers': 'authorization, x-client-info, apikey, content-type',
    'Vary': 'Origin',
    'Content-Type': 'application/json',
  }
}
const responder = (req: Request, cuerpo: unknown, status = 200) =>
  new Response(JSON.stringify(cuerpo), { status, headers: cabeceras(req) })

const esperar = (ms: number) => new Promise((r) => setTimeout(r, ms))

class ErrorInstagram extends Error {}

async function graph(ruta: string, token: string, params: Record<string, string>, metodo = 'POST') {
  const cuerpo = new URLSearchParams({ ...params, access_token: token })
  const r = metodo === 'POST'
    ? await fetch(`${GRAPH}/${ruta}`, { method: 'POST', body: cuerpo })
    : await fetch(`${GRAPH}/${ruta}?${cuerpo}`)
  const j = await r.json().catch(() => ({}))
  if (!r.ok || j.error) {
    const m = j.error?.error_user_msg ?? j.error?.message ?? `respuesta ${r.status}`
    throw new ErrorInstagram(`Instagram: ${m}`)
  }
  return j
}

// Instagram procesa cada contenedor de forma asíncrona; publicar antes de que
// esté FINISHED da error.
async function esperarContenedor(id: string, token: string) {
  for (let i = 0; i < 30; i++) {
    const { status_code } = await graph(id, token, { fields: 'status_code' }, 'GET')
    if (status_code === 'FINISHED') return
    if (status_code === 'ERROR' || status_code === 'EXPIRED') {
      throw new ErrorInstagram('Instagram no pudo procesar una de las imágenes (revisa formato y proporción).')
    }
    await esperar(2000)
  }
  throw new ErrorInstagram('Instagram tardó demasiado en procesar las imágenes. Intenta de nuevo en unos minutos.')
}

Deno.serve(async (req) => {
  if (req.method === 'OPTIONS') return new Response('ok', { headers: cabeceras(req) })
  if (req.method !== 'POST') return responder(req, { error: 'Método no permitido' }, 405)

  const admin = createClient(Deno.env.get('SUPABASE_URL')!, Deno.env.get('SUPABASE_SERVICE_ROLE_KEY')!)
  let publicacionId: string | null = null

  const marcarError = async (mensaje: string) => {
    if (publicacionId) {
      await admin.from('redes_publicaciones')
        .update({ estado: 'error', error: mensaje, updated_at: new Date().toISOString() })
        .eq('id', publicacionId)
    }
  }

  try {
    // 1. Sólo usuarios del panel.
    const jwt = (req.headers.get('Authorization') ?? '').replace(/^Bearer\s+/i, '')
    const { data: { user } } = await admin.auth.getUser(jwt)
    if (!user) return responder(req, { error: 'Inicia sesión en el panel.' }, 401)
    const { data: perfil } = await admin
      .from('perfiles').select('rol, activo').eq('id', user.id).maybeSingle()
    if (!perfil?.activo || !['admin', 'editor'].includes(perfil.rol)) {
      return responder(req, { error: 'Tu usuario no tiene permiso para publicar.' }, 403)
    }

    // 2. El borrador.
    const { publicacion_id } = await req.json().catch(() => ({}))
    const { data: pub } = await admin
      .from('redes_publicaciones').select('*').eq('id', publicacion_id).eq('red', 'instagram').maybeSingle()
    if (!pub) return responder(req, { error: 'Borrador no encontrado.' }, 404)
    if (pub.estado === 'publicado') return responder(req, { error: 'Este borrador ya se publicó.' }, 409)
    if (pub.estado === 'publicando') return responder(req, { error: 'Ya se está publicando. Espera unos segundos.' }, 409)

    const texto = (pub.texto ?? '').trim()
    if (texto.length > MAX_TEXTO) return responder(req, { error: `El texto pasa de ${MAX_TEXTO} caracteres.` }, 400)
    if ((texto.match(/#[\p{L}\p{N}_]+/gu) ?? []).length > MAX_HASHTAGS) {
      return responder(req, { error: `Instagram acepta como máximo ${MAX_HASHTAGS} hashtags.` }, 400)
    }

    const imagenes: string[] = (pub.imagenes?.length ? pub.imagenes : [pub.imagen_url]).filter(Boolean)
    if (!imagenes.length) return responder(req, { error: 'El borrador no tiene imágenes.' }, 400)
    if (imagenes.length > 10) return responder(req, { error: 'Un carrusel admite como máximo 10 imágenes.' }, 400)
    if (imagenes.some((u) => !u.startsWith(BUCKET) || !/\.jpe?g$/i.test(u))) {
      return responder(req, { error: 'Las imágenes deben ser JPEG subidos desde el panel.' }, 400)
    }

    // 3. La conexión. El panel la renueva al abrir Redes (instagram-auth).
    const { data: con } = await admin
      .from('redes_conexiones').select('access_token, expira, cuenta_id').eq('red', 'instagram').maybeSingle()
    if (!con) return responder(req, { error: 'Instagram no está conectado. Pulsa "Conectar Instagram".' }, 400)
    if (new Date(con.expira).getTime() <= Date.now()) {
      return responder(req, { error: 'La conexión con Instagram venció. Vuelve a pulsar "Conectar Instagram".' }, 400)
    }

    publicacionId = pub.id
    await admin.from('redes_publicaciones')
      .update({ estado: 'publicando', error: null, updated_at: new Date().toISOString() }).eq('id', pub.id)

    const token = con.access_token
    const cuenta = con.cuenta_id

    // 4. Contenedores.
    let contenedor: string
    if (imagenes.length === 1) {
      contenedor = (await graph(`${cuenta}/media`, token, { image_url: imagenes[0], caption: texto })).id
    } else {
      const hijos: string[] = []
      for (const url of imagenes) {
        hijos.push((await graph(`${cuenta}/media`, token, { image_url: url, is_carousel_item: 'true' })).id)
      }
      for (const h of hijos) await esperarContenedor(h, token)
      contenedor = (await graph(`${cuenta}/media`, token, {
        media_type: 'CAROUSEL', children: hijos.join(','), caption: texto,
      })).id
    }
    await esperarContenedor(contenedor, token)

    // 5. Publicar y leer el enlace.
    const { id: mediaId } = await graph(`${cuenta}/media_publish`, token, { creation_id: contenedor })
    let url: string | null = null
    try {
      url = (await graph(mediaId, token, { fields: 'permalink' }, 'GET')).permalink ?? null
    } catch (_) { /* el post ya salió; sólo falta el enlace */ }

    await admin.from('redes_publicaciones').update({
      estado: 'publicado',
      id_externo: mediaId,
      url_externa: url,
      error: null,
      publicado_en: new Date().toISOString(),
      updated_at: new Date().toISOString(),
    }).eq('id', pub.id)

    return responder(req, { ok: true, url })
  } catch (err) {
    if (err instanceof ErrorInstagram) {
      await marcarError(err.message)
      return responder(req, { error: err.message }, 502)
    }
    console.error('instagram-publicar:', err)
    await marcarError('Error interno al publicar.')
    return responder(req, { error: 'Error interno al publicar. Revisa los registros de la función.' }, 500)
  }
})
