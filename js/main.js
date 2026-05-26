// ─── SERVICE MODAL ───
const packageModalData = {
    essential: {
        title: 'Essential/Monitoreo 24/7',
        intro: 'Protección ideal para espacios pequeños con componentes básicos de seguridad.',
        components: [
            {
                name: 'Hub 2 (4G) Jeweller',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquetes alta calidad/Hub 2 (4G) Jeweller.png',
                imageWhite: 'img/Paquetes AJAX/Paquetes alta calidad/Hub 2 (4G) Jeweller W.png',
                description: 'Panel de control inalámbrico con soporte para la fotoverificación. Admite Ethernet y dos tarjetas SIM (2G/3G/LTE).'
            },
            {
                name: 'MotionProtect Jeweller',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquetes alta calidad/Render - Ajax MotionProtect U Jeweller - Black - front.png',
                imageWhite: 'img/Paquetes AJAX/Paquetes alta calidad/Render - Ajax MotionProtect U Jeweller - White - front clean.png',
                description: 'Detector IR e inalámbrico de movimiento.'
            },
            {
                name: 'DoorProtect Jeweller',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquetes alta calidad/Render - Ajax DoorProtect U Jeweller - Black - front.png',
                imageWhite: 'img/Paquetes AJAX/Paquetes alta calidad/Render - Ajax DoorProtect U Jeweller - Black - front W.png',
                description: 'Detector inalámbrico de apertura con relé reed.'
            },
            {
                name: 'HomeSiren Jeweller',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquetes alta calidad/Render - Ajax HomeSiren U Jeweller - Black - front angled.png',
                imageWhite: 'img/Paquetes AJAX/Paquetes alta calidad/Render - Ajax HomeSiren U Jeweller - Black - front angled W.png',
                description: 'Sirena inalámbrica.'
            },
            {
                name: 'SpaceControl Jeweller',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquetes alta calidad/ajax-superior-spacecontrol-black-front.png',
                imageWhite: 'img/Paquetes AJAX/Paquetes alta calidad/ajax-superior-spacecontrol-black-front W.png',
                description: 'Mando inalámbrico con botón de pánico y control de los modos de seguridad.'
            },
            {
                name: 'TurretCam (5 Mp/2.8 mm)',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquete Essential/Render - Ajax TurretCam - Black - front3 4.png',
                imageWhite: 'img/Paquetes AJAX/Paquetes alta calidad/Render - Ajax TurretCam - White - front clean W.png',
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
                image: 'img/Paquetes AJAX/Paquetes alta calidad/Hub 2 (4G) Jeweller.png',
                imageWhite: 'img/Paquetes AJAX/Paquetes alta calidad/Hub 2 (4G) Jeweller W.png',
                description: 'Panel de control inalámbrico con soporte para fotoverificación. Admite Wi-Fi, Ethernet y dos tarjetas SIM (2G/3G/LTE).'
            },
            {
                name: 'MotionCam (PhOD) Jeweller',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquetes alta calidad/Render - Ajax MotionCam U (PhOD) Jeweller - Black - front.png',
                imageWhite: 'img/Paquetes AJAX/Paquetes alta calidad/Render - Ajax MotionCam U (PhOD) Jeweller - Black - front W.png',
                description: 'Detector PIR e inalámbrico de movimiento con posibilidades ampliadas de verificación fotográfica.'
            },
            {
                name: 'DoorProtect Jeweller',
                badge: 'Cantidad: 2',
                image: 'img/Paquetes AJAX/Paquetes alta calidad/Render - Ajax DoorProtect U Jeweller - Black - front.png',
                imageWhite: 'img/Paquetes AJAX/Paquetes alta calidad/Render - Ajax DoorProtect U Jeweller - Black - front W.png',
                description: 'Detector inalámbrico de apertura con relé reed.'
            },
            {
                name: 'KeyPad Jeweller',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquetes alta calidad/Render - Ajax KeyPad Plus Jeweller-Black-front.png',
                imageWhite: 'img/Paquetes AJAX/Paquetes alta calidad/Render - Ajax KeyPad Plus Jeweller-Black-front W.png',
                description: 'Teclado inalámbrico y táctil.'
            },
            {
                name: 'StreetSiren Jeweller',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquetes alta calidad/Render - Ajax HomeSiren U Jeweller - Black - front angled.png',
                imageWhite: 'img/Paquetes AJAX/Paquetes alta calidad/Render - Ajax HomeSiren U Jeweller - Black - front angled W.png',
                description: 'Sirena inalámbrico para interiores y exteriores.'
            },
            {
                name: 'SpaceControl Jeweller',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquetes alta calidad/ajax-superior-spacecontrol-black-front.png',
                imageWhite: 'img/Paquetes AJAX/Paquetes alta calidad/ajax-superior-spacecontrol-black-front W.png',
                description: 'Mando inalámbrico con botón de pánico y control de los modos de seguridad.'
            },
            {
                name: 'TurretCam (5 Mp/2.8 mm)',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquete Profesional/Render - Ajax TurretCam - Black - front3 4.png',
                imageWhite: 'img/Paquetes AJAX/Paquetes alta calidad/Render - Ajax TurretCam - White - front clean W.png',
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
                image: 'img/Paquetes AJAX/Paquetes alta calidad/Hub 2 (4G) Jeweller.png',
                imageWhite: 'img/Paquetes AJAX/Paquetes alta calidad/Hub 2 (4G) Jeweller W.png',
                description: 'Panel de control inalámbrico con soporte para la fotoverificación. Admite Wi-Fi, Ethernet y dos tarjetas SIM (2G/3G/LTE).'
            },
            {
                name: 'MotionCam (PhOD) Jeweller',
                badge: 'Cantidad: 2',
                image: 'img/Paquetes AJAX/Paquetes alta calidad/Render - Ajax MotionCam U (PhOD) Jeweller - Black - front.png',
                imageWhite: 'img/Paquetes AJAX/Paquetes alta calidad/Render - Ajax MotionCam U (PhOD) Jeweller - Black - front W.png',
                description: 'Detector PIR e inalámbrico de movimiento con posibilidades ampliadas de verificación fotográfica.'
            },
            {
                name: 'DoorProtect Jeweller',
                badge: 'Cantidad: 2',
                image: 'img/Paquetes AJAX/Paquetes alta calidad/Render - Ajax DoorProtect U Jeweller - Black - front.png',
                imageWhite: 'img/Paquetes AJAX/Paquetes alta calidad/Render - Ajax DoorProtect U Jeweller - Black - front W.png',
                description: 'Detector inalámbrico de apertura con relé reed.'
            },
            {
                name: 'KeyPad Jeweller',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquetes alta calidad/Render - Ajax KeyPad Plus Jeweller-Black-front.png',
                imageWhite: 'img/Paquetes AJAX/Paquetes alta calidad/Render - Ajax KeyPad Plus Jeweller-Black-front W.png',
                description: 'Teclado inalámbrico y táctil.'
            },
            {
                name: 'StreetSiren Jeweller',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquetes alta calidad/Render - Ajax HomeSiren U Jeweller - Black - front angled.png',
                imageWhite: 'img/Paquetes AJAX/Paquetes alta calidad/Render - Ajax HomeSiren U Jeweller - Black - front angled W.png',
                description: 'Sirena inalámbrica para interiores y exteriores.'
            },
            {
                name: 'SpaceControl Jeweller',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquetes alta calidad/ajax-superior-spacecontrol-black-front.png',
                imageWhite: 'img/Paquetes AJAX/Paquetes alta calidad/ajax-superior-spacecontrol-black-front W.png',
                description: 'Mando inalámbrico con botón de pánico y control de los modos de seguridad.'
            },
            {
                name: 'DoorBell',
                badge: 'Cantidad: 1',
                image: 'img/Paquetes AJAX/Paquete Elite/Render - Black - Ajax DoorBell-front-angled.png',
                imageWhite: 'img/Paquetes AJAX/Paquetes alta calidad/Render - Ajax DoorBell - White - front angled clean.png',
                description: 'Video timbre con IA integrada, sensor PIR y control a través de apps.'
            }
        ]
    }
};

