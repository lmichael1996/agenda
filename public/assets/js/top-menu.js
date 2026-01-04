/**
 * Gestione menu superiore - Note e Ricerca Clienti
 */

import { openCenteredPopup, buildSearchUrl } from './utils/windows.js';

// ============================================================================
// DOM SELECTORS
// ============================================================================
const noteBtn = document.getElementById('note-btn');
const clientSearchBtn = document.getElementById('client-search');
const searchInput = document.getElementById('cerca');
const searchFieldSelect = document.getElementById('search-field-select');
const searchTypeSelect = document.getElementById('search-type-select');

// ============================================================================
// CONSTANTS
// ============================================================================
const POPUP_CONFIGS = {
    notes: {
        url: 'popup/notes.php',
        title: 'Gestione Nota',
        width: 900,
        height: 850
    },
    clients: {
        url: 'popup/clients.php',
        title: 'Gestione Cliente',
        width: 1600,
        height: 900
    }
};

// ============================================================================
// EVENT HANDLERS
// ============================================================================
function handleNoteClick() {
    const config = POPUP_CONFIGS.notes;
    openCenteredPopup(config.url, config.title, config.width, config.height);
}

function handleClientSearchClick() {
    const searchParams = {
        text: searchInput?.value.trim() || '',
        field: searchFieldSelect?.value || '',
        type: searchTypeSelect?.value || ''
    };
    
    const config = POPUP_CONFIGS.clients;
    const url = buildSearchUrl(config.url, searchParams);
    
    openCenteredPopup(url, config.title, config.width, config.height);
}

// ============================================================================
// INITIALIZATION
// ============================================================================
function init() {
    if (noteBtn) {
        noteBtn.addEventListener('click', handleNoteClick);
    }
    
    if (clientSearchBtn) {
        clientSearchBtn.addEventListener('click', handleClientSearchClick);
    }
}

// Start
init();