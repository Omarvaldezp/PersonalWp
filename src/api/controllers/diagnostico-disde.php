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
        $required = ['nombre', 'correo', 'grado', 'carrera', 'area_form', 'respuestas', 'conocimiento_total', 'detalle'];
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
                    area_formacion, institucion, conocimiento_total,
                    respuestas_area1, respuestas_area2, respuestas_area3,
                    respuestas_area4, respuestas_area5, respuestas_area6,
                    respuestas_completas, resultados_por_area,
                    ip_address, user_agent
                ) VALUES (
                    :nombre, :correo, :grado, :carrera,
                    :area_form, :institucion, :conocimiento_total,
                    :resp_area1, :resp_area2, :resp_area3,
                    :resp_area4, :resp_area5, :resp_area6,
                    :respuestas_completas, :resultados_por_area,
                    :ip, :user_agent
                ) RETURNING id";

        $params = [
            ':nombre' => $data['nombre'],
            ':correo' => $data['correo'],
            ':grado' => $data['grado'],
            ':carrera' => $data['carrera'],
            ':area_form' => $data['area_form'],
            ':institucion' => $data['institucion'] ?? null,
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
 * Enviar email de confirmación con PDF adjunto
 */
function enviarEmailConfirmacion($data) {
    $to = $data['correo'];
    $nombre = $data['nombre'];
    $puntaje = $data['conocimiento_total'];

    $subject = 'Diagnóstico DISDE - Confirmación de registro';

    // Mensaje (placeholder - el usuario lo definirá después)
    $message = "Estimado/a $nombre,\n\n";
    $message .= "Hemos recibido tu cuestionario diagnóstico del Curso Propedéutico DISDE.\n\n";
    $message .= "Tu puntaje de conocimiento: $puntaje/12\n\n";
    $message .= "En el archivo adjunto encontrarás información importante sobre el curso.\n\n";
    $message .= "Próximamente recibirás más instrucciones.\n\n";
    $message .= "Saludos,\n";
    $message .= "Dr. Omar Valdez Palazuelos\n";
    $message .= "Facilitador - Curso Propedéutico DISDE\n";
    $message .= "Facultad de Contaduría y Administración - UAS";

    // Headers
    $headers = [
        'From: noreply@omarvaldez.com',
        'Reply-To: omar@omarvaldez.com',
        'X-Mailer: PHP/' . phpversion(),
        'MIME-Version: 1.0'
    ];

    // Adjuntar PDF (si existe)
    $pdfPath = __DIR__ . '/../../uploads/diagnostico-disde-info.pdf';

    if (file_exists($pdfPath)) {
        // Email con adjunto
        $boundary = md5(time());

        $headers[] = 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';

        $body = "--$boundary\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $body .= $message . "\r\n\r\n";

        // Adjuntar PDF
        $fileContent = file_get_contents($pdfPath);
        $fileContentEncoded = chunk_split(base64_encode($fileContent));

        $body .= "--$boundary\r\n";
        $body .= "Content-Type: application/pdf; name=\"Informacion_DISDE.pdf\"\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n";
        $body .= "Content-Disposition: attachment; filename=\"Informacion_DISDE.pdf\"\r\n\r\n";
        $body .= $fileContentEncoded . "\r\n";
        $body .= "--$boundary--";

        return mail($to, $subject, $body, implode("\r\n", $headers));
    } else {
        // Email simple sin adjunto
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        return mail($to, $subject, $message, implode("\r\n", $headers));
    }
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
