let captchaVerified = false;
let captchaRequired = true; // Sempre richiesto

function startCaptchaVerification() {
    if (captchaVerified) return;
    
    const checkbox = document.getElementById('custom-checkbox');
    const spinner = document.getElementById('spinner');
    const header = document.getElementById('captcha-header');
    
    // Mostra rotellina
    checkbox.classList.add('loading');
    spinner.style.display = 'block';
    
    // Completa verifica dopo 1.2s
    setTimeout(() => {
        spinner.style.display = 'none';
        checkbox.classList.remove('loading');
        checkbox.classList.add('verified');
        
        // Aggiorna header con successo
        header.innerHTML = `
            <div class="recaptcha-success">
                <div class="success-icon">✓</div>
                <span class="recaptcha-text">Verifica completata con successo</span>
            </div>
        `;
        
        document.getElementById('captcha-solved').value = '1';
        captchaVerified = true;
        
        // Feedback visivo
        const container = document.querySelector('.captcha-container');
        container.style.borderColor = '#4caf50';
        container.style.background = 'linear-gradient(135deg, #e8f5e8, #c8e6c9)';
        
    }, 1200);
}

// Aspetta che il DOM sia caricato
document.addEventListener('DOMContentLoaded', function() {
    // CAPTCHA sempre richiesto
    const captchaContainer = document.querySelector('.captcha-container');
    if (captchaContainer) {
        captchaRequired = true;
        
        // Gestisci click del CAPTCHA
        const captchaCheckbox = document.querySelector('.recaptcha-checkbox');
        if (captchaCheckbox) {
            captchaCheckbox.addEventListener('click', startCaptchaVerification);
        }
    }
    
    // Previeni submit senza verifica CAPTCHA
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!captchaVerified) {
                e.preventDefault();
                
                const container = document.querySelector('.captcha-container');
                container.style.borderColor = '#f44336';
                container.style.background = 'linear-gradient(135deg, #ffebee, #ffcdd2)';
                
                setTimeout(() => {
                    container.style.borderColor = '#dee2e6';
                    container.style.background = 'linear-gradient(135deg, #f8f9fa, #e9ecef)';
                }, 2000);
                
                alert('🤖 Completa la verifica anti-bot prima di accedere');
                return false;
            }
        });
    }
});