/**
 * API Frontend per la gestione dei clienti
 * Wrapper JavaScript per le chiamate REST all'API clients
 */

const PATH = '../../api/backend/clients-api.php';

/**
 * Recupera la lista dei clienti con paginazione, ricerca e ordinamento
 */
export async function fetchClients(page = 1, limit = 50, search = '', searchField = 'all', sort = 'last_name_asc') {
    try {
        const params = new URLSearchParams();
        if (page > 1) params.set('page', page);
        if (limit !== 50) params.set('limit', limit);
        if (search.trim()) params.set('search', search.trim());
        if (searchField !== 'all') params.set('search_field', searchField);
        if (sort !== 'last_name_asc') params.set('sort', sort);
        
        const url = PATH + (params.toString() ? '?' + params.toString() : '');
        
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const text = await response.text();
        console.log('Clients response text:', text);
        
        // Controlla se la risposta HTTP è ok
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${text}`);
        }
        
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Errore parsing JSON clienti:', text, e);
            throw new Error('Risposta non valida dal server: ' + text);
        }
    } catch (error) {
        console.error('Errore caricamento clienti:', error);
        throw error;
    }
}

/**
 * Crea un nuovo cliente
 */
export async function createClient(clientData) {
    try {
        const response = await fetch(PATH, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(clientData)
        });
        
        const text = await response.text();
        console.log('Create client response:', text);
        
        // Controlla se la risposta HTTP è ok
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${text}`);
        }
        
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Errore parsing JSON createClient:', text, e);
            throw new Error('Risposta non valida dal server: ' + text);
        }
    } catch (error) {
        console.error('Errore creazione cliente:', error);
        throw error;
    }
}

/**
 * Aggiorna un cliente esistente
 */
export async function updateClient(clientData) {
    try {
        const response = await fetch(PATH, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(clientData)
        });
        
        const text = await response.text();
        console.log('Update client response:', text);
        
        // Controlla se la risposta HTTP è ok
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${text}`);
        }
        
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Errore parsing JSON updateClient:', text, e);
            throw new Error('Risposta non valida dal server: ' + text);
        }
    } catch (error) {
        console.error('Errore aggiornamento cliente:', error);
        throw error;
    }
}

/**
 * Elimina un cliente
 */
export async function deleteClient(clientId) {
    try {
        const response = await fetch(PATH, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: clientId })
        });
        
        const text = await response.text();
        console.log('Delete client response:', text);
        
        // Controlla se la risposta HTTP è ok
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${text}`);
        }
        
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Errore parsing JSON deleteClient:', text, e);
            throw new Error('Risposta non valida dal server: ' + text);
        }
    } catch (error) {
        console.error('Errore eliminazione cliente:', error);
        throw error;
    }
}

/**
 * Classe per gestire l'interfaccia utente dei clienti
 */
export class ClientsUI {
    constructor() {
        this.currentPage = 1;
        this.currentLimit = 50;
        this.currentSearch = '';
        this.currentSearchField = 'all';
        this.currentSort = 'last_name_asc';
        this.clients = [];
        this.pagination = {};
        
        this.initializeElements();
        this.attachEventListeners();
    }
    
    initializeElements() {
        this.tableBody = document.getElementById('client-table-body');
        this.searchInput = document.getElementById('client-search');
        this.searchFieldSelect = document.getElementById('search-field-select');
        this.searchBtn = document.getElementById('search-btn');
        this.sortSelect = document.getElementById('sort-select');
        this.prevBtn = document.getElementById('prev-btn');
        this.nextBtn = document.getElementById('next-btn');
        this.rangeLabel = document.getElementById('client-range-label');
        this.totalLabel = document.getElementById('client-total-label');
        this.addClientBtn = document.getElementById('add-client-btn');
        this.groupBtn = document.getElementById('group-btn');
        this.loadingIndicator = document.getElementById('loading-indicator');
        this.clientsTable = document.getElementById('clients-table');
        this.errorMessage = document.getElementById('error-message');
    }
    
