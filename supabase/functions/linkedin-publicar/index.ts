// =============================================
// Supabase Edge Function: linkedin-publicar
// =============================================
// Publica en el perfil personal de LinkedIn un borrador de
// redes_publicaciones. La llama el panel, sección Redes, cuando Omar pulsa
// "Publicar en LinkedIn":
//
//   POST { publicacion_id, imagen_jpeg_base64? }
//
// La imagen llega ya convertida a JPEG por el navegador: el sitio guarda las
// imágenes en WebP y LinkedIn sólo acepta JPG, PNG o GIF.
//
// Usa la Posts API (/rest/posts) con versión fija (LINKEDIN_API_VERSION,
// 202609 por omisión). LinkedIn mantiene cada versión al menos un año; cuando
// la fijada se retire, la API responde error y hay que subir el secreto.
// =============================================

import { createClient } from 'https://esm.sh/@supabase/supabase-js@2.45.4'

const ORIGENES = ['https://omarvaldez.com', 'https://www.omarvaldez.com']
const VERSION = Deno.env.get('LINKEDIN_API_VERSION') ?? '202609'
const API = 'https://api.linkedin.com/rest'
const MAX_TEXTO = 3000

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

// "little text" de LinkedIn: estos caracteres están reservados y, si no se
// escapan, LinkedIn responde 201 pero publica el texto CORTADO en el primer
// carácter sin escapar. Los hashtags van con su propia plantilla.
const RESERVADOS = /[\\|{}@\[\]()<>#*_~]/g
export function aLittleText(texto: string): string {
  const partes = texto.split(/(#[\p{L}\p{N}_]+)/u)
  return partes.map((p) => {
    const ht = p.match(/^#([\p{L}\p{N}_]+)$/u)
    if (ht) return `{hashtag|\\#|${ht[1].replace(RESERVADOS, (c) => '\\' + c)}}`
    return p.replace(RESERVADOS, (c) => '\\' + c)
  }).join('')
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
    const { publicacion_id, imagen_jpeg_base64 } = await req.json()
    const { data: pub } = await admin
      .from('redes_publicaciones').select('*').eq('id', publicacion_id).eq('red', 'linkedin').maybeSingle()
    if (!pub) return responder(req, { error: 'Borrador no encontrado.' }, 404)
    if (pub.estado === 'publicado') return responder(req, { error: 'Este borrador ya se publicó.' }, 409)
    if (pub.estado === 'publicando') return responder(req, { error: 'Ya se está publicando. Espera unos segundos.' }, 409)
    publicacionId = pub.id

    const texto = (pub.texto ?? '').trim()
    if (!texto) return responder(req, { error: 'El texto está vacío.' }, 400)
    if (texto.length > MAX_TEXTO) return responder(req, { error: `El texto pasa de ${MAX_TEXTO} caracteres.` }, 400)

    // 3. La conexión.
    const { data: con } = await admin
      .from('redes_conexiones').select('access_token, expira, cuenta_id').eq('red', 'linkedin').maybeSingle()
    if (!con) return responder(req, { error: 'LinkedIn no está conectado. Pulsa "Conectar LinkedIn".' }, 400)
    if (new Date(con.expira).getTime() <= Date.now()) {
      return responder(req, { error: 'La conexión con LinkedIn venció. Vuelve a pulsar "Conectar LinkedIn".' }, 400)
    }

    await admin.from('redes_publicaciones')
      .update({ estado: 'publicando', error: null, updated_at: new Date().toISOString() }).eq('id', pub.id)

    const autor = `urn:li:person:${con.cuenta_id}`
    const base = {
      Authorization: `Bearer ${con.access_token}`,
      'Linkedin-Version': VERSION,
      'X-Restli-Protocol-Version': '2.0.0',
    }

    // 4. Imagen (opcional): initializeUpload + PUT del binario.
    let imagenUrn: string | null = null
    if (imagen_jpeg_base64) {
      const ini = await fetch(`${API}/images?action=initializeUpload`, {
        method: 'POST',
        headers: { ...base, 'Content-Type': 'application/json' },
        body: JSON.stringify({ initializeUploadRequest: { owner: autor } }),
      })
      const iniJson = await ini.json().catch(() => ({}))
      if (!ini.ok || !iniJson?.value?.uploadUrl) {
        const m = `LinkedIn rechazó la imagen (${ini.status}): ${iniJson?.message ?? 'sin detalle'}`
        await marcarError(m)
        return responder(req, { error: m }, 502)
      }
      const binario = Uint8Array.from(atob(imagen_jpeg_base64), (c) => c.charCodeAt(0))
      const put = await fetch(iniJson.value.uploadUrl, {
        method: 'PUT',
        headers: { Authorization: `Bearer ${con.access_token}`, 'Content-Type': 'image/jpeg' },
        body: binario,
      })
      if (!put.ok) {
        const m = `No se pudo subir la imagen a LinkedIn (${put.status}).`
        await marcarError(m)
        return responder(req, { error: m }, 502)
      }
      imagenUrn = iniJson.value.image
    }

    // 5. El post. Con enlace: tarjeta de artículo (título, descripción,
    // miniatura). Sin enlace pero con imagen: post de imagen.
    const cuerpo: Record<string, unknown> = {
      author: autor,
      commentary: aLittleText(texto),
      visibility: 'PUBLIC',
      distribution: { feedDistribution: 'MAIN_FEED', targetEntities: [], thirdPartyDistributionChannels: [] },
      lifecycleState: 'PUBLISHED',
      isReshareDisabledByAuthor: false,
    }
    if (pub.enlace) {
      cuerpo.content = {
        article: {
          source: pub.enlace,
          title: (pub.enlace_titulo ?? '').slice(0, 200) || pub.enlace,
          ...(pub.enlace_descripcion ? { description: pub.enlace_descripcion.slice(0, 300) } : {}),
          ...(imagenUrn ? { thumbnail: imagenUrn } : {}),
        },
      }
    } else if (imagenUrn) {
      cuerpo.content = { media: { id: imagenUrn } }
    }

    const r = await fetch(`${API}/posts`, {
      method: 'POST',
      headers: { ...base, 'Content-Type': 'application/json' },
      body: JSON.stringify(cuerpo),
    })
    if (r.status !== 201) {
      const det = await r.json().catch(() => ({}))
      const m = `LinkedIn respondió ${r.status}: ${det?.message ?? 'sin detalle'}`
      await marcarError(m)
      return responder(req, { error: m }, 502)
    }

    const urn = r.headers.get('x-restli-id') ?? ''
    const url = urn ? `https://www.linkedin.com/feed/update/${urn}/` : null
    await admin.from('redes_publicaciones').update({
      estado: 'publicado',
      id_externo: urn || null,
      url_externa: url,
      error: null,
      publicado_en: new Date().toISOString(),
      updated_at: new Date().toISOString(),
    }).eq('id', pub.id)

    return responder(req, { ok: true, url })
  } catch (err) {
    console.error('linkedin-publicar:', err)
    await marcarError('Error interno al publicar.')
    return responder(req, { error: 'Error interno al publicar. Revisa los registros de la función.' }, 500)
  }
})
