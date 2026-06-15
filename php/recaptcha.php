<?php
function cdaRecaptchaSecret() {
    return getenv('RECAPTCHA_SECRET') ?: '';
}

function cdaValidarRecaptcha($post) {
    $secret = cdaRecaptchaSecret();

    if ($secret === '') {
        return true;
    }

    $respuesta = $post['g-recaptcha-response'] ?? '';
    if ($respuesta === '') {
        return false;
    }

    $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secret) . '&response=' . urlencode($respuesta);
    $verificacion = @file_get_contents($url);
    $datos = $verificacion ? json_decode($verificacion, true) : null;

    return !empty($datos['success']);
}
?>
