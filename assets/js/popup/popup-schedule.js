// ========== GESTIONE ORARI ==========
let scheduleIdCounter = 3;

// Lista orari semplificata
let schedulesList = [
    {
        id: 1,
        name: "Orario Bloccato",
        startDate: "",
        endDate: "",
        startTime: "09:00",
        endTime: "18:00",
        closureDays: ['sabato', 'domenica']
    },
    {
        id: 2,
        name: "Orario Estivo",
        startDate: "2025-06-01",
        endDate: "2025-08-31",
        startTime: "08:00",
        endTime: "17:00",
        closureDays: ['sabato', 'domenica']
    }
];

// ========== FUNZIONI PRINCIPALI ==========

// Inizializzazione popup orari
window.initSchedulePopup = function() {
    if (typeof PopupManager !== 'undefined' && PopupManager.currentPopup) {
        // Carica i dati salvati se esistono
        loadSchedulesFromStorage();
        
        // Mostra il popup
        PopupManager.currentPopup.innerHTML = getSchedulePopupContent();
        PopupManager.currentPopup.style.display = 'block';
        
        // Inizializza i listener degli eventi
        initializeScheduleEventListeners();
        
        // Aggiorna le statistiche
        updateScheduleStats();
    }
};

// Carica orari dal localStorage
function loadSchedulesFromStorage() {
    const saved = localStorage.getItem('calendar_schedules');
    if (saved) {
        try {
            const data = JSON.parse(saved);
            schedulesList = data.schedules || schedulesList;
            scheduleIdCounter = data.counter || scheduleIdCounter;
        } catch (e) {
            console.error('Errore nel caricamento orari:', e);
        }
    }
}

// Salva orari nel localStorage
function saveSchedulesToStorage() {
    try {
        localStorage.setItem('calendar_schedules', JSON.stringify({
            schedules: schedulesList,
            counter: scheduleIdCounter
        }));
        console.log('Orari salvati correttamente');
    } catch (e) {
        console.error('Errore nel salvataggio orari:', e);
    }
}

// ========== TEMPLATE HTML ==========

// Template HTML per il popup orari
function getSchedulePopupContent() {
    return `
        <div class="calendar-header">
            <h2>Gestione Orari</h2>
            <p>Aggiungi e modifica orari del sistema</p>
        </div>
        
        <div class="calendar-body">
            <div class="schedules-section">
                <div class="schedules-toolbar">
                    <button data-action="addNewSchedule" class="toolbar-btn btn-add">
                        <span>➕</span> Nuovo Orario
                    </button>
                    <button data-action="deleteSelectedSchedules" class="toolbar-btn btn-delete">
                        <span>🗑️</span> Elimina Selezionati
                    </button>
                </div>

                <div class="schedules-table-container">
                    <table class="excel-table" id="schedules-table">
                        <thead>
                            <tr>
                                <th class="select-col">
                                    <input type="checkbox" id="select-all-schedules" data-action="toggleSelectAll">
                                </th>
                                <th class="name-col">Nome Orario</th>
                                <th class="start-date-col">Data Inizio</th>
                                <th class="end-date-col">Data Fine</th>
                                <th class="start-time-col">Ora Inizio</th>
                                <th class="end-time-col">Ora Fine</th>
                                <th class="closure-days-col">Giorni Chiusura</th>
                                <th class="actions-col">Azioni</th>
                            </tr>
                        </thead>
                        <tbody id="schedules-list">
                            ${generateSchedulesRows()}
                        </tbody>
                    </table>
                </div>

                <div class="schedules-stats">
                    <span class="stat-item">Totale orari: <strong id="total-schedules">${schedulesList.length}</strong></span>
                    <span class="stat-item">Selezionati: <strong id="selected-schedules">0</strong></span>
                </div>
            </div>
        </div>
        
        <div class="calendar-footer">
            <button data-action="saveAllSchedules" class="save-btn">
                Salva Tutti gli Orari
            </button>
        </div>
    `;
}

// Genera le righe degli orari
function generateSchedulesRows() {
    if (!schedulesList.length) {
        return '<tr><td colspan="8" class="no-data">Nessun orario configurato</td></tr>';
    }
    
    return schedulesList.map(schedule => generateScheduleRow(schedule)).join('');
}

// ========== FUNZIONI DI SUPPORTO ==========

