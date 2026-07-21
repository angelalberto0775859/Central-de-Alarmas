<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/marketing_config.php';

if (empty($_GET['state']) || empty($_SESSION['google_oauth_state']) || !hash_equals($_SESSION['google_oauth_state'], $_GET['state'])) {
    header('Location: login.php');
    exit;
}

unset($_SESSION['google_oauth_state']);

if (empty($_GET['code'])) {
    header('Location: login.php');
    exit;
}

function cdaGooglePost($url, $payload) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($payload),
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT => 12,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode((string) $response, true);
}

function cdaGoogleGet($url, $token) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token],
        CURLOPT_TIMEOUT => 12,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode((string) $response, true);
}

$token = cdaGooglePost('https://oauth2.googleapis.com/token', [
    'code' => $_GET['code'],
    'client_id' => CDA_GOOGLE_CLIENT_ID,
    'client_secret' => CDA_GOOGLE_CLIENT_SECRET,
    'redirect_uri' => CDA_GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code',
]);

if (empty($token['access_token'])) {
    header('Location: login.php');
    exit;
}

$profile = cdaGoogleGet('https://openidconnect.googleapis.com/v1/userinfo', $token['access_token']);
$correo = filter_var($profile['email'] ?? '', FILTER_VALIDATE_EMAIL);

if (!$correo) {
    header('Location: login.php');
    exit;
}

$stmt = cdaDb()->prepare('SELECT id, google_sub, activo FROM marketing_usuarios WHERE correo = ? LIMIT 1');
$stmt->execute([$correo]);
$user = $stmt->fetch();

if (!$user || (int) $user['activo'] !== 1) {
    header('Location: login.php');
    exit;
}

if (empty($user['google_sub']) && !empty($profile['sub'])) {
    $update = cdaDb()->prepare('UPDATE marketing_usuarios SET google_sub = ? WHERE id = ?');
    $update->execute([$profile['sub'], $user['id']]);
}

cdaLoginUser($user['id']);
header('Location: panel-marketing.php');
exit;
