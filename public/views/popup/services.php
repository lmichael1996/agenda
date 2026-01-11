<?php
/**
 * Popup per la gestione dei servizi - Finestra separata
 * Versione organizzata basata su popup.css
 */

// Carica configurazione e controlli di sicurezza
require_once '../../../src/Auth/AccessControl.php';

// Il file access-control.php gestisce automaticamente tutti i controlli per i popup
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Servizi - Agenda</title>
    <link rel="stylesheet" href="../../assets/css/list-popup.css">
    <link rel="stylesheet" href="../../assets/css/scrollbar.css">
</head>
<body>
    <div class="popup-window-container">
        <div class="window-header">
            <span class="header-title">Gestione Servizi</span>
        </div>

        <div class="calendar-body">
            <div class="schedules-section">

                <div class="schedules-toolbar">
                    <button id="add-service-btn" class="toolbar-btn">Nuovo Servizio</button>
                    <button id="delete-selected-btn" class="toolbar-btn">Elimina Selezionati</button>
                </div>

                <div class="schedules-table-container">
                    <table class="excel-table" id="services-table">
                        <thead>
                            <tr>
                                <th class="select-col"><input type="checkbox" id="select-all-services"></th>
                                <th class="service-name-col">Nome Servizio</th>
                                <th class="price-col">Prezzo (€)</th>
                                <th class="duration-col">Durata (min)</th>
                                <th class="notification-col">Notifica (gg)</th>
                                <th class="description-col">Descrizione</th>
                                <th class="actions-col">Azioni</th>
                            </tr>
                        </thead>
                        <tbody id="services-list">
                            <!-- I dati vengono caricati via JS -->
                        </tbody>
                    </table>
                </div>

                <div class="services-stats">
                    <span>Totale servizi: <strong id="total-services">0</strong></span>
                    <span>Selezionati: <strong id="selected-services">0</strong></span>
                </div>

                <div class="save-btn-container">
                    <button id="save-all-btn" class="save-btn">Salva Tutti i Servizi</button>
                </div>

            </div>
        </div>

    </div>

