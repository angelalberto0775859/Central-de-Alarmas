<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/marketing_config.php';

function cdaGoogleLoginFail($reason = 'google') {
    header('Location: login.php?error=' . urlencode($reason));
    exit;
}

$returnTo = cdaSafeLocalReturnTo($_SESSION['google_oauth_return_to'] ?? 'panel-marketing.php');
unset($_SESSION['google_oauth_return_to']);

if (empty($_GET['state']) || empty($_SESSION['google_oauth_state']) || !hash_equals($_SESSION['google_oauth_state'], $_GET['state'])) {
    cdaGoogleLoginFail('google_state');
}

unset($_SESSION['google_oauth_state']);

if (empty($_GET['code'])) {
    cdaGoogleLoginFail('google_code');
}

function cdaGooglePost($url, $payload) {
    $body = http_build_query($payload);

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT => 12,
        ]);
        $response = curl_exec($ch);
        if ($response === false) {
            curl_close($ch);
            return null;
        }
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($status < 200 || $status >= 300) {
            return null;
        }
        return json_decode((string) $response, true);
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $body,
            'timeout' => 12,
            'ignore_errors' => true,
        ],
    ]);
    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        return null;
    }

    $status = 0;
    foreach ($http_response_header ?? [] as $header) {
        if (preg_match('/^HTTP\/\S+\s+(\d+)/', $header, $matches)) {
            $status = (int) $matches[1];
            break;
        }
    }
    if ($status < 200 || $status >= 300) {
        return null;
    }

    return json_decode((string) $response, true);
}

function cdaGoogleGet($url, $token) {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token],
            CURLOPT_TIMEOUT => 12,
        ]);
        $response = curl_exec($ch);
        if ($response === false) {
            curl_close($ch);
            return null;
        }
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($status < 200 || $status >= 300) {
            return null;
        }
        return json_decode((string) $response, true);
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Authorization: Bearer ' . $token . "\r\n",
            'timeout' => 12,
            'ignore_errors' => true,
        ],
    ]);
    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        return null;
    }

    $status = 0;
    foreach ($http_response_header ?? [] as $header) {
        if (preg_match('/^HTTP\/\S+\s+(\d+)/', $header, $matches)) {
            $status = (int) $matches[1];
            break;
        }
    }
    if ($status < 200 || $status >= 300) {
        return null;
    }

    return json_decode((string) $response, true);
}

try {
    $token = cdaGooglePost('https://oauth2.googleapis.com/token', [
        'code' => $_GET['code'],
        'client_id' => cdaGoogleClientId(),
        'client_secret' => cdaGoogleClientSecret(),
        'redirect_uri' => cdaGoogleRedirectUri(),
        'grant_type' => 'authorization_code',
    ]);

    if (empty($token['access_token'])) {
        cdaGoogleLoginFail('google_token');
    }

    $profile = cdaGoogleGet('https://openidconnect.googleapis.com/v1/userinfo', $token['access_token']);
    if (!is_array($profile) || empty($profile['sub'])) {
        cdaGoogleLoginFail('google_profile');
    }

    $correo = filter_var(strtolower($profile['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $emailVerified = filter_var($profile['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);

    if (!$correo || !$emailVerified) {
        cdaGoogleLoginFail('google_email');
    }

    $stmt = cdaDb()->prepare('SELECT id, google_sub, activo FROM marketing_usuarios WHERE correo = ? LIMIT 1');
    $stmt->execute([$correo]);
    $user = $stmt->fetch();

    if (!$user) {
        $displayName = trim((string) ($profile['name'] ?? ''));
        if ($displayName === '') {
            $displayName = strstr($correo, '@', true) ?: $correo;
        }

        $insert = cdaDb()->prepare(
            'INSERT INTO marketing_usuarios (nombre, correo, google_sub, rol, activo)
            VALUES (?, ?, ?, \'usuario\', 1)'
        );
        $insert->execute([$displayName, $correo, $profile['sub']]);

        cdaLoginUser(cdaDb()->lastInsertId());
        header('Location: ' . $returnTo);
        exit;
    }

    if ((int) $user['activo'] !== 1) {
        cdaGoogleLoginFail('google_not_allowed');
    }

    if (!empty($user['google_sub']) && !empty($profile['sub']) && !hash_equals($user['google_sub'], $profile['sub'])) {
        cdaGoogleLoginFail('google_account_mismatch');
    }

    if (empty($user['google_sub']) && !empty($profile['sub'])) {
        $update = cdaDb()->prepare('UPDATE marketing_usuarios SET google_sub = ? WHERE id = ?');
        $update->execute([$profile['sub'], $user['id']]);
    }

    cdaLoginUser($user['id']);
    header('Location: ' . $returnTo);
    exit;
} catch (PDOException $e) {
    error_log('CDA Google login database error: ' . $e->getMessage());
    if (strpos($e->getMessage(), '1146') !== false || stripos($e->getMessage(), "doesn't exist") !== false) {
        cdaGoogleLoginFail('google_db_schema');
    }
    cdaGoogleLoginFail('google_db');
} catch (Throwable $e) {
    error_log('CDA Google login error: ' . $e->getMessage());
    cdaGoogleLoginFail('google');
}
