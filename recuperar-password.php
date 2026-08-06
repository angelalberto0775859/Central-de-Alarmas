<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/marketing_config.php';
require_once __DIR__ . '/php/marketing_helpers.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cdaRequirePostCsrf();
    $correo = filter_var(strtolower(cdaMarketingClean($_POST['correo'] ?? '')), FILTER_VALIDATE_EMAIL);

    if (!$correo) {
        $error = 'Escribe un correo valido para recuperar el acceso.';
    } else {
        try {
            $stmt = cdaDb()->prepare('SELECT id, nombre, correo, activo FROM marketing_usuarios WHERE correo = ? LIMIT 1');
            $stmt->execute([$correo]);
            $user = $stmt->fetch();

            if ($user && (int) $user['activo'] === 1) {
                $token = cdaMarketingPasswordResetToken();
                $update = cdaDb()->prepare('UPDATE marketing_usuarios SET reset_token_hash = ?, reset_token_expira = ? WHERE id = ?');
                $update->execute([
                    cdaMarketingPasswordResetHash($token),
                    cdaMarketingPasswordResetExpiresAt(),
                    (int) $user['id'],
                ]);
                cdaMarketingSendPasswordResetEmail($user, $token);
            }

            $message = 'Si el correo esta registrado y activo, enviaremos un enlace para crear una nueva contrasena.';
        } catch (Throwable $e) {
            $error = 'No fue posible preparar la recuperacion. Revisa que la migracion de recuperacion de contrasena este instalada.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña | Marketing</title>
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
        <h1>Recuperar contraseña</h1>
        <p>Te enviaremos un enlace al correo registrado para definir una nueva contraseña de acceso.</p>
        <?php if ($message): ?><div class="success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="post" action="recuperar-password.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(cdaCsrfToken()); ?>">
            <label>Correo registrado <input name="correo" type="email" autocomplete="email" required placeholder="usuario@centraldealarmas.com.mx"></label>
            <button type="submit">Enviar enlace</button>
        </form>
        <a class="button secondary" href="login.php">Volver a iniciar sesión</a>
    </main>
</body>
</html>
