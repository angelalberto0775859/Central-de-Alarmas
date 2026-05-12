<?php
// Configuración
$destinatario = 'salducin@centraldealarmas.com.mx';
$asunto = 'Nuevo Mensaje de Contacto Empresarial - Central de Alarmas';

// Cabeceras para correo HTML
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= 'From: ' . $destinatario . "\r\n";
$headers .= 'Reply-To: ' . $destinatario . "\r\n";
$headers .= 'X-Mailer: PHP/' . phpversion();

// Función para limpiar y validar datos
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
    
    // Verificar reCAPTCHA
    $recaptcha_secret = '6LfjgrQpAAAAABoTmAW2j8d-zxYCKUWbPpb8Fb9G';
    $recaptcha_response = $_POST['g-recaptcha-response'];
    
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $recaptcha_secret . "&response=" . $recaptcha_response);
    $responseKeys = json_decode($response, true);
    
    if (intval($responseKeys["success"]) !== 1) {
        $respuesta = [
            'exito' => false,
            'mensaje' => 'Por favor, verifica que no eres un robot.'
        ];
        echo json_encode($respuesta);
        exit;
    }
    
    // Limpiar y validar datos
    $datos = [
        'nombre' => limpiarDatos($_POST['nombre']),
        'correo' => limpiarDatos($_POST['correo']),
        'telefono' => limpiarDatos($_POST['telefono']),
        'servicios' => limpiarDatos($_POST['servicios']),
        'estado' => limpiarDatos($_POST['estado']),
        'mensaje' => limpiarDatos($_POST['mensaje']),
        'titulo' => limpiarDatos($_POST['titulo'])
    ];
    
    // Validar formulario
    $errores = validarFormulario($datos);
    
    if (!empty($errores)) {
        $respuesta = [
            'exito' => false,
            'mensaje' => 'Por favor, corrige los siguientes errores: ' . implode(', ', $errores)
        ];
        echo json_encode($respuesta);
        exit;
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
            .badge { background: #f6eb17; color: #063970; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; margin-bottom: 10px; display: inline-block; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Nuevo Mensaje de Contacto Empresarial</h1>
            </div>
            <div class='content'>
                <div class='badge'>SOLICITUD EMPRESARIAL</div>
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
                <p>Este mensaje fue enviado desde el formulario de contacto empresarial de Central de Alarmas</p>
                <p>Fecha: " . date('d/m/Y H:i:s') . "</p>
            </div>
        </div>
    </body>
    </html>";
    
    // Enviar correo
    if (mail($destinatario, $asunto, $mensajeHTML, $headers)) {
        $respuesta = [
            'exito' => true,
            'mensaje' => '¡Mensaje enviado correctamente! Nos pondremos en contacto contigo pronto.'
        ];
    } else {
        $respuesta = [
            'exito' => false,
            'mensaje' => 'Hubo un error al enviar el mensaje. Por favor, inténtalo más tarde.'
        ];
    }
    
    echo json_encode($respuesta);
    exit;
} else {
    // Si alguien intenta acceder directamente al archivo
    http_response_code(403);
    echo 'Acceso denegado';
}
?>
