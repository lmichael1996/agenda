<?php
/**
 * Popup per aggiungere nuovo appuntamento
 * Form con: Nome, Cognome, Servizio, Data e ora, Nota
 */
require_once '../../../src/Auth/AccessControl.php';

// Recupera solo il clientId dalla URL
$clientId = $_GET['clientId'] ?? '';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuovo Appuntamento - Agenda</title>
    <link rel="stylesheet" href="../../assets/css/scheme-popup.css">
    <link rel="stylesheet" href="../../assets/css/client-detail.css">
    <link rel="stylesheet" href="../../assets/css/scrollbar.css">
</head>
<body>
    <div class="popup-window-container">
        <div class="window-header">
            <span class="header-title">Nuovo Appuntamento</span>
        </div>

        <div class="calendar-body note-calendar-body">
            <form id="appointment-form" class="note-form">
                <input type="hidden" id="client-id" name="client_id" value="<?= htmlspecialchars($clientId) ?>">
                
                <table class="excel-table note-table">
                    <tbody>
                        <tr>
                            <th class="note-table-th">Nome</th>
                            <td id="first-name">Caricamento...</td>
                        </tr>
                        <tr>
                            <th class="note-table-th">Cognome</th>
                            <td id="last-name">Caricamento...</td>
                        </tr>
                        <tr>
                            <th class="note-table-th">Servizi</th>
                            <td>
                                <div id="services-container">
                                    <!-- I servizi verranno aggiunti dinamicamente qui -->
                                </div>
                                <button type="button" id="add-service-btn" class="secondary-btn">➕ Aggiungi Servizio</button>
                            </td>
                        </tr>
                        <tr>
                            <th class="note-table-th">Data e ora</th>
                            <td>
                                <div class="date-time-container">
                                    <input type="date" id="appointment-date" class="cell-input date-input" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                                    <input type="number" id="appointment-hour" class="cell-input hour-input" min="0" max="23" value="9" required>
                                    <select id="appointment-minute" class="cell-input minute-select" required>
                                        <?php foreach (["00","15","30","45"] as $m): ?>
                                            <option value="<?= $m ?>"><?= $m ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th class="note-table-th">Multiplo</th>
                            <td>
                                <div class="multiple-appointment-container">
                                    <input type="checkbox" id="multiple-appointment" class="note-checkbox">
                                    <label for="multiple-appointment">Crea appuntamenti multipli</label>
                                    <input type="number" id="appointment-counter" class="cell-input" min="2" max="10" value="2" disabled>
                                    <span id="counter-label">settimane</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th class="note-table-th">Nota</th>
                            <td><textarea id="notes" name="notes" class="cell-textarea" placeholder="Note aggiuntive per l'appuntamento..." rows="3"></textarea></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="note-save-container">
                    <button type="submit" id="save-btn" class="save-btn note-save-btn">Salva Appuntamento</button>
                </div>
            </form>
        </div>
    </div>

    <script type="module">
        console.log('Script loaded');

        // Get client ID from URL
        const urlParams = new URLSearchParams(window.location.search);
        const clientId = urlParams.get('clientId');
        
        console.log('Client ID from URL:', clientId);

        // Elementi DOM
        const form = document.getElementById('appointment-form');
        const servicesContainer = document.getElementById('services-container');
        const addServiceBtn = document.getElementById('add-service-btn');
        const saveBtn = document.getElementById('save-btn');
        const multipleCheckbox = document.getElementById('multiple-appointment');
        const counterInput = document.getElementById('appointment-counter');

        // Gestione checkbox multiplo
        multipleCheckbox.addEventListener('change', (e) => {
            counterInput.disabled = !e.target.checked;
            if (!e.target.checked) {
                counterInput.value = 2;
            }
        });

        let availableServices = [];
        let availableUsers = [];
        let serviceCounter = 0;

        // Carica gli utenti disponibili
        async function loadUsers() {
            try {
                const response = await fetch('../../../src/Api/api.php?endpoint=users');
                const data = await response.json();
                
                if (data.success && data.users) {
                    availableUsers = data.users;
                }
            } catch (error) {
                console.error('Errore caricamento utenti:', error);
            }
        }

        // Carica i dati del cliente
        async function loadClientData() {
            console.log('Loading client data for ID:', clientId);
            
            if (!clientId) {
                alert('ID cliente mancante');
                window.close();
                return;
            }

            try {
                const url = `../../../src/Api/api.php?endpoint=clients&id=${clientId}`;
                console.log('Fetching from:', url);
                
                const response = await fetch(url);
                const data = await response.json();
                
                console.log('Client data received:', data);
                
                if (data.success && data.data) {
                    const firstName = data.data.first_name || '';
                    const lastName = data.data.last_name || '';
                    
                    console.log('Setting names:', firstName, lastName);
                    
                    document.getElementById('first-name').textContent = firstName;
                    document.getElementById('last-name').textContent = lastName;
                } else {
                    console.error('API returned error:', data);
                    alert('Errore nel caricamento dei dati del cliente');
                    window.close();
                }
            } catch (error) {
                console.error('Errore caricamento cliente:', error);
                alert('Errore di connessione');
                window.close();
            }
        }

        // Carica i servizi disponibili
        async function loadServices() {
            try {
                const response = await fetch('../../../src/Api/api.php?endpoint=services');
                const data = await response.json();
                
                if (data.success && data.services) {
                    availableServices = data.services;
                    // Aggiungi automaticamente il primo servizio
                    addServiceRow();
                }
            } catch (error) {
                console.error('Errore caricamento servizi:', error);
                alert('Errore nel caricamento dei servizi');
            }
        }

        // Crea select utente
        function createUserSelect(id) {
            const select = document.createElement('select');
            select.className = 'user-select cell-input';
            select.name = `user_id_${id}`;
            select.dataset.rowId = id;
            select.required = true;
            
            availableUsers.forEach((user, index) => {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = user.username;
                if (index === 0) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
            
            return select;
        }

        // Crea select servizio
        function createServiceSelect(id) {
            const select = document.createElement('select');
            select.className = 'service-select cell-input';
            select.name = `service_id_${id}`;
            select.dataset.rowId = id;
            select.required = true;
            
            availableServices.forEach((service, index) => {
                const option = document.createElement('option');
                option.value = service.id;
                option.textContent = service.name;
                option.dataset.duration = service.duration;
                if (index === 0) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
            
            // Update duration when service changes
            select.addEventListener('change', (e) => {
                const selectedOption = e.target.options[e.target.selectedIndex];
                const duration = selectedOption.dataset.duration;
                const durationSelect = document.querySelector(`select[name="duration_${id}"]`);
                if (durationSelect) {
                    durationSelect.value = duration;
                }
            });
            
            return select;
        }

        // Crea select durata
        function createDurationSelect(id, defaultDuration) {
            const select = document.createElement('select');
            select.className = 'duration-select cell-input';
            select.name = `duration_${id}`;
            select.dataset.rowId = id;
            select.required = true;
            
            // Durate comuni: 15, 30, 45, 60, 90, 120 minuti
            const durations = [15, 30, 45, 60, 90, 120];
            durations.forEach(duration => {
                const option = document.createElement('option');
                option.value = duration;
                option.textContent = `${duration} min`;
                if (duration == defaultDuration) {
                    option.selected = true;
                }
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
            
            const userSelect = createUserSelect(serviceCounter);
            const serviceSelect = createServiceSelect(serviceCounter);
            
            // Get default duration from first service
            const defaultDuration = availableServices.length > 0 ? availableServices[0].duration : 30;
            const durationSelect = createDurationSelect(serviceCounter, defaultDuration);
            
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
            
            serviceRow.appendChild(userSelect);
            serviceRow.appendChild(serviceSelect);
            serviceRow.appendChild(durationSelect);
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
            
            // Raccogli tutti i servizi selezionati con utente e durata
            const serviceRows = servicesContainer.querySelectorAll('.service-row');
            const appointments = Array.from(serviceRows).map(row => {
                const userId = row.querySelector('.user-select')?.value;
                const serviceId = row.querySelector('.service-select')?.value;
                const duration = row.querySelector('.duration-select')?.value;
                
                return { userId, serviceId, duration };
            }).filter(apt => apt.userId && apt.serviceId && apt.duration);
            
            if (appointments.length === 0) {
                alert('Seleziona almeno un servizio');
                return;
            }
            
            // Leggi nome e cognome dalle celle td
            const firstName = document.getElementById('first-name').textContent.trim();
            const lastName = document.getElementById('last-name').textContent.trim();

            // Validazione
            if (!firstName || !lastName || 
                firstName === 'Caricamento...' || 
                lastName === 'Caricamento...') {
                alert('Nome e cognome sono obbligatori');
                return;
            }

            const appointmentDate = document.getElementById('appointment-date').value;
            if (!appointmentDate || !hour || !minute) {
                alert('Data e ora sono obbligatori');
                return;
            }

            // Salvataggio
            saveBtn.disabled = true;
            saveBtn.textContent = 'Salvataggio...';

            try {
                // Crea un appuntamento per ogni riga servizio
                const promises = appointments.map(apt => {
                    const appointmentPayload = {
                        client_id: formData.get('client_id') || null,
                        first_name: firstName,
                        last_name: lastName,
                        user_id: apt.userId,
                        service_id: apt.serviceId,
                        duration: apt.duration,
                        appointment_date: appointmentDate,
                        appointment_time: appointmentTime,
                        notes: formData.get('notes') || ''
                    };
                    
                    return fetch('../../../src/Api/api.php?endpoint=schedule', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(appointmentPayload)
                    }).then(res => res.json());
                });
                
                const results = await Promise.all(promises);
                const allSuccess = results.every(r => r.success);
                
                if (allSuccess) {
                    alert(`${appointments.length} appuntamento/i creato/i con successo!`);
                    
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

        // Carica i dati all'avvio
        async function initializeForm() {
            await loadClientData();
            await loadUsers();
            await loadServices();
        }
        
        initializeForm();
    </script>
</body>
</html>