let packageGalleryState = {
    packageKey: null,
    index: 0
};

const packageColorState = {
    essential: 'black',
    professional: 'black',
    elite: 'black'
};

const packageColorImages = {
    essential: {
        black: 'img/Paquetes AJAX/Paquete Essential/Essential.png',
        white: 'img/Paquetes AJAX/Paquete Essential/Essential W Card.png'
    },
    professional: {
        black: 'img/Paquetes AJAX/Paquete Profesional/Profesional.png',
        white: 'img/Paquetes AJAX/Paquete Profesional/Profesional W Card.png'
    },
    elite: {
        black: 'img/Paquetes AJAX/Paquete Elite/Elite.png',
        white: 'img/Paquetes AJAX/Paquete Elite/Elite W Card.png'
    }
};

const branchLocations = {
    cdmx: {
        title: 'CDMX',
        address: '5 de Febrero 283 Col. Obrera. Cuauhtémoc, CP. 06800 CDMX',
        mapUrl: 'https://www.google.com/maps/search/?api=1&query=Central%20de%20Alarmas%20CDMX'
    },
    puebla: {
        title: 'Puebla',
        address: 'Av. 17 Poniente, #916 int. 8, C.P. 72090 Col. Centro Puebla, Puebla',
        mapUrl: 'https://www.google.com/maps/search/?api=1&query=Central%20de%20Alarmas%20Puebla'
    },
    guadalajara: {
        title: 'Guadalajara',
        address: 'C. José María Vigil 2312 - Interior 5, Italia Providencia, 44656 Guadalajara, Jal.',
        mapUrl: 'https://www.google.com/maps/search/?api=1&query=Central%20de%20Alarmas%20Guadalajara'
    },
    monterrey: {
        title: 'Monterrey',
        address: 'Av. Paseo de los Leones No. 860. Local 2A, Col. Leones, C.P. 64600, Monterrey, Nuevo León',
        mapUrl: 'https://www.google.com/maps/search/?api=1&query=Central%20de%20Alarmas%20Monterrey'
    },
    queretaro: {
        title: 'Querétaro',
        address: 'Av. México 61 N, Plaza de las Américas, Querétaro, Qro. C.P. 76050',
        mapUrl: 'https://www.google.com/maps/search/?api=1&query=Central%20de%20Alarmas%20Quer%C3%A9taro'
    }
};

