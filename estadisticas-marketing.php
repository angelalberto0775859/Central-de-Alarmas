<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/marketing_helpers.php';

$user = cdaRequireLogin();
if (!cdaMarketingCanViewAllTickets($user['rol'])) {
    header('Location: perfil-marketing.php');
    exit;
}

$stmt = cdaDb()->query('SELECT * FROM marketing_tickets WHERE eliminado_en IS NULL ORDER BY actualizado_en DESC LIMIT 1000');
$tickets = $stmt->fetchAll();

$statusStats = array_fill_keys(cdaMarketingStatuses(), 0);
$typeStats = [];
$userStats = [];
$priorityStats = ['Normal' => 0, 'Alta' => 0, 'Urgente' => 0];
$monthNames = ['01' => 'Ene', '02' => 'Feb', '03' => 'Mar', '04' => 'Abr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dic'];
$completedByMonth = [];
$completedStatuses = ['Entregado', 'Cerrado'];
$today = new DateTimeImmutable('today');

for ($i = 5; $i >= 0; $i--) {
    $month = $today->modify('-' . $i . ' months');
    $completedByMonth[$month->format('Y-m')] = [
        'label' => $monthNames[$month->format('m')] . ' ' . $month->format('y'),
        'value' => 0,
    ];
}

foreach ($tickets as $ticket) {
    if (isset($statusStats[$ticket['estado']])) $statusStats[$ticket['estado']]++;
    if (isset($priorityStats[$ticket['prioridad']])) $priorityStats[$ticket['prioridad']]++;

    $type = cdaMarketingClean($ticket['tipo_solicitud'] ?? 'Sin tipo') ?: 'Sin tipo';
    $typeStats[$type] = ($typeStats[$type] ?? 0) + 1;

    $requester = cdaMarketingClean($ticket['solicitante'] ?? '') ?: cdaMarketingClean($ticket['correo'] ?? 'Usuario sin nombre') ?: 'Usuario sin nombre';
    $userStats[$requester] = ($userStats[$requester] ?? 0) + 1;

    if (in_array($ticket['estado'], $completedStatuses, true)) {
        $updated = new DateTimeImmutable($ticket['actualizado_en'] ?? $ticket['creado_en']);
        $monthKey = $updated->format('Y-m');
        if (isset($completedByMonth[$monthKey])) {
            $completedByMonth[$monthKey]['value']++;
        }
    }
}

