<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/marketing_helpers.php';

$folio = cdaMarketingClean($_GET['folio'] ?? $_POST['folio'] ?? '');
$ticket = null;
$historial = [];
$archivos = [];
$mensajes = [];
$error = '';
$chatError = '';
$progressIndex = 0;
$isTicketFinished = false;
$deliveryFiles = [];
$currentUser = cdaCurrentUser();

if ($folio) {
    try {
        $stmt = cdaDb()->prepare('SELECT * FROM marketing_tickets WHERE folio = ? AND eliminado_en IS NULL LIMIT 1');
        $stmt->execute([$folio]);
        $ticket = $stmt->fetch();

        if ($ticket) {
            $progressIndex = cdaMarketingProgressIndex($ticket['estado']);
            $isTicketFinished = in_array($ticket['estado'], ['Entregado', 'Cerrado'], true);
            $histStmt = cdaDb()->prepare('SELECT estado, comentario, creado_en FROM marketing_ticket_historial WHERE ticket_id = ? ORDER BY creado_en DESC');
            $histStmt->execute([$ticket['id']]);
            $historial = $histStmt->fetchAll();

            $fileStmt = cdaDb()->prepare('SELECT id, nombre_original, ruta FROM marketing_ticket_archivos WHERE ticket_id = ? ORDER BY creado_en ASC');
            $fileStmt->execute([$ticket['id']]);
            $archivos = $fileStmt->fetchAll();

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'chat') {
                cdaRequirePostCsrf();
                $mensajeChat = cdaMarketingClean($_POST['mensaje'] ?? '');
                $hasChatFiles = $currentUser && cdaMarketingCanUploadChatFiles($currentUser['rol']) && !empty($_FILES['archivos']['name']) && is_array($_FILES['archivos']['name']) && count(array_filter($_FILES['archivos']['name'])) > 0;
                $canChat = $currentUser && (cdaMarketingCanManageTickets($currentUser['rol']) || strcasecmp($currentUser['correo'], $ticket['correo']) === 0);

                if (!$currentUser) {
                    $chatError = 'Inicia sesion con el usuario del ticket para enviar mensajes.';
                } elseif (!$canChat) {
                    $chatError = 'Tu usuario no coincide con el correo de este ticket.';
                } elseif ($mensajeChat === '' && !$hasChatFiles) {
                    $chatError = 'Escribe un mensaje para enviarlo al equipo.';
                } else {
                    try {
                        $db = cdaDb();
                        $db->beginTransaction();

                        $insert = $db->prepare(
                            'INSERT INTO marketing_ticket_mensajes (ticket_id, usuario_id, autor_nombre, autor_rol, mensaje)
                            VALUES (?, ?, ?, ?, ?)'
                        );
                        $authorRole = cdaMarketingCanManageTickets($currentUser['rol']) ? 'admin' : 'usuario';
                        $messageText = $mensajeChat !== '' ? $mensajeChat : 'Archivo enviado para este ticket.';
                        $insert->execute([$ticket['id'], $currentUser['id'], $currentUser['nombre'], $authorRole, $messageText]);
                        $messageId = (int) $db->lastInsertId();
                        $savedFiles = [];
                        if ($hasChatFiles) {
                            $savedFiles = cdaMarketingStoreMessageFiles($ticket, $messageId, $_FILES['archivos']);
                        }

                        $touch = $db->prepare('UPDATE marketing_tickets SET actualizado_en = CURRENT_TIMESTAMP WHERE id = ?');
                        $touch->execute([$ticket['id']]);

                        $db->commit();
                        if (cdaMarketingCanManageTickets($currentUser['rol'])) {
                            cdaMarketingSendChatEmail($ticket, $currentUser['nombre'], $messageText, $savedFiles);
                        } else {
                            cdaMarketingSendChatAdminEmail($ticket, $currentUser['nombre'], $messageText, $savedFiles);
                        }
                        header('Location: seguimiento.php?folio=' . urlencode($ticket['folio']) . '#chat');
                        exit;
                    } catch (Throwable $e) {
                        if (isset($db) && $db->inTransaction()) {
                            $db->rollBack();
                        }
                        $chatError = 'No fue posible enviar el mensaje. Revisa que la tabla de chat este instalada.';
                    }
                }
            }

            $mensajes = cdaMarketingFetchTicketMessages([(int) $ticket['id']])[(int) $ticket['id']] ?? [];
            if ($isTicketFinished) {
                foreach ($mensajes as $mensaje) {
                    if (!empty($mensaje['archivos'])) {
                        foreach ($mensaje['archivos'] as $file) {
                            $deliveryFiles[] = $file;
                        }
                    }
                }
            }
        } else {
            $error = 'No encontramos un ticket con ese folio.';
        }
    } catch (Throwable $e) {
        $error = 'No fue posible consultar el seguimiento en este momento.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento de ticket | Marketing</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="/contigo/Img/favicon.svg">
    <style>
        :root {
            --blue:#063970;
            --blue-2:#0b4f92;
            --ink:#10213f;
            --muted:#6b7890;
            --line:rgba(6,57,112,.14);
            --glass:rgba(255,255,255,.1);
            --soft:#f4f8fc;
            --yellow:#f6eb17;
            --radius:8px;
            --shadow:0 28px 80px rgba(0,0,0,.24);
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
        .shell { width:min(1160px, calc(100% - 2rem)); margin:0 auto; padding:1.1rem 0 3rem; position:relative; z-index:2; }
        .ambient-points { position:fixed; inset:0; overflow:hidden; pointer-events:none; z-index:1; }
        .ambient-point { --size:2px; --x:50vw; --y:50vh; --dx:18px; --dy:-22px; --duration:18s; --delay:0s; position:absolute; left:var(--x); top:var(--y); width:var(--size); height:var(--size); border-radius:50%; background:rgba(255,255,255,.55); box-shadow:0 0 calc(var(--size) * 4) rgba(166,205,255,.28); opacity:.42; transform:translate3d(0,0,0); animation:ambientDrift var(--duration) ease-in-out var(--delay) infinite alternate; }
        @keyframes ambientDrift { 0% { transform:translate3d(0,0,0); opacity:.18; } 38% { opacity:.56; } 100% { transform:translate3d(var(--dx), var(--dy), 0); opacity:.32; } }
        .topbar { display:flex; align-items:center; justify-content:space-between; gap:1rem; min-height:64px; color:#fff; margin-bottom:clamp(2rem,5vw,3.8rem); }
        .topbar img { width:140px; display:block; filter:drop-shadow(0 10px 18px rgba(0,0,0,.18)); }
        .nav { display:flex; flex-wrap:wrap; gap:.55rem; align-items:center; justify-content:flex-end; }
        .nav a {
            min-height:40px;
            display:inline-flex;
            align-items:center;
            color:rgba(255,255,255,.86);
            text-decoration:none;
            border:1px solid rgba(255,255,255,.2);
            border-radius:var(--radius);
            padding:.62rem .78rem;
            background:rgba(255,255,255,.08);
            font-size:.8rem;
            font-weight:850;
            white-space:nowrap;
            transition:background .18s ease, color .18s ease, border-color .18s ease, box-shadow .18s ease;
        }
        .nav a:hover { background:rgba(255,255,255,.15); color:#fff; border-color:rgba(255,255,255,.34); }
        .nav a.active { background:rgba(255,255,255,.18); color:#fff; border-color:rgba(246,235,23,.48); box-shadow:none; }
        .nav a.primary { background:var(--yellow); color:var(--blue); border-color:rgba(246,235,23,.65); }
        .nav a:focus-visible { outline:3px solid rgba(246,235,23,.52); outline-offset:2px; }
        .profile-menu { position:relative; }
        .profile-menu summary { min-height:40px; display:inline-flex; align-items:center; gap:.5rem; list-style:none; border:1px solid rgba(255,255,255,.22); border-radius:var(--radius); padding:.62rem .78rem; color:#fff; background:rgba(255,255,255,.1); font-size:.8rem; font-weight:850; cursor:pointer; }
        .profile-menu summary::-webkit-details-marker { display:none; }
        .profile-menu summary::after { content:""; width:.45rem; height:.45rem; border-right:2px solid currentColor; border-bottom:2px solid currentColor; transform:rotate(45deg) translateY(-2px); opacity:.75; }
        .profile-menu[open] summary { background:rgba(255,255,255,.16); border-color:rgba(255,255,255,.36); }
        .profile-menu.role-usuario summary { border-color:rgba(246,235,23,.82); box-shadow:0 0 0 1px rgba(246,235,23,.18); }
        .profile-menu.role-admin summary { border-color:rgba(248,113,113,.9); box-shadow:0 0 0 1px rgba(248,113,113,.2); }
        .profile-menu.role-trabajador summary { border-color:rgba(34,197,94,.88); box-shadow:0 0 0 1px rgba(34,197,94,.18); }
        .profile-menu.role-manager summary { border-color:rgba(96,165,250,.88); box-shadow:0 0 0 1px rgba(96,165,250,.18); }
        .profile-dropdown { position:absolute; right:0; top:calc(100% + .45rem); z-index:10; display:grid; min-width:190px; padding:.45rem; border:1px solid rgba(6,57,112,.12); border-radius:var(--radius); background:#fff; box-shadow:0 18px 40px rgba(0,0,0,.18); }
        .profile-dropdown a { min-height:36px; justify-content:flex-start; border:0; border-radius:6px; padding:.55rem .65rem; background:#fff; color:var(--ink); box-shadow:none; }
        .profile-dropdown a:hover { background:var(--soft); color:var(--blue); }
        .profile-dropdown a.logout-link { color:#991b1b; }
        .hero-grid { display:grid; grid-template-columns:minmax(0, .95fr) minmax(330px, .55fr); gap:clamp(1rem,4vw,2.4rem); align-items:end; margin-bottom:1rem; }
        .hero { color:#fff; }
        .eyebrow { color:var(--yellow); font-size:.78rem; font-weight:950; letter-spacing:.13em; text-transform:uppercase; margin-bottom:.8rem; }
        .hero h1 { max-width:710px; font-size:clamp(2.4rem,7vw,5.2rem); line-height:.95; letter-spacing:0; margin-bottom:1rem; font-weight:900; }
        .hero p { max-width:680px; color:rgba(255,255,255,.78); line-height:1.7; font-size:1.03rem; }
        .signal-card {
            border:1px solid rgba(255,255,255,.2);
            border-radius:var(--radius);
            padding:1rem;
            background:rgba(255,255,255,.09);
            backdrop-filter:blur(16px);
            color:#fff;
            box-shadow:0 18px 50px rgba(0,0,0,.16);
        }
        .signal-card h2 { font-size:1rem; margin-bottom:.85rem; }
        .signal-list { display:grid; gap:.65rem; }
        .signal-list div { display:grid; grid-template-columns:30px 1fr; gap:.65rem; align-items:center; color:rgba(255,255,255,.78); font-size:.86rem; line-height:1.35; }
        .signal-list span { width:30px; height:30px; border-radius:50%; display:grid; place-items:center; background:rgba(246,235,23,.16); color:var(--yellow); font-weight:950; }
        .search-card, .card {
            background:linear-gradient(180deg, rgba(255,255,255,.98), rgba(248,251,255,.95));
            border:1px solid rgba(255,255,255,.24);
            border-radius:var(--radius);
            padding:clamp(1rem,3vw,1.55rem);
            box-shadow:var(--shadow);
        }
        .search-head { display:flex; align-items:start; justify-content:space-between; gap:1rem; margin-bottom:1rem; }
        .search-head h2, .side h2, .main h2 { color:var(--blue); font-size:1.1rem; line-height:1.15; }
        .search-head p { color:var(--muted); line-height:1.55; margin-top:.35rem; }
        .mini-badge { border:1px solid var(--line); border-radius:999px; padding:.45rem .65rem; color:var(--blue); background:var(--soft); font-size:.72rem; font-weight:950; white-space:nowrap; }
        form { display:grid; grid-template-columns:1fr auto; gap:.85rem; align-items:end; }
        label { display:grid; gap:.42rem; color:var(--ink); font-size:.78rem; font-weight:900; }
        input, textarea {
            width:100%;
            border:1px solid var(--line);
            border-radius:var(--radius);
            padding:.92rem .95rem;
            font:inherit;
            color:var(--ink);
            background:#fff;
            outline:none;
            transition:border-color .2s ease, box-shadow .2s ease;
        }
        textarea { min-height:84px; resize:vertical; }
        input:focus, textarea:focus { border-color:var(--blue); box-shadow:0 0 0 4px rgba(6,57,112,.1); }
        button, .button {
            min-height:48px;
            border:0;
            border-radius:var(--radius);
            padding:.9rem 1rem;
            background:var(--yellow);
            color:var(--blue);
            font-weight:950;
            letter-spacing:.04em;
            text-transform:uppercase;
            cursor:pointer;
            text-decoration:none;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            box-shadow:0 14px 28px rgba(6,57,112,.16);
        }
        .error { margin-top:1rem; color:#b91c1c; background:#fee2e2; border:1px solid #fecaca; border-radius:var(--radius); padding:.85rem; font-weight:800; }
        .ticket { display:grid; grid-template-columns:minmax(0,1fr) 330px; gap:1rem; margin-top:1rem; }
        .ticket-head { display:flex; align-items:start; justify-content:space-between; gap:1rem; margin-bottom:.75rem; }
        .ticket-title { display:grid; gap:.35rem; }
        .folio { color:var(--muted); font-size:.78rem; font-weight:900; letter-spacing:.08em; text-transform:uppercase; }
        .status { display:inline-flex; width:fit-content; border-radius:999px; padding:.46rem .72rem; background:var(--blue); color:#fff; font-size:.78rem; font-weight:950; white-space:nowrap; box-shadow:0 10px 20px rgba(6,57,112,.14); }
        .status.rechazado { background:#991b1b; }
        .status.cerrado, .status.entregado { background:#047857; }
        .progress { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:.45rem; margin:1rem 0 .25rem; }
        .progress-step { min-height:78px; position:relative; border:1px solid var(--line); border-radius:var(--radius); background:#fff; padding:.74rem .58rem; color:var(--muted); font-size:.76rem; font-weight:900; text-align:center; overflow:hidden; }
        .progress-step::before { content:""; display:block; width:18px; height:18px; border-radius:50%; margin:0 auto .42rem; background:#dbe5f0; box-shadow:0 0 0 5px rgba(219,229,240,.35); }
        .progress-step.done { border-color:rgba(29,78,216,.24); background:linear-gradient(180deg,#fff,#eff6ff); color:#1d4ed8; }
        .progress-step.done::before { background:#1d4ed8; box-shadow:0 0 0 5px rgba(29,78,216,.13); }
        .progress-step.current { border-color:#f59e0b; background:linear-gradient(180deg,#fff7ed,#fff); color:#9a3412; box-shadow:0 0 0 1px rgba(245,158,11,.12), 0 16px 28px rgba(245,158,11,.14); }
        .progress-step.current::before { background:#f59e0b; animation:currentPulse 1.1s ease-in-out infinite; }
        .progress-step.finalized { border-color:rgba(4,120,87,.24); background:linear-gradient(180deg,#ecfdf5,#fff); color:#047857; }
        .progress-step.finalized::before { background:#047857; box-shadow:0 0 0 5px rgba(4,120,87,.14); }
        @keyframes currentPulse { 0%,100% { box-shadow:0 0 0 4px rgba(245,158,11,.2); transform:scale(1); } 50% { box-shadow:0 0 0 10px rgba(245,158,11,.05); transform:scale(1.08); } }
        .completion-box { margin-top:1rem; border:1px solid rgba(4,120,87,.2); border-left:5px solid #047857; border-radius:var(--radius); background:linear-gradient(180deg,#ecfdf5,#fff); padding:1rem; color:#065f46; line-height:1.55; box-shadow:0 16px 36px rgba(4,120,87,.1); }
        .completion-box strong { display:block; margin-bottom:.25rem; color:#047857; font-size:1rem; }
        .meta { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.72rem; margin-top:1rem; }
        .meta div, .files li {
            border:1px solid var(--line);
            border-radius:var(--radius);
            background:var(--soft);
            padding:.78rem;
            list-style:none;
        }
        .meta span { display:block; color:var(--muted); font-size:.76rem; margin-bottom:.2rem; font-weight:800; }
        .meta strong { color:var(--ink); }
        .response-box { margin-top:1rem; border-left:4px solid var(--yellow); border-radius:var(--radius); background:#fffbea; padding:.9rem; line-height:1.6; color:#3d3a12; }
        .timeline { display:grid; gap:.75rem; margin-top:.85rem; list-style:none; }
        .timeline li {
            position:relative;
            border:1px solid var(--line);
            border-radius:var(--radius);
            background:#fff;
            padding:.85rem .85rem .85rem 2.2rem;
            line-height:1.45;
        }
        .timeline li::before {
            content:"";
            position:absolute;
            left:.8rem;
            top:1rem;
            width:.62rem;
            height:.62rem;
            border-radius:50%;
            background:var(--yellow);
            box-shadow:0 0 0 5px rgba(246,235,23,.22);
        }
        .timeline small { color:var(--muted); display:block; margin-top:.3rem; font-weight:750; }
        .files { display:grid; gap:.55rem; margin-top:.8rem; list-style:none; }
        .files a { color:var(--blue); font-weight:900; text-decoration:none; }
        .chat-box { margin-top:1.1rem; display:grid; gap:.85rem; border:1px solid rgba(6,57,112,.1); border-radius:var(--radius); background:linear-gradient(180deg,#fbfdff,#fff); padding:1rem; box-shadow:0 16px 36px rgba(6,57,112,.08); }
        .chat-intro { color:var(--muted); line-height:1.55; font-size:.94rem; }
        .chat-thread { display:grid; gap:.7rem; max-height:460px; overflow:auto; padding:.2rem .35rem .2rem 0; }
        .chat-message { border:1px solid rgba(6,57,112,.1); border-left:5px solid var(--blue-2); border-radius:var(--radius); background:#fff; padding:.9rem 1rem; box-shadow:0 10px 24px rgba(6,57,112,.07); }
        .chat-message.admin { border-left-color:#f59e0b; background:#fffaf0; }
        .chat-message.usuario { border-left-color:#047857; background:#fbfffd; }
        .chat-meta { display:flex; justify-content:space-between; gap:.75rem; color:var(--muted); font-size:.76rem; font-weight:900; }
        .chat-message p { margin-top:.42rem; line-height:1.65; color:var(--ink); font-size:.96rem; }
        .chat-files { display:grid; gap:.4rem; margin-top:.55rem; }
        .chat-file { display:inline-flex; width:fit-content; border-radius:6px; padding:.38rem .55rem; background:#eef4fb; color:var(--blue); font-size:.78rem; font-weight:850; text-decoration:none; }
        .chat-form { display:grid; grid-template-columns:1fr; gap:.78rem; border:1px solid var(--line); border-radius:var(--radius); background:var(--soft); padding:1rem; }
        .chat-login { border:1px solid var(--line); border-radius:var(--radius); background:var(--soft); padding:.85rem; color:var(--muted); line-height:1.55; }
        .empty-state {
            margin-top:1rem;
            border:1px solid rgba(255,255,255,.16);
            border-radius:var(--radius);
            padding:1rem;
            color:rgba(255,255,255,.75);
            background:rgba(255,255,255,.08);
        }
        @media (max-width:860px) {
            .hero-grid, form, .ticket { grid-template-columns:1fr; }
            .signal-card { max-width:620px; }
        }
        @media (max-width:620px) {
            .shell { width:min(100% - 1rem, 1160px); }
            .topbar, .search-head, .ticket-head { align-items:flex-start; flex-direction:column; }
            .nav { justify-content:flex-start; }
            .profile-dropdown { left:0; right:auto; }
            .meta { grid-template-columns:1fr; }
            .progress { grid-template-columns:1fr; }
            .progress-step { min-height:auto; display:flex; align-items:center; gap:.5rem; text-align:left; }
            .progress-step::before { margin:0; flex:0 0 auto; }
            button { width:100%; }
        }
        @media (prefers-reduced-motion:reduce) { .ambient-point, .progress-step.current::before { animation:none; } }
    </style>
</head>
<body>
    <div class="ambient-points" aria-hidden="true"></div>
    <div class="shell">
        <header class="topbar">
            <a href="index.html"><img src="img/cda-logo-f.svg" alt="Central de Alarmas"></a>
            <nav class="nav" aria-label="Navegacion">
                <?php if ($currentUser): ?>
                    <?php if (cdaMarketingCanViewAllTickets($currentUser['rol'])): ?><a href="estadisticas-marketing.php">Estadísticas</a><?php endif; ?>
                    <?php if (cdaMarketingCanViewAllTickets($currentUser['rol'])): ?><a href="panel-marketing.php">Tickets</a><?php endif; ?>
                    <?php if (cdaMarketingCanAccessBoard($currentUser['rol'])): ?><a href="control-marketing.php">Tablero</a><?php endif; ?>
                    <?php if (cdaMarketingCanManageUsers($currentUser['rol'])): ?><a href="usuarios-marketing.php">Usuarios</a><?php endif; ?>
                    <?php if (cdaMarketingCanManageTrash($currentUser['rol'])): ?><a href="panel-marketing.php?papelera=1">Basurero</a><?php endif; ?>
                    <a href="crear-ticket.php">Crear ticket</a>
                    <a class="active" href="seguimiento.php">Seguimiento</a>
                    <details class="profile-menu role-<?php echo htmlspecialchars(cdaMarketingRoleClass($currentUser['rol'])); ?>">
                        <summary><?php echo htmlspecialchars($currentUser['nombre']); ?> · <?php echo htmlspecialchars(cdaMarketingRoleLabel($currentUser['rol'])); ?></summary>
                        <div class="profile-dropdown">
                            <a href="perfil-marketing.php">Mi perfil</a>
                            <a href="perfil-marketing.php#configuracion">Configuración</a>
                            <a class="logout-link" href="logout.php">Cerrar sesión</a>
                        </div>
                    </details>
                <?php else: ?>
                    <a href="crear-ticket.php">Crear ticket</a>
                    <a class="active" href="seguimiento.php">Seguimiento</a>
                    <a class="primary" href="login.php?return_to=estadisticas-marketing.php">Iniciar sesion</a>
                <?php endif; ?>
            </nav>
        </header>

        <section class="hero-grid">
            <div class="hero">
                <div class="eyebrow">Portal de Marketing</div>
                <h1>Del folio al cierre, sin perder contexto.</h1>
                <p>Consulta el estado de tu ticket, revisa el historial y usa el chat para aclarar materiales, aprobar ajustes o dejar comentarios junto a la solicitud.</p>
            </div>
            <aside class="signal-card" aria-label="Estados del proceso">
                <h2>Flujo visible</h2>
                <div class="signal-list">
                    <div><span>1</span>Solicitud recibida con usuario y folio.</div>
                    <div><span>2</span>Evaluacion de alcance, fecha y materiales.</div>
                    <div><span>3</span>Chat abierto para dudas, aprobaciones y ajustes.</div>
                    <div><span>4</span>Produccion, revision, entrega y cierre.</div>
                </div>
            </aside>
        </section>

        <section class="search-card">
            <div class="search-head">
                <div>
                    <h2>Buscar solicitud</h2>
                    <p>Usa el folio que recibiste al crear la solicitud. Para escribir en el chat, inicia sesion con el correo del ticket.</p>
                </div>
                <span class="mini-badge">Consulta por folio</span>
            </div>
            <form method="get" action="seguimiento.php">
                <label>Folio <input name="folio" value="<?php echo htmlspecialchars($folio); ?>" placeholder="MKT-20260721-0001-A1B2" required></label>
                <button type="submit">Consultar</button>
            </form>
            <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        </section>

        <?php if ($ticket): ?>
        <section class="ticket">
            <article class="card main">
                <div class="ticket-head">
                    <div class="ticket-title">
                        <span class="folio"><?php echo htmlspecialchars($ticket['folio']); ?></span>
                        <h2><?php echo htmlspecialchars($ticket['actividad']); ?></h2>
                    </div>
                    <span class="status <?php echo htmlspecialchars(cdaMarketingStatusClass($ticket['estado'])); ?>"><?php echo htmlspecialchars(cdaMarketingStatusLabel($ticket['estado'])); ?></span>
                </div>
                <div class="progress" aria-label="Progreso del ticket">
                    <?php $stepIndex = 0; foreach (cdaMarketingProgressSteps() as $stepStatus => $stepLabel): ?>
                        <?php
                            $stepClass = 'progress-step';
                            if ($isTicketFinished) {
                                $stepClass .= ' finalized';
                            } elseif ($stepIndex < $progressIndex) {
                                $stepClass .= ' done';
                            } elseif ($stepIndex === $progressIndex) {
                                $stepClass .= ' current';
                            }
                        ?>
                        <div class="<?php echo htmlspecialchars($stepClass); ?>"><?php echo htmlspecialchars($stepLabel); ?></div>
                    <?php $stepIndex++; endforeach; ?>
                </div>
                <?php if ($isTicketFinished): ?>
                    <div class="completion-box">
                        <strong>Ticket finalizado</strong>
                        La solicitud quedó cerrada en el flujo. Si hubo archivos de entrega adjuntos en el chat, aparecen también en el apartado de entrega.
                    </div>
                <?php endif; ?>
                <div class="meta">
                    <div><span>Tipo</span><strong><?php echo htmlspecialchars($ticket['tipo_solicitud']); ?></strong></div>
                    <div><span>Prioridad</span><strong><?php echo htmlspecialchars($ticket['prioridad']); ?></strong></div>
                    <div><span>Fecha requerida</span><strong><?php echo htmlspecialchars(cdaMarketingFormatDate($ticket['fecha_requerida'])); ?></strong></div>
                    <div><span>Entrega aproximada</span><strong><?php echo htmlspecialchars(cdaMarketingFormatDate($ticket['fecha_entrega_estimada'] ?? null)); ?></strong></div>
                    <div><span>Solicitante</span><strong><?php echo htmlspecialchars($ticket['solicitante']); ?></strong></div>
                    <div><span>Area</span><strong><?php echo htmlspecialchars($ticket['departamento']); ?></strong></div>
                </div>
                <?php if (!empty($ticket['respuesta_interna'])): ?>
                    <div class="response-box"><?php echo nl2br(htmlspecialchars($ticket['respuesta_interna'])); ?></div>
                <?php endif; ?>
                <section class="chat-box" id="chat" aria-label="Chat del ticket">
                    <h2>Chat de seguimiento</h2>
                    <p class="chat-intro">Este espacio queda ligado a tu folio. Usalo para agregar contexto, responder preguntas del equipo o confirmar avances sin perder la historia del ticket.</p>
                    <div class="chat-thread">
                        <?php foreach ($mensajes as $mensaje): ?>
                            <article class="chat-message <?php echo htmlspecialchars($mensaje['autor_rol']); ?>">
                                <div class="chat-meta">
                                    <strong><?php echo htmlspecialchars(cdaMarketingMessageAuthor($mensaje)); ?></strong>
                                    <span><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($mensaje['creado_en']))); ?></span>
                                </div>
                                <p><?php echo nl2br(htmlspecialchars($mensaje['mensaje'])); ?></p>
                                <?php if (!empty($mensaje['archivos'])): ?>
                                    <div class="chat-files">
                                        <?php foreach ($mensaje['archivos'] as $file): ?>
                                            <a class="chat-file" href="descargar-archivo.php?tipo=mensaje&id=<?php echo (int) $file['id']; ?>"><?php echo htmlspecialchars($file['nombre_original']); ?></a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                        <?php if (!$mensajes): ?><p class="muted">Aun no hay mensajes. Cuando el equipo necesite una aclaracion, aparecera aqui junto al folio.</p><?php endif; ?>
                    </div>
                    <?php if ($chatError): ?><div class="error"><?php echo htmlspecialchars($chatError); ?></div><?php endif; ?>
                    <?php $canUseChat = $currentUser && (cdaMarketingCanManageTickets($currentUser['rol']) || strcasecmp($currentUser['correo'], $ticket['correo']) === 0); ?>
                    <?php if ($canUseChat): ?>
                        <form class="chat-form" method="post" action="seguimiento.php#chat" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(cdaCsrfToken()); ?>">
                            <input type="hidden" name="action" value="chat">
                            <input type="hidden" name="folio" value="<?php echo htmlspecialchars($ticket['folio']); ?>">
                            <label>Mensaje <textarea name="mensaje" placeholder="Escribe tu respuesta para el equipo"></textarea></label>
                            <label>Archivos <input name="archivos[]" type="file" multiple accept="<?php echo htmlspecialchars(cdaMarketingAllowedUploadAccept()); ?>"></label>
                            <button type="submit">Enviar mensaje</button>
                        </form>
                    <?php else: ?>
                        <div class="chat-login">
                            Inicia sesion con el correo del ticket para escribir en el chat.
                            <br><a class="button" href="login.php?return_to=<?php echo urlencode('seguimiento.php?folio=' . $ticket['folio'] . '#chat'); ?>">Iniciar sesion</a>
                        </div>
                    <?php endif; ?>
                </section>
            </article>
            <aside class="card side">
                <h2>Historial</h2>
                <ul class="timeline">
                    <?php foreach ($historial as $item): ?>
                    <li><strong><?php echo htmlspecialchars(cdaMarketingStatusLabel($item['estado'])); ?></strong><br><?php echo nl2br(htmlspecialchars($item['comentario'] ?? '')); ?><small><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($item['creado_en']))); ?></small></li>
                    <?php endforeach; ?>
                </ul>
                <?php if ($archivos): ?>
                <h2 style="margin-top:1rem;">Archivos iniciales</h2>
                <ul class="files">
                    <?php foreach ($archivos as $archivo): ?>
                    <li><a href="descargar-archivo.php?tipo=ticket&id=<?php echo (int) $archivo['id']; ?>"><?php echo htmlspecialchars($archivo['nombre_original']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
                <?php if ($deliveryFiles): ?>
                <h2 style="margin-top:1rem;">Archivos de entrega</h2>
                <ul class="files">
                    <?php foreach ($deliveryFiles as $archivo): ?>
                    <li><a href="descargar-archivo.php?tipo=mensaje&id=<?php echo (int) $archivo['id']; ?>"><?php echo htmlspecialchars($archivo['nombre_original']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </aside>
        </section>
        <?php else: ?>
        <div class="empty-state">
            Ten a la mano tu folio. Si acabas de crear una solicitud, revisa el mensaje de confirmación del formulario.
        </div>
        <?php endif; ?>
    </div>
    <script>
        (function () {
            var layer = document.querySelector('.ambient-points');
            if (!layer) return;

            var count = window.matchMedia('(max-width: 620px)').matches ? 54 : 86;
            var fragment = document.createDocumentFragment();

            for (var i = 0; i < count; i += 1) {
                var point = document.createElement('span');
                var size = (Math.random() * 2.8 + .9).toFixed(2);
                var x = (Math.random() * 100).toFixed(2);
                var y = (Math.random() * 100).toFixed(2);
                var dx = (Math.random() * 72 - 36).toFixed(2);
                var dy = (Math.random() * 72 - 36).toFixed(2);
                var duration = (Math.random() * 18 + 16).toFixed(2);
                var delay = (Math.random() * -24).toFixed(2);

                point.className = 'ambient-point';
                point.style.setProperty('--size', size + 'px');
                point.style.setProperty('--x', x + 'vw');
                point.style.setProperty('--y', y + 'vh');
                point.style.setProperty('--dx', dx + 'px');
                point.style.setProperty('--dy', dy + 'px');
                point.style.setProperty('--duration', duration + 's');
                point.style.setProperty('--delay', delay + 's');
                point.style.opacity = (Math.random() * .32 + .18).toFixed(2);
                fragment.appendChild(point);
            }

            layer.appendChild(fragment);
        }());
    </script>
</body>
</html>
