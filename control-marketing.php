<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/marketing_helpers.php';

$user = cdaRequireLogin();
if ($user['rol'] !== 'admin') {
    header('Location: panel-marketing.php');
    exit;
}

$lanes = [
    'Entrada' => ['Recibido', 'En evaluacion', 'Pendiente de informacion'],
    'Planeado' => ['Aprobado', 'Programado'],
    'Produccion' => ['En diseno', 'En revision', 'Ajustes solicitados'],
    'Cierre' => ['Entregado', 'Cerrado', 'Rechazado'],
];

$stmt = cdaDb()->query('SELECT * FROM marketing_tickets WHERE eliminado_en IS NULL ORDER BY fecha_requerida ASC, actualizado_en DESC LIMIT 160');
$tickets = $stmt->fetchAll();
$ticketMessages = cdaMarketingFetchTicketMessages(array_column($tickets, 'id'));
$laneTickets = array_fill_keys(array_keys($lanes), []);

foreach ($tickets as $ticket) {
    $placed = false;
    foreach ($lanes as $laneName => $statuses) {
        if (in_array($ticket['estado'], $statuses, true)) {
            $laneTickets[$laneName][] = $ticket;
            $placed = true;
            break;
        }
    }
    if (!$placed) {
        $laneTickets['Entrada'][] = $ticket;
    }
}

