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
$currentUser = cdaCurrentUser();

if ($folio) {
    try {
        $stmt = cdaDb()->prepare('SELECT * FROM marketing_tickets WHERE folio = ? AND eliminado_en IS NULL LIMIT 1');
        $stmt->execute([$folio]);
        $ticket = $stmt->fetch();

        if ($ticket) {
            $progressIndex = cdaMarketingProgressIndex($ticket['estado']);
            $histStmt = cdaDb()->prepare('SELECT estado, comentario, creado_en FROM marketing_ticket_historial WHERE ticket_id = ? ORDER BY creado_en DESC');
            $histStmt->execute([$ticket['id']]);
            $historial = $histStmt->fetchAll();

            $fileStmt = cdaDb()->prepare('SELECT nombre_original, ruta FROM marketing_ticket_archivos WHERE ticket_id = ? ORDER BY creado_en ASC');
            $fileStmt->execute([$ticket['id']]);
            $archivos = $fileStmt->fetchAll();

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'chat') {
                cdaRequirePostCsrf();
                $mensajeChat = cdaMarketingClean($_POST['mensaje'] ?? '');
                $canChat = $currentUser && ($currentUser['rol'] === 'admin' || strcasecmp($currentUser['correo'], $ticket['correo']) === 0);

                if (!$currentUser) {
                    $chatError = 'Inicia sesion con el usuario del ticket para enviar mensajes.';
                } elseif (!$canChat) {
                    $chatError = 'Tu usuario no coincide con el correo de este ticket.';
                } elseif ($mensajeChat === '') {
                    $chatError = 'Escribe un mensaje para enviarlo al equipo.';
                } else {
                    try {
                        $db = cdaDb();
                        $db->beginTransaction();

                        $insert = $db->prepare(
                            'INSERT INTO marketing_ticket_mensajes (ticket_id, usuario_id, autor_nombre, autor_rol, mensaje)
                            VALUES (?, ?, ?, ?, ?)'
                        );
                        $authorRole = $currentUser['rol'] === 'admin' ? 'admin' : 'usuario';
                        $insert->execute([$ticket['id'], $currentUser['id'], $currentUser['nombre'], $authorRole, $mensajeChat]);

                        $touch = $db->prepare('UPDATE marketing_tickets SET actualizado_en = CURRENT_TIMESTAMP WHERE id = ?');
                        $touch->execute([$ticket['id']]);

                        $db->commit();
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
    <title>Seguimiento de ticket | Diseño y Marketing</title>
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
                radial-gradient(circle at 1px 1px, rgba(255,255,255,.17) 1px, transparent 1.7px),
                radial-gradient(circle at 16% 18%, rgba(246,235,23,.18), transparent 23rem),
                radial-gradient(circle at 78% 12%, rgba(69,143,255,.22), transparent 25rem),
                linear-gradient(135deg,#061226,#063970 58%,#031025);
            background-size:28px 28px,auto,auto,auto;
        }
        .shell { width:min(1160px, calc(100% - 2rem)); margin:0 auto; padding:1.1rem 0 3rem; position:relative; }
        .topbar { display:flex; align-items:center; justify-content:space-between; gap:1rem; color:#fff; margin-bottom:clamp(2rem,5vw,3.8rem); }
        .topbar img { width:154px; display:block; }
        .nav { display:flex; flex-wrap:wrap; gap:.62rem; justify-content:flex-end; }
        .nav a {
            min-height:40px;
            display:inline-flex;
            align-items:center;
            color:rgba(255,255,255,.88);
            text-decoration:none;
            border:1px solid rgba(255,255,255,.22);
            border-radius:var(--radius);
            padding:.62rem .78rem;
            background:rgba(255,255,255,.08);
            font-size:.78rem;
            font-weight:900;
            letter-spacing:.04em;
            text-transform:uppercase;
        }
        .nav a.primary { background:var(--yellow); color:var(--blue); border-color:rgba(246,235,23,.65); }
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
        .status { display:inline-flex; width:fit-content; border-radius:999px; padding:.46rem .72rem; background:var(--blue); color:#fff; font-size:.78rem; font-weight:950; white-space:nowrap; }
        .status.rechazado { background:#991b1b; }
        .status.cerrado, .status.entregado { background:#047857; }
        .progress { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:.45rem; margin:1rem 0 .25rem; }
        .progress-step { min-height:72px; border:1px solid var(--line); border-radius:var(--radius); background:#fff; padding:.72rem .55rem; color:var(--muted); font-size:.76rem; font-weight:900; text-align:center; }
        .progress-step::before { content:""; display:block; width:18px; height:18px; border-radius:50%; margin:0 auto .42rem; background:#dbe5f0; }
        .progress-step.done { border-color:rgba(6,57,112,.22); background:linear-gradient(180deg,#fff,#f5f9ff); color:var(--blue); }
        .progress-step.done::before { background:var(--yellow); box-shadow:0 0 0 5px rgba(246,235,23,.18); }
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
        .chat-box { margin-top:1rem; display:grid; gap:.75rem; }
        .chat-intro { color:var(--muted); line-height:1.55; font-size:.9rem; }
        .chat-thread { display:grid; gap:.55rem; max-height:320px; overflow:auto; padding-right:.25rem; }
        .chat-message { border-left:4px solid var(--blue-2); border-radius:0 var(--radius) var(--radius) 0; background:#fff; padding:.72rem .82rem; }
        .chat-message.admin { border-left-color:var(--yellow); background:#fffbea; }
        .chat-message.usuario { border-left-color:#047857; }
        .chat-meta { display:flex; justify-content:space-between; gap:.75rem; color:var(--muted); font-size:.72rem; font-weight:900; }
        .chat-message p { margin-top:.3rem; line-height:1.55; color:var(--ink); }
        .chat-form { display:grid; grid-template-columns:1fr; gap:.7rem; border:1px solid var(--line); border-radius:var(--radius); background:var(--soft); padding:.85rem; }
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
            .meta { grid-template-columns:1fr; }
            .progress { grid-template-columns:1fr; }
            .progress-step { min-height:auto; display:flex; align-items:center; gap:.5rem; text-align:left; }
            .progress-step::before { margin:0; flex:0 0 auto; }
            button { width:100%; }
        }
    </style>
</head>
<body>
    <div class="shell">
        <header class="topbar">
            <a href="index.html"><img src="img/cda-logo-f.svg" alt="Central de Alarmas"></a>
            <nav class="nav" aria-label="Navegacion">
                <a href="marketing.html">Crear ticket</a>
                <a class="primary" href="login.php">Iniciar sesion</a>
            </nav>
        </header>

        <section class="hero-grid">
            <div class="hero">
                <div class="eyebrow">Portal de Diseño y Marketing</div>
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
                        <div class="progress-step <?php echo $stepIndex <= $progressIndex ? 'done' : ''; ?>"><?php echo htmlspecialchars($stepLabel); ?></div>
                    <?php $stepIndex++; endforeach; ?>
                </div>
                <div class="meta">
                    <div><span>Tipo</span><strong><?php echo htmlspecialchars($ticket['tipo_solicitud']); ?></strong></div>
                    <div><span>Prioridad</span><strong><?php echo htmlspecialchars($ticket['prioridad']); ?></strong></div>
                    <div><span>Fecha requerida</span><strong><?php echo htmlspecialchars(cdaMarketingFormatDate($ticket['fecha_requerida'])); ?></strong></div>
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
                            </article>
                        <?php endforeach; ?>
                        <?php if (!$mensajes): ?><p class="muted">Aun no hay mensajes. Cuando el equipo necesite una aclaracion, aparecera aqui junto al folio.</p><?php endif; ?>
                    </div>
                    <?php if ($chatError): ?><div class="error"><?php echo htmlspecialchars($chatError); ?></div><?php endif; ?>
                    <?php $canUseChat = $currentUser && ($currentUser['rol'] === 'admin' || strcasecmp($currentUser['correo'], $ticket['correo']) === 0); ?>
                    <?php if ($canUseChat): ?>
                        <form class="chat-form" method="post" action="seguimiento.php#chat">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(cdaCsrfToken()); ?>">
                            <input type="hidden" name="action" value="chat">
                            <input type="hidden" name="folio" value="<?php echo htmlspecialchars($ticket['folio']); ?>">
                            <label>Mensaje <textarea name="mensaje" required placeholder="Escribe tu respuesta para el equipo"></textarea></label>
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
                <h2 style="margin-top:1rem;">Archivos</h2>
                <ul class="files">
                    <?php foreach ($archivos as $archivo): ?>
                    <li><?php echo htmlspecialchars($archivo['nombre_original']); ?></li>
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
</body>
</html>
