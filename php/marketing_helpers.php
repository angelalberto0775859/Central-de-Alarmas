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

function cdaMarketingFolio($date, $sequence) {
    $compactDate = preg_replace('/[^0-9]/', '', (string) $date);
    return 'MKT-' . $compactDate . '-' . str_pad((string) (int) $sequence, 4, '0', STR_PAD_LEFT);
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

function cdaMarketingStatusClass($status) {
    $slug = strtolower(str_replace(' ', '-', cdaMarketingClean($status)));
    return preg_replace('/[^a-z0-9-]/', '', $slug);
}
