/**
 * Script per il popup gestione clienti
 * Gestisce tutta la parte grafica e l'interazione utente
 * Utilizza le funzioni data da clients-api.js
 */

// Importa le funzioni per i dati dal backend
import { 
    fetchClients, 
    deleteClient 
} from '../../api/frontend/clients-api.js';

// Stato dell'applicazione
let currentPage = 1;
let currentLimit = 50;
let currentSearch = '';
let currentSearchField = 'all';
let currentSearchType = 'contains';
let currentSort = 'last_name_asc';
let clients = [];
let pagination = {};

// Elementi DOM
const tableBody = document.getElementById('client-table-body');
const searchInput = document.getElementById('client-search');
const searchFieldSelect = document.getElementById('search-field-select');
const searchTypeSelect = document.getElementById('search-type-select');
const searchBtn = document.getElementById('search-btn');
const sortSelect = document.getElementById('sort-select');
const prevBtn = document.getElementById('prev-btn');
const nextBtn = document.getElementById('next-btn');
const rangeLabel = document.getElementById('client-range-label');
const totalLabel = document.getElementById('client-total-label');
const addClientBtn = document.getElementById('add-client-btn');
const groupBtn = document.getElementById('group-btn');
const loadingIndicator = document.getElementById('loading-indicator');
const clientsTable = document.getElementById('clients-table');
const errorMessage = document.getElementById('error-message');

/**
 * Attacca gli event listeners
 */
function attachEventListeners() {
    if (searchBtn) {
        searchBtn.addEventListener('click', () => performSearch());
    }
    
    if (searchInput) {
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }
    
    if (prevBtn) {
        prevBtn.addEventListener('click', () => goToPreviousPage());
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', () => goToNextPage());
    }
    
    if (addClientBtn) {
        addClientBtn.addEventListener('click', () => showAddClientDialog());
    }
    
    if (groupBtn) {
        groupBtn.addEventListener('click', () => toggleCertificateFilter());
    }
    
    if (sortSelect) {
        sortSelect.addEventListener('change', () => performSort());
    }
    
    if (searchFieldSelect) {
        searchFieldSelect.addEventListener('change', () => performSearch());
    }
    
    if (searchTypeSelect) {
        searchTypeSelect.addEventListener('change', () => performSearch());
    }
}

/**
 * Carica i clienti dal backend
 */
async function loadClients() {
    try {
        showLoading();
        hideError();
        
        const response = await fetchClients(
            currentPage, 
            currentLimit, 
            currentSearch, 
            currentSearchField, 
            currentSearchType,
            currentSort
        );
        
        if (response.success) {
            clients = response.data;
            pagination = response.pagination;
            renderTable();
            updatePaginationControls();
            showTable();
        } else {
            throw new Error(response.error || 'Errore sconosciuto');
        }
    } catch (error) {
        console.error('Errore caricamento clienti:', error);
        showError('Errore nel caricamento dei clienti: ' + error.message);
        hideTable();
    } finally {
        hideLoading();
    }
}

/**
 * Renderizza la tabella con i dati dei clienti
 */
function renderTable() {
    if (!tableBody) return;
    
    // Pulisci la tabella
    tableBody.innerHTML = '';
    
    if (clients.length === 0) {
        const emptyRow = createTableRow(['Nessun cliente trovato'], 6, 'empty-row');
        tableBody.appendChild(emptyRow);
        return;
    }
    
    // Genera le righe con i dati dei clienti
    clients.forEach(client => {
        const row = createClientRow(client);
        tableBody.appendChild(row);
    });
}

/**
 * Crea una riga della tabella per un cliente
 */
