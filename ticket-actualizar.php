<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/marketing_helpers.php';

$user = cdaRequireLogin();
if (!cdaMarketingCanManageTickets($user['rol'])) {
    header('Location: panel-marketing.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: panel-marketing.php');
    exit;
}

cdaRequirePostCsrf();

$id = (int) ($_POST['id'] ?? 0);
$estado = cdaMarketingClean($_POST['estado'] ?? '');
$respuesta = cdaMarketingClean($_POST['respuesta_interna'] ?? '');
$assignableAdmins = cdaMarketingFetchAssignableAdmins();
$assignableAdminNames = array_map(function ($admin) {
    return (string) ($admin['nombre'] ?? '');
}, $assignableAdmins);
$asignado = cdaMarketingAssigneeValue($_POST['asignado_a'] ?? '', $assignableAdminNames);
$returnTo = cdaMarketingClean($_POST['return_to'] ?? 'panel-marketing.php');
$allowedReturnTo = ['panel-marketing.php', 'control-marketing.php'];
if (!in_array($returnTo, $allowedReturnTo, true)) {
    $returnTo = 'panel-marketing.php';
}

if ($id <= 0 || !cdaMarketingStatusAllowed($estado)) {
    header('Location: panel-marketing.php');
    exit;
}

$db = cdaDb();
$db->beginTransaction();

$ticketStmt = $db->prepare('SELECT id, folio, solicitante, correo, actividad, estado FROM marketing_tickets WHERE id = ? AND eliminado_en IS NULL LIMIT 1');
$ticketStmt->execute([$id]);
$ticket = $ticketStmt->fetch();
if (!$ticket) {
    $db->rollBack();
    header('Location: ' . $returnTo);
    exit;
}
$oldStatus = $ticket['estado'];

$update = $db->prepare('UPDATE marketing_tickets SET estado = ?, respuesta_interna = ?, asignado_a = ? WHERE id = ? AND eliminado_en IS NULL');
$update->execute([$estado, $respuesta, $asignado, $id]);

$hist = $db->prepare('INSERT INTO marketing_ticket_historial (ticket_id, usuario_id, estado, comentario) VALUES (?, ?, ?, ?)');
$hist->execute([$id, $user['id'], $estado, $respuesta ?: 'Actualizacion de estado.']);

$db->commit();

if ($oldStatus !== $estado) {
    cdaMarketingSendStatusEmail($ticket, $oldStatus, $estado, $respuesta);
}

header('Location: ' . $returnTo);
exit;
