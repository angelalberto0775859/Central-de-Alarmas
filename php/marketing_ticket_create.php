<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/marketing_helpers.php';

function cdaMarketingEnsureRequesterUser($name, $email, $password, $currentUser = null) {
    $db = cdaDb();
    $stmt = $db->prepare('SELECT id, correo, password_hash, google_sub, activo FROM marketing_usuarios WHERE correo = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        if ((int) $user['activo'] !== 1) {
            throw new RuntimeException('inactive_user');
        }

        $isCurrentUser = $currentUser && (int) $currentUser['id'] === (int) $user['id'];
        if (!$isCurrentUser) {
            if (!empty($user['password_hash']) && !password_verify($password, $user['password_hash'])) {
                throw new RuntimeException('invalid_password');
            }

            if (empty($user['password_hash']) && !empty($user['google_sub'])) {
                throw new RuntimeException('google_only_user');
            }
        }

        $hash = !empty($user['password_hash']) ? $user['password_hash'] : password_hash($password, PASSWORD_DEFAULT);
        $update = $db->prepare('UPDATE marketing_usuarios SET nombre = ?, password_hash = ? WHERE id = ?');
        $update->execute([$name, $hash, $user['id']]);

        return (int) $user['id'];
    }

    $insert = $db->prepare(
        'INSERT INTO marketing_usuarios (nombre, correo, password_hash, rol, activo)
        VALUES (?, ?, ?, ?, 1)'
    );
    $insert->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), cdaMarketingDefaultUserRole($email)]);

    return (int) $db->lastInsertId();
}

