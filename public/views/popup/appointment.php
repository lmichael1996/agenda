<?php
/**
 * Popup per aggiungere/modificare appuntamento
 * Parametri URL:
 * - clientId: per nuovo appuntamento
 * - superAppointmentId: per modificare appuntamento esistente
 * - date: data precompilata (formato DD-MM-YYYY)
 * - time: ora precompilata (formato HH:MM)
 */
require_once '../../../src/Auth/AccessControl.php';

// Recupera parametri dalla URL
$clientId = $_GET['clientId'] ?? '';
$superAppointmentId = $_GET['superAppointmentId'] ?? '';
$dateParam = $_GET['date'] ?? '';
$timeParam = $_GET['time'] ?? '';
$isEdit = !empty($superAppointmentId);
$pageTitle = $isEdit ? 'Modifica Appuntamento' : 'Nuovo Appuntamento';

// Converti data da DD-MM-YYYY a YYYY-MM-DD se presente
$defaultDate = date('Y-m-d', strtotime('+1 day'));
if ($dateParam) {
    // Formato atteso: DD-MM-YYYY
    $dateParts = explode('-', $dateParam);
    if (count($dateParts) === 3) {
        $defaultDate = sprintf('%s-%s-%s', $dateParts[2], $dateParts[1], $dateParts[0]);
    }
}

