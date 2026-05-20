(function () {
    const languages = [
        { code: 'es', label: 'ES', name: 'Español', google: 'es' },
        { code: 'en', label: 'EN', name: 'English', google: 'en' },
        { code: 'fr', label: 'FR', name: 'Français', google: 'fr' },
        { code: 'ja', label: 'JA', name: '日本語', google: 'ja' },
        { code: 'ko', label: 'KO', name: '한국어', google: 'ko' },
        { code: 'zh', label: 'ZH', name: '中文', google: 'zh-CN' }
    ];

    const storageKey = 'siteLanguage';
    const defaultLanguage = 'es';
    const translateCookieName = 'googtrans';
    let googleTranslateLoading = false;
    let pendingLanguage = null;

    function getLanguage(code) {
        return languages.find((language) => language.code === code) || languages[0];
    }

    function getStoredLanguage() {
        const storedLanguage = localStorage.getItem(storageKey);
        const language = getLanguage(storedLanguage || defaultLanguage);
        if (storedLanguage !== language.code) {
            localStorage.setItem(storageKey, language.code);
        }
        return language;
    }

    function getCookieDomain() {
        const host = window.location.hostname;
        if (!host || host === 'localhost' || /^[\d.]+$/.test(host)) return '';
        return `; domain=.${host.replace(/^www\./, '')}`;
    }

    function setCookie(name, value, maxAge) {
        const domain = getCookieDomain();
        document.cookie = `${name}=${value}; path=/; max-age=${maxAge}; SameSite=Lax${domain}`;
        document.cookie = `${name}=${value}; path=/; max-age=${maxAge}; SameSite=Lax`;
    }

    function clearTranslateCookie() {
        const domain = getCookieDomain();
        const paths = ['/', window.location.pathname.replace(/[^/]*$/, '') || '/'];
        paths.forEach((path) => {
            document.cookie = `${translateCookieName}=; path=${path}; expires=Thu, 01 Jan 1970 00:00:00 GMT; SameSite=Lax${domain}`;
            document.cookie = `${translateCookieName}=; path=${path}; expires=Thu, 01 Jan 1970 00:00:00 GMT; SameSite=Lax`;
        });
    }

    function persistTranslationCookie(language) {
        if (language.code === defaultLanguage) {
            clearTranslateCookie();
            return;
        }
        setCookie(translateCookieName, `/es/${language.google}`, 31536000);
    }

    function prepareInitialTranslation() {
        const language = getStoredLanguage();
        persistTranslationCookie(language);
        document.documentElement.lang = language.code;
        return language;
    }

    function optionMarkup(useNames) {
        return languages
            .map((language) => `<option value="${language.code}">${useNames ? language.name : language.label}</option>`)
            .join('');
    }

    function syncSelectOptions(select, useNames) {
        if (!select) return;
        const currentValue = select.value || getStoredLanguage().code;
        select.innerHTML = optionMarkup(useNames);
        select.value = getLanguage(currentValue).code;
    }

    function ensureDesktopSwitcher() {
        const nav = document.getElementById('main-nav');
        if (!nav) return null;

        let switcher = nav.querySelector('.language-switcher');
        if (!switcher) {
            switcher = document.createElement('div');
            switcher.className = 'language-switcher notranslate';
            switcher.setAttribute('aria-label', 'Cambiar idioma');
            switcher.innerHTML = `
                <span class="language-switcher-value" id="languageSwitcherValue">ES</span>
                <select id="languageSelect" aria-label="Cambiar idioma"></select>
            `;
            const payButton = nav.querySelector('.nav-cta');
            if (payButton) {
                payButton.insertAdjacentElement('afterend', switcher);
            } else {
                nav.appendChild(switcher);
            }
        } else {
            switcher.classList.add('notranslate');
        }

        syncSelectOptions(switcher.querySelector('select'), false);
        return switcher;
    }

    function ensureMobileSwitcher() {
        const mobileMenu = document.getElementById('mobile-menu');
        if (!mobileMenu) return null;

        let switcher = mobileMenu.querySelector('.mobile-language-switcher');
        if (!switcher) {
            switcher = document.createElement('label');
            switcher.className = 'mobile-language-switcher notranslate';
            switcher.innerHTML = `
                <span data-i18n="language.label">Idioma</span>
                <select id="mobileLanguageSelect" aria-label="Cambiar idioma"></select>
            `;
            mobileMenu.appendChild(switcher);
        } else {
            switcher.classList.add('notranslate');
        }

        syncSelectOptions(switcher.querySelector('select'), true);
        return switcher;
    }

    function ensureGoogleTranslateElement() {
        if (document.getElementById('google_translate_element')) return;
        const element = document.createElement('div');
        element.id = 'google_translate_element';
        element.setAttribute('aria-hidden', 'true');
        document.body.appendChild(element);
    }

    function addGooglePreconnects() {
        ['https://translate.google.com', 'https://translate.googleapis.com'].forEach((href) => {
            if (document.querySelector(`link[rel="preconnect"][href="${href}"]`)) return;
            const link = document.createElement('link');
            link.rel = 'preconnect';
            link.href = href;
            link.crossOrigin = 'anonymous';
            document.head.appendChild(link);
        });
    }

    function loadGoogleTranslate() {
        if (document.querySelector('script[data-google-translate]') || googleTranslateLoading) return;
        googleTranslateLoading = true;
        addGooglePreconnects();
        window.googleTranslateElementInit = function () {
            if (!window.google || !window.google.translate) return;
            new window.google.translate.TranslateElement({
                pageLanguage: 'es',
                includedLanguages: languages.map((language) => language.google).join(','),
                autoDisplay: false
            }, 'google_translate_element');
            window.dispatchEvent(new Event('siteTranslateReady'));
            if (pendingLanguage) {
                applyGoogleTranslation(pendingLanguage);
                pendingLanguage = null;
            }
        };

        const script = document.createElement('script');
        script.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
        script.defer = true;
        script.dataset.googleTranslate = 'true';
        document.head.appendChild(script);
    }

    function updateVisibleLanguage(code) {
        const language = getLanguage(code);
        document.documentElement.lang = language.code;

        document.querySelectorAll('#languageSelect, #mobileLanguageSelect').forEach((select) => {
            select.value = language.code;
        });

        document.querySelectorAll('.language-switcher-value').forEach((value) => {
            value.textContent = language.label;
        });

        document.querySelectorAll('.language-switcher').forEach((switcher) => {
            switcher.setAttribute('aria-label', `Cambiar idioma. Idioma actual: ${language.name}`);
        });
    }

    function applyGoogleTranslation(code, attempt = 0) {
        const language = getLanguage(code);
        if (language.code === defaultLanguage) {
            const combo = document.querySelector('.goog-te-combo');
            if (combo) {
                combo.value = '';
                combo.dispatchEvent(new Event('change'));
            }
            return;
        }

        const combo = document.querySelector('.goog-te-combo');
        if (!combo) {
            pendingLanguage = language.code;
            loadGoogleTranslate();
            if (attempt < 20) {
                setTimeout(() => applyGoogleTranslation(code, attempt + 1), 300);
            }
            return;
        }

        combo.value = language.code === defaultLanguage ? '' : language.google;
        combo.dispatchEvent(new Event('change'));
    }

    function setLanguage(code, options = {}) {
        const language = getLanguage(code);
        localStorage.setItem(storageKey, language.code);
        persistTranslationCookie(language);
        updateVisibleLanguage(language.code);

        if (options.reload) {
            window.location.reload();
            return;
        }

        if (typeof window.applyTranslations === 'function') {
            window.applyTranslations(language.code);
            updateVisibleLanguage(language.code);
        }

        if (language.code === defaultLanguage) {
            applyGoogleTranslation(language.code);
            return;
        }

        loadGoogleTranslate();
        applyGoogleTranslation(language.code);
    }

    function bindLanguageControls() {
        document.querySelectorAll('#languageSelect, #mobileLanguageSelect').forEach((select) => {
            if (select.dataset.languageBound === 'true') return;
            select.dataset.languageBound = 'true';
            select.addEventListener('change', (event) => setLanguage(event.target.value, { reload: true }));
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const initialLanguage = prepareInitialTranslation();
        ensureDesktopSwitcher();
        ensureMobileSwitcher();
        ensureGoogleTranslateElement();
        bindLanguageControls();
        updateVisibleLanguage(initialLanguage.code);
        if (initialLanguage.code !== defaultLanguage) {
            loadGoogleTranslate();
            applyGoogleTranslation(initialLanguage.code);
        }
    });

    window.addEventListener('siteTranslateReady', function () {
        const language = getStoredLanguage();
        updateVisibleLanguage(language.code);
        if (language.code !== defaultLanguage) {
            applyGoogleTranslation(language.code);
        }
    });

    window.setSiteLanguage = setLanguage;
    window.siteLanguages = languages.slice();
    prepareInitialTranslation();
}());
