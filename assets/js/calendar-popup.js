// Gestione popup semplificata
let activePopups = new Map();

window.openPopup = function(type) {
    const popup = createPopup(type);
    document.body.appendChild(popup);
    activePopups.set(type, popup);
    
    requestAnimationFrame(() => {
        popup.style.opacity = '1';
        popup.querySelector('.popup-content').style.transform = 'scale(1)';
    });
};

window.closePopup = function(popupId) {
    const popup = document.getElementById(popupId);
    if (popup) {
        const overlay = popup.closest('.popup-overlay');
        overlay.style.opacity = '0';
        
        setTimeout(() => {
            overlay.remove();
            const type = popupId.replace('popup-', '');
            activePopups.delete(type);
        }, 300);
    }
};

// Crea un popup con contenuto specifico
function createPopup(type) {
    const overlay = document.createElement('div');
    overlay.className = 'popup-overlay';
    
    const content = document.createElement('div');
    content.id = `popup-${type}`;
    content.className = 'popup-content';
    
    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '×';
    closeBtn.className = 'popup-close-btn';
    closeBtn.onclick = () => closePopup(`popup-${type}`);
    
    content.appendChild(closeBtn);
    content.appendChild(getPopupContent(type));
    overlay.appendChild(content);
    
    return overlay;
}

