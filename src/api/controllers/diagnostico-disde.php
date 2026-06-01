<?php
/**
 * API Controller para Diagnóstico DISDE
 *
 * POST /api/controllers/diagnostico-disde.php - Guardar respuesta del cuestionario
 * GET  /api/controllers/diagnostico-disde.php - Obtener todas las respuestas (admin)
 * GET  /api/controllers/diagnostico-disde.php?id=X - Obtener una respuesta específica
 * GET  /api/controllers/diagnostico-disde.php?analytics=true - Obtener análisis agregado
 */

require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../utils/Response.php';

try {
    $db = Database::getInstance();
    $method = $_SERVER['REQUEST_METHOD'];

    // POST - Guardar nueva respuesta
    if ($method === 'POST') {
        $data = Response::getRequestData();

        // Validar datos obligatorios
        $required = ['nombre', 'correo', 'grado', 'carrera_licenciatura', 'area_formacion', 'respuestas', 'conocimiento_total', 'detalle'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                Response::error("El campo '$field' es requerido", 400);
            }
        }

        // Validar email
        if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
            Response::error('Email inválido', 400);
        }

        // Organizar respuestas por área
        $respuestas = $data['respuestas'];
        $areas = [
            'area1' => ['A1', 'A2', 'A3', 'A4'],
            'area2' => ['B1', 'B2', 'B3', 'B4'],
            'area3' => ['C1', 'C2', 'C3', 'C4'],
            'area4' => ['D1', 'D2', 'D3', 'D4'],
            'area5' => ['E1', 'E2', 'E3', 'E4'],
            'area6' => ['F1', 'F2', 'F3', 'F4']
        ];

        $respuestasPorArea = [];
        foreach ($areas as $areaKey => $preguntas) {
            $respuestasPorArea[$areaKey] = [];
            foreach ($preguntas as $p) {
                if (isset($respuestas[$p])) {
                    $respuestasPorArea[$areaKey][$p] = $respuestas[$p];
                }
            }
        }

        // Insertar en base de datos
        $sql = "INSERT INTO diagnostico_disde_respuestas (
                    nombre, correo, grado_academico, carrera_licenciatura,
                    area_formacion, institucion_licenciatura, institucion_maestria,
                    conocimiento_total,
                    respuestas_area1, respuestas_area2, respuestas_area3,
                    respuestas_area4, respuestas_area5, respuestas_area6,
                    respuestas_completas, resultados_por_area,
                    ip_address, user_agent
                ) VALUES (
                    :nombre, :correo, :grado, :carrera,
                    :area_form, :institucion_lic, :institucion_maestria,
                    :conocimiento_total,
                    :resp_area1, :resp_area2, :resp_area3,
                    :resp_area4, :resp_area5, :resp_area6,
                    :respuestas_completas, :resultados_por_area,
                    :ip, :user_agent
                ) RETURNING id";

        $params = [
            ':nombre' => $data['nombre'],
            ':correo' => $data['correo'],
            ':grado' => $data['grado'],
            ':carrera' => $data['carrera_licenciatura'],
            ':area_form' => $data['area_formacion'],
            ':institucion_lic' => $data['institucion_licenciatura'] ?? null,
            ':institucion_maestria' => $data['institucion_maestria'] ?? null,
            ':conocimiento_total' => $data['conocimiento_total'],
            ':resp_area1' => json_encode($respuestasPorArea['area1']),
            ':resp_area2' => json_encode($respuestasPorArea['area2']),
            ':resp_area3' => json_encode($respuestasPorArea['area3']),
            ':resp_area4' => json_encode($respuestasPorArea['area4']),
            ':resp_area5' => json_encode($respuestasPorArea['area5']),
            ':resp_area6' => json_encode($respuestasPorArea['area6']),
            ':respuestas_completas' => json_encode($data['respuestas']),
            ':resultados_por_area' => json_encode($data['detalle']),
            ':ip' => Response::getClientIP(),
            ':user_agent' => Response::getUserAgent()
        ];

        $result = $db->selectOne($sql, $params);
        $insertedId = $result['id'];

        // Enviar email de confirmación
        $emailSent = enviarEmailConfirmacion($data);

        // Actualizar registro de email enviado
        if ($emailSent) {
            $db->update(
                "UPDATE diagnostico_disde_respuestas SET email_enviado = true, email_enviado_at = CURRENT_TIMESTAMP WHERE id = :id",
                [':id' => $insertedId]
            );
        }

        Response::success([
            'id' => $insertedId,
            'email_enviado' => $emailSent,
            'mensaje' => 'Respuesta guardada exitosamente. Revisa tu correo para las próximas instrucciones.'
        ], 'Cuestionario completado');
    }

    // GET - Obtener respuestas (admin)
    if ($method === 'GET') {
        // Analytics agregado
        if (isset($_GET['analytics'])) {
            $analytics = obtenerAnalytics($db);
            Response::success($analytics);
        }

        // Respuesta específica
        if (isset($_GET['id'])) {
            $sql = "SELECT * FROM diagnostico_disde_respuestas WHERE id = :id";
            $respuesta = $db->selectOne($sql, [':id' => $_GET['id']]);

            if (!$respuesta) {
                Response::error('Respuesta no encontrada', 404);
            }

            Response::success($respuesta);
        }

        // Todas las respuestas (con paginación)
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 20;
        $offset = ($page - 1) * $limit;

        // Contar total
        $total = $db->selectOne("SELECT COUNT(*) as count FROM diagnostico_disde_respuestas")['count'];

        // Obtener registros
        $sql = "SELECT id, nombre, correo, grado_academico, conocimiento_total,
                       created_at, email_enviado
                FROM diagnostico_disde_respuestas
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";

        $respuestas = $db->select($sql, [
            ':limit' => $limit,
            ':offset' => $offset
        ]);

        Response::success([
            'items' => $respuestas,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }

} catch (Exception $e) {
    error_log("Diagnostico DISDE error: " . $e->getMessage());
    Response::serverError($e->getMessage());
}

/**
 * Enviar email de confirmación con link a Bienvenida
 */
function enviarEmailConfirmacion($data) {
    $to = $data['correo'];
    $nombre = $data['nombre'];

    $subject = 'Recibimos tu cuestionario - Curso Propedéutico DISDE';

    // URL del servidor actual
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $enlaceBienvenida = "$protocol://$host/bienvenida-disde.php";

    // Plantilla HTML del email
    $htmlMessage = obtenerPlantillaEmail($nombre, $enlaceBienvenida);

    // Headers para email HTML
    $headers = [
        'From: noreply@omarvaldez.com',
        'Reply-To: omar@omarvaldez.com',
        'X-Mailer: PHP/' . phpversion(),
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8'
    ];

    return mail($to, $subject, $htmlMessage, implode("\r\n", $headers));
}

/**
 * Obtener plantilla HTML del email
 */
function obtenerPlantillaEmail($nombre, $enlaceSesion0) {
    return '<!DOCTYPE html>
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
                Estimado(a) <strong>' . htmlspecialchars($nombre) . '</strong>:
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
                    <a href="' . htmlspecialchars($enlaceSesion0) . '" target="_blank"
                       style="display:inline-block; background-color:#1B365D; color:#ffffff; text-decoration:none; font-size:16px; font-weight:bold; padding:14px 34px; border-radius:8px;">
                      Ver la Sesión 0 (Bienvenida)
                    </a>
                  </td>
                </tr>
              </table>
              <p style="margin:0 0 22px; color:#666666; font-size:13px; line-height:1.6; text-align:center;">
                Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
                <a href="' . htmlspecialchars($enlaceSesion0) . '" target="_blank" style="color:#2E5A9C; word-break:break-all;">' . htmlspecialchars($enlaceSesion0) . '</a>
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
</html>';
}

/**
 * Obtener analytics agregado de todas las respuestas
 */
function obtenerAnalytics($db) {
    // Total de respuestas
    $total = $db->selectOne("SELECT COUNT(*) as count FROM diagnostico_disde_respuestas")['count'];

    // Promedio de conocimiento
    $avgConocimiento = $db->selectOne("SELECT AVG(conocimiento_total) as avg FROM diagnostico_disde_respuestas")['avg'];

    // Distribución de puntajes
    $distribucion = $db->select("
        SELECT conocimiento_total, COUNT(*) as count
        FROM diagnostico_disde_respuestas
        GROUP BY conocimiento_total
        ORDER BY conocimiento_total
    ");

    // Emails enviados
    $emailsEnviados = $db->selectOne("SELECT COUNT(*) as count FROM diagnostico_disde_respuestas WHERE email_enviado = true")['count'];

    // Respuestas por día (últimos 30 días)
    $porDia = $db->select("
        SELECT DATE(created_at) as fecha, COUNT(*) as count
        FROM diagnostico_disde_respuestas
        WHERE created_at >= CURRENT_DATE - INTERVAL '30 days'
        GROUP BY DATE(created_at)
        ORDER BY fecha DESC
    ");

    // Distribución de grados académicos
    $porGrado = $db->select("
        SELECT grado_academico, COUNT(*) as count
        FROM diagnostico_disde_respuestas
        GROUP BY grado_academico
        ORDER BY count DESC
    ");

    return [
        'total_respuestas' => $total,
        'promedio_conocimiento' => round($avgConocimiento, 2),
        'distribucion_puntajes' => $distribucion,
        'emails_enviados' => $emailsEnviados,
        'respuestas_por_dia' => $porDia,
        'distribucion_grados' => $porGrado
    ];
}
