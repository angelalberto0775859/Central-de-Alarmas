<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/marketing_helpers.php';

$user = cdaRequireLogin();
cdaMarketingEnsureTicketSchema();
if (!cdaMarketingCanViewAllTickets($user['rol'])) {
    header('Location: ' . cdaMarketingDefaultRouteForRole($user['rol']));
    exit;
}
$canManageTickets = cdaMarketingCanManageTickets($user['rol']);
$estado = cdaMarketingClean($_GET['estado'] ?? '');
$query = cdaMarketingClean($_GET['q'] ?? '');
$trashMode = cdaMarketingCanManageTrash($user['rol']) && ($_GET['papelera'] ?? '') === '1';
$params = [];
$where = [];

if (!cdaMarketingCanViewAllTickets($user['rol'])) {
    $where[] = 'correo = ?';
    $params[] = $user['correo'];
}

$where[] = $trashMode ? 'eliminado_en IS NOT NULL' : 'eliminado_en IS NULL';

if ($estado && cdaMarketingStatusAllowed($estado)) {
    $where[] = 'estado = ?';
    $params[] = $estado;
}

if ($query) {
    $where[] = '(folio LIKE ? OR solicitante LIKE ? OR correo LIKE ? OR actividad LIKE ?)';
    $like = '%' . $query . '%';
    array_push($params, $like, $like, $like, $like);
}

$sql = 'SELECT * FROM marketing_tickets';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY actualizado_en DESC LIMIT 100';
$stmt = cdaDb()->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();
$ticketMessages = cdaMarketingFetchTicketMessages(array_column($tickets, 'id'));
$ticketFiles = cdaMarketingFetchTicketFiles(array_column($tickets, 'id'));
$assignableUsers = cdaMarketingFetchAssignableUsers();
$flashError = $_SESSION['cda_marketing_error'] ?? '';
$flashSuccess = $_SESSION['cda_marketing_success'] ?? '';
unset($_SESSION['cda_marketing_error']);
unset($_SESSION['cda_marketing_success']);