function getPackageColor(packageKey) {
    return packageColorState[packageKey] || 'black';
}

const translations = {
    es: {
        'nav.services': 'Servicios',
        'nav.packages': 'Paquetes',
        'nav.about': 'Nosotros',
        'nav.contact': 'Contacto',
        'nav.news': 'News',
        'nav.jobs': 'Empleos',
        'jobs.heroTitle': 'Desarrolla tu talento en seguridad',
        'jobs.heroDesc': 'Forma parte de una de las empresas mexicanas con mayor trayectoria y prestigio en el sector de la seguridad electrónica. Construye tu futuro profesional con nosotros.',
        'nav.pay': 'Pagar',
        'language.label': 'Idioma',
        'hero.advice': 'Recibe asesoría',
        'hero.viewPackages': 'Ver paquetes',
        'hero.words': ['hogar', 'negocio', 'empresa'],
        'packages.eyebrow': 'Nuestros paquetes',
        'packages.title': 'Encuentra el plan perfecto para ti',
        'packages.subtitle': 'Desde protección básica hasta soluciones corporativas completas.',
        'packages.availableColors': 'Disponible en',
        'packages.white': 'blanco',
        'packages.black': 'negro',
        'packages.viewPackage': 'Ver paquete',
        'packages.galleryButton': 'Galería',
        'packages.requestInfo': 'Solicitar más información',
        'packages.modalTitle': 'Componentes del paquete {title}',
        'packages.galleryTitle': 'Galería {title}',
        'packages.included.monitoring': 'Monitoreo 24 hrs/7',
        'packages.included.apps': 'App incluida',
        'packages.included.installation': 'Instalación gratuita',
        'packages.included.colors': 'Disponible en blanco o negro',
        'packages.ajaxCard.title': 'Sistema AJAX incluido',
        'packages.ajaxCard.badge': 'INCLUIDO',
        'packages.ajaxCard.description': 'Ecosistema profesional AJAX integrado al paquete para control, alertas y operación desde la app.',
        'packages.colorAria': 'Colores disponibles: blanco y negro',
        'packages.colorWhiteAria': 'Color blanco',
        'packages.colorBlackAria': 'Color negro',
        'gallery.count': '{current} de {total}',
        'gallery.prev': 'Ver componente anterior',
        'gallery.next': 'Ver siguiente componente',
        'gallery.nav': 'Navegación de componentes',
        'gallery.goTo': 'Ver componente {index}'
    },
    en: {
        'nav.services': 'Services',
        'nav.packages': 'Packages',
        'nav.about': 'About',
        'nav.contact': 'Contact',
        'nav.news': 'News',
        'nav.jobs': 'Careers',
        'jobs.heroTitle': 'Develop your talent in security',
        'jobs.heroDesc': 'Join one of the Mexican companies with the longest history and prestige in the electronic security sector. Build your professional future with us.',
        'nav.pay': 'Pay',
        'language.label': 'Language',
        'hero.advice': 'Get advice',
        'hero.viewPackages': 'View packages',
        'hero.words': ['home', 'business', 'company'],
        'packages.eyebrow': 'Our packages',
        'packages.title': 'Find the right plan for you',
        'packages.subtitle': 'From basic protection to complete corporate solutions.',
        'packages.availableColors': 'Available in',
        'packages.white': 'white',
        'packages.black': 'black',
        'packages.viewPackage': 'View package',
        'packages.galleryButton': 'Gallery',
        'packages.requestInfo': 'Request more information',
        'packages.modalTitle': '{title} package components',
        'packages.galleryTitle': '{title} gallery',
        'packages.included.monitoring': '24/7 monitoring',
        'packages.included.apps': 'App included',
        'packages.included.installation': 'Free installation',
        'packages.included.colors': 'Available in white or black',
        'packages.ajaxCard.title': 'AJAX system included',
        'packages.ajaxCard.badge': 'INCLUDED',
        'packages.ajaxCard.description': 'Professional AJAX ecosystem integrated into the package for control, alerts, and app-based operation.',
        'packages.colorAria': 'Available colors: white and black',
        'packages.colorWhiteAria': 'White color',
        'packages.colorBlackAria': 'Black color',
        'gallery.count': '{current} of {total}',
        'gallery.prev': 'View previous component',
        'gallery.next': 'View next component',
        'gallery.nav': 'Component navigation',
        'gallery.goTo': 'View component {index}'
    },
    zh: {
        'nav.services': '服务',
        'nav.packages': '套餐',
        'nav.about': '关于我们',
        'nav.contact': '联系',
        'nav.news': '新闻',
        'nav.jobs': '招聘',
        'jobs.heroTitle': '在安全领域发展您的才华',
        'jobs.heroDesc': '加入在电子安全领域拥有悠久历史和声誉的墨西哥公司之一。与我们共同构建您的职业未来。',
        'nav.pay': '付款',
        'language.label': '语言',
        'hero.advice': '获取咨询',
        'hero.viewPackages': '查看套餐',
        'hero.words': ['家庭', '商铺', '企业'],
        'packages.eyebrow': '我们的套餐',
        'packages.title': '找到适合您的方案',
        'packages.subtitle': '从基础防护到完整企业解决方案。',
        'packages.availableColors': '可选颜色',
        'packages.white': '白色',
        'packages.black': '黑色',
        'packages.viewPackage': '查看套餐',
        'packages.galleryButton': '图库',
        'packages.requestInfo': '索取更多信息',
        'packages.modalTitle': '{title} 套餐组件',
        'packages.galleryTitle': '{title} 图库',
        'packages.included.monitoring': '全天候监控',
        'packages.included.apps': '包含应用',
        'packages.included.installation': '免费安装',
        'packages.included.colors': '提供白色或黑色',
        'packages.ajaxCard.title': '包含 AJAX 系统',
        'packages.ajaxCard.badge': '已包含',
        'packages.ajaxCard.description': '专业 AJAX 生态系统集成在套餐中，可通过应用进行控制、接收警报并管理操作。',
        'packages.colorAria': '可选颜色：白色和黑色',
        'packages.colorWhiteAria': '白色',
        'packages.colorBlackAria': '黑色',
        'gallery.count': '{current} / {total}',
        'gallery.prev': '查看上一个组件',
        'gallery.next': '查看下一个组件',
        'gallery.nav': '组件导航',
        'gallery.goTo': '查看组件 {index}'
    }
};

