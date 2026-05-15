(function() {
    const storageKey = 'cda_cookie_consent';
    const existing = localStorage.getItem(storageKey);

    function saveConsent(value) {
        localStorage.setItem(storageKey, JSON.stringify({
            value,
            savedAt: new Date().toISOString()
        }));
        const banner = document.querySelector('.cookie-banner');
        if (banner) banner.remove();
    }

    function renderBanner() {
        if (existing) return;

        const banner = document.createElement('section');
        banner.className = 'cookie-banner';
        banner.setAttribute('aria-label', 'Aviso de cookies');
        banner.innerHTML = `
            <div class="cookie-copy">
                <span class="cookie-label">Privacidad</span>
                <h2>Usamos cookies para mejorar tu experiencia</h2>
                <p>Utilizamos cookies necesarias para que el sitio funcione y, con tu permiso, cookies de medicion para entender que contenido resulta mas util.</p>
            </div>
            <div class="cookie-actions">
                <button type="button" class="cookie-btn secondary" data-cookie-choice="necessary">Solo necesarias</button>
                <button type="button" class="cookie-btn primary" data-cookie-choice="accepted">Aceptar</button>
            </div>
        `;

        document.body.appendChild(banner);

        banner.querySelectorAll('[data-cookie-choice]').forEach(button => {
            button.addEventListener('click', () => saveConsent(button.dataset.cookieChoice));
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', renderBanner);
    } else {
        renderBanner();
    }
})();
