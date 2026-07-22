<?php
function cdaMarketingClean($value) {
    return trim(strip_tags((string) $value));
}

function cdaMarketingStatuses() {
    return [
        'Recibido',
        'En evaluacion',
        'Pendiente de informacion',
        'Aprobado',
        'En diseno',
        'En revision',
        'Ajustes solicitados',
        'Programado',
        'Entregado',
        'Cerrado',
        'Rechazado',
    ];
}

function cdaMarketingStatusAllowed($status) {
    return in_array((string) $status, cdaMarketingStatuses(), true);
}

function cdaMarketingPriority($priority) {
    $priority = cdaMarketingClean($priority);
    return in_array($priority, ['Normal', 'Alta', 'Urgente'], true) ? $priority : 'Normal';
}

function cdaMarketingDepartments() {
    return [
        'Ventas',
        'Operaciones',
        'Monitoreo',
        'Atención a clientes',
        'Recursos Humanos',
        'Administración',
        'Tecnología',
        'Dirección',
        'Alianzas',
        'Otro',
    ];
}

function cdaMarketingAudiences() {
    return [
        'Clientes actuales',
        'Prospectos',
        'Colaboradores internos',
        'Sucursales',
        'Administradores',
        'Técnicos',
        'Corporativos',
        'Público general',
        'Otro',
    ];
}

function cdaMarketingOption($value, array $allowed, $fallback = 'Otro') {
    $value = cdaMarketingClean($value);
    return in_array($value, $allowed, true) ? $value : $fallback;
}

function cdaMarketingFolio($date, $sequence, $suffix = '') {
    $compactDate = preg_replace('/[^0-9]/', '', (string) $date);
    $folio = 'MKT-' . $compactDate . '-' . str_pad((string) (int) $sequence, 4, '0', STR_PAD_LEFT);
    $suffix = strtoupper(preg_replace('/[^A-Z0-9]/i', '', (string) $suffix));

    return $suffix ? $folio . '-' . substr($suffix, 0, 4) : $folio;
}

function cdaMarketingRandomFolioSuffix() {
    return strtoupper(bin2hex(random_bytes(2)));
}

function cdaMarketingJson($ok, $message, $extra = []) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array_merge([
        'exito' => $ok,
        'mensaje' => $message,
    ], $extra));
    exit;
}

function cdaMarketingFormatDate($date) {
    if (!$date) return 'Sin fecha';
    $timestamp = strtotime($date);
    return $timestamp ? date('d/m/Y', $timestamp) : $date;
}

function cdaMarketingStatusClass($status) {
    $slug = strtolower(str_replace(' ', '-', cdaMarketingClean($status)));
    return preg_replace('/[^a-z0-9-]/', '', $slug);
}

function cdaMarketingStatusLabel($status) {
    $labels = [
        'Recibido' => 'Recibido',
        'En evaluacion' => 'En evaluación',
        'Pendiente de informacion' => 'Pendiente de información',
        'Aprobado' => 'Aprobado',
        'En diseno' => 'En diseño',
        'En revision' => 'En revisión',
        'Ajustes solicitados' => 'Ajustes solicitados',
        'Programado' => 'Programado',
        'Entregado' => 'Entregado',
        'Cerrado' => 'Cerrado',
        'Rechazado' => 'Rechazado',
    ];

    return $labels[$status] ?? $status;
}

function cdaMarketingStatusTone($status) {
    $tones = [
        'Recibido' => 'blue',
        'En evaluacion' => 'blue',
        'Pendiente de informacion' => 'amber',
        'Aprobado' => 'purple',
        'Programado' => 'purple',
        'En diseno' => 'blue',
        'En revision' => 'amber',
        'Ajustes solicitados' => 'amber',
        'Entregado' => 'green',
        'Cerrado' => 'green',
        'Rechazado' => 'red',
    ];

    return $tones[$status] ?? 'blue';
}

function cdaMarketingPriorityTone($priority) {
    $tones = [
        'Normal' => 'gray',
        'Alta' => 'amber',
        'Urgente' => 'red',
    ];

    return $tones[$priority] ?? 'gray';
}

function cdaMarketingStatusEmailStatuses() {
    return [
        'Pendiente de informacion',
        'Ajustes solicitados',
        'Aprobado',
        'Programado',
        'Entregado',
        'Cerrado',
        'Rechazado',
    ];
}

