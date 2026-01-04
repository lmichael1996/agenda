/**
 * Gestione menu laterale - Desktop & Mobile
 */

import { openCenteredPopup } from './utils/windows.js';

// ============================================================================
// STATE
// ============================================================================
const state = {
    isOpen: false,
    hoverTimeout: null,
    isMobile: window.innerWidth <= 768
};

// ============================================================================
// DOM SELECTORS
// ============================================================================
const sidebar = document.getElementById("sidebar");
const sidebarToggle = document.querySelector(".sidebar-toggle");

// ============================================================================
// CONSTANTS
// ============================================================================
const POPUP_CONFIGS = {
    services: {
        url: 'popup/services.php',
        title: 'Gestione Servizi',
        width: 1000,
        height: 850
    },
    users: {
        url: 'popup/users.php', 
        title: 'Gestione Utenti',
        width: 1000,
        height: 850
    },
    schedule: {
        url: 'popup/schedule.php',
        title: 'Gestione Orari',
        width: 800,
        height: 950
    }
};

const TIMINGS = {
    hover: {
        open: 100,
        close: 50
    },
    mobileClose: 300
};

// ============================================================================
// BACKDROP MANAGEMENT
// ============================================================================
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

function removeBackdrop() {
    const backdrop = document.querySelector('.sidebar-backdrop');
    if (backdrop) {
        backdrop.classList.remove('active');
        backdrop.removeEventListener('click', closeSidebar);
    }
}

// ============================================================================
// SIDEBAR CONTROL
// ============================================================================
function openSidebar() {
    if (state.isOpen) return;
    
    clearTimeout(state.hoverTimeout);
    
    if (state.isMobile) {
        sidebar.classList.add('sidebar-open');
        createBackdrop();
    } else {
        sidebar.style.width = "240px";
    }
    
    state.isOpen = true;
}

function closeSidebar() {
    if (!state.isOpen) return;
    
    clearTimeout(state.hoverTimeout);
    
    if (state.isMobile) {
        sidebar.classList.remove('sidebar-open');
        removeBackdrop();
    } else {
        sidebar.style.width = "0px";
    }
    
    state.isOpen = false;
}

function toggleSidebar() {
    state.isOpen ? closeSidebar() : openSidebar();
}

// ============================================================================
// EVENT HANDLERS
// ============================================================================
function handleResize() {
    const wasMobile = state.isMobile;
    state.isMobile = window.innerWidth <= 768;
    
    if (wasMobile !== state.isMobile) {
        closeSidebar();
    }
}

function handleSidebarClick(e) {
    const target = e.target.closest('[data-popup-window]');
    if (!target) return;
    
    e.preventDefault();
    
    const popupType = target.getAttribute('data-popup-window');
    const config = POPUP_CONFIGS[popupType];
    
    if (config) {
        openCenteredPopup(config.url, config.title, config.width, config.height);
        
        if (state.isMobile) {
            setTimeout(closeSidebar, TIMINGS.mobileClose);
        }
    } else {
        console.error('Tipo popup non riconosciuto:', popupType);
    }
}

function handleMouseEnter() {
    if (!state.isMobile) {
        clearTimeout(state.hoverTimeout);
        state.hoverTimeout = setTimeout(openSidebar, TIMINGS.hover.open);
    }
}

function handleMouseLeave() {
    if (!state.isMobile) {
        clearTimeout(state.hoverTimeout);
        state.hoverTimeout = setTimeout(closeSidebar, TIMINGS.hover.close);
    }
}

function handleToggleClick(e) {
    e.preventDefault();
    e.stopPropagation();
    toggleSidebar();
}

// ============================================================================
// INITIALIZATION
// ============================================================================
function init() {
    if (!sidebar) return;
    
    sidebar.style.transition = 'width 0.3s ease';
    
    // Event listeners
    window.addEventListener('resize', handleResize);
    sidebar.addEventListener('click', handleSidebarClick);
    
    if (!state.isMobile) {
        sidebar.addEventListener('mouseenter', handleMouseEnter);
        sidebar.addEventListener('mouseleave', handleMouseLeave);
    }
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', handleToggleClick);
    }
    
    closeSidebar();
}

// Start
init();