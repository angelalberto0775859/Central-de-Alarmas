<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/marketing_helpers.php';

$user = cdaRequireLogin();
$estado = cdaMarketingClean($_GET['estado'] ?? '');
$query = cdaMarketingClean($_GET['q'] ?? '');
$trashMode = $user['rol'] === 'admin' && ($_GET['papelera'] ?? '') === '1';
$params = [];
$where = [];

if ($user['rol'] !== 'admin') {
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

$stats = [
    'total' => 0,
    'urgent' => 0,
    'active' => 0,
    'done' => 0,
];
$statsParams = [];
$statsWhere = 'WHERE eliminado_en IS NULL';
if ($user['rol'] !== 'admin') {
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
    <title>Panel de tickets | Diseño y Marketing</title>
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
            background:linear-gradient(180deg, rgba(255,255,255,.08), transparent);
            mask-image:linear-gradient(180deg,#000,transparent);
            z-index:0;
        }
        body::after {
            content:"";
            position:fixed;
            inset:0;
            pointer-events:none;
            background:linear-gradient(120deg, transparent 0 40%, rgba(255,255,255,.08) 41%, transparent 44% 100%);
            opacity:.34;
            z-index:0;
        }
        .shell { width:min(1320px, calc(100% - 2rem)); margin:0 auto; padding:1.1rem 0 3rem; position:relative; z-index:2; }
        .ambient-points { position:fixed; inset:0; overflow:hidden; pointer-events:none; z-index:1; }
        .ambient-point { --size:2px; --x:50vw; --y:50vh; --dx:20px; --dy:-18px; --duration:24s; --delay:0s; position:absolute; left:var(--x); top:var(--y); width:var(--size); height:var(--size); border-radius:50%; background:rgba(255,255,255,.78); box-shadow:0 0 calc(var(--size) * 5) rgba(166,205,255,.45); opacity:.52; transform:translate3d(0,0,0); animation:ambientDrift var(--duration) ease-in-out var(--delay) infinite alternate; }
        @keyframes ambientDrift { 0% { transform:translate3d(0,0,0); opacity:.28; } 42% { opacity:.78; } 100% { transform:translate3d(var(--dx), var(--dy), 0); opacity:.42; } }
        .topbar {
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:1rem;
            margin-bottom:1.35rem;
            color:#fff;
            border:1px solid rgba(255,255,255,.16);
            border-radius:var(--radius);
            padding:.7rem;
            background:rgba(255,255,255,.08);
            backdrop-filter:blur(14px);
            box-shadow:0 18px 50px rgba(0,0,0,.14);
        }
        .topbar img { width:146px; display:block; filter:drop-shadow(0 10px 18px rgba(0,0,0,.18)); }
        .nav { display:flex; flex-wrap:wrap; gap:.6rem; align-items:center; }
        .nav a, button {
            min-height:40px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            color:var(--blue);
            text-decoration:none;
            border:1px solid rgba(255,255,255,.22);
            border-radius:8px;
            padding:.72rem .88rem;
            background:#fff;
            font-size:.82rem;
            font-weight:850;
            cursor:pointer;
            white-space:nowrap;
            transition:background .18s ease, color .18s ease, border-color .18s ease, transform .18s ease, box-shadow .18s ease;
        }
        .nav a { background:rgba(255,255,255,.12); color:#fff; border-color:rgba(255,255,255,.26); }
        .nav a:hover, .nav a.active { background:var(--yellow); color:var(--blue); border-color:var(--yellow); }
        .nav a.admin-link { border-color:rgba(246,235,23,.48); }
        .nav a.session-link { border-color:rgba(254,202,202,.5); background:rgba(185,28,28,.2); }
        .nav a.admin-link::before { display:inline-block; margin-right:.42rem; border-radius:999px; padding:.2rem .42rem; font-size:.62rem; line-height:1; letter-spacing:.04em; vertical-align:middle; }
        .nav a.admin-link::before { content:"ADMIN"; background:var(--yellow); color:var(--blue); }
        .user-chip { color:#fff; font-weight:850; opacity:.9; }
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
            padding:1rem;
            box-shadow:var(--shadow);
        }
        .card-head { display:flex; justify-content:space-between; align-items:start; gap:1rem; margin-bottom:.9rem; }
        .card-head h2 { color:var(--blue); font-size:1.05rem; }
        .table-shell { overflow:auto; border:1px solid rgba(6,57,112,.08); border-radius:var(--radius); background:#fff; }
        .filters {
            display:grid;
            grid-template-columns:1fr 240px auto;
            gap:.75rem;
            margin-bottom:1rem;
            padding:.8rem;
            border:1px solid var(--line);
            border-radius:var(--radius);
            background:#f8fbff;
        }
        input, select, textarea {
            width:100%;
            border:1px solid var(--line);
            border-radius:var(--radius);
            padding:.82rem .9rem;
            font:inherit;
            background:#fff;
            color:var(--ink);
            outline:none;
        }
        input:focus, select:focus, textarea:focus { border-color:var(--blue-2); box-shadow:0 0 0 4px rgba(6,57,112,.09); }
        table { width:100%; border-collapse:separate; border-spacing:0; min-width:980px; }
        th, td { padding:.9rem .78rem; border-bottom:1px solid var(--line); text-align:left; vertical-align:top; font-size:.88rem; }
        th { color:var(--blue); font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; background:#f3f8fd; }
        th:first-child { border-radius:var(--radius) 0 0 var(--radius); }
        th:last-child { border-radius:0 var(--radius) var(--radius) 0; }
        tr:hover td { background:#fbfdff; }
        .folio { font-weight:950; color:var(--blue); letter-spacing:.02em; }
        .request-title { color:var(--ink); font-weight:900; }
        .status { display:inline-flex; border-radius:999px; padding:.38rem .62rem; background:var(--soft); color:var(--blue); font-size:.75rem; font-weight:900; white-space:nowrap; }
        .status.entregado, .status.cerrado { background:#d1fae5; color:var(--green); }
        .status.rechazado { background:#fee2e2; color:#991b1b; }
        .status.en-diseno, .status.en-revision { background:#dbeafe; color:#1d4ed8; }
        .status.pendiente-de-informacion, .status.ajustes-solicitados { background:#fff7cc; color:#7c5800; }
        .priority { display:inline-flex; margin-top:.42rem; border-radius:999px; padding:.34rem .55rem; background:#eef4fb; color:var(--ink); font-size:.72rem; font-weight:900; }
        .priority.urgente { background:#fee2e2; color:var(--red); }
        .priority.alta { background:#fff7cc; color:#7c5800; }
        .inline-form { display:grid; gap:.5rem; min-width:280px; padding:.65rem; border:1px solid rgba(6,57,112,.08); border-radius:var(--radius); background:#fbfdff; }
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
        .ticket-actions { display:grid; gap:.5rem; margin-top:.6rem; }
        .muted { color:var(--muted); }
        .empty-state { padding:2rem; text-align:center; color:var(--muted); }
        .ticket-chat { display:grid; gap:.58rem; margin-top:.65rem; padding:.68rem; border:1px solid rgba(6,57,112,.1); border-radius:var(--radius); background:#f8fbff; }
        .ticket-chat h3 { color:var(--blue); font-size:.78rem; text-transform:uppercase; letter-spacing:.05em; }
        .chat-note { color:var(--muted); font-size:.72rem; line-height:1.35; }
        .chat-thread { display:grid; gap:.45rem; max-height:170px; overflow:auto; padding-right:.2rem; }
        .chat-message { border-left:3px solid var(--blue-2); padding:.48rem .55rem; border-radius:0 6px 6px 0; background:#fff; }
        .chat-message.admin { border-left-color:var(--yellow); }
        .chat-message.usuario { border-left-color:var(--green); }
        .chat-meta { display:flex; justify-content:space-between; gap:.5rem; color:var(--muted); font-size:.68rem; font-weight:850; }
        .chat-message p { margin-top:.24rem; color:var(--ink); font-size:.8rem; line-height:1.45; }
        .chat-form { display:grid; gap:.42rem; }
        @media (max-width:900px) {
            body { background:linear-gradient(180deg,#061b39 0 390px,#eef5fb 390px,#f7faff 100%); }
            .filters, .hero, .stats, .story-strip { grid-template-columns:1fr; flex-direction:column; align-items:stretch; }
            .topbar { align-items:flex-start; flex-direction:column; }
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
                <span class="user-chip"><?php echo htmlspecialchars($user['nombre']); ?></span>
                <?php if ($user['rol'] === 'admin'): ?><a class="admin-link <?php echo $trashMode ? '' : 'active'; ?>" href="panel-marketing.php">Tickets</a><?php endif; ?>
                <?php if ($user['rol'] !== 'admin'): ?><a class="active" href="panel-marketing.php">Tickets</a><?php endif; ?>
                <?php if ($user['rol'] === 'admin'): ?><a class="admin-link" href="control-marketing.php">Tablero</a><?php endif; ?>
                <?php if ($user['rol'] === 'admin'): ?><a class="admin-link" href="usuarios-marketing.php">Usuarios</a><?php endif; ?>
                <?php if ($user['rol'] === 'admin'): ?><a class="admin-link <?php echo $trashMode ? 'active' : ''; ?>" href="panel-marketing.php?papelera=1">Basurero</a><?php endif; ?>
                <a class="public-link" href="marketing.html">Crear ticket</a>
                <a class="public-link" href="seguimiento.php">Seguimiento</a>
                <a class="session-link" href="logout.php">Salir</a>
            </nav>
        </header>
        <section class="hero">
            <div>
                <div class="eyebrow"><?php echo $trashMode ? 'Basurero admin' : ($user['rol'] === 'admin' ? 'Vista general' : 'Mis solicitudes'); ?></div>
                <h1><?php echo $trashMode ? 'Tickets en basurero' : 'Tickets de Diseño y Marketing'; ?></h1>
                <p class="muted"><?php echo $trashMode ? 'Restaura solicitudes enviadas al basurero o borralas definitivamente cuando ya no se necesiten.' : 'Cada solicitud vive por folio: registro validado, estado visible, conversacion del ticket y cierre con historial.'; ?></p>
            </div>
        </section>
        <section class="story-strip" aria-label="Historia del ticket">
            <div class="story-step"><span>1</span><strong>Registro</strong><p>El correo crea o actualiza el usuario ligado a la solicitud.</p></div>
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
            <div class="table-shell">
                <table>
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Solicitud</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Actualizar</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                    <tr id="ticket-<?php echo (int) $ticket['id']; ?>">
                        <td><div class="folio"><?php echo htmlspecialchars($ticket['folio']); ?></div><div class="muted"><?php echo htmlspecialchars($ticket['solicitante']); ?><br><?php echo htmlspecialchars($ticket['correo']); ?></div></td>
                        <td><strong class="request-title"><?php echo htmlspecialchars($ticket['actividad']); ?></strong><br><span class="muted"><?php echo htmlspecialchars($ticket['tipo_solicitud']); ?> · <?php echo htmlspecialchars($ticket['departamento']); ?></span></td>
                        <td><span class="status <?php echo htmlspecialchars(cdaMarketingStatusClass($ticket['estado'])); ?>"><?php echo htmlspecialchars(cdaMarketingStatusLabel($ticket['estado'])); ?></span><br><span class="priority <?php echo htmlspecialchars(strtolower($ticket['prioridad'])); ?>"><?php echo htmlspecialchars($ticket['prioridad']); ?></span></td>
                        <td><?php echo htmlspecialchars(cdaMarketingFormatDate($ticket['fecha_requerida'])); ?><br><span class="muted">Actualizado <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($ticket['actualizado_en']))); ?></span></td>
                        <td>
                            <?php if (!$trashMode): ?>
                            <form class="inline-form" method="post" action="ticket-actualizar.php">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(cdaCsrfToken()); ?>">
                                <input type="hidden" name="id" value="<?php echo (int) $ticket['id']; ?>">
                                <select name="estado" required>
                                    <?php foreach (cdaMarketingStatuses() as $status): ?>
                                        <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $ticket['estado'] === $status ? 'selected' : ''; ?>><?php echo htmlspecialchars(cdaMarketingStatusLabel($status)); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input name="asignado_a" value="<?php echo htmlspecialchars($ticket['asignado_a'] ?? ''); ?>" placeholder="Asignado a">
                                <textarea name="respuesta_interna" rows="2" placeholder="Comentario visible para seguimiento"><?php echo htmlspecialchars($ticket['respuesta_interna'] ?? ''); ?></textarea>
                                <button type="submit">Guardar</button>
                            </form>
                            <?php endif; ?>
                            <?php if ($user['rol'] === 'admin'): ?>
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
                                        </article>
                                    <?php endforeach; ?>
                                    <?php if (empty($ticketMessages[(int) $ticket['id']])): ?><p class="muted">Aun no hay mensajes; escribe aqui si necesitas aclarar el brief o pedir aprobacion.</p><?php endif; ?>
                                </div>
                                <form class="chat-form" method="post" action="ticket-mensaje.php">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(cdaCsrfToken()); ?>">
                                    <input type="hidden" name="ticket_id" value="<?php echo (int) $ticket['id']; ?>">
                                    <input type="hidden" name="return_to" value="panel-marketing.php">
                                    <textarea name="mensaje" rows="2" required placeholder="Escribe un mensaje para este ticket"></textarea>
                                    <button type="submit">Enviar mensaje</button>
                                </form>
                            </section>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$tickets): ?>
                    <tr><td colspan="5" class="empty-state">No hay tickets con esos filtros.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
    <script>
        (function () {
            var layer = document.querySelector('.ambient-points');
            if (!layer) return;
            var count = window.matchMedia('(max-width: 620px)').matches ? 64 : 118;
            var fragment = document.createDocumentFragment();
            for (var i = 0; i < count; i += 1) {
                var point = document.createElement('span');
                point.className = 'ambient-point';
                point.style.setProperty('--size', (Math.random() * 3.2 + 1).toFixed(2) + 'px');
                point.style.setProperty('--x', (Math.random() * 100).toFixed(2) + 'vw');
                point.style.setProperty('--y', (Math.random() * 100).toFixed(2) + 'vh');
                point.style.setProperty('--dx', (Math.random() * 82 - 41).toFixed(2) + 'px');
                point.style.setProperty('--dy', (Math.random() * 82 - 41).toFixed(2) + 'px');
                point.style.setProperty('--duration', (Math.random() * 22 + 20).toFixed(2) + 's');
                point.style.setProperty('--delay', (Math.random() * -30).toFixed(2) + 's');
                point.style.opacity = (Math.random() * .4 + .28).toFixed(2);
                fragment.appendChild(point);
            }
            layer.appendChild(fragment);
        }());
    </script>
</body>
</html>
