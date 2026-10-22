// reCAPTCHA v3 (invisible) para los formularios con data-recaptcha-v3.
// Genera el token justo antes de enviar y lo manda en g-recaptcha-response,
// que valida php/recaptcha.php. Corre antes que el submit de form-handler.
(function () {
    var SITE_KEY = '6LdDqPotAAAAAH88MgHWxtWWAW9TfhQwP9i7vcXU';
    var forms = document.querySelectorAll('form[data-recaptcha-v3]');
    if (!forms.length) return;

    var cargado = false;
    function cargarRecaptcha() {
        if (cargado) return;
        cargado = true;
        var script = document.createElement('script');
        script.src = 'https://www.google.com/recaptcha/api.js?render=' + SITE_KEY;
        script.async = true;
        document.head.appendChild(script);
    }

    // Espera a grecaptcha hasta ~10 s; si no carga, sigue sin token y el
    // servidor responde "verifica que no eres un robot".
    function cuandoEsteListo(callback, intentos) {
        intentos = intentos || 0;
        if (window.grecaptcha && window.grecaptcha.ready) {
            window.grecaptcha.ready(function () {
                // form-handler llama grecaptcha.reset(), que en v3 no tiene widget.
                var reset = window.grecaptcha.reset;
                window.grecaptcha.reset = function () {
                    try { return reset && reset.apply(this, arguments); } catch (e) {}
                };
                callback(true);
            });
            return;
        }
        if (intentos > 100) return callback(false);
        setTimeout(function () { cuandoEsteListo(callback, intentos + 1); }, 100);
    }

    forms.forEach(function (form) {
        var campo = document.createElement('input');
        campo.type = 'hidden';
        campo.name = 'g-recaptcha-response';
        form.appendChild(campo);

        ['focusin', 'pointerenter', 'touchstart'].forEach(function (evento) {
            form.addEventListener(evento, cargarRecaptcha, { once: true, passive: true });
        });
    });

    var conToken = null;
    var esperando = false;

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.matches || !form.matches('form[data-recaptcha-v3]')) return;

        if (conToken === form) {
            conToken = null;
            return;
        }

        e.preventDefault();
        e.stopImmediatePropagation();
        if (esperando) return;
        esperando = true;
        cargarRecaptcha();

        cuandoEsteListo(function (listo) {
            var enviar = function (token) {
                form.querySelector('input[name="g-recaptcha-response"]').value = token || '';
                esperando = false;
                conToken = form;
                if (form.requestSubmit) {
                    form.requestSubmit();
                } else {
                    form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                }
            };
            if (!listo) return enviar('');
            window.grecaptcha.execute(SITE_KEY, { action: 'formulario' }).then(enviar, function () { enviar(''); });
        });
    }, true);
})();
