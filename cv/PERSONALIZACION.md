# 🎨 Guía de Personalización

Esta guía te ayudará a personalizar completamente tu página web empresarial según tus necesidades específicas.

## 🎯 Cambios Básicos

### 1. Información de la Empresa

#### Logo
```html
<!-- En index.html, línea ~30 -->
<div class="logo">
    <img src="URL_DE_TU_LOGO" alt="Logo de tu empresa" class="logo-image">
</div>
```

#### Nombre de la Empresa
```html
<!-- Cambiar en múltiples lugares -->
<h2 class="section-title">Tu Nombre de Empresa</h2>
<p class="section-subtitle">Tu descripción personalizada</p>
```

#### Información de Contacto
```html
<!-- En la sección de contacto -->
<li>Tu Dirección</li>
<li>Tu Ciudad, Código Postal</li>
<li>Tu Teléfono</li>
<li>tu@email.com</li>
```

### 2. Colores de la Marca

#### Variables CSS Principales
```css
/* En styles.css, líneas ~20-30 */
:root {
    --primary-color: #tu-color-principal;      /* Color principal de tu marca */
    --primary-hover: #tu-color-hover;          /* Color al hacer hover */
    --accent-color: #tu-color-accento;         /* Color de acento */
    --text-primary: #tu-color-texto;           /* Color de texto principal */
    --text-secondary: #tu-color-texto-sec;     /* Color de texto secundario */
}
```

#### Ejemplos de Paletas de Colores

**Azul Corporativo:**
```css
:root {
    --primary-color: #1e40af;
    --primary-hover: #1d4ed8;
    --accent-color: #dbeafe;
    --text-primary: #1e293b;
    --text-secondary: #475569;
}
```

**Verde Empresarial:**
```css
:root {
    --primary-color: #059669;
    --primary-hover: #047857;
    --accent-color: #d1fae5;
    --text-primary: #064e3b;
    --text-secondary: #065f46;
}
```

**Morado Creativo:**
```css
:root {
    --primary-color: #7c3aed;
    --primary-hover: #6d28d9;
    --accent-color: #ede9fe;
    --text-primary: #581c87;
    --text-secondary: #6b21a8;
}
```

## 🏢 Secciones Personalizables

### 1. Sección "Nosotros"

#### Historia de la Empresa
```html
<!-- En index.html, líneas ~80-90 -->
<h3 class="about-title">Tu Historia</h3>
<p class="about-description">
    Escribe aquí la historia de tu empresa, misión, visión y valores.
    Personaliza completamente este contenido según tu negocio.
</p>
```

#### Características Principales
```html
<!-- Modificar las características existentes -->
<div class="feature">
    <div class="feature-icon">
        <!-- Cambiar el SVG por tu icono -->
        <svg class="feature-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <!-- Tu icono personalizado -->
        </svg>
    </div>
    <div class="feature-content">
        <h4 class="feature-title">Tu Característica</h4>
        <p class="feature-description">Descripción de tu característica única.</p>
    </div>
</div>
```

### 2. Sección "Servicios"

#### Agregar Nuevos Servicios
```html
<!-- En index.html, líneas ~200-250 -->
<div class="service-card">
    <div class="service-icon">
        <svg class="service-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <!-- Icono SVG personalizado -->
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="..." />
        </svg>
    </div>
    <h3 class="service-title">Tu Nuevo Servicio</h3>
    <p class="service-description">Descripción detallada de tu servicio.</p>
</div>
```

#### Modificar Servicios Existentes
```html
<!-- Cambiar títulos y descripciones -->
<h3 class="service-title">Consultoría en Tu Área</h3>
<p class="service-description">Descripción personalizada de tu consultoría.</p>
```

### 3. Sección "Soluciones"

#### Agregar Nuevas Soluciones
```html
<!-- En index.html, líneas ~300-350 -->
<div class="solution-card">
    <div class="solution-icon">
        <svg class="solution-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <!-- Tu icono personalizado -->
        </svg>
    </div>
    <h3 class="solution-title">Tu Solución Innovadora</h3>
    <p class="solution-description">Explicación de cómo resuelves problemas específicos.</p>
</div>
```

### 4. Sección "Socios"

