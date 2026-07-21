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
        'En diseno' => 'En diseño',
        'En revision' => 'En revisión',
        'Ajustes solicitados' => 'Ajustes solicitados',
        'Programado' => 'Programado',
        'Entregado' => 'Entregado',
        'Cerrado' => 'Cerrado',
        'Rechazado' => 'Rechazado',
    ];

    return $labels[$status] ?? $status;
}

function cdaMarketingProgressSteps() {
    return [
        'Recibido' => 'Recibido',
        'En evaluacion' => 'Evaluación',
        'En diseno' => 'Diseño',
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
