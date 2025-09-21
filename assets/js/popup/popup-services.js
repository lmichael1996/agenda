// ========== GESTIONE SERVIZI ==========
let serviceIdCounter = 4;

// Template HTML per il popup servizi
function getServicesPopupContent() {
    return `
        <div class="calendar-header">
            <h2>Gestione Servizi</h2>
            <p>Aggiungi e modifica servizi disponibili</p>
        </div>
        
        <div class="calendar-body">
            <div class="services-section">
                <div class="services-toolbar">
                    <button data-action="addNewService" class="toolbar-btn btn-add">
                        <span>➕</span> Nuovo Servizio
                    </button>
                    <button data-action="deleteSelectedServices" class="toolbar-btn btn-delete">
                        <span>🗑️</span> Elimina Selezionati
                    </button>
                </div>

                <div class="services-table-container">
                    <table class="excel-table" id="services-table">
                        <thead>
                            <tr>
                                <th class="select-col">
                                    <input type="checkbox" id="select-all-services" data-action="toggleSelectAllServices">
                                </th>
                                <th class="service-name-col">Nome Servizio</th>
                                <th class="price-col">Prezzo (€)</th>
                                <th class="duration-col">Tempo (min)</th>
                                <th class="description-col">Descrizione</th>
                                <th class="status-col">Stato</th>
                                <th class="actions-col">Azioni</th>
                            </tr>
                        </thead>
                        <tbody id="services-list">
                            <tr data-service-id="1">
                                <td><input type="checkbox" class="row-select-service"></td>
                                <td><input type="text" value="Consulenza Personalizzata" class="cell-input"></td>
                                <td><input type="number" value="50.00" step="0.01" min="0" class="cell-input price-input"></td>
                                <td><input type="number" value="60" min="1" max="480" class="cell-input duration-input"></td>
                                <td><input type="text" value="Consulenza individuale personalizzata" class="cell-input"></td>
                                <td>
                                    <select class="cell-select status-select">
                                        <option value="attivo" selected>Attivo</option>
                                        <option value="inattivo">Inattivo</option>
                                        <option value="sospeso">Sospeso</option>
                                    </select>
                                </td>
                                <td class="actions-cell">
                                    <button data-action="deleteService" data-service-id="1" class="action-btn btn-delete-single" title="Elimina">🗑️</button>
                                </td>
                            </tr>
                            <tr data-service-id="2">
                                <td><input type="checkbox" class="row-select-service"></td>
                                <td><input type="text" value="Servizio Standard" class="cell-input"></td>
                                <td><input type="number" value="30.00" step="0.01" min="0" class="cell-input price-input"></td>
                                <td><input type="number" value="45" min="1" max="480" class="cell-input duration-input"></td>
                                <td><input type="text" value="Servizio base standard" class="cell-input"></td>
                                <td>
                                    <select class="cell-select status-select">
                                        <option value="attivo" selected>Attivo</option>
                                        <option value="inattivo">Inattivo</option>
                                        <option value="sospeso">Sospeso</option>
                                    </select>
                                </td>
                                <td class="actions-cell">
                                    <button data-action="deleteService" data-service-id="2" class="action-btn btn-delete-single" title="Elimina">🗑️</button>
                                </td>
                            </tr>
                            <tr data-service-id="3">
                                <td><input type="checkbox" class="row-select-service"></td>
                                <td><input type="text" value="Pacchetto Premium" class="cell-input"></td>
                                <td><input type="number" value="100.00" step="0.01" min="0" class="cell-input price-input"></td>
                                <td><input type="number" value="120" min="1" max="480" class="cell-input duration-input"></td>
                                <td><input type="text" value="Pacchetto completo premium" class="cell-input"></td>
                                <td>
                                    <select class="cell-select status-select">
                                        <option value="attivo" selected>Attivo</option>
                                        <option value="inattivo">Inattivo</option>
                                        <option value="sospeso">Sospeso</option>
                                    </select>
                                </td>
                                <td class="actions-cell">
                                    <button data-action="deleteService" data-service-id="3" class="action-btn btn-delete-single" title="Elimina">🗑️</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="services-stats">
                    <span class="stat-item">Totale servizi: <strong id="total-services">3</strong></span>
                    <span class="stat-item">Selezionati: <strong id="selected-services">0</strong></span>
                </div>
            </div>
        </div>
        
        <div class="calendar-footer">
            <button data-action="saveAllServices" class="save-btn">
                Salva Tutti i Servizi
            </button>
        </div>
    `;
}

