<?php
require_once __DIR__ . '/../php/auth.php';
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
assertSameValue(true, in_array('zip', cdaMarketingTicketFileExtensions(), true), 'initial files allow zip');
assertSameValue(true, in_array('rar', cdaMarketingTicketFileExtensions(), true), 'initial files allow rar');
assertSameValue(true, in_array('svg', cdaMarketingTicketFileExtensions(), true), 'initial files allow svg');
assertSameValue(true, in_array('ai', cdaMarketingTicketFileExtensions(), true), 'initial files allow ai');
assertSameValue(true, in_array('psd', cdaMarketingTicketFileExtensions(), true), 'initial files allow psd');
assertSameValue(true, in_array('indd', cdaMarketingTicketFileExtensions(), true), 'initial files allow indesign');
assertSameValue(true, in_array('fig', cdaMarketingTicketFileExtensions(), true), 'initial files allow figma exports');
assertSameValue(true, in_array('docm', cdaMarketingTicketFileExtensions(), true), 'initial files allow macro word');
assertSameValue(true, in_array('pptm', cdaMarketingTicketFileExtensions(), true), 'initial files allow macro powerpoint');
assertSameValue(true, in_array('xlsm', cdaMarketingTicketFileExtensions(), true), 'initial files allow macro excel');
assertSameValue(true, in_array('csv', cdaMarketingTicketFileExtensions(), true), 'initial files allow csv');
assertSameValue(true, in_array('mp3', cdaMarketingTicketFileExtensions(), true), 'initial files allow audio');
assertSameValue(true, in_array('heic', cdaMarketingTicketFileExtensions(), true), 'initial files allow phone photos');
assertSameValue(true, in_array('7z', cdaMarketingTicketFileExtensions(), true), 'initial files allow 7z archives');
assertSameValue(true, in_array('glb', cdaMarketingTicketFileExtensions(), true), 'initial files allow 3d assets');
assertSameValue(true, in_array('pdf', cdaMarketingChatFileExtensions(), true), 'chat files allow pdf');
assertSameValue(true, in_array('zip', cdaMarketingChatFileExtensions(), true), 'chat files allow zip');
assertSameValue(true, in_array('rar', cdaMarketingChatFileExtensions(), true), 'chat files allow rar');
assertSameValue(true, in_array('svg', cdaMarketingChatFileExtensions(), true), 'chat files allow svg');
assertSameValue(true, in_array('ai', cdaMarketingChatFileExtensions(), true), 'chat files allow ai');
assertSameValue(true, in_array('psd', cdaMarketingChatFileExtensions(), true), 'chat files allow psd');
assertSameValue(false, in_array('exe', cdaMarketingChatFileExtensions(), true), 'chat files reject executables');
assertSameValue(true, strpos(cdaMarketingAllowedUploadAccept(), '.rar') !== false, 'file input accept includes rar');
assertSameValue(true, strpos(cdaMarketingAllowedUploadAccept(), '.indd') !== false, 'file input accept includes indd');
assertSameValue(true, cdaMarketingCanUploadChatFiles('admin'), 'admins can upload chat files');
assertSameValue(true, cdaMarketingCanUploadChatFiles('usuario'), 'requesters can upload chat files');
assertSameValue(true, cdaMarketingCanUploadChatFiles('manager'), 'managers can upload chat files');
assertSameValue(true, cdaMarketingCanUploadChatFiles('trabajador'), 'workers can upload chat files');
assertSameValue(false, cdaMarketingCanUploadChatFiles('marketing'), 'old marketing role cannot upload chat files');
assertSameValue(true, cdaMarketingCanManageUsers('admin'), 'admins can manage users');
assertSameValue(false, cdaMarketingCanManageUsers('manager'), 'managers cannot manage users');
assertSameValue(true, cdaMarketingCanManageTickets('manager'), 'managers can manage tickets');
assertSameValue(false, cdaMarketingCanManageTickets('marketing'), 'old marketing role cannot manage tickets');
assertSameValue(false, cdaMarketingCanManageTickets('trabajador'), 'workers cannot manage tickets');
assertSameValue(true, cdaMarketingCanAccessBoard('trabajador'), 'workers can access board');
assertSameValue(false, cdaMarketingCanAccessBoard('usuario'), 'regular users do not access board');
assertSameValue('usuario', cdaMarketingRoleClass('marketing'), 'old marketing role normalizes to usuario');
assertSameValue('perfil-marketing.php', cdaMarketingDefaultRouteForRole('usuario'), 'regular users land on profile');
assertSameValue('control-marketing.php', cdaMarketingDefaultRouteForRole('trabajador'), 'workers land on board');
assertSameValue('panel-marketing.php', cdaMarketingDefaultRouteForRole('manager'), 'managers land on ticket panel');
assertSameValue('perfil-marketing.php', cdaMarketingRouteForUser(['rol' => 'usuario'], 'panel-marketing.php'), 'regular users are rerouted away from ticket panel');
assertSameValue('seguimiento.php?folio=MKT-1#chat', cdaMarketingRouteForUser(['rol' => 'usuario'], 'seguimiento.php?folio=MKT-1#chat'), 'regular users can return to seguimiento with query');
assertSameValue('Logo-CDA.png', cdaMarketingNormalizeUploadName(' Logo CDA.png '), 'upload names are normalized');
assertSameValue('Angel Admin', cdaMarketingAssigneeValue('Angel Admin', ['Angel Admin', 'Maria Admin']), 'known admin can be assigned');
assertSameValue('', cdaMarketingAssigneeValue('Persona externa', ['Angel Admin', 'Maria Admin']), 'unknown assignee is rejected');
assertSameValue('', cdaMarketingAssigneeValue('', ['Angel Admin']), 'empty assignee keeps ticket unassigned');
assertSameValue(64, strlen(cdaMarketingPasswordResetToken()), 'password reset token uses 32 random bytes');
assertSameValue(64, strlen(cdaMarketingPasswordResetHash('abc123')), 'password reset token hash is sha256 hex');
assertSameValue(true, strpos(cdaMarketingPasswordResetUrl('abc123'), 'reset-password.php?token=abc123') !== false, 'password reset url includes token');

$marketingFormHtml = file_get_contents(__DIR__ . '/../crear-ticket.php');
assertSameValue(true, strpos($marketingFormHtml, 'cdaMarketingAllowedUploadAccept()') !== false, 'authenticated marketing form uses shared accept list');
assertSameValue(true, strpos($marketingFormHtml, 'Subida de editables y material requerido') !== false, 'authenticated marketing form explains editable uploads');
assertSameValue(false, strpos($marketingFormHtml, 'accountPassword') !== false, 'authenticated marketing form does not ask for a ticket password');

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