// Aggiunge un nuovo orario
window.addNewSchedule = function() {
    const newSchedule = {
        id: scheduleIdCounter,
        name: "",
        startDate: new Date().toISOString().split('T')[0],
        endDate: new Date(Date.now() + 30*24*60*60*1000).toISOString().split('T')[0], // +30 giorni
        startTime: "09:00",
        endTime: "18:00",
        closureDays: []
    };
    
    schedulesList.push(newSchedule);
    
    const tbody = document.getElementById('schedules-list');
    if (!tbody) {
        console.error('Elemento schedules-list non trovato');
        return;
    }
    
    const newRow = document.createElement('tr');
    newRow.setAttribute('data-schedule-id', scheduleIdCounter);
    newRow.innerHTML = `
        <td><input type="checkbox" class="row-select"></td>
        <td><input type="text" value="" placeholder="Nome orario..." class="cell-input new-schedule"></td>
        <td><input type="date" value="${newSchedule.startDate}" class="cell-input new-schedule"></td>
        <td><input type="date" value="${newSchedule.endDate}" class="cell-input new-schedule"></td>
        <td><input type="time" value="09:00" class="cell-input new-schedule"></td>
        <td><input type="time" value="18:00" class="cell-input new-schedule"></td>
        <td>
            <select multiple class="cell-select closure-days-select new-schedule">
                <option value="lunedi">Lunedì</option>
                <option value="martedi">Martedì</option>
                <option value="mercoledi">Mercoledì</option>
                <option value="giovedi">Giovedì</option>
                <option value="venerdi">Venerdì</option>
                <option value="sabato" selected>Sabato</option>
                <option value="domenica" selected>Domenica</option>
            </select>
        </td>
        <td class="actions-cell">
            <button data-action="deleteSchedule" data-schedule-id="${scheduleIdCounter}" class="action-btn btn-delete-single" title="Elimina">🗑️</button>
        </td>
    `;
    
    // Se c'è la riga "nessun dato", la rimuoviamo
    const noDataRow = tbody.querySelector('.no-data');
    if (noDataRow) {
        noDataRow.parentElement.remove();
    }
    
    tbody.appendChild(newRow);
    
    scheduleIdCounter++;
    updateScheduleStats();
    
    // Focus sul nome del nuovo orario
    const nameInput = newRow.querySelector('input[type="text"]');
    if (nameInput) {
        nameInput.focus();
        nameInput.select();
    }
};

// Genera una singola riga di orario
function generateScheduleRow(schedule) {
    // Verifica se è la prima riga e se ha date vuote (per bloccare i valori)
    const isFirstRowWithEmptyDates = schedule.id === 1 && (!schedule.startDate || !schedule.endDate);
    const disabledAttr = isFirstRowWithEmptyDates ? 'disabled' : '';
    const blockedClass = isFirstRowWithEmptyDates ? 'blocked-row' : '';
    
    // Se è bloccata, mostra celle vuote per le date
    const startDateValue = isFirstRowWithEmptyDates ? '' : schedule.startDate;
    const endDateValue = isFirstRowWithEmptyDates ? '' : schedule.endDate;
    
    return `
        <tr data-schedule-id="${schedule.id}" class="${blockedClass}">
            <td><input type="checkbox" class="row-select"></td>
            <td><input type="text" value="${schedule.name}" placeholder="Nome orario..." class="cell-input" ${disabledAttr}></td>
            <td>${isFirstRowWithEmptyDates ? '<span class="empty-cell">-</span>' : `<input type="date" value="${startDateValue}" class="cell-input">`}</td>
            <td>${isFirstRowWithEmptyDates ? '<span class="empty-cell">-</span>' : `<input type="date" value="${endDateValue}" class="cell-input">`}</td>
            <td><input type="time" value="${schedule.startTime}" class="cell-input"></td>
            <td><input type="time" value="${schedule.endTime}" class="cell-input"></td>
            <td>
                <select multiple class="cell-select closure-days-select">
                    <option value="lunedi" ${schedule.closureDays.includes('lunedi') ? 'selected' : ''}>Lunedì</option>
                    <option value="martedi" ${schedule.closureDays.includes('martedi') ? 'selected' : ''}>Martedì</option>
                    <option value="mercoledi" ${schedule.closureDays.includes('mercoledi') ? 'selected' : ''}>Mercoledì</option>
                    <option value="giovedi" ${schedule.closureDays.includes('giovedi') ? 'selected' : ''}>Giovedì</option>
                    <option value="venerdi" ${schedule.closureDays.includes('venerdi') ? 'selected' : ''}>Venerdì</option>
                    <option value="sabato" ${schedule.closureDays.includes('sabato') ? 'selected' : ''}>Sabato</option>
                    <option value="domenica" ${schedule.closureDays.includes('domenica') ? 'selected' : ''}>Domenica</option>
                </select>
            </td>
            <td class="actions-cell">
                ${isFirstRowWithEmptyDates ? '' : `<button data-action="deleteSchedule" data-schedule-id="${schedule.id}" class="action-btn btn-delete-single" title="Elimina">🗑️</button>`}
            </td>
        </tr>
    `;
}

