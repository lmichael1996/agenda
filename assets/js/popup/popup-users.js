// ========== GESTIONE UTENTI ==========
let userIdCounter = 4;

// Template HTML per il popup utenti
function getUsersPopupContent() {
    return `
        <div class="calendar-header">
            <h2>Gestione Utenti</h2>
            <p>Aggiungi e modifica utenti del sistema</p>
        </div>
        
        <div class="calendar-body">
            <div class="users-section">
                <div class="users-toolbar">
                    <button data-action="addNewUser" class="toolbar-btn btn-add">
                        <span>➕</span> Nuovo Utente
                    </button>
                    <button data-action="deleteSelectedUsers" class="toolbar-btn btn-delete">
                        <span>🗑️</span> Elimina Selezionati
                    </button>
                </div>

                <div class="users-table-container">
                    <table class="excel-table" id="users-table">
                        <thead>
                            <tr>
                                <th class="select-col">
                                    <input type="checkbox" id="select-all-users" data-action="toggleSelectAll">
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
                                    <button data-action="deleteUser" data-user-id="1" class="action-btn btn-delete-single" title="Elimina">🗑️</button>
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
                                    <button data-action="deleteUser" data-user-id="2" class="action-btn btn-delete-single" title="Elimina">🗑️</button>
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
                                    <button data-action="deleteUser" data-user-id="3" class="action-btn btn-delete-single" title="Elimina">🗑️</button>
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
            <button data-action="saveAllUsers" class="save-btn">
                Salva Tutti gli Utenti
            </button>
        </div>
    `;
}

// Funzioni gestione utenti
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
            <button data-action="deleteUser" data-user-id="${userIdCounter}" class="action-btn btn-delete-single" title="Elimina">🗑️</button>
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
            username: inputs[0]?.value?.trim() || '',
            password: inputs[1]?.value || '',
            confermaPassword: inputs[2]?.value || '',
            colore: inputs[3]?.value || '#3498db',
            stato: inputs[4]?.value || 'attivo'
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
    
    localStorage.setItem('usersData', JSON.stringify(users));
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

// Carica dati utenti salvati
function loadSavedUsers() {
    const savedUsers = localStorage.getItem('usersData');
    if (savedUsers) {
        try {
            const users = JSON.parse(savedUsers);
            // Logica per ripopolare la tabella con i dati salvati
            console.log('Utenti caricati:', users);
        } catch (error) {
            console.error('Errore nel caricamento degli utenti:', error);
        }
    }
}

// Inizializza quando il popup utenti viene aperto
document.addEventListener('DOMContentLoaded', function() {
    // Auto-carica gli utenti quando viene aperto il popup
    document.addEventListener('click', function(e) {
        if (e.target.getAttribute('data-action') === 'openPopup' && 
            e.target.getAttribute('data-popup-type') === 'user') {
            setTimeout(loadSavedUsers, 100);
        }
    });
});