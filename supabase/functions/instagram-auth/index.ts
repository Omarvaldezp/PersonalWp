// =============================================
// Supabase Edge Function: instagram-auth
// =============================================
// Conecta la cuenta profesional de Instagram de Omar con el sitio, con la
// "Instagram API with Instagram Login" (no necesita página de Facebook).
//
//   POST { accion: 'iniciar' }      (desde el panel, con sesión)
//        -> { url } de autorización de Instagram
//   POST { accion: 'estado' }       -> { configurado, conectado, nombre, expira }
//        (de paso renueva el token si le quedan menos de 30 días)
//   POST { accion: 'desconectar' }  -> borra el token guardado
//   GET  ?code=...&state=...        (Instagram regresa aquí)
//        -> guarda el token de larga duración y redirige al panel
//
// Secretos (Supabase > Edge Functions > Secrets):
//   INSTAGRAM_APP_ID, INSTAGRAM_APP_SECRET   de la app de Meta, en
//   Instagram > Configuración de la API con inicio de sesión de Instagram.
//   OJO: son el id y la clave "de la app de Instagram", distintos de los de la
//   app de Meta que aparecen en Configuración > Básica.
//
// La app necesita esta función como URI de redireccionamiento de OAuth:
//   https://mogmgtnkyazpsgslucrx.supabase.co/functions/v1/instagram-auth
//
// A diferencia de LinkedIn, el token sí se renueva: dura 60 días y se puede
// refrescar cuando tiene al menos 24 horas. Si pasan 60 días sin renovarlo,
// vence y hay que volver a conectar. El panel lo renueva cada vez que abre la
// sección Redes.
// =============================================

import { createClient, type SupabaseClient } from 'https://esm.sh/@supabase/supabase-js@2.45.4'

const PANEL = 'https://omarvaldez.com/admin/'
const ORIGENES = ['https://omarvaldez.com', 'https://www.omarvaldez.com']
const CALLBACK = `${Deno.env.get('SUPABASE_URL')}/functions/v1/instagram-auth`
const PERMISOS = 'instagram_business_basic,instagram_business_content_publish'
const VIGENCIA_ESTADO_MIN = 10
const GRAPH = 'https://graph.instagram.com'

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

const volverAlPanel = (params: Record<string, string>) =>
  new Response(null, {
    status: 302,
    headers: { Location: `${PANEL}#redes?${new URLSearchParams(params)}` },
  })

async function usuarioDelPanel(admin: SupabaseClient, req: Request) {
  const token = (req.headers.get('Authorization') ?? '').replace(/^Bearer\s+/i, '')
  const { data: { user } } = await admin.auth.getUser(token)
  if (!user) return null
  const { data: perfil } = await admin
    .from('perfiles').select('rol, activo').eq('id', user.id).maybeSingle()
  return perfil?.activo && ['admin', 'editor'].includes(perfil.rol) ? user : null
}

// Renueva el token si le quedan menos de 30 días y tiene más de 24 horas.
// Devuelve la fila vigente (renovada o no) o null si no hay conexión.
export async function tokenVigente(admin: SupabaseClient) {
  const { data: con } = await admin
    .from('redes_conexiones').select('*').eq('red', 'instagram').maybeSingle()
  if (!con) return null
  const restante = new Date(con.expira).getTime() - Date.now()
  const edad = Date.now() - new Date(con.actualizado).getTime()
  if (restante <= 0 || restante > 30 * 86_400_000 || edad < 86_400_000) return con
  try {
    const r = await fetch(`${GRAPH}/refresh_access_token?` + new URLSearchParams({
      grant_type: 'ig_refresh_token', access_token: con.access_token,
    }))
    const j = await r.json()
    if (!r.ok || !j.access_token) {
      console.error('instagram-auth: no se pudo renovar el token', j?.error?.message)
      return con
    }
    const nueva = {
      access_token: j.access_token,
      expira: new Date(Date.now() + (j.expires_in ?? 5_184_000) * 1000).toISOString(),
      actualizado: new Date().toISOString(),
    }
    await admin.from('redes_conexiones').update(nueva).eq('red', 'instagram')
    return { ...con, ...nueva }
  } catch (e) {
    console.error('instagram-auth: error al renovar', e)
    return con
  }
}