// Aggiorna stato del seleziona tutto
function updateSelectAllState() {
    const selectAllCheckbox = document.getElementById('select-all-schedules');
    if (!selectAllCheckbox) return;
    
    const checkboxes = document.querySelectorAll('.row-select');
    const checkedCount = document.querySelectorAll('.row-select:checked').length;
    
    selectAllCheckbox.checked = checkedCount === checkboxes.length && checkboxes.length > 0;
    updateScheduleStats();
}

// Toggle seleziona tutto
window.toggleSelectAll = function() {
    const selectAll = document.getElementById('select-all-schedules');
    const checkboxes = document.querySelectorAll('.row-select');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateScheduleStats();
}

// Elimina orario singolo
window.deleteSchedule = function(scheduleId) {
    // Blocca eliminazione della prima riga se ha date vuote
    if (scheduleId === 1) {
        const firstSchedule = schedulesList.find(s => s.id === 1);
        if (firstSchedule && (!firstSchedule.startDate || !firstSchedule.endDate)) {
            alert('Non è possibile eliminare l\'orario bloccato con date vuote');
            return;
        }
    }
    
    if (confirm('Sei sicuro di voler eliminare questo orario?')) {
        const index = schedulesList.findIndex(s => s.id === parseInt(scheduleId));
        if (index > -1) {
            schedulesList.splice(index, 1);
            
            const row = document.querySelector(`tr[data-schedule-id="${scheduleId}"]`);
            if (row) {
                row.remove();
            }
            
            // Se non ci sono più orari, mostra messaggio
            const tbody = document.getElementById('schedules-list');
            if (tbody && tbody.children.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="no-data">Nessun orario configurato</td></tr>';
            }
            
            updateScheduleStats();
        }
    }
};

// Elimina orari selezionati
window.deleteSelectedSchedules = function() {
    const selectedCheckboxes = document.querySelectorAll('.row-select:checked');
    
    if (selectedCheckboxes.length === 0) {
        alert('Seleziona almeno un orario da eliminare');
        return;
    }
    
    // Controlla se tra gli selezionati c'è la prima riga bloccata
    const idsToDelete = Array.from(selectedCheckboxes).map(cb => {
        const row = cb.closest('tr');
        return parseInt(row.getAttribute('data-schedule-id'));
    });
    
    // Verifica se c'è l'orario bloccato tra quelli selezionati
    const hasBlockedSchedule = idsToDelete.includes(1);
    if (hasBlockedSchedule) {
        const firstSchedule = schedulesList.find(s => s.id === 1);
        if (firstSchedule && (!firstSchedule.startDate || !firstSchedule.endDate)) {
            alert('Non è possibile eliminare l\'orario bloccato con date vuote dalla selezione');
            return;
        }
    }
    
    if (confirm(`Sei sicuro di voler eliminare ${selectedCheckboxes.length} orari selezionati?`)) {
        // Rimuovi dalla lista
        schedulesList = schedulesList.filter(s => !idsToDelete.includes(s.id));
        
        // Rimuovi dal DOM
        idsToDelete.forEach(id => {
            const row = document.querySelector(`tr[data-schedule-id="${id}"]`);
            if (row) row.remove();
        });
        
        // Se non ci sono più orari, mostra messaggio
        const tbody = document.getElementById('schedules-list');
        if (tbody && tbody.children.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="no-data">Nessun orario configurato</td></tr>';
        }
        
        updateScheduleStats();
        
        // Resetta seleziona tutto
        const selectAll = document.getElementById('select-all-schedules');
        if (selectAll) selectAll.checked = false;
    }
};

// Aggiorna statistiche
function updateScheduleStats() {
    const totalSchedules = document.querySelectorAll('#schedules-list tr').length;
    const selectedSchedules = document.querySelectorAll('.row-select:checked').length;
    
    const totalElement = document.getElementById('total-schedules');
    const selectedElement = document.getElementById('selected-schedules');
    
    if (totalElement) {
        // Se c'è la riga "nessun dato", il totale è 0
        const noDataRow = document.querySelector('#schedules-list .no-data');
        totalElement.textContent = noDataRow ? '0' : schedulesList.length;
    }
    
    if (selectedElement) {
        selectedElement.textContent = selectedSchedules;
    }
}

