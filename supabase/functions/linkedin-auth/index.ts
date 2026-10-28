// =============================================
// Supabase Edge Function: linkedin-auth
// =============================================
// Conecta el perfil personal de LinkedIn de Omar con el sitio (OAuth 2.0).
//
//   POST { accion: 'iniciar' }      (desde el panel, con sesión)
//        -> { url } de autorización de LinkedIn
//   POST { accion: 'estado' }       -> { conectado, nombre, expira }
//   POST { accion: 'desconectar' }  -> borra el token guardado
//   GET  ?code=...&state=...        (LinkedIn regresa aquí)
//        -> guarda el token y redirige al panel
//
// Secretos (Supabase > Edge Functions > Secrets):
//   LINKEDIN_CLIENT_ID, LINKEDIN_CLIENT_SECRET   de la app en
//   https://www.linkedin.com/developers/apps (pestaña Auth).
//
// La app necesita los productos "Share on LinkedIn" (permiso w_member_social)
// y "Sign In with LinkedIn using OpenID Connect" (openid, profile), y esta
// función como Authorized redirect URL:
//   https://mogmgtnkyazpsgslucrx.supabase.co/functions/v1/linkedin-auth
//
// El token dura unos 60 días y, sin aprobación especial de LinkedIn, no trae
// refresh token: al vencer, Omar vuelve a pulsar "Conectar" en el panel.
// El token se guarda en redes_conexiones, que sólo la llave de servicio lee.
// =============================================

import { createClient, type SupabaseClient } from 'https://esm.sh/@supabase/supabase-js@2.45.4'

const PANEL = 'https://omarvaldez.com/admin/'
const ORIGENES = ['https://omarvaldez.com', 'https://www.omarvaldez.com']
const CALLBACK = `${Deno.env.get('SUPABASE_URL')}/functions/v1/linkedin-auth`
const PERMISOS = 'openid profile w_member_social'
const VIGENCIA_ESTADO_MIN = 10

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

