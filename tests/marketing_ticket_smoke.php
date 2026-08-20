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
assertSameValue('admin', cdaMarketingFixedRoleByEmail('angelalberto077@gmail.com'), 'angel email is fixed admin');
assertSameValue(null, cdaMarketingFixedRoleByEmail('salducin@centraldealarmas.com.mx'), 'salducin email is not fixed admin');
assertSameValue(null, cdaMarketingFixedRoleByEmail('rvillaverde@centraldealarmas.com.mx'), 'rvillaverde email is editable');
assertSameValue('trabajador', cdaMarketingInitialRoleByEmail('rvillaverde@centraldealarmas.com.mx'), 'rvillaverde starts as worker');
assertSameValue('usuario', cdaMarketingUserRoleValue('persona@centraldealarmas.com.mx', 'admin'), 'non fixed email cannot become admin');
assertSameValue('usuario', cdaMarketingUserRoleValue('salducin@centraldealarmas.com.mx', 'admin'), 'salducin cannot become admin');
assertSameValue('manager', cdaMarketingUserRoleValue('rvillaverde@centraldealarmas.com.mx', 'manager'), 'rvillaverde can be edited by admin');
assertSameValue('trabajador', cdaMarketingUserRoleValue('persona@centraldealarmas.com.mx', 'trabajador'), 'non fixed email can be assigned worker');
assertSameValue('usuario', cdaMarketingDefaultUserRole('nuevo@centraldealarmas.com.mx'), 'new users default to regular user');
assertSameValue('trabajador', cdaMarketingDefaultUserRole('rvillaverde@centraldealarmas.com.mx'), 'rvillaverde defaults to worker');
assertSameValue(false, cdaMarketingProtectedUserEmail('salducin@centraldealarmas.com.mx'), 'salducin email is not protected as admin');
assertSameValue(false, cdaMarketingProtectedUserEmail('nuevo@centraldealarmas.com.mx'), 'regular email is not protected');
assertSameValue(true, function_exists('cdaMarketingEnsureUserRoleSchema'), 'role schema repair helper exists');
assertSameValue(true, function_exists('cdaMarketingEnforceFixedUserRoles'), 'fixed role enforcement helper exists');
assertSameValue(true, function_exists('cdaMarketingTicketInternalRecipientEmails'), 'ticket internal recipient helper exists');
assertSameValue(true, function_exists('cdaMarketingMailHeaders'), 'shared mail header helper exists');
assertSameValue(true, function_exists('cdaMarketingSyncUserTicketEmail'), 'user email ticket sync helper exists');
assertSameValue(true, function_exists('cdaMarketingTicketOptionalColumns'), 'ticket optional column helper exists');
assertSameValue(true, function_exists('cdaMarketingSaveTicketUpdate'), 'ticket update save helper exists');
assertSameValue(true, function_exists('cdaMarketingInsertTicketHistorySafe'), 'ticket history safe insert helper exists');
assertSameValue(true, function_exists('cdaMarketingCanAssignTickets'), 'ticket assignment permission helper exists');
assertSameValue(true, function_exists('cdaMarketingFetchAssignableUsers'), 'managers and workers can be fetched for assignment');
assertSameValue(true, function_exists('cdaMarketingAssignableAssigneeValue'), 'ticket assignment validates assignee directly');
assertSameValue(true, function_exists('cdaMarketingTicketAssignmentLabel'), 'ticket assignment label helper exists');
assertSameValue('America/Mexico_City', date_default_timezone_get(), 'marketing uses Mexico City timezone');
assertSameValue(true, cdaMarketingCanManageTickets('manager'), 'managers can manage tickets');
assertSameValue(false, cdaMarketingCanManageTickets('marketing'), 'old marketing role cannot manage tickets');
assertSameValue(false, cdaMarketingCanManageTickets('trabajador'), 'workers cannot manage tickets');
assertSameValue(true, cdaMarketingCanAssignTickets('admin'), 'admins can assign tickets');
assertSameValue(true, cdaMarketingCanAssignTickets('manager'), 'managers can assign tickets');
assertSameValue(false, cdaMarketingCanAssignTickets('trabajador'), 'workers cannot assign tickets');
assertSameValue(true, cdaMarketingCanAccessBoard('trabajador'), 'workers can access board');
assertSameValue(false, cdaMarketingCanAccessBoard('usuario'), 'regular users do not access board');
assertSameValue('usuario', cdaMarketingRoleClass('marketing'), 'old marketing role normalizes to usuario');
assertSameValue('perfil-marketing.php', cdaMarketingDefaultRouteForRole('usuario'), 'regular users land on profile');
assertSameValue('control-marketing.php', cdaMarketingDefaultRouteForRole('trabajador'), 'workers land on board');
assertSameValue('estadisticas-marketing.php', cdaMarketingDefaultRouteForRole('manager'), 'managers land on statistics');
assertSameValue('perfil-marketing.php', cdaMarketingRouteForUser(['rol' => 'usuario'], 'panel-marketing.php'), 'regular users are rerouted away from ticket panel');
assertSameValue('perfil-marketing.php', cdaMarketingRouteForUser(['rol' => 'usuario'], 'estadisticas-marketing.php'), 'regular users are rerouted away from statistics');
assertSameValue('estadisticas-marketing.php', cdaMarketingRouteForUser(['rol' => 'manager'], 'estadisticas-marketing.php'), 'managers can access statistics');
assertSameValue('seguimiento.php?folio=MKT-1#chat', cdaMarketingRouteForUser(['rol' => 'usuario'], 'seguimiento.php?folio=MKT-1#chat'), 'regular users can return to seguimiento with query');
assertSameValue('Logo-CDA.png', cdaMarketingNormalizeUploadName(' Logo CDA.png '), 'upload names are normalized');
assertSameValue('2026-08-20', cdaMarketingOptionalDate('2026-08-20'), 'optional date accepts yyyy-mm-dd');
assertSameValue(null, cdaMarketingOptionalDate(''), 'optional date accepts blank');
assertSameValue(null, cdaMarketingOptionalDate('20/08/2026'), 'optional date rejects invalid format');
assertSameValue('Angel Admin', cdaMarketingAssigneeValue('Angel Admin', ['Angel Admin', 'Maria Admin']), 'known admin can be assigned');
assertSameValue('', cdaMarketingAssigneeValue('Persona externa', ['Angel Admin', 'Maria Admin']), 'unknown assignee is rejected');
assertSameValue('', cdaMarketingAssigneeValue('', ['Angel Admin']), 'empty assignee keeps ticket unassigned');
assertSameValue('Te toca a ti', cdaMarketingTicketAssignmentLabel(['asignado_a' => 'angel@centraldealarmas.com.mx'], ['nombre' => 'Angel Admin', 'correo' => 'angel@centraldealarmas.com.mx']), 'ticket assignment marks current user by email');
assertSameValue('Te toca a ti', cdaMarketingTicketAssignmentLabel(['asignado_a' => 'Angel Admin'], ['nombre' => 'Angel Admin']), 'ticket assignment marks current user by legacy name');
assertSameValue('Asignado a Maria', cdaMarketingTicketAssignmentLabel(['asignado_a' => 'Maria'], ['nombre' => 'Angel Admin']), 'ticket assignment shows owner');
assertSameValue('Sin asignar', cdaMarketingTicketAssignmentLabel(['asignado_a' => ''], ['nombre' => 'Angel Admin']), 'ticket assignment shows unassigned');
assertSameValue(64, strlen(cdaMarketingPasswordResetToken()), 'password reset token uses 32 random bytes');
assertSameValue(64, strlen(cdaMarketingPasswordResetHash('abc123')), 'password reset token hash is sha256 hex');
assertSameValue(true, strpos(cdaMarketingPasswordResetUrl('abc123'), 'reset-password.php?token=abc123') !== false, 'password reset url includes token');
assertSameValue(true, function_exists('cdaMarketingSendPasswordChangedEmail'), 'password changed email helper exists');