function createClientRow(client) {
    const row = document.createElement('tr');
    row.className = 'client-row';
    row.style.cursor = 'pointer';
    row.dataset.clientId = client.id;
    row.title = 'Clicca per vedere i dettagli del cliente';
    
    const q = (currentSearch || '').trim();
    const field = currentSearchField || 'all';
    const highlightFirst = q && (field === 'first_name' || field === 'name' || field === 'all');
    const highlightLast = q && (field === 'last_name' || field === 'name' || field === 'all');
    const highlightPhone = q && (field === 'phone' || field === 'all');
    const highlightNotes = q && (field === 'notes' || field === 'all');

    // Colonna Nome
    const nameCell = document.createElement('td');
    nameCell.innerHTML = highlightFirst
        ? getHighlightedHTML(client.first_name || '', q)
        : escapeHtml(client.first_name || '');
    nameCell.className = 'client-name-cell';
    
    // Colonna Cognome
    const surnameCell = document.createElement('td');
    surnameCell.innerHTML = highlightLast
        ? getHighlightedHTML(client.last_name || '', q)
        : escapeHtml(client.last_name || '');
    surnameCell.className = 'client-surname-cell';
    
    // Colonna Telefono
    const phoneCell = document.createElement('td');
    phoneCell.innerHTML = highlightPhone
        ? getHighlightedHTML(client.phone || '', q)
        : escapeHtml(client.phone || '');
    phoneCell.className = 'client-phone-cell';
    
    // Colonna Note
    const notesCell = document.createElement('td');
    const notes = client.notes || 'Nessuna nota';
    const notesDisplay = truncateText(notes, 50);
    notesCell.innerHTML = highlightNotes
        ? getHighlightedHTML(notesDisplay, q)
        : escapeHtml(notesDisplay);
    notesCell.title = notes;
    notesCell.className = 'client-notes-cell';
    
    // Colonna Certificato
    const certCell = document.createElement('td');
    certCell.textContent = client.has_certificate ? '✓' : '✗';
    certCell.className = 'client-cert-cell';
    certCell.style.textAlign = 'center';
    
    // Colonna Azioni
    const actionsCell = document.createElement('td');
    actionsCell.className = 'actions-cell';
    
    const editBtn = document.createElement('button');
    editBtn.className = 'action-btn btn-add-appointment';
    editBtn.textContent = 'Appuntamento';
    editBtn.title = 'Aggiungi appuntamento';
    editBtn.dataset.clientId = client.id;
    
    const deleteBtn = document.createElement('button');
    deleteBtn.className = 'action-btn btn-delete-client';
    deleteBtn.textContent = 'Elimina';
    deleteBtn.title = 'Elimina cliente';
    deleteBtn.dataset.clientId = client.id;
    
    actionsCell.appendChild(editBtn);
    actionsCell.appendChild(deleteBtn);
    
    // Event handlers for action buttons (prevent row click)
    editBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        addAppointment(client.id);
    });
    
    deleteBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        handleDeleteClient(client.id);
    });
    
    // Assembla la riga
    row.appendChild(nameCell);
    row.appendChild(surnameCell);
    row.appendChild(phoneCell);
    row.appendChild(notesCell);
    row.appendChild(certCell);
    row.appendChild(actionsCell);
    
    // Event listener per aprire dettagli cliente
    row.addEventListener('click', () => showClientDetails(client.id));
    
    // Hover effect
    row.addEventListener('mouseenter', () => {
        row.style.backgroundColor = '#e8f4f8';
        row.style.transform = 'translateX(2px)';  
        row.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
    });
    
    row.addEventListener('mouseleave', () => {
        row.style.backgroundColor = '';
        row.style.transform = '';
        row.style.boxShadow = '';
    });
    
    return row;
}

/**
 * Crea una riga generica della tabella
 */
function createTableRow(data, colspan = null, className = '') {
    const row = document.createElement('tr');
    if (className) row.className = className;
    
    if (colspan) {
        const cell = document.createElement('td');
        cell.colSpan = colspan;
        cell.textContent = data[0] || '';
        cell.style.textAlign = 'center';
        cell.style.fontStyle = 'italic';
        cell.style.color = '#666';
        row.appendChild(cell);
    } else {
        data.forEach(cellData => {
            const cell = document.createElement('td');
            cell.textContent = cellData || '';
            row.appendChild(cell);
        });
    }
    
    return row;
}

/**
 * Aggiorna i controlli di paginazione
 */
function updatePaginationControls() {
    if (prevBtn) {
        prevBtn.disabled = pagination.page <= 1;
    }
    
    if (nextBtn) {
        nextBtn.disabled = pagination.page >= pagination.totalPages;
    }
    
    if (rangeLabel) {
        rangeLabel.textContent = `${pagination.start}-${pagination.end}`;
    }
    
    if (totalLabel) {
        totalLabel.textContent = `Totale: ${pagination.total}`;
    }
}

/**
 * Esegue la ricerca
 */
function performSearch() {
    currentSearch = searchInput ? searchInput.value.trim() : '';
    currentSearchField = searchFieldSelect ? searchFieldSelect.value : 'all';
    currentSearchType = searchTypeSelect ? searchTypeSelect.value : 'contains';
    currentPage = 1;
    loadClients();
}

