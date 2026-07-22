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
assertSameValue('Logo-CDA.png', cdaMarketingNormalizeUploadName(' Logo CDA.png '), 'upload names are normalized');
assertSameValue('Angel Admin', cdaMarketingAssigneeValue('Angel Admin', ['Angel Admin', 'Maria Admin']), 'known admin can be assigned');
assertSameValue('', cdaMarketingAssigneeValue('Persona externa', ['Angel Admin', 'Maria Admin']), 'unknown assignee is rejected');
assertSameValue('', cdaMarketingAssigneeValue('', ['Angel Admin']), 'empty assignee keeps ticket unassigned');

$singleUpload = cdaMarketingNormalizeFileUpload([
    'name' => 'brief.pdf',
    'type' => 'application/pdf',
    'tmp_name' => '/tmp/php-upload',
    'error' => UPLOAD_ERR_OK,
    'size' => 1234,
]);
assertSameValue(['brief.pdf'], $singleUpload['name'], 'single upload normalizes to array names');
assertSameValue([UPLOAD_ERR_OK], $singleUpload['error'], 'single upload normalizes to array errors');

$multiUpload = cdaMarketingNormalizeFileUpload([
    'name' => ['brief.pdf', 'logo.png'],
    'type' => ['application/pdf', 'image/png'],
    'tmp_name' => ['/tmp/brief', '/tmp/logo'],
    'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
    'size' => [1234, 5678],
]);
assertSameValue(['brief.pdf', 'logo.png'], $multiUpload['name'], 'multi upload keeps array names');

$tmpBase = sys_get_temp_dir() . '/cda-marketing-upload-test-' . bin2hex(random_bytes(4));
$tmpDir = $tmpBase . '/nested';
cdaMarketingEnsureUploadDir($tmpDir);
assertSameValue(true, is_dir($tmpDir), 'upload dir is created');

$blockedPath = $tmpBase . '/blocked';
file_put_contents($blockedPath, 'not a directory');
$thrown = false;
try {
    cdaMarketingEnsureUploadDir($blockedPath);
} catch (RuntimeException $e) {
    $thrown = $e->getMessage() === 'upload_dir_unavailable';
}
assertSameValue(true, $thrown, 'upload dir rejects file path');

echo "marketing ticket smoke ok" . PHP_EOL;
