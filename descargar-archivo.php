<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/marketing_helpers.php';

$user = cdaRequireLogin();
$type = cdaMarketingClean($_GET['tipo'] ?? '');
$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0 || !in_array($type, ['ticket', 'mensaje'], true)) {
    http_response_code(404);
    exit('Archivo no encontrado.');
}

try {
    if ($type === 'ticket') {
        $stmt = cdaDb()->prepare(
            'SELECT a.*, t.correo, t.eliminado_en
            FROM marketing_ticket_archivos a
            INNER JOIN marketing_tickets t ON t.id = a.ticket_id
            WHERE a.id = ? LIMIT 1'
        );
    } else {
        $stmt = cdaDb()->prepare(
            'SELECT a.*, t.correo, t.eliminado_en
            FROM marketing_ticket_mensaje_archivos a
            INNER JOIN marketing_tickets t ON t.id = a.ticket_id
            WHERE a.id = ? LIMIT 1'
        );
    }
    $stmt->execute([$id]);
    $file = $stmt->fetch();
} catch (Throwable $e) {
    http_response_code(500);
    exit('No fue posible consultar el archivo.');
}

if (!$file || !empty($file['eliminado_en'])) {
    http_response_code(404);
    exit('Archivo no encontrado.');
}

$canDownload = cdaMarketingCanAccessBoard($user['rol']) || strcasecmp($user['correo'], $file['correo']) === 0;
if (!$canDownload) {
    http_response_code(403);
    exit('No tienes permiso para descargar este archivo.');
}

$baseDir = realpath(__DIR__ . '/uploads/marketing');
$absolutePath = realpath(__DIR__ . '/' . $file['ruta']);
if (!$baseDir || !$absolutePath || strpos($absolutePath, $baseDir . DIRECTORY_SEPARATOR) !== 0 || !is_file($absolutePath)) {
    http_response_code(404);
    exit('Archivo no encontrado.');
}

$downloadName = cdaMarketingNormalizeUploadName($file['nombre_original'] ?? 'archivo');
$mime = !empty($file['mime']) ? $file['mime'] : 'application/octet-stream';

while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($absolutePath));
header('Content-Disposition: attachment; filename="' . addcslashes($downloadName, '"\\') . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=0, must-revalidate');
readfile($absolutePath);
exit;