/**
 * Esegue l'ordinamento
 */
function performSort() {
    currentSort = sortSelect ? sortSelect.value : 'last_name_asc';
    currentPage = 1;
    loadClients();
}

/**
 * Va alla pagina precedente
 */
function goToPreviousPage() {
    if (currentPage > 1) {
        currentPage--;
        loadClients();
    }
}

/**
 * Va alla pagina successiva
 */
function goToNextPage() {
    if (currentPage < pagination.totalPages) {
        currentPage++;
        loadClients();
    }
}

/**
 * Mostra i dettagli del cliente
 */
function showClientDetails(clientId) {
    console.log('Opening client details for ID:', clientId);
    
    if (!clientId) {
        alert('ID cliente non valido');
        return;
    }

    // Open new popup window with client details
    const windowFeatures = [
        'width=650',
        'height=550',
        'left=' + (screen.width - 650) / 2,
        'top=' + (screen.height - 550) / 2,
        'scrollbars=yes',
        'resizable=yes',
        'menubar=no',
        'toolbar=no',
        'location=no',
        'status=no'
    ].join(',');
    
    const popupUrl = `client-detail.php?clientId=${clientId}`;
    console.log('Opening popup URL:', popupUrl);
    
    const popupWindow = window.open(popupUrl, 'ClientDetail_' + clientId, windowFeatures);
    
    if (popupWindow) {
        popupWindow.focus();
        console.log('Client detail popup opened successfully');
    } else {
        console.error('Failed to open popup window');
        alert('Impossibile aprire la finestra popup. Controlla le impostazioni del browser.');
    }
}

/**
 * Aggiunge un appuntamento per il cliente
 */
function addAppointment(clientId) {
    console.log('Opening add appointment for client ID:', clientId);
    
    if (!clientId) {
        alert('ID cliente non valido');
        return;
    }

    // Find client data for appointment context
    const client = clients.find(c => c.id == clientId);
    const clientName = client ? 
        [client.first_name, client.last_name].filter(Boolean).join(' ') : 
        `Cliente ID ${clientId}`;

    // Open appointment popup window with client pre-selected
    const windowFeatures = [
        'width=800',
        'height=700',
        'left=' + (screen.width - 800) / 2,
        'top=' + (screen.height - 700) / 2,
        'scrollbars=yes',
        'resizable=yes',
        'menubar=no',
        'toolbar=no',
        'location=no',
        'status=no'
    ].join(',');
    
    const popupUrl = `schedule.php?clientId=${clientId}&clientName=${encodeURIComponent(clientName)}`;
    console.log('Opening appointment popup URL:', popupUrl);
    
    const popupWindow = window.open(popupUrl, 'AddAppointment_' + clientId, windowFeatures);
    
    if (popupWindow) {
        popupWindow.focus();
        console.log(`Appointment popup opened for client: ${clientName}`);
    } else {
        console.error('Failed to open appointment popup window');
        alert('Impossibile aprire la finestra appuntamenti. Controlla le impostazioni del browser.');
    }
}

/**
 * Gestisce l'eliminazione di un cliente
 */
async function handleDeleteClient(clientId) {
    console.log('Deleting client with ID:', clientId);
    
    if (!clientId) {
        alert('ID cliente non valido');
        return;
    }

    // Find client data for confirmation message
    const client = clients.find(c => c.id == clientId);
    const clientName = client ? 
        [client.first_name, client.last_name].filter(Boolean).join(' ') : 
        `Cliente ID ${clientId}`;
    
    const confirmMessage = `Sei sicuro di voler eliminare il cliente "${clientName}"?\n\nQuesta operazione non può essere annullata.`;
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    try {
        const result = await deleteClient(clientId);
        
        if (result.success) {
            alert('Cliente eliminato con successo!');
            // Refresh client list
            loadClients();
        } else {
            alert('Errore durante l\'eliminazione: ' + (result.error || 'Errore sconosciuto'));
        }
    } catch (error) {
        console.error('Errore eliminazione cliente:', error);
        alert('Errore di connessione durante l\'eliminazione');
    }
}

/**
 * Mostra il dialog per aggiungere un cliente
 */
function showAddClientDialog() {
    console.log('Apri dialog aggiungi cliente');
    // Implementa dialog per aggiungere cliente
}

/**
 * Resetta tutti i filtri e mostra tutti i clienti
 */
