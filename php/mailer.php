<?php
// Envio de correo compartido por el panel de marketing y los formularios del
// sitio. Usa SMTP autenticado cuando CDA_SMTP_PASS existe y mail() si no.
require_once __DIR__ . '/marketing_config.php';

function cdaMarketingSmtpCommand($socket, $command, array $expectedCodes) {
    if ($command !== null) {
        fwrite($socket, $command . "\r\n");
    }

    $response = '';
    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        // Las respuestas multilinea usan "250-"; la ultima linea usa "250 ".
        if (strlen($line) < 4 || $line[3] !== '-') {
            break;
        }
    }

    if (!in_array((int) substr($response, 0, 3), $expectedCodes, true)) {
        throw new RuntimeException('SMTP respondio: ' . trim($response));
    }

    return $response;
}

function cdaMarketingSmtpSend($to, $subject, $message, $headers) {
    $secure = strtolower(CDA_SMTP_SECURE);
    $context = stream_context_create(['ssl' => ['peer_name' => CDA_SMTP_PEER_NAME]]);
    $address = ($secure === 'ssl' ? 'ssl://' : 'tcp://') . CDA_SMTP_HOST . ':' . CDA_SMTP_PORT;
    $socket = stream_socket_client($address, $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $context);
    if (!$socket) {
        throw new RuntimeException('No fue posible conectar a SMTP: ' . $errstr);
    }
    stream_set_timeout($socket, 20);

    try {
        $host = parse_url(CDA_SITE_URL, PHP_URL_HOST) ?: 'localhost';
        cdaMarketingSmtpCommand($socket, null, [220]);
        cdaMarketingSmtpCommand($socket, 'EHLO ' . $host, [250]);

        if ($secure === 'tls') {
            cdaMarketingSmtpCommand($socket, 'STARTTLS', [220]);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('No fue posible iniciar TLS con SMTP.');
            }
            cdaMarketingSmtpCommand($socket, 'EHLO ' . $host, [250]);
        }

        cdaMarketingSmtpCommand($socket, 'AUTH LOGIN', [334]);
        cdaMarketingSmtpCommand($socket, base64_encode(CDA_SMTP_USER), [334]);
        cdaMarketingSmtpCommand($socket, base64_encode(CDA_SMTP_PASS), [235]);
        cdaMarketingSmtpCommand($socket, 'MAIL FROM:<' . CDA_MAIL_FROM . '>', [250]);
        // Las copias (Cc) tambien necesitan su propio RCPT en SMTP.
        preg_match_all('/^Cc:\s*(.+)$/mi', $headers, $ccMatches);
        $recipients = array_merge([$to], array_map('trim', explode(',', implode(',', $ccMatches[1]))));
        foreach (array_filter($recipients, function ($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        }) as $recipient) {
            cdaMarketingSmtpCommand($socket, 'RCPT TO:<' . $recipient . '>', [250, 251]);
        }
        cdaMarketingSmtpCommand($socket, 'DATA', [354]);

        $data = 'Date: ' . date('r') . "\r\n";
        $data .= 'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . substr(strrchr(CDA_MAIL_FROM, '@'), 1) . ">\r\n";
        $data .= 'To: <' . $to . ">\r\n";
        $data .= 'Subject: =?UTF-8?B?' . base64_encode($subject) . "?=\r\n";
        $data .= rtrim($headers, "\r\n") . "\r\n\r\n";
        $body = preg_replace("/\r\n|\r|\n/", "\r\n", $message);
        // Una linea que empieza con punto se duplica para no cerrar el DATA.
        $data .= preg_replace('/^\./m', '..', $body);

        cdaMarketingSmtpCommand($socket, $data . "\r\n.", [250]);
        cdaMarketingSmtpCommand($socket, 'QUIT', [221]);
    } finally {
        fclose($socket);
    }

    return true;
}

function cdaMarketingMailLog($line) {
    error_log($line);
    // Copia en un archivo fuera de public_html (no accesible desde la web) para
    // revisarlo desde el Administrador de archivos de Hostinger.
    @file_put_contents(dirname(__DIR__, 2) . '/correo-marketing.log', date('Y-m-d H:i:s') . ' ' . $line . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function cdaMarketingMail($to, $subject, $message, $headers) {
    $sent = false;
    if (CDA_SMTP_PASS !== '') {
        try {
            $sent = cdaMarketingSmtpSend($to, $subject, $message, $headers);
        } catch (Throwable $e) {
            cdaMarketingMailLog('Error SMTP, se intenta con mail(): ' . $e->getMessage());
        }
    }

    if (!$sent) {
        $sent = mail($to, $subject, $message, $headers);
    }
    // Se registra cada intento para poder rastrear correos que no llegan.
    cdaMarketingMailLog(($sent ? 'Correo aceptado' : 'No fue posible enviar correo') . ' a ' . $to . ': ' . $subject);

    return $sent;
}

// Bloque HTML con el origen del envio (formulario, pagina, fecha, IP) para los
// correos de los formularios del sitio. La pagina sale del Referer que manda
// el fetch del formulario.
function cdaOrigenFormularioHTML($formulario) {
    $url = $_SERVER['HTTP_REFERER'] ?? '';
    $ruta = (string) parse_url($url, PHP_URL_PATH);
    $paginas = [
        '' => 'Inicio',
        '/' => 'Inicio',
        '/index.html' => 'Inicio',
        '/empresa.html' => 'Empresas',
        '/vacantes/monitorista.html' => 'Vacante: Monitorista',
        '/vacantes/tecnico-instalador.html' => 'Vacante: Técnico Instalador',
        '/vacantes/ejecutivo-cobranza.html' => 'Vacante: Ejecutivo de Cobranza'
    ];
    $pagina = $paginas[$ruta] ?? ($ruta !== '' ? basename($ruta) : 'No identificada');

    $filas = [
        'Formulario' => $formulario,
        'Página' => $pagina,
        'URL' => $url !== '' ? $url : 'No disponible',
        'Fecha' => date('d/m/Y H:i:s'),
        'IP' => $_SERVER['REMOTE_ADDR'] ?? 'No disponible',
        'Navegador' => $_SERVER['HTTP_USER_AGENT'] ?? 'No disponible'
    ];

    $html = "<table style='width: 100%; border-collapse: collapse; font-size: 13px;'>";
    foreach ($filas as $etiqueta => $valor) {
        $html .= "<tr><td style='padding: 6px 8px; border-bottom: 1px solid #e5e7eb; color: #063970; font-weight: bold; width: 110px; vertical-align: top;'>{$etiqueta}</td>"
            . "<td style='padding: 6px 8px; border-bottom: 1px solid #e5e7eb; word-break: break-all;'>" . htmlspecialchars($valor, ENT_QUOTES, 'UTF-8') . "</td></tr>";
    }
    return $html . '</table>';
}