#### Agregar Tus Socios
```html
<!-- En index.html, líneas ~400-450 -->
<div class="partner-card">
    <div class="partner-logo">
        <!-- Logo de tu socio o icono representativo -->
        <svg class="partner-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <!-- Icono personalizado -->
        </svg>
    </div>
    <h3 class="partner-name">Nombre de Tu Socio</h3>
    <p class="partner-description">Descripción de la colaboración.</p>
</div>
```

## 🖼️ Imágenes y Multimedia

### 1. Cambiar Imágenes

#### Imagen Principal (Nosotros)
```html
<!-- En index.html, línea ~120 -->
<img src="URL_DE_TU_IMAGEN" alt="Descripción de tu imagen" class="about-img">
```

#### Imágenes de Servicios
```html
<!-- Agregar imágenes a las tarjetas de servicios -->
<div class="service-card">
    <img src="imagen-servicio.jpg" alt="Servicio" class="service-image">
    <!-- Resto del contenido -->
</div>
```

### 2. Agregar Estilos para Imágenes

```css
/* En styles.css */
.service-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: var(--radius);
    margin-bottom: 1rem;
}

.about-img {
    width: 100%;
    height: auto;
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    transition: var(--transition);
}

.about-img:hover {
    transform: scale(1.02);
}
```

## 📱 Personalización Responsive

### 1. Breakpoints Personalizados

```css
/* En styles.css, modificar los breakpoints existentes */
@media (min-width: 768px) {
    /* Tu breakpoint personalizado */
}

@media (min-width: 1024px) {
    /* Tu breakpoint personalizado */
}
```

### 2. Grids Personalizados

```css
/* Modificar el número de columnas en diferentes secciones */
.services-grid {
    display: grid;
    grid-template-columns: 1fr; /* 1 columna en móvil */
}

@media (min-width: 640px) {
    .services-grid {
        grid-template-columns: repeat(2, 1fr); /* 2 columnas en tablet */
    }
}

@media (min-width: 1024px) {
    .services-grid {
        grid-template-columns: repeat(4, 1fr); /* 4 columnas en desktop */
    }
}
```

## 🎭 Animaciones Personalizadas

### 1. Nuevas Animaciones CSS

```css
/* En styles.css */
@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-50px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes bounceIn {
    0% {
        opacity: 0;
        transform: scale(0.3);
    }
    50% {
        opacity: 1;
        transform: scale(1.05);
    }
    70% {
        transform: scale(0.9);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}

.slide-in-left {
    animation: slideInLeft 0.8s ease-out;
}

.bounce-in {
    animation: bounceIn 1s ease-out;
}
```

### 2. Animaciones JavaScript

```javascript
// En script.js, agregar nuevas funciones de animación
function initCustomAnimations() {
    const observerOptions = {
        threshold: 0.2,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('bounce-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Observar elementos específicos
    document.querySelectorAll('.tu-clase-personalizada').forEach(element => {
        observer.observe(element);
    });
}
```

## 🔧 Funcionalidades JavaScript Personalizadas

### 1. Agregar Nuevas Funciones

```javascript
// En script.js
function tuFuncionPersonalizada() {
    // Tu lógica personalizada
    console.log('Función personalizada ejecutada');
}

// Agregar a la API global
window.WebApp.tuFuncion = tuFuncionPersonalizada;
```

### 2. Modificar Validaciones del Formulario

```javascript
// En script.js, modificar la función validateField
function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';
    
    // Tu validación personalizada
    if (field.id === 'telefono') {
        const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
        if (!phoneRegex.test(value)) {
            errorMessage = 'Ingrese un número de teléfono válido';
            isValid = false;
        }
    }
    
    // Resto de la lógica...
    return isValid;
}
```

## 📊 SEO y Metadatos

### 1. Metadatos HTML

```html
<!-- En index.html, sección head -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu Empresa - Tu Descripción</title>
    <meta name="description" content="Descripción SEO de tu empresa y servicios">
    <meta name="keywords" content="palabra clave 1, palabra clave 2, palabra clave 3">
    <meta name="author" content="Tu Nombre">
    
    <!-- Open Graph para redes sociales -->
    <meta property="og:title" content="Tu Empresa - Tu Descripción">
    <meta property="og:description" content="Descripción para redes sociales">
    <meta property="og:image" content="URL_DE_TU_IMAGEN_OG">
    <meta property="og:url" content="URL_DE_TU_SITIO">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Tu Empresa - Tu Descripción">
    <meta name="twitter:description" content="Descripción para Twitter">
    <meta name="twitter:image" content="URL_DE_TU_IMAGEN_TWITTER">
</head>
```