// Genera contenuto specifico per ogni tipo di popup
function getPopupContent(type) {
    const contentDiv = document.createElement('div');
    
    switch (type) {
        case 'service':
            contentDiv.innerHTML = `
                <div class="calendar-header">
                    <h2>Gestione Servizi</h2>
                    <p>Aggiungi e modifica servizi disponibili</p>
                </div>
                
                <div class="calendar-body">
                    <div class="services-section">
                        <div class="services-toolbar">
                            <button onclick="addNewService()" class="toolbar-btn btn-add">
                                <span>➕</span> Nuovo Servizio
                            </button>
                            <button onclick="deleteSelectedServices()" class="toolbar-btn btn-delete">
                                <span>🗑️</span> Elimina Selezionati
                            </button>
                        </div>

                        <div class="services-table-container">
                            <table class="excel-table" id="services-table">
                                <thead>
                                    <tr>
                                        <th class="select-col">
                                            <input type="checkbox" id="select-all-services" onchange="toggleSelectAllServices()">
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
                                    <tr data-service-id="1">
                                        <td><input type="checkbox" class="row-select-service"></td>
                                        <td><input type="text" value="Consulenza Personalizzata" class="cell-input"></td>
                                        <td><input type="number" value="50.00" step="0.01" min="0" class="cell-input price-input"></td>
                                        <td><input type="number" value="60" min="1" max="480" class="cell-input duration-input"></td>
                                        <td><input type="text" value="Consulenza individuale personalizzata" class="cell-input"></td>
                                        <td>
                                            <select class="cell-select status-select">
                                                <option value="attivo" selected>Attivo</option>
                                                <option value="inattivo">Inattivo</option>
                                                <option value="sospeso">Sospeso</option>
                                            </select>
                                        </td>
                                        <td class="actions-cell">
                                            <button onclick="deleteService(1)" class="action-btn btn-delete-single" title="Elimina">🗑️</button>
                                        </td>
                                    </tr>
                                    <tr data-service-id="2">
                                        <td><input type="checkbox" class="row-select-service"></td>
                                        <td><input type="text" value="Servizio Standard" class="cell-input"></td>
                                        <td><input type="number" value="30.00" step="0.01" min="0" class="cell-input price-input"></td>
                                        <td><input type="number" value="45" min="1" max="480" class="cell-input duration-input"></td>
                                        <td><input type="text" value="Servizio base standard" class="cell-input"></td>
                                        <td>
                                            <select class="cell-select status-select">
                                                <option value="attivo" selected>Attivo</option>
                                                <option value="inattivo">Inattivo</option>
                                                <option value="sospeso">Sospeso</option>
                                            </select>
                                        </td>
                                        <td class="actions-cell">
                                            <button onclick="deleteService(2)" class="action-btn btn-delete-single" title="Elimina">🗑️</button>
                                        </td>
                                    </tr>
                                    <tr data-service-id="3">
                                        <td><input type="checkbox" class="row-select-service"></td>
                                        <td><input type="text" value="Pacchetto Premium" class="cell-input"></td>
                                        <td><input type="number" value="100.00" step="0.01" min="0" class="cell-input price-input"></td>
                                        <td><input type="number" value="120" min="1" max="480" class="cell-input duration-input"></td>
                                        <td><input type="text" value="Pacchetto completo premium" class="cell-input"></td>
                                        <td>
                                            <select class="cell-select status-select">
                                                <option value="attivo" selected>Attivo</option>
                                                <option value="inattivo">Inattivo</option>
                                                <option value="sospeso">Sospeso</option>
                                            </select>
                                        </td>
                                        <td class="actions-cell">
                                            <button onclick="deleteService(3)" class="action-btn btn-delete-single" title="Elimina">🗑️</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="services-stats">
                            <span class="stat-item">Totale servizi: <strong id="total-services">3</strong></span>
                            <span class="stat-item">Selezionati: <strong id="selected-services">0</strong></span>
                        </div>
                    </div>
                </div>
                
                <div class="calendar-footer">
                    <button onclick="saveAllServices()" class="save-btn">
                        Salva Tutti i Servizi
                    </button>
                </div>
            `;
            break;

        case 'user':
            contentDiv.innerHTML = `
                <div class="calendar-header">
                    <h2>Gestione Utenti</h2>
                    <p>Aggiungi e modifica utenti del sistema</p>
                </div>
                
                <div class="calendar-body">
                    <div class="users-section">
                        <div class="users-toolbar">
                            <button onclick="addNewUser()" class="toolbar-btn btn-add">
                                <span>➕</span> Nuovo Utente
                            </button>
                            <button onclick="deleteSelectedUsers()" class="toolbar-btn btn-delete">
                                <span>🗑️</span> Elimina Selezionati
                            </button>
                        </div>

                        <div class="users-table-container">
                            <table class="excel-table" id="users-table">
                                <thead>
                                    <tr>
                                        <th class="select-col">
                                            <input type="checkbox" id="select-all-users" onchange="toggleSelectAll()">
                                        </th>
                                        <th class="username-col">Username</th>
                                        <th class="password-col">Password</th>
                                        <th class="confirm-password-col">Conferma Password</th>
                                        <th class="color-col">Colore</th>
                                        <th class="status-col">Stato</th>
                                        <th class="actions-col">Azioni</th>
                                    </tr>
                                </thead>
                                <tbody id="users-list">
                                    <tr data-user-id="1">
                                        <td><input type="checkbox" class="row-select"></td>
                                        <td><input type="text" value="mario.rossi" class="cell-input"></td>
                                        <td><input type="password" value="" placeholder="Password" class="cell-input"></td>
                                        <td><input type="password" value="" placeholder="Conferma Password" class="cell-input"></td>
                                        <td>
                                            <input type="color" value="#3498db" class="cell-color">
                                        </td>
                                        <td>
                                            <select class="cell-select status-select">
                                                <option value="attivo" selected>Attivo</option>
                                                <option value="inattivo">Inattivo</option>
                                                <option value="sospeso">Sospeso</option>
                                            </select>
                                        </td>
                                        <td class="actions-cell">
                                            <button onclick="deleteUser(1)" class="action-btn btn-delete-single" title="Elimina">🗑️</button>
                                        </td>
                                    </tr>
                                    <tr data-user-id="2">
                                        <td><input type="checkbox" class="row-select"></td>
                                        <td><input type="text" value="laura.bianchi" class="cell-input"></td>
                                        <td><input type="password" value="" placeholder="Password" class="cell-input"></td>
                                        <td><input type="password" value="" placeholder="Conferma Password" class="cell-input"></td>
                                        <td>
                                            <input type="color" value="#e74c3c" class="cell-color">
                                        </td>
                                        <td>
                                            <select class="cell-select status-select">
                                                <option value="attivo" selected>Attivo</option>
                                                <option value="inattivo">Inattivo</option>
                                                <option value="sospeso">Sospeso</option>
                                            </select>
                                        </td>
                                        <td class="actions-cell">
                                            <button onclick="deleteUser(2)" class="action-btn btn-delete-single" title="Elimina">🗑️</button>
                                        </td>
                                    </tr>
                                    <tr data-user-id="3">
                                        <td><input type="checkbox" class="row-select"></td>
                                        <td><input type="text" value="giuseppe.verdi" class="cell-input"></td>
                                        <td><input type="password" value="" placeholder="Password" class="cell-input"></td>
                                        <td><input type="password" value="" placeholder="Conferma Password" class="cell-input"></td>
                                        <td>
                                            <input type="color" value="#2ecc71" class="cell-color">
                                        </td>
                                        <td>
                                            <select class="cell-select status-select">
                                                <option value="attivo" selected>Attivo</option>
                                                <option value="inattivo">Inattivo</option>
                                                <option value="sospeso">Sospeso</option>
                                            </select>
                                        </td>
                                        <td class="actions-cell">
                                            <button onclick="deleteUser(3)" class="action-btn btn-delete-single" title="Elimina">🗑️</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="users-stats">
                            <span class="stat-item">Totale utenti: <strong id="total-users">3</strong></span>
                            <span class="stat-item">Attivi: <strong id="active-users">3</strong></span>
                            <span class="stat-item">Selezionati: <strong id="selected-users">0</strong></span>
                        </div>
                    </div>
                </div>
                
                <div class="calendar-footer">
                    <button onclick="saveAllUsers()" class="save-btn">
                        Salva Tutti gli Utenti
                    </button>
                </div>
            `;
            break;

        case 'schedule':
            contentDiv.innerHTML = `
                <div class="calendar-header">
                    <h2>Configurazione Orari</h2>
                    <p>Imposta i tuoi orari di lavoro</p>
                </div>
                
                <div class="calendar-body">
                    <div class="time-section">
                        <h3>Orari di Lavoro</h3>
                        <div class="time-row">
                            <div class="time-field">
                                <label>Apertura</label>
                                <input type="time" id="opening-time" value="08:00">
                            </div>
                            <div class="time-field">
                                <label>Chiusura</label>
                                <input type="time" id="closing-time" value="18:00">
                            </div>
                        </div>
                    </div>

                    <div class="break-section">
                        <h3>Pausa Pranzo</h3>
                        <div class="break-toggle">
                            <label>
                                <input type="checkbox" id="lunch-break" checked>
                                <span>Abilita pausa pranzo</span>
                            </label>
                        </div>
                        <div id="break-config" class="time-row">
                            <div class="time-field">
                                <label>Dalle</label>
                                <input type="time" id="break-start" value="12:30">
                            </div>
                            <div class="time-field">
                                <label>Alle</label>
                                <input type="time" id="break-end" value="13:30">
                            </div>
                        </div>
                    </div>

                    <div class="days-section">
                        <h3>Giorni Lavorativi</h3>
                        <div class="days-list">
                            <label><input type="checkbox" checked> Lunedì</label>
                            <label><input type="checkbox" checked> Martedì</label>
                            <label><input type="checkbox" checked> Mercoledì</label>
                            <label><input type="checkbox" checked> Giovedì</label>
                            <label><input type="checkbox" checked> Venerdì</label>
                            <label><input type="checkbox"> Sabato</label>
                            <label><input type="checkbox"> Domenica</label>
                        </div>
                    </div>

                    <div class="timezone-section">
                        <h3>Fuso Orario</h3>
                        <select id="fuso-orario">
                            <option value="Europe/Rome" selected>Europa/Roma (UTC+1)</option>
                            <option value="Europe/London">Europa/Londra (UTC+0)</option>
                            <option value="Europe/Paris">Europa/Parigi (UTC+1)</option>
                            <option value="Europe/Berlin">Europa/Berlino (UTC+1)</option>
                            <option value="Europe/Madrid">Europa/Madrid (UTC+1)</option>
                            <option value="Europe/Amsterdam">Europa/Amsterdam (UTC+1)</option>
                            <option value="Europe/Vienna">Europa/Vienna (UTC+1)</option>
                            <option value="Europe/Zurich">Europa/Zurigo (UTC+1)</option>
                            <option value="Europe/Brussels">Europa/Bruxelles (UTC+1)</option>
                            <option value="Europe/Stockholm">Europa/Stoccolma (UTC+1)</option>
                            <option value="Europe/Oslo">Europa/Oslo (UTC+1)</option>
                            <option value="Europe/Copenhagen">Europa/Copenaghen (UTC+1)</option>
                            <option value="Europe/Helsinki">Europa/Helsinki (UTC+2)</option>
                            <option value="Europe/Athens">Europa/Atene (UTC+2)</option>
                            <option value="Europe/Warsaw">Europa/Varsavia (UTC+1)</option>
                            <option value="Europe/Prague">Europa/Praga (UTC+1)</option>
                            <option value="Europe/Budapest">Europa/Budapest (UTC+1)</option>
                            <option value="Europe/Bucharest">Europa/Bucarest (UTC+2)</option>
                            <option value="Europe/Sofia">Europa/Sofia (UTC+2)</option>
                            <option value="Europe/Kiev">Europa/Kiev (UTC+2)</option>
                            <option value="Europe/Moscow">Europa/Mosca (UTC+3)</option>
                            <option value="Europe/Istanbul">Europa/Istanbul (UTC+3)</option>
                            <option value="Europe/Dublin">Europa/Dublino (UTC+0)</option>
                            <option value="Europe/Lisbon">Europa/Lisbona (UTC+0)</option>
                            <option value="America/New_York">America/New York (UTC-5)</option>
                            <option value="America/Los_Angeles">America/Los Angeles (UTC-8)</option>
                            <option value="America/Chicago">America/Chicago (UTC-6)</option>
                            <option value="America/Denver">America/Denver (UTC-7)</option>
                            <option value="America/Phoenix">America/Phoenix (UTC-7)</option>
                            <option value="America/Toronto">America/Toronto (UTC-5)</option>
                            <option value="America/Vancouver">America/Vancouver (UTC-8)</option>
                            <option value="America/Mexico_City">America/Città del Messico (UTC-6)</option>
                            <option value="America/Sao_Paulo">America/San Paolo (UTC-3)</option>
                            <option value="America/Buenos_Aires">America/Buenos Aires (UTC-3)</option>
                            <option value="America/Lima">America/Lima (UTC-5)</option>
                            <option value="America/Bogota">America/Bogotà (UTC-5)</option>
                            <option value="America/Santiago">America/Santiago (UTC-4)</option>
                            <option value="Asia/Tokyo">Asia/Tokyo (UTC+9)</option>
                            <option value="Asia/Shanghai">Asia/Shanghai (UTC+8)</option>
                            <option value="Asia/Hong_Kong">Asia/Hong Kong (UTC+8)</option>
                            <option value="Asia/Singapore">Asia/Singapore (UTC+8)</option>
                            <option value="Asia/Seoul">Asia/Seoul (UTC+9)</option>
                            <option value="Asia/Bangkok">Asia/Bangkok (UTC+7)</option>
                            <option value="Asia/Mumbai">Asia/Mumbai (UTC+5:30)</option>
                            <option value="Asia/Dubai">Asia/Dubai (UTC+4)</option>
                            <option value="Asia/Tehran">Asia/Tehran (UTC+3:30)</option>
                            <option value="Asia/Jerusalem">Asia/Gerusalemme (UTC+2)</option>
                            <option value="Asia/Riyadh">Asia/Riyadh (UTC+3)</option>
                            <option value="Asia/Karachi">Asia/Karachi (UTC+5)</option>
                            <option value="Asia/Dhaka">Asia/Dhaka (UTC+6)</option>
                            <option value="Asia/Jakarta">Asia/Jakarta (UTC+7)</option>
                            <option value="Asia/Manila">Asia/Manila (UTC+8)</option>
                            <option value="Australia/Sydney">Australia/Sydney (UTC+10)</option>
                            <option value="Australia/Melbourne">Australia/Melbourne (UTC+10)</option>
                            <option value="Australia/Perth">Australia/Perth (UTC+8)</option>
                            <option value="Australia/Brisbane">Australia/Brisbane (UTC+10)</option>
                            <option value="Pacific/Auckland">Pacifico/Auckland (UTC+12)</option>
                            <option value="Pacific/Honolulu">Pacifico/Honolulu (UTC-10)</option>
                            <option value="Pacific/Fiji">Pacifico/Fiji (UTC+12)</option>
                            <option value="Africa/Cairo">Africa/Il Cairo (UTC+2)</option>
                            <option value="Africa/Lagos">Africa/Lagos (UTC+1)</option>
                            <option value="Africa/Johannesburg">Africa/Johannesburg (UTC+2)</option>
                            <option value="Africa/Casablanca">Africa/Casablanca (UTC+0)</option>
                            <option value="Africa/Nairobi">Africa/Nairobi (UTC+3)</option>
                            <option value="Atlantic/Reykjavik">Atlantico/Reykjavik (UTC+0)</option>
                            <option value="GMT">GMT (UTC+0)</option>
                            <option value="UTC">UTC (UTC+0)</option>
                        </select>
                    </div>
                </div>
                
                <div class="calendar-footer">
                    <button onclick="saveOrarioConfig()" class="save-btn">
                        Salva Configurazione
                    </button>
                </div>
            `;
            
            // Gestione pausa pranzo
            setTimeout(() => {
                const pausaCheckbox = document.getElementById('pausa-pranzo');
                const pausaConfig = document.getElementById('pausa-config');
                pausaCheckbox.addEventListener('change', function() {
                    pausaConfig.style.display = this.checked ? 'flex' : 'none';
                });
            }, 0);
            break;
            
        default:
            contentDiv.innerHTML = `
                <h2>Sezione: ${type}</h2>
                <p>Contenuto per la sezione ${type} in sviluppo.</p>
            `;
    }
    
    return contentDiv;
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('popup-overlay')) {
            closePopup(e.target.querySelector('.popup-content').id);
        }
    });
    
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            activePopups.forEach((popup, type) => {
                closePopup(`popup-${type}`);
            });
        }
    });
});

