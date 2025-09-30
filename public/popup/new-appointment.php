<?php
/**
 * Popup per aggiungere nuovo appuntamento
 * Form con: Nome, Cognome, Servizio, Data e ora, Nota
 */
require_once '../../config/config.php';

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
                               placeholder="Inserisci il nome" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last-name">Cognome:</label>
                        <input type="text" id="last-name" name="last_name" 
                               value="<?= htmlspecialchars($lastName) ?>" 
                               placeholder="Inserisci il cognome" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="service">Servizio:</label>
                        <select id="service" name="service_id" required>
                            <option value="">Seleziona servizio...</option>
                            <!-- Le opzioni verranno caricate dinamicamente -->
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="appointment-date">Data:</label>
                        <input type="date" id="appointment-date" name="appointment_date" required>
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
        import { fetchServices } from '../../api/frontend/services-api.js';
        import { saveSchedule } from '../../api/frontend/schedule-api.js';

        // Elementi DOM
        const form = document.getElementById('appointment-form');
        const serviceSelect = document.getElementById('service');
        const cancelBtn = document.getElementById('cancel-btn');
        const saveBtn = document.getElementById('save-btn');

        // Carica i servizi disponibili
        async function loadServices() {
            try {
                const response = await fetchServices();
                if (response.success && response.services) {
                    serviceSelect.innerHTML = '<option value="">Seleziona servizio...</option>';
                    response.services.forEach(service => {
                        const option = document.createElement('option');
                        option.value = service.id;
                        option.textContent = `${service.name} (${service.duration} min - €${service.price})`;
                        serviceSelect.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Errore caricamento servizi:', error);
                serviceSelect.innerHTML = '<option value="">Errore caricamento servizi</option>';
            }
        }

        // Gestione invio form
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(form);
            
            // Costruisci l'orario da ora e minuti
            const hour = document.getElementById('appointment-hour').value;
            const minute = document.getElementById('appointment-minute').value;
            const appointmentTime = `${hour.padStart(2, '0')}:${minute}`;
            
            const appointmentData = {
                client_id: formData.get('client_id') || null,
                first_name: formData.get('first_name'),
                last_name: formData.get('last_name'),
                service_id: formData.get('service_id'),
                appointment_date: formData.get('appointment_date'),
                appointment_time: appointmentTime,
                notes: formData.get('notes') || ''
            };

            // Validazione
            if (!appointmentData.first_name || !appointmentData.last_name) {
                alert('Nome e cognome sono obbligatori');
                return;
            }

            if (!appointmentData.service_id) {
                alert('Seleziona un servizio');
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
                const result = await saveSchedule(appointmentData);
                
                if (result.success) {
                    alert('Appuntamento creato con successo!');
                    
                    // Notifica la finestra padre del cambiamento
                    if (window.opener && window.opener.clientsData) {
                        window.opener.clientsData.loadClients();
                    }
                    
                    window.close();
                } else {
                    alert('Errore durante il salvataggio: ' + (result.error || 'Errore sconosciuto'));
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

        // Imposta data minima (oggi)
        const today = new Date();
        const dateInput = document.getElementById('appointment-date');
        dateInput.min = today.toISOString().split('T')[0];

        // Carica i servizi all'avvio
        loadServices();

        // Focus sul primo campo
        document.getElementById('first-name').focus();
    </script>
</body>
</html>