let currentLanguage = localStorage.getItem('siteLanguage') || 'es';

function t(key, replacements = {}) {
    const dictionary = translations[currentLanguage] || translations.es;
    let value = dictionary[key] || translations.es[key] || key;
    if (Array.isArray(value)) return value;
    Object.entries(replacements).forEach(([token, replacement]) => {
        value = value.replace(`{${token}}`, replacement);
    });
    return value;
}

function applyTranslations(language) {
    currentLanguage = translations[language] ? language : 'es';
    localStorage.setItem('siteLanguage', currentLanguage);
    document.documentElement.lang = currentLanguage;

    document.querySelectorAll('[data-i18n]').forEach((element) => {
        element.textContent = t(element.dataset.i18n);
    });

    document.querySelectorAll('.color-swatches').forEach((group) => {
        group.setAttribute('aria-label', t('packages.colorAria'));
    });
    document.querySelectorAll('.color-swatch-white').forEach((swatch) => {
        swatch.setAttribute('aria-label', t('packages.colorWhiteAria'));
        swatch.title = t('packages.white');
    });
    document.querySelectorAll('.color-swatch-black').forEach((swatch) => {
        swatch.setAttribute('aria-label', t('packages.colorBlackAria'));
        swatch.title = t('packages.black');
    });

    const wordsForLanguage = t('hero.words');
    if (word2Element && Array.isArray(wordsForLanguage)) {
        currentWordIndex = Math.min(currentWordIndex, wordsForLanguage.length - 1);
        word2Element.textContent = wordsForLanguage[currentWordIndex];
    }

    document.querySelectorAll('#languageSelect, #mobileLanguageSelect').forEach((select) => {
        select.value = currentLanguage;
    });

    const languageSwitcherValue = document.getElementById('languageSwitcherValue');
    if (languageSwitcherValue) {
        const siteLanguage = Array.isArray(window.siteLanguages)
            ? window.siteLanguages.find((language) => language.code === currentLanguage)
            : null;
        if (siteLanguage && siteLanguage.flag) {
            languageSwitcherValue.innerHTML = `
                <span class="language-switcher-flag" aria-hidden="true">${siteLanguage.flag}</span>
                <span class="language-switcher-code">${siteLanguage.label}</span>
            `;
        } else {
            languageSwitcherValue.textContent = currentLanguage.toUpperCase();
        }
    }
}

