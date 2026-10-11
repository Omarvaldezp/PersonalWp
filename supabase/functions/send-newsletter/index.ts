// =============================================
// Supabase Edge Function: send-newsletter
// =============================================
// Envía un newsletter a los suscriptores activos por medio de Resend.
// La llama el panel (/admin/, sección Newsletter) con
//   { newsletter_id, test_mode }
//
// Requisitos (una sola vez):
//   - Cuenta en https://resend.com con el dominio omarvaldez.com verificado.
//   - Secreto RESEND_API_KEY en Supabase: Edge Functions > Secrets.
//   - Opcional: NEWSLETTER_FROM para cambiar el remitente.
//
// Seguridad (octubre de 2026):
//   La versión anterior estaba publicada con verify_jwt = false y no revisaba
//   quién la llamaba: cualquiera que conociera el id de un newsletter podía
//   mandarlo a todos los suscriptores. Ahora exige la sesión de un usuario del
//   panel con rol admin o editor activo (tabla perfiles, ver 00_roles.sql).
//   verify_jwt por sí solo no basta: la llave anónima también es un JWT válido.
// =============================================

import { createClient } from 'https://esm.sh/@supabase/supabase-js@2.45.4'

const ORIGENES = ['https://omarvaldez.com', 'https://www.omarvaldez.com']
const REMITENTE = Deno.env.get('NEWSLETTER_FROM') ?? 'Dr. Omar Valdez <newsletter@omarvaldez.com>'
const LOTE = 100          // máximo de correos por llamada a /emails/batch
const PAUSA_MS = 600      // Resend permite 2 peticiones por segundo

function cabeceras(req: Request) {
  const origen = req.headers.get('Origin') ?? ''
  return {
    'Access-Control-Allow-Origin': ORIGENES.includes(origen) ? origen : ORIGENES[0],
    'Access-Control-Allow-Headers': 'authorization, x-client-info, apikey, content-type',
    'Vary': 'Origin',
    'Content-Type': 'application/json',
  }
}

function responder(req: Request, cuerpo: unknown, status = 200) {
  return new Response(JSON.stringify(cuerpo), { status, headers: cabeceras(req) })
}

// base64 apto para URL: btoa() deja '+', '/' y '=', y un '+' crudo en la query
// llega como espacio a unsubscribe.html, con lo que atob() falla.
function enlaceBaja(email: string) {
  const b64 = btoa(String.fromCharCode(...new TextEncoder().encode(email)))
  return `https://omarvaldez.com/unsubscribe.html?e=${encodeURIComponent(b64)}`
}