arsort($typeStats);
arsort($userStats);
$topTypes = array_slice($typeStats, 0, 7, true);
$topUsers = array_slice($userStats, 0, 7, true);
$maxType = max(array_values($topTypes) ?: [1]);
$maxUser = max(array_values($topUsers) ?: [1]);
$maxCompleted = max(array_column($completedByMonth, 'value') ?: [1]);
$ticketTotal = count($tickets);
$completedTotal = ($statusStats['Entregado'] ?? 0) + ($statusStats['Cerrado'] ?? 0);
$activeTotal = max(0, $ticketTotal - $completedTotal - ($statusStats['Rechazado'] ?? 0));
$statusColors = [
    'Recibido' => '#0d62ad',
    'En evaluacion' => '#38bdf8',
    'Pendiente de informacion' => '#f59e0b',
    'Aprobado' => '#22c55e',
    'Programado' => '#84cc16',
    'En diseno' => '#6366f1',
    'En revision' => '#8b5cf6',
    'Ajustes solicitados' => '#ec4899',
    'Entregado' => '#047857',
    'Cerrado' => '#0f766e',
    'Rechazado' => '#b91c1c',
];
$conicParts = [];
$cursor = 0;
foreach (cdaMarketingStatuses() as $status) {
    $count = (int) ($statusStats[$status] ?? 0);
    if ($ticketTotal <= 0 || $count <= 0) continue;
    $next = $cursor + ($count / $ticketTotal * 100);
    $conicParts[] = ($statusColors[$status] ?? '#0d62ad') . ' ' . round($cursor, 2) . '% ' . round($next, 2) . '%';
    $cursor = $next;
}
$statusConic = $conicParts ? implode(', ', $conicParts) : '#e5edf7 0 100%';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas | Marketing</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="/contigo/Img/favicon.svg">
    <style>
        :root { --blue:#063970; --blue-2:#0b4f92; --blue-3:#0d62ad; --ink:#10213f; --muted:#66758d; --line:rgba(6,57,112,.14); --soft:#f4f8fc; --yellow:#f6eb17; --radius:8px; --shadow:0 18px 46px rgba(6,57,112,.12); }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { min-height:100vh; font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif; color:var(--ink); background:radial-gradient(circle at 18% 18%, rgba(246,235,23,.2), transparent 23rem), radial-gradient(circle at 82% 24%, rgba(71,151,255,.2), transparent 25rem), linear-gradient(135deg,#061226,#063970 58%,#031025); overflow-x:hidden; }
        body::before { content:""; position:fixed; inset:0; pointer-events:none; background:linear-gradient(120deg, transparent 0 44%, rgba(255,255,255,.09) 45%, transparent 48% 100%); opacity:.45; z-index:0; }
        .shell { width:min(1240px, calc(100% - 2rem)); margin:0 auto; padding:1rem 0 3rem; position:relative; z-index:2; }
        .ambient-points { position:fixed; inset:0; overflow:hidden; pointer-events:none; z-index:1; }
        .ambient-point { --size:2px; --x:50vw; --y:50vh; --dx:18px; --dy:-22px; --duration:18s; --delay:0s; position:absolute; left:var(--x); top:var(--y); width:var(--size); height:var(--size); border-radius:50%; background:rgba(255,255,255,.55); box-shadow:0 0 calc(var(--size) * 4) rgba(166,205,255,.28); opacity:.42; transform:translate3d(0,0,0); animation:ambientDrift var(--duration) ease-in-out var(--delay) infinite alternate; }
        @keyframes ambientDrift { 0% { transform:translate3d(0,0,0); opacity:.18; } 38% { opacity:.56; } 100% { transform:translate3d(var(--dx), var(--dy), 0); opacity:.32; } }
        .topbar { display:flex; justify-content:space-between; align-items:center; gap:1rem; min-height:64px; margin-bottom:1rem; color:#fff; }
        .topbar img { width:140px; display:block; filter:drop-shadow(0 10px 18px rgba(0,0,0,.18)); }
        .nav { display:flex; flex-wrap:wrap; gap:.55rem; align-items:center; justify-content:flex-end; }
        .nav a { min-height:40px; display:inline-flex; align-items:center; color:rgba(255,255,255,.86); text-decoration:none; border:1px solid rgba(255,255,255,.2); border-radius:var(--radius); padding:.62rem .78rem; background:rgba(255,255,255,.08); font-size:.8rem; font-weight:850; white-space:nowrap; }
        .nav a:hover, .nav a.active { background:rgba(255,255,255,.18); color:#fff; border-color:rgba(246,235,23,.48); }
        .profile-menu { position:relative; }
        .profile-menu summary { min-height:40px; display:inline-flex; align-items:center; gap:.5rem; list-style:none; border:1px solid rgba(255,255,255,.22); border-radius:8px; padding:.62rem .78rem; color:#fff; background:rgba(255,255,255,.1); font-size:.8rem; font-weight:850; cursor:pointer; }
        .profile-menu summary::-webkit-details-marker { display:none; }
        .profile-menu summary::after { content:""; width:.45rem; height:.45rem; border-right:2px solid currentColor; border-bottom:2px solid currentColor; transform:rotate(45deg) translateY(-2px); opacity:.75; }
        .profile-menu.role-usuario summary { border-color:rgba(246,235,23,.82); box-shadow:0 0 0 1px rgba(246,235,23,.18); }
        .profile-menu.role-admin summary { border-color:rgba(248,113,113,.9); box-shadow:0 0 0 1px rgba(248,113,113,.2); }
        .profile-menu.role-trabajador summary { border-color:rgba(34,197,94,.88); box-shadow:0 0 0 1px rgba(34,197,94,.18); }
        .profile-menu.role-manager summary { border-color:rgba(96,165,250,.88); box-shadow:0 0 0 1px rgba(96,165,250,.18); }
        .profile-dropdown { position:absolute; right:0; top:calc(100% + .45rem); z-index:10; display:grid; min-width:190px; padding:.45rem; border:1px solid rgba(6,57,112,.12); border-radius:8px; background:#fff; box-shadow:0 18px 40px rgba(0,0,0,.18); }
        .profile-dropdown a { min-height:36px; justify-content:flex-start; border:0; background:#fff; color:var(--ink); }
        .profile-dropdown a:hover { background:var(--soft); color:var(--blue); }
        .profile-dropdown a.logout-link { color:#991b1b; }
        .hero { margin:1rem 0; color:#fff; border:1px solid rgba(255,255,255,.16); border-radius:var(--radius); padding:1.2rem; background:linear-gradient(135deg,rgba(255,255,255,.12),rgba(255,255,255,.04)); box-shadow:0 24px 70px rgba(0,0,0,.16); position:relative; overflow:hidden; }
        .hero::before { content:""; position:absolute; inset:0 0 auto; height:5px; background:linear-gradient(90deg,var(--yellow),rgba(255,255,255,.6),var(--blue-3)); }
        .hero > * { position:relative; z-index:1; }
        .eyebrow { color:var(--yellow); font-size:.74rem; font-weight:950; letter-spacing:.12em; text-transform:uppercase; margin-bottom:.55rem; }
        h1 { color:#fff; font-size:clamp(1.9rem,4vw,3.35rem); line-height:1; letter-spacing:0; }
        .hero .muted { color:rgba(255,255,255,.76); margin-top:.45rem; }
        .muted { color:var(--muted); line-height:1.45; }
        .analytics { border:1px solid rgba(255,255,255,.5); border-radius:var(--radius); padding:1rem; background:linear-gradient(180deg,#fff,#f8fbff); box-shadow:var(--shadow); }
        .kpi-row { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.65rem; margin-bottom:1rem; }
        .kpi-mini { border:1px solid rgba(6,57,112,.08); border-radius:var(--radius); padding:.75rem; background:#fbfdff; }
        .kpi-mini span { display:block; color:var(--muted); font-size:.68rem; font-weight:850; line-height:1.25; }
        .kpi-mini strong { display:block; color:var(--blue); font-size:1.6rem; line-height:1; margin-top:.25rem; font-variant-numeric:tabular-nums; }
        .analytics-grid { display:grid; grid-template-columns:1.05fr 1fr 1fr; gap:.9rem; align-items:stretch; }
        .analysis-card { border:1px solid rgba(6,57,112,.1); border-radius:var(--radius); padding:.9rem; background:#fff; min-height:260px; }
        .analysis-card.wide { grid-column:span 2; }
        .analysis-card h2 { color:var(--blue); font-size:.95rem; margin-bottom:.72rem; }
        .pie-layout { display:grid; grid-template-columns:150px minmax(0,1fr); gap:1rem; align-items:center; }
        .pie-chart { width:150px; aspect-ratio:1; border-radius:50%; background:conic-gradient(var(--segments)); box-shadow:inset 0 0 0 22px #fff, 0 14px 28px rgba(6,57,112,.12); }
        .legend { display:grid; gap:.42rem; max-height:190px; overflow:auto; padding-right:.2rem; }
        .legend-row { display:grid; grid-template-columns:.65rem minmax(0,1fr) auto; gap:.45rem; align-items:center; color:var(--muted); font-size:.76rem; }
        .legend-dot { width:.65rem; height:.65rem; border-radius:50%; background:var(--dot); }
        .legend-row strong, .bar-meta strong, .month-bar strong { color:var(--ink); font-variant-numeric:tabular-nums; }
        .bars { display:grid; gap:.65rem; }
        .bar-row { display:grid; gap:.28rem; }
        .bar-meta { display:flex; justify-content:space-between; gap:.75rem; color:var(--muted); font-size:.76rem; line-height:1.25; }
        .bar-track { height:.66rem; border-radius:999px; overflow:hidden; background:#e8f0f8; }
        .bar-fill { display:block; width:var(--value); height:100%; border-radius:999px; background:linear-gradient(90deg,var(--blue-2),var(--yellow)); min-width:2px; }
        .month-bars { display:grid; grid-template-columns:repeat(6,minmax(0,1fr)); gap:.55rem; align-items:end; min-height:170px; padding-top:.5rem; }
        .month-bar { display:grid; grid-template-rows:1fr auto auto; gap:.35rem; height:170px; text-align:center; color:var(--muted); font-size:.7rem; }
        .month-fill { align-self:end; justify-self:center; width:100%; max-width:34px; height:var(--value); min-height:2px; border-radius:7px 7px 3px 3px; background:linear-gradient(180deg,var(--yellow),var(--blue-2)); }
        .empty-chart { border:1px dashed rgba(6,57,112,.18); border-radius:var(--radius); padding:.8rem; color:var(--muted); background:#fbfdff; font-size:.82rem; line-height:1.45; }
        @media (max-width:1100px) { .analytics-grid { grid-template-columns:1fr 1fr; } .analysis-card.wide { grid-column:span 2; } }
        @media (max-width:900px) { .topbar, .hero { align-items:flex-start; flex-direction:column; } .nav { justify-content:flex-start; } .profile-dropdown { left:0; right:auto; } .analytics-grid { grid-template-columns:1fr; } .analysis-card.wide { grid-column:auto; } .kpi-row { grid-template-columns:repeat(2,minmax(0,1fr)); } }
        @media (max-width:620px) { .kpi-row, .pie-layout { grid-template-columns:1fr; } .month-bars { grid-template-columns:repeat(3,minmax(0,1fr)); } }
        @media (prefers-reduced-motion:reduce) { .ambient-point { animation:none; } }
    </style>
</head>
<body>
    <div class="ambient-points" aria-hidden="true"></div>
    <div class="shell">
        <header class="topbar">
            <a href="index.html"><img src="img/cda-logo-f.svg" alt="Central de Alarmas"></a>
            <nav class="nav" aria-label="Navegacion">
                <a class="active" href="estadisticas-marketing.php">Estadísticas</a>
                <a href="panel-marketing.php">Tickets</a>
                <a href="control-marketing.php">Tablero</a>
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
        <section class="hero">
            <div class="eyebrow">Administración · Estadísticas</div>
            <h1>Analítica de tickets</h1>
            <p class="muted">Consulta estados, cierres, temas más solicitados, prioridades y usuarios con mayor actividad.</p>
        </section>
        <main class="analytics">
            <section class="kpi-row" aria-label="Resumen general">
                <div class="kpi-mini"><span>Total analizado</span><strong><?php echo $ticketTotal; ?></strong></div>
                <div class="kpi-mini"><span>Tickets activos</span><strong><?php echo $activeTotal; ?></strong></div>
                <div class="kpi-mini"><span>Terminados</span><strong><?php echo $completedTotal; ?></strong></div>
                <div class="kpi-mini"><span>Urgentes</span><strong><?php echo (int) $priorityStats['Urgente']; ?></strong></div>
            </section>
            <section class="analytics-grid" aria-label="Gráficas de tickets">
                <article class="analysis-card">
                    <h2>Estados de tickets</h2>
                    <div class="pie-layout">
                        <div class="pie-chart" style="--segments:<?php echo htmlspecialchars($statusConic); ?>" aria-hidden="true"></div>
                        <div class="legend">
                            <?php foreach (cdaMarketingStatuses() as $status): ?>
                                <?php $count = (int) ($statusStats[$status] ?? 0); ?>
                                <?php if ($count <= 0) continue; ?>
                                <div class="legend-row">
                                    <span class="legend-dot" style="--dot:<?php echo htmlspecialchars($statusColors[$status] ?? '#0d62ad'); ?>"></span>
                                    <span><?php echo htmlspecialchars(cdaMarketingStatusLabel($status)); ?></span>
                                    <strong><?php echo $count; ?></strong>
                                </div>
                            <?php endforeach; ?>
                            <?php if ($ticketTotal === 0): ?><p class="empty-chart">Aun no hay tickets para graficar estados.</p><?php endif; ?>
                        </div>
                    </div>
                </article>
                <article class="analysis-card">
                    <h2>Tickets terminados</h2>
                    <div class="month-bars">
                        <?php foreach ($completedByMonth as $month): ?>
                            <?php $height = $maxCompleted > 0 ? max(2, round(((int) $month['value'] / $maxCompleted) * 100)) : 2; ?>
                            <div class="month-bar">
                                <span class="month-fill" style="--value:<?php echo $height; ?>%"></span>
                                <strong><?php echo (int) $month['value']; ?></strong>
                                <span><?php echo htmlspecialchars($month['label']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>
                <article class="analysis-card">
                    <h2>Prioridad</h2>
                    <div class="bars">
                        <?php $maxPriority = max(array_values($priorityStats) ?: [1]); ?>
                        <?php foreach ($priorityStats as $label => $count): ?>
                            <?php $width = $maxPriority > 0 ? round(((int) $count / $maxPriority) * 100) : 0; ?>
                            <div class="bar-row">
                                <div class="bar-meta"><span><?php echo htmlspecialchars($label); ?></span><strong><?php echo (int) $count; ?></strong></div>
                                <div class="bar-track"><span class="bar-fill" style="--value:<?php echo $width; ?>%"></span></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>
                <article class="analysis-card">
                    <h2>Temas más solicitados</h2>
                    <div class="bars">
                        <?php foreach ($topTypes as $label => $count): ?>
                            <?php $width = $maxType > 0 ? round(((int) $count / $maxType) * 100) : 0; ?>
                            <div class="bar-row">
                                <div class="bar-meta"><span><?php echo htmlspecialchars($label); ?></span><strong><?php echo (int) $count; ?></strong></div>
                                <div class="bar-track"><span class="bar-fill" style="--value:<?php echo $width; ?>%"></span></div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (!$topTypes): ?><p class="empty-chart">Aun no hay temas registrados.</p><?php endif; ?>
                    </div>
                </article>
                <article class="analysis-card wide">
                    <h2>Usuarios que generan más tickets</h2>
                    <div class="bars">
                        <?php foreach ($topUsers as $label => $count): ?>
                            <?php $width = $maxUser > 0 ? round(((int) $count / $maxUser) * 100) : 0; ?>
                            <div class="bar-row">
                                <div class="bar-meta"><span><?php echo htmlspecialchars($label); ?></span><strong><?php echo (int) $count; ?></strong></div>
                                <div class="bar-track"><span class="bar-fill" style="--value:<?php echo $width; ?>%"></span></div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (!$topUsers): ?><p class="empty-chart">Aun no hay usuarios con tickets registrados.</p><?php endif; ?>
                    </div>
                </article>
            </section>
        </main>
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
