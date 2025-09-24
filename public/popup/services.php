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

// Definizione servizi di default
$defaultServices = [
    [
        'id' => 1,
        'name' => 'Consulenza Personalizzata',
        'price' => 50,
        'durationMinutes' => 60,
        'description' => 'Consulenza individuale personalizzata',
        'status' => 'disponibile'
    ],
    [
        'id' => 2,
        'name' => 'Servizio Standard',
        'price' => 30,
        'durationMinutes' => 45,
        'description' => 'Servizio base standard',
        'status' => 'disponibile'
    ],
    [
        'id' => 3,
        'name' => 'Pacchetto Premium',
        'price' => 100,
        'durationMinutes' => 120,
        'description' => 'Pacchetto completo premium',
        'status' => 'disponibile'
    ]
];
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

        <div class="calendar-body" style="padding:4px;">
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
                            <?php foreach ($defaultServices as $service): ?>
                                <tr data-service-id="<?= $service['id'] ?>">
                                    <td><input type="checkbox" class="row-select"></td>
                                    <td><input type="text" value="<?= htmlspecialchars($service['name']) ?>" placeholder="Nome servizio..." class="cell-input"></td>
                                    <td><input type="number" value="<?= number_format($service['price'], 2) ?>" step="1" min="0" max="9999.99" class="cell-input price-input"></td>
                                    <td><input type="number" value="<?= $service['durationMinutes'] ?>" step="15" min="15" max="480" class="cell-input duration-input"></td>
                                    <td><textarea placeholder="Descrizione dettagliata..." class="cell-textarea" rows="2"><?= htmlspecialchars($service['description']) ?></textarea></td>
                                    <td class="actions-cell"><button class="action-btn btn-delete-single" data-service-id="<?= $service['id'] ?>" title="Elimina">🗑️</button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="services-stats">
                    <span>Totale servizi: <strong id="total-services"><?= count($defaultServices) ?></strong></span>
                    <span>Selezionati: <strong id="selected-services">0</strong></span>
                </div>

                <div style="margin-top:20px; margin-bottom:20px; text-align:center;">
                    <button id="save-all-btn" class="save-btn">💾 Salva Tutti i Servizi</button>
                </div>

            </div>
        </div>

    </div>