Deno.serve(async (req) => {
  if (req.method === 'OPTIONS') return new Response('ok', { headers: cabeceras(req) })
  if (req.method !== 'POST') return responder(req, { error: 'Método no permitido' }, 405)

  try {
    const RESEND_API_KEY = Deno.env.get('RESEND_API_KEY')
    if (!RESEND_API_KEY) {
      return responder(req, { error: 'Falta configurar RESEND_API_KEY en Supabase (Edge Functions > Secrets).' }, 500)
    }

    const admin = createClient(Deno.env.get('SUPABASE_URL')!, Deno.env.get('SUPABASE_SERVICE_ROLE_KEY')!)

    // 1. ¿Quién llama? Debe ser un usuario del panel con rol admin o editor.
    const token = (req.headers.get('Authorization') ?? '').replace(/^Bearer\s+/i, '')
    const { data: { user } } = await admin.auth.getUser(token)
    if (!user) return responder(req, { error: 'Inicia sesión en el panel para enviar.' }, 401)

    const { data: perfil } = await admin
      .from('perfiles').select('rol, activo').eq('id', user.id).maybeSingle()
    if (!perfil?.activo || !['admin', 'editor'].includes(perfil.rol)) {
      return responder(req, { error: 'Tu usuario no tiene permiso para enviar newsletters.' }, 403)
    }

    // 2. El newsletter.
    const { newsletter_id, test_mode = false } = await req.json()
    const { data: newsletter } = await admin
      .from('newsletters').select('*').eq('id', newsletter_id).maybeSingle()
    if (!newsletter) return responder(req, { error: 'Newsletter no encontrado.' }, 404)
    if (!test_mode && newsletter.status === 'sent') {
      return responder(req, { error: 'Este newsletter ya se envió. Duplícalo si quieres mandarlo otra vez.' }, 409)
    }

    // 3. Destinatarios: en prueba, sólo quien envía.
    let destinatarios: string[]
    if (test_mode) {
      if (!user.email) return responder(req, { error: 'Tu usuario no tiene correo registrado.' }, 400)
      destinatarios = [user.email]
    } else {
      const { data, error } = await admin.from('subscribers').select('email').eq('active', true)
      if (error) throw error
      destinatarios = (data ?? []).map((s) => s.email)
    }
    if (destinatarios.length === 0) {
      return responder(req, { error: 'No hay suscriptores activos.', sent_count: 0 })
    }

    if (!test_mode) {
      await admin.from('newsletters').update({ status: 'sending' }).eq('id', newsletter_id)
    }

    // 4. Envío por lotes.
    let enviados = 0
    let fallidos = 0
    let primerError = ''
    for (let i = 0; i < destinatarios.length; i += LOTE) {
      const lote = destinatarios.slice(i, i + LOTE).map((email) => {
        const baja = enlaceBaja(email)
        return {
          from: REMITENTE,
          to: [email],
          subject: (test_mode ? '[Prueba] ' : '') + newsletter.subject,
          html: plantilla(newsletter.content ?? '', newsletter.preview_text ?? '', baja),
          headers: { 'List-Unsubscribe': `<${baja}>` },
        }
      })

      const r = await fetch('https://api.resend.com/emails/batch', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Authorization: `Bearer ${RESEND_API_KEY}` },
        body: JSON.stringify(lote),
      })
      if (r.ok) {
        enviados += lote.length
      } else {
        fallidos += lote.length
        if (!primerError) {
          const detalle = await r.json().catch(() => ({}))
          primerError = `Resend respondió ${r.status}: ${detalle?.message ?? 'sin detalle'}`
        }
      }
      if (i + LOTE < destinatarios.length) await new Promise((ok) => setTimeout(ok, PAUSA_MS))
    }

    if (!test_mode) {
      await admin.from('newsletters').update({
        status: enviados === 0 ? 'failed' : 'sent',
        recipients_count: enviados,
        sent_at: enviados > 0 ? new Date().toISOString() : null,
        updated_at: new Date().toISOString(),
      }).eq('id', newsletter_id)
    }

    if (enviados === 0) {
      return responder(req, { error: primerError || 'No se pudo enviar ningún correo.', sent_count: 0 })
    }
    return responder(req, {
      success: true,
      sent_count: enviados,
      failed_count: fallidos,
      total_recipients: destinatarios.length,
      test_mode,
      ...(primerError ? { warning: primerError } : {}),
    })
  } catch (err) {
    console.error('send-newsletter:', err)
    return responder(req, { error: 'Error interno al enviar. Revisa los registros de la función.' }, 500)
  }
})

function plantilla(contenido: string, preheader: string, baja: string): string {
  const oculto = preheader
    ? `<div style="display:none;max-height:0;overflow:hidden;opacity:0;">${preheader.replace(/</g, '&lt;')}</div>`
    : ''
  return `<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;">
${oculto}
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:30px 0;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;">
  <tr><td style="background:#2563eb;background:linear-gradient(135deg,#2563eb,#06b6d4);padding:35px 40px;text-align:center;">
    <h1 style="color:#ffffff;margin:0;font-size:24px;font-weight:700;">Dr. Omar Valdez Palazuelos</h1>
    <p style="color:#e0f2fe;margin:8px 0 0;font-size:14px;">Newsletter académico</p>
  </td></tr>
  <tr><td style="padding:40px 45px;font-size:16px;line-height:1.8;color:#1e293b;">
    ${contenido}
  </td></tr>
  <tr><td style="padding:0 45px;"><div style="border-top:1px solid #e2e8f0;"></div></td></tr>
  <tr><td style="padding:25px 45px 35px;text-align:center;font-size:13px;color:#94a3b8;">
    <p style="margin:0 0 6px;">Dr. Omar Valdez Palazuelos</p>
    <p style="margin:0 0 12px;">
      <a href="https://omarvaldez.com" style="color:#2563eb;text-decoration:none;">omarvaldez.com</a> ·
      <a href="https://www.linkedin.com/in/phd-omarvaldez" style="color:#2563eb;text-decoration:none;">LinkedIn</a> ·
      <a href="https://scholar.google.com/citations?user=OXXlj1IAAAAJ&hl=es&oi=ao" style="color:#2563eb;text-decoration:none;">Google Scholar</a>
    </p>
    <p style="margin:0;font-size:11px;">
      Recibes este correo porque te suscribiste en omarvaldez.com.
      <a href="${baja}" style="color:#94a3b8;text-decoration:underline;">Cancelar suscripción</a>
    </p>
  </td></tr>
</table>
</td></tr>
</table>
</body>
</html>`
}
