// ─── SERVICE MODAL ───
const packageModalData = {
    essential: {
        title: 'Essential/Monitoreo 24/7',
        intro: 'Protección ideal para espacios pequeños con componentes básicos de seguridad.',
        components: [
            {
                name: 'Hub 2 (4G) Jeweller',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquete Essential/Hub 2 Jeweller.avif',
                description: 'Panel de control inalámbrico con soporte para la fotoverificación. Admite Ethernet y dos tarjetas SIM (2G/3G/LTE).'
            },
            {
                name: 'MotionProtect Jeweller',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquete Essential/Motion protect jeweller.avif',
                description: 'Detector IR e inalámbrico de movimiento.'
            },
            {
                name: 'DoorProtect Jeweller',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquete Essential/Door Protect jeweller.avif',
                description: 'Detector inalámbrico de apertura con relé reed.'
            },
            {
                name: 'HomeSiren Jeweller',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquete Essential/Home Siren jeweller.avif',
                description: 'Sirena inalámbrica.'
            },
            {
                name: 'SpaceControl Jeweller',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquete Essential/Ajax space control jeweller.avif',
                description: 'Mando inalámbrico con botón de pánico y control de los modos de seguridad.'
            },
            {
                name: 'TurretCam (5 Mp/2.8 mm)',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquete Essential/Render - Ajax TurretCam - Black - front3 4.png',
                description: 'Cámara IP de seguridad cableada con tecnología IA, ángulo de visión de 110°, iluminación IR, True WDR, micrófono y PoE/12 V. Para exteriores e interiores.'
            }
        ]
    },
    professional: {
        title: 'Profesional/Monitoreo 24/7',
        intro: 'Paquete ideal para hogares y PyMES con vigilancia y acceso controlado.',
        components: [
            {
                name: 'Hub 2 Plus Jeweller',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquete Profesional/Hub 2 Plus Jeweller.avif',
                description: 'Panel de control inalámbrico con soporte para fotoverificación. Admite Wi-Fi, Ethernet y dos tarjetas SIM (2G/3G/LTE).'
            },
            {
                name: 'MotionCam (PhOD) Jeweller',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquete Profesional/MotionCam (PhOD) Jeweller.avif',
                description: 'Detector PIR e inalámbrico de movimiento con posibilidades ampliadas de verificación fotográfica.'
            },
            {
                name: 'DoorProtect Jeweller',
                badge: 'Cantidad: 2',
                image: 'img/Paquetes AJAX/Paquete Profesional/DoorProtect Jeweller.avif',
                description: 'Detector inalámbrico de apertura con relé reed.'
            },
            {
                name: 'KeyPad Jeweller',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquete Profesional/KeyPad Jeweller.avif',
                description: 'Teclado inalámbrico y táctil.'
            },
            {
                name: 'StreetSiren Jeweller',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquete Profesional/Streetsiren jeweller.avif',
                description: 'Sirena inalámbrico para interiores y exteriores.'
            },
            {
                name: 'SpaceControl Jeweller',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquete Profesional/Ajax SpaceCOntrol Jewellet.avif',
                description: 'Mando inalámbrico con botón de pánico y control de los modos de seguridad.'
            },
            {
                name: 'TurretCam (5 Mp/2.8 mm)',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquete Profesional/Render - Ajax TurretCam - Black - front3 4.png',
                description: 'Cámara IP de seguridad cableada con tecnología IA, ángulo de visión de 110°, iluminación IR, True WDR, micrófono y PoE/12 V. Para exteriores e interiores.'
            }
        ]
    },
    elite: {
        title: 'Elite/Monitoreo 24/7',
        intro: 'Solución corporativa para negocios que necesitan cobertura completa y respuesta garantizada.',
        components: [
            {
                name: 'Hub 2 Plus Jeweller',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquete Elite/Hub 2 Plus Jeweller.avif',
                description: 'Panel de control inalámbrico con soporte para la fotoverificación. Admite Wi-Fi, Ethernet y dos tarjetas SIM (2G/3G/LTE).'
            },
            {
                name: 'MotionCam (PhOD) Jeweller',
                badge: 'Cantidad: 2',
                image: 'img/Paquetes AJAX/Paquete Elite/MotionCam PhOD Jeweller.avif',
                description: 'Detector PIR e inalámbrico de movimiento con posibilidades ampliadas de verificación fotográfica.'
            },
            {
                name: 'DoorProtect Jeweller',
                badge: 'Cantidad: 2',
                image: 'img/Paquetes AJAX/Paquete Elite/DoorProtect Jeweller.avif',
                description: 'Detector inalámbrico de apertura con relé reed.'
            },
            {
                name: 'KeyPad Jeweller',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquete Elite/KeyPad Jeweller.avif',
                description: 'Teclado inalámbrico y táctil.'
            },
            {
                name: 'StreetSiren Jeweller',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquete Elite/StreetSiren Jeweller.avif',
                description: 'Sirena inalámbrica para interiores y exteriores.'
            },
            {
                name: 'SpaceControl Jeweller',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquete Elite/Ajax SpaceControl Jeweller.avif',
                description: 'Mando inalámbrico con botón de pánico y control de los modos de seguridad.'
            },
            {
                name: 'DoorBell',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquete Elite/Render - Black - Ajax DoorBell-front-angled.png',
                description: 'Video timbre con IA integrada, sensor PIR y control a través de apps.'
            }
        ]
    }
};

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function openServiceModal(title, description, imageSrc) {
    const modal = document.getElementById('serviceModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    const modalImage = document.getElementById('modalImage');
    if (!modal || !modalTitle || !modalBody || !modalImage) return;

    modal.classList.remove('package-modal');
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

function openPackageModal(packageKey) {
    const packageData = packageModalData[packageKey];
    const modal = document.getElementById('serviceModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    const modalImage = document.getElementById('modalImage');
    if (!packageData || !modal || !modalTitle || !modalBody || !modalImage) return;

    modal.classList.add('package-modal');
    modalTitle.textContent = `Componentes del paquete ${packageData.title}`;
    modalImage.src = '';
    modalImage.alt = '';
    modalImage.style.display = 'none';
    modalBody.innerHTML = `
        <p class="package-modal-intro">${escapeHtml(packageData.intro)}</p>
        <div class="package-included">
            <span>Monitoreo 24 hrs/7</span>
            <span>Apps incluidas</span>
        </div>
        <div class="package-components-grid">
            ${packageData.components.map((component) => `
                <article class="package-component-card">
                    <div class="package-component-media">
                        <img src="${escapeHtml(component.image)}" alt="${escapeHtml(component.name)}">
                    </div>
                    <div class="package-component-copy">
                        <h4>${escapeHtml(component.name)} <span>${escapeHtml(component.badge)}</span></h4>
                        <p>${escapeHtml(component.description)}</p>
                    </div>
                </article>
            `).join('')}
        </div>
    `;

    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeServiceModal() {
    const modal = document.getElementById('serviceModal');
    if (!modal) return;
    modal.classList.remove('package-modal');
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
