// Esperar a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar todas las funcionalidades
    initNavigation();
    initMobileMenu();
    initSmoothScrolling();
    initContactForm();
    initAnimations();
    initImageFallbacks();
    initSlidesSystem();
    initServiceModals();
    initHashNavigation();
});

// Función para inicializar la navegación (solo indicadores laterales)
function initNavigation() {
    // La navegación ahora solo se maneja a través de los indicadores laterales
    // que se inicializan en initSlidesSystem()
}

// Función para inicializar el menú móvil (ya no se necesita)
function initMobileMenu() {
    // El menú móvil se eliminó, solo se usa navegación lateral
}

// Función para inicializar el scroll suave
function initSmoothScrolling() {
    // El scroll suave ya está configurado en CSS con scroll-behavior: smooth
    // Esta función puede usarse para funcionalidades adicionales de scroll
}

// Función para hacer scroll a una sección específica
function scrollToSection(sectionId) {
    const slidesContainer = document.querySelector('.slides-container');
    const section = document.getElementById(sectionId);
    
    if (slidesContainer && section) {
        const slideTop = section.offsetTop;
        
        slidesContainer.scrollTo({
            top: slideTop,
            behavior: 'smooth'
        });
        
        // Actualizar URL sin recargar la página
        history.pushState(null, null, `#${sectionId}`);
    }
}

function initHashNavigation() {
    const initialSection = window.location.hash.replace('#', '');
    if (!initialSection) return;

    requestAnimationFrame(() => {
        scrollToSection(initialSection);
    });
}

// Función para inicializar el formulario de contacto
function initContactForm() {
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleContactFormSubmit(this);
        });
        
        // Validación en tiempo real
        const inputs = contactForm.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                clearFieldError(this);
            });
        });
    }
}

// Función para manejar el envío del formulario
function handleContactFormSubmit(form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    // Validar todos los campos
    let isValid = true;
    const fields = form.querySelectorAll('input, textarea');
    
    fields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });
    
    if (!isValid) {
        showNotification('Por favor, complete todos los campos correctamente.', 'error');
        return;
    }
    
    // Simular envío del formulario
    showNotification('Enviando mensaje...', 'info');
    
    // Simular delay de envío
    setTimeout(() => {
        // Aquí normalmente se enviaría el formulario a un servidor
        console.log('Datos del formulario:', data);
        
        showNotification('¡Mensaje enviado con éxito! Nos pondremos en contacto pronto.', 'success');
        form.reset();
        
        // Limpiar errores
        fields.forEach(field => {
            clearFieldError(field);
        });
    }, 2000);
}

// Función para validar un campo
function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';
    
    // Validaciones específicas por tipo de campo
    if (field.type === 'email') {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!value) {
            errorMessage = 'El email es requerido';
            isValid = false;
        } else if (!emailRegex.test(value)) {
            errorMessage = 'Por favor, ingrese un email válido';
            isValid = false;
        }
    } else if (field.type === 'text') {
        if (!value) {
            errorMessage = 'Este campo es requerido';
            isValid = false;
        } else if (value.length < 2) {
            errorMessage = 'Debe tener al menos 2 caracteres';
            isValid = false;
        }
    } else if (field.tagName === 'TEXTAREA') {
        if (!value) {
            errorMessage = 'El mensaje es requerido';
            isValid = false;
        } else if (value.length < 10) {
            errorMessage = 'El mensaje debe tener al menos 10 caracteres';
            isValid = false;
        }
    }
    
    if (!isValid) {
        showFieldError(field, errorMessage);
    } else {
        clearFieldError(field);
    }
    
    return isValid;
}

// Función para mostrar error en un campo
function showFieldError(field, message) {
    clearFieldError(field);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    errorDiv.style.color = '#ef4444';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '0.25rem';
    
    field.parentNode.appendChild(errorDiv);
    field.style.borderColor = '#ef4444';
}

// Función para limpiar error de un campo
function clearFieldError(field) {
    const errorDiv = field.parentNode.querySelector('.field-error');
    if (errorDiv) {
        errorDiv.remove();
    }
    field.style.borderColor = '';
}

