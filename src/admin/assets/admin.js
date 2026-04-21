/**
 * Admin Panel - Global JavaScript
 * Funciones y componentes globales para el panel de administración
 */

// ============================================
// TOAST NOTIFICATIONS
// ============================================

/**
 * Muestra una notificación toast
 * @param {string} message - Mensaje a mostrar
 * @param {string} type - Tipo: 'success', 'error', 'warning', 'info'
 * @param {number} duration - Duración en ms (default: 3000)
 */
function showToast(message, type = 'info', duration = 3000) {
    // Crear contenedor de toasts si no existe
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    // Crear toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;

    // Icono según tipo
    const icons = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ'
    };

    toast.innerHTML = `
        <div class="toast-icon">${icons[type] || icons.info}</div>
        <div class="toast-content">
            <p class="toast-message">${escapeHtml(message)}</p>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">×</button>
    `;

    container.appendChild(toast);

    // Animación de entrada
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);

    // Auto-remove
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();

            // Limpiar contenedor si está vacío
            if (container.children.length === 0) {
                container.remove();
            }
        }, 300);
    }, duration);
}

// Hacer disponible globalmente
window.showToast = showToast;

// ============================================
// MODAL UTILITIES
// ============================================

/**
 * Abre un modal
 * @param {string} modalId - ID del modal
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';

        // Focus en primer input
        const firstInput = modal.querySelector('input, textarea, select');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }
}

/**
 * Cierra un modal
 * @param {string} modalId - ID del modal
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

/**
 * Cierra modal al presionar Escape
 */
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const activeModal = document.querySelector('.modal.active');
        if (activeModal) {
            activeModal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }
});

// Hacer disponibles globalmente
window.openModal = openModal;
window.closeModal = closeModal;

// ============================================
// CONFIRMATION DIALOG
// ============================================

/**
 * Muestra un diálogo de confirmación
 * @param {Object} options - Opciones del diálogo
 * @returns {Promise<boolean>}
 */
