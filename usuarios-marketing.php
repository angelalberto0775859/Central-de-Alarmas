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
    cdaRequirePostCsrf();
    $action = $_POST['action'] ?? 'save';

    if ($action === 'delete') {
        $deleteId = (int) ($_POST['user_id'] ?? 0);

        if ($deleteId <= 0) {
            $error = 'Usuario no valido.';
        } elseif ($deleteId === (int) $user['id']) {
            $error = 'No puedes eliminar tu propio usuario.';
        } else {
            try {
                $db = cdaDb();
                $target = $db->prepare('SELECT id, rol, activo FROM marketing_usuarios WHERE id = ? LIMIT 1');
                $target->execute([$deleteId]);
                $targetUser = $target->fetch();

                if (!$targetUser) {
                    $error = 'El usuario no existe.';
                } else {
                    if ($targetUser['rol'] === 'admin' && (int) $targetUser['activo'] === 1) {
                        $adminCount = (int) $db->query("SELECT COUNT(*) FROM marketing_usuarios WHERE rol = 'admin' AND activo = 1")->fetchColumn();
                        if ($adminCount <= 1) {
                            throw new RuntimeException('last_admin');
                        }
                    }

                    $delete = $db->prepare('DELETE FROM marketing_usuarios WHERE id = ?');
                    $delete->execute([$deleteId]);
                    $message = 'Usuario eliminado correctamente.';
                }
            } catch (RuntimeException $e) {
                $error = 'No puedes eliminar al ultimo administrador activo.';
            } catch (Throwable $e) {
                $error = 'No fue posible eliminar el usuario.';
            }
        }
    } else {
        $nombre = cdaMarketingClean($_POST['nombre'] ?? '');
        $correo = filter_var(strtolower(cdaMarketingClean($_POST['correo'] ?? '')), FILTER_VALIDATE_EMAIL);
        $password = (string) ($_POST['password'] ?? '');
        $rol = in_array($_POST['rol'] ?? '', ['admin', 'marketing'], true) ? $_POST['rol'] : 'marketing';
        $activo = !empty($_POST['activo']) ? 1 : 0;

        if (!$nombre || !$correo) {
            $error = 'Nombre y correo son obligatorios.';
        } elseif ($password !== '' && strlen($password) < 8) {
            $error = 'La contrasena debe tener al menos 8 caracteres.';
        } elseif (strcasecmp($correo, $user['correo']) === 0 && ($rol !== 'admin' || $activo !== 1)) {
            $error = 'No puedes quitarte el rol administrador ni desactivar tu propio usuario.';
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
}

$users = cdaDb()->query('SELECT id, nombre, correo, rol, activo, google_sub, creado_en FROM marketing_usuarios ORDER BY nombre ASC')->fetchAll();
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
        :root { --blue:#063970; --blue-2:#0b4f92; --blue-3:#0d62ad; --ink:#10213f; --muted:#66758d; --line:rgba(6,57,112,.14); --soft:#f4f8fc; --yellow:#f6eb17; --radius:8px; --shadow:0 18px 46px rgba(6,57,112,.12); }
        * { box-sizing:border-box; margin:0; padding:0; }
        body {
            min-height:100vh;
            font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
            color:var(--ink);
            background:
                radial-gradient(circle at 12% 8%, rgba(246,235,23,.22), transparent 17rem),
                radial-gradient(circle at 88% 12%, rgba(13,98,173,.24), transparent 24rem),
                linear-gradient(180deg,#061b39 0 315px,#eef5fb 315px,#f7faff 100%);
        }
        body::before {
            content:"";
            position:fixed;
            inset:0 0 auto;
            height:315px;
            pointer-events:none;
            background:radial-gradient(circle at 1px 1px, rgba(255,255,255,.2) 1px, transparent 1.8px);
            background-size:26px 26px;
            mask-image:linear-gradient(180deg,#000,transparent);
        }
        body::after { content:""; position:fixed; inset:0; pointer-events:none; background:linear-gradient(120deg, transparent 0 40%, rgba(255,255,255,.08) 41%, transparent 44% 100%); opacity:.34; }
        .shell { width:min(1180px, calc(100% - 2rem)); margin:0 auto; padding:1.1rem 0 3rem; position:relative; z-index:1; }
        .topbar { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1rem; color:#fff; border:1px solid rgba(255,255,255,.16); border-radius:var(--radius); padding:.7rem; background:rgba(255,255,255,.08); backdrop-filter:blur(14px); box-shadow:0 18px 50px rgba(0,0,0,.14); }
        .topbar img { width:146px; display:block; filter:drop-shadow(0 10px 18px rgba(0,0,0,.18)); }
        .nav { display:flex; flex-wrap:wrap; gap:.6rem; align-items:center; }
        .nav a { color:rgba(255,255,255,.9); text-decoration:none; border:1px solid rgba(255,255,255,.22); border-radius:8px; padding:.7rem .85rem; background:rgba(255,255,255,.1); font-size:.82rem; font-weight:850; transition:background .18s ease, color .18s ease; }
        .nav a:hover, .nav a.active { background:#fff; color:var(--blue); }
        .hero { margin:1rem 0; color:#fff; border:1px solid rgba(255,255,255,.16); border-radius:var(--radius); padding:1.2rem; background:linear-gradient(135deg,rgba(255,255,255,.12),rgba(255,255,255,.04)); box-shadow:0 24px 70px rgba(0,0,0,.16); position:relative; overflow:hidden; }
        .hero::before { content:""; position:absolute; inset:0 0 auto; height:5px; background:linear-gradient(90deg,var(--yellow),rgba(255,255,255,.6),var(--blue-3)); }
        .hero > * { position:relative; z-index:1; }
        .eyebrow { color:var(--yellow); font-size:.74rem; font-weight:950; letter-spacing:.12em; text-transform:uppercase; margin-bottom:.55rem; }
        h1 { color:#fff; font-size:clamp(1.8rem,4vw,3rem); margin-bottom:.35rem; letter-spacing:0; }
        .hero .muted { color:rgba(255,255,255,.76); max-width:760px; }
        .muted { color:var(--muted); }
        .grid { display:grid; grid-template-columns:380px minmax(0,1fr); gap:1rem; align-items:start; margin-top:1rem; }
        .card { background:rgba(255,255,255,.96); border:1px solid var(--line); border-radius:var(--radius); padding:1rem; box-shadow:var(--shadow); }
        .card-head { display:grid; gap:.22rem; margin-bottom:.9rem; }
        .card-head h2 { color:var(--blue); font-size:1.05rem; }
        form { display:grid; gap:.85rem; }
        label { display:grid; gap:.38rem; font-size:.78rem; font-weight:850; }
        input, select { width:100%; border:1px solid var(--line); border-radius:var(--radius); padding:.82rem .9rem; font:inherit; background:#fff; color:var(--ink); outline:none; }
        input:focus, select:focus { border-color:var(--blue-2); box-shadow:0 0 0 4px rgba(6,57,112,.09); }
        .check { display:flex; align-items:center; gap:.5rem; }
        .check input { width:auto; }
        button { min-height:46px; border:0; border-radius:var(--radius); padding:.85rem 1rem; background:var(--yellow); color:var(--blue); font-weight:900; text-transform:uppercase; cursor:pointer; box-shadow:0 10px 22px rgba(6,57,112,.1); transition:transform .18s ease, box-shadow .18s ease; }
        button:hover { transform:translateY(-1px); box-shadow:0 14px 28px rgba(6,57,112,.14); }
        .danger-button { min-height:auto; padding:.55rem .65rem; background:#fee2e2; color:#991b1b; box-shadow:none; font-size:.72rem; }
        .danger-button:hover { box-shadow:0 10px 20px rgba(185,28,28,.12); }
        .row-action { display:block; }
        .table-shell { overflow:auto; border:1px solid rgba(6,57,112,.08); border-radius:var(--radius); background:#fff; }
        table { width:100%; min-width:760px; border-collapse:separate; border-spacing:0; }
        th, td { padding:.78rem; border-bottom:1px solid var(--line); text-align:left; font-size:.9rem; }
        th { color:var(--blue); font-size:.75rem; text-transform:uppercase; letter-spacing:.04em; background:#f3f8fd; }
        th:first-child { border-radius:var(--radius) 0 0 var(--radius); }
        th:last-child { border-radius:0 var(--radius) var(--radius) 0; }
        tr:hover td { background:#fbfdff; }
        .pill { display:inline-flex; border-radius:999px; padding:.36rem .58rem; background:var(--soft); color:var(--blue); font-size:.75rem; font-weight:900; }
        .pill.ok-google { background:#d1fae5; color:#047857; }
        .pill.pending-google { background:#fff7cc; color:#7c5800; }
        .ok, .error { margin-bottom:.8rem; border-radius:var(--radius); padding:.75rem; font-weight:750; }
        .ok { color:#047857; background:#d1fae5; border:1px solid #a7f3d0; }
        .error { color:#b91c1c; background:#fee2e2; border:1px solid #fecaca; }
        @media (max-width:820px) { body { background:linear-gradient(180deg,#061b39 0 390px,#eef5fb 390px,#f7faff 100%); } .topbar, .grid { grid-template-columns:1fr; flex-direction:column; align-items:stretch; } table, tbody, tr, td { display:block; min-width:0; } thead { display:none; } tr { border:1px solid var(--line); border-radius:var(--radius); margin-bottom:.75rem; background:#fff; overflow:hidden; } td { border:0; } td + td { border-top:1px solid rgba(6,57,112,.08); } }
    </style>
</head>
<body>
    <div class="shell">
        <header class="topbar">
            <a href="index.html"><img src="img/cda-logo-f.svg" alt="Central de Alarmas"></a>
            <nav class="nav" aria-label="Navegacion">
                <a href="panel-marketing.php">Tickets</a>
                <a href="control-marketing.php">Tablero</a>
                <a class="active" href="usuarios-marketing.php">Usuarios</a>
                <a href="marketing.html">Crear ticket</a>
                <a href="logout.php">Salir</a>
            </nav>
        </header>
        <section class="hero">
            <div class="eyebrow">Facultades administrativas</div>
            <h1>Usuarios autorizados</h1>
            <p class="muted">Administra accesos registrados por contrasena o Google, cambia roles y desactiva usuarios cuando sea necesario.</p>
        </section>
        <section class="grid">
            <article class="card">
                <div class="card-head">
                    <h2>Crear o actualizar acceso</h2>
                    <p class="muted">Los usuarios también se registran al crear tickets o entrar con Google.</p>
                </div>
                <?php if ($message): ?><div class="ok"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
                <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                <form method="post" action="usuarios-marketing.php">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(cdaCsrfToken()); ?>">
                    <input type="hidden" name="action" value="save">
                    <label>Nombre <input name="nombre" required></label>
                    <label>Correo autorizado <input name="correo" type="email" required></label>
                    <label>Contrasena inicial <input name="password" type="password" minlength="8" autocomplete="new-password" placeholder="Opcional si usara Google"></label>
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
                <div class="card-head">
                    <h2>Directorio de accesos</h2>
                    <p class="muted">Revisa estado, metodo de acceso y acciones disponibles.</p>
                </div>
                <div class="table-shell">
                    <table>
                        <thead><tr><th>Nombre</th><th>Correo</th><th>Rol</th><th>Google</th><th>Estado</th><th>Facultad admin</th></tr></thead>
                        <tbody>
                        <?php foreach ($users as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($item['correo']); ?></td>
                            <td><span class="pill"><?php echo htmlspecialchars($item['rol']); ?></span></td>
                            <td>
                                <?php if (!empty($item['google_sub'])): ?>
                                    <span class="pill ok-google">Vinculado</span>
                                <?php else: ?>
                                    <span class="pill pending-google">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo (int) $item['activo'] === 1 ? 'Activo' : 'Inactivo'; ?></td>
                            <td>
                                <?php if ((int) $item['id'] === (int) $user['id']): ?>
                                    <span class="muted">Tu usuario</span>
                                <?php else: ?>
                                    <form class="row-action" method="post" action="usuarios-marketing.php" onsubmit="return confirm('Eliminar este usuario del panel?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(cdaCsrfToken()); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?php echo (int) $item['id']; ?>">
                                        <button class="danger-button" type="submit">Eliminar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </article>
        </section>
    </div>
</body>
</html>
