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
$returnTo = cdaMarketingClean($_POST['return_to'] ?? 'panel-marketing.php');
$allowedReturnTo = ['panel-marketing.php', 'control-marketing.php'];
if (!in_array($returnTo, $allowedReturnTo, true)) {
    $returnTo = 'panel-marketing.php';
}

if ($ticketId <= 0 || $mensaje === '') {
    header('Location: ' . $returnTo);
    exit;
}

try {
    $db = cdaDb();
    $ticket = $db->prepare('SELECT id, correo FROM marketing_tickets WHERE id = ? LIMIT 1');
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
    $insert->execute([$ticketId, $user['id'], $user['nombre'], $authorRole, $mensaje]);

    $touch = $db->prepare('UPDATE marketing_tickets SET actualizado_en = CURRENT_TIMESTAMP WHERE id = ?');
    $touch->execute([$ticketId]);

    $db->commit();
} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
}

header('Location: ' . $returnTo . '#ticket-' . $ticketId);
exit;