function confirmDialog(options = {}) {
    return new Promise((resolve) => {
        const {
            title = '¿Estás seguro?',
            message = 'Esta acción no se puede deshacer',
            confirmText = 'Confirmar',
            cancelText = 'Cancelar',
            type = 'warning' // 'warning', 'danger', 'success'
        } = options;

        // Crear modal
        const modalId = 'confirmDialog_' + Date.now();
        const modal = document.createElement('div');
        modal.id = modalId;
        modal.className = `modal modal-small modal-confirm modal-${type}`;

        const icons = {
            warning: '⚠️',
            danger: '🗑️',
            success: '✓'
        };

        modal.innerHTML = `
            <div class="modal-overlay"></div>
            <div class="modal-container">
                <div class="modal-header">
                    <h3>${escapeHtml(title)}</h3>
                </div>
                <div class="modal-body">
                    <div class="modal-icon">${icons[type] || icons.warning}</div>
                    <p>${escapeHtml(message)}</p>
                </div>
                <div class="modal-footer">
                    <button class="btn-secondary" data-action="cancel">
                        ${escapeHtml(cancelText)}
                    </button>
                    <button class="btn-danger" data-action="confirm">
                        ${escapeHtml(confirmText)}
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Event listeners
        const handleClick = (e) => {
            const action = e.target.dataset.action;
            if (action) {
                modal.classList.remove('active');
                setTimeout(() => {
                    modal.remove();
                    resolve(action === 'confirm');
                }, 300);
            }
        };

        modal.querySelector('.modal-overlay').addEventListener('click', () => {
            modal.classList.remove('active');
            setTimeout(() => {
                modal.remove();
                resolve(false);
            }, 300);
        });

        modal.querySelectorAll('[data-action]').forEach(btn => {
            btn.addEventListener('click', handleClick);
        });

        // Mostrar modal
        setTimeout(() => modal.classList.add('active'), 10);
    });
}

window.confirmDialog = confirmDialog;

// ============================================
// FORM VALIDATION
// ============================================

/**
 * Valida un formulario
 * @param {HTMLFormElement} form - Formulario a validar
 * @returns {boolean}
 */
function validateForm(form) {
    let isValid = true;
    const fields = form.querySelectorAll('input, textarea, select');

    fields.forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });

    return isValid;
}

/**
 * Valida un campo individual
 * @param {HTMLElement} field - Campo a validar
 * @returns {boolean}
 */
function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';

    // Required
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'Este campo es requerido';
    }

    // Email
    if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            errorMessage = 'Email inválido';
        }
    }

    // URL
    if (field.type === 'url' && value) {
        try {
            new URL(value);
        } catch {
            isValid = false;
            errorMessage = 'URL inválida';
        }
    }

    // Min length
    if (field.minLength && value.length < field.minLength) {
        isValid = false;
        errorMessage = `Mínimo ${field.minLength} caracteres`;
    }

    // Max length
    if (field.maxLength && value.length > field.maxLength) {
        isValid = false;
        errorMessage = `Máximo ${field.maxLength} caracteres`;
    }

    // Pattern
    if (field.pattern && value) {
        const regex = new RegExp(field.pattern);
        if (!regex.test(value)) {
            isValid = false;
            errorMessage = field.title || 'Formato inválido';
        }
    }

    showFieldError(field, isValid, errorMessage);
    return isValid;
}

/**
 * Muestra/oculta error de campo
 * @param {HTMLElement} field - Campo
 * @param {boolean} isValid - Si es válido
 * @param {string} message - Mensaje de error
 */
function showFieldError(field, isValid, message) {
    // Limpiar estado anterior
    field.classList.remove('error', 'valid');

    // Eliminar mensaje de error anterior
    const existingError = field.parentNode.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }

    if (isValid) {
        field.classList.add('valid');
    } else {
        field.classList.add('error');

        if (message) {
            const errorEl = document.createElement('span');
            errorEl.className = 'error-message';
            errorEl.textContent = message;
            field.parentNode.appendChild(errorEl);
        }
    }
}

// Hacer disponibles globalmente
window.validateForm = validateForm;
window.validateField = validateField;

// ============================================
// SLUG GENERATOR
// ============================================

/**
 * Genera un slug desde un texto
 * @param {string} text - Texto a convertir
 * @returns {string}
 */
function generateSlug(text) {
    return text
        .toLowerCase()
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '') // Remover acentos
        .replace(/[^a-z0-9]+/g, '-')      // Reemplazar no alfanuméricos con -
        .replace(/^-+|-+$/g, '');         // Remover - al inicio y final
}

window.generateSlug = generateSlug;

// ============================================
// DATE FORMATTERS
// ============================================

/**
 * Formatea una fecha
 * @param {string|Date} date - Fecha
 * @param {Object} options - Opciones de formato
 * @returns {string}
 */
function formatDate(date, options = {}) {
    const d = typeof date === 'string' ? new Date(date) : date;

    const defaultOptions = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        ...options
    };

    return d.toLocaleDateString('es-MX', defaultOptions);
}

/**
 * Formatea fecha y hora
 * @param {string|Date} date - Fecha
 * @returns {string}
 */
function formatDateTime(date) {
    const d = typeof date === 'string' ? new Date(date) : date;

    return d.toLocaleDateString('es-MX', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Formatea fecha relativa (hace X tiempo)
 * @param {string|Date} date - Fecha
 * @returns {string}
 */
function formatRelativeDate(date) {
    const d = typeof date === 'string' ? new Date(date) : date;
    const now = new Date();
    const diff = now - d;

    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);

    if (seconds < 60) return 'Hace un momento';
    if (minutes < 60) return `Hace ${minutes} minuto${minutes > 1 ? 's' : ''}`;
    if (hours < 24) return `Hace ${hours} hora${hours > 1 ? 's' : ''}`;
    if (days < 7) return `Hace ${days} día${days > 1 ? 's' : ''}`;

    return formatDate(d);
}

window.formatDate = formatDate;
window.formatDateTime = formatDateTime;
window.formatRelativeDate = formatRelativeDate;

// ============================================
// ESCAPE HTML
// ============================================

/**
 * Escapa HTML para prevenir XSS
 * @param {string} text - Texto a escapar
 * @returns {string}
 */
function escapeHtml(text) {
    if (typeof text !== 'string') return '';

    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

window.escapeHtml = escapeHtml;

// ============================================
// DEBOUNCE
// ============================================

/**
 * Crea una función debounced
 * @param {Function} func - Función a debounce
 * @param {number} wait - Milisegundos de espera
 * @returns {Function}
 */
function debounce(func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

window.debounce = debounce;

// ============================================
// COPY TO CLIPBOARD
// ============================================

/**
 * Copia texto al clipboard
 * @param {string} text - Texto a copiar
 * @returns {Promise<boolean>}
 */
async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        showToast('Copiado al portapapeles', 'success', 2000);
        return true;
    } catch (err) {
        showToast('Error al copiar', 'error');
        return false;
    }
}

window.copyToClipboard = copyToClipboard;

// ============================================
// LOADING STATE
// ============================================

/**
 * Muestra/oculta spinner en un botón
 * @param {HTMLButtonElement} button - Botón
 * @param {boolean} loading - Si está cargando
 */
function setButtonLoading(button, loading) {
    if (loading) {
        button.dataset.originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="spinner"></span> Cargando...';
    } else {
        button.disabled = false;
        button.innerHTML = button.dataset.originalText || button.innerHTML;
    }
}

window.setButtonLoading = setButtonLoading;

// ============================================
// FILE SIZE FORMATTER
// ============================================

/**
 * Formatea tamaño de archivo
 * @param {number} bytes - Bytes
 * @returns {string}
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

window.formatFileSize = formatFileSize;

// ============================================
// NUMBER FORMATTER
// ============================================

/**
 * Formatea número con separadores
 * @param {number} num - Número
 * @returns {string}
 */
function formatNumber(num) {
    return new Intl.NumberFormat('es-MX').format(num);
}

window.formatNumber = formatNumber;

// ============================================
// INITIALIZE
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    console.log('Admin Panel initialized');

    // Agregar validación en tiempo real a todos los formularios
    document.querySelectorAll('form').forEach(form => {
        form.querySelectorAll('input, textarea, select').forEach(field => {
            field.addEventListener('blur', () => validateField(field));
        });
    });
});

// ============================================
// CSS PARA TOASTS (inyectar en el DOM)
// ============================================

const toastStyles = document.createElement('style');
toastStyles.textContent = `
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10000;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.toast {
    display: flex;
    align-items: center;
    gap: 1rem;
    min-width: 300px;
    max-width: 500px;
    padding: 1rem 1.25rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
                0 2px 4px -1px rgba(0, 0, 0, 0.06);
    opacity: 0;
    transform: translateX(400px);
    transition: all 0.3s ease;
}

.toast.show {
    opacity: 1;
    transform: translateX(0);
}

.toast-icon {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 0.875rem;
    font-weight: bold;
    flex-shrink: 0;
}

.toast-success .toast-icon {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.toast-error .toast-icon {
    background: rgba(220, 38, 38, 0.1);
    color: #dc2626;
}

.toast-warning .toast-icon {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
}

.toast-info .toast-icon {
    background: rgba(37, 99, 235, 0.1);
    color: #2563eb;
}

.toast-content {
    flex: 1;
}

.toast-message {
    margin: 0;
    color: var(--gray-900);
    font-size: 0.875rem;
    line-height: 1.4;
}

.toast-close {
    background: none;
    border: none;
    color: var(--gray-400);
    cursor: pointer;
    font-size: 1.25rem;
    line-height: 1;
    padding: 0;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.2s;
    flex-shrink: 0;
}

.toast-close:hover {
    background: var(--gray-100);
    color: var(--gray-700);
}

@media (max-width: 640px) {
    .toast-container {
        left: 20px;
        right: 20px;
    }

    .toast {
        min-width: auto;
        width: 100%;
    }
}
`;

document.head.appendChild(toastStyles);
