<?php
// Este archivo es seguro para versionar. Las credenciales reales viven en
// php/marketing_secrets.php, archivo ignorado por Git.
$secretFile = __DIR__ . '/marketing_secrets.php';
if (is_file($secretFile)) {
    require_once $secretFile;
}

defined('CDA_DB_HOST') || define('CDA_DB_HOST', 'localhost');
defined('CDA_DB_NAME') || define('CDA_DB_NAME', '');
defined('CDA_DB_USER') || define('CDA_DB_USER', '');
defined('CDA_DB_PASS') || define('CDA_DB_PASS', '');

// Dominio final, sin slash al final. Ejemplo: https://centraldealarmas.com.mx
defined('CDA_SITE_URL') || define('CDA_SITE_URL', 'https://centraldealarmas.com.mx');

// Google OAuth es opcional. Crea credenciales en Google Cloud y pega aqui.
defined('CDA_GOOGLE_CLIENT_ID') || define('CDA_GOOGLE_CLIENT_ID', '');
defined('CDA_GOOGLE_CLIENT_SECRET') || define('CDA_GOOGLE_CLIENT_SECRET', '');
defined('CDA_GOOGLE_REDIRECT_URI') || define('CDA_GOOGLE_REDIRECT_URI', CDA_SITE_URL . '/google-callback.php');