// Salva configurazione orari
window.saveOrarioConfig = function() {
    const config = {
        apertura: document.getElementById('orario-apertura').value,
        chiusura: document.getElementById('orario-chiusura').value,
        pausaPranzo: document.getElementById('pausa-pranzo').checked,
        pausaInizio: document.getElementById('pausa-inizio').value,
        pausaFine: document.getElementById('pausa-fine').value,
        fusoOrario: document.getElementById('fuso-orario').value,
        giorniLavorativi: Array.from(document.querySelectorAll('#popup-orario input[type="checkbox"]'))
            .slice(1).map(cb => cb.checked)
    };
    
    localStorage.setItem('orarioConfig', JSON.stringify(config));
    alert('✅ Configurazione salvata!');
    closePopup('popup-orario');
};

// Funzioni gestione utenti tipo Excel
let userIdCounter = 4; // Contatore per nuovi utenti
let serviceIdCounter = 4; // Contatore per nuovi servizi

window.addNewUser = function() {
    const tbody = document.getElementById('users-list');
    if (!tbody) {
        console.error('Elemento users-list non trovato');
        return;
    }
    
    const newRow = document.createElement('tr');
    newRow.setAttribute('data-user-id', userIdCounter);
    
    newRow.innerHTML = `
        <td><input type="checkbox" class="row-select"></td>
        <td><input type="text" value="" placeholder="username" class="cell-input new-user"></td>
        <td><input type="password" value="" placeholder="Password" class="cell-input new-user"></td>
        <td><input type="password" value="" placeholder="Conferma Password" class="cell-input new-user"></td>
        <td>
            <input type="color" value="#3498db" class="cell-color new-user">
        </td>
        <td>
            <select class="cell-select status-select new-user">
                <option value="attivo" selected>Attivo</option>
                <option value="inattivo">Inattivo</option>
                <option value="sospeso">Sospeso</option>
            </select>
        </td>
        <td class="actions-cell">
            <button onclick="deleteUser(${userIdCounter})" class="action-btn btn-delete-single" title="Elimina">🗑️</button>
        </td>
    `;
    
    tbody.appendChild(newRow);
    
    // Focus sull'username del nuovo utente
    newRow.querySelector('input[type="text"]').focus();
    
    userIdCounter++;
    updateUserStats();
};