function toggleCertificateFilter() {
    console.log('Reset filtri - mostra tutti i clienti');
    
    // Reset tutti i campi di ricerca e filtri
    if (searchInput) {
        searchInput.value = '';
    }
    
    if (searchFieldSelect) {
        searchFieldSelect.value = 'name';
    }
    
    if (searchTypeSelect) {
        searchTypeSelect.value = 'contains';
    }
    
    if (sortSelect) {
        sortSelect.value = 'first_name_asc';
    }
    
    // Reset variabili di stato
    currentSearch = '';
    currentSearchField = 'name';
    currentSearchType = 'contains';
    currentSort = 'first_name_asc';
    currentPage = 1;
    
    // Ricarica tutti i clienti senza filtri
    loadClients();
}

/**
 * Mostra l'indicatore di caricamento
 */
function showLoading() {
    if (loadingIndicator) {
        loadingIndicator.style.display = 'block';
    }
}

/**
 * Nasconde l'indicatore di caricamento
 */
function hideLoading() {
    if (loadingIndicator) {
        loadingIndicator.style.display = 'none';
    }
}

/**
 * Mostra la tabella
 */
function showTable() {
    if (clientsTable) {
        clientsTable.style.display = 'table';
    }
}

/**
 * Nasconde la tabella
 */
function hideTable() {
    if (clientsTable) {
        clientsTable.style.display = 'none';
    }
}

/**
 * Mostra un messaggio di errore
 */
function showError(message) {
    console.error('Errore UI:', message);
    if (errorMessage) {
        errorMessage.style.display = 'block';
        errorMessage.innerHTML = `<p>${escapeHtml(message)}</p>`;
    }
}

/**
 * Nasconde il messaggio di errore
 */
function hideError() {
    if (errorMessage) {
        errorMessage.style.display = 'none';
    }
}

// Utility functions

/**
 * Escapa il testo HTML
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Escapa le espressioni regolari
 */
function escapeRegExp(text) {
    return (text || '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

/**
 * Ottiene l'HTML con evidenziazione della ricerca
 */
function getHighlightedHTML(text, query) {
    const source = String(text || '');
    const q = String(query || '').trim();
    if (!q) return escapeHtml(source);
    try {
        const regex = new RegExp(escapeRegExp(q), 'gi');
        let lastIndex = 0;
        let match;
        const parts = [];
        while ((match = regex.exec(source)) !== null) {
            const start = match.index;
            const end = start + match[0].length;
            parts.push(escapeHtml(source.slice(lastIndex, start)));
            parts.push('<span class="search-highlight">', escapeHtml(source.slice(start, end)), '</span>');
            lastIndex = end;
            if (regex.lastIndex === start) regex.lastIndex++; // avoid zero-length loops
        }
        parts.push(escapeHtml(source.slice(lastIndex)));
        return parts.join('');
    } catch (e) {
        // Fallback safe
        return escapeHtml(source);
    }
}

/**
 * Tronca il testo alla lunghezza specificata
 */
function truncateText(text, maxLength) {
    if (!text || text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}

// Inizializzazione automatica quando il DOM è pronto
document.addEventListener('DOMContentLoaded', function() {
    // Leggi parametri URL per ricerca automatica
    const urlParams = new URLSearchParams(window.location.search);
    const searchText = urlParams.get('search') || '';
    const searchField = urlParams.get('searchField') || 'name';
    const searchType = urlParams.get('searchType') || 'contains';

    // Debug: mostra i parametri caricati
    console.log('Client popup caricato con parametri:', { searchText, searchField, searchType });

    // Inizializza event listeners
    attachEventListeners();

    // Se ci sono parametri di ricerca, impostali
    if (searchText || searchField !== 'name' || searchType !== 'contains') {
        // Imposta i valori nei controlli
        if (searchInput && searchText) {
            searchInput.value = searchText;
            currentSearch = searchText;
        }
        
        if (searchFieldSelect && searchField) {
            searchFieldSelect.value = searchField;
            currentSearchField = searchField;
        }

        if (searchTypeSelect && searchType) {
            searchTypeSelect.value = searchType;
            currentSearchType = searchType;
        }
    }

    // Carica i clienti
    loadClients();

    // Rendi disponibili globalmente per debug
    window.clientsData = {
        loadClients,
        currentPage,
        currentSearch,
        currentSearchField,
        currentSearchType,
        clients,
        pagination
    };
});