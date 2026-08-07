<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/marketing_helpers.php';

$user = cdaRequireLogin();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cdaRequirePostCsrf();

    $nombre = cdaMarketingClean($_POST['nombre'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['password_confirm'] ?? '');

    if ($nombre === '') {
        $error = 'Escribe tu nombre para actualizar el perfil.';
    } elseif ($password !== '' && strlen($password) < 8) {
        $error = 'La contrasena debe tener al menos 8 caracteres.';
    } elseif ($password !== $confirm) {
        $error = 'La confirmacion de contrasena no coincide.';
    } else {
        try {
            if ($password !== '') {
                $stmt = cdaDb()->prepare('UPDATE marketing_usuarios SET nombre = ?, password_hash = ? WHERE id = ?');
                $stmt->execute([$nombre, password_hash($password, PASSWORD_DEFAULT), $user['id']]);
            } else {
                $stmt = cdaDb()->prepare('UPDATE marketing_usuarios SET nombre = ? WHERE id = ?');
                $stmt->execute([$nombre, $user['id']]);
            }

            $user = cdaCurrentUser();
            $message = 'Perfil actualizado correctamente.';
        } catch (Throwable $e) {
            $error = 'No fue posible actualizar tu perfil.';
        }
    }
}

$ticketStmt = cdaDb()->prepare('SELECT folio, actividad, estado, prioridad, fecha_requerida, actualizado_en FROM marketing_tickets WHERE correo = ? AND eliminado_en IS NULL ORDER BY actualizado_en DESC LIMIT 8');
$ticketStmt->execute([$user['correo']]);
$tickets = $ticketStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil | Marketing</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="/contigo/Img/favicon.svg">
    <style>
        :root { --blue:#063970; --blue-2:#0b4f92; --ink:#10213f; --muted:#66758d; --line:rgba(6,57,112,.14); --soft:#f4f8fc; --yellow:#f6eb17; --radius:8px; --shadow:0 18px 46px rgba(6,57,112,.12); }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { min-height:100vh; font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif; color:var(--ink); background:linear-gradient(135deg,#061226,#063970 58%,#031025); }
        .shell { width:min(1120px, calc(100% - 2rem)); margin:0 auto; padding:1.1rem 0 3rem; }
        .topbar { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1rem; color:#fff; border:1px solid rgba(255,255,255,.16); border-radius:var(--radius); padding:.65rem .75rem; background:rgba(255,255,255,.12); }
        .topbar img { width:140px; display:block; }
        .nav { display:flex; flex-wrap:wrap; gap:.42rem; align-items:center; justify-content:flex-end; }
        .nav a, .chip { min-height:36px; display:inline-flex; align-items:center; color:rgba(255,255,255,.88); text-decoration:none; border:1px solid rgba(255,255,255,.18); border-radius:8px; padding:.58rem .7rem; background:rgba(255,255,255,.09); font-size:.8rem; font-weight:850; }
        .nav a.active { background:var(--yellow); color:var(--blue); border-color:var(--yellow); }
        .nav a.session-link { color:#fecaca; border-color:rgba(254,202,202,.32); background:rgba(185,28,28,.12); }
        .chip { color:#fff; }
        .hero { margin:1rem 0; color:#fff; border:1px solid rgba(255,255,255,.16); border-radius:var(--radius); padding:1.2rem; background:linear-gradient(135deg,rgba(255,255,255,.12),rgba(255,255,255,.04)); }
        .eyebrow { color:var(--yellow); font-size:.74rem; font-weight:950; letter-spacing:.12em; text-transform:uppercase; margin-bottom:.55rem; }
        h1 { color:#fff; font-size:clamp(1.9rem,4vw,3.35rem); line-height:1; letter-spacing:0; }
        .grid { display:grid; grid-template-columns:390px minmax(0,1fr); gap:1rem; align-items:start; }
        .card { background:rgba(255,255,255,.96); border:1px solid var(--line); border-radius:var(--radius); padding:1rem; box-shadow:var(--shadow); }
        .card h2 { color:var(--blue); font-size:1.05rem; margin-bottom:.75rem; }
        .profile-data { display:grid; gap:.55rem; margin-bottom:1rem; }
        .profile-data div { border:1px solid rgba(6,57,112,.08); border-radius:var(--radius); background:var(--soft); padding:.7rem; }
        .profile-data span { display:block; color:var(--muted); font-size:.72rem; font-weight:900; text-transform:uppercase; margin-bottom:.2rem; }
        label { display:grid; gap:.38rem; font-size:.78rem; font-weight:850; margin-bottom:.8rem; }
        input { width:100%; border:1px solid var(--line); border-radius:var(--radius); padding:.82rem .9rem; font:inherit; background:#fff; color:var(--ink); outline:none; }
        input:focus { border-color:var(--blue-2); box-shadow:0 0 0 4px rgba(6,57,112,.09); }
        button, .button { min-height:42px; border:0; border-radius:var(--radius); padding:.78rem .9rem; background:var(--yellow); color:var(--blue); font-weight:900; text-transform:uppercase; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; }
        .ok, .error { margin-bottom:.8rem; border-radius:var(--radius); padding:.75rem; font-weight:750; }
        .ok { color:#047857; background:#d1fae5; border:1px solid #a7f3d0; }
        .error { color:#b91c1c; background:#fee2e2; border:1px solid #fecaca; }
        .tickets { display:grid; gap:.6rem; }
        .ticket { border:1px solid var(--line); border-left:5px solid var(--blue-2); border-radius:var(--radius); background:#fff; padding:.78rem; }
        .ticket strong { color:var(--blue); }
        .ticket p { color:var(--muted); margin-top:.25rem; font-size:.86rem; }
        @media (max-width:820px) { .topbar, .grid { grid-template-columns:1fr; flex-direction:column; align-items:stretch; } .nav { justify-content:flex-start; } }
    </style>
</head>
<body>
    <div class="shell">
        <header class="topbar">
            <a href="index.html"><img src="img/cda-logo-f.svg" alt="Central de Alarmas"></a>
            <nav class="nav" aria-label="Navegacion">
                <span class="chip"><?php echo htmlspecialchars($user['nombre']); ?></span>
                <a href="panel-marketing.php">Mis tickets</a>
                <a href="crear-ticket.php">Crear ticket</a>
                <a href="seguimiento.php">Seguimiento</a>
                <a class="active" href="perfil-marketing.php">Perfil</a>
                <a class="session-link" href="logout.php">Cerrar sesión</a>
            </nav>
        </header>
        <section class="hero">
            <div class="eyebrow">Cuenta activa</div>
            <h1>Información del perfil</h1>
        </section>
        <section class="grid">
            <article class="card">
                <h2>Tu perfil</h2>
                <div class="profile-data">
                    <div><span>Nombre</span><strong><?php echo htmlspecialchars($user['nombre']); ?></strong></div>
                    <div><span>Correo</span><strong><?php echo htmlspecialchars($user['correo']); ?></strong></div>
                    <div><span>Rol</span><strong><?php echo htmlspecialchars($user['rol']); ?></strong></div>
                </div>
                <?php if ($message): ?><div class="ok"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
                <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                <form method="post" action="perfil-marketing.php">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(cdaCsrfToken()); ?>">
                    <label>Nombre <input name="nombre" value="<?php echo htmlspecialchars($user['nombre']); ?>" required></label>
                    <label>Nueva contrasena <input name="password" type="password" minlength="8" autocomplete="new-password" placeholder="Opcional"></label>
                    <label>Confirmar contrasena <input name="password_confirm" type="password" minlength="8" autocomplete="new-password" placeholder="Opcional"></label>
                    <button type="submit">Guardar perfil</button>
                </form>
            </article>
            <article class="card">
                <h2>Últimos tickets generados</h2>
                <div class="tickets">
                    <?php foreach ($tickets as $ticket): ?>
                    <a class="ticket" href="seguimiento.php?folio=<?php echo urlencode($ticket['folio']); ?>">
                        <strong><?php echo htmlspecialchars($ticket['folio']); ?> · <?php echo htmlspecialchars($ticket['actividad']); ?></strong>
                        <p><?php echo htmlspecialchars(cdaMarketingStatusLabel($ticket['estado'])); ?> · <?php echo htmlspecialchars($ticket['prioridad']); ?> · requerido <?php echo htmlspecialchars(cdaMarketingFormatDate($ticket['fecha_requerida'])); ?></p>
                    </a>
                    <?php endforeach; ?>
                    <?php if (!$tickets): ?><p class="muted">Aun no has generado tickets con este usuario.</p><?php endif; ?>
                </div>
            </article>
        </section>
    </div>
</body>
</html>