window.deleteUser = function(userId) {
    if (confirm('Sei sicuro di voler eliminare questo utente?')) {
        const row = document.querySelector(`tr[data-user-id="${userId}"]`);
        if (row) {
            row.remove();
            updateUserStats();
        }
    }
};

window.deleteSelectedUsers = function() {
    const selectedRows = document.querySelectorAll('.row-select:checked');
    if (selectedRows.length === 0) {
        alert('Seleziona almeno un utente da eliminare');
        return;
    }
    
    if (confirm(`Sei sicuro di voler eliminare ${selectedRows.length} utenti selezionati?`)) {
        selectedRows.forEach(checkbox => {
            checkbox.closest('tr').remove();
        });
        updateUserStats();
        const selectAllCheckbox = document.getElementById('select-all-users');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
        }
    }
};

window.toggleSelectAll = function() {
    const selectAll = document.getElementById('select-all-users');
    const rowSelects = document.querySelectorAll('.row-select');
    
    if (selectAll && rowSelects.length > 0) {
        rowSelects.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
        updateUserStats();
    }
};

window.saveAllUsers = function() {
    const rows = document.querySelectorAll('#users-list tr');
    const users = [];
    
    rows.forEach(row => {
        const userId = row.getAttribute('data-user-id');
        const inputs = row.querySelectorAll('.cell-input, .cell-select, .cell-color');
        
        const user = {
            id: userId,
            username: inputs[0].value.trim(),
            password: inputs[1].value,
            confermaPassword: inputs[2].value,
            colore: inputs[3].value,
            stato: inputs[4].value
        };
        
        // Validazione base
        if (user.username) {
            // Verifica corrispondenza password se sono entrambe compilate
            if (user.password && user.password !== user.confermaPassword) {
                alert(`⚠️ Le password per ${user.username} non corrispondono!`);
                return;
            }
            users.push(user);
        }
    });
    
    // Salva nel localStorage
    localStorage.setItem('usersData', JSON.stringify(users));
    
    // Feedback visivo
    alert(`✅ Salvati ${users.length} utenti!`);
    
    // Rimuovi evidenziazione dai nuovi utenti
    document.querySelectorAll('.new-user').forEach(element => {
        element.classList.remove('new-user');
    });
};