window.applyTranslations = applyTranslations;
window.siteTranslations = translations;

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
    modal.classList.remove('package-gallery-modal');
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

    modal.classList.remove('package-gallery-modal');
    modal.classList.add('package-modal');
    modal.dataset.packageKey = packageKey;
    modalTitle.textContent = t('packages.modalTitle', { title: packageData.title });
    modalImage.src = '';
    modalImage.alt = '';
    modalImage.style.display = 'none';
    modalBody.innerHTML = `
        <p class="package-modal-intro">${escapeHtml(packageData.intro)}</p>
        <div class="package-included">
            <span>${escapeHtml(t('packages.included.monitoring'))}</span>
            <span>${escapeHtml(t('packages.included.apps'))}</span>
            <span>${escapeHtml(t('packages.included.installation'))}</span>
            <span class="package-color-pill">
                ${escapeHtml(t('packages.availableColors'))}
                <span class="color-swatches" data-package-key="${escapeHtml(packageKey)}" aria-label="${escapeHtml(t('packages.colorAria'))}">
                    <button type="button" class="color-swatch color-swatch-white${getPackageColor(packageKey) === 'white' ? ' active' : ''}" data-package-color="${escapeHtml(t('packages.white'))}" data-color-value="white" aria-label="${escapeHtml(t('packages.colorWhiteAria'))}" aria-pressed="${getPackageColor(packageKey) === 'white'}"></button>
                    <button type="button" class="color-swatch color-swatch-black${getPackageColor(packageKey) === 'black' ? ' active' : ''}" data-package-color="${escapeHtml(t('packages.black'))}" data-color-value="black" aria-label="${escapeHtml(t('packages.colorBlackAria'))}" aria-pressed="${getPackageColor(packageKey) === 'black'}"></button>
                </span>
            </span>
        </div>
        <div class="package-components-grid">
            ${packageData.components.map((component) => `
                <article class="package-component-card">
                    <div class="package-component-media">
                        <img src="${escapeHtml(getPackageComponentImage(component, packageKey))}" alt="${escapeHtml(component.name)}">
                    </div>
                    <div class="package-component-copy">
                        <h4>${escapeHtml(component.name)} <span>${escapeHtml(component.badge)}</span></h4>
                        <p>${escapeHtml(component.description)}</p>
                    </div>
                </article>
            `).join('')}
            <article class="package-component-card package-ajax-system-card">
                <div class="package-component-media">
                    <img src="img/Paquetes/ajax3.webp" alt="${escapeHtml(t('packages.ajaxCard.title'))}">
                </div>
                <div class="package-component-copy">
                    <h4>${escapeHtml(t('packages.ajaxCard.title'))} <span>${escapeHtml(t('packages.ajaxCard.badge'))}</span></h4>
                    <p>${escapeHtml(t('packages.ajaxCard.description'))}</p>
                </div>
            </article>
        </div>
    `;

    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function renderPackageGallerySlide() {
    const modalBody = document.getElementById('modalBody');
    const modalTitle = document.getElementById('modalTitle');
    const packageData = packageModalData[packageGalleryState.packageKey];
    if (!modalBody || !modalTitle || !packageData) return;

    const total = packageData.components.length;
    const currentIndex = ((packageGalleryState.index % total) + total) % total;
    packageGalleryState.index = currentIndex;
    const component = packageData.components[currentIndex];
    const componentImage = getPackageComponentImage(component, packageGalleryState.packageKey);
    const imageClass = /\.avif$/i.test(componentImage) ? 'package-gallery-img compact-render' : 'package-gallery-img';

    modalTitle.textContent = t('packages.galleryTitle', { title: packageData.title });
    modalBody.innerHTML = `
        <div class="package-gallery">
            <div class="package-gallery-stage">
                <img class="${imageClass}" src="${escapeHtml(componentImage)}" alt="${escapeHtml(component.name)}" loading="eager" decoding="async" fetchpriority="high" draggable="false">
            </div>
            <div class="package-gallery-info">
                <div class="package-gallery-count">${escapeHtml(t('gallery.count', { current: currentIndex + 1, total }))}</div>
                <h4>${escapeHtml(component.name)}</h4>
                <span class="package-gallery-badge">${escapeHtml(component.badge)}</span>
                <p>${escapeHtml(component.description)}</p>
                <div class="package-gallery-controls">
                    <button type="button" class="package-gallery-control" onclick="changePackageGallerySlide(-1)" aria-label="${escapeHtml(t('gallery.prev'))}">‹</button>
                    <button type="button" class="package-gallery-control" onclick="changePackageGallerySlide(1)" aria-label="${escapeHtml(t('gallery.next'))}">›</button>
                    <div class="package-gallery-dots" aria-label="${escapeHtml(t('gallery.nav'))}">
                        ${packageData.components.map((_, index) => `
                            <button type="button" class="package-gallery-dot${index === currentIndex ? ' active' : ''}" onclick="goToPackageGallerySlide(${index})" aria-label="${escapeHtml(t('gallery.goTo', { index: index + 1 }))}"></button>
                        `).join('')}
                    </div>
                </div>
            </div>
        </div>
    `;

    preloadPackageGalleryNeighbors(packageData, currentIndex);
}

