<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/marketing_helpers.php';

$user = cdaRequireLogin();
$fixedRole = cdaMarketingFixedRoleByEmail($user['correo'] ?? '');
if ($fixedRole === 'admin' && $user['rol'] !== 'admin') {
    cdaMarketingEnsureUserRoleSchema();
    cdaMarketingEnforceFixedUserRoles();
    $user = cdaCurrentUser();
}

if ($user['rol'] !== 'admin') {
    header('Location: panel-marketing.php');
    exit;
}

cdaMarketingEnsureUserRoleSchema();
cdaMarketingEnforceFixedUserRoles();
$user = cdaCurrentUser();
if (!$user || $user['rol'] !== 'admin') {
    header('Location: panel-marketing.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cdaRequirePostCsrf();
    $action = $_GET['action'] ?? $_POST['action'] ?? 'save';

    if ($action === 'delete') {
        $deleteId = (int) ($_POST['user_id'] ?? 0);

        if ($deleteId <= 0) {
            $error = 'Usuario no valido.';
        } elseif ($deleteId === (int) $user['id']) {
            $error = 'No puedes eliminar tu propio usuario.';
        } else {
            try {
                $db = cdaDb();
                $target = $db->prepare('SELECT id, correo, rol, activo FROM marketing_usuarios WHERE id = ? LIMIT 1');
                $target->execute([$deleteId]);
                $targetUser = $target->fetch();

                if (!$targetUser) {
                    $error = 'El usuario no existe.';
                } elseif (cdaMarketingProtectedUserEmail($targetUser['correo'] ?? '')) {
                    $error = 'Este usuario tiene rol protegido por correo y no se puede eliminar desde el panel.';
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
    } elseif ($action === 'bulk_save') {
        $rows = is_array($_POST['users'] ?? null) ? $_POST['users'] : [];
        $updated = 0;

        try {
            $db = cdaDb();
            $db->beginTransaction();
            $targetStmt = $db->prepare('SELECT id, correo FROM marketing_usuarios WHERE id = ? LIMIT 1');

            foreach ($rows as $rowId => $row) {
                $editId = (int) $rowId;
                if ($editId <= 0 || !is_array($row)) {
                    continue;
                }
                $targetStmt->execute([$editId]);
                $existingUser = $targetStmt->fetch();
                if (!$existingUser) {
                    continue;
                }

                $nombre = cdaMarketingClean($row['nombre'] ?? '');
                $correo = filter_var(strtolower(cdaMarketingClean($row['correo'] ?? '')), FILTER_VALIDATE_EMAIL);
                $password = (string) ($row['password'] ?? '');
                $rol = cdaMarketingUserRoleValue($correo ?: '', $row['rol'] ?? 'usuario');
                $activo = !empty($row['activo']) ? 1 : 0;

                if (cdaMarketingProtectedUserEmail($existingUser['correo'] ?? '')) {
                    $correo = strtolower($existingUser['correo']);
                    $rol = cdaMarketingDefaultUserRole($correo);
                    $activo = 1;
                }

                if (!$nombre || !$correo) {
                    throw new RuntimeException('invalid_user');
                }

                if ($password !== '' && strlen($password) < 8) {
                    throw new RuntimeException('weak_password');
                }

                if (cdaMarketingProtectedUserEmail($correo)) {
                    $activo = 1;
                }

                if ($editId === (int) $user['id'] && ($rol !== 'admin' || $activo !== 1)) {
                    throw new RuntimeException('self_admin');
                }

                if ($password !== '') {
                    $stmt = $db->prepare('UPDATE marketing_usuarios SET nombre = ?, correo = ?, rol = ?, activo = ?, password_hash = ? WHERE id = ?');
                    $stmt->execute([$nombre, $correo, $rol, $activo, password_hash($password, PASSWORD_DEFAULT), $editId]);
                } else {
                    $stmt = $db->prepare('UPDATE marketing_usuarios SET nombre = ?, correo = ?, rol = ?, activo = ? WHERE id = ?');
                    $stmt->execute([$nombre, $correo, $rol, $activo, $editId]);
                }
                $updated++;
            }

            $db->commit();
            $message = 'Cambios guardados para ' . $updated . ' usuarios.';
            $user = cdaCurrentUser();
        } catch (Throwable $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            $error = match ($e->getMessage()) {
                'weak_password' => 'Las contrasenas nuevas deben tener al menos 8 caracteres.',
                'self_admin' => 'No puedes quitarte el rol administrador ni desactivar tu propio usuario.',
                'invalid_user' => 'Todos los usuarios deben tener nombre y correo valido.',
                default => 'No fue posible guardar todos los usuarios. Si cambiaste roles, revisa que la base tenga los roles manager y trabajador instalados.',
            };
        }
    } else {
        $editId = (int) ($_POST['user_id'] ?? 0);
        $nombre = cdaMarketingClean($_POST['nombre'] ?? '');
        $correo = filter_var(strtolower(cdaMarketingClean($_POST['correo'] ?? '')), FILTER_VALIDATE_EMAIL);
        $password = (string) ($_POST['password'] ?? '');
        $rol = cdaMarketingUserRoleValue($correo ?: '', $_POST['rol'] ?? 'usuario');
        $activo = !empty($_POST['activo']) ? 1 : 0;
        if ($correo && cdaMarketingProtectedUserEmail($correo)) {
            $activo = 1;
        }

        if (!$nombre || !$correo) {
            $error = 'Nombre y correo son obligatorios.';
        } elseif ($password !== '' && strlen($password) < 8) {
            $error = 'La contrasena debe tener al menos 8 caracteres.';
        } elseif ($editId === (int) $user['id'] && ($rol !== 'admin' || $activo !== 1)) {
            $error = 'No puedes quitarte el rol administrador ni desactivar tu propio usuario.';
        } else {
            try {
                if ($editId > 0) {
                    $target = cdaDb()->prepare('SELECT id, correo, rol, activo FROM marketing_usuarios WHERE id = ? LIMIT 1');
                    $target->execute([$editId]);
                    $targetUser = $target->fetch();

                    if (!$targetUser) {
                        throw new RuntimeException('missing_user');
                    }

                    if (cdaMarketingProtectedUserEmail($targetUser['correo'] ?? '')) {
                        $correo = strtolower($targetUser['correo']);
                        $rol = cdaMarketingDefaultUserRole($correo);
                        $activo = 1;
                    }

                    if ($targetUser['rol'] === 'admin' && (int) $targetUser['activo'] === 1 && ($rol !== 'admin' || $activo !== 1)) {
                        $adminCount = (int) cdaDb()->query("SELECT COUNT(*) FROM marketing_usuarios WHERE rol = 'admin' AND activo = 1")->fetchColumn();
                        if ($adminCount <= 1) {
                            throw new RuntimeException('last_admin');
                        }
                    }

                    if ($password !== '') {
                        $stmt = cdaDb()->prepare('UPDATE marketing_usuarios SET nombre = ?, correo = ?, rol = ?, activo = ?, password_hash = ? WHERE id = ?');
                        $stmt->execute([$nombre, $correo, $rol, $activo, password_hash($password, PASSWORD_DEFAULT), $editId]);
                    } else {
                        $stmt = cdaDb()->prepare('UPDATE marketing_usuarios SET nombre = ?, correo = ?, rol = ?, activo = ? WHERE id = ?');
                        $stmt->execute([$nombre, $correo, $rol, $activo, $editId]);
                    }

                    $message = 'Usuario actualizado correctamente.';
                    $user = cdaCurrentUser();
                } else {
                    $hash = $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : null;
                    $stmt = cdaDb()->prepare(
                        'INSERT INTO marketing_usuarios (nombre, correo, password_hash, rol, activo)
                        VALUES (?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), rol = VALUES(rol), activo = VALUES(activo), password_hash = COALESCE(VALUES(password_hash), password_hash)'
                    );
                    $stmt->execute([$nombre, $correo, $hash, $rol, $activo]);
                    $message = 'Usuario guardado correctamente.';
                }
            } catch (Throwable $e) {
                $error = $e->getMessage() === 'last_admin'
                    ? 'No puedes quitar al ultimo administrador activo.'
                    : 'No fue posible guardar el usuario. Si cambiaste roles, revisa que la base tenga los roles manager y trabajador instalados.';
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
    <title>Usuarios autorizados | Marketing</title>
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
                radial-gradient(circle at 18% 18%, rgba(246,235,23,.2), transparent 23rem),
                radial-gradient(circle at 82% 24%, rgba(71,151,255,.2), transparent 25rem),
                linear-gradient(135deg,#061226,#063970 58%,#031025);
            overflow-x:hidden;
        }
        body::before {
            content:"";
            position:fixed;
            inset:0;
            pointer-events:none;
            background:linear-gradient(120deg, transparent 0 44%, rgba(255,255,255,.09) 45%, transparent 48% 100%);
            opacity:.45;
            z-index:0;
        }
        .shell { width:min(1180px, calc(100% - 2rem)); margin:0 auto; padding:1.1rem 0 3rem; position:relative; z-index:2; }
        .ambient-points { position:fixed; inset:0; overflow:hidden; pointer-events:none; z-index:1; }
        .ambient-point { --size:2px; --x:50vw; --y:50vh; --dx:18px; --dy:-22px; --duration:18s; --delay:0s; position:absolute; left:var(--x); top:var(--y); width:var(--size); height:var(--size); border-radius:50%; background:rgba(255,255,255,.55); box-shadow:0 0 calc(var(--size) * 4) rgba(166,205,255,.28); opacity:.42; transform:translate3d(0,0,0); animation:ambientDrift var(--duration) ease-in-out var(--delay) infinite alternate; }
        @keyframes ambientDrift { 0% { transform:translate3d(0,0,0); opacity:.18; } 38% { opacity:.56; } 100% { transform:translate3d(var(--dx), var(--dy), 0); opacity:.32; } }
        .topbar { display:flex; align-items:center; justify-content:space-between; gap:1rem; min-height:64px; margin-bottom:1rem; color:#fff; }
        .topbar img { width:140px; display:block; filter:drop-shadow(0 10px 18px rgba(0,0,0,.18)); }
        .nav { display:flex; flex-wrap:wrap; gap:.55rem; align-items:center; justify-content:flex-end; }
        .nav a { min-height:40px; display:inline-flex; align-items:center; color:rgba(255,255,255,.86); text-decoration:none; border:1px solid rgba(255,255,255,.2); border-radius:8px; padding:.62rem .78rem; background:rgba(255,255,255,.08); font-size:.8rem; font-weight:850; white-space:nowrap; transition:background .18s ease, color .18s ease, border-color .18s ease, box-shadow .18s ease; }
        .nav a:hover { background:rgba(255,255,255,.15); color:#fff; border-color:rgba(255,255,255,.34); }
        .nav a.active { background:rgba(255,255,255,.18); color:#fff; border-color:rgba(246,235,23,.48); box-shadow:none; }
        .nav a:focus-visible { outline:3px solid rgba(246,235,23,.52); outline-offset:2px; }
        .profile-menu { position:relative; }
        .profile-menu summary { min-height:40px; display:inline-flex; align-items:center; gap:.5rem; list-style:none; border:1px solid rgba(255,255,255,.22); border-radius:8px; padding:.62rem .78rem; color:#fff; background:rgba(255,255,255,.1); font-size:.8rem; font-weight:850; cursor:pointer; }
        .profile-menu summary::-webkit-details-marker { display:none; }
        .profile-menu summary::after { content:""; width:.45rem; height:.45rem; border-right:2px solid currentColor; border-bottom:2px solid currentColor; transform:rotate(45deg) translateY(-2px); opacity:.75; }
        .profile-menu[open] summary { background:rgba(255,255,255,.18); border-color:rgba(255,255,255,.36); }
        .profile-menu.role-usuario summary { border-color:rgba(246,235,23,.82); box-shadow:0 0 0 1px rgba(246,235,23,.18); }
        .profile-menu.role-admin summary { border-color:rgba(248,113,113,.9); box-shadow:0 0 0 1px rgba(248,113,113,.2); }
        .profile-menu.role-trabajador summary { border-color:rgba(34,197,94,.88); box-shadow:0 0 0 1px rgba(34,197,94,.18); }
        .profile-menu.role-manager summary { border-color:rgba(96,165,250,.88); box-shadow:0 0 0 1px rgba(96,165,250,.18); }
        .profile-dropdown { position:absolute; right:0; top:calc(100% + .45rem); z-index:10; display:grid; min-width:190px; padding:.45rem; border:1px solid rgba(6,57,112,.12); border-radius:8px; background:#fff; box-shadow:0 18px 40px rgba(0,0,0,.18); }
        .profile-dropdown a { min-height:36px; justify-content:flex-start; border:0; background:#fff; color:var(--ink); box-shadow:none; }
        .profile-dropdown a:hover { background:var(--soft); color:var(--blue); border-color:transparent; }
        .profile-dropdown a.logout-link { color:#991b1b; }
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
        .user-edit { display:grid; grid-template-columns:minmax(130px,1fr) minmax(180px,1.1fr) 132px 96px minmax(140px,.8fr); gap:.45rem; align-items:center; }
        .user-edit input, .user-edit select { padding:.62rem .68rem; font-size:.78rem; }
        .user-edit .check { justify-content:center; }
        .bulk-actions { position:sticky; bottom:1rem; z-index:3; display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-top:.85rem; padding:.8rem; border:1px solid rgba(6,57,112,.12); border-radius:var(--radius); background:rgba(255,255,255,.96); box-shadow:0 14px 34px rgba(6,57,112,.14); }
        .bulk-actions span { color:var(--muted); font-size:.82rem; font-weight:750; }
        .bulk-actions button { min-width:190px; }
        .row-tools { display:flex; gap:.45rem; align-items:center; justify-content:flex-start; }
        .table-shell { overflow:auto; border:1px solid rgba(6,57,112,.08); border-radius:var(--radius); background:#fff; }
        table { width:100%; min-width:980px; border-collapse:separate; border-spacing:0; }
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
        @media (max-width:820px) { .topbar, .grid { grid-template-columns:1fr; flex-direction:column; align-items:stretch; } .nav { justify-content:flex-start; } .profile-dropdown { left:0; right:auto; } table, tbody, tr, td { display:block; min-width:0; } thead { display:none; } tr { border:1px solid var(--line); border-radius:var(--radius); margin-bottom:.75rem; background:#fff; overflow:hidden; } td { border:0; } td + td { border-top:1px solid rgba(6,57,112,.08); } .user-edit { grid-template-columns:1fr; } .user-edit .check { justify-content:flex-start; } .bulk-actions { position:static; align-items:stretch; flex-direction:column; } .bulk-actions button { width:100%; } }
        @media (prefers-reduced-motion:reduce) { .ambient-point { animation:none; } }
    </style>
</head>
<body>
    <div class="ambient-points" aria-hidden="true"></div>
    <div class="shell">
        <header class="topbar">
            <a href="index.html"><img src="img/cda-logo-f.svg" alt="Central de Alarmas"></a>
            <nav class="nav" aria-label="Navegacion">
                <a class="admin-link" href="estadisticas-marketing.php">Estadísticas</a>
                <a class="admin-link" href="panel-marketing.php">Tickets</a>
                <a class="admin-link" href="control-marketing.php">Tablero</a>
                <a class="admin-link active" href="usuarios-marketing.php">Usuarios</a>
                <a class="admin-link" href="panel-marketing.php?papelera=1">Basurero</a>
                <a class="public-link" href="crear-ticket.php">Crear ticket</a>
                <a class="public-link" href="seguimiento.php">Seguimiento</a>
                <details class="profile-menu role-<?php echo htmlspecialchars(cdaMarketingRoleClass($user['rol'])); ?>">
                    <summary><?php echo htmlspecialchars($user['nombre']); ?> · <?php echo htmlspecialchars(cdaMarketingRoleLabel($user['rol'])); ?></summary>
                    <div class="profile-dropdown">
                        <a href="perfil-marketing.php">Mi perfil</a>
                        <a href="perfil-marketing.php#configuracion">Configuración</a>
                        <a class="logout-link" href="logout.php">Cerrar sesión</a>
                    </div>
                </details>
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
                <form method="post" action="usuarios-marketing.php">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(cdaCsrfToken()); ?>">
                    <input type="hidden" name="action" value="save">
                    <label>Nombre <input name="nombre" required></label>
                    <label>Correo autorizado <input name="correo" type="email" required></label>
                    <label>Contrasena inicial <input name="password" type="password" minlength="8" autocomplete="new-password" placeholder="Opcional si usara Google"></label>
                    <label>Rol
                        <select name="rol">
                            <option value="usuario">Usuario</option>
                            <option value="manager">Manager</option>
                            <option value="trabajador">Trabajador</option>
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
                <?php if ($message): ?><div class="ok"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
                <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                <form method="post" action="usuarios-marketing.php">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(cdaCsrfToken()); ?>">
                    <input type="hidden" name="action" value="bulk_save">
                    <div class="table-shell">
                        <table>
                            <thead><tr><th>Gestionar usuario</th><th>Google</th><th>Creado</th><th>Acciones</th></tr></thead>
                            <tbody>
                            <?php foreach ($users as $item): ?>
                            <?php
                                $itemEmail = strtolower((string) $item['correo']);
                                $fixedItemRole = cdaMarketingFixedRoleByEmail($itemEmail);
                                $isProtected = $fixedItemRole !== null;
                                $rowRole = cdaMarketingUserRoleValue($itemEmail, $item['rol'] ?? 'usuario');
                            ?>
                            <tr>
                                <td>
                                    <div class="user-edit">
                                        <input name="users[<?php echo (int) $item['id']; ?>][nombre]" value="<?php echo htmlspecialchars($item['nombre']); ?>" required aria-label="Nombre">
                                        <input name="users[<?php echo (int) $item['id']; ?>][correo]" type="email" value="<?php echo htmlspecialchars($item['correo']); ?>" required aria-label="Correo">
                                        <select name="users[<?php echo (int) $item['id']; ?>][rol]" aria-label="Rol" <?php echo $isProtected ? 'disabled' : ''; ?>>
                                            <?php if ($fixedItemRole === 'admin'): ?>
                                                <option value="admin" selected>Administrador</option>
                                            <?php endif; ?>
                                            <option value="usuario" <?php echo $rowRole === 'usuario' ? 'selected' : ''; ?>>Usuario</option>
                                            <option value="manager" <?php echo $rowRole === 'manager' ? 'selected' : ''; ?>>Manager</option>
                                            <option value="trabajador" <?php echo $rowRole === 'trabajador' ? 'selected' : ''; ?>>Trabajador</option>
                                        </select>
                                        <label class="check"><input name="users[<?php echo (int) $item['id']; ?>][activo]" type="checkbox" value="1" <?php echo (int) $item['activo'] === 1 ? 'checked' : ''; ?> <?php echo $isProtected ? 'disabled' : ''; ?>> Activo</label>
                                        <input name="users[<?php echo (int) $item['id']; ?>][password]" type="password" minlength="8" autocomplete="new-password" placeholder="Nueva contraseña">
                                    </div>
                                    <?php if ($isProtected): ?><span class="pill">Rol protegido por correo</span><?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($item['google_sub'])): ?>
                                        <span class="pill ok-google">Vinculado</span>
                                    <?php else: ?>
                                        <span class="pill pending-google">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($item['creado_en']))); ?></td>
                                <td>
                                    <?php if ((int) $item['id'] === (int) $user['id']): ?>
                                        <span class="muted">Tu usuario</span>
                                    <?php elseif ($isProtected): ?>
                                        <span class="muted">Protegido</span>
                                    <?php else: ?>
                                        <button class="danger-button" type="submit" name="user_id" value="<?php echo (int) $item['id']; ?>" formaction="usuarios-marketing.php?action=delete" formmethod="post" onclick="return confirm('Eliminar este usuario del panel?');">Eliminar</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="bulk-actions">
                        <span>Guarda los cambios de rol, estado y contraseña del directorio completo.</span>
                        <button type="submit">Guardar todo</button>
                    </div>
                </form>
            </article>
        </section>
    </div>
    <script>
        (function () {
            var layer = document.querySelector('.ambient-points');
            if (!layer) return;
            var count = window.matchMedia('(max-width: 620px)').matches ? 54 : 86;
            var fragment = document.createDocumentFragment();
            for (var i = 0; i < count; i += 1) {
                var point = document.createElement('span');
                point.className = 'ambient-point';
                point.style.setProperty('--size', (Math.random() * 2.8 + .9).toFixed(2) + 'px');
                point.style.setProperty('--x', (Math.random() * 100).toFixed(2) + 'vw');
                point.style.setProperty('--y', (Math.random() * 100).toFixed(2) + 'vh');
                point.style.setProperty('--dx', (Math.random() * 72 - 36).toFixed(2) + 'px');
                point.style.setProperty('--dy', (Math.random() * 72 - 36).toFixed(2) + 'px');
                point.style.setProperty('--duration', (Math.random() * 18 + 16).toFixed(2) + 's');
                point.style.setProperty('--delay', (Math.random() * -24).toFixed(2) + 's');
                point.style.opacity = (Math.random() * .32 + .18).toFixed(2);
                fragment.appendChild(point);
            }
            layer.appendChild(fragment);
        }());
    </script>
</body>
</html>
