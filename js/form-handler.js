// Manejo de formularios de contacto
class FormHandler {
    constructor(formId) {
        this.form = document.getElementById(formId);
        if (!this.form) return;
        this.submitButton = this.form.querySelector('.btn-submit');
        this.originalButtonText = this.submitButton ? this.submitButton.textContent : '';
        this.cacheKey = `cdaFormDraft:${window.location.pathname}:${formId}`;

        this.init();
    }

    init() {
        if (!this.form) return;
        this.restoreCachedFields();

        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleSubmit();
        });

        this.getCacheableFields().forEach(field => {
            field.addEventListener('input', () => {
                this.clearFieldError(field);
                this.cacheFields();
            });

            field.addEventListener('change', () => {
                this.clearFieldError(field);
                this.cacheFields();
            });
        });
    }

    async handleSubmit() {
        // Validar formulario
        if (!this.validateForm() || !this.validateRecaptcha()) {
            return;
        }

        // Deshabilitar botón y mostrar loading
        this.setLoading(true);

        // Obtener datos del formulario
        const formData = new FormData(this.form);

        try {
            const response = await fetch(this.form.action, {
                method: 'POST',
                body: formData
            });
            const responseText = await response.text();
            let data = null;

            try {
                data = JSON.parse(responseText);
            } catch (error) {
                throw new Error('Respuesta inválida del servidor');
            }

            this.setLoading(false);

            if (!response.ok) {
                this.resetRecaptcha();
                this.showError(data.mensaje || 'Hubo un error al enviar el mensaje. Por favor, inténtalo más tarde.');
                return;
            }

            if (data.exito) {
                this.showSuccess(data.mensaje);
                this.form.reset();
                this.clearCachedFields();
                this.resetFileLabels();
                this.resetRecaptcha();
            } else {
                this.resetRecaptcha();
                this.showError(data.mensaje || 'Hubo un error al enviar el mensaje. Por favor, inténtalo más tarde.');
            }
        } catch (error) {
            this.setLoading(false);
            this.showError('Hubo un error al enviar el mensaje. Por favor, inténtalo más tarde.');
        }
    }

    getCacheableFields() {
        return Array.from(this.form.querySelectorAll('input, select, textarea')).filter(field => {
            if (!field.name || field.disabled) return false;
            if (field.classList.contains('ghost')) return false;

            const type = (field.type || '').toLowerCase();
            return !['button', 'file', 'hidden', 'password', 'reset', 'submit'].includes(type);
        });
    }

    restoreCachedFields() {
        let cachedValues = null;

        try {
            cachedValues = JSON.parse(localStorage.getItem(this.cacheKey) || '{}');
        } catch (error) {
            this.clearCachedFields();
            return;
        }

        this.getCacheableFields().forEach(field => {
            if (!Object.prototype.hasOwnProperty.call(cachedValues, field.name)) return;

            if (field.type === 'checkbox' || field.type === 'radio') {
                field.checked = cachedValues[field.name] === field.value;
            } else {
                field.value = cachedValues[field.name];
            }

            field.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    cacheFields() {
        const values = {};

        this.getCacheableFields().forEach(field => {
            if (field.type === 'checkbox' || field.type === 'radio') {
                if (field.checked) values[field.name] = field.value;
                return;
            }

            values[field.name] = field.value;
        });

        try {
            localStorage.setItem(this.cacheKey, JSON.stringify(values));
        } catch (error) {
            this.clearCachedFields();
        }
    }

    clearCachedFields() {
        try {
            localStorage.removeItem(this.cacheKey);
        } catch (error) {
            // Si el navegador bloquea storage, el formulario debe seguir funcionando.
        }
    }

    resetFileLabels() {
        this.form.querySelectorAll('.file-upload-text').forEach(label => {
            label.textContent = 'Adjuntar CV en PDF, DOC o DOCX';
        });
    }

    validateForm() {
        let isValid = true;
        const requiredFields = this.form.querySelectorAll('[required]');

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                this.showFieldError(field, 'Este campo es obligatorio');
                isValid = false;
            } else if (field.type === 'email' && !this.isValidEmail(field.value)) {
                this.showFieldError(field, 'Introduce un correo válido');
                isValid = false;
            } else {
                this.clearFieldError(field);
            }
        });

        return isValid;
    }

    validateRecaptcha() {
        if (!this.form.querySelector('.g-recaptcha')) {
            return true;
        }

        const response = this.form.querySelector('[name="g-recaptcha-response"]');
        if (response && response.value.trim()) {
            return true;
        }

        this.showError('Por favor, verifica que no eres un robot.');
        return false;
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    showFieldError(field, message) {
        this.clearFieldError(field);
        field.classList.add('error');

        const errorElement = field.parentNode.querySelector('.error-message');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
    }

    clearFieldError(field) {
        field.classList.remove('error');
        const errorElement = field.parentNode.querySelector('.error-message');
        if (errorElement) {
            errorElement.style.display = 'none';
        }
    }

    setLoading(loading) {
        if (!this.submitButton) return;

        if (loading) {
            this.submitButton.disabled = true;
            this.submitButton.textContent = 'Enviando...';
            this.submitButton.style.opacity = '0.7';
        } else {
            this.submitButton.disabled = false;
            this.submitButton.textContent = this.originalButtonText;
            this.submitButton.style.opacity = '1';
        }
    }

    showSuccess(message) {
        this.showNotification(message, 'success');
    }

    showError(message) {
        this.showNotification(message, 'error');
    }

    resetRecaptcha() {
        if (typeof grecaptcha !== 'undefined') {
            grecaptcha.reset();
        }
    }

    showNotification(message, type) {
        // Crear elemento de notificación
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;

        // Estilos
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            max-width: 300px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        `;

        if (type === 'success') {
            notification.style.background = '#10b981';
        } else {
            notification.style.background = '#ef4444';
        }

        document.body.appendChild(notification);

        // Animar entrada
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);

        // Remover después de 5 segundos
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 5000);
    }
}

// Inicializar formularios cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Formulario de contacto principal
    new FormHandler('contact-form');

    // Formulario de empresa (si existe en la página)
    const empresaForm = document.getElementById('contact-form-empresa');
    if (empresaForm) {
        // Crear una nueva instancia para el formulario de empresa
        new FormHandler('contact-form-empresa');
    }

    document.querySelectorAll('.job-application-form').forEach((form) => {
        if (!form.id) return;
        new FormHandler(form.id);
    });
});

document.addEventListener('change', function(event) {
    const genderSelect = event.target.closest('.job-gender-select');
    if (genderSelect) {
        const form = genderSelect.closest('.job-application-form');
        const militaryField = form ? form.querySelector('.job-military-field') : null;
        const militarySelect = militaryField ? militaryField.querySelector('select') : null;
        const isMale = genderSelect.value === 'Hombre';

        if (militaryField && militarySelect) {
            militaryField.hidden = !isMale;
            militarySelect.required = isMale;
            if (!isMale) {
                militarySelect.value = '';
                militarySelect.classList.remove('error');
                const errorMessage = militaryField.querySelector('.error-message');
                if (errorMessage) errorMessage.style.display = 'none';
            }
        }
    }

    const fileInput = event.target.closest('.job-application-form input[type="file"]');
    if (!fileInput) return;

    const label = fileInput.closest('.file-upload');
    const labelText = label ? label.querySelector('.file-upload-text') : null;
    if (!labelText) return;

    labelText.textContent = fileInput.files.length ? fileInput.files[0].name : 'Adjuntar CV en PDF, DOC o DOCX';
});
