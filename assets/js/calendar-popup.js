// ========== POPUP CORE SYSTEM ==========
let activePopups = new Map();

window.openPopup = function(type) {
    const popup = createPopup(type);
    document.body.appendChild(popup);
    activePopups.set(type, popup);
    requestAnimationFrame(() => popup.style.opacity = '1');
};

window.closePopup = function(popupId) {
    const popup = document.getElementById(popupId);
    if (!popup) return;
    
    const overlay = popup.closest('.popup-overlay');
    overlay.style.opacity = '0';
    setTimeout(() => {
        overlay.remove();
        activePopups.delete(popupId.replace('popup-', ''));
    }, 300);
};

function createPopup(type) {
    const overlay = document.createElement('div');
    overlay.className = 'popup-overlay';
    overlay.innerHTML = `
        <div id="popup-${type}" class="popup-content">
            <button class="popup-close-btn" data-action="closePopup" data-popup-id="popup-${type}">×</button>
            ${getPopupContent(type)}
        </div>
    `;
    return overlay;
}

// Routing del contenuto popup ai file specifici
function getPopupContent(type) {
    const functionMap = {
        'service': 'getServicesPopupContent',
        'user': 'getUsersPopupContent', 
        'schedule': 'getSchedulePopupContent'
    };
    
    const functionName = functionMap[type];
    if (functionName && typeof window[functionName] === 'function') {
        return window[functionName]();
    }
    
    return functionName 
        ? `<p>Errore: popup-${type}s.js non caricato</p>`
        : `<h2>Sezione: ${type}</h2><p>Contenuto in sviluppo.</p>`;
}

// ========== EVENT DELEGATION SYSTEM ==========
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', handleClick);
    document.addEventListener('change', handleChange);
    
    // Chiudi popup cliccando fuori o con ESC
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('popup-overlay')) {
            closePopup(e.target.querySelector('.popup-content').id);
        }
    });
    
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            activePopups.forEach((popup, type) => closePopup(`popup-${type}`));
        }
    });
});

// Gestione centralizzata degli eventi - delega ai file specifici
function handleClick(e) {
    const action = e.target.getAttribute('data-action');
    if (!action) return;
    
    // Gestione popup core
    if (action === 'closePopup') {
        closePopup(e.target.getAttribute('data-popup-id'));
        return;
    }
    if (action === 'openPopup') {
        openPopup(e.target.getAttribute('data-popup-type'));
        return;
    }
    
    // Delega ai file specifici
    if (typeof window[action] === 'function') {
        const id = e.target.getAttribute('data-service-id') || e.target.getAttribute('data-user-id');
        id ? window[action](id) : window[action]();
    }
}

function handleChange(e) {
    const action = e.target.getAttribute('data-action');
    if (action && typeof window[action] === 'function') {
        window[action]();
    }
    
    // Auto-aggiornamento statistiche
    const statsMap = {
        'row-select': 'updateUserStats',
        'row-select-service': 'updateServiceStats', 
        'price-input': 'updateServiceStats',
        'duration-input': 'updateServiceStats'
    };
    
    Object.entries(statsMap).forEach(([className, funcName]) => {
        if (e.target.classList.contains(className) && typeof window[funcName] === 'function') {
            window[funcName]();
        }
    });
}