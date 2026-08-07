# Configurar Google Login en Hostinger

El login con Google crea automaticamente usuarios activos de rol `marketing` cuando Google confirma un correo verificado que todavia no existe en `marketing_usuarios`.

## 1. Crear credenciales en Google Cloud

1. Entra a Google Cloud Console.
2. Crea o selecciona un proyecto.
3. Ve a **APIs y servicios > Pantalla de consentimiento OAuth**.
4. Configura la app como externa o interna segun tu cuenta.
5. Ve a **Credenciales > Crear credenciales > ID de cliente de OAuth**.
6. Tipo de aplicacion: **Aplicacion web**.

## 2. Agregar URI autorizado

En **URI de redireccion autorizados** agrega exactamente:

```text
https://centraldealarmas.com.mx/google-callback.php
```

Si el sitio final usa otro dominio o subdominio, debe coincidir exactamente con `CDA_SITE_URL`.

## 3. Configurar Hostinger

En el servidor, crea o edita:

```text
php/marketing_secrets.php
```

Puedes copiar `php/marketing_secrets.example.php` y llenar:

```php
define('CDA_GOOGLE_CLIENT_ID', 'TU_CLIENT_ID.apps.googleusercontent.com');
define('CDA_GOOGLE_CLIENT_SECRET', 'TU_CLIENT_SECRET');
define('CDA_GOOGLE_REDIRECT_URI', CDA_SITE_URL . '/google-callback.php');
```

Tambien puedes definir `CDA_GOOGLE_CLIENT_ID`, `CDA_GOOGLE_CLIENT_SECRET` y `CDA_GOOGLE_REDIRECT_URI` como variables de entorno del hosting.

Si prefieres guardar Google OAuth en la base de datos de Hostinger, importa `db/google_oauth_config.sql` y reemplaza los valores `TU_CLIENT_ID...` y `TU_CLIENT_SECRET`.

El sistema busca las credenciales en este orden:

1. `php/marketing_secrets.php`
2. Variables de entorno del hosting
3. Tabla `marketing_configuracion`

Si el Client ID o Secret estan vacios, el boton de Google no aparece y `google-login.php` regresa al login con error de configuracion.

## 4. Administrar usuarios

En el panel interno, entra a **Usuarios** para ver quien ya se registro, cambiar roles o desactivar accesos.

El primer login con Google vincula ese correo con el identificador seguro de Google (`google_sub`). Despues, si Google devuelve otro identificador para el mismo correo, el acceso se bloquea. Si desactivas un usuario, Google ya no lo deja entrar aunque su correo exista.

## 5. Activar chat de tickets

Para habilitar la conversacion entre administradores y usuarios dentro de cada ticket, importa en phpMyAdmin:

```text
db/marketing_ticket_mensajes.sql
```

El flujo publico inicia en `marketing.html`, donde se explica la gestion de tickets y se envia al usuario a iniciar sesion. Una vez autenticado, `panel-marketing.php` muestra las opciones principales, el perfil y los tickets generados; desde `crear-ticket.php` el formulario toma nombre y correo de la sesion activa, sin pedir contrasena ni datos del solicitante.

Cada ticket nuevo envia correo de confirmacion al solicitante y aviso a los administradores activos.