// Función para mostrar notificaciones
function showNotification(message, type = 'info') {
    // Remover notificaciones existentes
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Crear nueva notificación
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    // Estilos de la notificación
    const styles = {
        position: 'fixed',
        top: '80px',
        right: '20px',
        padding: '1rem 1.5rem',
        borderRadius: '0.5rem',
        color: '#fff',
        fontWeight: '500',
        zIndex: '1000',
        maxWidth: '400px',
        boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1)',
        transform: 'translateX(100%)',
        transition: 'transform 0.3s ease'
    };
    
    // Aplicar estilos
    Object.assign(notification.style, styles);
    
    // Colores según tipo
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        info: '#3b82f6',
        warning: '#f59e0b'
    };
    
    notification.style.backgroundColor = colors[type] || colors.info;
    notification.textContent = message;
    
    // Agregar al DOM
    document.body.appendChild(notification);
    
    // Animar entrada
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Auto-remover después de 5 segundos
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 5000);
}

// Función para inicializar animaciones
function initAnimations() {
    // Observador de intersección para animaciones al hacer scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in-up');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Observar elementos para animar
    const animatedElements = document.querySelectorAll('.section, .service-card, .certification-card, .solution-card, .partner-card');
    animatedElements.forEach(element => {
        observer.observe(element);
    });
}

// Función para inicializar fallbacks de imágenes
function applyImageFallback(imageElement) {
    if (!imageElement || imageElement.dataset.fallbackApplied) return;

    function showFallback(img) {
        if (img.parentNode.querySelector('svg.fallback-svg')) return;

        const fallbackSVG = `
            <svg class="fallback-svg" width="100" height="100" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:${img.naturalWidth || 100}px; height:${img.naturalHeight || 100}px; object-fit:contain; max-width: 100%;">
                <rect width="100" height="100" fill="#f1f5f9"/>
                <path d="M30 40C30 34.4772 34.4772 30 40 30H60C65.5228 30 70 34.4772 70 40V60C70 65.5228 65.5228 70 60 70H40C34.4772 70 30 65.5228 30 60V40Z" fill="#cbd5e1"/>
                <path d="M35 45C35 42.2386 37.2386 40 40 40H60C62.7614 40 65 42.2386 65 45V55C65 57.7614 62.7614 60 60 60H40C37.2386 60 35 57.7614 35 55V45Z" fill="#94a3b8"/>
                <circle cx="45" cy="50" r="3" fill="#64748b"/>
                <path d="M35 65L45 55L55 65H35Z" fill="#64748b"/>
            </svg>
        `;

        img.style.display = 'none';
        img.insertAdjacentHTML('afterend', fallbackSVG);
    }

    if (imageElement.complete && imageElement.naturalWidth > 0) return;

    if (imageElement.complete && imageElement.naturalWidth === 0 && imageElement.src) {
        showFallback(imageElement);
        imageElement.dataset.fallbackApplied = 'true';
        return;
    }

    imageElement.addEventListener('error', function handler() {
        showFallback(this);
        this.dataset.fallbackApplied = 'true';
        this.removeEventListener('error', handler);
    });
}

function initImageFallbacks() {
    document.querySelectorAll('img').forEach(applyImageFallback);
}

// Función para manejar el scroll del header
function handleHeaderScroll() {
    const header = document.querySelector('.header');
    let lastScrollTop = 0;
    
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop > lastScrollTop && scrollTop > 100) {
            // Scrolling down
            header.style.transform = 'translateY(-100%)';
        } else {
            // Scrolling up
            header.style.transform = 'translateY(0)';
        }
        
        lastScrollTop = scrollTop;
    });
}

