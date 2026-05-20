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

let packageGalleryState = {
    packageKey: null,
    index: 0
};

const translations = {
    es: {
        'nav.services': 'Servicios',
        'nav.packages': 'Paquetes',
        'nav.about': 'Nosotros',
        'nav.contact': 'Contacto',
        'nav.news': 'News',
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
        'packages.included.apps': 'Apps incluidas',
        'packages.included.installation': 'Instalación gratuita',
        'packages.included.colors': 'Disponible en blanco o negro',
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
        'packages.included.apps': 'Apps included',
        'packages.included.installation': 'Free installation',
        'packages.included.colors': 'Available in white or black',
        'packages.colorAria': 'Available colors: white and black',
        'packages.colorWhiteAria': 'White color',
        'packages.colorBlackAria': 'Black color',
        'gallery.count': '{current} of {total}',
        'gallery.prev': 'View previous component',
        'gallery.next': 'View next component',
        'gallery.nav': 'Component navigation',
        'gallery.goTo': 'View component {index}'
    },
    fr: {
        'nav.services': 'Services',
        'nav.packages': 'Forfaits',
        'nav.about': 'A propos',
        'nav.contact': 'Contact',
        'nav.news': 'News',
        'nav.pay': 'Payer',
        'language.label': 'Langue',
        'hero.advice': 'Recevoir un conseil',
        'hero.viewPackages': 'Voir les forfaits',
        'hero.words': ['maison', 'commerce', 'entreprise'],
        'packages.eyebrow': 'Nos forfaits',
        'packages.title': 'Trouvez le plan ideal pour vous',
        'packages.subtitle': 'De la protection de base aux solutions corporatives completes.',
        'packages.availableColors': 'Disponible en',
        'packages.white': 'blanc',
        'packages.black': 'noir',
        'packages.viewPackage': 'Voir le forfait',
        'packages.galleryButton': 'Galerie',
        'packages.requestInfo': 'Demander plus d information',
        'packages.modalTitle': 'Composants du forfait {title}',
        'packages.galleryTitle': 'Galerie {title}',
        'packages.included.monitoring': 'Surveillance 24/7',
        'packages.included.apps': 'Applications incluses',
        'packages.included.installation': 'Installation gratuite',
        'packages.included.colors': 'Disponible en blanc ou noir',
        'packages.colorAria': 'Couleurs disponibles : blanc et noir',
        'packages.colorWhiteAria': 'Couleur blanche',
        'packages.colorBlackAria': 'Couleur noire',
        'gallery.count': '{current} sur {total}',
        'gallery.prev': 'Voir le composant precedent',
        'gallery.next': 'Voir le composant suivant',
        'gallery.nav': 'Navigation des composants',
        'gallery.goTo': 'Voir le composant {index}'
    },
    ja: {
        'nav.services': 'サービス',
        'nav.packages': 'パッケージ',
        'nav.about': '会社情報',
        'nav.contact': 'お問い合わせ',
        'nav.news': 'ニュース',
        'nav.pay': '支払い',
        'language.label': '言語',
        'hero.advice': '相談する',
        'hero.viewPackages': 'パッケージを見る',
        'hero.words': ['住まい', 'ビジネス', '企業'],
        'packages.eyebrow': 'パッケージ',
        'packages.title': '最適なプランを見つける',
        'packages.subtitle': '基本的な保護から総合的な法人向けソリューションまで。',
        'packages.availableColors': '利用可能カラー',
        'packages.white': '白',
        'packages.black': '黒',
        'packages.viewPackage': 'パッケージを見る',
        'packages.galleryButton': 'ギャラリー',
        'packages.requestInfo': '詳しい情報を依頼',
        'packages.modalTitle': '{title} パッケージの構成',
        'packages.galleryTitle': '{title} ギャラリー',
        'packages.included.monitoring': '24時間365日モニタリング',
        'packages.included.apps': 'アプリ込み',
        'packages.included.installation': '無料設置',
        'packages.included.colors': '白または黒を選択可能',
        'packages.colorAria': '利用可能カラー: 白と黒',
        'packages.colorWhiteAria': '白色',
        'packages.colorBlackAria': '黒色',
        'gallery.count': '{current} / {total}',
        'gallery.prev': '前のコンポーネントを見る',
        'gallery.next': '次のコンポーネントを見る',
        'gallery.nav': 'コンポーネントナビゲーション',
        'gallery.goTo': 'コンポーネント {index} を見る'
    },
    ko: {
        'nav.services': '서비스',
        'nav.packages': '패키지',
        'nav.about': '회사 소개',
        'nav.contact': '문의',
        'nav.news': '뉴스',
        'nav.pay': '결제',
        'language.label': '언어',
        'hero.advice': '상담 받기',
        'hero.viewPackages': '패키지 보기',
        'hero.words': ['가정', '비즈니스', '기업'],
        'packages.eyebrow': '패키지',
        'packages.title': '나에게 맞는 플랜 찾기',
        'packages.subtitle': '기본 보호부터 종합 기업 솔루션까지.',
        'packages.availableColors': '제공 색상',
        'packages.white': '흰색',
        'packages.black': '검정',
        'packages.viewPackage': '패키지 보기',
        'packages.galleryButton': '갤러리',
        'packages.requestInfo': '자세한 정보 요청',
        'packages.modalTitle': '{title} 패키지 구성품',
        'packages.galleryTitle': '{title} 갤러리',
        'packages.included.monitoring': '24시간 연중무휴 모니터링',
        'packages.included.apps': '앱 포함',
        'packages.included.installation': '무료 설치',
        'packages.included.colors': '흰색 또는 검정 제공',
        'packages.colorAria': '제공 색상: 흰색 및 검정',
        'packages.colorWhiteAria': '흰색',
        'packages.colorBlackAria': '검정색',
        'gallery.count': '{current} / {total}',
        'gallery.prev': '이전 구성품 보기',
        'gallery.next': '다음 구성품 보기',
        'gallery.nav': '구성품 탐색',
        'gallery.goTo': '구성품 {index} 보기'
    },
    zh: {
        'nav.services': '服务',
        'nav.packages': '套餐',
        'nav.about': '关于我们',
        'nav.contact': '联系',
        'nav.news': '新闻',
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
        languageSwitcherValue.textContent = currentLanguage.toUpperCase();
    }
}

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
                <span class="color-swatches" aria-label="${escapeHtml(t('packages.colorAria'))}">
                    <button type="button" class="color-swatch color-swatch-white active" data-package-color="${escapeHtml(t('packages.white'))}" aria-label="${escapeHtml(t('packages.colorWhiteAria'))}"></button>
                    <button type="button" class="color-swatch color-swatch-black" data-package-color="${escapeHtml(t('packages.black'))}" aria-label="${escapeHtml(t('packages.colorBlackAria'))}"></button>
                </span>
            </span>
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