### 2. Estructura Semántica

```html
<!-- Usar etiquetas semánticas apropiadas -->
<main class="main-content" role="main">
    <section class="section about-section" aria-labelledby="about-title">
        <h2 id="about-title" class="section-title">Nosotros</h2>
        <!-- Contenido -->
    </section>
</main>
```

## 🚀 Optimización de Rendimiento

### 1. Lazy Loading de Imágenes

```html
<!-- Usar data-src para lazy loading -->
<img data-src="tu-imagen.jpg" alt="Descripción" class="lazy">
```

### 2. Minificación (Opcional)

```bash
# Instalar herramientas de minificación
npm install -g clean-css-cli uglify-js html-minifier

# Minificar CSS
cleancss -o styles.min.css styles.css

# Minificar JavaScript
uglifyjs script.js -o script.min.js

# Minificar HTML
html-minifier --collapse-whitespace --remove-comments --remove-optional-tags --remove-redundant-attributes --remove-script-type-attributes --remove-tag-whitespace --use-short-doctype --minify-css true --minify-js true index.html -o index.min.html
```

## 🎨 Temas Personalizados

### 1. Tema Oscuro Personalizado

```css
/* En styles.css */
.dark-theme {
    --primary-color: #60a5fa;
    --primary-hover: #3b82f6;
    --accent-color: #1f2937;
    --text-primary: #f9fafb;
    --text-secondary: #d1d5db;
    --background: #111827;
    --card-background: #1f2937;
}

.dark-theme .header {
    background-color: #1f2937;
    border-bottom: 1px solid #374151;
}

.dark-theme .service-card {
    background-color: #1f2937;
    border: 1px solid #374151;
}
```

### 2. Múltiples Temas

```javascript
// En script.js
function initMultipleThemes() {
    const themes = {
        default: {
            '--primary-color': '#3b82f6',
            '--accent-color': '#f1f5f9'
        },
        corporate: {
            '--primary-color': '#059669',
            '--accent-color': '#d1fae5'
        },
        creative: {
            '--primary-color': '#7c3aed',
            '--accent-color': '#ede9fe'
        }
    };
    
    function applyTheme(themeName) {
        const theme = themes[themeName];
        Object.entries(theme).forEach(([property, value]) => {
            document.documentElement.style.setProperty(property, value);
        });
        localStorage.setItem('selectedTheme', themeName);
    }
    
    // Botones de cambio de tema
    document.querySelectorAll('[data-theme]').forEach(button => {
        button.addEventListener('click', () => {
            applyTheme(button.dataset.theme);
        });
    });
}
```

## 📝 Checklist de Personalización

- [ ] Cambiar logo y nombre de la empresa
- [ ] Personalizar colores de marca
- [ ] Modificar contenido de secciones
- [ ] Agregar/quitar servicios según necesidades
- [ ] Cambiar imágenes por propias
- [ ] Personalizar formulario de contacto
- [ ] Agregar metadatos SEO
- [ ] Configurar Google Analytics
- [ ] Probar en diferentes dispositivos
- [ ] Optimizar para velocidad
- [ ] Verificar accesibilidad

## 🆘 Solución de Problemas

### Imagen no se muestra
- Verificar que la URL sea correcta
- Asegurar que la imagen esté en formato web (JPG, PNG, WebP)
- Comprobar permisos de acceso a la imagen

### Estilos no se aplican
- Verificar que `styles.css` esté en la misma carpeta que `index.html`
- Limpiar caché del navegador
- Revisar consola del navegador para errores

### JavaScript no funciona
- Verificar que `script.js` esté en la misma carpeta
- Revisar consola del navegador para errores
- Asegurar que el archivo HTML tenga la referencia correcta

---

**¡Con esta guía tienes todo lo necesario para personalizar completamente tu página web empresarial!** 🎉
