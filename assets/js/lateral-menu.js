/**
 * Gestione menu laterale - Desktop & Mobile
 */
const sidebar = document.getElementById("sidebar");
const sidebarToggle = document.querySelector(".sidebar-toggle");

// Stato
let isOpen = false;
let hoverTimeout = null;
let isMobile = window.innerWidth <= 768;

// Aggiorna stato mobile su resize
window.addEventListener('resize', () => {
    const wasMobile = isMobile;
    isMobile = window.innerWidth <= 768;
    
    // Se cambia da mobile a desktop o viceversa, reset del menu
    if (wasMobile !== isMobile) {
        closeSidebar();
    }
});

function openSidebar() {
    if (isOpen) return;
    
    clearTimeout(hoverTimeout);
    
    if (isMobile) {
        sidebar.classList.add('sidebar-open');
        createBackdrop();
    } else {
        sidebar.style.width = "240px";
    }
    
    isOpen = true;
}

function closeSidebar() {
    if (!isOpen) return;
    
    clearTimeout(hoverTimeout);
    
    if (isMobile) {
        sidebar.classList.remove('sidebar-open');
        removeBackdrop();
    } else {
        sidebar.style.width = "0px";
    }
    
    isOpen = false;
}

function toggleSidebar() {
    if (isOpen) {
        closeSidebar();
    } else {
        openSidebar();
    }
}

// Crea backdrop per mobile
function createBackdrop() {
    let backdrop = document.querySelector('.sidebar-backdrop');
    if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.className = 'sidebar-backdrop';
        document.body.appendChild(backdrop);
    }
    backdrop.classList.add('active');
    backdrop.addEventListener('click', closeSidebar);
}

// Rimuovi backdrop
function removeBackdrop() {
    const backdrop = document.querySelector('.sidebar-backdrop');
    if (backdrop) {
        backdrop.classList.remove('active');
        backdrop.removeEventListener('click', closeSidebar);
    }
}

// Eventi sidebar
if (sidebar) {
    // Imposta transizione CSS
    sidebar.style.transition = 'width 0.3s ease';
    
    // Desktop: hover behavior
    if (!isMobile) {
        sidebar.addEventListener('mouseenter', () => {
            if (!isMobile) {
                clearTimeout(hoverTimeout);
                hoverTimeout = setTimeout(openSidebar, 100);
            }
        });
        
        sidebar.addEventListener('mouseleave', () => {
            if (!isMobile) {
                clearTimeout(hoverTimeout);
                hoverTimeout = setTimeout(closeSidebar, 50);
            }
        });
    }
    
    // Mobile: click toggle button
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            toggleSidebar();
        });
    }
    
    // Stato iniziale chiuso
    closeSidebar();
    
    // Gestione click sui link popup
    sidebar.addEventListener('click', function(e) {
        const target = e.target.closest('[data-popup-window]');
        if (!target) return;
        
        e.preventDefault();
        
        const popupType = target.getAttribute('data-popup-window');
        openPopupWindow(popupType);
        
        // Chiudi menu su mobile dopo click
        if (isMobile) {
            setTimeout(closeSidebar, 300);
        }
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