$stats = [
    'total' => count($tickets),
    'urgent' => 0,
    'waiting' => 0,
    'dueSoon' => 0,
];
$today = new DateTimeImmutable('today');
foreach ($tickets as $ticket) {
    if ($ticket['prioridad'] === 'Urgente') $stats['urgent']++;
    if ($ticket['estado'] === 'Pendiente de informacion') $stats['waiting']++;
    $due = DateTimeImmutable::createFromFormat('Y-m-d', $ticket['fecha_requerida']);
    if ($due && $due <= $today->modify('+3 days') && !in_array($ticket['estado'], ['Entregado','Cerrado','Rechazado'], true)) {
        $stats['dueSoon']++;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control admin | Diseño y Marketing</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="/contigo/Img/favicon.svg">
    <style>
        :root {
            --blue:#063970;
            --blue-2:#0b4f92;
            --blue-3:#0d62ad;
            --ink:#10213f;
            --muted:#66758d;
            --line:rgba(6,57,112,.14);
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
                radial-gradient(circle at 10% 8%, rgba(246,235,23,.2), transparent 19rem),
                radial-gradient(circle at 88% 12%, rgba(13,98,173,.28), transparent 24rem),
                linear-gradient(180deg,#061b39 0 320px,#edf5fb 320px,#f8fbff 100%);
        }
        body::before {
            content:"";
            position:fixed;
            inset:0 0 auto;
            height:320px;
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
            opacity:.36;
            z-index:0;
        }
        .shell { width:min(1480px, calc(100% - 1.4rem)); margin:0 auto; padding:1rem 0 2.8rem; position:relative; z-index:2; }
        .ambient-points { position:fixed; inset:0; overflow:hidden; pointer-events:none; z-index:1; }
        .ambient-point { --size:2px; --x:50vw; --y:50vh; --dx:20px; --dy:-18px; --duration:24s; --delay:0s; position:absolute; left:var(--x); top:var(--y); width:var(--size); height:var(--size); border-radius:50%; background:rgba(255,255,255,.78); box-shadow:0 0 calc(var(--size) * 5) rgba(166,205,255,.45); opacity:.52; transform:translate3d(0,0,0); animation:ambientDrift var(--duration) ease-in-out var(--delay) infinite alternate; }
        @keyframes ambientDrift { 0% { transform:translate3d(0,0,0); opacity:.28; } 42% { opacity:.78; } 100% { transform:translate3d(var(--dx), var(--dy), 0); opacity:.42; } }
        .topbar { display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:1rem; color:#fff; border:1px solid rgba(255,255,255,.16); border-radius:var(--radius); padding:.65rem .75rem; background:rgba(255,255,255,.12); backdrop-filter:blur(14px); box-shadow:0 18px 50px rgba(0,0,0,.14); }
        .topbar img { width:140px; display:block; filter:drop-shadow(0 10px 18px rgba(0,0,0,.18)); }
        .nav { display:flex; flex-wrap:wrap; gap:.42rem; align-items:center; justify-content:flex-end; }
        .nav a { min-height:36px; display:inline-flex; align-items:center; color:rgba(255,255,255,.88); text-decoration:none; border:1px solid rgba(255,255,255,.18); border-radius:var(--radius); padding:.58rem .7rem; background:rgba(255,255,255,.09); font-size:.8rem; font-weight:850; white-space:nowrap; transition:background .18s ease, color .18s ease, border-color .18s ease, box-shadow .18s ease; }
        .nav a:hover { background:rgba(246,235,23,.16); color:#fff; border-color:rgba(246,235,23,.5); }
        .nav a.active { background:var(--yellow); color:var(--blue); border-color:var(--yellow); box-shadow:0 8px 18px rgba(0,0,0,.1); }
        .nav a:focus-visible { outline:3px solid rgba(246,235,23,.52); outline-offset:2px; }
        .nav a.session-link { color:#fecaca; border-color:rgba(254,202,202,.32); background:rgba(185,28,28,.12); }
        .user-chip, .role-chip { min-height:32px; display:inline-flex; align-items:center; border-radius:8px; padding:.48rem .62rem; font-size:.78rem; font-weight:850; }
        .user-chip { color:#fff; background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.16); }
        .role-chip { color:var(--blue); background:rgba(246,235,23,.92); border:1px solid rgba(246,235,23,.7); }
        .hero { display:flex; justify-content:space-between; gap:1rem; align-items:end; margin:1rem 0; color:#fff; border:1px solid rgba(255,255,255,.16); border-radius:var(--radius); padding:1.2rem; background:linear-gradient(135deg,rgba(255,255,255,.12),rgba(255,255,255,.04)); box-shadow:0 24px 70px rgba(0,0,0,.16); overflow:hidden; position:relative; }
        .hero::before { content:""; position:absolute; inset:0 0 auto; height:5px; background:linear-gradient(90deg,var(--yellow),rgba(255,255,255,.6),var(--blue-3)); }
        .hero > * { position:relative; z-index:1; }
        .eyebrow { color:var(--yellow); font-size:.74rem; font-weight:950; letter-spacing:.12em; text-transform:uppercase; margin-bottom:.55rem; }
        h1 { color:#fff; font-size:clamp(1.9rem,4vw,3.35rem); line-height:1; letter-spacing:0; }
        .hero .muted { color:rgba(255,255,255,.76); margin-top:.45rem; }
        .muted { color:var(--muted); line-height:1.45; }
        .story-strip { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.75rem; margin:0 0 1rem; }
        .story-step { border:1px solid rgba(255,255,255,.44); border-radius:var(--radius); padding:.82rem; background:linear-gradient(180deg,rgba(255,255,255,.96),rgba(248,251,255,.92)); box-shadow:var(--shadow); }
        .story-step span { display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; margin-bottom:.45rem; border-radius:50%; background:var(--blue); color:var(--yellow); font-weight:950; font-size:.76rem; }
        .story-step strong { display:block; color:var(--blue); font-size:.82rem; line-height:1.2; }
        .story-step p { margin-top:.22rem; color:var(--muted); font-size:.75rem; line-height:1.35; }
        .stats { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.85rem; margin-bottom:1rem; }
        .stat { border:1px solid rgba(255,255,255,.5); border-radius:var(--radius); padding:1rem; background:linear-gradient(180deg,#fff,#f8fbff); box-shadow:var(--shadow); position:relative; overflow:hidden; }
        .stat::before { content:""; position:absolute; inset:0 0 auto; height:4px; background:linear-gradient(90deg,var(--yellow),var(--blue-3)); }
        .stat span { display:block; color:var(--muted); font-size:.72rem; font-weight:950; letter-spacing:.05em; text-transform:uppercase; }
        .stat strong { display:block; color:var(--blue); font-size:2.15rem; line-height:1; margin-top:.25rem; }
        .board { display:grid; grid-template-columns:repeat(4,minmax(290px,1fr)); gap:.9rem; align-items:start; overflow-x:auto; padding:.15rem .1rem .45rem; }
        .lane { border:1px solid rgba(6,57,112,.12); border-radius:var(--radius); background:rgba(255,255,255,.86); min-height:560px; padding:.75rem; box-shadow:var(--shadow); backdrop-filter:blur(10px); }
        .lane-head { display:flex; justify-content:space-between; align-items:center; gap:.75rem; margin-bottom:.75rem; padding:.8rem; border-radius:var(--radius); background:linear-gradient(135deg,var(--blue),var(--blue-2)); color:#fff; box-shadow:0 14px 30px rgba(6,57,112,.18); }
        .lane h2 { color:#fff; font-size:1rem; }
        .lane-kicker { color:rgba(255,255,255,.68); font-size:.7rem; font-weight:800; margin-top:.18rem; }
        .count { border-radius:999px; background:var(--yellow); color:var(--blue); padding:.28rem .52rem; font-size:.72rem; font-weight:950; }
        .ticket-card { display:grid; gap:.62rem; border:1px solid var(--line); border-left:6px solid var(--blue-2); border-radius:var(--radius); background:#fff; padding:.55rem .62rem; margin-bottom:.62rem; box-shadow:0 10px 24px rgba(6,57,112,.08); transition:border-color .18s ease, transform .18s ease, box-shadow .18s ease; }
        .ticket-card:hover { border-color:rgba(6,57,112,.28); transform:translateY(-1px); box-shadow:0 16px 34px rgba(6,57,112,.13); }
        .ticket-card.tone-amber { border-left-color:#d97706; }
        .ticket-card.tone-green { border-left-color:var(--green); }
        .ticket-card.tone-red { border-left-color:var(--red); }
        .ticket-card.tone-purple { border-left-color:#6d28d9; }
        .ticket-card summary { list-style:none; cursor:pointer; }
        .ticket-card summary::-webkit-details-marker { display:none; }
        .ticket-summary { display:grid; gap:.5rem; }
        .summary-head { display:flex; justify-content:space-between; gap:.65rem; align-items:start; }
        .summary-meta { display:flex; flex-wrap:wrap; gap:.35rem; align-items:center; }
        .summary-date { color:var(--muted); font-size:.72rem; font-weight:850; }
        .ticket-detail { display:grid; gap:.62rem; padding-top:.6rem; border-top:1px solid rgba(6,57,112,.09); }
        .ticket-top { display:flex; justify-content:space-between; gap:.75rem; align-items:start; }
        .folio { color:var(--blue); font-size:.75rem; font-weight:950; letter-spacing:.06em; }
        .ticket-card h3 { font-size:1rem; line-height:1.18; color:var(--ink); }
        .card-section { display:grid; gap:.45rem; padding:.65rem; border:1px solid rgba(6,57,112,.08); border-radius:var(--radius); background:#fbfdff; }
        .section-label { color:var(--muted); font-size:.68rem; font-weight:950; letter-spacing:.05em; text-transform:uppercase; }
        .status, .priority { display:inline-flex; width:fit-content; border-radius:999px; padding:.32rem .5rem; font-size:.7rem; font-weight:950; }
        .status { background:#dbeafe; color:#1d4ed8; }
        .status.tone-green { background:#d1fae5; color:var(--green); }
        .status.tone-red { background:#fee2e2; color:#991b1b; }
        .status.tone-amber { background:#fff7cc; color:#7c5800; }
        .status.tone-purple { background:#ede9fe; color:#5b21b6; }
        .priority { background:#eef4fb; color:var(--ink); }
        .priority.tone-red { background:#fee2e2; color:var(--red); }
        .priority.tone-amber { background:#fff7cc; color:#7c5800; }
        .meta { display:grid; gap:.25rem; color:var(--muted); font-size:.78rem; line-height:1.35; }
        .quick-form { display:grid; gap:.48rem; }
        select, input, textarea { width:100%; border:1px solid var(--line); border-radius:var(--radius); padding:.68rem .72rem; font:inherit; font-size:.82rem; background:#fff; }
        select:focus, input:focus, textarea:focus { outline:none; border-color:var(--blue-2); box-shadow:0 0 0 4px rgba(6,57,112,.09); }
        textarea { resize:vertical; min-height:58px; }
        button { border:0; border-radius:var(--radius); padding:.72rem .8rem; background:var(--yellow); color:var(--blue); font-weight:950; text-transform:uppercase; cursor:pointer; box-shadow:0 10px 22px rgba(6,57,112,.1); transition:transform .18s ease, box-shadow .18s ease; }
        button:hover { transform:translateY(-1px); box-shadow:0 14px 28px rgba(6,57,112,.14); }
        .danger-button { background:#fee2e2; color:#991b1b; box-shadow:none; }
        .ticket-chat { display:grid; gap:.5rem; padding:.58rem; border:1px solid rgba(6,57,112,.1); border-radius:var(--radius); background:#f8fbff; }
        .ticket-chat h4 { color:var(--blue); font-size:.72rem; text-transform:uppercase; letter-spacing:.05em; }
        .chat-note { color:var(--muted); font-size:.72rem; line-height:1.35; }
        .chat-thread { display:grid; gap:.4rem; max-height:150px; overflow:auto; padding-right:.15rem; }
        .chat-message { border-left:3px solid var(--blue-2); padding:.44rem .5rem; border-radius:0 6px 6px 0; background:#fff; }
        .chat-message.admin { border-left-color:var(--yellow); }
        .chat-message.usuario { border-left-color:var(--green); }
        .chat-meta { display:flex; justify-content:space-between; gap:.45rem; color:var(--muted); font-size:.66rem; font-weight:850; }
        .chat-message p { margin-top:.22rem; color:var(--ink); font-size:.76rem; line-height:1.42; }
        .chat-form { display:grid; gap:.42rem; }
        .chat-files { display:grid; gap:.35rem; margin-top:.4rem; }
        .chat-file { display:inline-flex; width:fit-content; border-radius:6px; padding:.32rem .46rem; background:#eef4fb; color:var(--blue); font-size:.7rem; font-weight:850; text-decoration:none; }
        .file-input { padding:.52rem; font-size:.74rem; background:#fff; }
        @media (max-width:920px) { .topbar, .hero { align-items:flex-start; flex-direction:column; } .stats, .story-strip { grid-template-columns:repeat(2,minmax(0,1fr)); } }
        @media (max-width:620px) { .stats, .story-strip { grid-template-columns:1fr; } .board { grid-template-columns:1fr; overflow:visible; } .lane { min-height:auto; } }
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
                <span class="role-chip">Modo admin</span>
                <a class="admin-link" href="panel-marketing.php">Tickets</a>
                <a class="admin-link active" href="control-marketing.php">Tablero</a>
                <a class="admin-link" href="usuarios-marketing.php">Usuarios</a>
                <a class="admin-link" href="panel-marketing.php?papelera=1">Basurero</a>
                <a class="public-link" href="marketing.html">Crear ticket</a>
                <a class="public-link" href="seguimiento.php">Seguimiento</a>
                <a class="session-link" href="logout.php">Salir</a>
            </nav>
        </header>
        <section class="hero">
            <div>
                <div class="eyebrow">Dashboard administrativo</div>
                <h1>Control admin de solicitudes</h1>
                <p class="muted">Cada ticket conserva su contexto: estado, responsable, fecha y conversacion quedan juntos para decidir rapido sin perder la historia de la solicitud.</p>
            </div>
        </section>
        <section class="story-strip" aria-label="Flujo admin del ticket">
            <div class="story-step"><span>1</span><strong>Entrada validada</strong><p>El usuario queda ligado al correo del ticket desde el registro.</p></div>
            <div class="story-step"><span>2</span><strong>Prioridad visible</strong><p>El admin revisa urgencia, fecha y etapa sin abrir otra pantalla.</p></div>
            <div class="story-step"><span>3</span><strong>Chat con contexto</strong><p>Las dudas y aprobaciones se guardan directo en el folio.</p></div>
            <div class="story-step"><span>4</span><strong>Cierre trazable</strong><p>La entrega queda documentada con historial y comentarios.</p></div>
        </section>
        <section class="stats" aria-label="Resumen admin">
            <div class="stat"><span>En tablero</span><strong><?php echo $stats['total']; ?></strong></div>
            <div class="stat"><span>Urgentes</span><strong><?php echo $stats['urgent']; ?></strong></div>
            <div class="stat"><span>Por vencer</span><strong><?php echo $stats['dueSoon']; ?></strong></div>
            <div class="stat"><span>Esperando info</span><strong><?php echo $stats['waiting']; ?></strong></div>
        </section>
        <main class="board" aria-label="Tablero de tickets">
            <?php foreach ($laneTickets as $laneName => $items): ?>
            <section class="lane">
                <div class="lane-head">
                    <div>
                        <h2><?php echo htmlspecialchars($laneName); ?></h2>
                        <p class="lane-kicker"><?php echo htmlspecialchars(implode(' · ', array_map('cdaMarketingStatusLabel', $lanes[$laneName]))); ?></p>
                    </div>
                    <span class="count"><?php echo count($items); ?></span>
                </div>
                <?php foreach ($items as $ticket): ?>
                <details class="ticket-card tone-<?php echo htmlspecialchars(cdaMarketingStatusTone($ticket['estado'])); ?>" id="ticket-<?php echo (int) $ticket['id']; ?>">
                    <summary class="ticket-summary">
                        <div class="summary-head">
                            <span class="folio"><?php echo htmlspecialchars($ticket['folio']); ?></span>
                            <span class="priority tone-<?php echo htmlspecialchars(cdaMarketingPriorityTone($ticket['prioridad'])); ?>"><?php echo htmlspecialchars($ticket['prioridad']); ?></span>
                        </div>
                        <h3><?php echo htmlspecialchars($ticket['actividad']); ?></h3>
                        <div class="summary-meta">
                            <span class="status tone-<?php echo htmlspecialchars(cdaMarketingStatusTone($ticket['estado'])); ?>"><?php echo htmlspecialchars(cdaMarketingStatusLabel($ticket['estado'])); ?></span>
                            <span class="summary-date"><?php echo htmlspecialchars(cdaMarketingFormatDate($ticket['fecha_requerida'])); ?></span>
                        </div>
                    </summary>
                    <div class="ticket-detail">
                    <div class="card-section meta">
                        <span class="section-label">Datos</span>
                        <span><?php echo htmlspecialchars($ticket['solicitante']); ?> · <?php echo htmlspecialchars($ticket['departamento']); ?></span>
                        <span><?php echo htmlspecialchars($ticket['tipo_solicitud']); ?></span>
                        <span>Requerido: <?php echo htmlspecialchars(cdaMarketingFormatDate($ticket['fecha_requerida'])); ?></span>
                        <?php if (!empty($ticket['asignado_a'])): ?><span>Asignado a: <?php echo htmlspecialchars($ticket['asignado_a']); ?></span><?php endif; ?>
                    </div>
                    <form class="quick-form" method="post" action="ticket-actualizar.php">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(cdaCsrfToken()); ?>">
                        <input type="hidden" name="id" value="<?php echo (int) $ticket['id']; ?>">
                        <input type="hidden" name="return_to" value="control-marketing.php">
                        <select name="estado" required>
                            <?php foreach (cdaMarketingStatuses() as $status): ?>
                                <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $ticket['estado'] === $status ? 'selected' : ''; ?>><?php echo htmlspecialchars(cdaMarketingStatusLabel($status)); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input name="asignado_a" value="<?php echo htmlspecialchars($ticket['asignado_a'] ?? ''); ?>" placeholder="Asignado a">
                        <textarea name="respuesta_interna" placeholder="Comentario visible en seguimiento"><?php echo htmlspecialchars($ticket['respuesta_interna'] ?? ''); ?></textarea>
                        <button type="submit">Actualizar</button>
                    </form>
                    <form class="quick-form" method="post" action="ticket-eliminar.php" onsubmit="return confirm('Enviar este ticket al basurero?');">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(cdaCsrfToken()); ?>">
                        <input type="hidden" name="id" value="<?php echo (int) $ticket['id']; ?>">
                        <input type="hidden" name="action" value="trash">
                        <input type="hidden" name="return_to" value="control-marketing.php">
                        <button class="danger-button" type="submit">Enviar al basurero</button>
                    </form>
                    <section class="ticket-chat" aria-label="Chat del ticket <?php echo htmlspecialchars($ticket['folio']); ?>">
                        <h4>Chat del ticket</h4>
                        <p class="chat-note">Usa este hilo para pedir informacion, confirmar avances o dejar acuerdos visibles para el solicitante.</p>
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
                                                <a class="chat-file" href="<?php echo htmlspecialchars($file['ruta']); ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars($file['nombre_original']); ?></a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                            <?php if (empty($ticketMessages[(int) $ticket['id']])): ?><p class="muted">Aun no hay mensajes; el primer comentario abrira el seguimiento de este folio.</p><?php endif; ?>
                        </div>
                        <form class="chat-form" method="post" action="ticket-mensaje.php" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(cdaCsrfToken()); ?>">
                            <input type="hidden" name="ticket_id" value="<?php echo (int) $ticket['id']; ?>">
                            <input type="hidden" name="return_to" value="control-marketing.php">
                            <textarea name="mensaje" placeholder="Mensaje para este ticket"></textarea>
                            <input class="file-input" name="archivos[]" type="file" multiple accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.webp,.mp4,.mov,.zip">
                            <button type="submit">Enviar</button>
                        </form>
                    </section>
                    </div>
                </details>
                <?php endforeach; ?>
                <?php if (!$items): ?><p class="muted">Sin tickets en esta etapa.</p><?php endif; ?>
            </section>
            <?php endforeach; ?>
        </main>
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
