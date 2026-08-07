<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/marketing_helpers.php';

$user = cdaRequireLogin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centro de Gestión Creativa | Central de Alarmas</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#063970">
    <link rel="icon" type="image/svg+xml" href="/contigo/Img/favicon.svg">
    <link rel="preload" href="img/cda-logo-f.svg" as="image">
    <style>
        :root {
            --blue: #063970;
            --blue-2: #0b4f92;
            --ink: #10213f;
            --muted: #66758d;
            --line: rgba(6, 57, 112, 0.12);
            --soft: #f4f8fc;
            --yellow: #f6eb17;
            --white: #ffffff;
            --radius: 8px;
            --shadow: 0 22px 60px rgba(6, 57, 112, 0.13);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background:
                radial-gradient(circle at 20% 12%, rgba(246,235,23,0.16), transparent 24rem),
                linear-gradient(135deg, rgba(6,57,112,0.97), rgba(6,20,50,0.95)),
                url("img/optimized/hero-poster-v2.jpg") center/cover fixed;
            color: var(--ink);
            overflow-x: hidden;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(circle at 8% 14%, rgba(255,255,255,0.78) 0 0.8px, transparent 1.2px),
                radial-gradient(circle at 18% 76%, rgba(255,255,255,0.52) 0 1.1px, transparent 1.6px),
                radial-gradient(circle at 31% 28%, rgba(246,235,23,0.72) 0 0.9px, transparent 1.35px),
                radial-gradient(circle at 42% 62%, rgba(255,255,255,0.62) 0 1.4px, transparent 2px),
                radial-gradient(circle at 55% 18%, rgba(255,255,255,0.42) 0 0.7px, transparent 1.1px),
                radial-gradient(circle at 67% 84%, rgba(246,235,23,0.54) 0 1px, transparent 1.45px),
                radial-gradient(circle at 76% 36%, rgba(255,255,255,0.7) 0 1.2px, transparent 1.7px),
                radial-gradient(circle at 91% 68%, rgba(255,255,255,0.5) 0 0.85px, transparent 1.25px),
                radial-gradient(circle at 96% 22%, rgba(255,255,255,0.68) 0 1.5px, transparent 2.1px);
            background-size: 430px 310px;
            opacity: 0.58;
            animation: starDrift 72s linear infinite;
            mask-image: linear-gradient(180deg, rgba(0,0,0,0.95), rgba(0,0,0,0.24));
        }

        @keyframes starDrift {
            from { background-position: 0 0; }
            to { background-position: 360px 220px; }
        }

        @media (prefers-reduced-motion: reduce) {
            body::before { animation: none; }
        }

        .page-shell {
            width: min(1180px, calc(100% - 2rem));
            margin: 0 auto;
            padding: 1.2rem 0 3rem;
            position: relative;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            min-height: 64px;
            color: #fff;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 0.85rem;
            text-decoration: none;
            color: inherit;
        }

        .brand img {
            width: 146px;
            height: auto;
            display: block;
        }

        .nav {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
            align-items: center;
            justify-content: flex-end;
        }

        .nav a {
            display: inline-flex;
            align-items: center;
            min-height: 40px;
            border: 1px solid rgba(255,255,255,0.24);
            border-radius: var(--radius);
            padding: 0.62rem 0.78rem;
            color: rgba(255,255,255,0.88);
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 850;
            background: rgba(255,255,255,0.08);
            white-space: nowrap;
            transition: background 0.18s ease, color 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .nav a:hover { background: rgba(255,255,255,0.15); color: #fff; border-color: rgba(255,255,255,0.34); }
        .nav a.active { background: rgba(255,255,255,0.18); color: #fff; border-color: rgba(246,235,23,0.48); box-shadow: none; }
        .nav a:focus-visible { outline: 3px solid rgba(246,235,23,0.52); outline-offset: 2px; }

        .profile-menu {
            position: relative;
        }

        .profile-menu summary {
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            list-style: none;
            border: 1px solid rgba(255,255,255,0.24);
            border-radius: var(--radius);
            padding: 0.62rem 0.78rem;
            color: #fff;
            background: rgba(255,255,255,0.1);
            font-size: 0.8rem;
            font-weight: 850;
            cursor: pointer;
        }

        .profile-menu summary::-webkit-details-marker { display: none; }

        .profile-menu summary::after {
            content: "";
            width: 0.45rem;
            height: 0.45rem;
            border-right: 2px solid currentColor;
            border-bottom: 2px solid currentColor;
            transform: rotate(45deg) translateY(-2px);
            opacity: 0.75;
        }

        .profile-menu[open] summary {
            background: rgba(255,255,255,0.16);
            border-color: rgba(255,255,255,0.36);
        }
        .profile-menu.role-usuario summary { border-color:rgba(246,235,23,.82); box-shadow:0 0 0 1px rgba(246,235,23,.18); }
        .profile-menu.role-admin summary { border-color:rgba(248,113,113,.9); box-shadow:0 0 0 1px rgba(248,113,113,.2); }
        .profile-menu.role-trabajador summary { border-color:rgba(34,197,94,.88); box-shadow:0 0 0 1px rgba(34,197,94,.18); }
        .profile-menu.role-manager summary { border-color:rgba(96,165,250,.88); box-shadow:0 0 0 1px rgba(96,165,250,.18); }

        .profile-dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + 0.45rem);
            z-index: 10;
            display: grid;
            min-width: 190px;
            padding: 0.45rem;
            border: 1px solid rgba(6,57,112,0.12);
            border-radius: var(--radius);
            background: #fff;
            box-shadow: 0 18px 40px rgba(0,0,0,0.18);
        }

        .profile-dropdown a {
            min-height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: flex-start;
            border: 0;
            border-radius: 6px;
            padding: 0.55rem 0.65rem;
            background: #fff;
            color: var(--ink);
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 850;
            box-shadow: none;
        }

        .profile-dropdown a:hover {
            background: var(--soft);
            color: var(--blue);
        }

        .profile-dropdown a.logout-link { color: #991b1b; }

        .hero {
            padding: clamp(3rem, 7vw, 6.5rem) 0 clamp(2.4rem, 5vw, 4rem);
            color: #fff;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(340px, 0.48fr);
            align-items: center;
            gap: clamp(2rem, 6vw, 5rem);
        }

        .hero-title-stack {
            display: grid;
            gap: 1rem;
        }

        .hero-label {
            color: var(--yellow);
            font-size: 0.78rem;
            font-weight: 900;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        h1 {
            max-width: 830px;
            font-size: clamp(2.45rem, 6vw, 5.45rem);
            line-height: 0.98;
            letter-spacing: 0;
            font-weight: 880;
        }

        h1 span {
            display: block;
            color: rgba(255,255,255,0.72);
            font-size: clamp(1.6rem, 3.8vw, 3.35rem);
            font-weight: 650;
            margin-top: 0.18rem;
        }

        .hero p {
            max-width: 700px;
            margin-top: 1.35rem;
            color: rgba(255,255,255,0.78);
            font-size: clamp(1rem, 2vw, 1.18rem);
            line-height: 1.75;
        }

        .hero-kpis {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.9rem;
            max-width: 720px;
            margin-top: 1.65rem;
        }

        .hero-kpi {
            border: 1px solid rgba(255,255,255,0.16);
            border-radius: var(--radius);
            padding: 1rem;
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(14px);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.1);
            min-height: 108px;
        }

        .hero-kpi strong {
            display: block;
            color: #fff;
            font-size: 1.3rem;
            line-height: 1;
            margin-bottom: 0.32rem;
        }

        .hero-kpi span {
            color: rgba(255,255,255,0.72);
            font-size: 0.76rem;
            font-weight: 850;
            line-height: 1.35;
        }

        .hero-points {
            display: flex;
            flex-wrap: wrap;
            gap: 0.72rem;
            margin-top: 1.65rem;
        }

        .hero-points span {
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 999px;
            padding: 0.48rem 0.7rem;
            background: rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.84);
            font-size: 0.78rem;
            font-weight: 800;
        }

        .process-panel {
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: var(--radius);
            padding: clamp(1.15rem, 2.4vw, 1.55rem);
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(18px);
        }

        .process-panel h2 {
            color: #fff;
            font-size: 0.9rem;
            margin-bottom: 0.85rem;
        }

        .urgent-note {
            margin-top: 0.95rem;
            border: 1px solid rgba(246,235,23,0.26);
            border-radius: var(--radius);
            padding: 0.78rem;
            background: rgba(246,235,23,0.09);
            color: rgba(255,255,255,0.82);
            font-size: 0.78rem;
            line-height: 1.45;
        }

        .urgent-note strong {
            color: var(--yellow);
        }

        .status-track {
            display: grid;
            gap: 0.55rem;
        }

        .status-step {
            display: grid;
            grid-template-columns: 26px 1fr;
            align-items: center;
            gap: 0.65rem;
            color: rgba(255,255,255,0.74);
            font-size: 0.82rem;
            line-height: 1.35;
        }

        .status-step span {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: rgba(255,255,255,0.12);
            color: var(--yellow);
            font-size: 0.74rem;
            font-weight: 900;
        }

        .workspace {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(300px, 0.42fr);
            gap: clamp(1.2rem, 3vw, 2rem);
            align-items: start;
        }

        .request-card,
        .ideas-card,
        .ticket-preview {
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: rgba(255,255,255,0.96);
            box-shadow: var(--shadow);
        }

        .request-card {
            padding: clamp(1.35rem, 3.4vw, 2.4rem);
        }

        .section-head {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: start;
            margin-bottom: 1.4rem;
        }

        .section-head h2 {
            color: var(--blue);
            font-size: clamp(1.35rem, 3vw, 2rem);
            line-height: 1.12;
        }

        .ticket-number {
            flex: 0 0 auto;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 0.55rem 0.7rem;
            background: var(--soft);
            color: var(--blue);
            font-size: 0.76rem;
            font-weight: 900;
            letter-spacing: 0.06em;
        }

        form {
            display: grid;
            gap: 1.45rem;
        }

        .form-section {
            display: grid;
            gap: 1.15rem;
            border: 1px solid rgba(6,57,112,0.09);
            border-radius: var(--radius);
            padding: clamp(1rem, 2.2vw, 1.35rem);
            background: linear-gradient(180deg, #fff, #fbfdff);
        }

        .form-section-title {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            color: var(--blue);
            font-size: 0.88rem;
            font-weight: 900;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .form-section-title span {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: var(--blue);
            color: #fff;
            font-size: 0.76rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.1rem;
        }

        .field {
            display: grid;
            gap: 0.42rem;
        }

        .field.full { grid-column: 1 / -1; }

        label {
            color: var(--ink);
            font-size: 0.78rem;
            font-weight: 850;
        }

        input,
        select,
        textarea {
            width: 100%;
            border: 1px solid rgba(6,57,112,0.16);
            border-radius: var(--radius);
            background: #fff;
            color: var(--ink);
            font: inherit;
            font-size: 0.94rem;
            padding: 0.86rem 0.9rem;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        textarea {
            min-height: 128px;
            resize: vertical;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 4px rgba(6,57,112,0.1);
        }

        .file-drop {
            border: 1px dashed rgba(6,57,112,0.32);
            border-radius: var(--radius);
            background: linear-gradient(180deg, #fff, #f7fbff);
            padding: 1rem;
            cursor: pointer;
        }

        .file-drop input {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
            pointer-events: none;
        }

        .file-copy {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            color: var(--muted);
            font-size: 0.88rem;
        }

        .file-copy strong {
            display: block;
            color: var(--blue);
            font-size: 0.95rem;
        }

        .upload-mark {
            flex: 0 0 auto;
            border-radius: var(--radius);
            background: var(--blue);
            color: #fff;
            padding: 0.58rem 0.76rem;
            font-size: 0.78rem;
            font-weight: 850;
        }

        .file-list {
            margin-top: 0.65rem;
            color: var(--blue);
            font-size: 0.78rem;
            font-weight: 750;
        }

        .activity-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
        }

        .chip {
            border: 1px solid rgba(6,57,112,0.16);
            border-radius: 999px;
            background: #fff;
            color: var(--blue);
            padding: 0.5rem 0.72rem;
            font-size: 0.78rem;
            font-weight: 800;
            cursor: pointer;
        }

        .chip:hover {
            background: var(--blue);
            color: #fff;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 0.75rem;
            padding-top: 0.5rem;
        }

        .btn {
            min-height: 46px;
            border: 0;
            border-radius: var(--radius);
            padding: 0.82rem 1.05rem;
            font: inherit;
            font-size: 0.84rem;
            font-weight: 900;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            cursor: pointer;
        }

        .btn.secondary {
            background: var(--soft);
            color: var(--blue);
            border: 1px solid var(--line);
        }

        .btn.primary {
            background: var(--yellow);
            color: var(--blue);
            box-shadow: 0 12px 26px rgba(6,57,112,0.18);
        }

        .side-stack {
            display: grid;
            gap: 1.2rem;
        }

        .ideas-card,
        .ticket-preview {
            padding: 1.2rem;
        }

        .ideas-card h2,
        .ticket-preview h2 {
            color: var(--blue);
            font-size: 1rem;
            margin-bottom: 0.85rem;
        }

        .idea-list {
            display: grid;
            gap: 0.58rem;
        }

        .idea-item {
            border: 1px solid rgba(6,57,112,0.1);
            border-radius: var(--radius);
            padding: 0.72rem;
            background: #f8fbff;
            color: var(--muted);
            font-size: 0.82rem;
            line-height: 1.35;
        }

        .idea-item strong {
            display: block;
            color: var(--ink);
            margin-bottom: 0.18rem;
        }

        .preview-row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.62rem 0;
            border-top: 1px solid rgba(6,57,112,0.1);
            color: var(--muted);
            font-size: 0.82rem;
        }

        .preview-row strong {
            color: var(--ink);
            text-align: right;
        }

        .notice {
            display: none;
            margin-top: 1rem;
            border: 1px solid rgba(16,185,129,0.25);
            border-radius: var(--radius);
            padding: 1rem;
            background: rgba(16,185,129,0.08);
            color: #047857;
            font-size: 0.9rem;
            font-weight: 750;
        }

        .notice.visible { display: block; }

        .notice.error {
            border-color: rgba(220,38,38,0.28);
            background: rgba(220,38,38,0.08);
            color: #b91c1c;
        }

        .success-card {
            display: grid;
            gap: 0.75rem;
        }

        .success-card h3 {
            color: #047857;
            font-size: 1.12rem;
        }

        .success-folio {
            width: fit-content;
            border: 1px solid rgba(4,120,87,0.24);
            border-radius: var(--radius);
            padding: 0.55rem 0.75rem;
            background: rgba(255,255,255,0.76);
            color: var(--blue);
            font-size: 1.05rem;
            font-weight: 950;
            letter-spacing: 0.04em;
        }

        .success-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
        }

        .success-actions a,
        .success-actions button {
            min-height: 38px;
            border: 1px solid rgba(4,120,87,0.18);
            border-radius: var(--radius);
            padding: 0.58rem 0.75rem;
            background: #fff;
            color: #047857;
            text-decoration: none;
            font: inherit;
            font-size: 0.78rem;
            font-weight: 900;
            cursor: pointer;
        }

        @media (max-width: 900px) {
            .hero-grid,
            .workspace {
                grid-template-columns: 1fr;
            }

            .process-panel {
                max-width: 560px;
            }

            .hero-kpis {
                max-width: 560px;
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .page-shell {
                width: min(100% - 1rem, 1180px);
                padding-top: 0.8rem;
            }

            .topbar,
            .section-head,
            .actions {
                align-items: stretch;
                flex-direction: column;
            }

            .nav {
                justify-content: flex-start;
            }

            .profile-dropdown {
                left: 0;
                right: auto;
            }

            .ticket-number {
                width: fit-content;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .hero-kpis {
                grid-template-columns: 1fr;
            }

            .request-card,
            .ideas-card,
            .ticket-preview,
            .process-panel {
                padding: 1rem;
            }

            .form-section {
                padding: 0.85rem;
            }

            .btn {
                width: 100%;
            }

            .success-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <header class="topbar">
            <a class="brand" href="index.html" aria-label="Ir al inicio de Central de Alarmas">
                <img src="img/cda-logo-f.svg" alt="Central de Alarmas">
            </a>
            <nav class="nav" aria-label="Navegacion">
                <?php if (cdaMarketingCanViewAllTickets($user['rol'])): ?><a class="admin-link" href="panel-marketing.php">Tickets</a><?php endif; ?>
                <?php if (cdaMarketingCanAccessBoard($user['rol'])): ?><a class="admin-link" href="control-marketing.php">Tablero</a><?php endif; ?>
                <?php if (cdaMarketingCanManageUsers($user['rol'])): ?><a class="admin-link" href="usuarios-marketing.php">Usuarios</a><?php endif; ?>
                <?php if (cdaMarketingCanManageTrash($user['rol'])): ?><a class="admin-link" href="panel-marketing.php?papelera=1">Basurero</a><?php endif; ?>
                <a class="public-link active" href="crear-ticket.php">Crear ticket</a>
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

        <main>
            <section class="hero">
                <div class="hero-grid">
                    <div>
                        <div class="hero-title-stack">
                            <div class="hero-label">Central de Alarmas · Operación creativa</div>
                            <h1>Centro de gestión creativa <span>Marketing</span></h1>
                        </div>
                        <p>
                            Solicita piezas, campañas y materiales con un brief claro, usuario validado y trazabilidad por folio.
                            Desde el alta quedan listos la confirmación por correo, el seguimiento y el chat del ticket.
                        </p>
                        <div class="hero-kpis" aria-label="Beneficios del flujo">
                            <div class="hero-kpi"><strong>01</strong><span>Usuario validado al crear ticket</span></div>
                            <div class="hero-kpi"><strong>02</strong><span>Confirmación por correo y folio</span></div>
                            <div class="hero-kpi"><strong>03</strong><span>Chat de seguimiento con el equipo</span></div>
                        </div>
                        <div class="hero-points" aria-label="Datos necesarios para evaluar la solicitud">
                            <span>Brief operativo</span>
                            <span>Folio de seguimiento</span>
                            <span>Chat del ticket</span>
                            <span>Prioridad visible</span>
                        </div>
                    </div>
                    <aside class="process-panel" aria-label="Proceso de evaluación">
                        <h2>Proceso de solicitud</h2>
                        <div class="status-track">
                            <div class="status-step"><span>1</span>Solicitud recibida con usuario validado.</div>
                            <div class="status-step"><span>2</span>Confirmación enviada al solicitante y admins.</div>
                            <div class="status-step"><span>3</span>Chat abierto para aclaraciones y ajustes.</div>
                            <div class="status-step"><span>4</span>Entrega final con historial por folio.</div>
                        </div>
                        <p class="urgent-note">
                            <strong>Importante:</strong> si la solicitud es urgente y se envía un día antes o muy cerca de la fecha requerida, quedará en análisis. El área evaluará si es posible realizarla o si debe reagendarse.
                        </p>
                    </aside>
                </div>
            </section>

            <section class="workspace" aria-label="Solicitud de ticket">
                <article class="request-card">
                    <div class="section-head">
                        <div>
                            <h2>Crear nueva solicitud</h2>
                            <p style="margin-top:0.45rem;color:var(--muted);line-height:1.55;">
                                Entre más clara sea la actividad, más rápido podremos evaluar y producir el trabajo.
                            </p>
                        </div>
                        <span class="ticket-number" id="ticketNumber">MKT-0000</span>
                    </div>

                    <form id="marketingTicketForm" action="php/marketing_ticket_create.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(cdaCsrfToken()); ?>">
                        <div class="form-section">
                            <div class="form-section-title"><span>1</span>Perfil del solicitante</div>
                            <div class="form-grid">
                                <div class="field">
                                    <label>Solicitante</label>
                                    <input type="text" value="<?php echo htmlspecialchars($user['nombre']); ?>" readonly>
                                </div>
	                                <div class="field">
	                                    <label>Correo de sesión</label>
	                                    <input type="email" value="<?php echo htmlspecialchars($user['correo']); ?>" readonly>
	                                </div>
	                                <div class="field full">
	                                    <label for="department">Área / departamento *</label>
	                                    <select id="department" name="department" required>
                                            <option value="" selected disabled>Selecciona departamento</option>
                                            <option>Ventas</option>
                                            <option>Operaciones</option>
                                            <option>Monitoreo</option>
                                            <option>Atención a clientes</option>
                                            <option>Recursos Humanos</option>
                                            <option>Administración</option>
                                            <option>Tecnología</option>
                                            <option>Dirección</option>
                                            <option>Alianzas</option>
                                            <option>Otro</option>
                                        </select>
	                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-section-title"><span>2</span>Brief de la actividad</div>
                            <div class="form-grid">
                                <div class="field">
                                    <label for="activity">Actividad o proyecto *</label>
                                    <input id="activity" name="activity" type="text" required placeholder="Ej. Campaña para paquete empresarial">
                                </div>
                                <div class="field">
                                    <label for="requestType">Tipo de solicitud *</label>
                                    <select id="requestType" name="requestType" required>
                                        <option value="" selected disabled>Selecciona una opción</option>
                                        <option>Marketing para redes sociales</option>
                                        <option>Material impreso</option>
                                        <option>Presentación corporativa</option>
                                        <option>Landing page</option>
                                        <option>Campaña promocional</option>
                                        <option>Promoción de vacante</option>
                                        <option>Video / reel</option>
                                        <option>Comunicado</option>
                                        <option>Otro</option>
                                    </select>
                                </div>
                                <div class="field full">
                                    <label for="objective">Descripción y objetivo *</label>
                                    <textarea id="objective" name="objective" required placeholder="Explica qué necesitas, para qué se usará, medidas, canales, tono y entregables esperados."></textarea>
                                </div>
                                <div class="field">
                                    <label for="audience">Público objetivo</label>
                                    <select id="audience" name="audience">
                                        <option value="">Selecciona público</option>
                                        <option>Clientes actuales</option>
                                        <option>Prospectos</option>
                                        <option>Colaboradores internos</option>
                                        <option>Sucursales</option>
                                        <option>Administradores</option>
                                        <option>Técnicos</option>
                                        <option>Corporativos</option>
                                        <option>Público general</option>
                                        <option>Otro</option>
                                    </select>
                                </div>
                                <div class="field">
                                    <label for="references">Links de referencia</label>
                                    <input id="references" name="references" type="url" placeholder="https://...">
                                </div>
                            </div>

                            <div class="field full">
                                <label>Propuestas rápidas de actividades</label>
                                <div class="activity-chips" aria-label="Propuestas de actividades">
                                    <button class="chip" type="button">Post para redes sociales</button>
                                    <button class="chip" type="button">Flyer comercial</button>
                                    <button class="chip" type="button">Presentación para cliente</button>
                                    <button class="chip" type="button">Campaña de temporada</button>
                                    <button class="chip" type="button">Promoción de vacante</button>
                                    <button class="chip" type="button">Material para evento</button>
                                    <button class="chip" type="button">Landing page</button>
                                    <button class="chip" type="button">Reel / video corto</button>
                                    <button class="chip" type="button">Comunicado</button>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-section-title"><span>3</span>Entrega y documentos</div>
                            <div class="form-grid">
                                <div class="field">
                                    <label for="neededDate">Fecha requerida *</label>
                                    <input id="neededDate" name="neededDate" type="date" required>
                                </div>
                                <div class="field">
                                    <label for="priority">Prioridad *</label>
                                    <select id="priority" name="priority" required>
                                        <option value="" selected disabled>Selecciona prioridad</option>
                                        <option>Normal</option>
                                        <option>Alta</option>
                                        <option>Urgente</option>
                                    </select>
                                </div>
                                <div class="field full">
                                    <label for="documents">Documentos y archivos</label>
                                    <label class="file-drop" for="documents">
                                        <input id="documents" name="documents[]" type="file" multiple accept="<?php echo htmlspecialchars(cdaMarketingAllowedUploadAccept()); ?>">
                                        <span class="file-copy">
                                            <span>
                                                <strong>Subida de editables y material requerido</strong>
                                                Si agregas material editable, asegúrate de incluir el material requerido y que se utilizará para la creación de lo solicitado. El producto final se entregará por este medio y por correo.
                                            </span>
                                            <span class="upload-mark">Elegir archivos</span>
                                        </span>
                                        <span class="file-list" id="fileList">Sin archivos seleccionados</span>
                                    </label>
                                </div>
                                <div class="field full">
                                    <label for="comments">Comentarios adicionales</label>
                                    <textarea id="comments" name="comments" placeholder="Restricciones, aprobadores, medidas exactas, copys finales o cualquier detalle importante."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="actions">
                            <button class="btn secondary" type="reset">Limpiar</button>
                            <button class="btn primary" type="submit">Enviar solicitud a evaluación</button>
                        </div>
                        <div class="notice" id="successNotice" aria-live="polite">
                            Solicitud preparada. El área de Marketing evaluará la actividad, archivos y fecha requerida antes de producir.
                        </div>
                    </form>
                </article>

                <aside class="side-stack">
                    <section class="ideas-card">
                        <h2>Qué puedes solicitar</h2>
                        <div class="idea-list">
                            <div class="idea-item"><strong>Campañas y promociones</strong>Piezas para lanzar servicios, paquetes o mensajes comerciales.</div>
                            <div class="idea-item"><strong>Material corporativo</strong>Presentaciones, formatos, comunicados, firmas, manuales o materiales internos.</div>
                            <div class="idea-item"><strong>Contenido digital</strong>Posts, historias, banners, landing pages, emailings, reels y piezas web.</div>
                            <div class="idea-item"><strong>Material impreso</strong>Flyers, carteles, folletos, viniles, lonas, señalización o material para evento.</div>
                            <div class="idea-item"><strong>Apoyo creativo</strong>Ideas, conceptos, copy, adaptación de campañas y mejora visual de documentos.</div>
                        </div>
                    </section>

                    <section class="ticket-preview" aria-label="Resumen del ticket">
                        <h2>Resumen vivo</h2>
                        <div class="preview-row"><span>Tipo</span><strong id="previewType">Pendiente</strong></div>
                        <div class="preview-row"><span>Prioridad</span><strong id="previewPriority">Pendiente</strong></div>
                        <div class="preview-row"><span>Fecha requerida</span><strong id="previewDate">Pendiente</strong></div>
                        <div class="preview-row"><span>Archivos</span><strong id="previewFiles">0</strong></div>
                    </section>
                </aside>
            </section>
        </main>
    </div>

    <script>
        const ticketNumber = document.getElementById('ticketNumber');
        const form = document.getElementById('marketingTicketForm');
        const fileInput = document.getElementById('documents');
        const fileList = document.getElementById('fileList');
        const successNotice = document.getElementById('successNotice');
        const previewType = document.getElementById('previewType');
        const previewPriority = document.getElementById('previewPriority');
        const previewDate = document.getElementById('previewDate');
        const previewFiles = document.getElementById('previewFiles');
        const requestType = document.getElementById('requestType');
        const priority = document.getElementById('priority');
        const neededDate = document.getElementById('neededDate');
        const activity = document.getElementById('activity');
        const maxFiles = 5;
        const maxFileSize = 25 * 1024 * 1024;

        const today = new Date();
        const ticketSeed = `${today.getFullYear()}${String(today.getMonth() + 1).padStart(2, '0')}${String(today.getDate()).padStart(2, '0')}`;
        ticketNumber.textContent = `MKT-${ticketSeed}`;
        neededDate.min = today.toISOString().split('T')[0];

        function updatePreview() {
            previewType.textContent = requestType.value || 'Pendiente';
            previewPriority.textContent = priority.value || 'Pendiente';
            previewDate.textContent = neededDate.value || 'Pendiente';
            previewFiles.textContent = String(fileInput.files.length);
        }

        fileInput.addEventListener('change', () => {
            const names = Array.from(fileInput.files).map((file) => file.name);
            const tooManyFiles = fileInput.files.length > maxFiles;
            const tooLargeFile = Array.from(fileInput.files).some((file) => file.size > maxFileSize);

            if (tooManyFiles || tooLargeFile) {
                fileInput.value = '';
                fileList.textContent = tooManyFiles
                    ? 'Selecciona máximo 5 archivos'
                    : 'Cada archivo debe pesar máximo 25 MB';
                updatePreview();
                return;
            }

            fileList.textContent = names.length ? names.join(', ') : 'Sin archivos seleccionados';
            updatePreview();
        });

        [requestType, priority, neededDate].forEach((field) => {
            field.addEventListener('change', updatePreview);
        });

        document.querySelectorAll('.chip').forEach((chip) => {
            chip.addEventListener('click', () => {
                activity.value = chip.textContent.trim();
                activity.focus();
            });
        });

        form.addEventListener('reset', () => {
            setTimeout(() => {
                fileList.textContent = 'Sin archivos seleccionados';
                successNotice.className = 'notice';
                updatePreview();
            }, 0);
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!form.reportValidity()) return;

            const submitButton = form.querySelector('.btn.primary');
            const originalText = submitButton.textContent;
            submitButton.disabled = true;
            submitButton.textContent = 'Enviando...';
            successNotice.className = 'notice';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form)
                });
                const data = await response.json();

                if (!response.ok || !data.exito) {
                    throw new Error(data.mensaje || 'No fue posible enviar la solicitud.');
                }

                ticketNumber.textContent = data.folio;
	                successNotice.innerHTML = `
	                    <div class="success-card">
	                        <h3>Solicitud enviada a evaluación</h3>
	                        <p>El ticket quedó ligado a tu perfil de sesión. Guarda este folio y entra al seguimiento para usar el chat del ticket.</p>
	                        <div class="success-folio">${data.folio}</div>
	                        <div class="success-actions">
	                            <a href="${data.seguimiento}">Abrir seguimiento y chat</a>
	                            <button type="button" id="copyFolio">Copiar folio</button>
	                        </div>
	                    </div>
                `;
                successNotice.className = 'notice visible';
                const copyButton = document.getElementById('copyFolio');
                copyButton?.addEventListener('click', async () => {
                    await navigator.clipboard.writeText(data.folio);
                    copyButton.textContent = 'Folio copiado';
                });
                form.reset();
                fileList.textContent = 'Sin archivos seleccionados';
                updatePreview();
            } catch (error) {
                successNotice.textContent = error.message || 'Hubo un error al enviar el ticket. Intenta nuevamente.';
                successNotice.className = 'notice error visible';
            } finally {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
                successNotice.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });
    </script>
</body>
</html>
