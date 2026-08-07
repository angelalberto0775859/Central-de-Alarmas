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

function cdaMarketingOptionalDate($date) {
    $date = cdaMarketingClean($date);
    if ($date === '') {
        return null;
    }

    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : null;
}

function cdaMarketingRedirect($returnTo, $fallback = 'panel-marketing.php', $message = '', $messageType = 'error', $fragment = '') {
    $target = function_exists('cdaSafeLocalReturnTo')
        ? cdaSafeLocalReturnTo($returnTo, $fallback)
        : ($returnTo ?: $fallback);

    if ($message !== '' && session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION[$messageType === 'success' ? 'cda_marketing_success' : 'cda_marketing_error'] = $message;
    }

    if ($fragment !== '') {
        $target = preg_replace('/#.*$/', '', $target) . '#' . ltrim($fragment, '#');
    }

    header('Location: ' . $target);
    exit;
}

function cdaMarketingTableExists($table) {
    static $cache = [];
    $table = cdaMarketingClean($table);
    if ($table === '') return false;
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) return false;
    if (array_key_exists($table, $cache)) return $cache[$table];

    try {
        $stmt = cdaDb()->prepare('SHOW TABLES LIKE ?');
        $stmt->execute([$table]);
        $cache[$table] = (bool) $stmt->fetchColumn();
    } catch (Throwable $e) {
        $cache[$table] = false;
    }

    return $cache[$table];
}

function cdaMarketingColumnExists($table, $column) {
    static $cache = [];
    $table = cdaMarketingClean($table);
    $column = cdaMarketingClean($column);
    $key = $table . '.' . $column;
    if ($table === '' || $column === '') return false;
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $column)) return false;
    if (array_key_exists($key, $cache)) return $cache[$key];

    try {
        $stmt = cdaDb()->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
        $stmt->execute([$column]);
        $cache[$key] = (bool) $stmt->fetch();
    } catch (Throwable $e) {
        $cache[$key] = false;
    }

    return $cache[$key];
}

function cdaMarketingIndexExists($table, $index) {
    static $cache = [];
    $table = cdaMarketingClean($table);
    $index = cdaMarketingClean($index);
    $key = $table . '.' . $index;
    if ($table === '' || $index === '') return false;
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $index)) return false;
    if (array_key_exists($key, $cache)) return $cache[$key];

    try {
        $stmt = cdaDb()->prepare("SHOW INDEX FROM `$table` WHERE Key_name = ?");
        $stmt->execute([$index]);
        $cache[$key] = (bool) $stmt->fetch();
    } catch (Throwable $e) {
        $cache[$key] = false;
    }

    return $cache[$key];
}

function cdaMarketingTryExec($sql) {
    try {
        cdaDb()->exec($sql);
        return true;
    } catch (Throwable $e) {
        error_log('Marketing schema repair skipped: ' . $e->getMessage());
        return false;
    }
}

function cdaMarketingEnsureTable($table, $sql) {
    if (!cdaMarketingTableExists($table)) {
        return cdaMarketingTryExec($sql);
    }

    return true;
}

function cdaMarketingEnsureColumn($table, $column, $sql) {
    if (!cdaMarketingColumnExists($table, $column)) {
        return cdaMarketingTryExec($sql);
    }

    return true;
}

function cdaMarketingEnsureIndex($table, $index, $sql) {
    if (!cdaMarketingIndexExists($table, $index)) {
        return cdaMarketingTryExec($sql);
    }

    return true;
}