$marketingFormHtml = file_get_contents(__DIR__ . '/../crear-ticket.php');
assertSameValue(true, strpos($marketingFormHtml, 'cdaMarketingAllowedUploadAccept()') !== false, 'authenticated marketing form uses shared accept list');
assertSameValue(true, strpos($marketingFormHtml, 'Subida de editables y material requerido') !== false, 'authenticated marketing form explains editable uploads');
assertSameValue(false, strpos($marketingFormHtml, 'accountPassword') !== false, 'authenticated marketing form does not ask for a ticket password');

$panelHtml = file_get_contents(__DIR__ . '/../panel-marketing.php');
$controlHtml = file_get_contents(__DIR__ . '/../control-marketing.php');
$usersHtml = file_get_contents(__DIR__ . '/../usuarios-marketing.php');
$profileHtml = file_get_contents(__DIR__ . '/../perfil-marketing.php');
$schemaSql = file_get_contents(__DIR__ . '/../db/install_marketing_schema.sql');
assertSameValue(true, strpos($panelHtml, 'fecha_entrega_estimada') !== false, 'panel can edit estimated delivery date');
assertSameValue(true, strpos($controlHtml, 'fecha_entrega_estimada') !== false, 'board can edit estimated delivery date');
assertSameValue(true, strpos($panelHtml, 'cdaMarketingFetchAssignableUsers') !== false, 'panel assigns tickets to managers and workers');
assertSameValue(true, strpos($controlHtml, 'cdaMarketingFetchAssignableUsers') !== false, 'board assigns tickets to managers and workers');
assertSameValue(true, strpos($panelHtml, 'cdaMarketingTicketAssignmentLabel') !== false, 'panel distinguishes assigned ticket owner');
assertSameValue(true, strpos($controlHtml, 'cdaMarketingTicketAssignmentLabel') !== false, 'board distinguishes assigned ticket owner');
assertSameValue(true, strpos($profileHtml, 'function cdaProfileInitials') !== false, 'profile has local initials helper');
assertSameValue(false, strpos($profileHtml, 'mb_substr') !== false, 'profile does not require mbstring initials');
assertSameValue(true, strpos($profileHtml, 'function cdaProfileTicketColumns') !== false, 'profile guards optional ticket columns');
assertSameValue(true, strpos($profileHtml, 'SHOW COLUMNS FROM marketing_tickets LIKE') !== false, 'profile checks estimated delivery column before selecting it');
assertSameValue(true, strpos($profileHtml, 'asignado_a') !== false, 'profile can include tickets assigned to the current user');
assertSameValue(true, strpos($profileHtml, 'correo = ? OR asignado_a = ? OR asignado_a = ?') !== false, 'profile ticket filters include requester email, assigned email or legacy assigned name');
assertSameValue(true, strpos($profileHtml, 'Filtro inicial de solicitudes') !== false, 'profile settings include default ticket filter');
assertSameValue(true, strpos($profileHtml, 'cdaMarketingProfileSettings') !== false, 'profile settings persist locally');
assertSameValue(true, strpos($usersHtml, 'Guardar todo') !== false, 'users page has bulk save button');
assertSameValue(true, strpos($usersHtml, 'cdaMarketingEnsureUserRoleSchema()') !== false, 'users page repairs role schema before saving');
assertSameValue(true, strpos($usersHtml, 'cdaMarketingEnforceFixedUserRoles()') !== false, 'users page enforces fixed roles before listing users');
assertSameValue(true, strpos($usersHtml, 'Guarda los cambios de rol') !== false, 'users page explains the bottom save button');
assertSameValue(true, strpos($usersHtml, "value=\"bulk_save\"") !== false, 'users page posts bulk save action');
assertSameValue(true, strpos($usersHtml, 'name="users[') !== false, 'users page submits editable user rows');
assertSameValue(false, strpos($usersHtml, '<option value="admin">Administrador</option>') !== false, 'new user form does not offer admin role');
assertSameValue(true, strpos($usersHtml, '$rowRole = cdaMarketingUserRoleValue') !== false, 'users page normalizes row role before rendering select');
assertSameValue(true, strpos($usersHtml, '$fixedItemRole === \'admin\'') !== false, 'users page only renders admin option for fixed admin email');
assertSameValue(true, strpos($usersHtml, 'formaction="usuarios-marketing.php?action=delete"') !== false, 'users page keeps delete action per row');
assertSameValue(true, strpos($usersHtml, 'Rol protegido por correo') !== false, 'users page explains protected roles');
assertSameValue(true, strpos($schemaSql, 'fecha_entrega_estimada DATE NULL') !== false, 'schema includes estimated delivery date');
assertSameValue(true, strpos($schemaSql, 'CREATE TABLE IF NOT EXISTS marketing_ticket_archivos') !== false, 'install schema creates ticket attachment table idempotently');
$helpersSource = file_get_contents(__DIR__ . '/../php/marketing_helpers.php');
assertSameValue(true, strpos($helpersSource, 'cdaMarketingEnsureTable') !== false && strpos($helpersSource, 'marketing_ticket_archivos') !== false, 'ticket schema repair creates attachment table');
assertSameValue(true, strpos($helpersSource, 'function cdaMarketingColumnExists($table, $column, $refresh = false)') !== false, 'column existence helper can refresh cache after repairs');
assertSameValue(true, strpos($helpersSource, 'cdaMarketingColumnExists($table, $column, true)') !== false, 'column repair refreshes existence cache');
assertSameValue(true, strpos($helpersSource, "cdaMarketingEnsureColumn('marketing_ticket_historial', 'usuario_id'") !== false, 'ticket schema repair adds missing history user column');
assertSameValue(true, strpos($helpersSource, "cdaMarketingTicketInternalRecipientEmails") !== false, 'ticket emails include internal involved recipients');
assertSameValue(true, strpos($helpersSource, "rol) IN ('manager','trabajador')") !== false, 'assignable users are only managers and workers');
assertSameValue(true, strpos($helpersSource, 'LOWER(rol)') !== false, 'assignable user query tolerates role casing');
assertSameValue(true, strpos($helpersSource, "UPDATE marketing_usuarios SET rol = 'manager' WHERE LOWER(rol) = 'marketing'") !== false, 'role repair always converts legacy marketing users to managers');
assertSameValue(true, strpos($helpersSource, 'No fue posible consultar asignables') !== false, 'assignable user query logs failures');
assertSameValue(true, strpos($helpersSource, 'function cdaMarketingAssignableAssigneeValue') !== false, 'assignment validation is independent from rendered options');
assertSameValue(true, strpos($helpersSource, 'function cdaMarketingTicketAssigneeLabel') !== false, 'assignment labels resolve assigned email to display name');
assertSameValue(true, strpos($helpersSource, 'function cdaMarketingTicketUpdateColumnsReady') !== false, 'ticket update checks required assignment and delivery columns');
assertSameValue(true, strpos($helpersSource, "['asignado_a', 'fecha_entrega_estimada']") !== false, 'ticket updates require assignee and estimated delivery columns');
assertSameValue(true, strpos($helpersSource, 'function cdaMarketingSaveTicketUpdate') !== false, 'ticket update helper centralizes resilient saves');
assertSameValue(true, strpos($helpersSource, 'function cdaMarketingInsertTicketHistorySafe') !== false, 'ticket history insert does not block ticket saves');
$ticketUpdateSource = file_get_contents(__DIR__ . '/../ticket-actualizar.php');
$authSource = file_get_contents(__DIR__ . '/../php/auth.php');
assertSameValue(true, strpos($authSource, 'cdaMarketingEnsureUserRoleSchema') !== false, 'auth repairs legacy user roles before permission checks');
assertSameValue(true, strpos($ticketUpdateSource, 'cdaMarketingSaveTicketUpdate') !== false, 'ticket update endpoint uses resilient save helper');
assertSameValue(true, strpos($ticketUpdateSource, 'cdaMarketingAssignableAssigneeValue') !== false, 'ticket update endpoint validates assignee from database');
assertSameValue(true, strpos($ticketUpdateSource, 'No se pudo asignar') !== false, 'ticket update endpoint rejects invalid assignment instead of clearing it');
assertSameValue(true, strpos($ticketUpdateSource, 'cdaMarketingTicketUpdateColumnsReady') !== false, 'ticket update refuses success when assignment schema is missing');
assertSameValue(true, strpos($ticketUpdateSource, 'db/repair_marketing_schema.sql') !== false, 'ticket update tells admins which SQL repair to run');
assertSameValue(true, strpos($panelHtml, 'value="<?php echo htmlspecialchars($assigneeValue); ?>"') !== false, 'panel posts assignee email instead of display name');
assertSameValue(true, strpos($controlHtml, 'value="<?php echo htmlspecialchars($assigneeValue); ?>"') !== false, 'board posts assignee email instead of display name');
assertSameValue(true, strpos($usersHtml, 'cdaMarketingSyncUserTicketEmail') !== false, 'users page updates ticket requester emails when user email changes');
assertSameValue(true, strpos($usersHtml, 'cdaMarketingSendPasswordChangedEmail') !== false, 'users page emails users when password changes');
$fixedRolesSql = file_get_contents(__DIR__ . '/../db/marketing_fixed_user_roles.sql');
assertSameValue(true, strpos($fixedRolesSql, "ENUM('admin','usuario','manager','trabajador')") !== false, 'fixed roles migration updates user role enum');
$repairSql = file_get_contents(__DIR__ . '/../db/repair_marketing_schema.sql');
assertSameValue(true, strpos($repairSql, "ENUM('admin','usuario','marketing','manager','trabajador')") !== false, 'repair SQL temporarily allows legacy marketing role');
assertSameValue(true, strpos($repairSql, "UPDATE marketing_usuarios SET rol = 'manager' WHERE LOWER(rol) = 'marketing'") !== false, 'repair SQL converts legacy marketing users before final enum cleanup');

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