function preloadPackageGalleryImage(src) {
    if (!src) return;
    const image = new Image();
    image.decoding = 'async';
    image.src = src;
    if (typeof image.decode === 'function') {
        image.decode().catch(() => {});
    }
}

function getPackageComponentImage(component, packageKey) {
    if (!component) return '';
    const color = getPackageColor(packageKey);
    return color === 'white' && component.imageWhite ? component.imageWhite : component.image;
}

function preloadPackageGalleryNeighbors(packageData, currentIndex) {
    if (!packageData || !Array.isArray(packageData.components)) return;
    const total = packageData.components.length;
    [currentIndex, currentIndex + 1, currentIndex - 1].forEach((index) => {
        const component = packageData.components[((index % total) + total) % total];
        if (component) preloadPackageGalleryImage(getPackageComponentImage(component, packageGalleryState.packageKey));
    });
}

function preloadPackageGallery(packageData, packageKey) {
    if (!packageData || !Array.isArray(packageData.components)) return;
    packageData.components.forEach((component) => {
        preloadPackageGalleryImage(getPackageComponentImage(component, packageKey));
        if (component.imageWhite) preloadPackageGalleryImage(component.imageWhite);
    });
}

function findPackageKeyFromSwatch(swatch) {
    const group = swatch.closest('.color-swatches');
    if (group && group.dataset.packageKey) return group.dataset.packageKey;
    const card = swatch.closest('.package-card');
    if (card && card.dataset.packageKey) return card.dataset.packageKey;
    const modal = swatch.closest('#serviceModal');
    if (modal && modal.dataset.packageKey) return modal.dataset.packageKey;
    return null;
}

