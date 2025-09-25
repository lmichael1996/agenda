<?php
/**
 * Popup per la gestione dei servizi - Finestra separata
 * Versione organizzata basata su popup.css
 */

// Carica configurazione e controlli di sicurezza
require_once '../../config/config.php';

// Verifica autenticazione
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Headers di sicurezza per popup
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Servizi - Agenda</title>
    <link rel="stylesheet" href="../../assets/css/popup.css">
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
                    <button id="add-service-btn" class="toolbar-btn">➕ Nuovo Servizio</button>
                    <button id="delete-selected-btn" class="toolbar-btn">🗑️ Elimina Selezionati</button>
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
                    <button id="save-all-btn" class="save-btn">💾 Salva Tutti i Servizi</button>
                </div>

            </div>
        </div>

    </div>

<script type="module">
    // ========== GESTIONE SERVIZI ==========
    import { fetchServices, saveAllServices } from '../../api/frontend/services-api.js';
    
    let servicesList = [];
    let serviceIdCounter = 1;

    // Funzioni di utilità
    async function loadServicesFromApi() {
        try {
            const data = await fetchServices();
            if (data.success && Array.isArray(data.services)) {
                servicesList = data.services;
                serviceIdCounter = servicesList.length ? Math.max(...servicesList.map(s => s.id)) + 1 : 1;
            }
        } catch (e) {
            console.error('Errore caricamento servizi:', e);
        }
    }

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
                <td><input type="number" value="${service.durationMinutes}" step="15" min="15" max="480" class="cell-input duration-input"></td>
                <td><textarea class="cell-textarea" rows="2" placeholder="Descrizione dettagliata...">${escapeHtml(service.description)}</textarea></td>
                <td class="actions-cell"><button class="action-btn btn-delete-single" data-service-id="${service.id}" title="Elimina">🗑️</button></td>
            </tr>
        `;
    }

    function renderServicesTable() {
        const tbody = document.getElementById('services-list');
        if (!tbody) return;
        
        if (!servicesList.length) {
            tbody.innerHTML = '<tr><td colspan="6">Nessun servizio configurato</td></tr>';
            updateSelectionStats();
            return;
        }
        
        tbody.innerHTML = servicesList.map(generateServiceRow).join('');
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
        
        // Validazione solo per nome obbligatorio
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

    // Gestori eventi principali
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

        if (!confirm(`Eliminare servizio ${service.name} dalla tabella?`)) return;

        servicesList = servicesList.filter(s => s.id != id);
        renderServicesTable();
    }

    function onDeleteSelected() {
        const selectedCheckboxes = document.querySelectorAll('.row-select:checked');
        if (selectedCheckboxes.length === 0) {
            alert('Nessun servizio selezionato');
            return;
        }
        
        if (!confirm(`Eliminare ${selectedCheckboxes.length} servizio/i selezionato/i dalla tabella?`)) return;
        
        const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.closest('tr').dataset.serviceId);
        servicesList = servicesList.filter(s => !selectedIds.includes(String(s.id)));
        renderServicesTable();
        document.getElementById('select-all-services').checked = false;
    }

    async function onSaveAll() {
        // Sincronizza tutti i dati dalla UI
        let allValid = true;
        document.querySelectorAll('#services-list tr[data-service-id]').forEach(row => {
            const serviceId = row.dataset.serviceId;
            // Supporta sia ID numerici che temporanei (stringhe)
            const isValid = syncRowToModel(row, serviceId);
            if (!isValid) allValid = false;
        });
        
        if (!allValid) {
            alert('⚠️ Correggi gli errori evidenziati prima di salvare');
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
        saveBtn.textContent = '💾 Salvataggio...';
        saveBtn.disabled = true;
        
        try {
            const result = await saveAllServices(servicesList);
            
            if (result.success) {
                alert('✅ Servizi salvati con successo!');
                // Ricarica i dati dal database
                await loadServicesFromApi();
                renderServicesTable();
                
                // Chiudi la finestra popup dopo il salvataggio
                setTimeout(() => {
                    window.close();
                }, 500);
            } else {
                alert('❌ Errore durante il salvataggio:\n' + (result.error || 'Errore sconosciuto'));
            }
        } catch (error) {
            console.error('Errore salvataggio:', error);
            alert('❌ Errore di connessione durante il salvataggio');
        } finally {
            saveBtn.textContent = originalText;
            saveBtn.disabled = false;
        }
    }

    // Event listeners
    document.addEventListener('click', e => {
        const deleteBtn = e.target.closest('.btn-delete-single');
        if (deleteBtn) {
            const id = deleteBtn.dataset.serviceId;
            // Non convertire a int per supportare ID temporanei come 'temp_123'
            onDeleteSingle(id);
        }
    });

    // Event listener per validazione in tempo reale (senza salvataggio automatico)
    document.addEventListener('input', e => {
        if (e.target.classList.contains('cell-input') || 
            e.target.classList.contains('cell-textarea')) {
            const row = e.target.closest('tr');
            if (row) {
                const serviceId = row.dataset.serviceId;
                // Supporta sia ID numerici che temporanei (stringhe)
                syncRowToModel(row, serviceId);
            }
        }
    });

    document.addEventListener('change', e => {
        if (e.target.id === 'select-all-services') {
            const checked = e.target.checked;
            document.querySelectorAll('.row-select').forEach(cb => cb.checked = checked);
            updateSelectionStats();
        }
        
        if (e.target.classList.contains('row-select')) {
            updateSelectionStats();
        }
    });

    document.addEventListener('DOMContentLoaded', async () => {
        await loadServicesFromApi();
        renderServicesTable();
        
        document.getElementById('add-service-btn')?.addEventListener('click', onAddService);
        document.getElementById('delete-selected-btn')?.addEventListener('click', onDeleteSelected);
        document.getElementById('save-all-btn')?.addEventListener('click', onSaveAll);
        
        document.getElementById('select-all-services')?.addEventListener('change', e => {
            const checked = e.target.checked;
            document.querySelectorAll('.row-select').forEach(cb => cb.checked = checked);
            updateSelectionStats();
        });
    });
</script>
</body>
</html>