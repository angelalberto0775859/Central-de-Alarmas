// ─── SERVICE MODAL ───
function openServiceModal(title, description, imageSrc) {
    const modal = document.getElementById('serviceModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    const modalImage = document.getElementById('modalImage');
    if (!modal || !modalTitle || !modalBody || !modalImage) return;
    
    modalTitle.textContent = title;
    modalBody.innerHTML = description;

    if (imageSrc) {
        modalImage.src = imageSrc;
        modalImage.alt = title;
        modalImage.style.display = 'block';
    } else {
        modalImage.src = '';
        modalImage.alt = '';
        modalImage.style.display = 'none';
    }

    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeServiceModal() {
    const modal = document.getElementById('serviceModal');
    if (!modal) return;
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Cerrar modal al hacer clic fuera del contenido
const serviceModal = document.getElementById('serviceModal');
if (serviceModal) {
    serviceModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeServiceModal();
        }
    });
}

// Cerrar modal con tecla Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeServiceModal();
    }
});

// ─── AOS INITIALIZATION ───
// Inicializar AOS con configuración para repetir animaciones
if (window.AOS) {
    AOS.init({
        duration: 800,
        once: false, // Importante: false para que las animaciones se repitan
        offset: 100,
        easing: 'ease-in-out'
    });
}

const header = document.getElementById('main-header');
const backToTop = document.getElementById('back-to-top');
const hasTransparentHero = Boolean(document.querySelector('#inicio video, .blog-hero video, .news-hero-video'));

function handleScroll() {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    document.body.style.backgroundPosition = `${scrollTop * 0.1}px ${scrollTop * 0.05}px`;
    if (header) {
        header.classList.toggle('scrolled', !hasTransparentHero || scrollTop > 24);
    }
    if (backToTop) backToTop.classList.toggle('visible', scrollTop > 100);
}

// Refrescar AOS cuando se carga la página y aplicar carga diferida de imágenes
document.addEventListener('DOMContentLoaded', function() {
    if (window.AOS) {
        AOS.refresh();
        setTimeout(() => AOS.refresh(), 100);
    }

    document.querySelectorAll('img').forEach(img => {
        if (!img.hasAttribute('loading')) {
            img.setAttribute('loading', 'lazy');
        }
        img.decoding = 'async';
    });

    handleScroll();
});

window.addEventListener('scroll', () => {
    requestAnimationFrame(handleScroll);
}, { passive: true });

// ─── MOBILE MENU ───
const hamburger = document.getElementById('hamburger');
const mainNav = document.getElementById('main-nav');

if (hamburger && mainNav) {
    hamburger.addEventListener('click', function() {
        mainNav.classList.toggle('active');
        mainNav.classList.toggle('open');
        hamburger.classList.toggle('active');
        hamburger.classList.toggle('open');
    });
    
    // Close menu when clicking on a link
    const navLinks = mainNav.querySelectorAll('a');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            mainNav.classList.remove('active');
            mainNav.classList.remove('open');
            hamburger.classList.remove('active');
            hamburger.classList.remove('open');
        });
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        if (!hamburger.contains(event.target) && !mainNav.contains(event.target)) {
            mainNav.classList.remove('active');
            mainNav.classList.remove('open');
            hamburger.classList.remove('active');
            hamburger.classList.remove('open');
        }
    });
}

// ─── TEXTO DINÁMICO ───
const words = ['hogar', 'negocio', 'empresa'];
let currentWordIndex = 0;
const word2Element = document.getElementById('word2');

function changeWord() {
    if (!word2Element) return;
    // Efecto de desvanecimiento
    word2Element.style.opacity = '0';
    word2Element.style.transform = 'translateY(-10px)';
    
    setTimeout(() => {
        // Cambiar palabra
        currentWordIndex = (currentWordIndex + 1) % words.length;
        word2Element.textContent = words[currentWordIndex];
        
        // Efecto de aparición
        word2Element.style.opacity = '1';
        word2Element.style.transform = 'translateY(0)';
    }, 300);
}

if (word2Element) {
    // Configurar transiciones CSS y color amarillo
    word2Element.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    word2Element.style.color = '#f6eb17'; // Color amarillo

    // Iniciar el ciclo
    setInterval(changeWord, 3000);
}