function setSwatchState(group, color) {
    if (!group) return;
    group.querySelectorAll('.color-swatch').forEach((item) => {
        const isActive = (item.dataset.colorValue || 'white') === color;
        item.classList.toggle('active', isActive);
        item.setAttribute('aria-pressed', String(isActive));
    });
}

function getPackageColorImage(card, packageKey, color) {
    if (card && color === 'white' && card.dataset.imageWhite) return card.dataset.imageWhite;
    if (card && color === 'black' && card.dataset.imageBlack) return card.dataset.imageBlack;
    return packageColorImages[packageKey] ? packageColorImages[packageKey][color] : null;
}

function updatePackageCardImage(card, packageKey, color) {
    const image = card ? card.querySelector('[data-package-image], .package-img-wrap > img') : null;
    const colorImage = getPackageColorImage(card, packageKey, color);
    if (!image || !colorImage) return;

    image.src = colorImage;
    image.classList.toggle('package-image-white', color === 'white');
}

function applyPackageColor(packageKey, color) {
    if (!packageKey) return;
    const nextColor = color === 'black' ? 'black' : 'white';
    packageColorState[packageKey] = nextColor;

    document.querySelectorAll(`.package-card[data-package-key="${packageKey}"]`).forEach((card) => {
        updatePackageCardImage(card, packageKey, nextColor);
        setSwatchState(card.querySelector('.color-swatches'), nextColor);
    });

    const modal = document.getElementById('serviceModal');
    if (modal && modal.dataset.packageKey === packageKey) {
        setSwatchState(modal.querySelector('.color-swatches'), nextColor);
        if (modal.classList.contains('package-gallery-modal')) {
            renderPackageGallerySlide();
        } else if (modal.classList.contains('package-modal')) {
            openPackageModal(packageKey);
        }
    }
}

