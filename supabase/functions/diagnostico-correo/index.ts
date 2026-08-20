// ============================================================================
// Edge Function: correo de bienvenida del diagnóstico DISDE
//
// Sustituye al mail() de src/api/controllers/diagnostico-disde.php. Es la
// pieza que faltaba para poder retirar el PHP sin que el estudiante deje de
// recibir su correo.
//
// Cómo encaja:
//   1. El formulario público inserta la respuesta en Supabase.
//   2. Enseguida llama a esta función con el id de esa respuesta.
//   3. La función lee el registro, envía el correo por Resend y marca
//      email_enviado.
//
// Por qué recibe el id y no el nombre y el correo: si aceptara los datos
// directamente, cualquiera podría usar esta función para mandar correos a
// quien quisiera desde tu dominio. Recibiendo solo un id, lo único que puede
// hacer es reenviar el correo de una respuesta que ya existe.
//
// VARIABLES DE ENTORNO (Supabase > Edge Functions > Secrets):
//   RESEND_API_KEY   llave de https://resend.com  (plan gratis: 3000/mes)
//   REMITENTE        ej. "Dr. Omar Valdez <noreply@omarvaldez.com>"
//   RESPONDER_A      ej. "omar@omarvaldez.com"
//   ENLACE_BIENVENIDA  ej. "https://omarvaldez.com/bienvenida-disde.html"
//
// El dominio omarvaldez.com tiene que estar verificado en Resend (registros
// SPF y DKIM en el DNS de SiteGround) o los correos se irán a spam.
//
// DESPLEGAR:
//   supabase functions deploy diagnostico-correo --project-ref mogmgtnkyazpsgslucrx
// ============================================================================

import { createClient } from 'https://esm.sh/@supabase/supabase-js@2';

const CORS = {
  'Access-Control-Allow-Origin': 'https://omarvaldez.com',
  'Access-Control-Allow-Headers': 'authorization, x-client-info, apikey, content-type',
  'Access-Control-Allow-Methods': 'POST, OPTIONS',
};

