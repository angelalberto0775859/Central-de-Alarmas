// ─── HEADER SCROLL ───
const header = document.getElementById('main-header');
const backToTop = document.getElementById('back-to-top');

window.addEventListener('scroll', () => {
    const st = window.pageYOffset || document.documentElement.scrollTop;
    if (header) header.classList.toggle('scrolled', st > 0);
    if (backToTop) backToTop.classList.toggle('visible', st > 100);
});

// ─── MOBILE MENU ───
const hamburger = document.getElementById('hamburger');
const mobileMenu = document.getElementById('mobile-menu');
const mobileOverlay = document.getElementById('mobile-overlay');

if (hamburger && mobileMenu && mobileOverlay) {
    const closeMobileMenu = () => {
        mobileMenu.classList.remove('open');
        mobileOverlay.classList.remove('open');
        hamburger.classList.remove('open');
    };

    hamburger.addEventListener('click', function() {
        mobileMenu.classList.toggle('open');
        mobileOverlay.classList.toggle('open');
        hamburger.classList.toggle('open');
    });

    mobileMenu.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMobileMenu));
    mobileOverlay.addEventListener('click', closeMobileMenu);
}

// ─── AOS INITIALIZATION ───
let aosInitialized = false;

function initAOS() {
    if (!window.AOS || aosInitialized) return;

    document.body.classList.add('aos-enhanced');
    AOS.init({
        duration: 800,
        once: false,
        offset: 100,
        easing: 'ease-in-out'
    });
    aosInitialized = true;
}

function refreshAOS() {
    if (window.AOS) AOS.refresh();
}

initAOS();

function loadDeferredHeroVideo() {
    const video = document.getElementById('background-video');
    if (!video || video.dataset.loaded === 'true') return;

    const source = video.querySelector('source[data-src]');
    if (!source) return;

    source.src = source.dataset.src;
    source.removeAttribute('data-src');
    video.dataset.loaded = 'true';
    video.load();
    video.play().catch(() => {});
}

function loadRecaptcha() {
    if (!document.querySelector('.g-recaptcha') || document.querySelector('script[data-recaptcha-loader]')) return;

    const script = document.createElement('script');
    script.src = 'https://www.google.com/recaptcha/api.js';
    script.async = true;
    script.defer = true;
    script.dataset.recaptchaLoader = 'true';
    document.head.appendChild(script);
}

function scheduleNonCriticalResources() {
    const loadVideoSoon = () => {
        if ('requestIdleCallback' in window) {
            requestIdleCallback(loadDeferredHeroVideo, { timeout: 1800 });
            return;
        }
        setTimeout(loadDeferredHeroVideo, 900);
    };

    if (document.readyState === 'complete') {
        loadVideoSoon();
    } else {
        window.addEventListener('load', loadVideoSoon, { once: true });
    }

    ['pointerdown', 'keydown', 'touchstart', 'scroll'].forEach((eventName) => {
        window.addEventListener(eventName, loadDeferredHeroVideo, { once: true, passive: true });
    });

    const contactForm = document.getElementById('contact-form-empresa');
    if (!contactForm) return;

    ['focusin', 'pointerenter', 'touchstart'].forEach((eventName) => {
        contactForm.addEventListener(eventName, loadRecaptcha, { once: true, passive: true });
    });

    if ('IntersectionObserver' in window) {
        const recaptchaObserver = new IntersectionObserver((entries) => {
            if (!entries.some((entry) => entry.isIntersecting)) return;
            loadRecaptcha();
            recaptchaObserver.disconnect();
        }, { rootMargin: '500px 0px' });
        recaptchaObserver.observe(contactForm);
    }
}

// Variable para controlar si ya se reiniciaron las animaciones
let animationsReset = false;
let lastScrollTop = 0;

// Refrescar AOS cuando se carga la página para asegurar que las animaciones se repitan
document.addEventListener('DOMContentLoaded', function() {
    initAOS();
    refreshAOS();
    
    // También refrescar después de un pequeño retraso
    setTimeout(function() {
        refreshAOS();
    }, 100);

    scheduleNonCriticalResources();
});

// Refrescar AOS cuando la página se vuelve visible (por si viene de navegación)
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        setTimeout(function() {
            refreshAOS();
        }, 100);
    }
});

// Reiniciar animaciones cuando se regresa al header
window.addEventListener('scroll', function() {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    
    // Animación dinámica de la textura
    const textureX = scrollTop * 0.1;
    const textureY = scrollTop * 0.05;
    document.body.style.backgroundPosition = `${textureX}px ${textureY}px`;
    
    // Si estamos cerca del inicio (header visible) y las animaciones no se han reiniciado
    if (scrollTop < 100 && !animationsReset) {
        setTimeout(function() {
            refreshAOS();
            animationsReset = true;
        }, 200);
    }
    
    // Si hacemos scroll hacia abajo, resetear la bandera
    if (scrollTop > 200) {
        animationsReset = false;
    }
    
    lastScrollTop = scrollTop;
});

// ─── SCROLL ANIMATIONS FOR SERVICES ───
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
        }
    });
}, observerOptions);

// Observar todos los servicios
document.querySelectorAll('.service-item-empresa, .enterprise-service-card, .story-step, .operation-card').forEach(service => {
    observer.observe(service);
});