window.applyPackageColor = applyPackageColor;

function openBranchCard(branchKey) {
    const branch = branchLocations[branchKey];
    const card = document.querySelector('.branch-card');
    if (!branch || !card) return;

    const title = card.querySelector('h3');
    const address = card.querySelector('p');
    const link = card.querySelector('a');
    if (title) title.textContent = branch.title;
    if (address) address.textContent = branch.address;
    if (link) {
        link.href = branch.mapUrl;
        link.textContent = 'Ver ficha en Maps';
    }

    card.hidden = false;
    card.dataset.activeBranch = branchKey;
    document.querySelectorAll('.branch-marker').forEach((marker) => {
        const isActive = marker.dataset.branch === branchKey;
        marker.classList.toggle('active', isActive);
        marker.setAttribute('aria-expanded', String(isActive));
    });
}

function closeBranchCard() {
    const card = document.querySelector('.branch-card');
    if (!card) return;
    card.hidden = true;
    document.querySelectorAll('.branch-marker').forEach((marker) => {
        marker.classList.remove('active');
        marker.setAttribute('aria-expanded', 'false');
    });
}

function openPackageGallery(packageKey) {
    const packageData = packageModalData[packageKey];
    const modal = document.getElementById('serviceModal');
    const modalImage = document.getElementById('modalImage');
    if (!packageData || !modal || !modalImage) return;

    packageGalleryState = { packageKey, index: 0 };
    preloadPackageGallery(packageData, packageKey);
    modal.classList.remove('package-modal');
    modal.classList.add('package-gallery-modal');
    modal.dataset.packageKey = packageKey;
    modalImage.src = '';
    modalImage.alt = '';
    modalImage.style.display = 'none';
    renderPackageGallerySlide();

    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function changePackageGallerySlide(direction) {
    packageGalleryState.index += direction;
    renderPackageGallerySlide();
}

function goToPackageGallerySlide(index) {
    packageGalleryState.index = index;
    renderPackageGallerySlide();
}

function closeServiceModal() {
    const modal = document.getElementById('serviceModal');
    if (!modal) return;
    modal.classList.remove('package-modal');
    modal.classList.remove('package-gallery-modal');
    delete modal.dataset.packageKey;
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
    const modal = document.getElementById('serviceModal');
    if (modal && modal.classList.contains('package-gallery-modal') && modal.style.display === 'block') {
        if (e.key === 'ArrowLeft') changePackageGallerySlide(-1);
        if (e.key === 'ArrowRight') changePackageGallerySlide(1);
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

    document.addEventListener('click', (event) => {
        const branchMarker = event.target.closest('.branch-marker');
        if (branchMarker && branchMarker.dataset.branch) {
            event.stopPropagation();
            openBranchCard(branchMarker.dataset.branch);
            return;
        }

        if (event.target.closest('.branch-card-close')) {
            closeBranchCard();
            return;
        }

        const swatch = event.target.closest('.color-swatch');
        if (!swatch || swatch.disabled) return;
        const group = swatch.closest('.color-swatches');
        if (!group) return;
        const packageKey = findPackageKeyFromSwatch(swatch);
        const color = swatch.dataset.colorValue || (swatch.classList.contains('color-swatch-black') ? 'black' : 'white');
        applyPackageColor(packageKey, color);
    });

    applyTranslations(currentLanguage);
    Object.keys(packageColorState).forEach((packageKey) => applyPackageColor(packageKey, getPackageColor(packageKey)));

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
let currentWordIndex = 0;
const word2Element = document.getElementById('word2');

function changeWord() {
    if (!word2Element) return;
    const words = t('hero.words');
    if (!Array.isArray(words) || words.length === 0) return;
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

applyTranslations(currentLanguage);
