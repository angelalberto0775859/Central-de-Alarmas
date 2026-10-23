<?php
// Configuración
$destinatario = 'rvillaverde@centraldealarmas.com.mx';
$copia = 'marketing@centraldealarmas.com.mx';
$asunto = 'Nuevo Mensaje de Contacto - Central de Alarmas';

require_once __DIR__ . '/recaptcha.php';
require_once __DIR__ . '/mailer.php';

header('Content-Type: application/json; charset=UTF-8');

function responder($exito, $mensaje) {
    echo json_encode([
        'exito' => $exito,
        'mensaje' => $mensaje
    ]);
    exit;
}

function limpiarDatos($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Función de validación
function validarFormulario($datos) {
    $errores = [];

    if (empty($datos['nombre'])) {
        $errores[] = 'El nombre es obligatorio';
    }

    if (empty($datos['correo']) || !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electrónico no es válido';
    }

    if (empty($datos['telefono'])) {
        $errores[] = 'El teléfono es obligatorio';
    }

    if (empty($datos['servicios'])) {
        $errores[] = 'Debes seleccionar un servicio';
    }

    if (empty($datos['estado'])) {
        $errores[] = 'Debes seleccionar un estado';
    }

    if (empty($datos['mensaje'])) {
        $errores[] = 'El mensaje es obligatorio';
    }

    return $errores;
}

// Verificar si el formulario fue enviado por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!cdaValidarRecaptcha($_POST)) {
        responder(false, 'Por favor, verifica que no eres un robot.');
    }

    // Limpiar y validar datos
    $datos = [
        'nombre' => limpiarDatos($_POST['nombre'] ?? ''),
        'correo' => limpiarDatos($_POST['correo'] ?? ''),
        'telefono' => limpiarDatos($_POST['telefono'] ?? ''),
        'servicios' => limpiarDatos($_POST['servicios'] ?? ''),
        'estado' => limpiarDatos($_POST['estado'] ?? ''),
        'mensaje' => limpiarDatos($_POST['mensaje'] ?? ''),
        'titulo' => limpiarDatos($_POST['titulo'] ?? '')
    ];

    // Validar formulario
    $errores = validarFormulario($datos);

    if (!empty($errores)) {
        responder(false, 'Por favor, corrige los siguientes errores: ' . implode(', ', $errores));
    }

    $asunto = 'Contacto de ' . $datos['nombre'] . ' | Formulario de contacto (Inicio) - Central de Alarmas';

    // Crear mensaje HTML
    $correoLink = "<a href='mailto:" . $datos['correo'] . "' style='color: #063970;'>" . $datos['correo'] . "</a>";
    $telefonoLink = "<a href='tel:" . preg_replace('/[^0-9+]/', '', $datos['telefono']) . "' style='color: #063970;'>" . $datos['telefono'] . "</a>";
    $mensajeHTML = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 640px; margin: 0 auto; padding: 20px; }
            .header { background: #063970; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .header h1 { margin: 0; font-size: 24px; }
            .header p { margin: 6px 0 0; color: rgba(255,255,255,.85); font-size: 14px; }
            .content { background: #f9f9f9; padding: 26px 30px; border: 1px solid #ddd; border-radius: 0 0 8px 8px; }
            .seccion { margin: 0 0 8px; padding-bottom: 4px; border-bottom: 2px solid #f6eb17; color: #063970; font-size: 15px; text-transform: uppercase; letter-spacing: .5px; }
            .campo { margin-bottom: 10px; }
            .campo strong { color: #063970; display: inline-block; width: 120px; }
            .mensaje { background: #fff; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px 14px; margin-bottom: 22px; }
            .badge { background: #f6eb17; color: #063970; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; margin-bottom: 14px; display: inline-block; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Nuevo Mensaje de Contacto</h1>
                <p>Formulario de contacto (Inicio)</p>
            </div>
            <div class='content'>
                <div class='badge'>SOLICITUD DE CONTACTO</div>

                <h2 class='seccion'>Datos del cliente</h2>
                <div class='campo'><strong>Nombre:</strong> " . $datos['nombre'] . "</div>
                <div class='campo'><strong>Correo:</strong> " . $correoLink . "</div>
                <div class='campo'><strong>Teléfono:</strong> " . $telefonoLink . "</div>
                <div class='campo' style='margin-bottom: 22px;'><strong>Estado:</strong> " . $datos['estado'] . "</div>

                <h2 class='seccion'>Solicitud</h2>
                <div class='campo'><strong>Servicio:</strong> " . $datos['servicios'] . "</div>
                <div class='campo'><strong>Mensaje:</strong></div>
                <div class='mensaje'>" . nl2br($datos['mensaje']) . "</div>

                <h2 class='seccion'>Origen del mensaje</h2>
                " . cdaOrigenFormularioHTML('Formulario de contacto (Inicio)') . "
            </div>
            <div class='footer'>
                <p>Responde este correo para contestarle directamente al cliente.</p>
            </div>
        </div>
    </body>
    </html>";

    // Cabeceras para correo HTML
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: Central de Alarmas <' . CDA_MAIL_FROM . ">\r\n";
    // Sin copia (Cc) a quien llena el formulario: los bots dejan correos falsos que
    // rebotan y agotan el limite de envio del buzon de Hostinger.
    $headers .= 'Reply-To: ' . $datos['correo'] . "\r\n";
    $headers .= 'Cc: ' . $copia . "\r\n";

    // Enviar correo
    if (cdaMarketingMail($destinatario, $asunto, $mensajeHTML, $headers)) {
        responder(true, '¡Listo! Recibimos tu solicitud. Un asesor te contactará en breve.');
    } else {
        responder(false, 'Hubo un error al enviar el mensaje. Por favor, inténtalo más tarde.');
    }
} else {
    // Si alguien intenta acceder directamente al archivo
    http_response_code(403);
    responder(false, 'Acceso denegado');
}
?>