function cdaMarketingStatusSendsEmail($status) {
    return in_array((string) $status, cdaMarketingStatusEmailStatuses(), true);
}

function cdaMarketingStatusEmailSubject($status, $folio) {
    return 'Actualizacion de ticket ' . cdaMarketingClean($folio) . ': ' . cdaMarketingStatusLabel($status);
}

function cdaMarketingStatusEmailIntro($status) {
    $messages = [
        'Pendiente de informacion' => 'El equipo necesita informacion o materiales adicionales para continuar con tu solicitud.',
        'Ajustes solicitados' => 'El ticket requiere ajustes o confirmaciones antes de avanzar.',
        'Aprobado' => 'Tu solicitud fue aprobada para continuar con el flujo de trabajo.',
        'Programado' => 'Tu solicitud ya quedo programada por el equipo.',
        'Entregado' => 'Tu solicitud fue marcada como entregada. Revisa el seguimiento y el chat del ticket.',
        'Cerrado' => 'Tu ticket fue cerrado con historial de seguimiento.',
        'Rechazado' => 'Tu solicitud fue rechazada. Revisa el comentario del equipo para conocer el motivo.',
    ];

    return $messages[$status] ?? 'Tu ticket tuvo una actualizacion importante.';
}

function cdaMarketingTicketUrl($folio) {
    return rtrim(CDA_SITE_URL, '/') . '/seguimiento.php?folio=' . urlencode((string) $folio) . '#chat';
}

function cdaMarketingSendStatusEmail($ticket, $oldStatus, $newStatus, $comment = '') {
    if (empty($ticket['correo']) || !filter_var($ticket['correo'], FILTER_VALIDATE_EMAIL) || !cdaMarketingStatusSendsEmail($newStatus)) {
        return false;
    }

    $subject = cdaMarketingStatusEmailSubject($newStatus, $ticket['folio'] ?? '');
    $statusLabel = cdaMarketingStatusLabel($newStatus);
    $oldStatusLabel = cdaMarketingStatusLabel($oldStatus);
    $commentHtml = $comment !== '' ? nl2br(htmlspecialchars($comment)) : 'Sin comentario adicional.';
    $url = cdaMarketingTicketUrl($ticket['folio'] ?? '');

    $message = '
    <html>
    <head><meta charset="UTF-8"></head>
    <body style="font-family:Arial,sans-serif;color:#10213f;line-height:1.55;">
        <div style="max-width:680px;margin:0 auto;border:1px solid #d8e3f0;border-radius:8px;overflow:hidden;">
            <div style="background:#063970;color:#fff;padding:18px 20px;">
                <h1 style="margin:0;font-size:21px;">Actualizacion de ticket</h1>
            </div>
            <div style="padding:20px;background:#fff;">
                <p>' . htmlspecialchars(cdaMarketingStatusEmailIntro($newStatus)) . '</p>
                <p><strong>Folio:</strong> ' . htmlspecialchars($ticket['folio'] ?? '') . '</p>
                <p><strong>Solicitud:</strong> ' . htmlspecialchars($ticket['actividad'] ?? '') . '</p>
                <p><strong>Estado anterior:</strong> ' . htmlspecialchars($oldStatusLabel) . '</p>
                <p><strong>Nuevo estado:</strong> ' . htmlspecialchars($statusLabel) . '</p>
                <div style="background:#f4f8fc;border:1px solid #d8e3f0;border-radius:8px;padding:12px;margin-top:12px;">
                    <strong>Comentario del equipo</strong><br>' . $commentHtml . '
                </div>
                <a href="' . htmlspecialchars($url) . '" style="display:inline-block;margin-top:14px;background:#f6eb17;color:#063970;padding:12px 16px;border-radius:8px;text-decoration:none;font-weight:bold;">Abrir seguimiento</a>
            </div>
        </div>
    </body>
    </html>';

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Central de Alarmas <no-reply@centraldealarmas.com.mx>\r\n";
    $headers .= "Reply-To: no-reply@centraldealarmas.com.mx\r\n";

    return mail($ticket['correo'], $subject, $message, $headers);
}