// Función para inicializar funcionalidades adicionales
function initAdditionalFeatures() {
    // Lazy loading para imágenes
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    // Smooth scroll para enlaces internos
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            scrollToSection(targetId);
        });
    });
    
    // Añadir clase activa al enlace de navegación actual
    window.addEventListener('scroll', function() {
        const sections = document.querySelectorAll('section[id]');
        const navItems = document.querySelectorAll('.nav-item, .mobile-menu-item');
        
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop - 100;
            const sectionHeight = section.clientHeight;
            if (window.pageYOffset >= sectionTop && window.pageYOffset < sectionTop + sectionHeight) {
                current = section.getAttribute('id');
            }
        });
        
        navItems.forEach(item => {
            item.classList.remove('active');
            if (item.getAttribute('data-section') === current) {
                item.classList.add('active');
            }
        });
    });
}

// Función para manejar el tema oscuro/claro (opcional)
function initThemeToggle() {
    const themeToggle = document.createElement('button');
    themeToggle.innerHTML = '🌙';
    themeToggle.className = 'theme-toggle';
    themeToggle.setAttribute('aria-label', 'Cambiar tema');
    
    // Estilos del botón de tema
    Object.assign(themeToggle.style, {
        position: 'fixed',
        top: '80px',
        left: '20px',
        width: '50px',
        height: '50px',
        borderRadius: '50%',
        border: 'none',
        backgroundColor: '#fff',
        boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)',
        cursor: 'pointer',
        fontSize: '20px',
        zIndex: '1000',
        transition: 'all 0.3s ease'
    });
    
    // Agregar al DOM
    document.body.appendChild(themeToggle);

    function applyTheme(theme) {
        const isDark = theme === 'dark';
        document.body.classList.toggle('dark-theme', isDark);
        document.documentElement.dataset.theme = theme;
        document.body.dataset.theme = theme;
        themeToggle.innerHTML = isDark ? '☀️' : '🌙';
        localStorage.setItem('theme', theme);
    }
    
    // Funcionalidad del toggle
    themeToggle.addEventListener('click', function() {
        const nextTheme = document.body.classList.contains('dark-theme') ? 'light' : 'dark';
        applyTheme(nextTheme);
    });
    
    // Aplicar tema guardado
    const savedTheme = localStorage.getItem('theme');
    applyTheme(savedTheme === 'dark' ? 'dark' : 'light');
}

// Función para inicializar todas las funcionalidades adicionales
function initAllFeatures() {
    handleHeaderScroll();
    initAdditionalFeatures();
    initThemeToggle();
}

// Inicializar funcionalidades adicionales después de un pequeño delay
setTimeout(initAllFeatures, 100);

// Manejar eventos de teclado para accesibilidad
document.addEventListener('keydown', function(e) {
    // ESC para cerrar menú móvil
    if (e.key === 'Escape') {
        closeMobileMenu();
    }
    
    // Enter para activar elementos enfocados
    if (e.key === 'Enter') {
        const focusedElement = document.activeElement;
        if (focusedElement && focusedElement.classList.contains('nav-item')) {
            const sectionId = focusedElement.getAttribute('data-section');
            scrollToSection(sectionId);
        }
    }
});

// Función para mejorar la accesibilidad
function enhanceAccessibility() {
    // Agregar roles ARIA
    const nav = document.querySelector('.nav-desktop');
    if (nav) {
        nav.setAttribute('role', 'navigation');
        nav.setAttribute('aria-label', 'Navegación principal');
    }
    
    const mobileMenu = document.getElementById('mobileMenu');
    if (mobileMenu) {
        mobileMenu.setAttribute('role', 'navigation');
        mobileMenu.setAttribute('aria-label', 'Menú móvil');
        mobileMenu.setAttribute('aria-hidden', 'true');
    }
    
    // Agregar skip links
    const skipLink = document.createElement('a');
    skipLink.href = '#main-content';
    skipLink.textContent = 'Saltar al contenido principal';
    skipLink.className = 'skip-link';
    Object.assign(skipLink.style, {
        position: 'absolute',
        top: '-40px',
        left: '6px',
        backgroundColor: '#000',
        color: '#fff',
        padding: '8px',
        textDecoration: 'none',
        zIndex: '1001'
    });
    
    skipLink.addEventListener('focus', function() {
        this.style.top = '6px';
    });
    
    skipLink.addEventListener('blur', function() {
        this.style.top = '-40px';
    });
    
    document.body.insertBefore(skipLink, document.body.firstChild);
    
    // Agregar ID al contenido principal
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        mainContent.id = 'main-content';
    }
}

