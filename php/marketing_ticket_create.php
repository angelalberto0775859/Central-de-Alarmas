<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/marketing_helpers.php';

function cdaMarketingNotifyAdmins($ticket, $files) {
    $stmt = cdaDb()->query("SELECT correo FROM marketing_usuarios WHERE rol = 'admin' AND activo = 1");
    $admins = array_filter(array_map(function ($row) {
        return filter_var($row['correo'] ?? '', FILTER_VALIDATE_EMAIL);
    }, $stmt->fetchAll()));

    if (!$admins) {
        return;
    }

    $fileList = $files ? '<ul><li>' . implode('</li><li>', array_map('htmlspecialchars', $files)) . '</li></ul>' : '<p>Sin archivos adjuntos.</p>';
    $seguimientoUrl = rtrim(CDA_SITE_URL, '/') . '/seguimiento.php?folio=' . urlencode($ticket['folio']);
    $panelUrl = rtrim(CDA_SITE_URL, '/') . '/panel-marketing.php';

    $subject = 'Nuevo ticket de Diseño y Marketing: ' . $ticket['folio'];
    $message = '
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
            a { color: #063970; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="wrap">
            <div class="head"><h1>Nuevo ticket de Diseño y Marketing</h1></div>
            <div class="body">
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
                <p><a href="' . htmlspecialchars($panelUrl) . '">Abrir panel interno</a></p>
                <p><a href="' . htmlspecialchars($seguimientoUrl) . '">Ver seguimiento publico</a></p>
            </div>
        </div>
    </body>
    </html>';

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Central de Alarmas <no-reply@centraldealarmas.com.mx>\r\n";
    $headers .= "Reply-To: " . $ticket['correo'] . "\r\n";

    foreach ($admins as $adminEmail) {
        mail($adminEmail, $subject, $message, $headers);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    cdaMarketingJson(false, 'Metodo no permitido.');
}

$required = ['requester', 'email', 'department', 'activity', 'requestType', 'objective', 'neededDate', 'priority'];
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

$email = filter_var(cdaMarketingClean($_POST['email']), FILTER_VALIDATE_EMAIL);
if (!$email) {
    http_response_code(422);
    cdaMarketingJson(false, 'El correo del solicitante no es valido.');
}

$neededDate = cdaMarketingClean($_POST['neededDate']);
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $neededDate)) {
    http_response_code(422);
    cdaMarketingJson(false, 'La fecha requerida no es valida.');
}

$allowedFileExtensions = ['pdf','doc','docx','ppt','pptx','xls','xlsx','jpg','jpeg','png','webp','mp4','mov'];
$allowedMimePrefixes = ['image/', 'video/'];
$allowedMimeTypes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/zip',
    'application/octet-stream',
];
$maxFiles = 5;
$maxFileSize = 25 * 1024 * 1024;

if (!empty($_FILES['documents']) && is_array($_FILES['documents']['name'])) {
    $uploadedCount = count(array_filter($_FILES['documents']['name'], function ($name) {
        return trim((string) $name) !== '';
    }));

    if ($uploadedCount > $maxFiles) {
        http_response_code(422);
        cdaMarketingJson(false, 'Puedes adjuntar maximo 5 archivos por ticket.');
    }

    foreach ($_FILES['documents']['name'] as $index => $name) {
        if ($_FILES['documents']['error'][$index] === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        if ($_FILES['documents']['error'][$index] !== UPLOAD_ERR_OK || (int) ($_FILES['documents']['size'][$index] ?? 0) > $maxFileSize) {
            http_response_code(422);
            cdaMarketingJson(false, 'Uno de los archivos supera el limite permitido o no se cargo correctamente.');
        }

        $extension = strtolower(pathinfo((string) $name, PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedFileExtensions, true)) {
            http_response_code(422);
            cdaMarketingJson(false, 'Uno de los archivos tiene un formato no permitido.');
        }

        $mime = '';
        if (is_uploaded_file($_FILES['documents']['tmp_name'][$index])) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = (string) $finfo->file($_FILES['documents']['tmp_name'][$index]);
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
        ':solicitante' => cdaMarketingClean($_POST['requester']),
        ':correo' => $email,
        ':departamento' => cdaMarketingClean($_POST['department']),
        ':actividad' => cdaMarketingClean($_POST['activity']),
        ':tipo_solicitud' => cdaMarketingClean($_POST['requestType']),
        ':objetivo' => cdaMarketingClean($_POST['objective']),
        ':publico' => cdaMarketingClean($_POST['audience'] ?? ''),
        ':referencias' => cdaMarketingClean($_POST['references'] ?? ''),
        ':fecha_requerida' => $neededDate,
        ':prioridad' => cdaMarketingPriority($_POST['priority']),
        ':estado' => 'Recibido',
        ':comentarios' => cdaMarketingClean($_POST['comments'] ?? ''),
    ]);

    $ticketId = (int) $db->lastInsertId();
    $hist = $db->prepare('INSERT INTO marketing_ticket_historial (ticket_id, estado, comentario) VALUES (?, ?, ?)');
    $hist->execute([$ticketId, 'Recibido', 'Ticket creado desde el formulario de Diseño y Marketing.']);

    $uploadedFileNames = [];
    if (!empty($_FILES['documents']) && is_array($_FILES['documents']['name'])) {
        $uploadDir = dirname(__DIR__) . '/uploads/marketing/' . $folio;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileStmt = $db->prepare('INSERT INTO marketing_ticket_archivos (ticket_id, nombre_original, ruta, mime, tamano) VALUES (?, ?, ?, ?, ?)');

        foreach ($_FILES['documents']['name'] as $index => $name) {
            if ($_FILES['documents']['error'][$index] !== UPLOAD_ERR_OK) {
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

            if (move_uploaded_file($_FILES['documents']['tmp_name'][$index], $absolutePath)) {
                $uploadedFileNames[] = $original;
                $fileStmt->execute([
                    $ticketId,
                    $original,
                    $relativePath,
                    $_FILES['documents']['type'][$index] ?? null,
                    (int) ($_FILES['documents']['size'][$index] ?? 0),
                ]);
            }
        }
    }

    $db->commit();

    cdaMarketingNotifyAdmins([
        'folio' => $folio,
        'solicitante' => cdaMarketingClean($_POST['requester']),
        'correo' => $email,
        'departamento' => cdaMarketingClean($_POST['department']),
        'actividad' => cdaMarketingClean($_POST['activity']),
        'tipo_solicitud' => cdaMarketingClean($_POST['requestType']),
        'objetivo' => cdaMarketingClean($_POST['objective']),
        'publico' => cdaMarketingClean($_POST['audience'] ?? ''),
        'referencias' => cdaMarketingClean($_POST['references'] ?? ''),
        'fecha_requerida' => $neededDate,
        'prioridad' => cdaMarketingPriority($_POST['priority']),
        'comentarios' => cdaMarketingClean($_POST['comments'] ?? ''),
    ], $uploadedFileNames);

    cdaMarketingJson(true, 'Ticket creado correctamente.', [
        'folio' => $folio,
        'seguimiento' => 'seguimiento.php?folio=' . urlencode($folio),
    ]);
} catch (Throwable $error) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(500);
    cdaMarketingJson(false, 'No fue posible crear el ticket. Revisa la configuracion de base de datos.');
}
