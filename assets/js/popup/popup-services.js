// ========== GESTIONE SERVIZI ==========
let serviceIdCounter = 4;

// Lista servizi centralizzata
let servicesList = [
    {
        id: 1,
        name: "Consulenza Personalizzata",
        price: 50.00,
        durationMinutes: 60,
        description: "Consulenza individuale personalizzata",
        status: "attivo"
    },
    {
        id: 2,
        name: "Servizio Standard", 
        price: 30.00,
        durationMinutes: 45,
        description: "Servizio base standard",
        status: "attivo"
    },
    {
        id: 3,
        name: "Pacchetto Premium",
        price: 100.00,
        durationMinutes: 120,
        description: "Pacchetto completo premium", 
        status: "attivo"
    }
];

// Genera HTML per una singola riga servizio
function generateServiceRow(service) {
    // Protezione contro valori undefined
    const safeService = {
        id: service.id || 0,
        name: service.name || '',
        price: Number(service.price) || 0,
        durationMinutes: Number(service.durationMinutes) || 15,
        description: service.description || '',
        status: service.status || 'attivo'
    };
    
    return `
        <tr data-service-id="${safeService.id}">
            <td><input type="checkbox" class="row-select-service"></td>
            <td><input type="text" value="${safeService.name}" class="cell-input"></td>
            <td><input type="number" value="${safeService.price.toFixed(2)}" step="0.01" min="0" class="cell-input price-input"></td>
            <td><input type="number" value="${safeService.durationMinutes}" step="15" min="15" max="480" class="cell-input duration-input"></td>
            <td><input type="text" value="${safeService.description}" class="cell-input"></td>
            <td>
                <select class="cell-select status-select">
                    <option value="attivo" ${safeService.status === 'attivo' ? 'selected' : ''}>Attivo</option>
                    <option value="inattivo" ${safeService.status === 'inattivo' ? 'selected' : ''}>Inattivo</option>
                    <option value="sospeso" ${safeService.status === 'sospeso' ? 'selected' : ''}>Sospeso</option>
                </select>
            </td>
            <td class="actions-cell">
                <button data-action="deleteService" data-service-id="${safeService.id}" class="action-btn btn-delete-single" title="Elimina">🗑️</button>
            </td>
        </tr>
    `;
}

// Genera HTML per tutte le righe servizi
function generateServicesRows() {
    // Verifica di sicurezza: se servicesList è vuoto, ripristina i dati di default
    if (!servicesList || !Array.isArray(servicesList) || servicesList.length === 0) {
        console.warn('servicesList vuoto, ripristino dati di default');
        servicesList = [
            {
                id: 1,
                name: "Consulenza Personalizzata",
                price: 50.00,
                durationMinutes: 60,
                description: "Consulenza individuale personalizzata",
                status: "attivo"
            },
            {
                id: 2,
                name: "Servizio Standard", 
                price: 30.00,
                durationMinutes: 45,
                description: "Servizio base standard",
                status: "attivo"
            },
            {
                id: 3,
                name: "Pacchetto Premium",
                price: 100.00,
                durationMinutes: 120,
                description: "Pacchetto completo premium", 
                status: "attivo"
            }
        ];
        serviceIdCounter = 4;
    }
    
    return servicesList.map(service => generateServiceRow(service)).join('');
}

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
                            ${generateServicesRows()}
                        </tbody>
                    </table>
                </div>

                <div class="services-stats">
                    <span class="stat-item">Totale servizi: <strong id="total-services">${servicesList.length}</strong></span>
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
    const newService = {
        id: serviceIdCounter,
        name: "",
        price: 0.00,
        durationMinutes: 15,
        description: "",
        status: "attivo"
    };
    
    servicesList.push(newService);
    
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
        <td><input type="number" value="10.00" step="1.00" min="0" class="cell-input price-input new-service"></td>
        <td><input type="number" value="15" step="15" min="15" max="480" placeholder="30" class="cell-input duration-input new-service"></td>
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
    newRow.querySelector('input[type="text"]').focus();
    
    serviceIdCounter++;
    updateServiceStats();
};

window.deleteService = function(serviceId) {
    if (confirm('Sei sicuro di voler eliminare questo servizio?')) {
        // Rimuovi dalla variabile servicesList
        servicesList = servicesList.filter(service => service.id != serviceId);
        
        // Rimuovi dalla UI
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
        const selectedIds = [];
        selectedRows.forEach(checkbox => {
            const serviceId = checkbox.closest('tr').getAttribute('data-service-id');
            selectedIds.push(parseInt(serviceId));
            checkbox.closest('tr').remove();
        });
        
        // Rimuovi dalla variabile servicesList
        servicesList = servicesList.filter(service => !selectedIds.includes(service.id));
        
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
    // Sincronizza dati dalla UI alla variabile servicesList
    syncServicesFromUI();
    
    // Salva la lista servizi aggiornata
    localStorage.setItem('servicesData', JSON.stringify(servicesList));
    alert(`✅ Salvati ${servicesList.length} servizi!`);
    
    // Rimuovi evidenziazione dai nuovi servizi
    document.querySelectorAll('.new-service').forEach(element => {
        element.classList.remove('new-service');
    });
};

// Sincronizza dati dalla UI alla variabile servicesList
function syncServicesFromUI() {
    const rows = document.querySelectorAll('#services-list tr');
    
    rows.forEach(row => {
        const serviceId = parseInt(row.getAttribute('data-service-id'));
        const inputs = row.querySelectorAll('.cell-input, .cell-select');
        
        const serviceData = {
            id: serviceId,
            name: inputs[0]?.value?.trim() || '',
            price: parseFloat(inputs[1]?.value) || 0,
            durationMinutes: parseInt(inputs[2]?.value) || 15,
            description: inputs[3]?.value?.trim() || '',
            status: inputs[4]?.value || 'attivo'
        };
        
        // Trova e aggiorna il servizio nella lista
        const serviceIndex = servicesList.findIndex(s => s.id === serviceId);
        if (serviceIndex !== -1) {
            servicesList[serviceIndex] = serviceData;
        }
    });
}

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
            const parsedServices = JSON.parse(savedServices);
            
            // Solo sostituisci se ci sono effettivamente servizi validi
            if (Array.isArray(parsedServices) && parsedServices.length > 0) {
                servicesList = parsedServices;
                
                // Aggiorna il counter con l'ID più alto + 1
                const maxId = Math.max(...servicesList.map(s => s.id), 0);
                serviceIdCounter = maxId + 1;
                
                console.log('Servizi caricati dal localStorage:', servicesList);
            } else {
                console.log('localStorage vuoto o non valido, mantengo dati di default');
            }
            
        } catch (error) {
            console.error('Errore nel parsing del localStorage:', error);
            console.log('Mantengo dati di default');
        }
    } else {
        console.log('Nessun dato nel localStorage, uso dati di default');
    }
    
    // Rigenera sempre la tabella (con dati caricati o di default)
    const tbody = document.getElementById('services-list');
    if (tbody) {
        tbody.innerHTML = generateServicesRows();
        updateServiceStats();
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