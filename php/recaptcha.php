<?php
// La clave secreta va en php/recaptcha_secret.php o php/marketing_secrets.php
// (CDA_RECAPTCHA_SECRET), ambos ignorados por Git, o en la variable de entorno
// RECAPTCHA_SECRET. Sin clave, el reCAPTCHA no se valida.
if (is_file(__DIR__ . '/recaptcha_secret.php')) {
    require_once __DIR__ . '/recaptcha_secret.php';
}

function cdaRecaptchaSecret() {
    if (defined('CDA_RECAPTCHA_SECRET') && trim((string) CDA_RECAPTCHA_SECRET) !== '') {
        return trim((string) CDA_RECAPTCHA_SECRET);
    }
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