function cdaMarketingChatFileExtensions() {
    return ['pdf','doc','docx','ppt','pptx','xls','xlsx','jpg','jpeg','png','webp','mp4','mov','zip'];
}

function cdaMarketingCanUploadChatFiles($role) {
    return in_array((string) $role, ['admin', 'marketing', 'usuario'], true);
}

function cdaMarketingNormalizeUploadName($name) {
    $safe = preg_replace('/[^a-zA-Z0-9._-]/', '-', basename((string) $name));
    return trim($safe, '.-') ?: 'archivo';
}

function cdaMarketingAssigneeValue($value, array $allowedNames) {
    $value = cdaMarketingClean($value);
    if ($value === '') {
        return '';
    }

    return in_array($value, $allowedNames, true) ? $value : '';
}

function cdaMarketingFetchAssignableAdmins() {
    try {
        $stmt = cdaDb()->query("SELECT nombre, correo FROM marketing_usuarios WHERE rol = 'admin' AND activo = 1 ORDER BY nombre ASC");
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

function cdaMarketingEnsureUploadDir($path) {
    if (is_dir($path)) {
        return;
    }

    if (file_exists($path)) {
        throw new RuntimeException('upload_dir_unavailable');
    }

    if (!mkdir($path, 0755, true) && !is_dir($path)) {
        throw new RuntimeException('upload_dir_unavailable');
    }
}

function cdaMarketingFetchMessageFiles(array $messageIds) {
    $messageIds = array_values(array_unique(array_filter(array_map('intval', $messageIds))));
    if (!$messageIds) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($messageIds), '?'));
    try {
        $stmt = cdaDb()->prepare(
            "SELECT * FROM marketing_ticket_mensaje_archivos
            WHERE mensaje_id IN ($placeholders)
            ORDER BY creado_en ASC, id ASC"
        );
        $stmt->execute($messageIds);
    } catch (Throwable $e) {
        return [];
    }

    $files = [];
    foreach ($stmt->fetchAll() as $file) {
        $files[(int) $file['mensaje_id']][] = $file;
    }

    return $files;
}

function cdaMarketingFetchTicketFiles(array $ticketIds) {
    $ticketIds = array_values(array_unique(array_filter(array_map('intval', $ticketIds))));
    if (!$ticketIds) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($ticketIds), '?'));
    try {
        $stmt = cdaDb()->prepare(
            "SELECT * FROM marketing_ticket_archivos
            WHERE ticket_id IN ($placeholders)
            ORDER BY creado_en ASC, id ASC"
        );
        $stmt->execute($ticketIds);
    } catch (Throwable $e) {
        return [];
    }

    $files = [];
    foreach ($stmt->fetchAll() as $file) {
        $files[(int) $file['ticket_id']][] = $file;
    }

    return $files;
}

