# Página Web Empresarial

Una página web empresarial moderna y responsive construida con HTML, CSS y JavaScript vanilla.

## 🚀 Características

- **Diseño Responsive**: Se adapta perfectamente a todos los dispositivos
- **Navegación Suave**: Scroll automático a las secciones con animaciones
- **Menú Móvil**: Navegación optimizada para dispositivos móviles
- **Formulario de Contacto**: Con validación en tiempo real
- **Animaciones**: Efectos visuales al hacer scroll
- **Accesibilidad**: Cumple con estándares de accesibilidad web
- **Tema Oscuro/Claro**: Toggle opcional de tema
- **Optimizado para SEO**: Estructura semántica HTML5

## 📁 Estructura del Proyecto

```
Página Web Empresarial/
├── index.html          # Archivo HTML principal
├── styles.css          # Estilos CSS completos
├── script.js           # Funcionalidad JavaScript
├── README.md           # Este archivo
└── package.json        # Dependencias del proyecto (legacy)
```

## 🛠️ Tecnologías Utilizadas

- **HTML5**: Estructura semántica y accesible
- **CSS3**: Estilos modernos con variables CSS y Grid/Flexbox
- **JavaScript ES6+**: Funcionalidad interactiva sin dependencias
- **Responsive Design**: Mobile-first approach
- **CSS Variables**: Sistema de diseño consistente
- **Intersection Observer API**: Animaciones al hacer scroll

## 🎨 Secciones Incluidas

1. **Header**: Navegación fija con logo y menú
2. **Nosotros**: Información de la empresa con características
3. **Certificaciones**: Credenciales y reconocimientos
4. **Soluciones**: Servicios principales ofrecidos
5. **Servicios**: Catálogo completo de servicios
6. **Socios**: Empresas colaboradoras
7. **Contacto**: Formulario e información de contacto
8. **Footer**: Enlaces y información adicional

## 🚀 Cómo Usar

### Opción 1: Abrir Directamente
1. Descarga todos los archivos
2. Abre `index.html` en tu navegador web
3. ¡Listo! La página funcionará completamente

### Opción 2: Servidor Local (Recomendado)
1. Instala un servidor local (ej: Live Server en VS Code)
2. Abre la carpeta del proyecto
3. Inicia el servidor local
4. Navega a la URL proporcionada

### Opción 3: Hosting Web
1. Sube todos los archivos a tu servidor web
2. Asegúrate de que `index.html` esté en la raíz
3. La página estará disponible en tu dominio

## 🎯 Funcionalidades JavaScript

### Navegación
- Scroll suave a secciones
- Menú móvil responsive
- Navegación por teclado (ESC, Enter)

### Formulario de Contacto
- Validación en tiempo real
- Mensajes de error personalizados
- Notificaciones de éxito/error
- Simulación de envío

### Animaciones
- Fade-in al hacer scroll
- Hover effects en tarjetas
- Transiciones suaves

### Accesibilidad
- Roles ARIA
- Skip links
- Navegación por teclado
- Contraste adecuado

## 🎨 Personalización

### Colores
Modifica las variables CSS en `styles.css`:

```css
:root {
    --primary-color: #3b82f6;    /* Color principal */
    --primary-hover: #2563eb;    /* Color hover */
    --accent-color: #f1f5f9;    /* Color de acento */
    --text-primary: #1e293b;    /* Texto principal */
    --text-secondary: #64748b;  /* Texto secundario */
}
```

### Contenido
- Edita el texto en `index.html`
- Cambia las imágenes por URLs de tus propias imágenes
- Modifica los servicios y soluciones según tu negocio

### Funcionalidades
- Añade nuevas secciones en `index.html`
- Modifica la lógica en `script.js`
- Personaliza estilos en `styles.css`

## 📱 Responsive Breakpoints

- **Mobile**: < 640px
- **Tablet**: 640px - 1024px
- **Desktop**: > 1024px

## 🌐 Compatibilidad de Navegadores

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+
- Internet Explorer 11+ (con polyfills)

## 🔧 Funciones JavaScript Disponibles

```javascript
// Navegación
WebApp.scrollToSection('seccion-id');

// Notificaciones
WebApp.showNotification('Mensaje', 'success|error|info|warning');

// Menú móvil
WebApp.toggleMobileMenu();
WebApp.closeMobileMenu();
```

## 📝 Notas de Desarrollo

### Conversión de React a Vanilla
Este proyecto fue convertido desde una aplicación React/TypeScript a HTML/CSS/JavaScript vanilla para:

- **Mejor rendimiento**: Sin overhead de framework
- **Fácil despliegue**: Solo archivos estáticos
- **Mantenimiento simple**: Código directo y comprensible
- **Hosting universal**: Funciona en cualquier servidor web

### Características Mantenidas
- Diseño visual idéntico
- Funcionalidad completa
- Responsive design
- Accesibilidad
- Animaciones

## 🤝 Contribuciones

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 📞 Soporte

Si tienes preguntas o necesitas ayuda:

- Abre un issue en GitHub
- Revisa la documentación del código
- Contacta al equipo de desarrollo

## 🎉 Agradecimientos

- Diseño original inspirado en mejores prácticas de UX/UI
- Iconos SVG de Heroicons
- Imágenes de Unsplash
- Comunidad de desarrolladores web

---

**¡Disfruta usando tu nueva página web empresarial!** 🚀  