<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/marketing_helpers.php';

$user = cdaRequireLogin();
cdaMarketingEnsureTicketSchema();
if ($user['rol'] !== 'admin') {
    cdaMarketingRedirect('panel-marketing.php', 'panel-marketing.php', 'No tienes permisos para modificar el basurero.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    cdaMarketingRedirect('panel-marketing.php');
}

cdaRequirePostCsrf();

$id = (int) ($_POST['id'] ?? 0);
$action = cdaMarketingClean($_POST['action'] ?? 'trash');
$returnTo = cdaMarketingClean($_POST['return_to'] ?? 'panel-marketing.php');
$allowedReturnTo = ['panel-marketing.php', 'panel-marketing.php?papelera=1', 'control-marketing.php'];
if (!in_array($returnTo, $allowedReturnTo, true)) {
    $returnTo = 'panel-marketing.php';
}

if ($id <= 0 || !in_array($action, ['trash', 'restore', 'purge'], true)) {
    cdaMarketingRedirect($returnTo, 'panel-marketing.php', 'No se pudo procesar la accion solicitada.');
}

try {
    $db = cdaDb();
    $db->beginTransaction();

    if ($action === 'trash') {
        $stmt = $db->prepare('UPDATE marketing_tickets SET eliminado_en = CURRENT_TIMESTAMP, eliminado_por = ? WHERE id = ? AND eliminado_en IS NULL');
        $stmt->execute([$user['id'], $id]);
        $comment = 'Ticket enviado al basurero.';
    } elseif ($action === 'restore') {
        $stmt = $db->prepare('UPDATE marketing_tickets SET eliminado_en = NULL, eliminado_por = NULL WHERE id = ?');
        $stmt->execute([$id]);
        $comment = 'Ticket restaurado desde el basurero.';
    } else {
        $db->prepare('DELETE FROM marketing_ticket_mensaje_archivos WHERE ticket_id = ?')->execute([$id]);
        $db->prepare('DELETE FROM marketing_ticket_mensajes WHERE ticket_id = ?')->execute([$id]);
        $db->prepare('DELETE FROM marketing_ticket_archivos WHERE ticket_id = ?')->execute([$id]);
        $db->prepare('DELETE FROM marketing_ticket_historial WHERE ticket_id = ?')->execute([$id]);
        $stmt = $db->prepare('DELETE FROM marketing_tickets WHERE id = ? AND eliminado_en IS NOT NULL');
        $stmt->execute([$id]);
        $comment = '';
    }

    if ($action !== 'purge') {
        $hist = $db->prepare('INSERT INTO marketing_ticket_historial (ticket_id, usuario_id, estado, comentario) VALUES (?, ?, ?, ?)');
        $hist->execute([$id, $user['id'], 'Cerrado', $comment]);
    }

    $db->commit();
} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log('No fue posible modificar basurero de tickets: ' . $e->getMessage());
    cdaMarketingRedirect($returnTo, 'panel-marketing.php', 'No fue posible modificar este ticket. Revisa el esquema de base de datos si vuelve a pasar.');
}

$successMessage = match ($action) {
    'restore' => 'Ticket restaurado correctamente.',
    'purge' => 'Ticket borrado definitivamente.',
    default => 'Ticket enviado al basurero.',
};
cdaMarketingRedirect($returnTo, 'panel-marketing.php', $successMessage, 'success');