// Inicializar mejoras de accesibilidad
enhanceAccessibility();

// ========================================
// SLIDES SYSTEM FUNCTIONS
// ========================================

// Función para inicializar el sistema de slides
function initSlidesSystem() {
    const slidesContainer = document.querySelector('.slides-container');
    const sideIndicators = document.querySelectorAll('.side-nav-indicator');
    const slides = document.querySelectorAll('.slide');
    
    if (!slidesContainer || sideIndicators.length === 0) return;
    
    // Configurar indicadores laterales
    sideIndicators.forEach(indicator => {
        indicator.addEventListener('click', function() {
            const sectionId = this.getAttribute('data-section');
            scrollToSlide(sectionId);
        });
    });
    
    // Observar cambios en el scroll para actualizar indicadores
    let scrollTimeout;
    
    slidesContainer.addEventListener('scroll', function() {
        // Actualizar inmediatamente para mejor responsividad
        updateActiveIndicator();
        
        // También actualizar después de un pequeño delay para capturar el estado final
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            updateActiveIndicator();
        }, 50);
    }, { passive: true });
    
    // Función para hacer scroll a un slide específico
    function scrollToSlide(sectionId) {
        const targetSlide = document.getElementById(sectionId);
        if (targetSlide && slidesContainer) {
            const slideTop = targetSlide.offsetTop;
            slidesContainer.scrollTo({
                top: slideTop,
                behavior: 'smooth'
            });
        }
    }
    
    // Función para actualizar el indicador activo
    function updateActiveIndicator() {
        const scrollTop = slidesContainer.scrollTop;
        const containerHeight = slidesContainer.clientHeight;
        
        let activeSection = null;
        
        // Encontrar la sección que está más visible en el viewport
        slides.forEach(slide => {
            const slideTop = slide.offsetTop;
            const slideHeight = slide.offsetHeight;
            const slideBottom = slideTop + slideHeight;
            
            // Calcular qué porcentaje del slide está visible
            const visibleTop = Math.max(slideTop, scrollTop);
            const visibleBottom = Math.min(slideBottom, scrollTop + containerHeight);
            const visibleHeight = Math.max(0, visibleBottom - visibleTop);
            const visibilityRatio = visibleHeight / slideHeight;
            
            // Si más del 50% del slide está visible, considerarlo activo
            if (visibilityRatio > 0.5) {
                activeSection = slide.id;
            }
            
            // Si estamos muy cerca del inicio del slide (dentro de los primeros 100px)
            if (scrollTop >= slideTop - 50 && scrollTop < slideTop + 100) {
                activeSection = slide.id;
            }
        });
        
        // Si no se encontró ninguna sección activa, usar la primera visible
        if (!activeSection) {
            slides.forEach(slide => {
                const slideTop = slide.offsetTop;
                const slideBottom = slideTop + slide.offsetHeight;
                
                if (scrollTop >= slideTop - 100 && scrollTop < slideBottom) {
                    activeSection = slide.id;
                    return;
                }
            });
        }
        
        // Actualizar indicadores
        sideIndicators.forEach(indicator => {
            indicator.classList.remove('active');
            if (indicator.getAttribute('data-section') === activeSection) {
                indicator.classList.add('active');
            }
        });
    }
    
    // Inicializar indicador activo
    updateActiveIndicator();
    
    // Usar Intersection Observer para mejor detección de secciones visibles
    const observerOptions = {
        root: slidesContainer,
        rootMargin: '-20% 0px -20% 0px', // Solo considerar activo cuando esté en el centro 60% del viewport
        threshold: [0, 0.25, 0.5, 0.75, 1]
    };
    
    const sectionObserver = new IntersectionObserver((entries) => {
        let mostVisibleSection = null;
        let maxVisibilityRatio = 0;
        
        entries.forEach(entry => {
            if (entry.isIntersecting && entry.intersectionRatio > maxVisibilityRatio) {
                maxVisibilityRatio = entry.intersectionRatio;
                mostVisibleSection = entry.target.id;
            }
        });
        
        if (mostVisibleSection) {
            // Actualizar indicadores basado en la sección más visible
            sideIndicators.forEach(indicator => {
                indicator.classList.remove('active');
                if (indicator.getAttribute('data-section') === mostVisibleSection) {
                    indicator.classList.add('active');
                }
            });
        }
    }, observerOptions);
    
    // Observar todas las secciones
    slides.forEach(slide => {
        sectionObserver.observe(slide);
    });
    
    // Manejar navegación con teclado
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
            e.preventDefault();
            navigateSlides(e.key === 'ArrowDown' ? 1 : -1);
        }
    });
    
    // Función para navegar entre slides con flechas
    function navigateSlides(direction) {
        const currentActive = document.querySelector('.side-nav-indicator.active');
        if (!currentActive) return;
        
        const currentIndex = Array.from(sideIndicators).indexOf(currentActive);
        const nextIndex = Math.max(0, Math.min(sideIndicators.length - 1, currentIndex + direction));
        const nextIndicator = sideIndicators[nextIndex];
        
        if (nextIndicator) {
            const sectionId = nextIndicator.getAttribute('data-section');
            scrollToSlide(sectionId);
        }
    }
    
    // La rueda del mouse conserva el scroll nativo para que las secciones
    // con más contenido, como Servicios y Valor Agregado, no se recorten.
}

