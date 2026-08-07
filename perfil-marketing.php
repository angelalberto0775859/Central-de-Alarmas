<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/marketing_helpers.php';

$user = cdaRequireLogin();
$message = '';
$error = '';

$dbUserStmt = cdaDb()->prepare('SELECT id, nombre, correo, password_hash, google_sub, rol, activo, creado_en FROM marketing_usuarios WHERE id = ? LIMIT 1');
$dbUserStmt->execute([$user['id']]);
$fullUser = $dbUserStmt->fetch() ?: $user;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cdaRequirePostCsrf();

    $nombre = cdaMarketingClean($_POST['nombre'] ?? '');
    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['password_confirm'] ?? '');

    if ($nombre === '') {
        $error = 'Escribe tu nombre para actualizar el perfil.';
    } elseif ($newPassword !== '' || $currentPassword !== '') {
        if (!empty($fullUser['password_hash']) && !password_verify($currentPassword, $fullUser['password_hash'])) {
            $error = 'La contraseña actual es incorrecta.';
        } elseif (strlen($newPassword) < 8) {
            $error = 'La nueva contraseña debe tener al menos 8 caracteres.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'La confirmación de la contraseña no coincide.';
        }
    }

    if (!$error) {
        try {
            if ($newPassword !== '') {
                $stmt = cdaDb()->prepare('UPDATE marketing_usuarios SET nombre = ?, password_hash = ? WHERE id = ?');
                $stmt->execute([$nombre, password_hash($newPassword, PASSWORD_DEFAULT), $user['id']]);
            } else {
                $stmt = cdaDb()->prepare('UPDATE marketing_usuarios SET nombre = ? WHERE id = ?');
                $stmt->execute([$nombre, $user['id']]);
            }

            $user = cdaCurrentUser();
            $dbUserStmt->execute([$user['id']]);
            $fullUser = $dbUserStmt->fetch() ?: $user;
            $message = 'Perfil y configuración actualizados correctamente.';
        } catch (Throwable $e) {
            $error = 'No fue posible actualizar tu perfil.';
        }
    }
}