function cdaMarketingStoreMessageFiles($ticket, $messageId, $files) {
    if (empty($files['name']) || !is_array($files['name'])) {
        return [];
    }

    $allowed = cdaMarketingChatFileExtensions();
    $maxFiles = 5;
    $maxFileSize = 25 * 1024 * 1024;
    $uploadedCount = count(array_filter($files['name'], function ($name) {
        return trim((string) $name) !== '';
    }));

    if ($uploadedCount > $maxFiles) {
        throw new RuntimeException('too_many_chat_files');
    }

    $folio = preg_replace('/[^a-zA-Z0-9._-]/', '-', (string) ($ticket['folio'] ?? ('ticket-' . (int) ($ticket['id'] ?? 0))));
    $uploadDir = dirname(__DIR__) . '/uploads/marketing/' . $folio . '/chat';
    cdaMarketingEnsureUploadDir($uploadDir);

    $db = cdaDb();
    $stmt = $db->prepare(
        'INSERT INTO marketing_ticket_mensaje_archivos (mensaje_id, ticket_id, nombre_original, ruta, mime, tamano)
        VALUES (?, ?, ?, ?, ?, ?)'
    );

    $saved = [];
    foreach ($files['name'] as $index => $name) {
        if (($files['error'][$index] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        if (($files['error'][$index] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || (int) ($files['size'][$index] ?? 0) > $maxFileSize) {
            throw new RuntimeException('invalid_chat_file');
        }

        $original = basename((string) $name);
        $extension = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        if (!in_array($extension, $allowed, true)) {
            throw new RuntimeException('invalid_chat_extension');
        }

        $safeName = cdaMarketingNormalizeUploadName($original);
        $finalName = uniqid('mensaje-', true) . '-' . $safeName;
        $absolutePath = $uploadDir . '/' . $finalName;
        $relativePath = 'uploads/marketing/' . $folio . '/chat/' . $finalName;
        $tmpName = $files['tmp_name'][$index] ?? '';
        $moved = is_uploaded_file($tmpName)
            ? move_uploaded_file($tmpName, $absolutePath)
            : (is_file($tmpName) && rename($tmpName, $absolutePath));

        if ($moved) {
            $payload = [
                'nombre_original' => $original,
                'ruta' => $relativePath,
                'mime' => $files['type'][$index] ?? null,
                'tamano' => (int) ($files['size'][$index] ?? 0),
            ];
            $stmt->execute([
                (int) $messageId,
                (int) ($ticket['id'] ?? 0),
                $payload['nombre_original'],
                $payload['ruta'],
                $payload['mime'],
                $payload['tamano'],
            ]);
            $saved[] = $payload;
        } else {
            throw new RuntimeException('upload_move_failed');
        }
    }

    return $saved;
}

function cdaMarketingSendChatEmail($ticket, $authorName, $messageText, array $files = []) {
    if (empty($ticket['correo']) || !filter_var($ticket['correo'], FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $url = cdaMarketingTicketUrl($ticket['folio'] ?? '');
    $fileHtml = $files
        ? '<ul><li>' . implode('</li><li>', array_map(function ($file) {
            return htmlspecialchars($file['nombre_original'] ?? 'Archivo');
        }, $files)) . '</li></ul>'
        : '<p>Sin archivos adjuntos.</p>';

    $subject = 'Nuevo mensaje en ticket ' . cdaMarketingClean($ticket['folio'] ?? '');
    $message = '
    <html>
    <head><meta charset="UTF-8"></head>
    <body style="font-family:Arial,sans-serif;color:#10213f;line-height:1.55;">
        <div style="max-width:680px;margin:0 auto;border:1px solid #d8e3f0;border-radius:8px;overflow:hidden;">
            <div style="background:#063970;color:#fff;padding:18px 20px;">
                <h1 style="margin:0;font-size:21px;">Nuevo mensaje del equipo</h1>
            </div>
            <div style="padding:20px;background:#fff;">
                <p><strong>Folio:</strong> ' . htmlspecialchars($ticket['folio'] ?? '') . '</p>
                <p><strong>Solicitud:</strong> ' . htmlspecialchars($ticket['actividad'] ?? '') . '</p>
                <p><strong>Mensaje de:</strong> ' . htmlspecialchars($authorName) . '</p>
                <div style="background:#f4f8fc;border:1px solid #d8e3f0;border-radius:8px;padding:12px;margin-top:12px;">
                    ' . nl2br(htmlspecialchars($messageText)) . '
                </div>
                <div style="margin-top:12px;"><strong>Archivos enviados</strong>' . $fileHtml . '</div>
                <a href="' . htmlspecialchars($url) . '" style="display:inline-block;margin-top:14px;background:#f6eb17;color:#063970;padding:12px 16px;border-radius:8px;text-decoration:none;font-weight:bold;">Abrir seguimiento</a>
            </div>
        </div>
    </body>
    </html>';

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Central de Alarmas <no-reply@centraldealarmas.com.mx>\r\n";
    $headers .= "Reply-To: no-reply@centraldealarmas.com.mx\r\n";

    return mail($ticket['correo'], $subject, $message, $headers);
}

function cdaMarketingSendChatAdminEmail($ticket, $authorName, $messageText, array $files = []) {
    $stmt = cdaDb()->query("SELECT correo FROM marketing_usuarios WHERE rol = 'admin' AND activo = 1");
    $admins = array_filter(array_map(function ($row) {
        return filter_var($row['correo'] ?? '', FILTER_VALIDATE_EMAIL);
    }, $stmt->fetchAll()));

    if (!$admins) {
        return false;
    }

    $panelUrl = rtrim(CDA_SITE_URL, '/') . '/panel-marketing.php#ticket-' . (int) ($ticket['id'] ?? 0);
    $fileHtml = $files
        ? '<ul><li>' . implode('</li><li>', array_map(function ($file) {
            return htmlspecialchars($file['nombre_original'] ?? 'Archivo');
        }, $files)) . '</li></ul>'
        : '<p>Sin archivos adjuntos.</p>';

    $subject = 'Nuevo material en ticket ' . cdaMarketingClean($ticket['folio'] ?? '');
    $message = '
    <html>
    <head><meta charset="UTF-8"></head>
    <body style="font-family:Arial,sans-serif;color:#10213f;line-height:1.55;">
        <div style="max-width:680px;margin:0 auto;border:1px solid #d8e3f0;border-radius:8px;overflow:hidden;">
            <div style="background:#063970;color:#fff;padding:18px 20px;">
                <h1 style="margin:0;font-size:21px;">Nuevo material en el chat</h1>
            </div>
            <div style="padding:20px;background:#fff;">
                <p><strong>Folio:</strong> ' . htmlspecialchars($ticket['folio'] ?? '') . '</p>
                <p><strong>Solicitud:</strong> ' . htmlspecialchars($ticket['actividad'] ?? '') . '</p>
                <p><strong>Enviado por:</strong> ' . htmlspecialchars($authorName) . '</p>
                <div style="background:#f4f8fc;border:1px solid #d8e3f0;border-radius:8px;padding:12px;margin-top:12px;">
                    ' . nl2br(htmlspecialchars($messageText)) . '
                </div>
                <div style="margin-top:12px;"><strong>Archivos enviados</strong>' . $fileHtml . '</div>
                <a href="' . htmlspecialchars($panelUrl) . '" style="display:inline-block;margin-top:14px;background:#f6eb17;color:#063970;padding:12px 16px;border-radius:8px;text-decoration:none;font-weight:bold;">Abrir ticket</a>
            </div>
        </div>
    </body>
    </html>';

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Central de Alarmas <no-reply@centraldealarmas.com.mx>\r\n";
    $headers .= "Reply-To: no-reply@centraldealarmas.com.mx\r\n";

    foreach ($admins as $adminEmail) {
        mail($adminEmail, $subject, $message, $headers);
    }

    return true;
}

function cdaMarketingFetchTicketMessages(array $ticketIds) {
    $ticketIds = array_values(array_unique(array_filter(array_map('intval', $ticketIds))));
    if (!$ticketIds) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($ticketIds), '?'));
    try {
        $stmt = cdaDb()->prepare(
            "SELECT m.*, u.nombre AS usuario_nombre, u.rol AS usuario_rol
            FROM marketing_ticket_mensajes m
            LEFT JOIN marketing_usuarios u ON u.id = m.usuario_id
            WHERE m.ticket_id IN ($placeholders)
            ORDER BY m.creado_en ASC, m.id ASC"
        );
        $stmt->execute($ticketIds);
    } catch (PDOException $e) {
        return [];
    }

    $rows = $stmt->fetchAll();
    $filesByMessage = cdaMarketingFetchMessageFiles(array_column($rows, 'id'));

    $messages = [];
    foreach ($rows as $message) {
        $message['archivos'] = $filesByMessage[(int) $message['id']] ?? [];
        $messages[(int) $message['ticket_id']][] = $message;
    }

    return $messages;
}

function cdaMarketingMessageAuthor($message) {
    if (!empty($message['usuario_nombre'])) {
        return $message['usuario_nombre'];
    }

    return !empty($message['autor_nombre']) ? $message['autor_nombre'] : 'Equipo';
}

function cdaMarketingProgressSteps() {
    return [
        'Recibido' => 'Recibido',
        'En evaluacion' => 'Evaluación',
        'En diseno' => 'Diseño',
        'En revision' => 'Revisión',
        'Entregado' => 'Entrega',
    ];
}

function cdaMarketingProgressIndex($status) {
    $map = [
        'Recibido' => 0,
        'En evaluacion' => 1,
        'Pendiente de informacion' => 1,
        'Aprobado' => 1,
        'Programado' => 2,
        'En diseno' => 2,
        'En revision' => 3,
        'Ajustes solicitados' => 3,
        'Entregado' => 4,
        'Cerrado' => 4,
        'Rechazado' => 1,
    ];

    return $map[$status] ?? 0;
}