// ========================================
// SERVICE MODALS FUNCTIONALITY
// ========================================

// Datos de los servicios con información detallada
const servicesData = {
    'monitoreo': {
        title: 'Monitoreo 24/7',
        description: 'Utiliza dispositivos inteligentes y sensores, conectados a una central de monitoreo activa las 24 horas del día, los 7 días de la semana. Estos dispositivos, como cámaras, detectores de movimiento y botones de pánico, envían alertas en tiempo real, que son analizadas por profesionales en seguridad para determinar la respuesta adecuada.',
        image: './img/Servicios/Monitoreo.jpg',
        icons: [
            { image: './img/Certificaciones/Logo-Bosch.png', label: 'Bosch' },
            { image: './img/Certificaciones/Logo-DMP.png', label: 'DMP' },
            { image: './img/Certificaciones/Logo-Lenel.png', label: 'Lenel' },
            { image: './img/Certificaciones/Logo-Notifier.png', label: 'Notifier' }
        ]
    },
    'control-acceso': {
        title: 'Control de Acceso',
        description: 'Sistemas inteligentes de control de acceso que permiten gestionar quién, cuándo y dónde pueden ingresar las personas a sus instalaciones. Incluye tecnologías biométricas, tarjetas de proximidad, códigos PIN y reconocimiento facial para máxima seguridad y trazabilidad completa.',
        image: './img/Servicios/ControlDeAcceso.jpg',
        icons: [
            { image: './img/Certificaciones/Logo-Bosch.png', label: 'Biométrico' },
            { image: './img/Certificaciones/Logo-DMP.png', label: 'Tarjetas' },
            { image: './img/Certificaciones/Logo-Lenel.png', label: 'Códigos' },
            { image: './img/Certificaciones/Logo-DNV.png', label: 'Facial' }
        ]
    },
    'intrusion': {
        title: 'Sistema de Intrusión',
        description: 'Detectores de movimiento de última generación, sensores perimetrales y sistemas de alarma inteligentes que identifican intrusiones no autorizadas. Nuestros sistemas minimizan las falsas alarmas mientras mantienen la máxima sensibilidad para detectar amenazas reales.',
        image: './img/Servicios/Intrusión.jpg',
        icons: [
            { image: './img/Certificaciones/Logo-Bosch.png', label: 'Sensores' },
            { image: './img/Certificaciones/Logo-DMP.png', label: 'Perimetral' },
            { image: './img/Certificaciones/Logo-Notifier.png', label: 'Alarmas' }
        ]
    },
    'incendios': {
        title: 'Detección de Incendios',
        description: 'Sistemas de detección temprana de incendios con sensores de humo, calor y llama. Incluye notificación automática a cuerpos de bomberos y sistemas de supresión. Cumplimos con todas las normativas nacionales e internacionales de seguridad contra incendios.',
        image: './img/Servicios/Incendios.jpg',
        icons: [
            { image: './img/Certificaciones/Logo-Notifier.png', label: 'Detectores' },
            { image: './img/Certificaciones/Logo-Bosch.png', label: 'Supresión' },
            { image: './img/Certificaciones/Logo-DNV.png', label: 'Normativas' }
        ]
    },
    'niebla': {
        title: 'Niebla de Seguridad',
        description: 'Sistema innovador que genera una densa niebla no tóxica en segundos, reduciendo la visibilidad a menos de 50cm. Esto disuade efectivamente a los intrusos y protege bienes valiosos sin causar daños. Ideal para joyerías, bancos y tiendas de lujo.',
        image: './img/Servicios/Niebla.png',
        icons: [
            { image: './img/Certificaciones/Logo-Bosch.png', label: 'No Tóxico' },
            { image: './img/Certificaciones/Logo-DMP.png', label: 'Rápido' },
            { image: './img/Certificaciones/Logo-Lenel.png', label: 'Efectivo' }
        ]
    },
    'videovigilancia': {
        title: 'Videovigilancia',
        description: 'Cámaras de alta definición 4K con visión nocturna, análisis inteligente de video y almacenamiento en la nube. Incluye detección de movimiento, reconocimiento facial y alertas automáticas. Acceso remoto desde cualquier dispositivo móvil o computadora.',
        image: './img/Servicios/Videovigilancia.jpg',
        icons: [
            { image: './img/Certificaciones/Logo-Bosch.png', label: '4K HD' },
            { image: './img/Certificaciones/Logo-DMP.png', label: 'Nocturna' },
            { image: './img/Certificaciones/Logo-Lenel.png', label: 'IA' },
            { image: './img/Certificaciones/Logo-DNV.png', label: 'Remoto' }
        ]
    },
    'sismico': {
        title: 'Alertamiento Sísmico',
        description: 'Sistema de alerta temprana conectado a la red sismológica nacional que proporciona avisos hasta 120 segundos antes de que lleguen las ondas sísmicas destructivas. Incluye protocolos automáticos de evacuación y protección de equipos críticos.',
        image: './img/Servicios/Sismos.png',
        icons: [
            { image: './img/Certificaciones/Logo-DNV.png', label: 'Temprana' },
            { image: './img/Certificaciones/Logo-Bosch.png', label: 'Nacional' },
            { image: './img/Certificaciones/Logo-DMP.png', label: 'Automático' }
        ]
    },
    'antenas': {
        title: 'Antenas',
        description: 'Sistemas de comunicación inalámbrica para garantizar conectividad y transmisión de datos en tiempo real. Ideales para enlazar puntos remotos, extender cobertura de red y asegurar la comunicación de sistemas críticos de seguridad.',
        image: './img/Servicios/Antenas.jpg',
        icons: [
            { image: './img/Certificaciones/Logo-Bosch.png', label: 'Largo Alcance' },
            { image: './img/Certificaciones/Logo-DMP.png', label: 'Confiable' },
            { image: './img/Certificaciones/Logo-Lenel.png', label: 'Alta Velocidad' }
        ]
    },
    'videoverificacion': {
        title: 'Videoverificación',
        description: 'Verificación visual de alarmas mediante cámaras para reducir falsas alarmas y mejorar la respuesta. Nuestros operadores confirman visualmente la causa de una alerta antes de despachar a las autoridades, asegurando una respuesta rápida y precisa solo cuando es necesario.',
        image: './img/Servicios/Videoverificacion.jpg',
        icons: [
            { image: './img/Certificaciones/Logo-Bosch.png', label: 'Confirmación Visual' },
            { image: './img/Certificaciones/Logo-DMP.png', label: 'Respuesta Rápida' },
            { image: './img/Certificaciones/Logo-Notifier.png', label: 'Menos Falsas Alarmas' }
        ]
    },
    'rastreo-gps': {
        title: 'Rastreo GPS',
        description: 'Localización y seguimiento en tiempo real de vehículos y activos móviles para mayor seguridad y control logístico. Monitorea rutas, velocidad y paradas desde nuestra plataforma web o app móvil.',
        image: './img/Servicios/RastreoGPS.jpg',
        icons: [
            { image: './img/Certificaciones/Logo-DNV.png', label: 'Tiempo Real' },
            { image: './img/Certificaciones/Logo-Bosch.png', label: 'Control Logístico' },
            { image: './img/Certificaciones/Logo-DMP.png', label: 'App Móvil' }
        ]
    },
    'acceso-web': {
        title: 'Acceso Web Corporativo',
        description: 'Plataforma web para gestión remota y monitoreo de sistemas de seguridad desde cualquier dispositivo. Controle accesos, revise historiales y reciba notificaciones en tiempo real.',
        image: './img/Servicios/ValorAgregado/AccesoWeb.jpg',
        icons: [] // Sin iconos
    },
    'soluciones-integrales': {
        title: 'Soluciones Integrales',
        description: 'Diseño e implementación de sistemas de seguridad completos, desde el análisis inicial hasta la puesta en marcha y el soporte continuo, adaptados a las necesidades específicas de su negocio.',
        image: './img/Servicios/ValorAgregado/SolucionesIntegrales.jpg',
        icons: [] // Sin iconos
    },
    'analisis-riesgo': {
        title: 'Análisis de Riesgo',
        description: 'Evaluación profesional de vulnerabilidades en sus instalaciones para identificar puntos débiles y diseñar estrategias de seguridad personalizadas que minimicen amenazas potenciales.',
        image: './img/Servicios/ValorAgregado/AnalisisRiesgo.jpg',
        icons: [] // Sin iconos
    },
    'mantenimiento-preventivo': {
        title: 'Mantenimiento Preventivo',
        description: 'Programas de mantenimiento regular para garantizar el funcionamiento óptimo y la longevidad de todos sus sistemas de seguridad, evitando fallos inesperados.',
        image: './img/Servicios/ValorAgregado/MantenimientoPreventivo.jpg',
        icons: [] // Sin iconos
    }
};

