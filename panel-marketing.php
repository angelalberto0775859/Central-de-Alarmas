<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/marketing_helpers.php';

$user = cdaRequireLogin();
$estado = cdaMarketingClean($_GET['estado'] ?? '');
$query = cdaMarketingClean($_GET['q'] ?? '');
$params = [];
$where = [];

if ($user['rol'] !== 'admin') {
    $where[] = 'correo = ?';
    $params[] = $user['correo'];
}

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
$statsWhere = '';
if ($user['rol'] !== 'admin') {
    $statsWhere = 'WHERE correo = ?';
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
            background:
                radial-gradient(circle at 1px 1px, rgba(255,255,255,.2) 1px, transparent 1.8px);
            background-size:26px 26px;
            mask-image:linear-gradient(180deg,#000,transparent);
        }
        body::after {
            content:"";
            position:fixed;
            inset:0;
            pointer-events:none;
            background:linear-gradient(120deg, transparent 0 40%, rgba(255,255,255,.08) 41%, transparent 44% 100%);
            opacity:.34;
        }
        .shell { width:min(1320px, calc(100% - 2rem)); margin:0 auto; padding:1.1rem 0 3rem; position:relative; z-index:1; }
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
            color:var(--blue);
            text-decoration:none;
            border:1px solid rgba(255,255,255,.22);
            border-radius:8px;
            padding:.72rem .88rem;
            background:#fff;
            font-size:.82rem;
            font-weight:850;
            cursor:pointer;
            transition:background .18s ease, color .18s ease, border-color .18s ease, transform .18s ease, box-shadow .18s ease;
        }
        .nav a { background:rgba(255,255,255,.1); color:rgba(255,255,255,.9); }
        .nav a:hover, .nav a.active { background:#fff; color:var(--blue); }
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
        .muted { color:var(--muted); }
        .empty-state { padding:2rem; text-align:center; color:var(--muted); }
        .ticket-chat { display:grid; gap:.58rem; margin-top:.65rem; padding:.68rem; border:1px solid rgba(6,57,112,.1); border-radius:var(--radius); background:#f8fbff; }
        .ticket-chat h3 { color:var(--blue); font-size:.78rem; text-transform:uppercase; letter-spacing:.05em; }
        .chat-thread { display:grid; gap:.45rem; max-height:170px; overflow:auto; padding-right:.2rem; }
        .chat-message { border-left:3px solid var(--blue-2); padding:.48rem .55rem; border-radius:0 6px 6px 0; background:#fff; }
        .chat-message.admin { border-left-color:var(--yellow); }
        .chat-message.usuario { border-left-color:var(--green); }
        .chat-meta { display:flex; justify-content:space-between; gap:.5rem; color:var(--muted); font-size:.68rem; font-weight:850; }
        .chat-message p { margin-top:.24rem; color:var(--ink); font-size:.8rem; line-height:1.45; }
        .chat-form { display:grid; gap:.42rem; }
        @media (max-width:900px) {
            body { background:linear-gradient(180deg,#061b39 0 390px,#eef5fb 390px,#f7faff 100%); }
            .filters, .hero, .stats { grid-template-columns:1fr; flex-direction:column; align-items:stretch; }
            .topbar { align-items:flex-start; flex-direction:column; }
            table, tbody, tr, td { display:block; min-width:0; }
            thead { display:none; }
            tr { border:1px solid var(--line); border-radius:var(--radius); margin-bottom:.8rem; background:#fff; overflow:hidden; }
            td { border:0; }
            td + td { border-top:1px solid rgba(6,57,112,.08); }
            .inline-form { min-width:0; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <header class="topbar">
            <a href="index.html"><img src="img/cda-logo-f.svg" alt="Central de Alarmas"></a>
            <nav class="nav" aria-label="Navegacion">
                <span class="user-chip"><?php echo htmlspecialchars($user['nombre']); ?></span>
                <?php if ($user['rol'] === 'admin'): ?><a href="control-marketing.php">Control admin</a><?php endif; ?>
                <?php if ($user['rol'] === 'admin'): ?><a href="usuarios-marketing.php">Usuarios</a><?php endif; ?>
                <a class="active" href="panel-marketing.php">Tickets</a>
                <a href="marketing.html">Crear ticket</a>
                <a href="seguimiento.php">Seguimiento publico</a>
                <a href="logout.php">Salir</a>
            </nav>
        </header>
        <section class="hero">
            <div>
                <div class="eyebrow"><?php echo $user['rol'] === 'admin' ? 'Vista general' : 'Mis solicitudes'; ?></div>
                <h1>Tickets de Diseño y Marketing</h1>
                <p class="muted">Alta, revisión y actualización de solicitudes internas.</p>
            </div>
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
                    <h2>Listado operativo</h2>
                    <p class="muted">Filtra, actualiza estado y conversa en el chat de cada solicitud.</p>
                </div>
            </div>
            <form class="filters" method="get" action="panel-marketing.php">
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
                            <section class="ticket-chat" aria-label="Chat del ticket <?php echo htmlspecialchars($ticket['folio']); ?>">
                                <h3>Chat</h3>
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
                                    <?php if (empty($ticketMessages[(int) $ticket['id']])): ?><p class="muted">Sin mensajes todavia.</p><?php endif; ?>
                                </div>
                                <form class="chat-form" method="post" action="ticket-mensaje.php">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(cdaCsrfToken()); ?>">
                                    <input type="hidden" name="ticket_id" value="<?php echo (int) $ticket['id']; ?>">
                                    <input type="hidden" name="return_to" value="panel-marketing.php">
                                    <textarea name="mensaje" rows="2" required placeholder="Escribe un mensaje para este ticket"></textarea>
                                    <button type="submit">Enviar mensaje</button>
                                </form>
                            </section>
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
</body>
</html>
