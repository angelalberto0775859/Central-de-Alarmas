// ─── HEADER SCROLL ───
const header = document.getElementById('main-header');
const backToTop = document.getElementById('back-to-top');

window.addEventListener('scroll', () => {
    const st = window.pageYOffset || document.documentElement.scrollTop;
    console.log('Scroll position:', st); // Debug
    header.classList.toggle('scrolled', st > 0);
    console.log('Header classes:', header.className); // Debug
    backToTop.classList.toggle('visible', st > 100);
});

// ─── MOBILE MENU ───
const hamburger = document.getElementById('hamburger');
const mainNav = document.getElementById('main-nav');

if (hamburger && mainNav) {
    hamburger.addEventListener('click', function() {
        mainNav.classList.toggle('active');
        hamburger.classList.toggle('active');
    });
    
    // Close menu when clicking on a link
    const navLinks = mainNav.querySelectorAll('a');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            mainNav.classList.remove('active');
            hamburger.classList.remove('active');
        });
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        if (!hamburger.contains(event.target) && !mainNav.contains(event.target)) {
            mainNav.classList.remove('active');
            hamburger.classList.remove('active');
        }
    });
}

// ─── AOS INITIALIZATION ───
// Inicializar AOS con configuración para repetir animaciones
AOS.init({
    duration: 800,
    once: false, // Importante: false para que las animaciones se repitan
    offset: 100,
    easing: 'ease-in-out'
});

// Variable para controlar si ya se reiniciaron las animaciones
let animationsReset = false;
let lastScrollTop = 0;

// Refrescar AOS cuando se carga la página para asegurar que las animaciones se repitan
document.addEventListener('DOMContentLoaded', function() {
    // Forzar reinicio de AOS
    AOS.refresh();
    
    // También refrescar después de un pequeño retraso
    setTimeout(function() {
        AOS.refresh();
    }, 100);
});

// Refrescar AOS cuando la página se vuelve visible (por si viene de navegación)
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        setTimeout(function() {
            AOS.refresh();
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
            AOS.refresh();
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
document.querySelectorAll('.service-item-empresa').forEach(service => {
    observer.observe(service);
});
