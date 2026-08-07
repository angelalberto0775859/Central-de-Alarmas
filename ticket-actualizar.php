<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/marketing_helpers.php';

$user = cdaRequireLogin();
cdaMarketingEnsureTicketSchema();
if (!cdaMarketingCanManageTickets($user['rol'])) {
    cdaMarketingRedirect('panel-marketing.php', 'panel-marketing.php', 'No tienes permisos para actualizar tickets.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    cdaMarketingRedirect('panel-marketing.php');
}

cdaRequirePostCsrf();

$id = (int) ($_POST['id'] ?? 0);
$estado = cdaMarketingClean($_POST['estado'] ?? '');
$respuesta = cdaMarketingClean($_POST['respuesta_interna'] ?? '');
$fechaEntregaRaw = cdaMarketingClean($_POST['fecha_entrega_estimada'] ?? '');
$fechaEntregaEstimada = cdaMarketingOptionalDate($fechaEntregaRaw);
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
$ticketFragment = $id > 0 ? 'ticket-' . $id : '';

if ($id <= 0 || !cdaMarketingStatusAllowed($estado) || ($fechaEntregaRaw !== '' && $fechaEntregaEstimada === null)) {
    cdaMarketingRedirect($returnTo, 'panel-marketing.php', 'No se pudo guardar: revisa que el estado y la fecha sean validos.', 'error', $ticketFragment);
}

try {
    $db = cdaDb();
    $db->beginTransaction();

    $ticketStmt = $db->prepare('SELECT id, folio, solicitante, correo, actividad, estado, fecha_entrega_estimada FROM marketing_tickets WHERE id = ? AND eliminado_en IS NULL LIMIT 1');
    $ticketStmt->execute([$id]);
    $ticket = $ticketStmt->fetch();
    if (!$ticket) {
        $db->rollBack();
        cdaMarketingRedirect($returnTo, 'panel-marketing.php', 'No encontramos ese ticket o ya fue eliminado.', 'error', $ticketFragment);
    }
    $oldStatus = $ticket['estado'];
    $ticket['fecha_entrega_estimada'] = $fechaEntregaEstimada;

    $update = $db->prepare('UPDATE marketing_tickets SET estado = ?, respuesta_interna = ?, asignado_a = ?, fecha_entrega_estimada = ? WHERE id = ? AND eliminado_en IS NULL');
    $update->execute([$estado, $respuesta, $asignado, $fechaEntregaEstimada, $id]);

    $hist = $db->prepare('INSERT INTO marketing_ticket_historial (ticket_id, usuario_id, estado, comentario) VALUES (?, ?, ?, ?)');
    $hist->execute([$id, $user['id'], $estado, $respuesta ?: 'Actualizacion de estado.']);

    $db->commit();

    if ($oldStatus !== $estado) {
        cdaMarketingSendStatusEmail($ticket, $oldStatus, $estado, $respuesta);
    }
} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log('No fue posible actualizar ticket de marketing: ' . $e->getMessage());
    cdaMarketingRedirect($returnTo, 'panel-marketing.php', 'No fue posible guardar los cambios. Si vuelve a pasar, ejecuta db/install_marketing_schema.sql en la base de datos.', 'error', $ticketFragment);
}

cdaMarketingRedirect($returnTo, 'panel-marketing.php', 'Cambios guardados correctamente.', 'success', $ticketFragment);