<script>
        // ========== GESTIONE SERVIZI ==========
        let servicesList = <?= json_encode($defaultServices) ?>;
        const STORAGE_KEY = 'services_data';
        let serviceIdCounter = Math.max(...servicesList.map(s => s.id)) + 1;

        // Funzioni di utilità
        function loadServicesFromStorage() {
            try {
                const raw = localStorage.getItem(STORAGE_KEY);
                if (!raw) return;
                const parsed = JSON.parse(raw);
                if (Array.isArray(parsed.services)) {
                    servicesList = parsed.services;
                    serviceIdCounter = parsed.counter || (Math.max(...servicesList.map(s => s.id)) + 1);
                }
            } catch (e) {
                console.warn('Impossibile leggere storage servizi:', e);
            }
        }

        function saveServicesToStorage() {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify({
                    services: servicesList,
                    counter: serviceIdCounter
                }));
            } catch (e) {
                console.error('Errore salvataggio storage servizi:', e);
            }
        }

        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function validatePrice(price) {
            const num = parseFloat(price);
            return !isNaN(num) && num >= 0 && num <= 9999.99;
        }

        function validateDuration(duration) {
            const num = parseInt(duration);
            return !isNaN(num) && num >= 15 && num <= 480 && num % 15 === 0;
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
            const service = servicesList.find(s => s.id === id);
            if (!service) return;
            
            const inputs = row.querySelectorAll('.cell-input, .cell-textarea');
            const name = inputs[0]?.value?.trim() || '';
            const price = parseFloat(inputs[1]?.value) || 0;
            const duration = parseInt(inputs[2]?.value) || 15;
            const description = inputs[3]?.value?.trim() || '';
            
            // Validazione
            let hasErrors = false;
            
            if (!name) {
                inputs[0].classList.add('error-highlight');
                hasErrors = true;
            } else {
                inputs[0].classList.remove('error-highlight');
            }
            
            if (!validatePrice(price)) {
                inputs[1].classList.add('error-highlight');
                hasErrors = true;
            } else {
                inputs[1].classList.remove('error-highlight');
            }
            
            if (!validateDuration(duration)) {
                inputs[2].classList.add('error-highlight');
                hasErrors = true;
            } else {
                inputs[2].classList.remove('error-highlight');
            }
            
            if (hasErrors) return false;
            
            // Aggiorna modello
            service.name = name;
            service.price = price;
            service.durationMinutes = duration;
            service.description = description;
            
            saveServicesToStorage();
            return true;
        }

        // Gestori eventi principali
        function onAddService() {
            const newService = {
                id: serviceIdCounter++,
                name: 'Nuovo Servizio ' + serviceIdCounter,
                price: 25.00,
                durationMinutes: 30,
                description: ''
            };
            
            servicesList.push(newService);
            renderServicesTable();
            saveServicesToStorage();
            
            setTimeout(() => {
                const newRow = document.querySelector(`tr[data-service-id="${newService.id}"] input[type="text"]`);
                if (newRow) newRow.focus();
            }, 0);
        }

        function onDeleteSingle(id) {
            const service = servicesList.find(s => s.id === id);
            if (!service) return;
            
            if (!confirm('Eliminare questo servizio?')) return;
            
            servicesList = servicesList.filter(s => s.id !== id);
            renderServicesTable();
            saveServicesToStorage();
        }

        function onDeleteSelected() {
            const ids = Array.from(document.querySelectorAll('.row-select:checked'))
                .map(cb => parseInt(cb.closest('tr').dataset.serviceId));
            
            if (ids.length === 0) {
                alert('Nessun servizio selezionato');
                return;
            }
            
            if (!confirm(`Eliminare ${ids.length} servizio/i selezionato/i?`)) return;
            
            servicesList = servicesList.filter(s => !ids.includes(s.id));
            renderServicesTable();
            saveServicesToStorage();
            document.getElementById('select-all-services').checked = false;
        }

        function onSaveAll() {
            // Sincronizza tutti i dati dalla UI
            let allValid = true;
            document.querySelectorAll('#services-list tr[data-service-id]').forEach(row => {
                const isValid = syncRowToModel(row, parseInt(row.dataset.serviceId));
                if (!isValid) allValid = false;
            });
            
            if (!allValid) {
                alert('⚠️ Correggi gli errori evidenziati prima di salvare');
                return;
            }
            
            saveServicesToStorage();
            alert('Servizi salvati correttamente!');
        }

        // Event listeners
        document.addEventListener('click', e => {
            const deleteBtn = e.target.closest('.btn-delete-single');
            if (deleteBtn) {
                const id = parseInt(deleteBtn.dataset.serviceId);
                onDeleteSingle(id);
            }
        });

        document.addEventListener('input', e => {
            if (e.target.classList.contains('cell-input') || 
                e.target.classList.contains('cell-textarea') ||
                e.target.classList.contains('cell-textarea')) {
                const row = e.target.closest('tr');
                if (row) {
                    syncRowToModel(row, parseInt(row.dataset.serviceId));
                }
            }
        });

        // Controlli specifici per durata
        document.addEventListener('keydown', e => {
            if (e.target.classList.contains('duration-input')) {
                const allowedKeys = [9, 13, 27, 37, 38, 39, 40, 8, 46];
                
                if ((e.keyCode >= 48 && e.keyCode <= 57) || 
                    (e.keyCode >= 96 && e.keyCode <= 105) || 
                    (e.keyCode >= 65 && e.keyCode <= 90) || 
                    (e.keyCode >= 186 && e.keyCode <= 222)) {
                    e.preventDefault();
                    return false;
                }
                
                if (!allowedKeys.includes(e.keyCode)) {
                    e.preventDefault();
                    return false;
                }
            }
        });

        ['paste', 'cut', 'copy'].forEach(event => {
            document.addEventListener(event, function(e) {
                if (e.target && e.target.classList.contains('duration-input')) {
                    e.preventDefault();
                }
            });
        });

        document.addEventListener('wheel', function(e) {
            if (e.target && e.target.classList.contains('duration-input') && e.target === document.activeElement) {
                e.preventDefault();
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

        document.addEventListener('DOMContentLoaded', () => {
            loadServicesFromStorage();
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