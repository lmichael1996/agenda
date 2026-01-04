/**
 * Utility per gestione finestre popup
 */

// ============================================================================
// CONSTANTS
// ============================================================================
export const POPUP_DEFAULTS = {
    width: 1600,
    height: 900
};

// ============================================================================
// POPUP WINDOW
// ============================================================================
/**
 * Apre popup centrato rispetto alla finestra corrente
 * @param {string} url - URL da aprire
 * @param {string} windowName - Nome della finestra
 * @param {number} width - Larghezza della finestra
 * @param {number} height - Altezza della finestra
 * @returns {Window|null} - Riferimento alla finestra aperta o null
 */
export function openCenteredPopup(url, windowName, width = POPUP_DEFAULTS.width, height = POPUP_DEFAULTS.height) {
    const left = window.screenX + (window.outerWidth - width) / 2;
    const top = window.screenY + (window.outerHeight - height) / 2;
    
    const features = [
        `width=${width}`,
        `height=${height}`,
        `left=${left}`,
        `top=${top}`,
        'scrollbars=yes',
        'resizable=yes',
        'menubar=no',
        'toolbar=no',
        'location=no',
        'status=no'
    ].join(',');
    
    const popup = window.open(url, windowName, features);
    
    if (popup) {
        popup.focus();
    } else {
        console.warn('Impossibile aprire popup. Controlla le impostazioni del browser.');
    }
    
    return popup;
}

/**
 * Costruisce URL con parametri di ricerca
 * @param {string} baseUrl - URL base
 * @param {Object} searchParams - Parametri di ricerca
 * @returns {string} - URL completo
 */
export function buildSearchUrl(baseUrl, searchParams) {
    const params = new URLSearchParams();
    
    if (searchParams.text) {
        params.set('search', searchParams.text);
    }
    
    if (searchParams.field) {
        params.set('searchField', searchParams.field);
    }
    
    if (searchParams.type) {
        params.set('searchType', searchParams.type);
    }
    
    const queryString = params.toString();
    return queryString ? `${baseUrl}?${queryString}` : baseUrl;
}
