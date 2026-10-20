<?php
// Configuración
$destinatario = 'salducin@centraldealarmas.com.mx';
$asunto = 'Nuevo Mensaje de Contacto - Central de Alarmas';

require_once __DIR__ . '/recaptcha.php';

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

    // Crear mensaje HTML
    $mensajeHTML = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #063970; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .header h1 { margin: 0; font-size: 24px; }
            .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; border-radius: 0 0 8px 8px; }
            .campo { margin-bottom: 15px; }
            .campo strong { color: #063970; display: inline-block; width: 120px; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Nuevo Mensaje de Contacto</h1>
            </div>
            <div class='content'>
                <div class='campo'>
                    <strong>Nombre:</strong> " . $datos['nombre'] . "
                </div>
                <div class='campo'>
                    <strong>Correo:</strong> " . $datos['correo'] . "
                </div>
                <div class='campo'>
                    <strong>Teléfono:</strong> " . $datos['telefono'] . "
                </div>
                <div class='campo'>
                    <strong>Servicio:</strong> " . $datos['servicios'] . "
                </div>
                <div class='campo'>
                    <strong>Estado:</strong> " . $datos['estado'] . "
                </div>
                <div class='campo'>
                    <strong>Mensaje:</strong><br>" . nl2br($datos['mensaje']) . "
                </div>
            </div>
            <div class='footer'>
                <p>Este mensaje fue enviado desde el formulario de contacto de Central de Alarmas</p>
                <p>Fecha: " . date('d/m/Y H:i:s') . "</p>
            </div>
        </div>
    </body>
    </html>";

    // Cabeceras para correo HTML
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: ' . $destinatario . "\r\n";
    $headers .= 'Reply-To: ' . $datos['correo'] . "\r\n";
    $headers .= 'Cc: ' . $datos['correo'] . "\r\n";

    // Enviar correo
    if (mail($destinatario, $asunto, $mensajeHTML, $headers)) {
        responder(true, '¡Mensaje enviado correctamente! Nos pondremos en contacto contigo pronto.');
    } else {
        responder(false, 'Hubo un error al enviar el mensaje. Por favor, inténtalo más tarde.');
    }
} else {
    // Si alguien intenta acceder directamente al archivo
    http_response_code(403);
    responder(false, 'Acceso denegado');
}
?>
