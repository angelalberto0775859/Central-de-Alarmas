<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/marketing_helpers.php';

$user = cdaRequireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: panel-marketing.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$estado = cdaMarketingClean($_POST['estado'] ?? '');
$respuesta = cdaMarketingClean($_POST['respuesta_interna'] ?? '');
$asignado = cdaMarketingClean($_POST['asignado_a'] ?? '');

if ($id <= 0 || !cdaMarketingStatusAllowed($estado)) {
    header('Location: panel-marketing.php');
    exit;
}

$db = cdaDb();
$db->beginTransaction();

$update = $db->prepare('UPDATE marketing_tickets SET estado = ?, respuesta_interna = ?, asignado_a = ? WHERE id = ?');
$update->execute([$estado, $respuesta, $asignado, $id]);

$hist = $db->prepare('INSERT INTO marketing_ticket_historial (ticket_id, usuario_id, estado, comentario) VALUES (?, ?, ?, ?)');
$hist->execute([$id, $user['id'], $estado, $respuesta ?: 'Actualizacion de estado.']);

$db->commit();

header('Location: panel-marketing.php');
exit;