Deno.serve(async (req) => {
  if (req.method === 'OPTIONS') return new Response('ok', { headers: cabeceras(req) })

  const admin = createClient(Deno.env.get('SUPABASE_URL')!, Deno.env.get('SUPABASE_SERVICE_ROLE_KEY')!)
  const CLIENT_ID = Deno.env.get('LINKEDIN_CLIENT_ID')
  const CLIENT_SECRET = Deno.env.get('LINKEDIN_CLIENT_SECRET')

  // ---- Regreso desde LinkedIn -------------------------------------------
  if (req.method === 'GET') {
    const url = new URL(req.url)
    const estado = url.searchParams.get('state') ?? ''
    const codigo = url.searchParams.get('code')
    const errorLinkedIn = url.searchParams.get('error_description') ?? url.searchParams.get('error')

    // El state se usa una sola vez, aunque el intento falle.
    const { data: fila } = await admin
      .from('redes_oauth_estados').delete().eq('estado', estado).eq('red', 'linkedin')
      .select('usuario, creado').maybeSingle()

    if (!fila) return volverAlPanel({ linkedin: 'error', motivo: 'El enlace de conexión no es válido o ya se usó. Vuelve a pulsar Conectar.' })
    if (Date.now() - new Date(fila.creado).getTime() > VIGENCIA_ESTADO_MIN * 60_000) {
      return volverAlPanel({ linkedin: 'error', motivo: 'Pasaron más de 10 minutos. Vuelve a pulsar Conectar.' })
    }
    if (errorLinkedIn || !codigo) {
      return volverAlPanel({ linkedin: 'error', motivo: errorLinkedIn ?? 'LinkedIn no devolvió autorización.' })
    }
    if (!CLIENT_ID || !CLIENT_SECRET) {
      return volverAlPanel({ linkedin: 'error', motivo: 'Faltan LINKEDIN_CLIENT_ID y LINKEDIN_CLIENT_SECRET en Supabase.' })
    }

    try {
      const r = await fetch('https://www.linkedin.com/oauth/v2/accessToken', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          grant_type: 'authorization_code',
          code: codigo,
          redirect_uri: CALLBACK,
          client_id: CLIENT_ID,
          client_secret: CLIENT_SECRET,
        }),
      })
      const tok = await r.json()
      if (!r.ok || !tok.access_token) {
        return volverAlPanel({ linkedin: 'error', motivo: tok.error_description ?? `LinkedIn respondió ${r.status}` })
      }

      // Con OpenID Connect, "sub" es el id de miembro: el autor de los posts
      // es urn:li:person:{sub}.
      const ui = await fetch('https://api.linkedin.com/v2/userinfo', {
        headers: { Authorization: `Bearer ${tok.access_token}` },
      })
      const yo = await ui.json()
      if (!ui.ok || !yo.sub) {
        return volverAlPanel({ linkedin: 'error', motivo: 'No se pudo leer tu perfil de LinkedIn.' })
      }

      const { error } = await admin.from('redes_conexiones').upsert({
        red: 'linkedin',
        access_token: tok.access_token,
        refresh_token: tok.refresh_token ?? null,
        expira: new Date(Date.now() + (tok.expires_in ?? 5_184_000) * 1000).toISOString(),
        cuenta_id: yo.sub,
        cuenta_nombre: yo.name ?? null,
        conectado_por: fila.usuario,
        actualizado: new Date().toISOString(),
      })
      if (error) throw error

      return volverAlPanel({ linkedin: 'conectado' })
    } catch (e) {
      console.error('linkedin-auth callback:', e)
      return volverAlPanel({ linkedin: 'error', motivo: 'Error interno al guardar la conexión.' })
    }
  }

  // ---- Llamadas del panel ----------------------------------------------
  if (req.method !== 'POST') return responder(req, { error: 'Método no permitido' }, 405)

  const usuario = await usuarioDelPanel(admin, req)
  if (!usuario) return responder(req, { error: 'Inicia sesión en el panel.' }, 401)

  const { accion } = await req.json().catch(() => ({}))

  if (accion === 'estado') {
    const { data } = await admin
      .from('redes_conexiones').select('cuenta_nombre, expira').eq('red', 'linkedin').maybeSingle()
    return responder(req, {
      configurado: !!(CLIENT_ID && CLIENT_SECRET),
      conectado: !!data && new Date(data.expira).getTime() > Date.now(),
      nombre: data?.cuenta_nombre ?? null,
      expira: data?.expira ?? null,
    })
  }

  if (accion === 'desconectar') {
    await admin.from('redes_conexiones').delete().eq('red', 'linkedin')
    return responder(req, { ok: true })
  }

  if (accion === 'iniciar') {
    if (!CLIENT_ID || !CLIENT_SECRET) {
      return responder(req, { error: 'Faltan LINKEDIN_CLIENT_ID y LINKEDIN_CLIENT_SECRET en Supabase (Edge Functions > Secrets).' }, 500)
    }
    const estado = crypto.randomUUID()
    // Limpia intentos viejos y registra el nuevo.
    await admin.from('redes_oauth_estados').delete()
      .lt('creado', new Date(Date.now() - VIGENCIA_ESTADO_MIN * 60_000).toISOString())
    const { error } = await admin.from('redes_oauth_estados')
      .insert({ estado, red: 'linkedin', usuario: usuario.id })
    if (error) {
      console.error('linkedin-auth iniciar:', error)
      return responder(req, { error: 'No se pudo iniciar la conexión. ¿Ya se corrió 10_redes_sociales.sql?' }, 500)
    }
    const url = 'https://www.linkedin.com/oauth/v2/authorization?' + new URLSearchParams({
      response_type: 'code',
      client_id: CLIENT_ID,
      redirect_uri: CALLBACK,
      state: estado,
      scope: PERMISOS,
    })
    return responder(req, { url })
  }

  return responder(req, { error: 'Acción no reconocida.' }, 400)
})
