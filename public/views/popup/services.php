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
            const response = await fetch('../../../src/Api/api.php?endpoint=services', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ services: services })
            });
            
            const data = await response.json();
            console.log('Risposta salvataggio:', data);
            
            return data;
        } catch (e) {
            console.error('Errore salvataggio:', e);
            return { success: false, error: 'Errore di connessione' };
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
        return `
            <tr data-service-id="${service.id}">
                <td><input type="checkbox" class="row-select"></td>
                <td><input type="text" value="${escapeHtml(service.name)}" class="cell-input"></td>
                <td><input type="number" value="${Number(service.price).toFixed(2)}" step="1" min="0" max="9999.99" class="cell-input price-input"></td>
                <td><input type="number" value="${service.durationMinutes || service.duration}" step="15" min="15" max="480" class="cell-input duration-input"></td>
                <td><textarea class="cell-textarea" rows="2" placeholder="Descrizione dettagliata...">${escapeHtml(service.description)}</textarea></td>
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
            tbody.innerHTML = '<tr><td colspan="6">Nessun servizio configurato</td></tr>';
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
        
        const inputs = row.querySelectorAll('.cell-input, .cell-textarea');
        const name = inputs[0]?.value?.trim() || '';
        const price = parseFloat(inputs[1]?.value) || 0;
        const duration = parseInt(inputs[2]?.value) || 15;
        const description = inputs[3]?.value?.trim() || '';
        
        // Validazione nome obbligatorio
        if (!name) {
            inputs[0].classList.add('error-highlight');
            return false;
        } else {
            inputs[0].classList.remove('error-highlight');
        }
        
        // Aggiorna modello
        service.name = name;
        service.price = price;
        service.durationMinutes = duration;
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
        // Sincronizza tutti i dati dalla UI
        let allValid = true;
        document.querySelectorAll('#services-list tr[data-service-id]').forEach(row => {
            const serviceId = row.dataset.serviceId;
            const isValid = syncRowToModel(row, serviceId);
            if (!isValid) allValid = false;
        });
        
        if (!allValid) {
            alert('Correggi gli errori evidenziati prima di salvare');
            return;
        }
        
        if (servicesList.length === 0) {
            alert('Nessun servizio da salvare');
            return;
        }
        
        if (!confirm(`Aggiorna la lista servizi nel database?\n\nATTENZIONE: Questa operazione sostituirà tutti i servizi esistenti.`)) {
            return;
        }
        
        const saveBtn = document.getElementById('save-all-btn');
        const originalText = saveBtn.textContent;
        saveBtn.textContent = 'Salvataggio...';
        saveBtn.disabled = true;
        
        try {
            const result = await saveServicesToDb(servicesList);
            
            if (result.success) {
                alert('Servizi salvati con successo!');
                await loadServicesFromDb();
                renderServicesTable();
                
                setTimeout(() => {
                    window.close();
                }, 500);
            } else {
                alert('Errore durante il salvataggio:\n' + (result.error || 'Errore sconosciuto'));
            }
        } catch (error) {
            console.error('Errore salvataggio:', error);
            alert('Errore di connessione durante il salvataggio');
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
</body>
</html>