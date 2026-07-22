<?php
require_once __DIR__ . '/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function cdaCsrfToken() {
    if (empty($_SESSION['cda_csrf_token'])) {
        $_SESSION['cda_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['cda_csrf_token'];
}

function cdaVerifyCsrfToken($token) {
    return !empty($_SESSION['cda_csrf_token']) && is_string($token) && hash_equals($_SESSION['cda_csrf_token'], $token);
}

function cdaRequirePostCsrf() {
    if (!cdaVerifyCsrfToken($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit('Solicitud no valida.');
    }
}

function cdaCurrentUser() {
    if (empty($_SESSION['cda_user_id'])) {
        return null;
    }

    $stmt = cdaDb()->prepare('SELECT id, nombre, correo, rol, activo FROM marketing_usuarios WHERE id = ? LIMIT 1');
    $stmt->execute([$_SESSION['cda_user_id']]);
    $user = $stmt->fetch();

    if (!$user || (int) $user['activo'] !== 1) {
        cdaLogout();
        return null;
    }

    return $user;
}

function cdaRequireLogin() {
    $user = cdaCurrentUser();
    if (!$user) {
        header('Location: login.php');
        exit;
    }
    return $user;
}

function cdaSafeLocalReturnTo($value, $fallback = 'panel-marketing.php') {
    $value = trim((string) $value);
    if ($value === '') {
        return $fallback;
    }

    if (preg_match('/[\r\n]/', $value) || preg_match('#^[a-z][a-z0-9+.-]*:#i', $value) || substr($value, 0, 2) === '//') {
        return $fallback;
    }

    $allowed = ['panel-marketing.php', 'control-marketing.php', 'seguimiento.php'];
    $path = parse_url($value, PHP_URL_PATH) ?: $value;

    return in_array($path, $allowed, true) ? $value : $fallback;
}

function cdaLoginUser($userId) {
    session_regenerate_id(true);
    $_SESSION['cda_user_id'] = (int) $userId;
    unset($_SESSION['cda_login_failures'], $_SESSION['cda_login_locked_until']);
}

function cdaLogout() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
