<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/marketing_config.php';

if (!cdaGoogleOAuthReady()) {
    header('Location: login.php?error=google_config');
    exit;
}

$_SESSION['google_oauth_state'] = bin2hex(random_bytes(16));
$params = [
    'client_id' => cdaGoogleClientId(),
    'redirect_uri' => cdaGoogleRedirectUri(),
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $_SESSION['google_oauth_state'],
    'prompt' => 'select_account',
    'include_granted_scopes' => 'true',
];

header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
exit;
