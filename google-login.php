<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/marketing_config.php';

if (CDA_GOOGLE_CLIENT_ID === '' || CDA_GOOGLE_CLIENT_SECRET === '') {
    header('Location: login.php');
    exit;
}

$_SESSION['google_oauth_state'] = bin2hex(random_bytes(16));
$params = [
    'client_id' => CDA_GOOGLE_CLIENT_ID,
    'redirect_uri' => CDA_GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $_SESSION['google_oauth_state'],
    'prompt' => 'select_account',
    'include_granted_scopes' => 'true',
];

header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
exit;