function escapar(texto: string): string {
  return String(texto ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

// Plantilla portada tal cual desde obtenerPlantillaEmail() del PHP.
function plantilla(nombre: string, enlace: string): string {
  return PLANTILLA_HTML
    .replaceAll('{{NOMBRE}}', escapar(nombre))
    .replaceAll('{{ENLACE}}', escapar(enlace));
}

const PLANTILLA_HTML = `<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Confirmación · Cuestionario Diagnóstico DISDE</title>
</head>
<body style="margin:0; padding:0; background-color:#eef1f5;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eef1f5; padding:24px 0;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:600px; max-width:600px; background-color:#ffffff; border-radius:12px; overflow:hidden; font-family:Arial, Helvetica, sans-serif; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
          <tr>
            <td style="background-color:#1B365D; background-image:linear-gradient(135deg,#1B365D 0%,#2E5A9C 100%); padding:32px 30px; text-align:center;">
              <div style="color:#C9A227; font-size:13px; letter-spacing:1px; text-transform:uppercase; margin-bottom:8px;">Universidad Autónoma de Sinaloa · FCA</div>
              <div style="color:#ffffff; font-size:22px; font-weight:bold; line-height:1.3;">Curso Propedéutico · DISDE</div>
              <div style="color:#dfe7f2; font-size:14px; margin-top:6px;">Doctorado en Ciencias en Innovación Social y Desarrollo Económico</div>
            </td>
          </tr>
          <tr>
            <td style="background-color:#C9A227; padding:12px 30px; text-align:center; color:#1B365D; font-size:15px; font-weight:bold;">
              Hemos recibido tu cuestionario diagnóstico
            </td>
          </tr>
          <tr>
            <td style="padding:30px;">
              <p style="margin:0 0 16px; color:#333333; font-size:15px; line-height:1.7;">
                Estimado(a) <strong>{{NOMBRE}}</strong>:
              </p>
              <p style="margin:0 0 16px; color:#333333; font-size:15px; line-height:1.7;">
                Gracias por completar el <strong>cuestionario diagnóstico</strong> del Curso Propedéutico del DISDE. Tus respuestas nos ayudan a conocer tu punto de partida para acompañarte mejor durante estas tres semanas.
              </p>
              <p style="margin:0 0 22px; color:#333333; font-size:15px; line-height:1.7;">
                Antes de comenzar formalmente el curso, te pedimos revisar la <strong>Sesión 0 (Bienvenida)</strong>. Ahí encontrarás la información esencial: las Líneas de Investigación e Incidencia Social (LIES), la ruta de las nueve sesiones, la forma de evaluación, los lineamientos de uso ético de la inteligencia artificial y cómo está organizada el aula.
              </p>
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center" style="padding:6px 0 26px;">
                    <a href="{{ENLACE}}" target="_blank"
                       style="display:inline-block; background-color:#1B365D; color:#ffffff; text-decoration:none; font-size:16px; font-weight:bold; padding:14px 34px; border-radius:8px;">
                      Ver la Sesión 0 (Bienvenida)
                    </a>
                  </td>
                </tr>
              </table>
              <p style="margin:0 0 22px; color:#666666; font-size:13px; line-height:1.6; text-align:center;">
                Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
                <a href="{{ENLACE}}" target="_blank" style="color:#2E5A9C; word-break:break-all;">{{ENLACE}}</a>
              </p>
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="background-color:#E8EEF7; border-left:4px solid #1B365D; border-radius:0 8px 8px 0; padding:16px 18px;">
                    <p style="margin:0; color:#1B365D; font-size:14px; line-height:1.6;">
                      <strong>Recomendación:</strong> ten a la mano un tema o problema de interés para investigar; lo iremos afinando desde la primera sesión para construir tu anteproyecto.
                    </p>
                  </td>
                </tr>
              </table>
              <p style="margin:24px 0 0; color:#333333; font-size:15px; line-height:1.7;">
                Nos vemos en la Sesión 1. ¡Bienvenido(a) al curso!
              </p>
              <p style="margin:14px 0 0; color:#333333; font-size:15px; line-height:1.6;">
                Atentamente,<br>
                <strong>Dr. Omar Valdez Palazuelos</strong><br>
                <span style="color:#666666; font-size:13px;">Facilitador del Curso Propedéutico DISDE</span>
              </p>
            </td>
          </tr>
          <tr>
            <td style="background-color:#1B365D; padding:18px 30px; text-align:center;">
              <p style="margin:0; color:#dfe7f2; font-size:12px; line-height:1.6;">
                Facultad de Contaduría y Administración · Universidad Autónoma de Sinaloa<br>
                Este mensaje fue enviado automáticamente tras registrar tu cuestionario diagnóstico.
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>`;

Deno.serve(async (req) => {
  if (req.method === 'OPTIONS') {
    return new Response('ok', { headers: CORS });
  }

  if (req.method !== 'POST') {
    return new Response(JSON.stringify({ error: 'Method not allowed' }), {
      status: 405,
      headers: { ...CORS, 'Content-Type': 'application/json' },
    });
  }

  try {
    const { respuesta_id } = await req.json();

    if (!respuesta_id) {
      return new Response(JSON.stringify({ error: 'Falta respuesta_id' }), {
        status: 400,
        headers: { ...CORS, 'Content-Type': 'application/json' },
      });
    }

    // Cliente con service_role: la tabla no deja leer al rol anónimo, y esta
    // función necesita el nombre y el correo del registro recién insertado.
    // Esa llave vive solo aquí dentro, nunca llega al navegador.
    const supabase = createClient(
      Deno.env.get('SUPABASE_URL')!,
      Deno.env.get('SUPABASE_SERVICE_ROLE_KEY')!,
    );

    const { data: respuesta, error: errorLectura } = await supabase
      .from('diagnostico_disde_respuestas')
      .select('id, nombre, correo, email_enviado')
      .eq('id', respuesta_id)
      .single();

    if (errorLectura || !respuesta) {
      return new Response(JSON.stringify({ error: 'Respuesta no encontrada' }), {
        status: 404,
        headers: { ...CORS, 'Content-Type': 'application/json' },
      });
    }

    // Idempotencia: si alguien llama dos veces con el mismo id, no se manda
    // el correo dos veces.
    if (respuesta.email_enviado) {
      return new Response(JSON.stringify({ ok: true, ya_enviado: true }), {
        headers: { ...CORS, 'Content-Type': 'application/json' },
      });
    }

    const enlace = Deno.env.get('ENLACE_BIENVENIDA') ??
      'https://omarvaldez.com/bienvenida-disde.html';

    const envio = await fetch('https://api.resend.com/emails', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${Deno.env.get('RESEND_API_KEY')}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        from: Deno.env.get('REMITENTE') ?? 'Dr. Omar Valdez <noreply@omarvaldez.com>',
        reply_to: Deno.env.get('RESPONDER_A') ?? 'omar@omarvaldez.com',
        to: [respuesta.correo],
        subject: 'Recibimos tu cuestionario - Curso Propedéutico DISDE',
        html: plantilla(respuesta.nombre, enlace),
      }),
    });

    if (!envio.ok) {
      const detalle = await envio.text();
      console.error('Resend rechazó el envío:', detalle);
      // 502 y no 500: el fallo es del proveedor de correo, no de esta función.
      // El formulario ya guardó la respuesta; esto solo afecta al correo.
      return new Response(JSON.stringify({ error: 'No se pudo enviar el correo' }), {
        status: 502,
        headers: { ...CORS, 'Content-Type': 'application/json' },
      });
    }

    await supabase
      .from('diagnostico_disde_respuestas')
      .update({ email_enviado: true, email_enviado_at: new Date().toISOString() })
      .eq('id', respuesta.id);

    return new Response(JSON.stringify({ ok: true }), {
      headers: { ...CORS, 'Content-Type': 'application/json' },
    });

  } catch (e) {
    console.error('Error inesperado:', e);
    return new Response(JSON.stringify({ error: 'Error interno' }), {
      status: 500,
      headers: { ...CORS, 'Content-Type': 'application/json' },
    });
  }
});
