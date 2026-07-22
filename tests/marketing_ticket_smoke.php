<?php
require_once __DIR__ . '/../php/marketing_helpers.php';

function assertSameValue($expected, $actual, $label) {
    if ($expected !== $actual) {
        fwrite(STDERR, $label . " failed. Expected " . var_export($expected, true) . ", got " . var_export($actual, true) . PHP_EOL);
        exit(1);
    }
}

assertSameValue('MKT-20260721-0007', cdaMarketingFolio('2026-07-21', 7), 'folio format');
assertSameValue('MKT-20260721-0007-A1B2', cdaMarketingFolio('2026-07-21', 7, 'a1b2c3'), 'folio format with suffix');
assertSameValue(true, cdaMarketingStatusAllowed('En evaluacion'), 'known status allowed');
assertSameValue(false, cdaMarketingStatusAllowed('Inventado'), 'unknown status rejected');
assertSameValue('Texto con espacios', cdaMarketingClean("  Texto con espacios  "), 'clean trims text');
assertSameValue('Urgente', cdaMarketingPriority('Urgente'), 'known priority kept');
assertSameValue('Normal', cdaMarketingPriority('Fuera'), 'unknown priority defaults');
assertSameValue('En evaluación', cdaMarketingStatusLabel('En evaluacion'), 'status label uses accents');
assertSameValue(4, cdaMarketingProgressIndex('Cerrado'), 'closed status maps to final progress');
assertSameValue(1, cdaMarketingProgressIndex('Pendiente de informacion'), 'waiting info maps to evaluation progress');
assertSameValue(true, in_array('Ventas', cdaMarketingDepartments(), true), 'departments expose ventas option');
assertSameValue(true, in_array('Clientes actuales', cdaMarketingAudiences(), true), 'audiences expose clientes actuales option');
assertSameValue('green', cdaMarketingStatusTone('Entregado'), 'delivered tickets use green tone');
assertSameValue('red', cdaMarketingPriorityTone('Urgente'), 'urgent priority uses red tone');
assertSameValue(true, cdaMarketingStatusSendsEmail('Pendiente de informacion'), 'waiting info status sends email');
assertSameValue(true, cdaMarketingStatusSendsEmail('Ajustes solicitados'), 'adjustments status sends email');
assertSameValue(true, cdaMarketingStatusSendsEmail('Entregado'), 'delivered status sends email');
assertSameValue(false, cdaMarketingStatusSendsEmail('En diseno'), 'in design status stays internal');
assertSameValue(true, strpos(cdaMarketingStatusEmailSubject('Entregado', 'MKT-1'), 'MKT-1') !== false, 'status email subject includes folio');
assertSameValue(true, in_array('pdf', cdaMarketingChatFileExtensions(), true), 'chat files allow pdf');
assertSameValue(false, in_array('exe', cdaMarketingChatFileExtensions(), true), 'chat files reject executables');
assertSameValue(true, cdaMarketingCanUploadChatFiles('admin'), 'admins can upload chat files');
assertSameValue(true, cdaMarketingCanUploadChatFiles('marketing'), 'marketing users can upload chat files');
assertSameValue(true, cdaMarketingCanUploadChatFiles('usuario'), 'requesters can upload chat files');

echo "marketing ticket smoke ok" . PHP_EOL;