// Funzioni gestione servizi
window.addNewService = function() {
    const tbody = document.getElementById('services-list');
    if (!tbody) {
        console.error('Elemento services-list non trovato');
        return;
    }
    
    const newRow = document.createElement('tr');
    newRow.setAttribute('data-service-id', serviceIdCounter);
    
    newRow.innerHTML = `
        <td><input type="checkbox" class="row-select-service"></td>
        <td><input type="text" value="" placeholder="Nome servizio" class="cell-input new-service"></td>
        <td><input type="number" value="0.00" step="0.01" min="0" placeholder="0.00" class="cell-input price-input new-service"></td>
        <td><input type="number" value="30" min="1" max="480" placeholder="30" class="cell-input duration-input new-service"></td>
        <td><input type="text" value="" placeholder="Descrizione servizio" class="cell-input new-service"></td>
        <td>
            <select class="cell-select status-select new-service">
                <option value="attivo" selected>Attivo</option>
                <option value="inattivo">Inattivo</option>
                <option value="sospeso">Sospeso</option>
            </select>
        </td>
        <td class="actions-cell">
            <button data-action="deleteService" data-service-id="${serviceIdCounter}" class="action-btn btn-delete-single" title="Elimina">🗑️</button>
        </td>
    `;
    
    tbody.appendChild(newRow);
    
    // Focus sul nome del nuovo servizio
    newRow.querySelector('input[type="text"]').focus();
    
    serviceIdCounter++;
    updateServiceStats();
};

window.deleteService = function(serviceId) {
    if (confirm('Sei sicuro di voler eliminare questo servizio?')) {
        const row = document.querySelector(`tr[data-service-id="${serviceId}"]`);
        if (row) {
            row.remove();
            updateServiceStats();
        }
    }
};

window.deleteSelectedServices = function() {
    const selectedRows = document.querySelectorAll('.row-select-service:checked');
    if (selectedRows.length === 0) {
        alert('Seleziona almeno un servizio da eliminare');
        return;
    }
    
    if (confirm(`Sei sicuro di voler eliminare ${selectedRows.length} servizi selezionati?`)) {
        selectedRows.forEach(checkbox => {
            checkbox.closest('tr').remove();
        });
        updateServiceStats();
        const selectAllCheckbox = document.getElementById('select-all-services');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
        }
    }
};

window.toggleSelectAllServices = function() {
    const selectAll = document.getElementById('select-all-services');
    const rowSelects = document.querySelectorAll('.row-select-service');
    
    if (selectAll && rowSelects.length > 0) {
        rowSelects.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
        updateServiceStats();
    }
};

window.saveAllServices = function() {
    const rows = document.querySelectorAll('#services-list tr');
    const services = [];
    
    rows.forEach(row => {
        const serviceId = row.getAttribute('data-service-id');
        const inputs = row.querySelectorAll('.cell-input, .cell-select, .cell-color');
        
        const service = {
            id: serviceId,
            nome: inputs[0]?.value?.trim() || '',
            prezzo: parseFloat(inputs[1]?.value) || 0,
            tempoMinuti: parseInt(inputs[2]?.value) || 30,
            descrizione: inputs[3]?.value?.trim() || '',
            stato: inputs[4]?.value || 'attivo'
        };
        
        // Validazione base
        if (service.nome && service.prezzo >= 0 && service.tempoMinuti > 0) {
            services.push(service);
        }
    });
    
    localStorage.setItem('servicesData', JSON.stringify(services));
    alert(`✅ Salvati ${services.length} servizi!`);
    
    // Rimuovi evidenziazione dai nuovi servizi
    document.querySelectorAll('.new-service').forEach(element => {
        element.classList.remove('new-service');
    });
};

function updateServiceStats() {
    const totalServices = document.querySelectorAll('#services-list tr').length;
    const selectedServices = document.querySelectorAll('.row-select-service:checked').length;
    
    const totalEl = document.getElementById('total-services');
    const selectedEl = document.getElementById('selected-services');
    
    if (totalEl) totalEl.textContent = totalServices;
    if (selectedEl) selectedEl.textContent = selectedServices;
}

// Carica dati servizi salvati
function loadSavedServices() {
    const savedServices = localStorage.getItem('servicesData');
    if (savedServices) {
        try {
            const services = JSON.parse(savedServices);
            // Logica per ripopolare la tabella con i dati salvati
            console.log('Servizi caricati:', services);
        } catch (error) {
            console.error('Errore nel caricamento dei servizi:', error);
        }
    }
}

// Inizializza quando il popup servizi viene aperto
document.addEventListener('DOMContentLoaded', function() {
    // Auto-carica i servizi quando viene aperto il popup
    document.addEventListener('click', function(e) {
        if (e.target.getAttribute('data-action') === 'openPopup' && 
            e.target.getAttribute('data-popup-type') === 'service') {
            setTimeout(loadSavedServices, 100);
        }
    });
});