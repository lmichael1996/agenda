<?php
/**
 * Popup per aggiungere nuovo appuntamento
 * Form con: Nome, Cognome, Servizio, Data e ora, Nota
 */
require_once '../../../src/Auth/AccessControl.php';

// Recupera i parametri dalla URL
$clientId = $_GET['clientId'] ?? '';
$clientName = $_GET['clientName'] ?? '';

// Dividi il nome completo in nome e cognome se fornito
$firstName = '';
$lastName = '';
if ($clientName) {
    $nameParts = explode(' ', trim($clientName), 2);
    $firstName = $nameParts[0] ?? '';
    $lastName = $nameParts[1] ?? '';
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuovo Appuntamento - Agenda</title>
    <link rel="stylesheet" href="../../assets/css/scheme-popup.css">
    <link rel="stylesheet" href="../../assets/css/scrollbar.css">
</head>
<body>
    <div class="popup-window-container">
        <div class="window-header">
            <span class="header-title">Nuovo Appuntamento</span>
        </div>

        <div class="calendar-body">
            <form id="appointment-form" class="appointment-form">
                <input type="hidden" id="client-id" name="client_id" value="<?= htmlspecialchars($clientId) ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first-name">Nome:</label>
                        <input type="text" id="first-name" name="first_name" 
                               value="<?= htmlspecialchars($firstName) ?>" 
                               placeholder="Inserisci il nome" 
                               readonly required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last-name">Cognome:</label>
                        <input type="text" id="last-name" name="last_name" 
                               value="<?= htmlspecialchars($lastName) ?>" 
                               placeholder="Inserisci il cognome" 
                               readonly required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label>Servizi:</label>
                        <div id="services-container">
                            <!-- I servizi verranno aggiunti dinamicamente qui -->
                        </div>
                        <button type="button" id="add-service-btn" class="secondary-btn">
                            ➕ Aggiungi Servizio
                        </button>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="appointment-date">Data:</label>
                        <input type="date" id="appointment-date" name="appointment_date" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="appointment-time">Ora:</label>
                        <div class="time-input-container">
                            <input type="number" id="appointment-hour" class="cell-input hour-input" min="0" max="23" value="9" required>
                            <select id="appointment-minute" class="cell-input minute-select" required>
                                <?php foreach (["00","15","30","45"] as $m): ?>
                                    <option value="<?= $m ?>"><?= $m ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="notes">Nota:</label>
                        <textarea id="notes" name="notes" 
                                  placeholder="Note aggiuntive per l'appuntamento..." 
                                  rows="4"></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" id="cancel-btn" class="secondary-btn">Annulla</button>
                    <button type="submit" id="save-btn" class="primary-btn">Salva Appuntamento</button>
                </div>
            </form>
        </div>
    </div>

    <script type="module">
        import { fetchServices } from '../assets/js/api/services-api.js';
        import { saveSchedule } from '../assets/js/api/schedule-api.js';

        // Elementi DOM
        const form = document.getElementById('appointment-form');
        const servicesContainer = document.getElementById('services-container');
        const addServiceBtn = document.getElementById('add-service-btn');
        const cancelBtn = document.getElementById('cancel-btn');
        const saveBtn = document.getElementById('save-btn');

        let availableServices = [];
        let serviceCounter = 0;

        // Carica i servizi disponibili
        async function loadServices() {
            try {
                const response = await fetchServices();
                if (response.success && response.services) {
                    availableServices = response.services;
                    // Aggiungi automaticamente il primo servizio
                    addServiceRow();
                }
            } catch (error) {
                console.error('Errore caricamento servizi:', error);
                alert('Errore nel caricamento dei servizi');
            }
        }

        // Crea una riga di servizio
        function createServiceSelect(id) {
            const select = document.createElement('select');
            select.className = 'service-select';
            select.name = `service_id_${id}`;
            select.dataset.serviceId = id;
            select.required = true;
            
            select.innerHTML = '<option value="">Seleziona servizio...</option>';
            availableServices.forEach(service => {
                const option = document.createElement('option');
                option.value = service.id;
                option.textContent = `${service.name} (${service.duration} min - €${service.price})`;
                select.appendChild(option);
            });
            
            return select;
        }

        // Aggiungi una riga servizio
        function addServiceRow() {
            serviceCounter++;
            
            const serviceRow = document.createElement('div');
            serviceRow.className = 'service-row';
            serviceRow.dataset.serviceId = serviceCounter;
            
            const select = createServiceSelect(serviceCounter);
            
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.textContent = '✖';
            removeBtn.className = 'remove-service-btn';
            removeBtn.title = 'Rimuovi servizio';
            
            removeBtn.addEventListener('click', () => {
                if (servicesContainer.children.length > 1) {
                    serviceRow.remove();
                } else {
                    alert('Deve essere presente almeno un servizio');
                }
            });
            
            serviceRow.appendChild(select);
            serviceRow.appendChild(removeBtn);
            servicesContainer.appendChild(serviceRow);
        }

        // Pulsante aggiungi servizio
        addServiceBtn.addEventListener('click', () => {
            addServiceRow();
        });

        // Gestione invio form
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(form);
            
            // Costruisci l'orario da ora e minuti
            const hour = document.getElementById('appointment-hour').value;
            const minute = document.getElementById('appointment-minute').value;
            const appointmentTime = `${hour.padStart(2, '0')}:${minute}`;
            
            // Raccogli tutti i servizi selezionati
            const serviceSelects = servicesContainer.querySelectorAll('.service-select');
            const serviceIds = Array.from(serviceSelects)
                .map(select => select.value)
                .filter(value => value); // Rimuovi valori vuoti
            
            if (serviceIds.length === 0) {
                alert('Seleziona almeno un servizio');
                return;
            }
            
            const appointmentData = {
                client_id: formData.get('client_id') || null,
                first_name: formData.get('first_name'),
                last_name: formData.get('last_name'),
                service_ids: serviceIds, // Array di servizi
                appointment_date: formData.get('appointment_date'),
                appointment_time: appointmentTime,
                notes: formData.get('notes') || ''
            };

            // Validazione
            if (!appointmentData.first_name || !appointmentData.last_name) {
                alert('Nome e cognome sono obbligatori');
                return;
            }

            if (!appointmentData.appointment_date || !hour || !minute) {
                alert('Data e ora sono obbligatori');
                return;
            }

            // Salvataggio
            saveBtn.disabled = true;
            saveBtn.textContent = 'Salvataggio...';

            try {
                // Se ci sono più servizi, crea più appuntamenti
                const promises = serviceIds.map(serviceId => {
                    return saveSchedule({
                        ...appointmentData,
                        service_id: serviceId
                    });
                });
                
                const results = await Promise.all(promises);
                const allSuccess = results.every(r => r.success);
                
                if (allSuccess) {
                    alert(`${serviceIds.length} appuntamento/i creato/i con successo!`);
                    
                    // Notifica la finestra padre del cambiamento
                    if (window.opener && window.opener.clientsData) {
                        window.opener.clientsData.loadClients();
                    }
                    
                    window.close();
                } else {
                    const errors = results.filter(r => !r.success).map(r => r.error).join(', ');
                    alert('Alcuni appuntamenti non sono stati salvati: ' + errors);
                }
            } catch (error) {
                console.error('Errore salvataggio:', error);
                alert('Errore di connessione durante il salvataggio');
            } finally {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Salva Appuntamento';
            }
        });

        // Pulsante annulla
        cancelBtn.addEventListener('click', () => {
            if (confirm('Vuoi davvero annullare? Le modifiche non verranno salvate.')) {
                window.close();
            }
        });

        // Carica i servizi all'avvio
        loadServices();
    </script>
</body>
</html>