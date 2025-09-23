/**
 * Utility per bloccare l'input da tastiera sui campi durata mantenendo le freccette
 * Autore: Sistema di gestione agenda
 * Data: Settembre 2025
 */

/**
 * Funzione principale per bloccare input da tastiera sui campi durata
 * Mantiene le freccette per incrementare/decrementare ma blocca la digitazione diretta
 */
function blockKeyboardInputOnDuration() {
    // Event listener per bloccare specifici tasti sui campi duration-input
    document.addEventListener('keydown', function(e) {
        // Controlla se l'elemento attivo è un campo duration-input
        if (e.target && e.target.classList.contains('duration-input')) {
            // Codici tasti permessi: Tab(9), Enter(13), Esc(27), Frecce(37-40), Backspace(8), Delete(46)
            const allowedKeys = [9, 13, 27, 37, 38, 39, 40, 8, 46];
            
            // Blocca tutti i numeri (48-57 e 96-105) e lettere
            if ((e.keyCode >= 48 && e.keyCode <= 57) || // numeri riga superiore
                (e.keyCode >= 96 && e.keyCode <= 105) || // numpad numeri
                (e.keyCode >= 65 && e.keyCode <= 90) || // lettere
                (e.keyCode >= 186 && e.keyCode <= 222)) { // simboli
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
            
            // Permetti solo i tasti nella lista allowedKeys
            if (!allowedKeys.includes(e.keyCode)) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        }
    });
    
    // Blocca operazioni clipboard sui campi duration
    document.addEventListener('paste', function(e) {
        if (e.target && e.target.classList.contains('duration-input')) {
            e.preventDefault();
        }
    });
    
    document.addEventListener('cut', function(e) {
        if (e.target && e.target.classList.contains('duration-input')) {
            e.preventDefault();
        }
    });
    
    document.addEventListener('copy', function(e) {
        if (e.target && e.target.classList.contains('duration-input')) {
            e.preventDefault();
        }
    });
    
    // Blocca wheel per evitare cambi accidentali con rotellina mouse
    document.addEventListener('wheel', function(e) {
        if (e.target && e.target.classList.contains('duration-input') && e.target === document.activeElement) {
            e.preventDefault();
        }
    });
    
    console.log('✅ Blocco input tastiera attivato per campi duration-input');
}

/**
 * Funzione di inizializzazione per i campi durata
 * Applica automaticamente il blocco quando il DOM è pronto
 */
function initializeDurationInputBlocker() {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', blockKeyboardInputOnDuration);
    } else {
        blockKeyboardInputOnDuration();
    }
}

/**
 * Funzione per applicare il blocco a specifici selettori
 * @param {string} selector - Selettore CSS per i campi target (default: '.duration-input')
 */
function blockKeyboardInputOnSelector(selector = '.duration-input') {
    document.addEventListener('keydown', function(e) {
        if (e.target && e.target.matches(selector)) {
            const allowedKeys = [9, 13, 27, 37, 38, 39, 40, 8, 46];
            
            if ((e.keyCode >= 48 && e.keyCode <= 57) || 
                (e.keyCode >= 96 && e.keyCode <= 105) || 
                (e.keyCode >= 65 && e.keyCode <= 90) || 
                (e.keyCode >= 186 && e.keyCode <= 222)) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
            
            if (!allowedKeys.includes(e.keyCode)) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        }
    });
    
    ['paste', 'cut', 'copy'].forEach(eventType => {
        document.addEventListener(eventType, function(e) {
            if (e.target && e.target.matches(selector)) {
                e.preventDefault();
            }
        });
    });
    
    document.addEventListener('wheel', function(e) {
        if (e.target && e.target.matches(selector) && e.target === document.activeElement) {
            e.preventDefault();
        }
    });
}

// Esporta le funzioni per uso globale
if (typeof window !== 'undefined') {
    window.blockKeyboardInputOnDuration = blockKeyboardInputOnDuration;
    window.initializeDurationInputBlocker = initializeDurationInputBlocker;
    window.blockKeyboardInputOnSelector = blockKeyboardInputOnSelector;
}

// Auto-inizializzazione se il file viene incluso direttamente
if (typeof window !== 'undefined' && window.document) {
    initializeDurationInputBlocker();
}