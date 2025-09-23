// ========== GESTIONE UTENTI ==========
let userIdCounter = 4;

// Lista utenti centralizzata
let usersList = [
    {
        id: 1,
        username: "mario.rossi",
        color: "#3498db",
        status: "attivo"
    },
    {
        id: 2,
        username: "laura.bianchi", 
        color: "#e74c3c",
        status: "attivo"
    },
    {
        id: 3,
        username: "giuseppe.verdi",
        color: "#2ecc71",
        status: "attivo"
    }
];

// Genera HTML per una singola riga utente
function generateUserRow(user) {
    return `
        <tr data-user-id="${user.id}">
            <td><input type="checkbox" class="row-select"></td>
            <td><input type="text" value="${user.username}" class="cell-input"></td>
            <td><input type="password" value="" placeholder="Password" class="cell-input"></td>
            <td><input type="password" value="" placeholder="Conferma Password" class="cell-input"></td>
            <td>
                <input type="color" value="${user.color}" class="cell-color">
            </td>
            <td>
                <select class="cell-select status-select">
                    <option value="attivo" ${user.status === 'attivo' ? 'selected' : ''}>Attivo</option>
                    <option value="inattivo" ${user.status === 'inattivo' ? 'selected' : ''}>Inattivo</option>
                    <option value="sospeso" ${user.status === 'sospeso' ? 'selected' : ''}>Sospeso</option>
                </select>
            </td>
            <td class="actions-cell">
                <button data-action="deleteUser" data-user-id="${user.id}" class="action-btn btn-delete-single" title="Elimina">🗑️</button>
            </td>
        </tr>
    `;
}

// Genera HTML per tutte le righe utenti
function generateUsersRows() {
    return usersList.map(user => generateUserRow(user)).join('');
}

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
                                <th class="password-col">Vecchia Password</th>
                                <th class="confirm-password-col">Conferma Password</th>
                                <th class="color-col">Colore</th>
                                <th class="status-col">Stato</th>
                                <th class="actions-col">Azioni</th>
                            </tr>
                        </thead>
                        <tbody id="users-list">
                            ${generateUsersRows()}
                        </tbody>
                    </table>
                </div>

                <div class="users-stats">
                    <span class="stat-item">Totale utenti: <strong id="total-users">${usersList.length}</strong></span>
                    <span class="stat-item">Attivi: <strong id="active-users">${usersList.filter(u => u.status === 'attivo').length}</strong></span>
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
    const newUser = {
        id: userIdCounter,
        username: "",
        color: "#3498db",
        status: "attivo"
    };
    
    usersList.push(newUser);
    
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
    newRow.querySelector('input[type="text"]').focus();
    
    userIdCounter++;
    updateUserStats();
};

window.deleteUser = function(userId) {
    if (confirm('Sei sicuro di voler eliminare questo utente?')) {
        // Rimuovi dalla variabile usersList
        usersList = usersList.filter(user => user.id != userId);
        
        // Rimuovi dalla UI
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
        const selectedIds = [];
        selectedRows.forEach(checkbox => {
            const userId = checkbox.closest('tr').getAttribute('data-user-id');
            selectedIds.push(parseInt(userId));
            checkbox.closest('tr').remove();
        });
        
        // Rimuovi dalla variabile usersList
        usersList = usersList.filter(user => !selectedIds.includes(user.id));
        
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
    // Sincronizza dati dalla UI alla variabile usersList
    syncUsersFromUI();
    
    // Salva la lista utenti aggiornata
    localStorage.setItem('usersData', JSON.stringify(usersList));
    alert(`✅ Salvati ${usersList.length} utenti!`);
    
    // Rimuovi evidenziazione dai nuovi utenti
    document.querySelectorAll('.new-user').forEach(element => {
        element.classList.remove('new-user');
    });
};

// Sincronizza dati dalla UI alla variabile usersList
function syncUsersFromUI() {
    const rows = document.querySelectorAll('#users-list tr');
    
    rows.forEach(row => {
        const userId = parseInt(row.getAttribute('data-user-id'));
        const inputs = row.querySelectorAll('.cell-input, .cell-select, .cell-color');
        
        const userData = {
            id: userId,
            username: inputs[0]?.value?.trim() || '',
            color: inputs[3]?.value || '#3498db',
            status: inputs[4]?.value || 'attivo'
        };
        
        // Validazione password se compilata (solo per verifica UI)
        const password = inputs[1]?.value || '';
        const confirmPassword = inputs[2]?.value || '';
        if (password && password !== confirmPassword) {
            alert(`⚠️ Le password per ${userData.username} non corrispondono!`);
            return;
        }
        
        // Trova e aggiorna l'utente nella lista
        const userIndex = usersList.findIndex(u => u.id === userId);
        if (userIndex !== -1) {
            usersList[userIndex] = userData;
        }
    });
}

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
            usersList = JSON.parse(savedUsers);
            
            // Aggiorna il counter con l'ID più alto + 1
            const maxId = Math.max(...usersList.map(u => u.id), 0);
            userIdCounter = maxId + 1;
            
            // Rigenera la tabella con i dati caricati
            const tbody = document.getElementById('users-list');
            if (tbody) {
                tbody.innerHTML = generateUsersRows();
                updateUserStats();
            }
            
            console.log('Utenti caricati:', usersList);
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