// Función para inicializar los modales de servicios
function initServiceModals() {
    const clickableCards = document.querySelectorAll('.service-card, .value-added-card');
    const modal = document.getElementById('serviceModal');
    const modalClose = document.querySelector('.modal-close');
    
    if (!modal) return;
    
    // Agregar event listeners a las tarjetas de servicio
    clickableCards.forEach(card => {
        card.addEventListener('click', function() {
            const serviceId = this.getAttribute('data-service');
            openServiceModal(serviceId);
        });
        
        // Agregar indicador visual de que es clickeable
        card.style.cursor = 'pointer';
        card.setAttribute('tabindex', '0');
        card.setAttribute('role', 'button');
        
        // Soporte para teclado
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const serviceId = this.getAttribute('data-service');
                openServiceModal(serviceId);
            }
        });
    });
    
    // Cerrar modal con botón X
    if (modalClose) {
        modalClose.addEventListener('click', closeServiceModal);
    }
    
    // Cerrar modal al hacer clic fuera del contenido
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeServiceModal();
        }
    });
    
    // Cerrar modal con tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('show')) {
            closeServiceModal();
        }
    });
}

// Función para abrir el modal de servicio
function openServiceModal(serviceId) {
    const modal = document.getElementById('serviceModal');
    const serviceData = servicesData[serviceId];
    
    if (!modal || !serviceData) return;
    
    // Actualizar contenido del modal
    document.getElementById('modalTitle').textContent = serviceData.title;
    document.getElementById('modalDescription').textContent = serviceData.description; // Esto es un <p>, así que está bien
    const modalImage = document.getElementById('modalImage');
    
    // Limpiar estado anterior para que cada servicio cargue su imagen real.
    modalImage.parentNode.querySelectorAll('svg.fallback-svg').forEach(svg => svg.remove());
    modalImage.style.display = '';
    delete modalImage.dataset.fallbackApplied;

    modalImage.src = serviceData.image;
    modalImage.alt = serviceData.title;
    applyImageFallback(modalImage);

    // Manejo de la sección "Los dispositivos que implementamos"
    const modalIconsSection = document.querySelector('.modal-icons');
    const iconsContainer = document.getElementById('modalIcons');
    iconsContainer.innerHTML = '';

    // Desactivar/ocultar siempre la sección de dispositivos e iconos
    if (modalIconsSection) {
        modalIconsSection.style.display = 'none';
    }
    
    // Mostrar modal
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
    
    // Focus en el botón de cerrar para accesibilidad
    setTimeout(() => {
        document.querySelector('.modal-close').focus();
    }, 100);
}