function cdaMarketingTicketEmailTemplate($heading, $ticket, $files, $intro, $primaryUrl, $primaryText) {
    $fileList = $files ? '<ul><li>' . implode('</li><li>', array_map('htmlspecialchars', $files)) . '</li></ul>' : '<p>Sin archivos adjuntos.</p>';

    return '
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; color: #10213f; line-height: 1.55; }
            .wrap { max-width: 720px; margin: 0 auto; border: 1px solid #d8e3f0; border-radius: 8px; overflow: hidden; }
            .head { background: #063970; color: #fff; padding: 20px; }
            .head h1 { margin: 0; font-size: 22px; }
            .body { padding: 22px; background: #ffffff; }
            .row { border-bottom: 1px solid #edf2f7; padding: 10px 0; }
            .row strong { display: inline-block; min-width: 160px; color: #063970; }
            .box { background: #f4f8fc; border: 1px solid #d8e3f0; border-radius: 8px; padding: 14px; margin-top: 12px; }
            .button { display: inline-block; margin-top: 14px; background: #f6eb17; color: #063970; padding: 12px 16px; border-radius: 8px; text-decoration: none; font-weight: bold; }
            a { color: #063970; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="wrap">
            <div class="head"><h1>' . htmlspecialchars($heading) . '</h1></div>
            <div class="body">
                <p>' . htmlspecialchars($intro) . '</p>
                <div class="row"><strong>Folio:</strong> ' . htmlspecialchars($ticket['folio']) . '</div>
                <div class="row"><strong>Solicitante:</strong> ' . htmlspecialchars($ticket['solicitante']) . '</div>
                <div class="row"><strong>Correo:</strong> ' . htmlspecialchars($ticket['correo']) . '</div>
                <div class="row"><strong>Area:</strong> ' . htmlspecialchars($ticket['departamento']) . '</div>
                <div class="row"><strong>Actividad:</strong> ' . htmlspecialchars($ticket['actividad']) . '</div>
                <div class="row"><strong>Tipo:</strong> ' . htmlspecialchars($ticket['tipo_solicitud']) . '</div>
                <div class="row"><strong>Prioridad:</strong> ' . htmlspecialchars($ticket['prioridad']) . '</div>
                <div class="row"><strong>Fecha requerida:</strong> ' . htmlspecialchars(cdaMarketingFormatDate($ticket['fecha_requerida'])) . '</div>
                <div class="row"><strong>Publico objetivo:</strong> ' . htmlspecialchars($ticket['publico'] ?: 'No especificado') . '</div>
                <div class="row"><strong>Referencias:</strong> ' . nl2br(htmlspecialchars($ticket['referencias'] ?: 'No especificadas')) . '</div>
                <div class="box"><strong>Descripcion y objetivo</strong><br>' . nl2br(htmlspecialchars($ticket['objetivo'])) . '</div>
                <div class="box"><strong>Comentarios adicionales</strong><br>' . nl2br(htmlspecialchars($ticket['comentarios'] ?: 'Sin comentarios.')) . '</div>
                <div class="box"><strong>Archivos cargados</strong>' . $fileList . '</div>
                <a class="button" href="' . htmlspecialchars($primaryUrl) . '">' . htmlspecialchars($primaryText) . '</a>
            </div>
        </div>
    </body>
    </html>';
}

function cdaMarketingNotifyAdmins($ticket, $files) {
    $stmt = cdaDb()->query("SELECT correo FROM marketing_usuarios WHERE rol = 'admin' AND activo = 1");
    $admins = array_filter(array_map(function ($row) {
        return filter_var($row['correo'] ?? '', FILTER_VALIDATE_EMAIL);
    }, $stmt->fetchAll()));

    if (!$admins) {
        return;
    }

    $panelUrl = rtrim(CDA_SITE_URL, '/') . '/panel-marketing.php';

    $subject = 'Nuevo ticket de Marketing: ' . $ticket['folio'];
    $message = cdaMarketingTicketEmailTemplate(
        'Nuevo ticket de Marketing',
        $ticket,
        $files,
        'Se registro una nueva solicitud para evaluacion del equipo.',
        $panelUrl,
        'Abrir panel interno'
    );

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Central de Alarmas <no-reply@centraldealarmas.com.mx>\r\n";
    $headers .= "Reply-To: " . $ticket['correo'] . "\r\n";

    foreach ($admins as $adminEmail) {
        mail($adminEmail, $subject, $message, $headers);
    }
}

function cdaMarketingNotifyRequester($ticket, $files) {
    $seguimientoUrl = rtrim(CDA_SITE_URL, '/') . '/seguimiento.php?folio=' . urlencode($ticket['folio']) . '#chat';
    $subject = 'Confirmacion de ticket: ' . $ticket['folio'];
    $message = cdaMarketingTicketEmailTemplate(
        'Tu ticket fue creado correctamente',
        $ticket,
        $files,
        'Recibimos tu solicitud. Entra con tu usuario para consultar avance y escribir en el chat del ticket si necesitas agregar informacion.',
        $seguimientoUrl,
        'Abrir seguimiento y chat'
    );

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Central de Alarmas <no-reply@centraldealarmas.com.mx>\r\n";
    $headers .= "Reply-To: no-reply@centraldealarmas.com.mx\r\n";

    mail($ticket['correo'], $subject, $message, $headers);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    cdaMarketingJson(false, 'Metodo no permitido.');
}

$currentUser = cdaCurrentUser();
if (!$currentUser) {
    http_response_code(401);
    cdaMarketingJson(false, 'Inicia sesion para crear un ticket.');
}

if (!cdaVerifyCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    cdaMarketingJson(false, 'Solicitud no valida. Recarga la pagina e intenta de nuevo.');
}

$required = ['department', 'activity', 'requestType', 'objective', 'neededDate', 'priority'];
$missing = [];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        $missing[] = $field;
    }
}

if ($missing) {
    http_response_code(422);
    cdaMarketingJson(false, 'Faltan datos obligatorios para crear el ticket.');
}

$email = filter_var(strtolower(cdaMarketingClean($currentUser['correo'] ?? '')), FILTER_VALIDATE_EMAIL);
if (!$email) {
    http_response_code(422);
    cdaMarketingJson(false, 'El correo de tu perfil no es valido.');
}

$requester = cdaMarketingClean($currentUser['nombre'] ?? '');
$department = cdaMarketingOption($_POST['department'] ?? '', cdaMarketingDepartments());
$audience = cdaMarketingOption($_POST['audience'] ?? '', cdaMarketingAudiences(), '');
if ($requester === '') {
    http_response_code(422);
    cdaMarketingJson(false, 'Completa el nombre de tu perfil antes de crear tickets.');
}

$neededDate = cdaMarketingClean($_POST['neededDate']);
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $neededDate)) {
    http_response_code(422);
    cdaMarketingJson(false, 'La fecha requerida no es valida.');
}

$allowedFileExtensions = cdaMarketingTicketFileExtensions();
$allowedMimePrefixes = ['image/', 'video/', 'audio/', 'text/'];
$allowedMimeTypes = [
    'application/pdf',
    'application/rtf',
    'application/json',
    'application/xml',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-word.document.macroEnabled.12',
    'application/vnd.oasis.opendocument.text',
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
    'application/vnd.oasis.opendocument.presentation',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-excel.sheet.macroEnabled.12',
    'application/vnd.oasis.opendocument.spreadsheet',
    'text/csv',
    'application/csv',
    'application/zip',
    'application/x-zip-compressed',
    'application/x-7z-compressed',
    'application/x-tar',
    'application/gzip',
    'application/x-gzip',
    'application/vnd.rar',
    'application/x-rar',
    'application/x-rar-compressed',
    'application/octet-stream',
    'application/postscript',
    'application/illustrator',
    'application/vnd.adobe.illustrator',
    'application/photoshop',
    'application/x-photoshop',
    'image/vnd.adobe.photoshop',
    'image/svg+xml',
    'text/xml',
    'font/otf',
    'font/ttf',
    'font/woff',
    'font/woff2',
    'application/font-woff',
    'application/vnd.ms-fontobject',
    'model/gltf-binary',
    'model/gltf+json',
    'model/obj',
    'model/stl',
];
$maxFiles = 5;
$maxFileSize = 25 * 1024 * 1024;

$documentFiles = !empty($_FILES['documents']) ? cdaMarketingNormalizeFileUpload($_FILES['documents']) : null;

if ($documentFiles) {
    $uploadedCount = count(array_filter($documentFiles['name'], function ($name) {
        return trim((string) $name) !== '';
    }));

    if ($uploadedCount > $maxFiles) {
        http_response_code(422);
        cdaMarketingJson(false, 'Puedes adjuntar maximo 5 archivos por ticket.');
    }

    foreach ($documentFiles['name'] as $index => $name) {
        if ($documentFiles['error'][$index] === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        if ($documentFiles['error'][$index] !== UPLOAD_ERR_OK || (int) ($documentFiles['size'][$index] ?? 0) > $maxFileSize) {
            http_response_code(422);
            cdaMarketingJson(false, 'Uno de los archivos supera el limite permitido o no se cargo correctamente.');
        }

        $extension = strtolower(pathinfo((string) $name, PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedFileExtensions, true)) {
            http_response_code(422);
            cdaMarketingJson(false, 'Uno de los archivos tiene un formato no permitido.');
        }

        $mime = '';
        if (is_uploaded_file($documentFiles['tmp_name'][$index])) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = (string) $finfo->file($documentFiles['tmp_name'][$index]);
        }

        $prefixAllowed = false;
        foreach ($allowedMimePrefixes as $prefix) {
            if (strpos($mime, $prefix) === 0) {
                $prefixAllowed = true;
                break;
            }
        }

        if ($mime && !$prefixAllowed && !in_array($mime, $allowedMimeTypes, true)) {
            http_response_code(422);
            cdaMarketingJson(false, 'Uno de los archivos no coincide con un formato permitido.');
        }
    }
}

try {
    $db = cdaDb();
    $db->beginTransaction();

    try {
        $requesterUserId = (int) $currentUser['id'];
    } catch (RuntimeException $e) {
        $db->rollBack();
        http_response_code(422);
        cdaMarketingJson(false, 'Tu usuario existe, pero no esta activo. Pide a un administrador que lo reactive.');
    }

    $today = date('Y-m-d');
    $countStmt = $db->prepare('SELECT COUNT(*) FROM marketing_tickets WHERE DATE(creado_en) = ?');
    $countStmt->execute([$today]);
    $folio = cdaMarketingFolio($today, ((int) $countStmt->fetchColumn()) + 1, cdaMarketingRandomFolioSuffix());

    $stmt = $db->prepare(
        'INSERT INTO marketing_tickets
        (folio, solicitante, correo, departamento, actividad, tipo_solicitud, objetivo, publico, referencias, fecha_requerida, prioridad, estado, comentarios)
        VALUES
        (:folio, :solicitante, :correo, :departamento, :actividad, :tipo_solicitud, :objetivo, :publico, :referencias, :fecha_requerida, :prioridad, :estado, :comentarios)'
    );
    $stmt->execute([
        ':folio' => $folio,
        ':solicitante' => $requester,
        ':correo' => $email,
        ':departamento' => $department,
        ':actividad' => cdaMarketingClean($_POST['activity']),
        ':tipo_solicitud' => cdaMarketingClean($_POST['requestType']),
        ':objetivo' => cdaMarketingClean($_POST['objective']),
        ':publico' => $audience,
        ':referencias' => cdaMarketingClean($_POST['references'] ?? ''),
        ':fecha_requerida' => $neededDate,
        ':prioridad' => cdaMarketingPriority($_POST['priority']),
        ':estado' => 'Recibido',
        ':comentarios' => cdaMarketingClean($_POST['comments'] ?? ''),
    ]);

    $ticketId = (int) $db->lastInsertId();
    $hist = $db->prepare('INSERT INTO marketing_ticket_historial (ticket_id, estado, comentario) VALUES (?, ?, ?)');
    $hist->execute([$ticketId, 'Recibido', 'Ticket creado desde el formulario de Marketing.']);

    $messageStmt = $db->prepare(
        'INSERT INTO marketing_ticket_mensajes (ticket_id, usuario_id, autor_nombre, autor_rol, mensaje)
        VALUES (?, ?, ?, \'usuario\', ?)'
    );
    $messageStmt->execute([$ticketId, $requesterUserId, $requester, 'Ticket creado. Quedo listo el chat para seguimiento de esta solicitud.']);

    $uploadedFileNames = [];
    if ($documentFiles) {
        $uploadDir = dirname(__DIR__) . '/uploads/marketing/' . $folio;
        cdaMarketingEnsureUploadDir($uploadDir);

        $fileStmt = $db->prepare('INSERT INTO marketing_ticket_archivos (ticket_id, nombre_original, ruta, mime, tamano) VALUES (?, ?, ?, ?, ?)');

        foreach ($documentFiles['name'] as $index => $name) {
            if ($documentFiles['error'][$index] !== UPLOAD_ERR_OK) {
                continue;
            }

            $original = basename((string) $name);
            $extension = strtolower(pathinfo($original, PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedFileExtensions, true)) {
                continue;
            }

            $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '-', $original);
            $finalName = uniqid('archivo-', true) . '-' . $safeName;
            $absolutePath = $uploadDir . '/' . $finalName;
            $relativePath = 'uploads/marketing/' . $folio . '/' . $finalName;

            if (move_uploaded_file($documentFiles['tmp_name'][$index], $absolutePath)) {
                $uploadedFileNames[] = $original;
                $fileStmt->execute([
                    $ticketId,
                    $original,
                    $relativePath,
                    $documentFiles['type'][$index] ?? null,
                    (int) ($documentFiles['size'][$index] ?? 0),
                ]);
            } else {
                throw new RuntimeException('upload_move_failed');
            }
        }
    }

    $db->commit();

    $ticketPayload = [
        'folio' => $folio,
        'solicitante' => $requester,
        'correo' => $email,
        'departamento' => $department,
        'actividad' => cdaMarketingClean($_POST['activity']),
        'tipo_solicitud' => cdaMarketingClean($_POST['requestType']),
        'objetivo' => cdaMarketingClean($_POST['objective']),
        'publico' => $audience,
        'referencias' => cdaMarketingClean($_POST['references'] ?? ''),
        'fecha_requerida' => $neededDate,
        'prioridad' => cdaMarketingPriority($_POST['priority']),
        'comentarios' => cdaMarketingClean($_POST['comments'] ?? ''),
    ];

    cdaMarketingNotifyAdmins($ticketPayload, $uploadedFileNames);
    cdaMarketingNotifyRequester($ticketPayload, $uploadedFileNames);
    cdaMarketingJson(true, 'Ticket creado correctamente.', [
        'folio' => $folio,
        'seguimiento' => 'seguimiento.php?folio=' . urlencode($folio) . '#chat',
    ]);
} catch (Throwable $error) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    if (in_array($error->getMessage(), ['upload_dir_unavailable', 'upload_move_failed'], true)) {
        cdaMarketingJson(false, 'No fue posible guardar los archivos adjuntos. Revisa permisos de la carpeta uploads/marketing e intenta de nuevo.');
    }
    cdaMarketingJson(false, 'No fue posible crear el ticket. Revisa la configuracion de base de datos.');
}
