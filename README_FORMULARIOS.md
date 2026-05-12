# Configuración de Formularios de Contacto

## Archivos Creados

### PHP
- `php/contacto.php` - Procesa el formulario de contacto principal (index.html)
- `php/empresa.php` - Procesa el formulario de contacto empresarial (empresa.html)

### JavaScript
- `js/form-handler.js` - Maneja el envío asíncrono de ambos formularios
- `js/main.js` - Funcionalidades principales para index.html
- `js/main-empresa.js` - Funcionalidades específicas para empresa.html

## Configuración del Servidor

### Requisitos
1. **PHP 7.4 o superior** instalado en el servidor
2. **Función mail() de PHP** configurada y funcionando
3. **Permisos de escritura** en la carpeta del proyecto

### Configuración de Correo
Los correos se enviarán a: `salducin@centraldealarmas.com.mx`

Para cambiar el destinatario, edita la variable `$destinatario` en ambos archivos PHP:

```php
$destinatario = 'nuevo-correo@ejemplo.com';
```

### Configuración de reCAPTCHA
Los formularios utilizan Google reCAPTCHA v2. La clave secreta está configurada en ambos archivos PHP:

```php
$recaptcha_secret = '6LfjgrQpAAAAABoTmAW2j8d-zxYCKUWbPpb8Fb9G';
```

## Estructura de Archivos

```
CDA Sitio 2026/
├── index.html                 # Página principal
├── empresa.html              # Página empresarial
├── php/
│   ├── contacto.php          # Procesador de formulario principal
│   └── empresa.php          # Procesador de formulario empresarial
├── js/
│   ├── form-handler.js      # Manejador de formularios
│   ├── main.js             # Funcionalidades página principal
│   └── main-empresa.js     # Funcionalidades página empresarial
├── css/
│   └── styles.css          # Estilos (necesita ser creado)
└── img/                   # Imágenes del sitio
```

## Funcionalidades Implementadas

### ✅ Validación del lado del cliente
- Campos obligatorios
- Formato de correo electrónico
- Mensajes de error en tiempo real

### ✅ Validación del lado del servidor
- Limpieza de datos (XSS protection)
- Validación completa de campos
- Verificación de reCAPTCHA

### ✅ Envío de correos
- Formato HTML profesional
- Todos los datos del formulario
- Timestamp de envío
- Manejo de errores

### ✅ Experiencia de usuario
- Notificaciones emergentes (success/error)
- Estados de loading en botones
- Reset automático de formularios
- Reset de reCAPTCHA

## Características de Seguridad

1. **Protección XSS**: Todos los datos son sanitizados
2. **Validación de entrada**: Verificación estricta de campos
3. **reCAPTCHA**: Protección contra bots
4. **CSRF Protection**: Headers de seguridad configurados
5. **Rate Limiting**: Implementado a nivel de servidor (recomendado)

## Personalización

### Cambiar estilos de notificaciones
Edita el método `showNotification()` en `js/form-handler.js`:

```javascript
if (type === 'success') {
    notification.style.background = '#10b981'; // Color éxito
} else {
    notification.style.background = '#ef4444'; // Color error
}
```

### Modificar plantilla de correo
Edita la variable `$mensajeHTML` en los archivos PHP para personalizar el diseño del correo.

## Solución de Problemas

### Correos no llegan
1. Verifica que la función mail() de PHP esté configurada
2. Revisa la carpeta de spam
3. Configura un servidor SMTP si es necesario

### Error 403
1. Verifica permisos de archivos PHP (755 recomendado)
2. Asegúrate que el servidor ejecute PHP

### reCAPTCHA no funciona
1. Verifica la clave del sitio en el HTML
2. Confirma la clave secreta en los archivos PHP
3. Asegúrate que el dominio esté registrado en Google reCAPTCHA

## Contacto de Soporte

Para cualquier problema o modificación, contacta al desarrollador o revisa la documentación oficial de:
- PHP: https://www.php.net/docs.php
- reCAPTCHA: https://developers.google.com/recaptcha
- Fetch API: https://developer.mozilla.org/es/docs/Web/API/Fetch_API