<script>
    // ========== GESTIONE SERVIZI ==========
    
    let servicesList = [];
    let serviceIdCounter = 1;

    // ========== API FUNCTIONS ==========
    
    async function loadServicesFromDb() {
        try {
            console.log('Inizio caricamento servizi...');
            const response = await fetch('../../../src/Api/api.php?endpoint=services');
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            console.log('Response headers:', response.headers.get('content-type'));
            
            const text = await response.text();
            console.log('Response text length:', text.length);
            console.log('Response text:', text);
            
            if (!text || text.trim() === '') {
                console.error('Risposta vuota dal server');
                alert('Errore: risposta vuota dal server');
                return false;
            }
            
            let data;
            try {
                data = JSON.parse(text);
            } catch (jsonError) {
                console.error('Errore parsing JSON:', jsonError);
                console.error('Testo ricevuto:', text);
                alert('Errore parsing JSON. Vedi console per dettagli.');
                return false;
            }
            
            console.log('Risposta caricamento servizi:', data);
            
            if (data.success && Array.isArray(data.services)) {
                // Escludi il servizio default (id=1) dalla visualizzazione
                servicesList = data.services.filter(s => s.id != 1);
                serviceIdCounter = servicesList.length ? Math.max(...servicesList.map(s => s.id)) + 1 : 2;
                console.log('Servizi caricati (escluso default):', servicesList.length);
                if (servicesList.length > 0) {
                    console.log('Primo servizio:', servicesList[0]);
                }
                return true;
            } else {
                console.error('Errore caricamento servizi:', data);
                alert('Errore caricamento servizi dal database: ' + (data.error || 'Errore sconosciuto'));
                return false;
            }
        } catch (e) {
            console.error('Errore connessione API:', e);
            alert('Errore di connessione al server: ' + e.message);
            return false;
        }
    }
    
    async function saveServicesToDb(services) {
        try {
            console.log('Invio richiesta PUT con servizi:', services);
            const url = '../../../src/Api/api.php?endpoint=services';
            console.log('URL chiamata:', url);
            
            const response = await fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ services: services })
            });
            
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);
            
            if (!response.ok) {
                const text = await response.text();
                console.error('Response text:', text);
                return { success: false, error: `HTTP ${response.status}: ${text}` };
            }
            
            const data = await response.json();
            console.log('Risposta salvataggio:', data);
            
            return data;
        } catch (e) {
            console.error('Errore salvataggio:', e);
            console.error('Stack:', e.stack);
            return { success: false, error: 'Errore di connessione: ' + e.message };
        }
    }
    
    async function createServiceInDb(service) {
        try {
            const response = await fetch('../../../src/Api/api.php?endpoint=services', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    name: service.name,
                    price: service.price,
                    durationMinutes: service.durationMinutes,
                    description: service.description
                })
            });
            
            const data = await response.json();
            return data;
        } catch (e) {
            console.error('Errore creazione servizio:', e);
            return { success: false, error: 'Errore di connessione' };
        }
    }
    
    async function deleteServiceFromDb(id) {
        try {
            const response = await fetch(`../../../src/Api/api.php?endpoint=services&id=${id}`, {
                method: 'DELETE'
            });
            
            const data = await response.json();
            return data;
        } catch (e) {
            console.error('Errore eliminazione servizio:', e);
            return { success: false, error: 'Errore di connessione' };
        }
    }

    // ========== UI FUNCTIONS ==========

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function generateServiceRow(service) {
        const hasNotification = service.notification_days !== null && service.notification_days !== undefined;
        const notificationDays = hasNotification ? service.notification_days : 90;
        
        return `
            <tr data-service-id="${service.id}">
                <td><input type="checkbox" class="row-select"></td>
                <td><input type="text" value="${escapeHtml(service.name)}" class="cell-input name-input"></td>
                <td><input type="number" value="${Number(service.price).toFixed(2)}" step="1" min="0" max="9999.99" class="cell-input price-input"></td>
                <td><input type="number" value="${service.durationMinutes || service.duration}" step="15" min="15" max="480" class="cell-input duration-input"></td>
                <td>
                    <div style="display: flex; align-items: center; gap: 6px; justify-content: center;">
                        <label class="notification-toggle" title="Attiva/Disattiva notifica promemoria">
                            <input type="checkbox" class="notification-enabled" ${hasNotification ? 'checked' : ''}>
                            <span class="toggle-slider"></span>
                        </label>
                        <input type="number" value="${notificationDays}" step="1" min="1" max="365" class="notification-input" title="Giorni per promemoria" ${!hasNotification ? 'disabled' : ''} style="width: 70px;">
                    </div>
                </td>
                <td><textarea class="cell-textarea description-input" rows="2" placeholder="Descrizione dettagliata...">${escapeHtml(service.description)}</textarea></td>
                <td class="actions-cell"><button class="action-btn btn-delete-single" data-service-id="${service.id}" title="Elimina">✘</button></td>
            </tr>
        `;
    }

    function renderServicesTable() {
        console.log('=== RENDER TABLE ===');
        const tbody = document.getElementById('services-list');
        console.log('tbody element:', tbody);
        console.log('servicesList:', servicesList);
        
        if (!tbody) {
            console.error('Elemento tbody non trovato!');
            return;
        }
        
        if (!servicesList.length) {
            console.log('Nessun servizio da mostrare');
            tbody.innerHTML = '<tr><td colspan="7">Nessun servizio configurato</td></tr>';
            updateSelectionStats();
            return;
        }
        
        console.log('Generazione righe per', servicesList.length, 'servizi');
        const rows = servicesList.map(generateServiceRow).join('');
        console.log('HTML generato (primi 200 char):', rows.substring(0, 200));
        
        tbody.innerHTML = rows;
        console.log('Righe inserite nel DOM');
        updateSelectionStats();
    }

    function updateSelectionStats() {
        const totalServices = servicesList.length;
        const selectedServices = document.querySelectorAll('.row-select:checked').length;
        
        document.getElementById('total-services').textContent = totalServices;
        document.getElementById('selected-services').textContent = selectedServices;
    }

    function syncRowToModel(row, id) {
        const service = servicesList.find(s => s.id == id);
        if (!service) return true;
        
        // Selettori specifici per ogni campo
        const nameInput = row.querySelector('.name-input');
        const priceInput = row.querySelector('.price-input');
        const durationInput = row.querySelector('.duration-input');
        const descriptionInput = row.querySelector('.description-input');
        
        const name = nameInput?.value?.trim() || '';
        const price = parseFloat(priceInput?.value) || 0;
        const duration = parseInt(durationInput?.value) || 15;
        
        // Gestione notifica con checkbox
        const notificationCheckbox = row.querySelector('.notification-enabled');
        const notificationInput = row.querySelector('.notification-input');
        const notificationDays = notificationCheckbox?.checked ? (parseInt(notificationInput?.value) || 90) : null;
        
        const description = descriptionInput?.value?.trim() || '';
        
        // Validazione nome obbligatorio
        if (!name) {
            nameInput.classList.add('error-highlight');
            return false;
        } else {
            nameInput.classList.remove('error-highlight');
        }
        
        // Aggiorna modello
        service.name = name;
        service.price = price;
        service.durationMinutes = duration;
        service.notification_days = notificationDays;
        service.description = description;
        
        return true;
    }

    // ========== EVENT HANDLERS ==========

    function onAddService() {
        const newService = {
            id: 'temp_' + Date.now(),
            name: 'Nuovo Servizio ' + (servicesList.length + 1),
            price: 25.00,
            durationMinutes: 30,
            notification_days: 90,
            description: ''
        };
        
        servicesList.push(newService);
        renderServicesTable();
        
        setTimeout(() => {
            const newRow = document.querySelector(`tr[data-service-id="${newService.id}"] input[type="text"]`);
            if (newRow) newRow.focus();
        }, 0);
    }

    function onDeleteSingle(id) {
        const service = servicesList.find(s => s.id == id);
        if (!service) return;

        if (!confirm(`Rimuovere servizio "${service.name}" dalla lista?\n\nNOTA: La modifica sarà effettiva solo dopo aver premuto "Salva Tutti i Servizi".`)) return;
        
        // Rimuove il servizio solo dalla lista locale (non dal database)
        servicesList = servicesList.filter(s => s.id != id);
        renderServicesTable();
    }

    function onDeleteSelected() {
        const selectedCheckboxes = document.querySelectorAll('.row-select:checked');
        if (selectedCheckboxes.length === 0) {
            alert('Nessun servizio selezionato');
            return;
        }
        
        if (!confirm(`Rimuovere ${selectedCheckboxes.length} servizio/i selezionato/i dalla lista?\n\nNOTA: La modifica sarà effettiva solo dopo aver premuto "Salva Tutti i Servizi".`)) return;
        
        const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.closest('tr').dataset.serviceId);
        
        // Rimuove i servizi solo dalla lista locale (non dal database)
        servicesList = servicesList.filter(s => !selectedIds.includes(String(s.id)));
        
        renderServicesTable();
        document.getElementById('select-all-services').checked = false;
    }

    async function onSaveAll() {
        console.log('=== onSaveAll chiamata ===');
        
        // Sincronizza tutti i dati dalla UI
        let allValid = true;
        document.querySelectorAll('#services-list tr[data-service-id]').forEach(row => {
            const serviceId = row.dataset.serviceId;
            const isValid = syncRowToModel(row, serviceId);
            if (!isValid) allValid = false;
        });
        
        console.log('Validazione:', allValid);
        
        if (!allValid) {
            alert('Correggi gli errori evidenziati prima di salvare');
            return;
        }
        
        console.log('servicesList.length:', servicesList.length);
        console.log('servicesList:', servicesList);
        
        if (servicesList.length === 0) {
            alert('Nessun servizio da salvare');
            return;
        }
        
        console.log('Mostrando conferma...');
        const confirmed = confirm(`Aggiorna la lista servizi nel database?\n\nATTENZIONE: Questa operazione aggiornerà tutti i servizi esistenti.`);
        console.log('Confermato:', confirmed);
        
        if (!confirmed) {
            console.log('Operazione annullata dall\'utente');
            return;
        }
        
        console.log('Inizio salvataggio...');
        const saveBtn = document.getElementById('save-all-btn');
        const originalText = saveBtn.textContent;
        saveBtn.textContent = 'Salvataggio...';
        saveBtn.disabled = true;
        
        try {
            console.log('Chiamando saveServicesToDb...');
            const result = await saveServicesToDb(servicesList);
            console.log('Risultato ricevuto:', result);
            
            if (result.success) {
                alert('Servizi salvati con successo!');
                console.log('Ricaricando servizi...');
                await loadServicesFromDb();
                renderServicesTable();
                
                setTimeout(() => {
                    console.log('Chiudendo finestra...');
                    window.close();
                }, 500);
            } else {
                console.error('Errore dal server:', result.error);
                console.error('Dettagli errore:', result.details);
                console.error('Risposta completa:', result);
                
                let errorMsg = 'Errore durante il salvataggio:\n' + (result.error || 'Errore sconosciuto');
                if (result.details && Array.isArray(result.details)) {
                    errorMsg += '\n\nDettagli:\n' + result.details.join('\n');
                }
                alert(errorMsg);
            }
        } catch (error) {
            console.error('Eccezione durante salvataggio:', error);
            console.error('Stack:', error.stack);
            alert('Errore di connessione durante il salvataggio: ' + error.message);
        } finally {
            saveBtn.textContent = originalText;
            saveBtn.disabled = false;
        }
    }

    // ========== INIT ==========

    document.addEventListener('DOMContentLoaded', async () => {
        console.log('=== DOM LOADED ===');
        console.log('Caricamento servizi...');
        
        const loaded = await loadServicesFromDb();
        console.log('Dati caricati:', loaded);
        console.log('servicesList length:', servicesList.length);
        console.log('servicesList:', servicesList);
        
        renderServicesTable();
        console.log('Tabella renderizzata');
        
        // Event listeners pulsanti
        document.getElementById('add-service-btn')?.addEventListener('click', onAddService);
        document.getElementById('delete-selected-btn')?.addEventListener('click', onDeleteSelected);
        document.getElementById('save-all-btn')?.addEventListener('click', onSaveAll);
        
        // Event listener select all
        document.getElementById('select-all-services')?.addEventListener('change', e => {
            const checked = e.target.checked;
            document.querySelectorAll('.row-select').forEach(cb => cb.checked = checked);
            updateSelectionStats();
        });
        
        // Event listener checkbox individuali
        document.addEventListener('change', e => {
            if (e.target.classList.contains('row-select')) {
                updateSelectionStats();
            }
            
            // Gestione checkbox attivazione notifica
            if (e.target.classList.contains('notification-enabled')) {
                const row = e.target.closest('tr');
                const notificationInput = row.querySelector('.notification-input');
                if (e.target.checked) {
                    notificationInput.disabled = false;
                    if (!notificationInput.value || notificationInput.value === '0') {
                        notificationInput.value = 90; // Default quando si attiva
                    }
                } else {
                    notificationInput.disabled = true;
                }
            }
        });
        
        // Event listener delete singolo
        document.addEventListener('click', e => {
            const deleteBtn = e.target.closest('.btn-delete-single');
            if (deleteBtn) {
                const id = deleteBtn.dataset.serviceId;
                onDeleteSingle(id);
            }
        });
        
        // Event listener validazione input
        document.addEventListener('input', e => {
            if (e.target.classList.contains('cell-input') || 
                e.target.classList.contains('cell-textarea')) {
                const row = e.target.closest('tr');
                if (row) {
                    const serviceId = row.dataset.serviceId;
                    syncRowToModel(row, serviceId);
                }
            }
        });
    });
</script>

<style>
/* Toggle Switch per Notifica */
.notification-toggle {
    position: relative;
    display: inline-block;
    width: 40px;
    height: 20px;
    cursor: pointer;
}

.notification-toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    border: 1px solid #999;
    border-radius: 20px;
    transition: all 0.3s ease;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 14px;
    width: 14px;
    left: 2px;
    bottom: 2px;
    background-color: white;
    border-radius: 50%;
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

.notification-toggle input:checked + .toggle-slider {
    background-color: #4CAF50;
    border-color: #45a049;
}

.notification-toggle input:checked + .toggle-slider:before {
    transform: translateX(20px);
}

.notification-toggle:hover .toggle-slider {
    box-shadow: 0 0 5px rgba(0,0,0,0.2);
}

/* Input giorni quando disabilitato */
.notification-input:disabled {
    background-color: #f0f0f0;
    color: #999;
    cursor: not-allowed;
    border-color: #ddd;
}

.notification-input:enabled {
    background-color: #fff;
}
</style>

</body>
</html>