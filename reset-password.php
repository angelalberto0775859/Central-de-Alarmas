<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/marketing_config.php';
require_once __DIR__ . '/php/marketing_helpers.php';

$token = cdaMarketingClean($_GET['token'] ?? $_POST['token'] ?? '');
$tokenHash = $token !== '' ? cdaMarketingPasswordResetHash($token) : '';
$user = null;
$error = '';
$message = '';

if ($tokenHash !== '') {
    try {
        $stmt = cdaDb()->prepare('SELECT id, nombre, correo, activo, reset_token_expira FROM marketing_usuarios WHERE reset_token_hash = ? LIMIT 1');
        $stmt->execute([$tokenHash]);
        $user = $stmt->fetch();

        if (!$user || (int) $user['activo'] !== 1 || empty($user['reset_token_expira']) || strtotime($user['reset_token_expira']) < time()) {
            $user = null;
            $error = 'El enlace no es valido o ya vencio. Solicita uno nuevo.';
        }
    } catch (Throwable $e) {
        $error = 'No fue posible validar el enlace. Revisa que la migracion de recuperacion de contrasena este instalada.';
    }
} else {
    $error = 'El enlace no incluye un token valido.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    cdaRequirePostCsrf();
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

    if (strlen($password) < 8) {
        $error = 'La contrasena debe tener al menos 8 caracteres.';
    } elseif ($password !== $passwordConfirm) {
        $error = 'Las contrasenas no coinciden.';
    } else {
        try {
            $update = cdaDb()->prepare('UPDATE marketing_usuarios SET password_hash = ?, reset_token_hash = NULL, reset_token_expira = NULL WHERE id = ?');
            $update->execute([password_hash($password, PASSWORD_DEFAULT), (int) $user['id']]);
            cdaLoginUser((int) $user['id']);
            header('Location: panel-marketing.php');
            exit;
        } catch (Throwable $e) {
            $error = 'No fue posible guardar la nueva contrasena.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva contraseña | Marketing</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="/contigo/Img/favicon.svg">
    <style>
        :root { --blue:#063970; --ink:#10213f; --muted:#6b7890; --line:rgba(6,57,112,.13); --soft:#f4f8fc; --yellow:#f6eb17; --radius:8px; --shadow:0 28px 80px rgba(0,0,0,.28); }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { min-height:100vh; font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif; color:var(--ink); background:linear-gradient(135deg,#061226,#063970 58%,#031025); display:grid; place-items:center; padding:1rem; }
        .card { width:min(480px,100%); border:1px solid rgba(255,255,255,.24); border-radius:var(--radius); background:linear-gradient(180deg,rgba(255,255,255,.98),rgba(248,251,255,.95)); box-shadow:var(--shadow); padding:1.35rem; }
        .brand { display:block; margin-bottom:1rem; }
        .brand img { width:150px; display:block; }
        h1 { color:var(--blue); font-size:1.65rem; line-height:1.1; margin-bottom:.45rem; }
        p { color:var(--muted); line-height:1.55; margin-bottom:1rem; }
        form { display:grid; gap:.85rem; }
        label { display:grid; gap:.42rem; font-size:.78rem; font-weight:900; color:var(--ink); }
        input { width:100%; border:1px solid var(--line); border-radius:var(--radius); padding:.92rem .95rem; font:inherit; color:var(--ink); background:#fff; outline:none; }
        input:focus { border-color:var(--blue); box-shadow:0 0 0 4px rgba(6,57,112,.1); }
        button, .button { min-height:48px; border:0; border-radius:var(--radius); padding:.9rem 1rem; background:var(--yellow); color:var(--blue); font-weight:950; letter-spacing:.04em; text-transform:uppercase; cursor:pointer; text-decoration:none; display:flex; justify-content:center; align-items:center; }
        .button.secondary { margin-top:.7rem; background:#fff; border:1px solid var(--line); box-shadow:none; text-transform:none; }
        .error, .success { margin:.8rem 0; border-radius:var(--radius); padding:.78rem; font-weight:800; line-height:1.45; }
        .error { color:#b91c1c; background:#fee2e2; border:1px solid #fecaca; }
        .success { color:#047857; background:#d1fae5; border:1px solid #a7f3d0; }
    </style>
</head>
<body>
    <main class="card">
        <a class="brand" href="index.html" aria-label="Central de Alarmas"><img src="img/cda-logo-f.svg" alt="Central de Alarmas"></a>
        <h1>Crear nueva contraseña</h1>
        <p><?php echo $user ? 'Define una nueva contraseña para el acceso de ' . htmlspecialchars($user['correo']) . '.' : 'Solicita un nuevo enlace para recuperar el acceso.'; ?></p>
        <?php if ($message): ?><div class="success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if ($user): ?>
        <form method="post" action="reset-password.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(cdaCsrfToken()); ?>">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <label>Nueva contraseña <input name="password" type="password" autocomplete="new-password" minlength="8" required placeholder="Minimo 8 caracteres"></label>
            <label>Confirmar contraseña <input name="password_confirm" type="password" autocomplete="new-password" minlength="8" required placeholder="Repite la contrasena"></label>
            <button type="submit">Guardar contraseña</button>
        </form>
        <?php endif; ?>
        <a class="button secondary" href="recuperar-password.php">Solicitar otro enlace</a>
    </main>
</body>
</html>
