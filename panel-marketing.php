<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/marketing_helpers.php';

$user = cdaRequireLogin();
$estado = cdaMarketingClean($_GET['estado'] ?? '');
$query = cdaMarketingClean($_GET['q'] ?? '');
$params = [];
$where = [];

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
        :root { --blue:#063970; --ink:#10213f; --muted:#66758d; --line:rgba(6,57,112,.14); --soft:#f4f8fc; --yellow:#f6eb17; --radius:8px; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { min-height:100vh; font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif; color:var(--ink); background:#f3f7fb; }
        .shell { width:min(1220px, calc(100% - 2rem)); margin:0 auto; padding:1.2rem 0 3rem; }
        .topbar { display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-bottom:1.2rem; }
        .topbar img { width:146px; display:block; }
        .nav { display:flex; flex-wrap:wrap; gap:.6rem; align-items:center; }
        .nav a, button { color:var(--blue); text-decoration:none; border:1px solid var(--line); border-radius:8px; padding:.7rem .85rem; background:#fff; font-size:.82rem; font-weight:850; cursor:pointer; }
        .hero { display:flex; justify-content:space-between; gap:1rem; align-items:end; margin-bottom:1rem; }
        h1 { color:var(--blue); font-size:clamp(1.8rem,4vw,3rem); }
        .card { background:#fff; border:1px solid var(--line); border-radius:var(--radius); padding:1rem; box-shadow:0 12px 38px rgba(6,57,112,.08); }
        .filters { display:grid; grid-template-columns:1fr 240px auto; gap:.75rem; margin-bottom:1rem; }
        input, select, textarea { width:100%; border:1px solid var(--line); border-radius:var(--radius); padding:.82rem .9rem; font:inherit; background:#fff; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:.78rem; border-bottom:1px solid var(--line); text-align:left; vertical-align:top; font-size:.88rem; }
        th { color:var(--blue); font-size:.75rem; text-transform:uppercase; letter-spacing:.04em; }
        .folio { font-weight:900; color:var(--blue); }
        .status { display:inline-flex; border-radius:999px; padding:.38rem .6rem; background:var(--soft); color:var(--blue); font-size:.75rem; font-weight:900; white-space:nowrap; }
        .priority { font-weight:900; }
        .inline-form { display:grid; gap:.5rem; min-width:260px; }
        .inline-form button, .filters button { background:var(--yellow); border-color:transparent; color:var(--blue); text-transform:uppercase; }
        .muted { color:var(--muted); }
        @media (max-width:900px) { .filters, .hero { grid-template-columns:1fr; flex-direction:column; align-items:stretch; } table, tbody, tr, td { display:block; } thead { display:none; } tr { border:1px solid var(--line); border-radius:var(--radius); margin-bottom:.8rem; background:#fff; } td { border:0; } .inline-form { min-width:0; } }
    </style>
</head>
<body>
    <div class="shell">
        <header class="topbar">
            <a href="index.html"><img src="img/cda-logo-f.svg" alt="Central de Alarmas"></a>
            <nav class="nav" aria-label="Navegacion">
                <span class="muted"><?php echo htmlspecialchars($user['nombre']); ?></span>
                <?php if ($user['rol'] === 'admin'): ?><a href="usuarios-marketing.php">Usuarios</a><?php endif; ?>
                <a href="marketing.html">Crear ticket</a>
                <a href="seguimiento.php">Seguimiento publico</a>
                <a href="logout.php">Salir</a>
            </nav>
        </header>
        <section class="hero">
            <div>
                <h1>Tickets de Diseño y Marketing</h1>
                <p class="muted">Alta, revision y actualizacion de solicitudes internas.</p>
            </div>
        </section>
        <section class="card">
            <form class="filters" method="get" action="panel-marketing.php">
                <input name="q" value="<?php echo htmlspecialchars($query); ?>" placeholder="Buscar folio, solicitante, correo o actividad">
                <select name="estado">
                    <option value="">Todos los estados</option>
                    <?php foreach (cdaMarketingStatuses() as $status): ?>
                        <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $estado === $status ? 'selected' : ''; ?>><?php echo htmlspecialchars($status); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Filtrar</button>
            </form>
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
                    <tr>
                        <td><div class="folio"><?php echo htmlspecialchars($ticket['folio']); ?></div><div class="muted"><?php echo htmlspecialchars($ticket['solicitante']); ?><br><?php echo htmlspecialchars($ticket['correo']); ?></div></td>
                        <td><strong><?php echo htmlspecialchars($ticket['actividad']); ?></strong><br><span class="muted"><?php echo htmlspecialchars($ticket['tipo_solicitud']); ?> · <?php echo htmlspecialchars($ticket['departamento']); ?></span></td>
                        <td><span class="status"><?php echo htmlspecialchars($ticket['estado']); ?></span><br><span class="priority"><?php echo htmlspecialchars($ticket['prioridad']); ?></span></td>
                        <td><?php echo htmlspecialchars(cdaMarketingFormatDate($ticket['fecha_requerida'])); ?><br><span class="muted">Actualizado <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($ticket['actualizado_en']))); ?></span></td>
                        <td>
                            <form class="inline-form" method="post" action="ticket-actualizar.php">
                                <input type="hidden" name="id" value="<?php echo (int) $ticket['id']; ?>">
                                <select name="estado" required>
                                    <?php foreach (cdaMarketingStatuses() as $status): ?>
                                        <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $ticket['estado'] === $status ? 'selected' : ''; ?>><?php echo htmlspecialchars($status); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input name="asignado_a" value="<?php echo htmlspecialchars($ticket['asignado_a'] ?? ''); ?>" placeholder="Asignado a">
                                <textarea name="respuesta_interna" rows="2" placeholder="Comentario visible para seguimiento"><?php echo htmlspecialchars($ticket['respuesta_interna'] ?? ''); ?></textarea>
                                <button type="submit">Guardar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$tickets): ?>
                    <tr><td colspan="5" class="muted">No hay tickets con esos filtros.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>
