let captchaVerified = false;

function startCaptchaVerification() {
    if (captchaVerified) return;
    
    const checkbox = document.getElementById('custom-checkbox');
    const spinner = document.getElementById('spinner');
    const header = document.getElementById('captcha-header');
    
    // Mostra rotellina
    checkbox.classList.add('loading');
    spinner.style.display = 'block';
    
    // Completa verifica dopo 1.5s
    setTimeout(() => {
        spinner.style.display = 'none';
        checkbox.classList.remove('loading');
        checkbox.classList.add('verified');
        
        // Aggiorna header con successo
        header.innerHTML = `
            <div class="recaptcha-success">
                <div class="success-icon"></div>
                <span class="recaptcha-text">Verifica completata</span>
            </div>
        `;
        
        document.getElementById('captcha-solved').value = '1';
        captchaVerified = true;
        
        // Feedback visivo
        const container = document.querySelector('.captcha-container');
        container.style.borderColor = '#4caf50';
        
    }, 1500);
}

// Aspetta che il DOM sia caricato
document.addEventListener('DOMContentLoaded', function() {
    // Gestisci click del CAPTCHA
    const captchaCheckbox = document.querySelector('.recaptcha-checkbox');
    if (captchaCheckbox) {
        captchaCheckbox.addEventListener('click', startCaptchaVerification);
    }
    
    // Previeni submit senza verifica
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!captchaVerified) {
                e.preventDefault();
                
                const container = document.querySelector('.captcha-container');
                container.style.borderColor = '#f44336';
                
                setTimeout(() => {
                    container.style.borderColor = '#d3d3d3';
                }, 2000);
                
                alert('⚠️ Completa la verifica CAPTCHA');
                return false;
            }
        });
    }
});