// User Ticket Statistics
$statsStmt = cdaDb()->prepare("
    SELECT
        COUNT(*) AS total,
        COALESCE(SUM(CASE WHEN estado NOT IN ('Entregado','Cerrado','Rechazado') THEN 1 ELSE 0 END), 0) AS activas,
        COALESCE(SUM(CASE WHEN estado IN ('Entregado','Cerrado') THEN 1 ELSE 0 END), 0) AS finalizadas,
        COALESCE(SUM(CASE WHEN prioridad = 'Urgente' AND estado NOT IN ('Entregado','Cerrado','Rechazado') THEN 1 ELSE 0 END), 0) AS urgentes
    FROM marketing_tickets
    WHERE correo = ? AND eliminado_en IS NULL
");
$statsStmt->execute([$user['correo']]);
$stats = $statsStmt->fetch() ?: ['total' => 0, 'activas' => 0, 'finalizadas' => 0, 'urgentes' => 0];

// User Tickets List
$ticketStmt = cdaDb()->prepare("
    SELECT id, folio, actividad, departamento, tipo_solicitud, estado, prioridad, fecha_requerida, fecha_entrega_estimada, creado_en, actualizado_en
    FROM marketing_tickets
    WHERE correo = ? AND eliminado_en IS NULL
    ORDER BY actualizado_en DESC
    LIMIT 30
");
$ticketStmt->execute([$user['correo']]);
$tickets = $ticketStmt->fetchAll();

// Role Descriptions
$roleDescriptions = [
    'admin' => 'Administrador · Tienes acceso total al panel, gestión de tickets, tablero Kanban, reportes y administración de usuarios.',
    'manager' => 'Coordinador / Manager · Tienes acceso a la gestión de tickets, asignación de responsables, tablero Kanban y estadísticas.',
    'trabajador' => 'Equipo de Marketing · Tienes acceso al tablero Kanban de producción y atención de solicitudes asignadas.',
    'usuario' => 'Solicitante · Puedes crear solicitudes de marketing, adjuntar archivos y dar seguimiento a tus tickets.'
];
$roleDesc = $roleDescriptions[$user['rol']] ?? $roleDescriptions['usuario'];

// Generate User Initials
$initials = '';
foreach (explode(' ', trim($user['nombre'])) as $w) {
    if ($w !== '') {
        $initials .= mb_substr($w, 0, 1);
    }
}
$initials = strtoupper(mb_substr($initials, 0, 2)) ?: 'U';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil y Configuración | Central de Alarmas Marketing</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="/contigo/Img/favicon.svg">
    <style>
        :root {
            --blue: #063970;
            --blue-2: #0b4f92;
            --blue-dark: #031833;
            --ink: #10213f;
            --muted: #66758d;
            --line: rgba(6, 57, 112, .14);
            --soft: #f4f8fc;
            --yellow: #f6eb17;
            --radius: 12px;
            --shadow: 0 18px 46px rgba(6, 57, 112, .12);
            --green: #047857;
            --red: #b91c1c;
            --orange: #b45309;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, Arial, sans-serif;
            color: var(--ink);
            background: linear-gradient(135deg, #040e1e, #063970 60%, #020c1a);
            background-attachment: fixed;
        }
        .shell {
            width: min(1140px, calc(100% - 2rem));
            margin: 0 auto;
            padding: 1.1rem 0 3rem;
        }
        /* Top Navigation Header */
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            min-height: 64px;
            margin-bottom: 1.25rem;
            color: #fff;
        }
        .topbar img {
            width: 140px;
            display: block;
            filter: drop-shadow(0 10px 18px rgba(0,0,0,.18));
        }
        .nav {
            display: flex;
            flex-wrap: wrap;
            gap: .55rem;
            align-items: center;
            justify-content: flex-end;
        }
        .nav a {
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            color: rgba(255,255,255,.86);
            text-decoration: none;
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 8px;
            padding: .62rem .78rem;
            background: rgba(255,255,255,.08);
            font-size: .8rem;
            font-weight: 850;
            transition: all .15s ease;
        }
        .nav a:hover {
            background: rgba(255,255,255,.15);
            color: #fff;
            border-color: rgba(255,255,255,.34);
        }
        .nav a.active {
            background: rgba(255,255,255,.18);
            color: #fff;
            border-color: rgba(246,235,23,.48);
        }
        .profile-menu { position: relative; }
        .profile-menu summary {
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            list-style: none;
            border: 1px solid rgba(255,255,255,.22);
            border-radius: 8px;
            padding: .62rem .78rem;
            color: #fff;
            background: rgba(255,255,255,.1);
            font-size: .8rem;
            font-weight: 850;
            cursor: pointer;
            transition: all .15s ease;
        }
        .profile-menu summary::-webkit-details-marker { display: none; }
        .profile-menu summary::after {
            content: "";
            width: .45rem;
            height: .45rem;
            border-right: 2px solid currentColor;
            border-bottom: 2px solid currentColor;
            transform: rotate(45deg) translateY(-2px);
            opacity: .75;
        }
        .profile-menu[open] summary { background: rgba(255,255,255,.18); border-color: rgba(255,255,255,.36); }
        .profile-menu.role-usuario summary { border-color: rgba(246,235,23,.82); box-shadow: 0 0 0 1px rgba(246,235,23,.18); }
        .profile-menu.role-admin summary { border-color: rgba(248,113,113,.9); box-shadow: 0 0 0 1px rgba(248,113,113,.2); }
        .profile-menu.role-trabajador summary { border-color: rgba(34,197,94,.88); box-shadow: 0 0 0 1px rgba(34,197,94,.18); }
        .profile-menu.role-manager summary { border-color: rgba(96,165,250,.88); box-shadow: 0 0 0 1px rgba(96,165,250,.18); }
        .profile-dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + .45rem);
            z-index: 20;
            display: grid;
            min-width: 200px;
            padding: .45rem;
            border: 1px solid rgba(6,57,112,.12);
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 18px 40px rgba(0,0,0,.22);
        }
        .profile-dropdown a {
            min-height: 38px;
            justify-content: flex-start;
            border: 0;
            background: #fff;
            color: var(--ink);
            box-shadow: none;
        }
        .profile-dropdown a:hover { background: var(--soft); color: var(--blue); border-color: transparent; }
        .profile-dropdown a.logout-link { color: #991b1b; }

        /* Profile Hero Banner */
        .hero-banner {
            margin-bottom: 1.25rem;
            color: #fff;
            border: 1px solid rgba(255,255,255,.18);
            border-radius: var(--radius);
            padding: 1.5rem;
            background: linear-gradient(135deg, rgba(255,255,255,.14), rgba(255,255,255,.05));
            backdrop-filter: blur(12px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        .user-identity {
            display: flex;
            align-items: center;
            gap: 1.2rem;
        }
        .avatar-circle {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--yellow), #eab308);
            color: var(--blue-dark);
            font-size: 1.6rem;
            font-weight: 900;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(0,0,0,.25);
            border: 3px solid rgba(255,255,255,.3);
            flex-shrink: 0;
        }
        .user-titles h1 {
            font-size: clamp(1.5rem, 3.5vw, 2.2rem);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: .3rem;
            color: #fff;
        }
        .user-meta {
            display: flex;
            align-items: center;
            gap: .65rem;
            flex-wrap: wrap;
        }
        .user-email {
            font-size: .88rem;
            color: rgba(255,255,255,.82);
        }
        .role-pill {
            display: inline-flex;
            align-items: center;
            padding: .22rem .65rem;
            border-radius: 20px;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .03em;
            text-transform: uppercase;
            background: rgba(246,235,23,.22);
            color: var(--yellow);
            border: 1px solid rgba(246,235,23,.45);
        }
        .role-pill.role-admin { background: rgba(239,68,68,.25); color: #fca5a5; border-color: rgba(239,68,68,.4); }
        .role-pill.role-manager { background: rgba(59,130,246,.25); color: #93c5fd; border-color: rgba(59,130,246,.4); }
        .role-pill.role-trabajador { background: rgba(34,197,94,.25); color: #86efac; border-color: rgba(34,197,94,.4); }

        .hero-stats {
            display: flex;
            gap: .8rem;
            flex-wrap: wrap;
        }
        .stat-box {
            background: rgba(0, 0, 0, .22);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 10px;
            padding: .7rem 1rem;
            min-width: 110px;
            text-align: center;
        }
        .stat-box .num {
            font-size: 1.4rem;
            font-weight: 900;
            color: var(--yellow);
            line-height: 1;
        }
        .stat-box .lbl {
            font-size: .7rem;
            font-weight: 750;
            color: rgba(255,255,255,.75);
            text-transform: uppercase;
            margin-top: .3rem;
        }

        /* Tabs Nav Header */
        .tabs-header {
            display: flex;
            gap: .5rem;
            margin-bottom: 1.2rem;
            border-bottom: 1px solid rgba(255,255,255,.15);
            padding-bottom: .6rem;
            overflow-x: auto;
        }
        .tab-btn {
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.14);
            border-radius: 8px;
            color: rgba(255,255,255,.8);
            padding: .65rem 1.1rem;
            font-size: .84rem;
            font-weight: 800;
            cursor: pointer;
            transition: all .2s ease;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
        }
        .tab-btn:hover {
            background: rgba(255,255,255,.12);
            color: #fff;
        }
        .tab-btn.active {
            background: var(--yellow);
            color: var(--blue-dark);
            border-color: var(--yellow);
            box-shadow: 0 4px 14px rgba(246,235,23,.25);
        }

        /* Card container */
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .card {
            background: rgba(255,255,255,.98);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 1.4rem;
            box-shadow: var(--shadow);
            margin-bottom: 1.25rem;
        }
        .card h2 {
            color: var(--blue);
            font-size: 1.15rem;
            font-weight: 800;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        /* Grid layout for two columns */
        .two-cols {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 1.25rem;
        }
        @media (max-width: 840px) {
            .two-cols { grid-template-columns: 1fr; }
        }

        /* Data list styling */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: .8rem;
            margin-bottom: 1rem;
        }
        .info-item {
            border: 1px solid rgba(6,57,112,.08);
            border-radius: 8px;
            background: var(--soft);
            padding: .75rem .9rem;
        }
        .info-item label {
            display: block;
            color: var(--muted);
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: .25rem;
        }
        .info-item span {
            font-size: .95rem;
            font-weight: 800;
            color: var(--ink);
            word-break: break-word;
        }

        .role-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: .9rem 1.1rem;
            color: #1e40af;
            font-size: .86rem;
            line-height: 1.45;
            margin-bottom: 1rem;
        }

        /* Alert notifications */
        .ok, .error {
            margin-bottom: 1rem;
            border-radius: 8px;
            padding: .85rem 1rem;
            font-weight: 750;
            font-size: .88rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .ok { color: #047857; background: #d1fae5; border: 1px solid #a7f3d0; }
        .error { color: #b91c1c; background: #fee2e2; border: 1px solid #fecaca; }

        /* Form Controls */
        .form-group {
            margin-bottom: 1.1rem;
        }
        .form-group label {
            display: block;
            font-size: .82rem;
            font-weight: 850;
            color: var(--ink);
            margin-bottom: .38rem;
        }
        .form-group input {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: .8rem .9rem;
            font: inherit;
            background: #fff;
            color: var(--ink);
            outline: none;
            transition: all .15s ease;
        }
        .form-group input:focus {
            border-color: var(--blue-2);
            box-shadow: 0 0 0 4px rgba(6,57,112,.09);
        }
        .form-hint {
            font-size: .75rem;
            color: var(--muted);
            margin-top: .3rem;
        }

        button.btn-primary, .button {
            min-height: 44px;
            border: 0;
            border-radius: 8px;
            padding: .75rem 1.3rem;
            background: var(--yellow);
            color: var(--blue-dark);
            font-weight: 900;
            font-size: .85rem;
            letter-spacing: .03em;
            text-transform: uppercase;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            box-shadow: 0 4px 12px rgba(6,57,112,.15);
            transition: all .15s ease;
        }
        button.btn-primary:hover, .button:hover {
            background: #edd800;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(6,57,112,.2);
        }

        /* Tickets view */
        .ticket-filters {
            display: flex;
            gap: .5rem;
            margin-bottom: 1rem;
        }
        .filter-btn {
            border: 1px solid var(--line);
            background: var(--soft);
            color: var(--muted);
            border-radius: 20px;
            padding: .35rem .85rem;
            font-size: .78rem;
            font-weight: 800;
            cursor: pointer;
        }
        .filter-btn.active {
            background: var(--blue);
            color: #fff;
            border-color: var(--blue);
        }

        .tickets-list {
            display: grid;
            gap: .75rem;
        }
        .ticket-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            border: 1px solid var(--line);
            border-left: 5px solid var(--blue-2);
            border-radius: 10px;
            background: #fff;
            padding: .9rem 1.1rem;
            text-decoration: none;
            color: inherit;
            transition: all .15s ease;
        }
        .ticket-item:hover {
            border-color: var(--blue);
            transform: translateX(3px);
            box-shadow: 0 6px 16px rgba(6,57,112,.08);
        }
        .ticket-main {
            display: grid;
            gap: .25rem;
        }
        .ticket-title {
            font-weight: 850;
            color: var(--blue);
            font-size: .95rem;
        }
        .ticket-sub {
            font-size: .8rem;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: .6rem;
            flex-wrap: wrap;
        }
        .badge {
            display: inline-block;
            padding: .15rem .5rem;
            border-radius: 4px;
            font-size: .72rem;
            font-weight: 850;
            text-transform: uppercase;
        }
        .badge-green { background: #d1fae5; color: #047857; border: 1px solid #a7f3d0; }
        .badge-red { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .badge-yellow { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .badge-blue { background: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .badge-gray { background: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; }

        .empty-state {
            text-align: center;
            padding: 2.5rem 1rem;
            color: var(--muted);
        }
        .empty-state p { margin-bottom: 1rem; font-weight: 600; }

        @media (max-width: 820px) {
            .topbar { flex-direction: column; align-items: stretch; }
            .nav { justify-content: flex-start; }
            .profile-dropdown { left: 0; right: auto; }
            .hero-banner { flex-direction: column; align-items: flex-start; }
            .ticket-item { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <!-- Header Nav Bar -->
        <header class="topbar">
            <a href="index.html"><img src="img/cda-logo-f.svg" alt="Central de Alarmas"></a>
            <nav class="nav" aria-label="Navegación principal">
                <?php if (cdaMarketingCanViewAllTickets($user['rol'])): ?><a href="estadisticas-marketing.php">Estadísticas</a><?php endif; ?>
                <?php if (cdaMarketingCanViewAllTickets($user['rol'])): ?><a href="panel-marketing.php">Tickets</a><?php endif; ?>
                <?php if (cdaMarketingCanAccessBoard($user['rol'])): ?><a href="control-marketing.php">Tablero</a><?php endif; ?>
                <?php if (cdaMarketingCanManageUsers($user['rol'])): ?><a href="usuarios-marketing.php">Usuarios</a><?php endif; ?>
                <?php if (cdaMarketingCanManageTrash($user['rol'])): ?><a href="panel-marketing.php?papelera=1">Basurero</a><?php endif; ?>
                <a href="crear-ticket.php">Crear ticket</a>
                <a href="seguimiento.php">Seguimiento</a>
                <details class="profile-menu role-<?php echo htmlspecialchars(cdaMarketingRoleClass($user['rol'])); ?>" open>
                    <summary><?php echo htmlspecialchars($user['nombre']); ?> · <?php echo htmlspecialchars(cdaMarketingRoleLabel($user['rol'])); ?></summary>
                    <div class="profile-dropdown">
                        <a href="perfil-marketing.php">Mi perfil</a>
                        <a href="perfil-marketing.php#configuracion">Configuración</a>
                        <a class="logout-link" href="logout.php">Cerrar sesión</a>
                    </div>
                </details>
            </nav>
        </header>

        <!-- Profile Hero Banner -->
        <section class="hero-banner">
            <div class="user-identity">
                <div class="avatar-circle"><?php echo htmlspecialchars($initials); ?></div>
                <div class="user-titles">
                    <h1><?php echo htmlspecialchars($user['nombre']); ?></h1>
                    <div class="user-meta">
                        <span class="user-email"><?php echo htmlspecialchars($user['correo']); ?></span>
                        <span class="role-pill role-<?php echo htmlspecialchars(cdaMarketingRoleClass($user['rol'])); ?>">
                            <?php echo htmlspecialchars(cdaMarketingRoleLabel($user['rol'])); ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="hero-stats">
                <div class="stat-box">
                    <div class="num"><?php echo (int)$stats['activas']; ?></div>
                    <div class="lbl">Activas</div>
                </div>
                <div class="stat-box">
                    <div class="num"><?php echo (int)$stats['finalizadas']; ?></div>
                    <div class="lbl">Entregadas</div>
                </div>
                <div class="stat-box">
                    <div class="num"><?php echo (int)$stats['total']; ?></div>
                    <div class="lbl">Total</div>
                </div>
            </div>
        </section>

        <!-- Tabs Navigation -->
        <nav class="tabs-header" aria-label="Secciones de perfil">
            <button class="tab-btn active" data-tab="perfil" type="button">👤 Mi Perfil</button>
            <button class="tab-btn" data-tab="configuracion" type="button">⚙️ Configuración y Seguridad</button>
            <button class="tab-btn" data-tab="tickets" type="button">📋 Mis Solicitudes (<?php echo count($tickets); ?>)</button>
        </nav>

        <?php if ($message): ?>
            <div class="ok" role="alert">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"></path></svg>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error" role="alert">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <!-- Tab 1: Mi Perfil -->
        <section id="tab-perfil" class="tab-content active">
            <div class="two-cols">
                <article class="card">
                    <h2>Datos de la Cuenta</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Nombre de Usuario</label>
                            <span><?php echo htmlspecialchars($user['nombre']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Correo Electrónico</label>
                            <span><?php echo htmlspecialchars($user['correo']); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Rol asignado</label>
                            <span><?php echo htmlspecialchars(cdaMarketingRoleLabel($user['rol'])); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Estado de Cuenta</label>
                            <span style="color:#047857;">● Activa</span>
                        </div>
                        <div class="info-item">
                            <label>Acceso con Google</label>
                            <span><?php echo !empty($fullUser['google_sub']) ? 'Vinculado ✓' : 'No vinculado'; ?></span>
                        </div>
                        <div class="info-item">
                            <label>Fecha de Registro</label>
                            <span><?php echo htmlspecialchars(cdaMarketingFormatDate($fullUser['creado_en'] ?? null)); ?></span>
                        </div>
                    </div>
                </article>

                <article class="card">
                    <h2>Permisos de tu Rol</h2>
                    <div class="role-box">
                        <strong>Nivel de Acceso:</strong> <?php echo htmlspecialchars($roleDesc); ?>
                    </div>
                    <p style="font-size: .84rem; color: var(--muted); line-height: 1.45;">
                        Si requieres cambiar tu rol o permisos para coordinar solicitudes de marketing, comunícate con un administrador del sistema o escribe a soporte.
                    </p>
                </article>
            </div>
        </section>

        <!-- Tab 2: Configuración y Seguridad -->
        <section id="tab-configuracion" class="tab-content">
            <div class="two-cols">
                <article class="card">
                    <h2>Actualizar Nombre y Contraseña</h2>
                    <form id="configuracion" method="post" action="perfil-marketing.php#configuracion">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(cdaCsrfToken()); ?>">
                        
                        <div class="form-group">
                            <label for="input-nombre">Nombre completo</label>
                            <input id="input-nombre" name="nombre" value="<?php echo htmlspecialchars($user['nombre']); ?>" required>
                        </div>

                        <?php if (!empty($fullUser['password_hash'])): ?>
                            <div class="form-group">
                                <label for="input-current-password">Contraseña actual</label>
                                <input id="input-current-password" name="current_password" type="password" autocomplete="current-password" placeholder="Requerida solo si cambias contraseña">
                                <div class="form-hint">Escribe tu contraseña actual para confirmar cambios de contraseña.</div>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="input-password">Nueva contraseña</label>
                            <input id="input-password" name="password" type="password" minlength="8" autocomplete="new-password" placeholder="Dejar en blanco para mantener la actual">
                            <div class="form-hint">Mínimo 8 caracteres.</div>
                        </div>

                        <div class="form-group">
                            <label for="input-confirm">Confirmar nueva contraseña</label>
                            <input id="input-confirm" name="password_confirm" type="password" minlength="8" autocomplete="new-password" placeholder="Repite la nueva contraseña">
                        </div>

                        <button type="submit" class="btn-primary">Guardar perfil</button>
                    </form>
                </article>

                <article class="card">
                    <h2>Seguridad de la Cuenta</h2>
                    <div style="font-size: .86rem; color: var(--ink); line-height: 1.5; display: grid; gap: 1rem;">
                        <div style="background: var(--soft); padding: 1rem; border-radius: 8px; border: 1px solid var(--line);">
                            <strong>🔒 Autenticación Segura</strong>
                            <p style="color: var(--muted); font-size: .8rem; margin-top: .3rem;">
                                Si utilizas Google Workspace o tu correo institucional con Google Login, no necesitas configurar contraseña manual.
                            </p>
                        </div>
                        <div style="background: var(--soft); padding: 1rem; border-radius: 8px; border: 1px solid var(--line);">
                            <strong>🔑 Contraseñas Seguras</strong>
                            <p style="color: var(--muted); font-size: .8rem; margin-top: .3rem;">
                                Utiliza combinaciones de letras, números y caracteres especiales. Nunca compartas tus credenciales de acceso con terceros.
                            </p>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        <!-- Tab 3: Mis Solicitudes -->
        <section id="tab-tickets" class="tab-content">
            <article class="card">
                <h2>Solicitudes de Marketing</h2>

                <div class="ticket-filters">
                    <button type="button" class="filter-btn active" data-filter="all">Todas (<?php echo count($tickets); ?>)</button>
                    <button type="button" class="filter-btn" data-filter="active">Activas (<?php echo (int)$stats['activas']; ?>)</button>
                    <button type="button" class="filter-btn" data-filter="done">Entregadas (<?php echo (int)$stats['finalizadas']; ?>)</button>
                </div>

                <div class="tickets-list">
                    <?php foreach ($tickets as $t): 
                        $tone = cdaMarketingStatusTone($t['estado']);
                        $isFinished = in_array($t['estado'], ['Entregado','Cerrado','Rechazado'], true);
                        $badgeClass = $tone === 'green' ? 'badge-green' : ($tone === 'red' ? 'badge-red' : ($tone === 'orange' ? 'badge-yellow' : ($tone === 'purple' ? 'badge-blue' : 'badge-gray')));
                    ?>
                        <a class="ticket-item" data-state="<?php echo $isFinished ? 'done' : 'active'; ?>" href="seguimiento.php?folio=<?php echo urlencode($t['folio']); ?>">
                            <div class="ticket-main">
                                <div class="ticket-title">
                                    <strong><?php echo htmlspecialchars($t['folio']); ?></strong> · <?php echo htmlspecialchars($t['actividad']); ?>
                                </div>
                                <div class="ticket-sub">
                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars(cdaMarketingStatusLabel($t['estado'])); ?></span>
                                    <span>Prioridad: <strong><?php echo htmlspecialchars($t['prioridad']); ?></strong></span>
                                    <span>Solicitado: <strong><?php echo htmlspecialchars(cdaMarketingFormatDate($t['fecha_requerida'])); ?></strong></span>
                                    <?php if (!empty($t['fecha_entrega_estimada'])): ?>
                                        <span>Entrega est.: <strong><?php echo htmlspecialchars(cdaMarketingFormatDate($t['fecha_entrega_estimada'])); ?></strong></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="button" style="min-height: 36px; padding: .4rem .8rem; font-size: .75rem;">Ver ticket →</span>
                        </a>
                    <?php endforeach; ?>

                    <?php if (!$tickets): ?>
                        <div class="empty-state">
                            <p>No has registrado solicitudes de marketing aún.</p>
                            <a href="crear-ticket.php" class="button">Crear mi primera solicitud</a>
                        </div>
                    <?php endif; ?>
                </div>
            </article>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tab-btn');
            const contents = document.querySelectorAll('.tab-content');
            const filterBtns = document.querySelectorAll('.filter-btn');
            const ticketItems = document.querySelectorAll('.ticket-item');

            function switchTab(tabId) {
                tabs.forEach(btn => btn.classList.toggle('active', btn.dataset.tab === tabId));
                contents.forEach(content => content.classList.toggle('active', content.id === 'tab-' + tabId));
            }

            tabs.forEach(btn => {
                btn.addEventListener('click', function() {
                    switchTab(this.dataset.tab);
                });
            });

            // Handle hash navigation (#configuracion, #perfil, #tickets)
            if (window.location.hash) {
                const hash = window.location.hash.replace('#', '');
                if (['perfil', 'configuracion', 'tickets'].includes(hash)) {
                    switchTab(hash);
                }
            }

            // Ticket filter logic
            filterBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    filterBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    const filter = this.dataset.filter;
                    ticketItems.forEach(item => {
                        if (filter === 'all') {
                            item.style.display = 'flex';
                        } else if (filter === 'active') {
                            item.style.display = item.dataset.state === 'active' ? 'flex' : 'none';
                        } else if (filter === 'done') {
                            item.style.display = item.dataset.state === 'done' ? 'flex' : 'none';
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>