// Estrai ora e minuti se presenti
$defaultHour = 9;
$defaultMinute = '00';
if ($timeParam) {
    // Formato atteso: HH:MM
    $timeParts = explode(':', $timeParam);
    if (count($timeParts) === 2) {
        $defaultHour = (int)$timeParts[0];
        $defaultMinute = $timeParts[1];
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Agenda</title>
    <link rel="stylesheet" href="../../assets/css/scheme-popup.css">
    <link rel="stylesheet" href="../../assets/css/client-detail.css">
    <link rel="stylesheet" href="../../assets/css/scrollbar.css">
</head>
<body>
    <div class="popup-window-container">
        <div class="window-header">
            <span class="header-title"><?= $pageTitle ?></span>
        </div>

        <div class="calendar-body note-calendar-body">
            <form id="appointment-form" class="note-form">
                <input type="hidden" id="client-id" name="client_id" value="<?= htmlspecialchars($clientId) ?>">
                <input type="hidden" id="super-appointment-id" name="super_appointment_id" value="<?= htmlspecialchars($superAppointmentId) ?>">
                
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
                        <?php if ($isEdit): ?>
                        <tr>
                            <th class="note-table-th">Prodotti</th>
                            <td>
                                <div id="product-responsible-container" style="display: none; justify-content: center; align-items: center; gap: 10px; margin-bottom: 10px;">
                                    <label for="product-responsible" style="font-weight: 600;">Responsabile vendita:</label>
                                    <select id="product-responsible" class="cell-input" style="width: 150px;">
                                        <!-- Verrà popolato con gli utenti -->
                                    </select>
                                </div>
                                <div id="products-container">
                                    <!-- I prodotti verranno aggiunti dinamicamente qui -->
                                </div>
                                <button type="button" id="add-product-btn" class="secondary-btn">➕ Vendita</button>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <th class="note-table-th">Data e ora</th>
                            <td>
                                <div class="date-time-container">
                                    <input type="date" id="appointment-date" class="cell-input date-input" value="<?= htmlspecialchars($defaultDate) ?>" required>
                                    <input type="number" id="appointment-hour" class="cell-input hour-input" min="0" max="23" value="<?= $defaultHour ?>" required>
                                    <select id="appointment-minute" class="cell-input minute-select" required>
                                        <?php foreach (["00","15","30","45"] as $m): ?>
                                            <option value="<?= $m ?>" <?= $m === $defaultMinute ? 'selected' : '' ?>><?= $m ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div id="future-dates-list" style="display: flex; flex-direction: column; gap: 8px; margin-top: 8px;">
                                    <!-- I campi data verranno aggiunti dinamicamente qui -->
                                </div>
                            </td>
                        </tr>
                        <?php if (!$isEdit): ?>
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
                        <?php endif; ?>
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

        // Get parameters from URL
        const urlParams = new URLSearchParams(window.location.search);
        const clientId = urlParams.get('clientId');
        const superAppointmentId = urlParams.get('superAppointmentId');
        const isEdit = !!superAppointmentId;
        
        console.log('Client ID:', clientId);
        console.log('Super Appointment ID:', superAppointmentId);
        console.log('Edit mode:', isEdit);

        // Elementi DOM
        const form = document.getElementById('appointment-form');
        const servicesContainer = document.getElementById('services-container');
        const addServiceBtn = document.getElementById('add-service-btn');
        const saveBtn = document.getElementById('save-btn');
        const multipleCheckbox = document.getElementById('multiple-appointment');
        const counterInput = document.getElementById('appointment-counter');
        const futureDatesList = document.getElementById('future-dates-list');
        const appointmentDateInput = document.getElementById('appointment-date');
        
        // Cambia testo pulsante se in modalità edit
        if (isEdit) {
            saveBtn.textContent = 'Aggiorna Appuntamento';
        }

        // Funzione per aggiornare la lista delle date future (solo se non in modalità edit)
        function updateFutureDates() {
            if (isEdit || !multipleCheckbox || !counterInput) {
                return; // Non disponibile in modalità edit
            }
            
            const isMultiple = multipleCheckbox.checked;
            const weeksCount = parseInt(counterInput.value) || 2;
            const baseDate = appointmentDateInput.value;
            const baseHour = document.getElementById('appointment-hour').value;
            const baseMinute = document.getElementById('appointment-minute').value;
            
            // Genera input date, ora e minuti
            futureDatesList.innerHTML = '';
            
            if (!isMultiple || !baseDate) {
                return;
            }
            
            const date = new Date(baseDate);
            
            // Parti da i=1 perché il primo appuntamento (settimana 0) è già quello principale
            for (let i = 1; i < weeksCount; i++) {
                const futureDate = new Date(date);
                futureDate.setDate(date.getDate() + (i * 7));
                
                // Container per data + ora + minuti
                const dateTimeRow = document.createElement('div');
                dateTimeRow.style.display = 'flex';
                dateTimeRow.style.gap = '8px';
                dateTimeRow.style.alignItems = 'center';
                dateTimeRow.style.justifyContent = 'center';
                
                // Input data
                const dateInput = document.createElement('input');
                dateInput.type = 'date';
                dateInput.className = 'cell-input multiple-date-input';
                dateInput.name = `multiple_date_${i}`;
                dateInput.value = formatDateInput(futureDate);
                dateInput.required = true;
                dateInput.style.flex = '1';
                
                // Input ora
                const hourInput = document.createElement('input');
                hourInput.type = 'number';
                hourInput.className = 'cell-input hour-input';
                hourInput.name = `multiple_hour_${i}`;
                hourInput.min = '0';
                hourInput.max = '23';
                hourInput.value = baseHour;
                hourInput.required = true;
                hourInput.style.width = '60px';
                
                // Input minuti
                const minuteSelect = document.createElement('select');
                minuteSelect.className = 'cell-input';
                minuteSelect.name = `multiple_minute_${i}`;
                minuteSelect.required = true;
                minuteSelect.style.width = '70px';
                
                // Opzioni minuti (00, 15, 30, 45)
                const minutes = ['00', '15', '30', '45'];
                minutes.forEach(min => {
                    const option = document.createElement('option');
                    option.value = min;
                    option.textContent = min;
                    if (min === baseMinute) {
                        option.selected = true;
                    }
                    minuteSelect.appendChild(option);
                });
                
                // Assembla il row
                dateTimeRow.appendChild(dateInput);
                dateTimeRow.appendChild(hourInput);
                dateTimeRow.appendChild(minuteSelect);
                
                futureDatesList.appendChild(dateTimeRow);
            }
        }
        
        // Formatta data per input (YYYY-MM-DD)
        function formatDateInput(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        // Gestione checkbox multiplo (solo se non in modalità edit)
        if (multipleCheckbox) {
            multipleCheckbox.addEventListener('change', (e) => {
                counterInput.disabled = !e.target.checked;
                if (!e.target.checked) {
                    counterInput.value = 2;
                }
                updateFutureDates();
            });
        }
        
        // Aggiorna date quando cambia il counter (solo se non in modalità edit)
        if (counterInput) {
            counterInput.addEventListener('input', updateFutureDates);
        }
        
        // Aggiorna date quando cambia la data base
        appointmentDateInput.addEventListener('change', updateFutureDates);
        
        // Aggiorna date quando cambiano ora o minuti (solo se non in modalità edit)
        if (!isEdit) {
            document.getElementById('appointment-hour').addEventListener('input', updateFutureDates);
            document.getElementById('appointment-minute').addEventListener('change', updateFutureDates);
        }

        let availableServices = [];
        let availableServicesForNew = []; // Servizi per nuove righe (senza servizio generico)
        let availableUsers = [];
        let availableProducts = [];
        let serviceCounter = 0;
        let productCounter = 0;

        // Carica i prodotti disponibili (solo se in modalità edit)
        async function loadProducts() {
            if (!isEdit) {
                return; // Non caricare prodotti per nuovi appuntamenti
            }
            
            try {
                const response = await fetch('../../../src/Api/api.php?endpoint=products');
                const data = await response.json();
                
                if (data.success && data.products) {
                    availableProducts = data.products;
                }
            } catch (error) {
                console.error('Errore caricamento prodotti:', error);
            }
        }

        // Carica gli utenti disponibili
        async function loadUsers() {
            try {
                const response = await fetch('../../../src/Api/api.php?endpoint=users');
                const data = await response.json();
                
                if (data.success && data.users) {
                    availableUsers = data.users;
                    
                    // Popola il select responsabile vendita (solo se esiste, cioè in modalità edit)
                    const productResponsibleSelect = document.getElementById('product-responsible');
                    if (productResponsibleSelect) {
                        availableUsers.forEach((user, index) => {
                            const option = document.createElement('option');
                            option.value = user.id;
                            option.textContent = user.username;
                            productResponsibleSelect.appendChild(option);
                        });
                    }
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
                    // Sempre escludi servizio generico per nuove righe
                    availableServicesForNew = data.services.filter(service => service.id !== 1);
                    
                    // In modalità edit, controlla se c'è servizio generico negli appointments
                    let includeGenericService = false;
                    if (isEdit && window.existingAppointments) {
                        includeGenericService = window.existingAppointments.some(apt => apt.service_id === 1);
                    }
                    
                    // Per appointments esistenti: includi servizio generico solo se presente
                    if (includeGenericService) {
                        availableServices = data.services; // Includi tutti i servizi per gli esistenti
                    } else {
                        availableServices = availableServicesForNew; // Usa lista filtrata
                    }
                    
                    // Se in modalità edit, carica appointments esistenti
                    if (isEdit && window.existingAppointments) {
                        window.existingAppointments.forEach(apt => {
                            addServiceRow(apt);
                        });
                    } else {
                        // Altrimenti aggiungi una riga vuota
                        addServiceRow();
                    }
                }
            } catch (error) {
                console.error('Errore caricamento servizi:', error);
                alert('Errore nel caricamento dei servizi');
            }
        }

        // Crea select utente
        function createUserSelect(id, selectedUserId = null) {
            const select = document.createElement('select');
            select.className = 'user-select cell-input';
            select.name = `user_id_${id}`;
            select.dataset.rowId = id;
            select.required = true;
            
            availableUsers.forEach((user, index) => {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = user.username;
                if (selectedUserId ? user.id == selectedUserId : index === 0) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
            
            return select;
        }

        // Crea select servizio
        function createServiceSelect(id, selectedServiceId = null, isExisting = false) {
            const select = document.createElement('select');
            select.className = 'service-select cell-input';
            select.name = `service_id_${id}`;
            select.dataset.rowId = id;
            select.required = true;
            
            // Usa lista completa per esistenti, lista filtrata per nuovi
            const servicesList = isExisting ? availableServices : availableServicesForNew;
            
            servicesList.forEach((service, index) => {
                const option = document.createElement('option');
                option.value = service.id;
                option.textContent = service.name;
                option.dataset.duration = service.durationMinutes;
                if (selectedServiceId ? service.id == selectedServiceId : index === 0) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
            
            // Update duration when service changes
            select.addEventListener('change', (e) => {
                const selectedOption = e.target.options[e.target.selectedIndex];
                const duration = selectedOption.dataset.duration;
                // Trova l'input duration nella stessa riga (parent)
                const serviceRow = e.target.closest('.service-row');
                const durationInput = serviceRow.querySelector('.duration-input');
                if (durationInput && duration) {
                    durationInput.value = duration;
                }
            });
            
            return select;
        }

        // Crea input durata
        function createDurationInput(id, defaultDuration) {
            const input = document.createElement('input');
            input.type = 'number';
            input.className = 'duration-input cell-input';
            input.name = `duration_${id}`;
            input.dataset.rowId = id;
            input.required = true;
            input.min = 15;
            input.max = 480;
            input.step = 15;
            input.value = defaultDuration || 30;
            input.placeholder = 'min';
            
            return input;
        }

        // Aggiungi una riga servizio
        function addServiceRow(existingData = null) {
            serviceCounter++;
            
            const serviceRow = document.createElement('div');
            serviceRow.className = 'service-row';
            serviceRow.dataset.serviceId = serviceCounter;
            
            // Determina se è un appuntamento esistente (con dati) o nuovo
            const isExisting = existingData !== null;
            
            // Determina valori predefiniti
            const defaultUserId = existingData?.user_id || (availableUsers.length > 0 ? availableUsers[0].id : null);
            const defaultServiceId = existingData?.service_id || (availableServicesForNew.length > 0 ? availableServicesForNew[0].id : null);
            const defaultDuration = existingData?.duration || (availableServicesForNew.length > 0 ? availableServicesForNew[0].duration : 30);
            
            const userSelect = createUserSelect(serviceCounter, defaultUserId);
            const serviceSelect = createServiceSelect(serviceCounter, defaultServiceId, isExisting);
            const durationInput = createDurationInput(serviceCounter, defaultDuration);
            
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
            serviceRow.appendChild(durationInput);
            serviceRow.appendChild(removeBtn);
            servicesContainer.appendChild(serviceRow);
        }

        // Crea select prodotto
        function createProductSelect(id, selectedProductId = null) {
            const select = document.createElement('select');
            select.className = 'product-select cell-input';
            select.name = `product_id_${id}`;
            select.dataset.rowId = id;
            select.required = true;
            
            availableProducts.forEach((product, index) => {
                const option = document.createElement('option');
                option.value = product.id;
                option.textContent = product.name;
                option.dataset.price = product.price;
                if (selectedProductId ? product.id == selectedProductId : index === 0) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
            
            // Update price when product changes
            select.addEventListener('change', (e) => {
                const selectedOption = e.target.options[e.target.selectedIndex];
                const price = selectedOption.dataset.price;
                const productRow = e.target.closest('.product-row');
                const priceInput = productRow.querySelector('.product-price-input');
                if (priceInput && price) {
                    priceInput.value = parseFloat(price).toFixed(2);
                }
            });
            
            return select;
        }

        // Crea input quantità prodotto
        function createQuantityInput(id, defaultQuantity = 1) {
            const input = document.createElement('input');
            input.type = 'number';
            input.className = 'quantity-input cell-input';
            input.name = `quantity_${id}`;
            input.dataset.rowId = id;
            input.required = true;
            input.min = 1;
            input.max = 999;
            input.step = 1;
            input.value = defaultQuantity;
            input.placeholder = 'Qtà';
            input.style.width = '70px';
            
            return input;
        }

        // Crea input prezzo prodotto
        function createProductPriceInput(id, defaultPrice = 0) {
            const input = document.createElement('input');
            input.type = 'number';
            input.className = 'product-price-input cell-input';
            input.name = `product_price_${id}`;
            input.dataset.rowId = id;
            input.required = true;
            input.min = 0;
            input.max = 9999.99;
            input.step = 0.01;
            input.value = parseFloat(defaultPrice).toFixed(2);
            input.placeholder = '€';
            input.style.width = '80px';
            
            return input;
        }

        // Aggiungi una riga prodotto
        function addProductRow(existingData = null) {
            productCounter++;
            
            const productRow = document.createElement('div');
            productRow.className = 'product-row';
            productRow.dataset.productId = productCounter;
            
            const defaultProductId = existingData?.product_id || (availableProducts.length > 0 ? availableProducts[0].id : null);
            const defaultQuantity = existingData?.quantity || 1;
            const defaultPrice = existingData?.price || (availableProducts.length > 0 ? availableProducts[0].price : 0);
            
            const productSelect = createProductSelect(productCounter, defaultProductId);
            const quantityInput = createQuantityInput(productCounter, defaultQuantity);
            const priceInput = createProductPriceInput(productCounter, defaultPrice);
            
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.textContent = '✖';
            removeBtn.className = 'remove-product-btn';
            removeBtn.title = 'Rimuovi prodotto';
            
            removeBtn.addEventListener('click', () => {
                productRow.remove();
                updateProductResponsibleVisibility();
            });
            
            productRow.appendChild(productSelect);
            productRow.appendChild(quantityInput);
            productRow.appendChild(priceInput);
            productRow.appendChild(removeBtn);
            
            const productsContainer = document.getElementById('products-container');
            productsContainer.appendChild(productRow);
            
            // Mostra il responsabile vendita se è il primo prodotto
            updateProductResponsibleVisibility();
        }
        
        // Mostra/nascondi il campo responsabile vendita in base alla presenza di prodotti
        function updateProductResponsibleVisibility() {
            if (!isEdit) {
                return; // Non disponibile per nuovi appuntamenti
            }
            
            const productsContainer = document.getElementById('products-container');
            const responsibleContainer = document.getElementById('product-responsible-container');
            
            if (!productsContainer || !responsibleContainer) {
                return;
            }
            
            const hasProducts = productsContainer.children.length > 0;
            
            if (hasProducts) {
                responsibleContainer.style.display = 'flex';
            } else {
                responsibleContainer.style.display = 'none';
            }
        }

        // Pulsante aggiungi servizio
        addServiceBtn.addEventListener('click', () => {
            addServiceRow();
        });

        // Pulsante aggiungi prodotto (solo se in modalità edit)
        const addProductBtn = document.getElementById('add-product-btn');
        if (addProductBtn) {
            addProductBtn.addEventListener('click', () => {
                addProductRow();
            });
        }

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
                const duration = row.querySelector('.duration-input')?.value;
                
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
                    // Calcola start_time e end_time
                    const startDateTime = `${appointmentDate} ${appointmentTime}:00`;
                    
                    // Calcola end_time aggiungendo la durata
                    const startDate = new Date(`${appointmentDate}T${appointmentTime}:00`);
                    const endDate = new Date(startDate.getTime() + parseInt(apt.duration) * 60000);
                    const endTime = endDate.toTimeString().substring(0, 8); // HH:MM:SS
                    const endDateTime = `${appointmentDate} ${endTime}`;
                    
                    const appointmentPayload = {
                        client_id: formData.get('client_id') || null,
                        service_id: apt.serviceId,
                        user_id: apt.userId,
                        start_time: startDateTime,
                        end_time: endDateTime,
                        status: 'scheduled',
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
            if (isEdit) {
                // Modalità modifica: carica dati esistenti
                await loadAppointmentData(superAppointmentId);
            } else {
                // Modalità creazione: carica dati cliente
                await loadClientData();
            }
            await loadUsers();
            await loadProducts();
            await loadServices();
        }
        
        // Carica dati appuntamento esistente
        async function loadAppointmentData(id) {
            try {
                const response = await fetch(`../../../src/Api/api.php?endpoint=schedule&id=${id}`);
                const data = await response.json();
                
                if (data.success && data.data) {
                    const { super_appointment, appointments } = data.data;
                    
                    // Imposta dati cliente
                    document.getElementById('first-name').textContent = super_appointment.first_name || '';
                    document.getElementById('last-name').textContent = super_appointment.last_name || '';
                    document.getElementById('client-id').value = super_appointment.client_id;
                    
                    // Imposta data e ora
                    const startTime = new Date(super_appointment.start_time);
                    const year = startTime.getFullYear();
                    const month = String(startTime.getMonth() + 1).padStart(2, '0');
                    const day = String(startTime.getDate()).padStart(2, '0');
                    document.getElementById('appointment-date').value = `${year}-${month}-${day}`;
                    document.getElementById('appointment-hour').value = startTime.getHours();
                    document.getElementById('appointment-minute').value = String(startTime.getMinutes()).padStart(2, '0');
                    
                    // Imposta note
                    document.getElementById('notes').value = super_appointment.note || '';
                    
                    // Salva appointments da caricare dopo aver caricato servizi/users
                    window.existingAppointments = appointments;
                } else {
                    alert('Errore nel caricamento dell\'appuntamento');
                    window.close();
                }
            } catch (error) {
                console.error('Errore caricamento appuntamento:', error);
                alert('Errore di connessione');
                window.close();
            }
        }
        
        initializeForm();
    </script>
</body>
</html>