Deno.serve(async (req) => {
  if (req.method === 'OPTIONS') return new Response('ok', { headers: cabeceras(req) })

  const admin = createClient(Deno.env.get('SUPABASE_URL')!, Deno.env.get('SUPABASE_SERVICE_ROLE_KEY')!)
  const APP_ID = Deno.env.get('INSTAGRAM_APP_ID')
  const APP_SECRET = Deno.env.get('INSTAGRAM_APP_SECRET')

  // ---- Regreso desde Instagram -----------------------------------------
  if (req.method === 'GET') {
    const url = new URL(req.url)
    const estado = url.searchParams.get('state') ?? ''
    // Instagram a veces agrega "#_" al final del code.
    const codigo = url.searchParams.get('code')?.replace(/#_$/, '')
    const errorIg = url.searchParams.get('error_description') ?? url.searchParams.get('error_reason') ?? url.searchParams.get('error')

    // El state se usa una sola vez, aunque el intento falle.
    const { data: fila } = await admin
      .from('redes_oauth_estados').delete().eq('estado', estado).eq('red', 'instagram')
      .select('usuario, creado').maybeSingle()

    if (!fila) return volverAlPanel({ instagram: 'error', motivo: 'El enlace de conexión no es válido o ya se usó. Vuelve a pulsar Conectar.' })
    if (Date.now() - new Date(fila.creado).getTime() > VIGENCIA_ESTADO_MIN * 60_000) {
      return volverAlPanel({ instagram: 'error', motivo: 'Pasaron más de 10 minutos. Vuelve a pulsar Conectar.' })
    }
    if (errorIg || !codigo) {
      return volverAlPanel({ instagram: 'error', motivo: errorIg ?? 'Instagram no devolvió autorización.' })
    }
    if (!APP_ID || !APP_SECRET) {
      return volverAlPanel({ instagram: 'error', motivo: 'Faltan INSTAGRAM_APP_ID e INSTAGRAM_APP_SECRET en Supabase.' })
    }

    try {
      // 1. Código -> token de corta duración (1 hora).
      const r1 = await fetch('https://api.instagram.com/oauth/access_token', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          client_id: APP_ID,
          client_secret: APP_SECRET,
          grant_type: 'authorization_code',
          redirect_uri: CALLBACK,
          code: codigo,
        }),
      })
      const j1 = await r1.json()
      // Según la versión, viene plano o dentro de data[0].
      const corto = j1.access_token ?? j1.data?.[0]?.access_token
      if (!r1.ok || !corto) {
        return volverAlPanel({ instagram: 'error', motivo: j1.error_message ?? j1.error?.message ?? `Instagram respondió ${r1.status}` })
      }

      // 2. Token de corta duración -> de larga duración (60 días).
      const r2 = await fetch(`${GRAPH}/access_token?` + new URLSearchParams({
        grant_type: 'ig_exchange_token', client_secret: APP_SECRET, access_token: corto,
      }))
      const j2 = await r2.json()
      if (!r2.ok || !j2.access_token) {
        return volverAlPanel({ instagram: 'error', motivo: j2.error?.message ?? 'No se pudo obtener el token de larga duración.' })
      }

      // 3. La cuenta: user_id es el id profesional con el que se publica.
      const r3 = await fetch(`${GRAPH}/me?` + new URLSearchParams({
        fields: 'user_id,username,account_type', access_token: j2.access_token,
      }))
      const yo = await r3.json()
      if (!r3.ok || !yo.user_id) {
        return volverAlPanel({ instagram: 'error', motivo: 'No se pudo leer tu cuenta de Instagram.' })
      }

      const { error } = await admin.from('redes_conexiones').upsert({
        red: 'instagram',
        access_token: j2.access_token,
        refresh_token: null,
        expira: new Date(Date.now() + (j2.expires_in ?? 5_184_000) * 1000).toISOString(),
        cuenta_id: String(yo.user_id),
        cuenta_nombre: yo.username ? '@' + yo.username : null,
        conectado_por: fila.usuario,
        actualizado: new Date().toISOString(),
      })
      if (error) throw error

      return volverAlPanel({ instagram: 'conectado' })
    } catch (e) {
      console.error('instagram-auth callback:', e)
      return volverAlPanel({ instagram: 'error', motivo: 'Error interno al guardar la conexión.' })
    }
  }

  // ---- Llamadas del panel ----------------------------------------------
  if (req.method !== 'POST') return responder(req, { error: 'Método no permitido' }, 405)

  const usuario = await usuarioDelPanel(admin, req)
  if (!usuario) return responder(req, { error: 'Inicia sesión en el panel.' }, 401)

  const { accion } = await req.json().catch(() => ({}))

  if (accion === 'estado') {
    const con = await tokenVigente(admin)
    return responder(req, {
      configurado: !!(APP_ID && APP_SECRET),
      conectado: !!con && new Date(con.expira).getTime() > Date.now(),
      nombre: con?.cuenta_nombre ?? null,
      expira: con?.expira ?? null,
    })
  }

  if (accion === 'desconectar') {
    await admin.from('redes_conexiones').delete().eq('red', 'instagram')
    return responder(req, { ok: true })
  }

  if (accion === 'iniciar') {
    if (!APP_ID || !APP_SECRET) {
      return responder(req, { error: 'Faltan INSTAGRAM_APP_ID e INSTAGRAM_APP_SECRET en Supabase (Edge Functions > Secrets).' }, 500)
    }
    const estado = crypto.randomUUID()
    await admin.from('redes_oauth_estados').delete()
      .lt('creado', new Date(Date.now() - VIGENCIA_ESTADO_MIN * 60_000).toISOString())
    const { error } = await admin.from('redes_oauth_estados')
      .insert({ estado, red: 'instagram', usuario: usuario.id })
    if (error) {
      console.error('instagram-auth iniciar:', error)
      return responder(req, { error: 'No se pudo iniciar la conexión.' }, 500)
    }
    const url = 'https://www.instagram.com/oauth/authorize?' + new URLSearchParams({
      client_id: APP_ID,
      redirect_uri: CALLBACK,
      response_type: 'code',
      scope: PERMISOS,
      state: estado,
      enable_fb_login: '0',
      force_reauth: 'true',
    })
    return responder(req, { url })
  }

  return responder(req, { error: 'Acción no reconocida.' }, 400)
})
