<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/marketing_helpers.php';

$user = cdaRequireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: panel-marketing.php');
    exit;
}

cdaRequirePostCsrf();

$ticketId = (int) ($_POST['ticket_id'] ?? 0);
$mensaje = cdaMarketingClean($_POST['mensaje'] ?? '');
$hasFiles = cdaMarketingCanUploadChatFiles($user['rol']) && !empty($_FILES['archivos']['name']) && is_array($_FILES['archivos']['name']) && count(array_filter($_FILES['archivos']['name'])) > 0;
$returnTo = cdaMarketingClean($_POST['return_to'] ?? 'panel-marketing.php');
$allowedReturnTo = ['panel-marketing.php', 'control-marketing.php'];
if (!in_array($returnTo, $allowedReturnTo, true)) {
    $returnTo = 'panel-marketing.php';
}

if ($ticketId <= 0 || ($mensaje === '' && !$hasFiles)) {
    header('Location: ' . $returnTo);
    exit;
}

try {
    $db = cdaDb();
    $ticket = $db->prepare('SELECT id, folio, correo, actividad FROM marketing_tickets WHERE id = ? AND eliminado_en IS NULL LIMIT 1');
    $ticket->execute([$ticketId]);
    $ticketRow = $ticket->fetch();

    if (!$ticketRow) {
        header('Location: ' . $returnTo);
        exit;
    }

    $canChat = $user['rol'] === 'admin' || strcasecmp($user['correo'], $ticketRow['correo']) === 0;
    if (!$canChat) {
        header('Location: ' . $returnTo);
        exit;
    }

    $db->beginTransaction();

    $insert = $db->prepare(
        'INSERT INTO marketing_ticket_mensajes (ticket_id, usuario_id, autor_nombre, autor_rol, mensaje)
        VALUES (?, ?, ?, ?, ?)'
    );
    $authorRole = $user['rol'] === 'admin' ? 'admin' : 'usuario';
    $messageText = $mensaje !== '' ? $mensaje : 'Archivo enviado para este ticket.';
    $insert->execute([$ticketId, $user['id'], $user['nombre'], $authorRole, $messageText]);
    $messageId = (int) $db->lastInsertId();
    $savedFiles = [];
    if ($hasFiles) {
        $savedFiles = cdaMarketingStoreMessageFiles($ticketRow, $messageId, $_FILES['archivos']);
    }

    $touch = $db->prepare('UPDATE marketing_tickets SET actualizado_en = CURRENT_TIMESTAMP WHERE id = ?');
    $touch->execute([$ticketId]);

    $db->commit();

    if ($user['rol'] === 'admin') {
        cdaMarketingSendChatEmail($ticketRow, $user['nombre'], $messageText, $savedFiles);
    } else {
        cdaMarketingSendChatAdminEmail($ticketRow, $user['nombre'], $messageText, $savedFiles);
    }
} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    $_SESSION['cda_marketing_error'] = 'No fue posible enviar el mensaje o guardar los archivos. Revisa que exista la tabla de archivos del chat y que la carpeta uploads/marketing tenga permisos de escritura.';
}

header('Location: ' . $returnTo . '#ticket-' . $ticketId);
exit;
