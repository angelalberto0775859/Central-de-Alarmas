<?php
// Este archivo es seguro para versionar. Las credenciales reales viven en
// php/marketing_secrets.php, archivo ignorado por Git.
$secretFile = __DIR__ . '/marketing_secrets.php';
if (is_file($secretFile)) {
    require_once $secretFile;
}

function cdaConfigValue($key, $default = '') {
    $value = getenv($key);
    if ($value === false && isset($_ENV[$key])) {
        $value = $_ENV[$key];
    }
    if ($value === false && isset($_SERVER[$key])) {
        $value = $_SERVER[$key];
    }

    return is_string($value) && trim($value) !== '' ? trim($value) : $default;
}

function cdaConfigRuntimeValue($key, $default = '') {
    $constantValue = defined($key) ? (string) constant($key) : '';
    if (trim($constantValue) !== '') {
        return trim($constantValue);
    }

    return cdaConfigValue($key, $default);
}

defined('CDA_DB_HOST') || define('CDA_DB_HOST', cdaConfigValue('CDA_DB_HOST', 'localhost'));
defined('CDA_DB_NAME') || define('CDA_DB_NAME', cdaConfigValue('CDA_DB_NAME'));
defined('CDA_DB_USER') || define('CDA_DB_USER', cdaConfigValue('CDA_DB_USER'));
defined('CDA_DB_PASS') || define('CDA_DB_PASS', cdaConfigValue('CDA_DB_PASS'));

// Dominio final, sin slash al final. Ejemplo: https://centraldealarmas.com.mx
defined('CDA_SITE_URL') || define('CDA_SITE_URL', rtrim(cdaConfigValue('CDA_SITE_URL', 'https://centraldealarmas.com.mx'), '/'));

// Google OAuth es opcional. Crea credenciales en Google Cloud y pega aqui.
defined('CDA_GOOGLE_CLIENT_ID') || define('CDA_GOOGLE_CLIENT_ID', cdaConfigValue('CDA_GOOGLE_CLIENT_ID'));
defined('CDA_GOOGLE_CLIENT_SECRET') || define('CDA_GOOGLE_CLIENT_SECRET', cdaConfigValue('CDA_GOOGLE_CLIENT_SECRET'));
defined('CDA_GOOGLE_REDIRECT_URI') || define('CDA_GOOGLE_REDIRECT_URI', cdaConfigValue('CDA_GOOGLE_REDIRECT_URI', CDA_SITE_URL . '/google-callback.php'));

function cdaGoogleClientId() {
    return cdaConfigRuntimeValue('CDA_GOOGLE_CLIENT_ID');
}

function cdaGoogleClientSecret() {
    return cdaConfigRuntimeValue('CDA_GOOGLE_CLIENT_SECRET');
}

function cdaGoogleRedirectUri() {
    return cdaConfigRuntimeValue('CDA_GOOGLE_REDIRECT_URI', CDA_SITE_URL . '/google-callback.php');
}

function cdaGoogleOAuthReady() {
    $clientId = cdaGoogleClientId();
    $clientSecret = cdaGoogleClientSecret();

    return $clientId !== ''
        && $clientSecret !== ''
        && strpos($clientId, 'TU_') !== 0
        && strpos($clientSecret, 'TU_') !== 0;
}
