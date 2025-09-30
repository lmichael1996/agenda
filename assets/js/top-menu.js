const noteBtn = document.getElementById('note-btn');
const clientSearchBtn = document.getElementById('client-search');

/**
 * Funzione utility per aprire popup centrati
 * @param {string} url - URL da aprire
 * @param {string} windowName - Nome della finestra
 * @param {number} width - Larghezza della finestra (default: 1600)
 * @param {number} height - Altezza della finestra (default: 900)
 * @returns {Window} - Riferimento alla finestra aperta
 */
function openCenteredPopup(url, windowName, width = 1600, height = 900) {
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
    
    return window.open(url, windowName, features);
}

noteBtn.addEventListener('click', function() {
    openCenteredPopup('popup/note.php', 'Gestione Nota');
});

clientSearchBtn.addEventListener('click', function() {
    // Ottieni i valori di ricerca dal dashboard
    const searchText = document.getElementById('cerca').value.trim();
    const searchField = document.getElementById('search-field-select').value;
    const searchType = document.getElementById('search-type-select').value;
    
    // Costruisci URL con parametri di ricerca - sempre includi tutti i parametri
    const params = new URLSearchParams();
    if (searchText) params.set('search', searchText);
    params.set('searchField', searchField); // Sempre includi searchField
    params.set('searchType', searchType);   // Sempre includi searchType
    
    let url = 'popup/clients.php';
    if (params.toString()) {
        url += '?' + params.toString();
    }
    
    openCenteredPopup(url, 'Gestione Cliente');
});