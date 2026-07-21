<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/marketing_helpers.php';

$user = cdaRequireLogin();
if ($user['rol'] !== 'admin') {
    header('Location: panel-marketing.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = cdaMarketingClean($_POST['nombre'] ?? '');
    $correo = filter_var(cdaMarketingClean($_POST['correo'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = (string) ($_POST['password'] ?? '');
    $rol = in_array($_POST['rol'] ?? '', ['admin', 'marketing'], true) ? $_POST['rol'] : 'marketing';
    $activo = !empty($_POST['activo']) ? 1 : 0;

    if (!$nombre || !$correo) {
        $error = 'Nombre y correo son obligatorios.';
    } else {
        try {
            $hash = $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : null;
            $stmt = cdaDb()->prepare(
                'INSERT INTO marketing_usuarios (nombre, correo, password_hash, rol, activo)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), rol = VALUES(rol), activo = VALUES(activo), password_hash = COALESCE(VALUES(password_hash), password_hash)'
            );
            $stmt->execute([$nombre, $correo, $hash, $rol, $activo]);
            $message = 'Usuario guardado correctamente.';
        } catch (Throwable $e) {
            $error = 'No fue posible guardar el usuario.';
        }
    }
}

$users = cdaDb()->query('SELECT id, nombre, correo, rol, activo, creado_en FROM marketing_usuarios ORDER BY nombre ASC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios autorizados | Diseño y Marketing</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="/contigo/Img/favicon.svg">
    <style>
        :root { --blue:#063970; --ink:#10213f; --muted:#66758d; --line:rgba(6,57,112,.14); --soft:#f4f8fc; --yellow:#f6eb17; --radius:8px; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { min-height:100vh; font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif; color:var(--ink); background:#f3f7fb; }
        .shell { width:min(1100px, calc(100% - 2rem)); margin:0 auto; padding:1.2rem 0 3rem; }
        .topbar { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1.2rem; }
        .topbar img { width:146px; display:block; }
        .nav { display:flex; flex-wrap:wrap; gap:.6rem; align-items:center; }
        .nav a { color:var(--blue); text-decoration:none; border:1px solid var(--line); border-radius:8px; padding:.7rem .85rem; background:#fff; font-size:.82rem; font-weight:850; }
        h1 { color:var(--blue); font-size:clamp(1.8rem,4vw,3rem); margin-bottom:.35rem; }
        .muted { color:var(--muted); }
        .grid { display:grid; grid-template-columns:360px 1fr; gap:1rem; align-items:start; margin-top:1rem; }
        .card { background:#fff; border:1px solid var(--line); border-radius:var(--radius); padding:1rem; box-shadow:0 12px 38px rgba(6,57,112,.08); }
        form { display:grid; gap:.85rem; }
        label { display:grid; gap:.38rem; font-size:.78rem; font-weight:850; }
        input, select { width:100%; border:1px solid var(--line); border-radius:var(--radius); padding:.82rem .9rem; font:inherit; background:#fff; }
        .check { display:flex; align-items:center; gap:.5rem; }
        .check input { width:auto; }
        button { min-height:46px; border:0; border-radius:var(--radius); padding:.85rem 1rem; background:var(--yellow); color:var(--blue); font-weight:900; text-transform:uppercase; cursor:pointer; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:.78rem; border-bottom:1px solid var(--line); text-align:left; font-size:.9rem; }
        th { color:var(--blue); font-size:.75rem; text-transform:uppercase; letter-spacing:.04em; }
        .pill { display:inline-flex; border-radius:999px; padding:.36rem .58rem; background:var(--soft); color:var(--blue); font-size:.75rem; font-weight:900; }
        .ok, .error { margin-bottom:.8rem; border-radius:var(--radius); padding:.75rem; font-weight:750; }
        .ok { color:#047857; background:#d1fae5; border:1px solid #a7f3d0; }
        .error { color:#b91c1c; background:#fee2e2; border:1px solid #fecaca; }
        @media (max-width:820px) { .topbar, .grid { grid-template-columns:1fr; flex-direction:column; align-items:stretch; } table, tbody, tr, td { display:block; } thead { display:none; } tr { border:1px solid var(--line); border-radius:var(--radius); margin-bottom:.75rem; } td { border:0; } }
    </style>
</head>
<body>
    <div class="shell">
        <header class="topbar">
            <a href="index.html"><img src="img/cda-logo-f.svg" alt="Central de Alarmas"></a>
            <nav class="nav" aria-label="Navegacion">
                <a href="panel-marketing.php">Tickets</a>
                <a href="marketing.html">Crear ticket</a>
                <a href="logout.php">Salir</a>
            </nav>
        </header>
        <h1>Usuarios autorizados</h1>
        <p class="muted">Da de alta correos que pueden entrar al panel por contrasena o por Google cuando este configurado.</p>
        <section class="grid">
            <article class="card">
                <?php if ($message): ?><div class="ok"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
                <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                <form method="post" action="usuarios-marketing.php">
                    <label>Nombre <input name="nombre" required></label>
                    <label>Correo autorizado <input name="correo" type="email" required></label>
                    <label>Contrasena inicial <input name="password" type="password" placeholder="Opcional si usara Google"></label>
                    <label>Rol
                        <select name="rol">
                            <option value="marketing">Marketing</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </label>
                    <label class="check"><input name="activo" type="checkbox" value="1" checked> Usuario activo</label>
                    <button type="submit">Guardar usuario</button>
                </form>
            </article>
            <article class="card">
                <table>
                    <thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Estado</th></tr></thead>
                    <tbody>
                        <?php foreach ($users as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($item['correo']); ?></td>
                            <td><span class="pill"><?php echo htmlspecialchars($item['rol']); ?></span></td>
                            <td><?php echo (int) $item['activo'] === 1 ? 'Activo' : 'Inactivo'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </article>
        </section>
    </div>
</body>
</html>