    attachEventListeners() {
        if (this.searchBtn) {
            this.searchBtn.addEventListener('click', () => this.performSearch());
        }
        
        if (this.searchInput) {
            this.searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.performSearch();
                }
            });
        }
        
        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', () => this.goToPreviousPage());
        }
        
        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', () => this.goToNextPage());
        }
        
        if (this.addClientBtn) {
            this.addClientBtn.addEventListener('click', () => this.showAddClientDialog());
        }
        
        if (this.groupBtn) {
            this.groupBtn.addEventListener('click', () => this.toggleCertificateFilter());
        }
        
        if (this.sortSelect) {
            this.sortSelect.addEventListener('change', () => this.performSort());
        }
        
        if (this.searchFieldSelect) {
            this.searchFieldSelect.addEventListener('change', () => this.performSearch());
        }
    }
    
    async loadClients() {
        try {
            const response = await fetchClients(this.currentPage, this.currentLimit, this.currentSearch, this.currentSearchField, this.currentSort);
            
            if (response.success) {
                this.clients = response.data;
                this.pagination = response.pagination;
                this.renderTable();
                this.updatePaginationControls();
            } else {
                throw new Error(response.error || 'Errore sconosciuto');
            }
        } catch (error) {
            console.error('Errore caricamento clienti:', error);
            this.showError('Errore nel caricamento dei clienti: ' + error.message);
        }
    }
    
    renderTable() {
        if (!this.tableBody) return;
        
        // Pulisci la tabella
        this.tableBody.innerHTML = '';
        
        if (this.clients.length === 0) {
            const emptyRow = this.createTableRow(['Nessun cliente trovato'], 5, 'empty-row');
            this.tableBody.appendChild(emptyRow);
            return;
        }
        
        // Genera le righe con i dati dei clienti
        this.clients.forEach(client => {
            const row = this.createClientRow(client);
            this.tableBody.appendChild(row);
        });
    }
    
    createClientRow(client) {
        const row = document.createElement('tr');
        row.className = 'client-row';
        row.style.cursor = 'pointer';
        
        const q = (this.currentSearch || '').trim();
        const field = this.currentSearchField || 'all';
        const highlightFirst = q && (field === 'first_name' || field === 'name' || field === 'all');
        const highlightLast = q && (field === 'last_name' || field === 'name' || field === 'all');
        const highlightPhone = q && (field === 'phone' || field === 'all');
        const highlightNotes = q && (field === 'notes' || field === 'all');

        // Colonna Nome
        const nameCell = document.createElement('td');
        nameCell.innerHTML = highlightFirst
            ? this.getHighlightedHTML(client.first_name || '', q)
            : this.escapeHtml(client.first_name || '');
        nameCell.className = 'client-name-cell';
        
        // Colonna Cognome
        const surnameCell = document.createElement('td');
        surnameCell.innerHTML = highlightLast
            ? this.getHighlightedHTML(client.last_name || '', q)
            : this.escapeHtml(client.last_name || '');
        surnameCell.className = 'client-surname-cell';
        
        // Colonna Telefono
        const phoneCell = document.createElement('td');
        phoneCell.innerHTML = highlightPhone
            ? this.getHighlightedHTML(client.phone || '', q)
            : this.escapeHtml(client.phone || '');
        phoneCell.className = 'client-phone-cell';
        
        // Colonna Note
        const notesCell = document.createElement('td');
        const notes = client.notes || 'Nessuna nota';
        const notesDisplay = this.truncateText(notes, 50);
        notesCell.innerHTML = highlightNotes
            ? this.getHighlightedHTML(notesDisplay, q)
            : this.escapeHtml(notesDisplay);
        notesCell.title = notes;
        notesCell.className = 'client-notes-cell';
        
        // Colonna Certificato
        const certCell = document.createElement('td');
        certCell.textContent = client.has_certificate ? '✓' : '✗';
        certCell.className = 'client-cert-cell';
        certCell.style.textAlign = 'center';
        
        // Assembla la riga
        row.appendChild(nameCell);
        row.appendChild(surnameCell);
        row.appendChild(phoneCell);
        row.appendChild(notesCell);
        row.appendChild(certCell);
        
        // Event listener per selezione
        row.addEventListener('click', () => this.selectClient(client));
        
        // Hover effect
        row.addEventListener('mouseenter', () => {
            row.style.backgroundColor = '#f5f5f5';
        });
        
        row.addEventListener('mouseleave', () => {
            row.style.backgroundColor = '';
        });
        
        return row;
    }
    
    createTableRow(data, colspan = null, className = '') {
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
    
    updatePaginationControls() {
        if (this.prevBtn) {
            this.prevBtn.disabled = this.pagination.page <= 1;
        }
        
        if (this.nextBtn) {
            this.nextBtn.disabled = this.pagination.page >= this.pagination.totalPages;
        }
        
        if (this.rangeLabel) {
            this.rangeLabel.textContent = `${this.pagination.start}-${this.pagination.end}`;
        }
        
        if (this.totalLabel) {
            this.totalLabel.textContent = `Totale: ${this.pagination.total}`;
        }
    }
    
    performSearch() {
        this.currentSearch = this.searchInput ? this.searchInput.value.trim() : '';
        this.currentSearchField = this.searchFieldSelect ? this.searchFieldSelect.value : 'all';
        this.currentPage = 1;
        this.loadClients();
    }
    
    performSort() {
        this.currentSort = this.sortSelect ? this.sortSelect.value : 'last_name_asc';
        this.currentPage = 1;
        this.loadClients();
    }
    
    goToPreviousPage() {
        if (this.currentPage > 1) {
            this.currentPage--;
            this.loadClients();
        }
    }
    
    goToNextPage() {
        if (this.currentPage < this.pagination.totalPages) {
            this.currentPage++;
            this.loadClients();
        }
    }
    
    selectClient(client) {
        console.log('Cliente selezionato:', client);
        // Implementa logica per selezione cliente
    }
    
    showAddClientDialog() {
        console.log('Apri dialog aggiungi cliente');
        // Implementa dialog per aggiungere cliente
    }
    
    toggleCertificateFilter() {
        console.log('Toggle filtro certificato');
        // Implementa filtro per certificato
    }
    
    showLoading() {
        if (this.loadingIndicator) {
            this.loadingIndicator.style.display = 'block';
        }
    }
    
    hideLoading() {
        if (this.loadingIndicator) {
            this.loadingIndicator.style.display = 'none';
        }
    }
    
    showTable() {
        if (this.clientsTable) {
            this.clientsTable.style.display = 'table';
        }
    }
    
    hideTable() {
        if (this.clientsTable) {
            this.clientsTable.style.display = 'none';
        }
    }
    
    showError(message) {
        console.error('Errore UI:', message);
        if (this.errorMessage) {
            this.errorMessage.style.display = 'block';
            this.errorMessage.innerHTML = `<p>${this.escapeHtml(message)}</p>`;
        }
    }
    
    hideError() {
        if (this.errorMessage) {
            this.errorMessage.style.display = 'none';
        }
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    escapeRegExp(text) {
        return (text || '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
    
    getHighlightedHTML(text, query) {
        const source = String(text || '');
        const q = String(query || '').trim();
        if (!q) return this.escapeHtml(source);
        try {
            const regex = new RegExp(this.escapeRegExp(q), 'gi');
            let lastIndex = 0;
            let match;
            const parts = [];
            while ((match = regex.exec(source)) !== null) {
                const start = match.index;
                const end = start + match[0].length;
                parts.push(this.escapeHtml(source.slice(lastIndex, start)));
                parts.push('<span class="search-highlight">', this.escapeHtml(source.slice(start, end)), '</span>');
                lastIndex = end;
                if (regex.lastIndex === start) regex.lastIndex++; // avoid zero-length loops
            }
            parts.push(this.escapeHtml(source.slice(lastIndex)));
            return parts.join('');
        } catch (e) {
            // Fallback safe
            return this.escapeHtml(source);
        }
    }
    
    truncateText(text, maxLength) {
        if (!text || text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    }
}

// Inizializzazione automatica quando il DOM è pronto
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('client-table-body')) {
        const clientsUI = new ClientsUI();
        
        // Controlla parametri URL per ricerca automatica
        const urlParams = new URLSearchParams(window.location.search);
        const searchText = urlParams.get('search') || '';
        const searchField = urlParams.get('searchField') || 'name';
        
        // Se ci sono parametri di ricerca, impostali e esegui la ricerca
        if (searchText || searchField !== 'name') {
            // Imposta i valori nei controlli
            setTimeout(() => {
                if (clientsUI.searchInput && searchText) {
                    clientsUI.searchInput.value = searchText;
                    clientsUI.currentSearch = searchText;
                }
                
                if (clientsUI.searchFieldSelect && searchField) {
                    clientsUI.searchFieldSelect.value = searchField;
                    clientsUI.currentSearchField = searchField;
                }
                
                // Esegui la ricerca
                clientsUI.loadClients();
            }, 100);
        } else {
            // Caricamento normale
            clientsUI.loadClients();
        }
        
        // Rendi disponibile globalmente per debug
        window.clientsUI = clientsUI;
    }
});
