/**
 * Gestione menu laterale semplificata
 */
const sidebar = document.getElementById("sidebar");

// Configurazione
const CLOSED_WIDTH = "0px";
const OPEN_WIDTH = "224px";
const TRANSITION = 'width 0.4s cubic-bezier(0.77,0,0.18,1)';

// Stato
let isOpen = false;
let hoverTimeout = null;

function openSidebar() {
    if (isOpen) return;
    
    clearTimeout(hoverTimeout);
    sidebar.style.width = OPEN_WIDTH;
    isOpen = true;
}

function closeSidebar() {
    if (!isOpen) return;
    
    clearTimeout(hoverTimeout);
    sidebar.style.width = CLOSED_WIDTH;
    isOpen = false;
}

// Eventi con debounce sulla sidebar
if (sidebar) {
    // Imposta subito la transizione per evitare lentezza iniziale
    sidebar.style.transition = TRANSITION;
    
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
}