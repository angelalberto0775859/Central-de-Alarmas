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

$stmt = cdaDb()->query('SELECT * FROM marketing_tickets ORDER BY fecha_requerida ASC, actualizado_en DESC LIMIT 160');
$tickets = $stmt->fetchAll();
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
        :root { --blue:#063970; --ink:#10213f; --muted:#66758d; --line:rgba(6,57,112,.14); --soft:#f4f8fc; --yellow:#f6eb17; --radius:8px; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { min-height:100vh; font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif; color:var(--ink); background:linear-gradient(180deg,#edf5fb,#f8fbff 36%,#f3f7fb); }
        .shell { width:min(1440px, calc(100% - 1.4rem)); margin:0 auto; padding:1rem 0 2.5rem; }
        .topbar { display:flex; justify-content:space-between; align-items:center; gap:1rem; margin-bottom:1rem; }
        .topbar img { width:146px; display:block; }
        .nav { display:flex; flex-wrap:wrap; gap:.55rem; align-items:center; }
        .nav a { color:var(--blue); text-decoration:none; border:1px solid var(--line); border-radius:var(--radius); padding:.65rem .78rem; background:#fff; font-size:.8rem; font-weight:900; }
        .hero { display:flex; justify-content:space-between; gap:1rem; align-items:end; margin:1rem 0; }
        h1 { color:var(--blue); font-size:clamp(1.8rem,4vw,3.2rem); line-height:1; }
        .muted { color:var(--muted); line-height:1.45; }
        .stats { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.75rem; margin-bottom:1rem; }
        .stat { border:1px solid var(--line); border-radius:var(--radius); padding:1rem; background:#fff; box-shadow:0 12px 34px rgba(6,57,112,.07); }
        .stat span { display:block; color:var(--muted); font-size:.72rem; font-weight:950; letter-spacing:.05em; text-transform:uppercase; }
        .stat strong { display:block; color:var(--blue); font-size:2rem; line-height:1; margin-top:.25rem; }
        .board { display:grid; grid-template-columns:repeat(4,minmax(260px,1fr)); gap:.85rem; align-items:start; overflow-x:auto; padding-bottom:.25rem; }
        .lane { border:1px solid var(--line); border-radius:var(--radius); background:rgba(255,255,255,.74); min-height:520px; padding:.75rem; }
        .lane-head { display:flex; justify-content:space-between; align-items:center; gap:.75rem; margin-bottom:.7rem; }
        .lane h2 { color:var(--blue); font-size:1rem; }
        .count { border-radius:999px; background:var(--blue); color:#fff; padding:.28rem .52rem; font-size:.72rem; font-weight:950; }
        .ticket-card { display:grid; gap:.65rem; border:1px solid var(--line); border-radius:var(--radius); background:#fff; padding:.8rem; margin-bottom:.7rem; box-shadow:0 10px 28px rgba(6,57,112,.07); }
        .ticket-top { display:flex; justify-content:space-between; gap:.75rem; align-items:start; }
        .folio { color:var(--blue); font-size:.75rem; font-weight:950; letter-spacing:.06em; }
        .status, .priority { display:inline-flex; width:fit-content; border-radius:999px; padding:.32rem .5rem; font-size:.7rem; font-weight:950; }
        .status { background:var(--soft); color:var(--blue); }
        .status.entregado, .status.cerrado { background:#d1fae5; color:#047857; }
        .status.rechazado { background:#fee2e2; color:#991b1b; }
        .priority { background:#eef4fb; color:var(--ink); }
        .priority.urgente { background:#fee2e2; color:#991b1b; }
        .priority.alta { background:#fff7cc; color:#7c5800; }
        .ticket-card h3 { font-size:.98rem; line-height:1.18; color:var(--ink); }
        .meta { display:grid; gap:.25rem; color:var(--muted); font-size:.78rem; line-height:1.35; }
        .quick-form { display:grid; gap:.48rem; }
        select, input, textarea { width:100%; border:1px solid var(--line); border-radius:var(--radius); padding:.68rem .72rem; font:inherit; font-size:.82rem; background:#fff; }
        textarea { resize:vertical; min-height:58px; }
        button { border:0; border-radius:var(--radius); padding:.72rem .8rem; background:var(--yellow); color:var(--blue); font-weight:950; text-transform:uppercase; cursor:pointer; }
        @media (max-width:920px) { .topbar, .hero { align-items:flex-start; flex-direction:column; } .stats { grid-template-columns:repeat(2,minmax(0,1fr)); } }
        @media (max-width:620px) { .stats { grid-template-columns:1fr; } .board { grid-template-columns:1fr; overflow:visible; } .lane { min-height:auto; } }
    </style>
</head>
<body>
    <div class="shell">
        <header class="topbar">
            <a href="index.html"><img src="img/cda-logo-f.svg" alt="Central de Alarmas"></a>
            <nav class="nav" aria-label="Navegacion">
                <span class="muted"><?php echo htmlspecialchars($user['nombre']); ?></span>
                <a href="panel-marketing.php">Lista</a>
                <a href="usuarios-marketing.php">Usuarios</a>
                <a href="marketing.html">Crear ticket</a>
                <a href="logout.php">Salir</a>
            </nav>
        </header>
        <section class="hero">
            <div>
                <h1>Control admin de solicitudes</h1>
                <p class="muted">Tablero operativo para priorizar, asignar y mover estatus de tickets de Diseño y Marketing.</p>
            </div>
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
                    <h2><?php echo htmlspecialchars($laneName); ?></h2>
                    <span class="count"><?php echo count($items); ?></span>
                </div>
                <?php foreach ($items as $ticket): ?>
                <article class="ticket-card">
                    <div class="ticket-top">
                        <span class="folio"><?php echo htmlspecialchars($ticket['folio']); ?></span>
                        <span class="priority <?php echo htmlspecialchars(strtolower($ticket['prioridad'])); ?>"><?php echo htmlspecialchars($ticket['prioridad']); ?></span>
                    </div>
                    <h3><?php echo htmlspecialchars($ticket['actividad']); ?></h3>
                    <span class="status <?php echo htmlspecialchars(cdaMarketingStatusClass($ticket['estado'])); ?>"><?php echo htmlspecialchars(cdaMarketingStatusLabel($ticket['estado'])); ?></span>
                    <div class="meta">
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
                </article>
                <?php endforeach; ?>
                <?php if (!$items): ?><p class="muted">Sin tickets en esta etapa.</p><?php endif; ?>
            </section>
            <?php endforeach; ?>
        </main>
    </div>
</body>
</html>
