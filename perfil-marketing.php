<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/marketing_helpers.php';

$user = cdaRequireLogin();
$message = '';
$error = '';

function cdaProfileInitials($name) {
    $initials = '';
    foreach (preg_split('/\s+/', trim((string) $name)) as $word) {
        if ($word !== '') {
            $initials .= strtoupper(substr($word, 0, 1));
        }
    }

    return substr($initials, 0, 2) ?: 'U';
}

function cdaProfileTicketColumns() {
    static $columns = null;
    if ($columns !== null) {
        return $columns;
    }

    $columns = [
        'id',
        'folio',
        'actividad',
        'departamento',
        'tipo_solicitud',
        'estado',
        'prioridad',
        'fecha_requerida',
        'creado_en',
        'actualizado_en',
    ];

    try {
        $stmt = cdaDb()->query("SHOW COLUMNS FROM marketing_tickets LIKE 'fecha_entrega_estimada'");
        if ($stmt && $stmt->fetch()) {
            $columns[] = 'fecha_entrega_estimada';
        }
    } catch (Throwable $e) {
        // En hosting sin permisos de SHOW, dejamos la vista operable con las columnas base.
    }

    return $columns;
}

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
$ticketColumns = implode(', ', cdaProfileTicketColumns());
$ticketStmt = cdaDb()->prepare("
    SELECT $ticketColumns
    FROM marketing_tickets
    WHERE correo = ? AND eliminado_en IS NULL
    ORDER BY actualizado_en DESC
    LIMIT 30
");
$ticketStmt->execute([$user['correo']]);
$tickets = $ticketStmt->fetchAll();

// Role Descriptions
$roleDescriptions = [
    'admin' => 'Administrador · Acceso completo al panel, gestión de tickets, tablero Kanban, estadísticas y administración de usuarios.',
    'manager' => 'Coordinador / Manager · Gestión de tickets, asignación de tareas, tablero Kanban y estadísticas.',
    'trabajador' => 'Equipo de Marketing · Acceso al tablero Kanban de producción y atención de solicitudes asignadas.',
    'usuario' => 'Solicitante · Creación de solicitudes de marketing, carga de archivos y seguimiento de tickets.'
];
$roleDesc = $roleDescriptions[$user['rol']] ?? $roleDescriptions['usuario'];

// Generate User Initials
$initials = cdaProfileInitials($user['nombre']);
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
            --line: rgba(6, 57, 112, .15);
            --soft: #f4f8fc;
            --yellow: #f6eb17;
            --radius: 12px;
            --shadow: 0 18px 46px rgba(6, 57, 112, .14);
            --green: #047857;
            --red: #b91c1c;
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
            color: rgba(255,255,255,.9);
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
            background: rgba(255,255,255,.16);
            color: #fff;
            border-color: rgba(255,255,255,.36);
        }
        .nav a.active {
            background: rgba(255,255,255,.2);
            color: #fff;
            border-color: rgba(246,235,23,.6);
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
        .profile-dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + .45rem);
            z-index: 50;
            display: grid;
            min-width: 200px;
            padding: .45rem;
            border: 1px solid rgba(6,57,112,.15);
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 18px 40px rgba(0,0,0,.25);
        }
        .profile-dropdown a {
            min-height: 38px;
            justify-content: flex-start;
            border: 0;
            background: #fff;
            color: var(--ink);
        }
        .profile-dropdown a:hover { background: var(--soft); color: var(--blue); }
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
            color: rgba(255,255,255,.88);
        }
        .role-pill {
            display: inline-flex;
            align-items: center;
            padding: .22rem .65rem;
            border-radius: 20px;
            font-size: .72rem;
            font-weight: 850;
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
            background: rgba(0, 0, 0, .28);
            border: 1px solid rgba(255,255,255,.14);
            border-radius: 10px;
            padding: .75rem 1.1rem;
            min-width: 110px;
            text-align: center;
        }
        .stat-box .num {
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--yellow);
            line-height: 1;
        }
        .stat-box .lbl {
            font-size: .7rem;
            font-weight: 750;
            color: rgba(255,255,255,.8);
            text-transform: uppercase;
            margin-top: .3rem;
        }

        /* Section Navigation Shortcuts */
        .section-nav {
            display: flex;
            gap: .6rem;
            margin-bottom: 1.25rem;
            flex-wrap: wrap;
        }
        .section-link {
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 8px;
            color: #fff;
            padding: .6rem 1rem;
            font-size: .84rem;
            font-weight: 850;
            text-decoration: none;
            transition: all .15s ease;
        }
        .section-link:hover {
            background: var(--yellow);
            color: var(--blue-dark);
            border-color: var(--yellow);
        }

        /* Cards & Section Container */
        .card {
            background: #ffffff;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 1.4rem;
            box-shadow: var(--shadow);
            margin-bottom: 1.4rem;
            color: var(--ink);
        }
        .card h2 {
            color: var(--blue);
            font-size: 1.2rem;
            font-weight: 800;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .two-cols {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 1.25rem;
        }
        @media (max-width: 840px) {
            .two-cols { grid-template-columns: 1fr; }
        }

        /* Information Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: .8rem;
            margin-bottom: 1rem;
        }
        .info-item {
            border: 1px solid rgba(6,57,112,.1);
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
            padding: .95rem 1.1rem;
            color: #1e40af;
            font-size: .88rem;
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        /* Notifications */
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
        body[data-visible-confirmations="true"] .ok,
        body[data-visible-confirmations="true"] .error {
            box-shadow: 0 10px 24px rgba(15,23,42,.16);
        }
        body[data-reduced-motion="true"] *,
        body[data-reduced-motion="true"] *::before,
        body[data-reduced-motion="true"] *::after {
            transition-duration: 0s !important;
            animation-duration: 0s !important;
            scroll-behavior: auto !important;
        }
        body[data-reduced-motion="true"] .ticket-item:hover,
        body[data-reduced-motion="true"] button.btn-primary:hover,
        body[data-reduced-motion="true"] .button:hover {
            transform: none;
        }

        /* Forms */
        .form-group {
            margin-bottom: 1.1rem;
        }
        .form-group label {
            display: block;
            font-size: .84rem;
            font-weight: 850;
            color: var(--ink);
            margin-bottom: .38rem;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            border: 1px solid rgba(6,57,112,.25);
            border-radius: 8px;
            padding: .8rem .9rem;
            font: inherit;
            background: #ffffff;
            color: var(--ink);
            outline: none;
            transition: all .15s ease;
        }
        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--blue-2);
            box-shadow: 0 0 0 4px rgba(6,57,112,.12);
        }
        .form-hint {
            font-size: .75rem;
            color: var(--muted);
            margin-top: .3rem;
        }
        .settings-list {
            display: grid;
            gap: .75rem;
        }
        .setting-row {
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            gap: .85rem;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--soft);
            padding: .8rem .9rem;
        }
        .setting-row strong {
            display: block;
            color: var(--ink);
            font-size: .86rem;
            margin-bottom: .18rem;
        }
        .setting-row span {
            display: block;
            color: var(--muted);
            font-size: .76rem;
            line-height: 1.35;
        }
        .setting-row select {
            min-width: 160px;
            border: 1px solid rgba(6,57,112,.25);
            border-radius: 8px;
            padding: .65rem .75rem;
            color: var(--ink);
            background: #fff;
            font: inherit;
            font-size: .82rem;
            font-weight: 750;
        }
        .switch {
            position: relative;
            display: inline-flex;
            width: 46px;
            height: 26px;
            flex-shrink: 0;
        }
        .switch input {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }
        .switch span {
            position: absolute;
            inset: 0;
            border-radius: 999px;
            background: #cbd5e1;
            transition: background .15s ease;
        }
        .switch span::after {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            top: 3px;
            left: 3px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 2px 6px rgba(15,23,42,.22);
            transition: transform .15s ease;
        }
        .switch input:checked + span {
            background: var(--blue-2);
        }
        .switch input:checked + span::after {
            transform: translateX(20px);
        }
        .settings-status {
            margin-top: .85rem;
            min-height: 20px;
            color: var(--green);
            font-size: .78rem;
            font-weight: 800;
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
            box-shadow: 0 4px 12px rgba(6,57,112,.18);
            transition: all .15s ease;
        }
        button.btn-primary:hover, .button:hover {
            background: #edd800;
            transform: translateY(-1px);
        }

        /* Ticket Cards */
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
            padding: .38rem .9rem;
            font-size: .78rem;
            font-weight: 850;
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
            padding: .95rem 1.1rem;
            text-decoration: none;
            color: inherit;
            transition: all .15s ease;
        }
        .ticket-item:hover {
            border-color: var(--blue);
            transform: translateX(3px);
            box-shadow: 0 6px 16px rgba(6,57,112,.1);
        }
        .ticket-main {
            display: grid;
            gap: .28rem;
        }
        .ticket-title {
            font-weight: 850;
            color: var(--blue);
            font-size: .98rem;
        }
        .ticket-sub {
            font-size: .82rem;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: .65rem;
            flex-wrap: wrap;
        }
        .badge {
            display: inline-block;
            padding: .18rem .55rem;
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
            .setting-row { grid-template-columns: 1fr; }
            .setting-row select { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <!-- Header Navigation Bar -->
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

        <!-- Shortcut Navigation Links -->
        <nav class="section-nav" aria-label="Accesos rápidos de perfil">
            <a href="#seccion-cuenta" class="section-link">👤 Información de Cuenta</a>
            <a href="#configuracion" class="section-link">⚙️ Configuración y Seguridad</a>
            <a href="#seccion-solicitudes" class="section-link">📋 Mis Solicitudes (<?php echo count($tickets); ?>)</a>
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

        <!-- Section 1: Información de Cuenta -->
        <section id="seccion-cuenta" class="two-cols">
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
                <h2>Permisos del Rol</h2>
                <div class="role-box">
                    <strong>Nivel de Acceso:</strong> <?php echo htmlspecialchars($roleDesc); ?>
                </div>
                <div style="display: flex; gap: .6rem; flex-wrap: wrap; margin-top: .8rem;">
                    <a href="crear-ticket.php" class="button" style="min-height: 38px; padding: .5rem .9rem; font-size: .8rem;">+ Nueva Solicitud</a>
                    <a href="seguimiento.php" class="button" style="min-height: 38px; padding: .5rem .9rem; font-size: .8rem; background: var(--soft); color: var(--blue); border: 1px solid var(--line);">Ver Seguimiento</a>
                </div>
            </article>
        </section>

        <!-- Section 2: Configuración y Seguridad -->
        <section class="two-cols">
            <article class="card">
                <h2>Editar Perfil y Contraseña</h2>
                <form id="configuracion" method="post" action="perfil-marketing.php#configuracion">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(cdaCsrfToken()); ?>">
                    
                    <div class="form-group">
                        <label for="input-nombre">Nombre completo</label>
                        <input id="input-nombre" name="nombre" value="<?php echo htmlspecialchars($user['nombre']); ?>" required>
                    </div>

                    <?php if (!empty($fullUser['password_hash'])): ?>
                        <div class="form-group">
                            <label for="input-current-password">Contraseña actual</label>
                            <input id="input-current-password" name="current_password" type="password" autocomplete="current-password" placeholder="Requerida solo para cambiar contraseña">
                            <div class="form-hint">Escribe tu contraseña actual para autorizar la actualización de contraseña.</div>
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
                <h2>Seguridad y Preferencias</h2>
                <div style="font-size: .88rem; color: var(--ink); line-height: 1.5; display: grid; gap: 1rem;">
                    <div style="background: var(--soft); padding: 1rem; border-radius: 8px; border: 1px solid var(--line);">
                        <strong style="color: var(--blue);">🔒 Autenticación Segura</strong>
                        <p style="color: var(--muted); font-size: .82rem; margin-top: .3rem;">
                            Si inicias sesión con tu cuenta de Google o correo corporativo, no es necesario asignar contraseña manual.
                        </p>
                    </div>
                    <div style="background: var(--soft); padding: 1rem; border-radius: 8px; border: 1px solid var(--line);">
                        <strong style="color: var(--blue);">🔑 Buenas Prácticas</strong>
                        <p style="color: var(--muted); font-size: .82rem; margin-top: .3rem;">
                            Utiliza una contraseña robusta de al menos 8 caracteres combinando letras y números. Nunca compartas tus accesos.
                        </p>
                    </div>
                    <div class="settings-list" aria-label="Preferencias de perfil">
                        <label class="setting-row" for="setting-default-filter">
                            <span>
                                <strong>Filtro inicial de solicitudes</strong>
                                <span>Elige qué lista se muestra primero al abrir tu perfil.</span>
                            </span>
                            <select id="setting-default-filter" data-profile-setting="defaultFilter">
                                <option value="all">Todas</option>
                                <option value="active">Activas</option>
                                <option value="done">Entregadas</option>
                            </select>
                        </label>
                        <label class="setting-row" for="setting-profile-menu">
                            <span>
                                <strong>Menú de perfil cerrado</strong>
                                <span>Mantiene el menú superior cerrado al cargar la página.</span>
                            </span>
                            <span class="switch">
                                <input id="setting-profile-menu" type="checkbox" data-profile-setting="closeProfileMenu" checked>
                                <span aria-hidden="true"></span>
                            </span>
                        </label>
                        <label class="setting-row" for="setting-confirmations">
                            <span>
                                <strong>Confirmaciones visibles</strong>
                                <span>Resalta mensajes de éxito y avisos importantes después de guardar.</span>
                            </span>
                            <span class="switch">
                                <input id="setting-confirmations" type="checkbox" data-profile-setting="visibleConfirmations" checked>
                                <span aria-hidden="true"></span>
                            </span>
                        </label>
                        <label class="setting-row" for="setting-reduced-motion">
                            <span>
                                <strong>Reducir movimiento</strong>
                                <span>Desactiva animaciones pequeñas en botones y tarjetas.</span>
                            </span>
                            <span class="switch">
                                <input id="setting-reduced-motion" type="checkbox" data-profile-setting="reducedMotion">
                                <span aria-hidden="true"></span>
                            </span>
                        </label>
                    </div>
                    <div class="settings-status" id="settingsStatus" role="status" aria-live="polite"></div>
                </div>
            </article>
        </section>

        <!-- Section 3: Mis Solicitudes -->
        <section id="seccion-solicitudes">
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
            const filterBtns = document.querySelectorAll('.filter-btn');
            const ticketItems = document.querySelectorAll('.ticket-item');

            const profileSettingsKey = 'cdaMarketingProfileSettings';
            const profileMenu = document.querySelector('.profile-menu');
            const settingsStatus = document.getElementById('settingsStatus');
            const settingInputs = document.querySelectorAll('[data-profile-setting]');
            const defaultSettings = {
                defaultFilter: 'all',
                closeProfileMenu: true,
                visibleConfirmations: true,
                reducedMotion: false
            };
            let settings = { ...defaultSettings };

            try {
                settings = {
                    ...defaultSettings,
                    ...JSON.parse(localStorage.getItem(profileSettingsKey) || '{}')
                };
            } catch (error) {
                settings = { ...defaultSettings };
            }

            function clickFilter(filter) {
                const target = document.querySelector(`.filter-btn[data-filter="${filter}"]`) || document.querySelector('.filter-btn[data-filter="all"]');
                target?.click();
            }

            function applyProfileSettings(showSavedStatus = false) {
                if (profileMenu && settings.closeProfileMenu) {
                    profileMenu.removeAttribute('open');
                }

                document.body.dataset.visibleConfirmations = settings.visibleConfirmations ? 'true' : 'false';
                document.body.dataset.reducedMotion = settings.reducedMotion ? 'true' : 'false';
                clickFilter(settings.defaultFilter);

                if (showSavedStatus && settingsStatus) {
                    settingsStatus.textContent = 'Preferencias guardadas en este navegador.';
                    window.clearTimeout(settingsStatus.dataset.timerId);
                    settingsStatus.dataset.timerId = window.setTimeout(() => {
                        settingsStatus.textContent = '';
                    }, 2200);
                }
            }

            function syncSettingInputs() {
                settingInputs.forEach((input) => {
                    const key = input.dataset.profileSetting;
                    if (input.type === 'checkbox') {
                        input.checked = Boolean(settings[key]);
                    } else {
                        input.value = settings[key] || defaultSettings[key];
                    }
                });
            }

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

            settingInputs.forEach((input) => {
                input.addEventListener('change', () => {
                    const key = input.dataset.profileSetting;
                    settings[key] = input.type === 'checkbox' ? input.checked : input.value;
                    localStorage.setItem(profileSettingsKey, JSON.stringify(settings));
                    applyProfileSettings(true);
                });
            });

            syncSettingInputs();
            applyProfileSettings(false);
        });
    </script>
</body>
</html>
