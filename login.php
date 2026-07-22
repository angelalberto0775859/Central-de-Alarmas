<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/marketing_config.php';

if (cdaCurrentUser()) {
    header('Location: panel-marketing.php');
    exit;
}

$error = '';
$googleErrorMessages = [
    'google_state' => 'No fue posible validar la sesión de Google. Intenta nuevamente.',
    'google_code' => 'Google no devolvió un código válido. Intenta nuevamente.',
    'google_token' => 'No fue posible completar la conexión con Google.',
    'google_profile' => 'Google no devolvió un perfil válido.',
    'google_email' => 'Google no confirmó un correo verificado.',
    'google_not_allowed' => 'Ese correo de Google no está dado de alta en el panel.',
    'google_account_mismatch' => 'Ese correo ya está vinculado con otra cuenta de Google.',
    'google_config' => 'Google Login todavía no está configurado. Agrega Client ID y Secret.',
    'google' => 'No fue posible iniciar sesión con Google.',
];
if (!empty($_GET['error']) && isset($googleErrorMessages[$_GET['error']])) {
    $error = $googleErrorMessages[$_GET['error']];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cdaRequirePostCsrf();
    $correo = filter_var(trim($_POST['correo'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = (string) ($_POST['password'] ?? '');
    $lockedUntil = (int) ($_SESSION['cda_login_locked_until'] ?? 0);

    if ($lockedUntil > time()) {
        $error = 'Demasiados intentos. Intenta nuevamente en unos minutos.';
    } elseif (!$correo || !$password) {
        $error = 'Escribe correo y contraseña.';
    } else {
        try {
            $stmt = cdaDb()->prepare('SELECT id, password_hash, activo FROM marketing_usuarios WHERE correo = ? LIMIT 1');
            $stmt->execute([$correo]);
            $user = $stmt->fetch();

            if ($user && (int) $user['activo'] === 1 && $user['password_hash'] && password_verify($password, $user['password_hash'])) {
                cdaLoginUser($user['id']);
                header('Location: panel-marketing.php');
                exit;
            }

            $_SESSION['cda_login_failures'] = (int) ($_SESSION['cda_login_failures'] ?? 0) + 1;
            if ($_SESSION['cda_login_failures'] >= 5) {
                $_SESSION['cda_login_locked_until'] = time() + 600;
            }
            $error = 'Correo o contrasena incorrectos.';
        } catch (Throwable $e) {
            $error = 'No fue posible iniciar sesion. Revisa la configuracion.';
        }
    }
}

$googleReady = cdaGoogleOAuthReady();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | Diseño y Marketing</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/svg+xml" href="/contigo/Img/favicon.svg">
    <style>
        :root {
            --blue:#063970;
            --blue-2:#0b4f92;
            --ink:#10213f;
            --muted:#6b7890;
            --line:rgba(255,255,255,.18);
            --line-dark:rgba(6,57,112,.13);
            --soft:#f4f8fc;
            --yellow:#f6eb17;
            --white:#fff;
            --radius:8px;
            --shadow:0 28px 80px rgba(0,0,0,.28);
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
        }
        .ambient-points {
            position:fixed;
            inset:0;
            overflow:hidden;
            pointer-events:none;
            z-index:0;
        }
        .ambient-point {
            --size:2px;
            --x:50vw;
            --y:50vh;
            --dx:18px;
            --dy:-22px;
            --duration:18s;
            --delay:0s;
            position:absolute;
            left:var(--x);
            top:var(--y);
            width:var(--size);
            height:var(--size);
            border-radius:50%;
            background:rgba(255,255,255,.55);
            box-shadow:0 0 calc(var(--size) * 4) rgba(166,205,255,.28);
            opacity:.42;
            transform:translate3d(0,0,0);
            animation:ambientDrift var(--duration) ease-in-out var(--delay) infinite alternate;
        }
        @keyframes ambientDrift {
            0% { transform:translate3d(0,0,0); opacity:.18; }
            38% { opacity:.56; }
            100% { transform:translate3d(var(--dx), var(--dy), 0); opacity:.32; }
        }
        .shell {
            width:min(1120px, calc(100% - 2rem));
            min-height:100vh;
            margin:0 auto;
            padding:1.1rem 0 2.4rem;
            display:grid;
            grid-template-rows:auto 1fr;
            position:relative;
            z-index:1;
        }
        .topbar {
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:1rem;
            min-height:62px;
            color:#fff;
        }
        .brand img { width:154px; display:block; }
        .nav { display:flex; flex-wrap:wrap; gap:.62rem; justify-content:flex-end; }
        .nav a {
            min-height:40px;
            display:inline-flex;
            align-items:center;
            border:1px solid rgba(255,255,255,.22);
            border-radius:var(--radius);
            padding:.62rem .78rem;
            color:rgba(255,255,255,.88);
            text-decoration:none;
            font-size:.78rem;
            font-weight:900;
            letter-spacing:.04em;
            text-transform:uppercase;
            background:rgba(255,255,255,.08);
        }
        .login-grid {
            display:grid;
            grid-template-columns:minmax(0, .9fr) minmax(360px, 440px);
            gap:clamp(1rem, 4vw, 2.4rem);
            align-items:center;
            padding:clamp(2rem, 7vw, 5.2rem) 0 1.5rem;
        }
        .intro { color:#fff; }
        .eyebrow {
            color:var(--yellow);
            font-size:.78rem;
            font-weight:950;
            letter-spacing:.13em;
            text-transform:uppercase;
            margin-bottom:.8rem;
        }
        h1 {
            max-width:650px;
            font-size:clamp(2.4rem, 7vw, 5.2rem);
            line-height:.95;
            letter-spacing:0;
            font-weight:900;
        }
        .intro p {
            max-width:590px;
            margin-top:1rem;
            color:rgba(255,255,255,.76);
            font-size:1.03rem;
            line-height:1.7;
        }
        .status-strip {
            display:grid;
            grid-template-columns:repeat(3, minmax(0, 1fr));
            gap:.72rem;
            margin-top:1.4rem;
            max-width:650px;
        }
        .status-chip {
            border:1px solid rgba(255,255,255,.16);
            border-radius:var(--radius);
            padding:.85rem;
            background:rgba(255,255,255,.08);
            backdrop-filter:blur(14px);
            color:rgba(255,255,255,.78);
            min-height:86px;
        }
        .status-chip strong {
            display:block;
            color:#fff;
            font-size:1.35rem;
            line-height:1;
            margin-bottom:.35rem;
        }
        .card {
            border:1px solid rgba(255,255,255,.24);
            border-radius:var(--radius);
            background:linear-gradient(180deg, rgba(255,255,255,.97), rgba(248,251,255,.94));
            box-shadow:var(--shadow);
            padding:clamp(1.2rem, 3vw, 1.65rem);
            position:relative;
            overflow:hidden;
        }
        .card::before {
            content:"";
            position:absolute;
            inset:0 0 auto;
            height:5px;
            background:linear-gradient(90deg, var(--yellow), #fff36a, var(--blue-2));
        }
        .card-head { display:flex; justify-content:space-between; gap:1rem; align-items:start; margin-bottom:1rem; }
        .card h2 { color:var(--blue); font-size:1.55rem; line-height:1.1; }
        .secure-pill {
            border:1px solid var(--line-dark);
            border-radius:999px;
            padding:.45rem .62rem;
            color:var(--blue);
            background:var(--soft);
            font-size:.72rem;
            font-weight:950;
            white-space:nowrap;
        }
        .card p { color:var(--muted); line-height:1.55; margin-bottom:1rem; }
        form { display:grid; gap:.85rem; }
        label { display:grid; gap:.42rem; font-size:.78rem; font-weight:900; color:var(--ink); }
        input {
            width:100%;
            border:1px solid var(--line-dark);
            border-radius:var(--radius);
            padding:.92rem .95rem;
            font:inherit;
            color:var(--ink);
            background:#fff;
            outline:none;
            transition:border-color .2s ease, box-shadow .2s ease, transform .2s ease;
        }
        input:focus {
            border-color:var(--blue);
            box-shadow:0 0 0 4px rgba(6,57,112,.1);
        }
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
            display:flex;
            justify-content:center;
            align-items:center;
            box-shadow:0 14px 28px rgba(6,57,112,.16);
        }
        .button.google {
            margin-top:.75rem;
            background:#fff;
            border:1px solid var(--line-dark);
            color:var(--ink);
            text-transform:none;
            box-shadow:none;
        }
        .links {
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:.7rem;
            margin-top:1rem;
        }
        .links a {
            border:1px solid var(--line-dark);
            border-radius:var(--radius);
            padding:.74rem;
            color:var(--blue);
            background:#fff;
            font-weight:900;
            text-align:center;
            text-decoration:none;
            font-size:.82rem;
        }
        .error, .note { margin:.8rem 0; border-radius:var(--radius); padding:.78rem; font-weight:800; line-height:1.45; }
        .error { color:#b91c1c; background:#fee2e2; border:1px solid #fecaca; }
        .note { color:#7c5800; background:#fff8cf; border:1px solid #f5df76; }
        @media (max-width:860px) {
            .login-grid { grid-template-columns:1fr; align-items:start; }
            .intro { padding-top:1.2rem; }
        }
        @media (max-width:620px) {
            .shell { width:min(100% - 1rem, 1120px); }
            .topbar { align-items:flex-start; flex-direction:column; }
            .nav { justify-content:flex-start; }
            .status-strip, .links { grid-template-columns:1fr; }
            .card-head { flex-direction:column; }
        }
        @media (prefers-reduced-motion:reduce) {
            .ambient-point { animation:none; }
        }
    </style>
</head>
<body>
    <div class="ambient-points" aria-hidden="true"></div>
    <div class="shell">
        <header class="topbar">
            <a class="brand" href="index.html" aria-label="Central de Alarmas">
                <img src="img/cda-logo-f.svg" alt="Central de Alarmas">
            </a>
            <nav class="nav" aria-label="Navegacion">
                <a href="marketing.html">Crear ticket</a>
                <a href="seguimiento.php">Seguimiento</a>
            </nav>
        </header>

        <main class="login-grid">
            <section class="intro" aria-label="Portal interno">
                <div class="eyebrow">Diseño y Marketing · Acceso interno</div>
                <h1>Control de tickets con seguimiento claro.</h1>
                <p>Entra al panel para revisar solicitudes, cambiar estados, asignar responsables y mantener informadas a las areas que pidieron apoyo creativo.</p>
                <div class="status-strip" aria-label="Funciones del panel">
                    <div class="status-chip"><strong>01</strong>Altas y filtros por folio.</div>
                    <div class="status-chip"><strong>02</strong>Estados visibles para usuarios.</div>
                    <div class="status-chip"><strong>03</strong>Correos a administradores.</div>
                </div>
            </section>

            <article class="card">
                <div class="card-head">
                    <div>
                        <h2>Iniciar sesión</h2>
                        <p>Usa un correo autorizado del equipo.</p>
                    </div>
                    <span class="secure-pill">Acceso seguro</span>
                </div>
                <?php if ($error): ?><div class="error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                <form method="post" action="login.php">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(cdaCsrfToken()); ?>">
                    <label>Correo autorizado <input name="correo" type="email" autocomplete="email" required placeholder="usuario@centraldealarmas.com.mx"></label>
                    <label>Contraseña <input name="password" type="password" autocomplete="current-password" required placeholder="Tu contraseña"></label>
                    <button type="submit">Entrar al panel</button>
                </form>
                <?php if ($googleReady): ?>
                    <a class="button google" href="google-login.php">Continuar con Google</a>
                <?php else: ?>
                    <div class="note">Google Login se activa al agregar Client ID y Secret en <strong>php/marketing_secrets.php</strong> o como variables de entorno.</div>
                <?php endif; ?>
                <div class="links">
                    <a href="marketing.html">Nueva solicitud</a>
                    <a href="seguimiento.php">Consultar ticket</a>
                </div>
            </article>
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
