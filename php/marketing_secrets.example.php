<?php
// Copia este archivo como php/marketing_secrets.php en Hostinger.
// marketing_secrets.php esta ignorado por Git para no subir credenciales.

define('CDA_DB_HOST', 'localhost');
define('CDA_DB_NAME', 'u111788276_marketing2026');
define('CDA_DB_USER', 'u111788276_admin2026');
define('CDA_DB_PASS', 'CAMBIA_ESTA_PASSWORD');

define('CDA_SITE_URL', 'https://centraldealarmas.com.mx');

// Google Cloud Console -> APIs y servicios -> Credenciales -> OAuth client ID.
define('CDA_GOOGLE_CLIENT_ID', 'TU_CLIENT_ID.apps.googleusercontent.com');
define('CDA_GOOGLE_CLIENT_SECRET', 'TU_CLIENT_SECRET');
define('CDA_GOOGLE_REDIRECT_URI', CDA_SITE_URL . '/google-callback.php');

// Alternativa: tambien puedes definir estos mismos nombres como variables de entorno
// o guardarlos en la tabla marketing_configuracion del hosting.
