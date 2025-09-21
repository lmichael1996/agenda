// ========== POPUP CORE SYSTEM ==========
let activePopups = new Map();

window.openPopup = function(type) {
    const popup = createPopup(type);
    document.body.appendChild(popup);
    activePopups.set(type, popup);
    
    requestAnimationFrame(() => {
        popup.style.opacity = '1';
        popup.querySelector('.popup-content').style.transform = 'scale(1)';
    });
};

window.closePopup = function(popupId) {
    const popup = document.getElementById(popupId);
    if (popup) {
        const overlay = popup.closest('.popup-overlay');
        overlay.style.opacity = '0';
        
        setTimeout(() => {
            overlay.remove();
            const type = popupId.replace('popup-', '');
            activePopups.delete(type);
        }, 300);
    }
};

function createPopup(type) {
    const overlay = document.createElement('div');
    overlay.className = 'popup-overlay';
    
    const content = document.createElement('div');
    content.id = `popup-${type}`;
    content.className = 'popup-content';
    
    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '×';
    closeBtn.className = 'popup-close-btn';
    closeBtn.setAttribute('data-action', 'closePopup');
    closeBtn.setAttribute('data-popup-id', `popup-${type}`);
    
    content.appendChild(closeBtn);
    content.appendChild(getPopupContent(type));
    overlay.appendChild(content);
    
    return overlay;
}

// Routing del contenuto popup ai file specifici
function getPopupContent(type) {
    const contentDiv = document.createElement('div');
    
    switch (type) {
        case 'service':
            if (typeof getServicesPopupContent === 'function') {
                contentDiv.innerHTML = getServicesPopupContent();
            } else {
                contentDiv.innerHTML = '<p>Errore: popup-services.js non caricato</p>';
            }
            break;
            
        case 'user':
            if (typeof getUsersPopupContent === 'function') {
                contentDiv.innerHTML = getUsersPopupContent();
            } else {
                contentDiv.innerHTML = '<p>Errore: popup-users.js non caricato</p>';
            }
            break;
            
        case 'schedule':
            if (typeof getSchedulePopupContent === 'function') {
                contentDiv.innerHTML = getSchedulePopupContent();
            } else {
                contentDiv.innerHTML = '<p>Errore: popup-schedule.js non caricato</p>';
            }
            break;
            
        default:
            contentDiv.innerHTML = `
                <h2>Sezione: ${type}</h2>
                <p>Contenuto per la sezione ${type} in sviluppo.</p>
            `;
    }
    
    return contentDiv;
}

// ========== EVENT DELEGATION SYSTEM ==========
document.addEventListener('DOMContentLoaded', function() {
    // Event delegation per tutti i click sui popup
    document.addEventListener('click', handleClick);
    
    // Event delegation per i change events
    document.addEventListener('change', handleChange);
    
    // Chiudi popup cliccando fuori
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('popup-overlay')) {
            closePopup(e.target.querySelector('.popup-content').id);
        }
    });
    
    // Chiudi popup con ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            activePopups.forEach((popup, type) => {
                closePopup(`popup-${type}`);
            });
        }
    });
});

// Gestione centralizzata dei click - delega ai file specifici
function handleClick(e) {
    const action = e.target.getAttribute('data-action');
    if (!action) return;
    
    switch (action) {
        case 'closePopup':
            const popupId = e.target.getAttribute('data-popup-id');
            closePopup(popupId);
            break;
        
        case 'openPopup':
            const popupType = e.target.getAttribute('data-popup-type');
            if (popupType) {
                openPopup(popupType);
            }
            break;
        
        // Servizi - delegate to popup-services.js
        case 'addNewService':
        case 'deleteSelectedServices':
        case 'deleteService':
        case 'saveAllServices':
            if (typeof window[action] === 'function') {
                const serviceId = e.target.getAttribute('data-service-id');
                if (serviceId) {
                    window[action](serviceId);
                } else {
                    window[action]();
                }
            }
            break;
        
        // Utenti - delegate to popup-users.js
        case 'addNewUser':
        case 'deleteSelectedUsers':
        case 'deleteUser':
        case 'saveAllUsers':
            if (typeof window[action] === 'function') {
                const userId = e.target.getAttribute('data-user-id');
                if (userId) {
                    window[action](userId);
                } else {
                    window[action]();
                }
            }
            break;
        
        // Orari - delegate to popup-schedule.js
        case 'saveOrarioConfig':
            if (typeof window[action] === 'function') {
                window[action]();
            }
            break;
    }
}

// Gestione centralizzata dei change events - delega ai file specifici
function handleChange(e) {
    const action = e.target.getAttribute('data-action');
    
    switch (action) {
        case 'toggleSelectAllServices':
        case 'toggleSelectAll':
            if (typeof window[action] === 'function') {
                window[action]();
            }
            break;
    }
    
    // Aggiorna stats quando cambiano le selezioni - delega ai file specifici
    if (e.target.classList.contains('row-select')) {
        if (typeof updateUserStats === 'function') {
            updateUserStats();
        }
    }
    if (e.target.classList.contains('row-select-service')) {
        if (typeof updateServiceStats === 'function') {
            updateServiceStats();
        }
    }
    if (e.target.classList.contains('price-input') || e.target.classList.contains('duration-input')) {
        if (typeof updateServiceStats === 'function') {
            updateServiceStats();
        }
    }
}