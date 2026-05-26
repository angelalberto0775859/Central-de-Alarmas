<?php
$destinatario = 'reclutamiento@centraldealarmas.com.mx';

function limpiarCampo($valor) {
    return htmlspecialchars(trim(stripslashes($valor ?? '')), ENT_QUOTES, 'UTF-8');
}

function responder($exito, $mensaje) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['exito' => $exito, 'mensaje' => $mensaje]);
    exit;
}

function nivelCumpleLicenciatura($nivel) {
    $nivelesValidos = ['Licenciatura trunca', 'Licenciatura concluida', 'Maestría', 'Doctorado'];
    return in_array($nivel, $nivelesValidos, true);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    responder(false, 'Acceso denegado.');
}

if (!empty($_POST['empresa'])) {
    responder(false, 'No fue posible enviar la solicitud.');
}

$recaptchaRespuesta = $_POST['g-recaptcha-response'] ?? '';
if ($recaptchaRespuesta === '') {
    responder(false, 'Por favor, completa la verificacion de Google.');
}

$recaptchaSecret = '6LfjgrQpAAAAABoTmAW2j8d-zxYCKUWbPpb8Fb9G';
$recaptchaVerificacion = @file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($recaptchaSecret) . '&response=' . urlencode($recaptchaRespuesta));
$recaptchaDatos = $recaptchaVerificacion ? json_decode($recaptchaVerificacion, true) : null;

if (empty($recaptchaDatos['success'])) {
    responder(false, 'No se pudo validar la verificacion de Google. Intenta nuevamente.');
}

$datos = [
    'vacante' => limpiarCampo($_POST['vacante'] ?? ''),
    'nombre' => limpiarCampo($_POST['nombre'] ?? ''),
    'correo' => limpiarCampo($_POST['correo'] ?? ''),
    'telefono' => limpiarCampo($_POST['telefono'] ?? ''),
    'ciudad' => limpiarCampo($_POST['ciudad'] ?? ''),
    'sexo' => limpiarCampo($_POST['sexo'] ?? ''),
    'cartilla_militar_liberada' => limpiarCampo($_POST['cartilla_militar_liberada'] ?? ''),
    'nivel_estudios' => limpiarCampo($_POST['nivel_estudios'] ?? ''),
    'experiencia' => limpiarCampo($_POST['experiencia'] ?? ''),
    'disponibilidad' => limpiarCampo($_POST['disponibilidad'] ?? ''),
    'mensaje' => limpiarCampo($_POST['mensaje'] ?? '')
];

$errores = [];
if ($datos['vacante'] === '') $errores[] = 'vacante';
if ($datos['nombre'] === '') $errores[] = 'nombre';
if ($datos['correo'] === '' || !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) $errores[] = 'correo';
if ($datos['telefono'] === '') $errores[] = 'telefono';
if ($datos['ciudad'] === '') $errores[] = 'ciudad';
if ($datos['sexo'] === '') $errores[] = 'sexo';
if ($datos['sexo'] === 'Hombre' && $datos['cartilla_militar_liberada'] === '') $errores[] = 'cartilla militar liberada';
if ($datos['nivel_estudios'] === '') $errores[] = 'nivel de estudios';
if ($datos['experiencia'] === '') $errores[] = 'experiencia';

if (!empty($errores)) {
    responder(false, 'Por favor, revisa los campos obligatorios: ' . implode(', ', $errores) . '.');
}

$vacantesConBachillerato = ['Monitorista', 'Técnico Instalador'];
if (!in_array($datos['vacante'], $vacantesConBachillerato, true) && !nivelCumpleLicenciatura($datos['nivel_estudios'])) {
    responder(false, 'Para esta vacante se requiere licenciatura o nivel superior.');
}

$archivoAdjunto = null;
if (isset($_FILES['cv']) && $_FILES['cv']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['cv']['error'] !== UPLOAD_ERR_OK) {
        responder(false, 'No se pudo cargar el CV. Intenta de nuevo con un archivo PDF, DOC o DOCX.');
    }

    if ($_FILES['cv']['size'] > 5 * 1024 * 1024) {
        responder(false, 'El CV no debe pesar más de 5 MB.');
    }

    $permitidos = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES['cv']['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $permitidos, true)) {
        responder(false, 'El CV debe estar en formato PDF, DOC o DOCX.');
    }

    $archivoAdjunto = [
        'tmp_name' => $_FILES['cv']['tmp_name'],
        'name' => preg_replace('/[^A-Za-z0-9._-]/', '_', $_FILES['cv']['name']),
        'type' => $mime
    ];
}

$asunto = 'Postulacion - ' . $datos['vacante'] . ' | Central de Alarmas';
$fecha = date('d/m/Y H:i:s');
$mensajeHTML = "
<html>
<body style='font-family: Arial, sans-serif; color: #1a202c; line-height: 1.6;'>
    <div style='max-width: 640px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;'>
        <div style='background: #063970; color: #fff; padding: 22px 26px;'>
            <h1 style='margin: 0; font-size: 22px;'>Nueva postulacion</h1>
            <p style='margin: 6px 0 0; color: rgba(255,255,255,.82);'>Vacante: {$datos['vacante']}</p>
        </div>
        <div style='padding: 26px; background: #f8fafc;'>
            <p><strong>Nombre:</strong> {$datos['nombre']}</p>
            <p><strong>Correo:</strong> {$datos['correo']}</p>
            <p><strong>Telefono:</strong> {$datos['telefono']}</p>
            <p><strong>Ciudad:</strong> {$datos['ciudad']}</p>
            <p><strong>Sexo:</strong> {$datos['sexo']}</p>
            <p><strong>Cartilla militar liberada:</strong> {$datos['cartilla_militar_liberada']}</p>
            <p><strong>Nivel de estudios:</strong> {$datos['nivel_estudios']}</p>
            <p><strong>Experiencia:</strong> {$datos['experiencia']}</p>
            <p><strong>Disponibilidad:</strong> {$datos['disponibilidad']}</p>
            <p><strong>Mensaje:</strong><br>" . nl2br($datos['mensaje']) . "</p>
            <p style='font-size: 12px; color: #64748b;'>Enviado desde bolsa de trabajo el {$fecha}</p>
        </div>
    </div>
</body>
</html>";

$headers = [];
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'From: Central de Alarmas <' . $destinatario . '>';
$headers[] = 'Reply-To: ' . $datos['correo'];

if ($archivoAdjunto) {
    $boundary = md5((string) time());
    $headers[] = 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';

    $cuerpo = "--{$boundary}\r\n";
    $cuerpo .= "Content-Type: text/html; charset=UTF-8\r\n";
    $cuerpo .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $cuerpo .= $mensajeHTML . "\r\n";
    $cuerpo .= "--{$boundary}\r\n";
    $cuerpo .= "Content-Type: {$archivoAdjunto['type']}; name=\"{$archivoAdjunto['name']}\"\r\n";
    $cuerpo .= "Content-Disposition: attachment; filename=\"{$archivoAdjunto['name']}\"\r\n";
    $cuerpo .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $cuerpo .= chunk_split(base64_encode(file_get_contents($archivoAdjunto['tmp_name']))) . "\r\n";
    $cuerpo .= "--{$boundary}--";
} else {
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $cuerpo = $mensajeHTML;
}

if (mail($destinatario, $asunto, $cuerpo, implode("\r\n", $headers))) {
    responder(true, 'Tu postulacion fue enviada correctamente. Reclutamiento revisara tu perfil.');
}

responder(false, 'Hubo un error al enviar la postulacion. Intenta nuevamente mas tarde.');
?>