// Sincronizza tutti gli orari dal DOM
function syncSchedulesFromUI() {
    const rows = document.querySelectorAll('#schedules-list tr[data-schedule-id]');
    
    rows.forEach(row => {
        const scheduleId = parseInt(row.getAttribute('data-schedule-id'));
        
        // Skip sincronizzazione per la prima riga se è bloccata
        if (scheduleId === 1) {
            const firstSchedule = schedulesList.find(s => s.id === 1);
            if (firstSchedule && (!firstSchedule.startDate || !firstSchedule.endDate)) {
                console.log('Saltata sincronizzazione riga bloccata ID:', scheduleId);
                return; // Skip sincronizzazione per riga bloccata
            }
        }
        
        const inputs = row.querySelectorAll('.cell-input, .cell-select');
        
        const scheduleData = {
            id: scheduleId,
            name: inputs[0]?.value?.trim() || '',
            startDate: inputs[1]?.value || new Date().toISOString().split('T')[0],
            endDate: inputs[2]?.value || new Date(Date.now() + 30*24*60*60*1000).toISOString().split('T')[0],
            startTime: inputs[3]?.value || '09:00',
            endTime: inputs[4]?.value || '18:00',
            closureDays: inputs[5] ? Array.from(inputs[5].selectedOptions).map(option => option.value) : []
        };
        
        // Trova e aggiorna l'orario nella lista
        const scheduleIndex = schedulesList.findIndex(s => s.id === scheduleId);
        if (scheduleIndex !== -1) {
            schedulesList[scheduleIndex] = scheduleData;
        }
    });
}

// Salva tutti gli orari
window.saveAllSchedules = function() {
    try {
        // Sincronizza i dati dal DOM
        syncSchedulesFromUI();
        
        // Salva nel localStorage
        saveSchedulesToStorage();
        
        alert('Tutti gli orari sono stati salvati correttamente!');
        
        // Rimuovi evidenziazione dai nuovi orari
        document.querySelectorAll('.new-schedule').forEach(element => {
            element.classList.remove('new-schedule');
        });
        
        // Chiudi il popup se esiste
        if (typeof PopupManager !== 'undefined' && PopupManager.closePopup) {
            PopupManager.closePopup();
        }
    } catch (error) {
        console.error('Errore nel salvataggio:', error);
        alert('Si è verificato un errore durante il salvataggio degli orari.');
    }
};

// ========== INIZIALIZZAZIONE EVENTI ==========

function initializeScheduleEventListeners() {
    const popupElement = PopupManager?.currentPopup;
    if (!popupElement) return;
    
    // Event delegation per i pulsanti
    popupElement.addEventListener('click', function(e) {
        const target = e.target.closest('[data-action]');
        if (!target) return;
        
        const action = target.getAttribute('data-action');
        console.log('Azione popup orari:', action);
        
        switch (action) {
            case 'addNewSchedule':
                addNewSchedule();
                break;
            case 'deleteSelectedSchedules':
                deleteSelectedSchedules();
                break;
            case 'deleteSchedule':
                const scheduleId = target.getAttribute('data-schedule-id');
                if (scheduleId) deleteSchedule(parseInt(scheduleId));
                break;
            case 'toggleSelectAll':
                toggleSelectAll();
                break;
            case 'saveAllSchedules':
                saveAllSchedules();
                break;
        }
        
        e.preventDefault();
        e.stopPropagation();
    });
    
    // Event listener per auto-salvataggio su input/change
    popupElement.addEventListener('input', function(e) {
        if (e.target.classList.contains('cell-input') || e.target.classList.contains('cell-select')) {
            // Auto-salva dopo 500ms dall'ultimo input
            clearTimeout(window.scheduleAutoSaveTimeout);
            window.scheduleAutoSaveTimeout = setTimeout(() => {
                syncSchedulesFromUI();
                // Non salvare automaticamente, solo sincronizzare
            }, 500);
        }
    });
    
    popupElement.addEventListener('change', function(e) {
        if (e.target.classList.contains('cell-select')) {
            syncSchedulesFromUI();
        }
        if (e.target.classList.contains('row-select')) {
            updateSelectAllState();
        }
    });
    
    console.log('Event listeners per popup orari inizializzati');
}