function updateUserStats() {
    const totalUsers = document.querySelectorAll('#users-list tr').length;
    const activeUsers = document.querySelectorAll('.status-select option[value="attivo"]:checked').length;
    const selectedUsers = document.querySelectorAll('.row-select:checked').length;
    
    const totalUsersEl = document.getElementById('total-users');
    const activeUsersEl = document.getElementById('active-users');
    const selectedUsersEl = document.getElementById('selected-users');
    
    if (totalUsersEl) totalUsersEl.textContent = totalUsers;
    if (activeUsersEl) activeUsersEl.textContent = activeUsers;
    if (selectedUsersEl) selectedUsersEl.textContent = selectedUsers;
}

// Event listener per aggiornare stats quando cambia selezione
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('row-select')) {
        updateUserStats();
    }
    if (e.target.classList.contains('row-select-service')) {
        updateServiceStats();
    }
    if (e.target.classList.contains('price-input') || e.target.classList.contains('duration-input')) {
        updateServiceStats();
    }
});

// Funzioni gestione servizi semplificato
window.addNewService = function() {
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
        <td><input type="number" value="0.00" step="0.01" min="0" placeholder="0.00" class="cell-input price-input new-service"></td>
        <td><input type="number" value="30" min="1" max="480" placeholder="30" class="cell-input duration-input new-service"></td>
        <td><input type="text" value="" placeholder="Descrizione servizio" class="cell-input new-service"></td>
        <td>
            <select class="cell-select status-select new-service">
                <option value="attivo" selected>Attivo</option>
                <option value="inattivo">Inattivo</option>
                <option value="sospeso">Sospeso</option>
            </select>
        </td>
        <td class="actions-cell">
            <button onclick="deleteService(${serviceIdCounter})" class="action-btn btn-delete-single" title="Elimina">🗑️</button>
        </td>
    `;
    
    tbody.appendChild(newRow);
    
    // Focus sul nome del nuovo servizio
    newRow.querySelector('input[type="text"]').focus();
    
    serviceIdCounter++;
    updateServiceStats();
};

window.deleteService = function(serviceId) {
    if (confirm('Sei sicuro di voler eliminare questo servizio?')) {
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
        selectedRows.forEach(checkbox => {
            checkbox.closest('tr').remove();
        });
        updateServiceStats();
        document.getElementById('select-all-services').checked = false;
    }
};

window.toggleSelectAllServices = function() {
    const selectAll = document.getElementById('select-all-services');
    const rowSelects = document.querySelectorAll('.row-select-service');
    
    rowSelects.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateServiceStats();
};

window.saveAllServices = function() {
    const rows = document.querySelectorAll('#services-list tr');
    const services = [];
    
    rows.forEach(row => {
        const serviceId = row.getAttribute('data-service-id');
        const inputs = row.querySelectorAll('.cell-input, .cell-select, .cell-color');
        
        const service = {
            id: serviceId,
            nome: inputs[0].value.trim(),
            prezzo: parseFloat(inputs[1].value) || 0,
            tempoMinuti: parseInt(inputs[2].value) || 30,
            descrizione: inputs[3].value.trim(),
            stato: inputs[4].value
        };
        
        // Validazione base
        if (service.nome && service.prezzo >= 0 && service.tempoMinuti > 0) {
            services.push(service);
        }
    });
    
    // Salva nel localStorage
    localStorage.setItem('servicesData', JSON.stringify(services));
    
    // Feedback visivo
    alert(`✅ Salvati ${services.length} servizi!`);
    
    // Rimuovi evidenziazione dai nuovi servizi
    document.querySelectorAll('.new-service').forEach(element => {
        element.classList.remove('new-service');
    });
};

function updateServiceStats() {
    const totalServices = document.querySelectorAll('#services-list tr').length;
    const selectedServices = document.querySelectorAll('.row-select-service:checked').length;
    
    const totalServicesEl = document.getElementById('total-services');
    const selectedServicesEl = document.getElementById('selected-services');
    
    if (totalServicesEl) {
        totalServicesEl.textContent = totalServices;
    }
    if (selectedServicesEl) {
        selectedServicesEl.textContent = selectedServices;
    }
}