function renderPackageGallerySlide() {
    const modalBody = document.getElementById('modalBody');
    const modalTitle = document.getElementById('modalTitle');
    const packageData = packageModalData[packageGalleryState.packageKey];
    if (!modalBody || !modalTitle || !packageData) return;

    const total = packageData.components.length;
    const currentIndex = ((packageGalleryState.index % total) + total) % total;
    packageGalleryState.index = currentIndex;
    const component = packageData.components[currentIndex];

    modalTitle.textContent = t('packages.galleryTitle', { title: packageData.title });
    modalBody.innerHTML = `
        <div class="package-gallery">
            <div class="package-gallery-stage">
                <img src="${escapeHtml(component.image)}" alt="${escapeHtml(component.name)}">
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
}

function openPackageGallery(packageKey) {
    const packageData = packageModalData[packageKey];
    const modal = document.getElementById('serviceModal');
    const modalImage = document.getElementById('modalImage');
    if (!packageData || !modal || !modalImage) return;

    packageGalleryState = { packageKey, index: 0 };
    modal.classList.remove('package-modal');
    modal.classList.add('package-gallery-modal');
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
        const swatch = event.target.closest('.color-swatch');
        if (!swatch) return;
        const group = swatch.closest('.color-swatches');
        if (!group) return;
        group.querySelectorAll('.color-swatch').forEach((item) => {
            const isActive = item === swatch;
            item.classList.toggle('active', isActive);
            item.setAttribute('aria-pressed', String(isActive));
        });
    });

    applyTranslations(currentLanguage);

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
