<?php
require_once __DIR__ . '/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
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

function cdaLoginUser($userId) {
    session_regenerate_id(true);
    $_SESSION['cda_user_id'] = (int) $userId;
}

function cdaLogout() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