function cdaMarketingEnsureTicketSchema() {
    static $checked = false;
    if ($checked) return;
    $checked = true;

    if (!cdaMarketingTableExists('marketing_tickets')) {
        return;
    }

    cdaMarketingEnsureColumn('marketing_tickets', 'fecha_entrega_estimada', "ALTER TABLE marketing_tickets ADD COLUMN fecha_entrega_estimada DATE NULL");
    cdaMarketingEnsureColumn('marketing_tickets', 'respuesta_interna', "ALTER TABLE marketing_tickets ADD COLUMN respuesta_interna TEXT NULL");
    cdaMarketingEnsureColumn('marketing_tickets', 'asignado_a', "ALTER TABLE marketing_tickets ADD COLUMN asignado_a VARCHAR(140) NULL");
    cdaMarketingEnsureColumn('marketing_tickets', 'eliminado_en', "ALTER TABLE marketing_tickets ADD COLUMN eliminado_en TIMESTAMP NULL");
    cdaMarketingEnsureColumn('marketing_tickets', 'eliminado_por', "ALTER TABLE marketing_tickets ADD COLUMN eliminado_por INT UNSIGNED NULL");
    cdaMarketingEnsureColumn('marketing_tickets', 'actualizado_en', "ALTER TABLE marketing_tickets ADD COLUMN actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    cdaMarketingEnsureIndex('marketing_tickets', 'idx_fecha_entrega_estimada', 'CREATE INDEX idx_fecha_entrega_estimada ON marketing_tickets (fecha_entrega_estimada)');
    cdaMarketingEnsureIndex('marketing_tickets', 'idx_eliminado', 'CREATE INDEX idx_eliminado ON marketing_tickets (eliminado_en)');

    cdaMarketingEnsureTable(
        'marketing_ticket_archivos',
        "CREATE TABLE marketing_ticket_archivos (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            ticket_id INT UNSIGNED NOT NULL,
            nombre_original VARCHAR(255) NOT NULL,
            ruta VARCHAR(500) NOT NULL,
            mime VARCHAR(160) NULL,
            tamano INT UNSIGNED NOT NULL DEFAULT 0,
            creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ticket_id (ticket_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    if (cdaMarketingTableExists('marketing_ticket_archivos')) {
        cdaMarketingEnsureColumn('marketing_ticket_archivos', 'ticket_id', "ALTER TABLE marketing_ticket_archivos ADD COLUMN ticket_id INT UNSIGNED NULL");
        cdaMarketingEnsureColumn('marketing_ticket_archivos', 'nombre_original', "ALTER TABLE marketing_ticket_archivos ADD COLUMN nombre_original VARCHAR(255) NULL");
        cdaMarketingEnsureColumn('marketing_ticket_archivos', 'ruta', "ALTER TABLE marketing_ticket_archivos ADD COLUMN ruta VARCHAR(500) NULL");
        cdaMarketingEnsureColumn('marketing_ticket_archivos', 'mime', "ALTER TABLE marketing_ticket_archivos ADD COLUMN mime VARCHAR(160) NULL");
        cdaMarketingEnsureColumn('marketing_ticket_archivos', 'tamano', "ALTER TABLE marketing_ticket_archivos ADD COLUMN tamano INT UNSIGNED NOT NULL DEFAULT 0");
        cdaMarketingEnsureColumn('marketing_ticket_archivos', 'creado_en', "ALTER TABLE marketing_ticket_archivos ADD COLUMN creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
        cdaMarketingEnsureIndex('marketing_ticket_archivos', 'idx_ticket_id', 'CREATE INDEX idx_ticket_id ON marketing_ticket_archivos (ticket_id)');
    }

    cdaMarketingEnsureTable(
        'marketing_ticket_historial',
        "CREATE TABLE IF NOT EXISTS marketing_ticket_historial (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ticket_id INT NOT NULL,
            usuario_id INT NULL,
            estado VARCHAR(80) NOT NULL,
            comentario TEXT NULL,
            creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ticket_historial (ticket_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    if (cdaMarketingTableExists('marketing_ticket_historial')) {
        cdaMarketingEnsureColumn('marketing_ticket_historial', 'ticket_id', "ALTER TABLE marketing_ticket_historial ADD COLUMN ticket_id INT UNSIGNED NULL");
        cdaMarketingEnsureColumn('marketing_ticket_historial', 'usuario_id', "ALTER TABLE marketing_ticket_historial ADD COLUMN usuario_id INT UNSIGNED NULL");
        cdaMarketingEnsureColumn('marketing_ticket_historial', 'estado', "ALTER TABLE marketing_ticket_historial ADD COLUMN estado VARCHAR(80) NOT NULL DEFAULT 'Recibido'");
        cdaMarketingEnsureColumn('marketing_ticket_historial', 'comentario', "ALTER TABLE marketing_ticket_historial ADD COLUMN comentario TEXT NULL");
        cdaMarketingEnsureColumn('marketing_ticket_historial', 'creado_en', "ALTER TABLE marketing_ticket_historial ADD COLUMN creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
        cdaMarketingEnsureIndex('marketing_ticket_historial', 'idx_ticket_historial', 'CREATE INDEX idx_ticket_historial ON marketing_ticket_historial (ticket_id)');
        cdaMarketingEnsureIndex('marketing_ticket_historial', 'idx_usuario_id', 'CREATE INDEX idx_usuario_id ON marketing_ticket_historial (usuario_id)');
    }

    cdaMarketingEnsureTable(
        'marketing_ticket_mensajes',
        "CREATE TABLE IF NOT EXISTS marketing_ticket_mensajes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ticket_id INT NOT NULL,
            usuario_id INT NULL,
            autor_nombre VARCHAR(140) NOT NULL,
            autor_rol ENUM('admin','usuario') NOT NULL DEFAULT 'usuario',
            mensaje TEXT NOT NULL,
            creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ticket_mensajes (ticket_id),
            INDEX idx_usuario_mensajes (usuario_id),
            INDEX idx_creado_mensajes (creado_en)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    if (cdaMarketingTableExists('marketing_ticket_mensajes')) {
        cdaMarketingEnsureColumn('marketing_ticket_mensajes', 'ticket_id', "ALTER TABLE marketing_ticket_mensajes ADD COLUMN ticket_id INT UNSIGNED NULL");
        cdaMarketingEnsureColumn('marketing_ticket_mensajes', 'usuario_id', "ALTER TABLE marketing_ticket_mensajes ADD COLUMN usuario_id INT UNSIGNED NULL");
        cdaMarketingEnsureColumn('marketing_ticket_mensajes', 'autor_nombre', "ALTER TABLE marketing_ticket_mensajes ADD COLUMN autor_nombre VARCHAR(140) NOT NULL DEFAULT 'Equipo'");
        cdaMarketingEnsureColumn('marketing_ticket_mensajes', 'autor_rol', "ALTER TABLE marketing_ticket_mensajes ADD COLUMN autor_rol ENUM('admin','usuario') NOT NULL DEFAULT 'usuario'");
        cdaMarketingEnsureColumn('marketing_ticket_mensajes', 'mensaje', "ALTER TABLE marketing_ticket_mensajes ADD COLUMN mensaje TEXT NULL");
        cdaMarketingEnsureColumn('marketing_ticket_mensajes', 'creado_en', "ALTER TABLE marketing_ticket_mensajes ADD COLUMN creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
        cdaMarketingTryExec("UPDATE marketing_ticket_mensajes SET autor_rol = 'admin' WHERE autor_rol = 'marketing'");
        cdaMarketingTryExec("ALTER TABLE marketing_ticket_mensajes MODIFY autor_rol ENUM('admin','usuario') NOT NULL DEFAULT 'usuario'");
        cdaMarketingEnsureIndex('marketing_ticket_mensajes', 'idx_ticket_id', 'CREATE INDEX idx_ticket_id ON marketing_ticket_mensajes (ticket_id)');
        cdaMarketingEnsureIndex('marketing_ticket_mensajes', 'idx_usuario_id', 'CREATE INDEX idx_usuario_id ON marketing_ticket_mensajes (usuario_id)');
        cdaMarketingEnsureIndex('marketing_ticket_mensajes', 'idx_creado', 'CREATE INDEX idx_creado ON marketing_ticket_mensajes (creado_en)');
    }

    cdaMarketingEnsureTable(
        'marketing_ticket_mensaje_archivos',
        "CREATE TABLE IF NOT EXISTS marketing_ticket_mensaje_archivos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            mensaje_id INT NOT NULL,
            ticket_id INT NOT NULL,
            nombre_original VARCHAR(255) NOT NULL,
            ruta VARCHAR(500) NOT NULL,
            mime VARCHAR(160) NULL,
            tamano INT NULL,
            creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_mensaje_archivos (mensaje_id),
            INDEX idx_ticket_mensaje_archivos (ticket_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    if (cdaMarketingTableExists('marketing_ticket_mensaje_archivos')) {
        cdaMarketingEnsureColumn('marketing_ticket_mensaje_archivos', 'mensaje_id', "ALTER TABLE marketing_ticket_mensaje_archivos ADD COLUMN mensaje_id INT UNSIGNED NULL");
        cdaMarketingEnsureColumn('marketing_ticket_mensaje_archivos', 'ticket_id', "ALTER TABLE marketing_ticket_mensaje_archivos ADD COLUMN ticket_id INT UNSIGNED NULL");
        cdaMarketingEnsureColumn('marketing_ticket_mensaje_archivos', 'nombre_original', "ALTER TABLE marketing_ticket_mensaje_archivos ADD COLUMN nombre_original VARCHAR(255) NULL");
        cdaMarketingEnsureColumn('marketing_ticket_mensaje_archivos', 'ruta', "ALTER TABLE marketing_ticket_mensaje_archivos ADD COLUMN ruta VARCHAR(500) NULL");
        cdaMarketingEnsureColumn('marketing_ticket_mensaje_archivos', 'mime', "ALTER TABLE marketing_ticket_mensaje_archivos ADD COLUMN mime VARCHAR(160) NULL");
        cdaMarketingEnsureColumn('marketing_ticket_mensaje_archivos', 'tamano', "ALTER TABLE marketing_ticket_mensaje_archivos ADD COLUMN tamano INT UNSIGNED NOT NULL DEFAULT 0");
        cdaMarketingEnsureColumn('marketing_ticket_mensaje_archivos', 'creado_en', "ALTER TABLE marketing_ticket_mensaje_archivos ADD COLUMN creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
        cdaMarketingEnsureIndex('marketing_ticket_mensaje_archivos', 'idx_mensaje_id', 'CREATE INDEX idx_mensaje_id ON marketing_ticket_mensaje_archivos (mensaje_id)');
        cdaMarketingEnsureIndex('marketing_ticket_mensaje_archivos', 'idx_ticket_id', 'CREATE INDEX idx_ticket_id ON marketing_ticket_mensaje_archivos (ticket_id)');
    }
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
        'En diseno' => 'En producción',
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

function cdaMarketingTicketFileExtensions() {
    return [
        'pdf','txt','rtf','md','csv','json','xml',
        'doc','docx','docm','odt','pages',
        'ppt','pptx','pptm','odp','key',
        'xls','xlsx','xlsm','ods','numbers',
        'jpg','jpeg','png','webp','gif','bmp','tif','tiff','heic','heif','ico','svg',
        'mp4','mov','m4v','avi','mkv','webm',
        'mp3','wav','m4a','aac','ogg','flac',
        'zip','rar','7z','tar','gz',
        'ai','psd','eps','indd','indt','xd','fig','sketch','cdr',
        'otf','ttf','woff','woff2',
        'glb','gltf','obj','fbx','stl',
    ];
}

function cdaMarketingAllowedUploadAccept() {
    return '.' . implode(',.', cdaMarketingTicketFileExtensions());
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

function cdaMarketingMailHeaders($replyTo = 'no-reply@centraldealarmas.com.mx') {
    $replyTo = filter_var($replyTo, FILTER_VALIDATE_EMAIL) ?: 'no-reply@centraldealarmas.com.mx';

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Central de Alarmas <no-reply@centraldealarmas.com.mx>\r\n";
    $headers .= "Reply-To: " . $replyTo . "\r\n";

    return $headers;
}

function cdaMarketingUniqueEmails(array $emails) {
    $unique = [];
    foreach ($emails as $email) {
        $email = filter_var(strtolower(cdaMarketingClean($email)), FILTER_VALIDATE_EMAIL);
        if ($email) {
            $unique[$email] = $email;
        }
    }

    return array_values($unique);
}

function cdaMarketingTicketInternalRecipientEmails($ticket = []) {
    try {
        $stmt = cdaDb()->query("SELECT nombre, correo, rol FROM marketing_usuarios WHERE rol IN ('admin','manager') AND activo = 1");
        $rows = $stmt->fetchAll();
    } catch (Throwable $e) {
        $rows = [];
    }

    $assignedName = cdaMarketingClean($ticket['asignado_a'] ?? '');
    if ($assignedName !== '') {
        try {
            $assignedStmt = cdaDb()->prepare("SELECT nombre, correo, rol FROM marketing_usuarios WHERE nombre = ? AND rol IN ('admin','manager','trabajador') AND activo = 1");
            $assignedStmt->execute([$assignedName]);
            $rows = array_merge($rows, $assignedStmt->fetchAll());
        } catch (Throwable $e) {
            error_log('No fue posible consultar involucrados del ticket: ' . $e->getMessage());
        }
    }

    return cdaMarketingUniqueEmails(array_map(function ($row) {
        return $row['correo'] ?? '';
    }, $rows));
}

function cdaMarketingSendHtmlMailToMany(array $recipients, $subject, $message, $headers) {
    $sent = false;
    foreach (cdaMarketingUniqueEmails($recipients) as $email) {
        $sent = mail($email, $subject, $message, $headers) || $sent;
    }

    return $sent;
}

function cdaMarketingSendStatusEmail($ticket, $oldStatus, $newStatus, $comment = '') {
    if (!cdaMarketingStatusSendsEmail($newStatus)) {
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
                <p><strong>Entrega aproximada:</strong> ' . htmlspecialchars(cdaMarketingFormatDate($ticket['fecha_entrega_estimada'] ?? null)) . '</p>
                <div style="background:#f4f8fc;border:1px solid #d8e3f0;border-radius:8px;padding:12px;margin-top:12px;">
                    <strong>Comentario del equipo</strong><br>' . $commentHtml . '
                </div>
                <a href="' . htmlspecialchars($url) . '" style="display:inline-block;margin-top:14px;background:#f6eb17;color:#063970;padding:12px 16px;border-radius:8px;text-decoration:none;font-weight:bold;">Abrir seguimiento</a>
            </div>
        </div>
    </body>
    </html>';

    $recipients = cdaMarketingUniqueEmails(array_merge(
        [$ticket['correo'] ?? ''],
        cdaMarketingTicketInternalRecipientEmails($ticket)
    ));

    return cdaMarketingSendHtmlMailToMany($recipients, $subject, $message, cdaMarketingMailHeaders());
}

function cdaMarketingChatFileExtensions() {
    return cdaMarketingTicketFileExtensions();
}

function cdaMarketingCanUploadChatFiles($role) {
    return in_array((string) $role, ['admin', 'usuario', 'manager', 'trabajador'], true);
}

function cdaMarketingRoleLabel($role) {
    $labels = [
        'admin' => 'Admin',
        'manager' => 'Manager',
        'trabajador' => 'Trabajador',
        'usuario' => 'Usuario',
    ];

    return $labels[(string) $role] ?? 'Usuario';
}

function cdaMarketingRoleClass($role) {
    $role = strtolower(cdaMarketingClean($role));
    return in_array($role, ['admin', 'manager', 'trabajador', 'usuario'], true) ? $role : 'usuario';
}

function cdaMarketingFixedRoleByEmail($email) {
    $email = strtolower(cdaMarketingClean($email));
    $roles = [
        'angelalberto077@gmail.com' => 'admin',
    ];

    return $roles[$email] ?? null;
}

function cdaMarketingInitialRoleByEmail($email) {
    $email = strtolower(cdaMarketingClean($email));
    $roles = [
        'rvillaverde@centraldealarmas.com.mx' => 'trabajador',
    ];

    return $roles[$email] ?? null;
}

function cdaMarketingUserRoleValue($email, $requestedRole = 'usuario') {
    $fixedRole = cdaMarketingFixedRoleByEmail($email);
    if ($fixedRole !== null) {
        return $fixedRole;
    }

    $requestedRole = cdaMarketingRoleClass($requestedRole);
    return $requestedRole === 'admin' ? 'usuario' : $requestedRole;
}

function cdaMarketingDefaultUserRole($email) {
    $fixedRole = cdaMarketingFixedRoleByEmail($email);
    if ($fixedRole !== null) {
        return $fixedRole;
    }

    $initialRole = cdaMarketingInitialRoleByEmail($email);
    return $initialRole !== null ? $initialRole : 'usuario';
}

function cdaMarketingProtectedUserEmail($email) {
    return cdaMarketingFixedRoleByEmail($email) !== null;
}

function cdaMarketingSyncUserTicketEmail($oldEmail, $newEmail) {
    $oldEmail = filter_var(strtolower(cdaMarketingClean($oldEmail)), FILTER_VALIDATE_EMAIL);
    $newEmail = filter_var(strtolower(cdaMarketingClean($newEmail)), FILTER_VALIDATE_EMAIL);
    if (!$oldEmail || !$newEmail || $oldEmail === $newEmail) {
        return 0;
    }

    try {
        cdaMarketingEnsureTicketSchema();
        $stmt = cdaDb()->prepare('UPDATE marketing_tickets SET correo = ? WHERE correo = ?');
        $stmt->execute([$newEmail, $oldEmail]);
        return $stmt->rowCount();
    } catch (Throwable $e) {
        error_log('No fue posible sincronizar correo de tickets de marketing: ' . $e->getMessage());
        throw $e;
    }
}

function cdaMarketingEnsureUserRoleSchema() {
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    try {
        $stmt = cdaDb()->query("SHOW COLUMNS FROM marketing_usuarios LIKE 'rol'");
        $column = $stmt->fetch();
        $type = strtolower((string) ($column['Type'] ?? ''));

        if (strpos($type, "'manager'") === false || strpos($type, "'trabajador'") === false) {
            $db = cdaDb();
            $db->exec("ALTER TABLE marketing_usuarios MODIFY rol ENUM('admin','usuario','marketing','manager','trabajador') NOT NULL DEFAULT 'usuario'");
            $db->exec("UPDATE marketing_usuarios SET rol = 'manager' WHERE rol = 'marketing'");
            $db->exec("ALTER TABLE marketing_usuarios MODIFY rol ENUM('admin','usuario','manager','trabajador') NOT NULL DEFAULT 'usuario'");
        }
    } catch (Throwable $e) {
        error_log('No fue posible actualizar el esquema de roles de marketing: ' . $e->getMessage());
    }
}

function cdaMarketingEnforceFixedUserRoles() {
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    try {
        $db = cdaDb();
        $db->exec("UPDATE marketing_usuarios SET rol = 'usuario' WHERE rol = 'admin' AND correo <> 'angelalberto077@gmail.com'");

        $stmt = $db->prepare('UPDATE marketing_usuarios SET rol = ?, activo = 1 WHERE correo = ?');
        $stmt->execute(['admin', 'angelalberto077@gmail.com']);
    } catch (Throwable $e) {
        error_log('No fue posible reforzar los roles fijos de marketing: ' . $e->getMessage());
    }
}

function cdaMarketingCanManageUsers($role) {
    return (string) $role === 'admin';
}

function cdaMarketingCanManageTrash($role) {
    return (string) $role === 'admin';
}

function cdaMarketingCanManageTickets($role) {
    return in_array((string) $role, ['admin', 'manager'], true);
}

function cdaMarketingCanAccessBoard($role) {
    return in_array((string) $role, ['admin', 'manager', 'trabajador'], true);
}

function cdaMarketingCanViewAllTickets($role) {
    return in_array((string) $role, ['admin', 'manager'], true);
}

function cdaMarketingDefaultRouteForRole($role) {
    if (cdaMarketingCanManageUsers($role) || cdaMarketingCanViewAllTickets($role)) {
        return 'estadisticas-marketing.php';
    }

    if (cdaMarketingCanAccessBoard($role)) {
        return 'control-marketing.php';
    }

    return 'perfil-marketing.php';
}

function cdaMarketingRouteForUser($user, $requested = '') {
    $role = (string) ($user['rol'] ?? 'usuario');
    $requested = cdaSafeLocalReturnTo($requested ?: cdaMarketingDefaultRouteForRole($role), cdaMarketingDefaultRouteForRole($role));
    $requestedPath = parse_url($requested, PHP_URL_PATH) ?: $requested;

    if ($requestedPath === 'usuarios-marketing.php' && !cdaMarketingCanManageUsers($role)) {
        return cdaMarketingDefaultRouteForRole($role);
    }

    if ($requestedPath === 'control-marketing.php' && !cdaMarketingCanAccessBoard($role)) {
        return cdaMarketingDefaultRouteForRole($role);
    }

    if ($requestedPath === 'panel-marketing.php' && !cdaMarketingCanViewAllTickets($role)) {
        return cdaMarketingDefaultRouteForRole($role);
    }

    if ($requestedPath === 'estadisticas-marketing.php' && !cdaMarketingCanViewAllTickets($role)) {
        return cdaMarketingDefaultRouteForRole($role);
    }

    return $requested;
}

function cdaMarketingNormalizeUploadName($name) {
    $safe = preg_replace('/[^a-zA-Z0-9._-]/', '-', basename((string) $name));
    return trim($safe, '.-') ?: 'archivo';
}

function cdaMarketingNormalizeFileUpload(array $files) {
    $keys = ['name', 'type', 'tmp_name', 'error', 'size'];
    $normalized = [];

    foreach ($keys as $key) {
        $value = $files[$key] ?? [];
        $normalized[$key] = is_array($value) ? array_values($value) : [$value];
    }

    return $normalized;
}

function cdaMarketingAssigneeValue($value, array $allowedNames) {
    $value = cdaMarketingClean($value);
    if ($value === '') {
        return '';
    }

    return in_array($value, $allowedNames, true) ? $value : '';
}

function cdaMarketingPasswordResetToken() {
    return bin2hex(random_bytes(32));
}

function cdaMarketingPasswordResetHash($token) {
    return hash('sha256', (string) $token);
}

function cdaMarketingPasswordResetUrl($token) {
    $siteUrl = defined('CDA_SITE_URL') ? CDA_SITE_URL : 'https://centraldealarmas.com.mx';
    return rtrim($siteUrl, '/') . '/reset-password.php?token=' . urlencode((string) $token);
}

function cdaMarketingPasswordResetExpiresAt($ttlSeconds = 3600) {
    return date('Y-m-d H:i:s', time() + (int) $ttlSeconds);
}

function cdaMarketingSendPasswordResetEmail($user, $token) {
    $email = filter_var($user['correo'] ?? '', FILTER_VALIDATE_EMAIL);
    if (!$email) {
        return false;
    }

    $name = cdaMarketingClean($user['nombre'] ?? 'Usuario');
    $url = cdaMarketingPasswordResetUrl($token);
    $subject = 'Recuperacion de acceso | Marketing Central de Alarmas';
    $message = '
    <html>
    <head><meta charset="UTF-8"></head>
    <body style="font-family:Arial,sans-serif;color:#10213f;line-height:1.55;">
        <div style="max-width:680px;margin:0 auto;border:1px solid #d8e3f0;border-radius:8px;overflow:hidden;">
            <div style="background:#063970;color:#fff;padding:18px 20px;">
                <h1 style="margin:0;font-size:21px;">Recuperacion de acceso</h1>
            </div>
            <div style="padding:20px;background:#fff;">
                <p>Hola ' . htmlspecialchars($name) . ', recibimos una solicitud para restablecer o crear la contraseña de tu acceso al panel de Marketing.</p>
                <p>Por seguridad no enviamos contraseñas por correo. Usa el siguiente enlace para definir una nueva contraseña:</p>
                <a href="' . htmlspecialchars($url) . '" style="display:inline-block;margin-top:14px;background:#f6eb17;color:#063970;padding:12px 16px;border-radius:8px;text-decoration:none;font-weight:bold;">Crear nueva contraseña</a>
                <p style="margin-top:16px;color:#66758d;">Este enlace vence en 1 hora. Si no solicitaste este cambio, puedes ignorar este mensaje.</p>
            </div>
        </div>
    </body>
    </html>';

    return mail($email, $subject, $message, cdaMarketingMailHeaders());
}

function cdaMarketingFetchAssignableAdmins() {
    try {
        $stmt = cdaDb()->query("SELECT nombre, correo FROM marketing_usuarios WHERE rol IN ('admin','manager','trabajador') AND activo = 1 ORDER BY nombre ASC");
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

    return mail($ticket['correo'], $subject, $message, cdaMarketingMailHeaders());
}

function cdaMarketingSendChatAdminEmail($ticket, $authorName, $messageText, array $files = []) {
    $admins = cdaMarketingTicketInternalRecipientEmails($ticket);

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

    return cdaMarketingSendHtmlMailToMany($admins, $subject, $message, cdaMarketingMailHeaders());
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
        'En diseno' => 'Producción',
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