$stats = [
    'total' => 0,
    'urgent' => 0,
    'active' => 0,
    'done' => 0,
];
$statsParams = [];
$statsWhere = 'WHERE eliminado_en IS NULL';
if (!cdaMarketingCanViewAllTickets($user['rol'])) {
    $statsWhere .= ' AND correo = ?';
    $statsParams[] = $user['correo'];
}
$statsStmt = cdaDb()->prepare("
    SELECT
        COUNT(*) AS total,
        SUM(prioridad = 'Urgente') AS urgent,
        SUM(estado NOT IN ('Entregado','Cerrado','Rechazado')) AS active,
        SUM(estado IN ('Entregado','Cerrado')) AS done
    FROM marketing_tickets
    $statsWhere
");
$statsStmt->execute($statsParams);
$statsRow = $statsStmt->fetch();
if ($statsRow) {
    $stats = [
        'total' => (int) $statsRow['total'],
        'urgent' => (int) $statsRow['urgent'],
        'active' => (int) $statsRow['active'],
        'done' => (int) $statsRow['done'],
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de tickets | Marketing</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="/contigo/Img/favicon.svg">
    <style>
        :root {
            --blue:#063970;
            --blue-2:#0b4f92;
            --blue-3:#0d62ad;
            --ink:#10213f;
            --muted:#66758d;
            --line:rgba(6,57,112,.13);
            --soft:#f4f8fc;
            --yellow:#f6eb17;
            --green:#047857;
            --red:#b91c1c;
            --radius:8px;
            --shadow:0 18px 46px rgba(6,57,112,.12);
        }
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
        .shell { width:min(1320px, calc(100% - 2rem)); margin:0 auto; padding:1.1rem 0 3rem; position:relative; z-index:2; }
        .ambient-points { position:fixed; inset:0; overflow:hidden; pointer-events:none; z-index:1; }
        .ambient-point { --size:2px; --x:50vw; --y:50vh; --dx:18px; --dy:-22px; --duration:18s; --delay:0s; position:absolute; left:var(--x); top:var(--y); width:var(--size); height:var(--size); border-radius:50%; background:rgba(255,255,255,.55); box-shadow:0 0 calc(var(--size) * 4) rgba(166,205,255,.28); opacity:.42; transform:translate3d(0,0,0); animation:ambientDrift var(--duration) ease-in-out var(--delay) infinite alternate; }
        @keyframes ambientDrift { 0% { transform:translate3d(0,0,0); opacity:.18; } 38% { opacity:.56; } 100% { transform:translate3d(var(--dx), var(--dy), 0); opacity:.32; } }
        .topbar { display:flex; align-items:center; justify-content:space-between; gap:1rem; min-height:64px; margin-bottom:1.35rem; color:#fff; }
        .topbar img { width:140px; display:block; filter:drop-shadow(0 10px 18px rgba(0,0,0,.18)); }
        .nav { display:flex; flex-wrap:wrap; gap:.55rem; align-items:center; justify-content:flex-end; }
        .nav a, button {
            min-height:40px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            color:rgba(255,255,255,.86);
            text-decoration:none;
            border:1px solid rgba(255,255,255,.2);
            border-radius:8px;
            padding:.62rem .78rem;
            background:rgba(255,255,255,.08);
            font-size:.8rem;
            font-weight:850;
            cursor:pointer;
            white-space:nowrap;
            transition:background .18s ease, color .18s ease, border-color .18s ease, transform .18s ease, box-shadow .18s ease;
        }
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
        .hero {
            display:flex;
            justify-content:space-between;
            gap:1rem;
            align-items:end;
            margin-bottom:1rem;
            color:#fff;
            border:1px solid rgba(255,255,255,.16);
            border-radius:var(--radius);
            padding:1.2rem;
            background:linear-gradient(135deg,rgba(255,255,255,.12),rgba(255,255,255,.04));
            box-shadow:0 24px 70px rgba(0,0,0,.16);
            position:relative;
            overflow:hidden;
        }
        .hero::before { content:""; position:absolute; inset:0 0 auto; height:5px; background:linear-gradient(90deg,var(--yellow),rgba(255,255,255,.6),var(--blue-3)); }
        .hero > * { position:relative; z-index:1; }
        .eyebrow { color:var(--yellow); font-size:.74rem; font-weight:950; letter-spacing:.12em; text-transform:uppercase; margin-bottom:.55rem; }
        h1 { color:#fff; font-size:clamp(1.9rem,4vw,3.35rem); line-height:1; letter-spacing:0; }
        .hero .muted { color:rgba(255,255,255,.76); margin-top:.45rem; }
        .story-strip { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.75rem; margin:0 0 1rem; }
        .story-step { border:1px solid rgba(255,255,255,.44); border-radius:var(--radius); padding:.82rem; background:linear-gradient(180deg,rgba(255,255,255,.96),rgba(248,251,255,.92)); box-shadow:var(--shadow); }
        .story-step span { display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; margin-bottom:.45rem; border-radius:50%; background:var(--blue); color:var(--yellow); font-weight:950; font-size:.76rem; }
        .story-step strong { display:block; color:var(--blue); font-size:.82rem; line-height:1.2; }
        .story-step p { margin-top:.22rem; color:var(--muted); font-size:.75rem; line-height:1.35; }
        .stats { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.8rem; margin-bottom:1rem; }
        .stat {
            border:1px solid rgba(255,255,255,.5);
            border-radius:var(--radius);
            padding:1rem;
            background:linear-gradient(180deg,#fff,#f8fbff);
            box-shadow:var(--shadow);
            position:relative;
            overflow:hidden;
        }
        .stat::before { content:""; position:absolute; inset:0 0 auto; height:4px; background:linear-gradient(90deg,var(--yellow),var(--blue-3)); }
        .stat span { color:var(--muted); font-size:.74rem; font-weight:900; letter-spacing:.05em; text-transform:uppercase; }
        .stat strong { display:block; color:var(--blue); font-size:2.2rem; line-height:1.05; margin-top:.25rem; }
        .card {
            background:rgba(255,255,255,.96);
            border:1px solid var(--line);
            border-radius:var(--radius);
            padding:.78rem;
            box-shadow:var(--shadow);
        }
        .card-head { display:flex; justify-content:space-between; align-items:start; gap:.75rem; margin-bottom:.62rem; }
        .card-head h2 { color:var(--blue); font-size:.98rem; }
        .table-shell { overflow:auto; border:1px solid rgba(6,57,112,.08); border-radius:var(--radius); background:#fff; }
        .filters {
            display:grid;
            grid-template-columns:1fr 240px auto;
            gap:.55rem;
            margin-bottom:.7rem;
            padding:.58rem;
            border:1px solid var(--line);
            border-radius:var(--radius);
            background:#f8fbff;
        }
        input, select, textarea {
            width:100%;
            border:1px solid var(--line);
            border-radius:var(--radius);
            padding:.64rem .72rem;
            font:inherit;
            background:#fff;
            color:var(--ink);
            outline:none;
        }
        input:focus, select:focus, textarea:focus { border-color:var(--blue-2); box-shadow:0 0 0 4px rgba(6,57,112,.09); }
        table { width:100%; border-collapse:separate; border-spacing:0; min-width:980px; }
        th, td { padding:.58rem .56rem; border-bottom:1px solid var(--line); text-align:left; vertical-align:top; font-size:.8rem; }
        th { color:var(--blue); font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; background:#f3f8fd; }
        th:first-child { border-radius:var(--radius) 0 0 var(--radius); }
        th:last-child { border-radius:0 var(--radius) var(--radius) 0; }
        tr:hover td { background:#fbfdff; }
        .ticket-list { display:grid; gap:.7rem; }
        .ticket-row-card { border:1px solid var(--line); border-left:6px solid var(--blue-2); border-radius:var(--radius); background:linear-gradient(180deg,#fff,#fbfdff); box-shadow:0 12px 28px rgba(6,57,112,.08); overflow:hidden; transition:border-color .18s ease, box-shadow .18s ease, transform .18s ease; }
        .ticket-row-card:hover { border-color:rgba(6,57,112,.28); box-shadow:0 16px 34px rgba(6,57,112,.12); transform:translateY(-1px); }
        .ticket-row-card.tone-amber { border-left-color:#f59e0b; }
        .ticket-row-card.tone-green { border-left-color:var(--green); }
        .ticket-row-card.tone-red { border-left-color:var(--red); }
        .ticket-row-card.tone-purple { border-left-color:#6d28d9; }
        .ticket-row-card[open] { box-shadow:0 18px 38px rgba(6,57,112,.13); }
        .ticket-row-card summary { list-style:none; cursor:pointer; padding:.9rem 2.2rem .9rem 1rem; position:relative; }
        .ticket-row-card summary::after { content:""; position:absolute; right:1rem; top:50%; width:.55rem; height:.55rem; border-right:2px solid var(--blue); border-bottom:2px solid var(--blue); transform:translateY(-60%) rotate(45deg); transition:transform .18s ease; opacity:.75; }
        .ticket-row-card[open] summary::after { transform:translateY(-30%) rotate(225deg); }
        .ticket-row-card summary::-webkit-details-marker { display:none; }
        .ticket-row-summary { display:grid; grid-template-columns:190px minmax(0,1fr) auto; gap:.9rem; align-items:center; }
        .ticket-main { min-width:0; }
        .ticket-main .muted { display:block; margin-top:.2rem; font-size:.78rem; }
        .ticket-pills { display:flex; flex-wrap:wrap; align-items:center; justify-content:flex-end; gap:.38rem; }
        .summary-date { color:var(--muted); font-size:.72rem; font-weight:850; white-space:nowrap; }
        .ticket-drawer { display:grid; grid-template-columns:minmax(0,1fr) minmax(300px,.54fr); gap:.85rem; padding:0 1rem 1rem; border-top:1px solid rgba(6,57,112,.08); }
        .ticket-panel { display:grid; gap:.65rem; align-content:start; padding-top:.85rem; }
        .ticket-meta-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.55rem; }
        .ticket-meta-grid div { border:1px solid rgba(6,57,112,.08); border-radius:var(--radius); background:#fff; padding:.62rem .7rem; }
        .ticket-meta-grid span { display:block; color:var(--muted); font-size:.68rem; font-weight:950; text-transform:uppercase; letter-spacing:.05em; margin-bottom:.18rem; }
        .ticket-meta-grid strong { color:var(--ink); font-size:.84rem; }
        .section-label { color:var(--muted); font-size:.68rem; font-weight:950; letter-spacing:.05em; text-transform:uppercase; }
        .folio { font-weight:950; color:var(--blue); letter-spacing:.02em; }
        .request-title { color:var(--ink); font-weight:900; line-height:1.2; }
        .status { display:inline-flex; border-radius:999px; padding:.34rem .56rem; background:var(--soft); color:var(--blue); font-size:.72rem; font-weight:900; white-space:nowrap; }
        .status.entregado, .status.cerrado { background:#d1fae5; color:var(--green); }
        .status.rechazado { background:#fee2e2; color:#991b1b; }
        .status.en-diseno, .status.en-revision { background:#dbeafe; color:#1d4ed8; }
        .status.pendiente-de-informacion, .status.ajustes-solicitados { background:#fff7cc; color:#7c5800; }
        .priority { display:inline-flex; border-radius:999px; padding:.3rem .5rem; background:#eef4fb; color:var(--ink); font-size:.7rem; font-weight:900; }
        .priority.urgente { background:#fee2e2; color:var(--red); }
        .priority.alta { background:#fff7cc; color:#7c5800; }
        .assignment-badge { display:inline-flex; width:fit-content; border-radius:999px; padding:.32rem .55rem; font-size:.7rem; font-weight:950; background:#eef4fb; color:var(--blue); }
        .assignment-badge.mine { background:#d1fae5; color:#047857; }
        .assignment-badge.assigned { background:#ede9fe; color:#5b21b6; }
        .assignment-badge.unassigned { background:#f1f5f9; color:#475569; }
        .inline-form { display:grid; gap:.5rem; min-width:240px; padding:.7rem; border:1px solid rgba(6,57,112,.08); border-radius:var(--radius); background:#fbfdff; }
        .inline-form button, .filters button {
            background:var(--yellow);
            border-color:transparent;
            color:var(--blue);
            text-transform:uppercase;
            box-shadow:0 10px 22px rgba(6,57,112,.1);
        }
        .inline-form button:hover, .filters button:hover { transform:translateY(-1px); box-shadow:0 14px 28px rgba(6,57,112,.14); }
        .danger-button { background:#fee2e2 !important; color:#991b1b !important; box-shadow:none !important; }
        .restore-button { background:#d1fae5 !important; color:#047857 !important; box-shadow:none !important; }
        .alert-error, .alert-success { margin-bottom:1rem; border-radius:var(--radius); padding:.85rem 1rem; font-size:.86rem; font-weight:850; line-height:1.45; }
        .alert-error { border:1px solid #fecaca; background:#fee2e2; color:#991b1b; }
        .alert-success { border:1px solid #a7f3d0; background:#d1fae5; color:#047857; }
        .ticket-actions { display:grid; gap:.5rem; margin-top:.6rem; }
        .muted { color:var(--muted); }
        .empty-state { padding:2rem; text-align:center; color:var(--muted); }
        .ticket-chat { display:grid; gap:.58rem; padding:.78rem; border:1px solid rgba(6,57,112,.1); border-radius:var(--radius); background:linear-gradient(180deg,#f8fbff,#fff); box-shadow:0 10px 24px rgba(6,57,112,.06); }
        .ticket-chat h3 { color:var(--blue); font-size:.78rem; text-transform:uppercase; letter-spacing:.05em; }
        .chat-note { color:var(--muted); font-size:.74rem; line-height:1.4; }
        .chat-thread { display:grid; gap:.48rem; max-height:230px; overflow:auto; padding-right:.2rem; }
        .chat-message { border:1px solid rgba(6,57,112,.08); border-left:4px solid var(--blue-2); padding:.6rem .68rem; border-radius:var(--radius); background:#fff; box-shadow:0 8px 18px rgba(6,57,112,.05); }
        .chat-message.admin { border-left-color:#f59e0b; background:#fffaf0; }
        .chat-message.usuario { border-left-color:var(--green); background:#fbfffd; }
        .chat-meta { display:flex; justify-content:space-between; gap:.5rem; color:var(--muted); font-size:.7rem; font-weight:850; }
        .chat-message p { margin-top:.28rem; color:var(--ink); font-size:.84rem; line-height:1.5; }
        .chat-form { display:grid; gap:.5rem; }
        .chat-files { display:grid; gap:.35rem; margin-top:.42rem; }
        .chat-file { display:inline-flex; width:fit-content; border-radius:6px; padding:.34rem .48rem; background:#eef4fb; color:var(--blue); font-size:.72rem; font-weight:850; text-decoration:none; }
        .ticket-files { display:flex; flex-wrap:wrap; gap:.35rem; margin-top:.45rem; }
        .ticket-file { display:inline-flex; border-radius:6px; padding:.34rem .48rem; background:#fff7cc; color:#7c5800; font-size:.72rem; font-weight:850; text-decoration:none; }
        .file-input { padding:.42rem; font-size:.72rem; background:#fff; }
        @media (max-width:900px) {
            .filters, .hero, .stats, .story-strip { grid-template-columns:1fr; flex-direction:column; align-items:stretch; }
            .topbar { align-items:flex-start; flex-direction:column; }
            .nav { justify-content:flex-start; }
            .profile-dropdown { left:0; right:auto; }
            .ticket-row-summary, .ticket-drawer, .ticket-meta-grid { grid-template-columns:1fr; }
            .ticket-pills { justify-content:flex-start; }
            table, tbody, tr, td { display:block; min-width:0; }
            thead { display:none; }
            tr { border:1px solid var(--line); border-radius:var(--radius); margin-bottom:.8rem; background:#fff; overflow:hidden; }
            td { border:0; }
            td + td { border-top:1px solid rgba(6,57,112,.08); }
            .inline-form { min-width:0; }
        }
        @media (prefers-reduced-motion:reduce) { .ambient-point { animation:none; } }
    </style>
</head>
<body>
    <div class="ambient-points" aria-hidden="true"></div>
    <div class="shell">
        <header class="topbar">
            <a href="index.html"><img src="img/cda-logo-f.svg" alt="Central de Alarmas"></a>
            <nav class="nav" aria-label="Navegacion">
                <?php if (cdaMarketingCanViewAllTickets($user['rol'])): ?><a class="admin-link" href="estadisticas-marketing.php">Estadísticas</a><?php endif; ?>
                <?php if (cdaMarketingCanViewAllTickets($user['rol'])): ?><a class="admin-link <?php echo $trashMode ? '' : 'active'; ?>" href="panel-marketing.php">Tickets</a><?php endif; ?>
                <?php if (cdaMarketingCanAccessBoard($user['rol'])): ?><a class="admin-link" href="control-marketing.php">Tablero</a><?php endif; ?>
                <?php if (cdaMarketingCanManageUsers($user['rol'])): ?><a class="admin-link" href="usuarios-marketing.php">Usuarios</a><?php endif; ?>
                <?php if (cdaMarketingCanManageTrash($user['rol'])): ?><a class="admin-link <?php echo $trashMode ? 'active' : ''; ?>" href="panel-marketing.php?papelera=1">Basurero</a><?php endif; ?>
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
            <div>
                <div class="eyebrow"><?php echo $trashMode ? 'Basurero admin' : (cdaMarketingCanViewAllTickets($user['rol']) ? 'Vista general' : 'Mis solicitudes'); ?></div>
                <h1><?php echo $trashMode ? 'Tickets en basurero' : 'Tickets de Marketing'; ?></h1>
                <p class="muted"><?php echo $trashMode ? 'Restaura solicitudes enviadas al basurero o borralas definitivamente cuando ya no se necesiten.' : 'Cada solicitud vive por folio: registro validado, estado visible, conversacion del ticket y cierre con historial.'; ?></p>
            </div>
        </section>
        <section class="story-strip" aria-label="Historia del ticket">
            <div class="story-step"><span>1</span><strong>Sesión</strong><p>El ticket queda ligado al perfil activo del solicitante.</p></div>
            <div class="story-step"><span>2</span><strong>Confirmacion</strong><p>El folio llega al solicitante y avisa a los admins.</p></div>
            <div class="story-step"><span>3</span><strong>Seguimiento</strong><p>El chat mantiene dudas, aprobaciones y acuerdos en el ticket.</p></div>
            <div class="story-step"><span>4</span><strong>Entrega</strong><p>Estado e historial dejan claro que paso y cuando cerro.</p></div>
        </section>
        <section class="stats" aria-label="Resumen de tickets">
            <div class="stat"><span>Total</span><strong><?php echo $stats['total']; ?></strong></div>
            <div class="stat"><span>Activos</span><strong><?php echo $stats['active']; ?></strong></div>
            <div class="stat"><span>Urgentes</span><strong><?php echo $stats['urgent']; ?></strong></div>
            <div class="stat"><span>Cerrados</span><strong><?php echo $stats['done']; ?></strong></div>
        </section>
        <?php if ($flashError): ?>
            <div class="alert-error"><?php echo htmlspecialchars($flashError); ?></div>
        <?php endif; ?>
        <?php if ($flashSuccess): ?>
            <div class="alert-success"><?php echo htmlspecialchars($flashSuccess); ?></div>
        <?php endif; ?>
        <section class="card">
            <div class="card-head">
                <div>
                    <h2><?php echo $trashMode ? 'Basurero de tickets' : 'Listado operativo'; ?></h2>
                    <p class="muted"><?php echo $trashMode ? 'Los tickets aqui ya no aparecen en el tablero ni en seguimiento.' : 'Filtra, actualiza estado y conversa en el chat de cada solicitud.'; ?></p>
                </div>
            </div>
            <form class="filters" method="get" action="panel-marketing.php">
                <?php if ($trashMode): ?><input type="hidden" name="papelera" value="1"><?php endif; ?>
                <input name="q" value="<?php echo htmlspecialchars($query); ?>" placeholder="Buscar folio, solicitante, correo o actividad">
                <select name="estado">
                    <option value="">Todos los estados</option>
                    <?php foreach (cdaMarketingStatuses() as $status): ?>
                        <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $estado === $status ? 'selected' : ''; ?>><?php echo htmlspecialchars(cdaMarketingStatusLabel($status)); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Filtrar</button>
            </form>
            <div class="ticket-list">
                <?php foreach ($tickets as $ticket): ?>
                <details class="ticket-row-card tone-<?php echo htmlspecialchars(cdaMarketingStatusTone($ticket['estado'])); ?>" id="ticket-<?php echo (int) $ticket['id']; ?>">
                    <summary>
                        <div class="ticket-row-summary">
                            <div>
                                <div class="folio"><?php echo htmlspecialchars($ticket['folio']); ?></div>
                                <span class="muted"><?php echo htmlspecialchars($ticket['solicitante']); ?></span>
                            </div>
                            <div class="ticket-main">
                                <strong class="request-title"><?php echo htmlspecialchars($ticket['actividad']); ?></strong>
                                <span class="muted"><?php echo htmlspecialchars($ticket['tipo_solicitud']); ?> · <?php echo htmlspecialchars($ticket['departamento']); ?></span>
                            </div>
                            <div class="ticket-pills">
                                <span class="status <?php echo htmlspecialchars(cdaMarketingStatusClass($ticket['estado'])); ?>"><?php echo htmlspecialchars(cdaMarketingStatusLabel($ticket['estado'])); ?></span>
                                <span class="priority <?php echo htmlspecialchars(strtolower($ticket['prioridad'])); ?>"><?php echo htmlspecialchars($ticket['prioridad']); ?></span>
                                <span class="assignment-badge <?php echo htmlspecialchars(cdaMarketingTicketAssignmentClass($ticket, $user)); ?>"><?php echo htmlspecialchars(cdaMarketingTicketAssignmentLabel($ticket, $user)); ?></span>
                                <span class="summary-date"><?php echo htmlspecialchars(cdaMarketingFormatDate($ticket['fecha_requerida'])); ?></span>
                            </div>
                        </div>
                    </summary>
                    <div class="ticket-drawer">
                        <div class="ticket-panel">
                            <div class="ticket-meta-grid">
                                <div><span>Correo</span><strong><?php echo htmlspecialchars($ticket['correo']); ?></strong></div>
                                <div><span>Actualizado</span><strong><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($ticket['actualizado_en']))); ?></strong></div>
                                <div><span>Asignado</span><strong><?php echo htmlspecialchars($ticket['asignado_a'] ?: 'Sin asignar'); ?></strong></div>
                                <div><span>Entrega aproximada</span><strong><?php echo htmlspecialchars(cdaMarketingFormatDate($ticket['fecha_entrega_estimada'] ?? null)); ?></strong></div>
                                <div><span>Área</span><strong><?php echo htmlspecialchars($ticket['departamento']); ?></strong></div>
                            </div>
                            <?php if (!empty($ticketFiles[(int) $ticket['id']])): ?>
                                <div>
                                    <div class="section-label">Archivos iniciales</div>
                                    <div class="ticket-files" aria-label="Archivos iniciales">
                                        <?php foreach ($ticketFiles[(int) $ticket['id']] as $file): ?>
                                            <a class="ticket-file" href="descargar-archivo.php?tipo=ticket&id=<?php echo (int) $file['id']; ?>"><?php echo htmlspecialchars($file['nombre_original']); ?></a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!$trashMode && $canManageTickets): ?>
                            <form class="inline-form" method="post" action="ticket-actualizar.php">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(cdaCsrfToken()); ?>">
                                <input type="hidden" name="id" value="<?php echo (int) $ticket['id']; ?>">
                                <input type="hidden" name="return_to" value="panel-marketing.php">
                                <select name="estado" required>
                                    <?php foreach (cdaMarketingStatuses() as $status): ?>
                                        <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $ticket['estado'] === $status ? 'selected' : ''; ?>><?php echo htmlspecialchars(cdaMarketingStatusLabel($status)); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="asignado_a" aria-label="Asignar ticket">
                                    <option value="">Sin asignar</option>
                                    <?php foreach ($assignableUsers as $assignableUser): ?>
                                        <?php $adminName = (string) ($assignableUser['nombre'] ?? ''); ?>
                                        <?php $roleLabel = cdaMarketingRoleLabel($assignableUser['rol'] ?? 'usuario'); ?>
                                        <option value="<?php echo htmlspecialchars($adminName); ?>" <?php echo ($ticket['asignado_a'] ?? '') === $adminName ? 'selected' : ''; ?>><?php echo htmlspecialchars($adminName . ' · ' . $roleLabel); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label class="section-label">Entrega aproximada
                                    <input name="fecha_entrega_estimada" type="date" value="<?php echo htmlspecialchars($ticket['fecha_entrega_estimada'] ?? ''); ?>">
                                </label>
                                <textarea name="respuesta_interna" rows="3" placeholder="Comentario visible para seguimiento"><?php echo htmlspecialchars($ticket['respuesta_interna'] ?? ''); ?></textarea>
                                <button type="submit">Guardar cambios</button>
                            </form>
                            <?php endif; ?>
                            <?php if (cdaMarketingCanManageTrash($user['rol'])): ?>
                            <div class="ticket-actions">
                                <?php if ($trashMode): ?>
                                <form class="inline-form" method="post" action="ticket-eliminar.php">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(cdaCsrfToken()); ?>">
                                    <input type="hidden" name="id" value="<?php echo (int) $ticket['id']; ?>">
                                    <input type="hidden" name="action" value="restore">
                                    <input type="hidden" name="return_to" value="panel-marketing.php?papelera=1">
                                    <button class="restore-button" type="submit">Restaurar</button>
                                </form>
                                <form class="inline-form" method="post" action="ticket-eliminar.php" onsubmit="return confirm('Borrar definitivamente este ticket? Esta accion no se puede deshacer.');">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(cdaCsrfToken()); ?>">
                                    <input type="hidden" name="id" value="<?php echo (int) $ticket['id']; ?>">
                                    <input type="hidden" name="action" value="purge">
                                    <input type="hidden" name="return_to" value="panel-marketing.php?papelera=1">
                                    <button class="danger-button" type="submit">Borrar definitivo</button>
                                </form>
                                <?php else: ?>
                                <form class="inline-form" method="post" action="ticket-eliminar.php" onsubmit="return confirm('Enviar este ticket al basurero?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(cdaCsrfToken()); ?>">
                                    <input type="hidden" name="id" value="<?php echo (int) $ticket['id']; ?>">
                                    <input type="hidden" name="action" value="trash">
                                    <input type="hidden" name="return_to" value="panel-marketing.php">
                                    <button class="danger-button" type="submit">Enviar al basurero</button>
                                </form>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php if (!$trashMode): ?>
                        <section class="ticket-chat" aria-label="Chat del ticket <?php echo htmlspecialchars($ticket['folio']); ?>">
                            <h3>Chat de seguimiento</h3>
                            <p class="chat-note">Este hilo queda asociado al folio; el solicitante puede responder desde seguimiento con su login.</p>
                            <div class="chat-thread">
                                <?php foreach (($ticketMessages[(int) $ticket['id']] ?? []) as $message): ?>
                                    <article class="chat-message <?php echo htmlspecialchars($message['autor_rol']); ?>">
                                        <div class="chat-meta">
                                            <strong><?php echo htmlspecialchars(cdaMarketingMessageAuthor($message)); ?></strong>
                                            <span><?php echo htmlspecialchars(date('d/m H:i', strtotime($message['creado_en']))); ?></span>
                                        </div>
                                        <p><?php echo nl2br(htmlspecialchars($message['mensaje'])); ?></p>
                                        <?php if (!empty($message['archivos'])): ?>
                                            <div class="chat-files">
                                                <?php foreach ($message['archivos'] as $file): ?>
                                                    <a class="chat-file" href="descargar-archivo.php?tipo=mensaje&id=<?php echo (int) $file['id']; ?>"><?php echo htmlspecialchars($file['nombre_original']); ?></a>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </article>
                                <?php endforeach; ?>
                                <?php if (empty($ticketMessages[(int) $ticket['id']])): ?><p class="muted">Aun no hay mensajes; escribe aqui si necesitas aclarar el brief o pedir aprobacion.</p><?php endif; ?>
                            </div>
                            <form class="chat-form" method="post" action="ticket-mensaje.php" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(cdaCsrfToken()); ?>">
                                <input type="hidden" name="ticket_id" value="<?php echo (int) $ticket['id']; ?>">
                                <input type="hidden" name="return_to" value="panel-marketing.php">
                                <textarea name="mensaje" rows="3" placeholder="Escribe un mensaje para este ticket"></textarea>
                                <input class="file-input" name="archivos[]" type="file" multiple accept="<?php echo htmlspecialchars(cdaMarketingAllowedUploadAccept()); ?>">
                                <button type="submit">Enviar mensaje</button>
                            </form>
                        </section>
                        <?php endif; ?>
                    </div>
                </details>
                <?php endforeach; ?>
                <?php if (!$tickets): ?>
                    <div class="empty-state">No hay tickets con esos filtros.</div>
                <?php endif; ?>
            </div>
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