// Función para cerrar el modal de servicio
function closeServiceModal() {
    const modal = document.getElementById('serviceModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

// ========================================
// CAROUSEL FUNCTIONALITY
// ========================================

// Continuous marquee-style scroll
const continuousStates = {};

function initContinuousCarousels() {
    const tracks = document.querySelectorAll('.carousel-track');
    tracks.forEach(track => {
        const id = track.id;
        if (!id) return;

        // Duplicate items once for seamless loop
        if (!track.dataset.duplicated) {
            track.innerHTML = track.innerHTML + track.innerHTML;
            track.dataset.duplicated = 'true';
            track.classList.add('carousel-continuous');
        }

        // Calculate width of one set (half of children)
        const children = Array.from(track.children);
        const half = Math.floor(children.length / 2);
        const gap = parseFloat(getComputedStyle(track).gap || '8');
        const halfWidth = children.slice(0, half).reduce((sum, el, idx) => {
            const rect = el.getBoundingClientRect();
            return sum + rect.width + (idx < half - 1 ? gap : 0);
        }, 0);

        if (!continuousStates[id]) continuousStates[id] = { position: 0, speed: 0.2, halfWidth: halfWidth };
        else {
            continuousStates[id].halfWidth = halfWidth;
        }
    });

    if (!initContinuousCarousels._started) {
        initContinuousCarousels._started = true;
        requestAnimationFrame(stepContinuousCarousels);
    }
}

function stepContinuousCarousels() {
    Object.keys(continuousStates).forEach(id => {
        const state = continuousStates[id];
        const track = document.getElementById(id);
        if (!track || !state || !state.halfWidth) return;

        state.position -= state.speed; // move left smoothly
        if (Math.abs(state.position) >= state.halfWidth) {
            state.position = 0; // reset seamlessly
        }
        track.style.transform = `translateX(${state.position}px)`;
    });
    requestAnimationFrame(stepContinuousCarousels);
}

// Initialize carousels when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit for CSS to load
    setTimeout(() => {
        initContinuousCarousels();
    }, 100);
});

// Handle window resize
window.addEventListener('resize', function() {
    setTimeout(() => initContinuousCarousels(), 50);
});

// Meta-slider logic
let metaSlideIndex = 0;
const metaSliderTrack = document.getElementById('metaSliderTrack');
const metaSlides = metaSliderTrack ? metaSliderTrack.children.length : 0;

function moveMetaSlide(direction) {
    if (!metaSliderTrack) return;
    metaSlideIndex += direction;
    if (metaSlideIndex < 0) metaSlideIndex = 0;
    if (metaSlideIndex >= metaSlides) metaSlideIndex = metaSlides - 1;
    metaSliderTrack.style.transform = `translateX(-${metaSlideIndex * 100}%)`;
}

// Optionally, allow arrow keys for navigation
document.addEventListener('keydown', function(e) {
    if (document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'TEXTAREA') return;
    if (e.key === 'ArrowLeft')moveMetaSlide(-1);
    if (e.key === 'ArrowRight') moveMetaSlide(1);
});

// Exportar funciones para uso global si es necesario
window.WebApp = {
    scrollToSection,
    showNotification,
    initSlidesSystem,
    openServiceModal,

    closeServiceModal,
    initContinuousCarousels
};
