/**
 * Login CAPTCHA Handler
 * Gestisce la verifica anti-bot per il form di login
 */

// === STATO ===
const CaptchaState = {
    verified: false,
    required: true,
    verificationDelay: 1200
};

// === SELETTORI DOM ===
const Selectors = {
    checkbox: '#custom-checkbox',
    spinner: '#spinner',
    header: '#captcha-header',
    container: '.captcha-container',
    checkboxArea: '.recaptcha-checkbox',
    form: 'form',
    hiddenInput: '#captcha-solved'
};

// === STILI ===
const Styles = {
    success: {
        borderColor: '#4caf50',
        background: '#c8e6c9'
    },
    error: {
        borderColor: '#f44336',
        background: '#ffebee'
    },
    default: {
        borderColor: '#dee2e6',
        background: '#f8f9fa'
    }
};

// === FUNZIONI UTILITY ===
const DOM = {
    get: (selector) => document.querySelector(selector),
    getId: (id) => document.getElementById(id)
};

const updateContainerStyle = (container, style) => {
    container.style.borderColor = style.borderColor;
    container.style.background = style.background;
};

// === VERIFICA CAPTCHA ===
const verifyCaptcha = () => {
    if (CaptchaState.verified) return;
    
    const checkbox = DOM.getId('custom-checkbox');
    const spinner = DOM.getId('spinner');
    const header = DOM.getId('captcha-header');
    const container = DOM.get(Selectors.container);
    
    if (!checkbox || !spinner || !header) return;
    
    // Mostra loading
    checkbox.classList.add('loading');
    spinner.style.display = 'block';
    
    // Simula verifica
    setTimeout(() => {
        spinner.style.display = 'none';
        checkbox.classList.remove('loading');
        checkbox.classList.add('verified');
        
        header.innerHTML = `
            <div class="recaptcha-success">
                <div class="success-icon">✓</div>
                <span class="recaptcha-text">Verifica completata</span>
            </div>
        `;
        
        const hiddenInput = DOM.getId('captcha-solved');
        if (hiddenInput) hiddenInput.value = '1';
        
        CaptchaState.verified = true;
        updateContainerStyle(container, Styles.success);
        
    }, CaptchaState.verificationDelay);
};

// === VALIDAZIONE FORM ===
const validateForm = (e) => {
    if (CaptchaState.verified) return true;
    
    e.preventDefault();
    
    const container = DOM.get(Selectors.container);
    if (!container) return false;
    
    // Mostra errore
    updateContainerStyle(container, Styles.error);
    
    // Ripristina stile dopo 2 secondi
    setTimeout(() => {
        updateContainerStyle(container, Styles.default);
    }, 2000);
    
    alert('🤖 Completa la verifica anti-bot prima di accedere');
    return false;
};

// === INIZIALIZZAZIONE ===
const initCaptcha = () => {
    const checkboxArea = DOM.get(Selectors.checkboxArea);
    if (checkboxArea) {
        checkboxArea.addEventListener('click', verifyCaptcha);
    }
};

const initFormValidation = () => {
    const form = DOM.get(Selectors.form);
    if (form) {
        form.addEventListener('submit', validateForm);
    }
};

// === AVVIO ===
document.addEventListener('DOMContentLoaded', () => {
    initCaptcha();
    initFormValidation();
});