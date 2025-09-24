/**
 * Gestione menu laterale semplificata
 */
const sidebar = document.getElementById("sidebar");

// Stato
let isOpen = false;
let hoverTimeout = null;

function openSidebar() {
    if (isOpen) return;
    
    clearTimeout(hoverTimeout);
    sidebar.style.width = "224px";
    isOpen = true;
}

function closeSidebar() {
    if (!isOpen) return;
    
    clearTimeout(hoverTimeout);
    sidebar.style.width = "0px";
    isOpen = false;
}

// Eventi con debounce sulla sidebar
if (sidebar) {
    // Imposta subito la transizione per evitare lentezza iniziale
    sidebar.style.transition = 'width 0.4s cubic-bezier(0.77,0,0.18,1)';
    
    sidebar.addEventListener('mouseenter', () => {
        clearTimeout(hoverTimeout);
        hoverTimeout = setTimeout(openSidebar, 100);
    });
    
    sidebar.addEventListener('mouseleave', () => {
        clearTimeout(hoverTimeout);
        hoverTimeout = setTimeout(closeSidebar, 50);
    });
    
    // Stato iniziale chiuso
    closeSidebar();
    
    // Gestione click sui link popup
    sidebar.addEventListener('click', function(e) {
        const target = e.target.closest('[data-popup-window]');
        if (!target) return;
        
        e.preventDefault();
        
        const popupType = target.getAttribute('data-popup-window');
        openPopupWindow(popupType);
    });
}

// Funzione per aprire finestre popup
function openPopupWindow(type) {
    const popupConfigs = {
        services: {
            url: 'popup/services.php',
            title: 'Gestione Servizi',
            width: 1400,
            height: 1000
        },
        users: {
            url: 'popup/users.php', 
            title: 'Gestione Utenti',
            width: 1300,
            height: 850
        },
        schedule: {
            url: 'popup/schedule.php',
            title: 'Gestione Orari',
            width: 1600,
            height: 1000
        }
    };
    
    const config = popupConfigs[type];
    if (!config) {
        console.error('Tipo popup non riconosciuto:', type);
        return;
    }
    
    // Calcola posizione centrale
    const left = (screen.width - config.width) / 2;
    const top = (screen.height - config.height) / 2;
    
    const windowFeatures = [
        `width=${config.width}`,
        `height=${config.height}`,
        `left=${left}`,
        `top=${top}`,
        'scrollbars=yes',
        'resizable=yes',
        'menubar=no',
        'toolbar=no',
        'location=no',
        'status=no'
    ].join(',');
    
    const popupWindow = window.open(config.url, config.title, windowFeatures);
    
    if (popupWindow) {
        popupWindow.focus();
    } else {
        alert('Impossibile aprire la finestra popup. Controlla le impostazioni del browser.');
    }
}