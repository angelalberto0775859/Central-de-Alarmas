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
$assignableAdmins = cdaMarketingFetchAssignableUsers();
$assignableAdminNames = array_map(function ($admin) {
    return (string) ($admin['nombre'] ?? '');
}, $assignableAdmins);
$asignadoRaw = cdaMarketingClean($_POST['asignado_a'] ?? '');
$asignado = cdaMarketingAssigneeValue($asignadoRaw, $assignableAdminNames);
$returnTo = cdaMarketingClean($_POST['return_to'] ?? 'panel-marketing.php');
$allowedReturnTo = ['panel-marketing.php', 'control-marketing.php'];
if (!in_array($returnTo, $allowedReturnTo, true)) {
    $returnTo = 'panel-marketing.php';
}
$ticketFragment = $id > 0 ? 'ticket-' . $id : '';

if ($id <= 0 || !cdaMarketingStatusAllowed($estado) || ($fechaEntregaRaw !== '' && $fechaEntregaEstimada === null)) {
    cdaMarketingRedirect($returnTo, 'panel-marketing.php', 'No se pudo guardar: revisa que el estado y la fecha sean validos.', 'error', $ticketFragment);
}

if ($asignadoRaw !== '' && $asignado === '') {
    cdaMarketingRedirect($returnTo, 'panel-marketing.php', 'No se pudo asignar: el usuario debe estar activo y tener rol admin, manager o trabajador.', 'error', $ticketFragment);
}

try {
    $db = cdaDb();
    $ticket = cdaMarketingFetchTicketForUpdate($id);
    if (!$ticket) {
        cdaMarketingRedirect($returnTo, 'panel-marketing.php', 'No encontramos ese ticket o ya fue eliminado.', 'error', $ticketFragment);
    }
    $oldStatus = $ticket['estado'];
    $ticket['fecha_entrega_estimada'] = $fechaEntregaEstimada;
    $ticket['asignado_a'] = $asignado;

    cdaMarketingSaveTicketUpdate($id, $estado, $respuesta, $asignado, $fechaEntregaEstimada);
    cdaMarketingInsertTicketHistorySafe($id, $user['id'], $estado, $respuesta ?: 'Actualizacion de estado.');

    if ($oldStatus !== $estado) {
        cdaMarketingSendStatusEmail($ticket, $oldStatus, $estado, $respuesta);
    }
} catch (Throwable $e) {
    error_log('No fue posible actualizar ticket de marketing: ' . $e->getMessage());
    cdaMarketingRedirect($returnTo, 'panel-marketing.php', 'No fue posible guardar los cambios. Si vuelve a pasar, ejecuta db/install_marketing_schema.sql en la base de datos.', 'error', $ticketFragment);
}

cdaMarketingRedirect($returnTo, 'panel-marketing.php', 'Cambios guardados correctamente.', 'success', $ticketFragment);
