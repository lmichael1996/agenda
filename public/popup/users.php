<?php
/**
 * Popup per la gestione degli utenti - Finestra separata
 * Versione organizzata basata su popup.css
 */

// Carica configurazione e controlli di sicurezza
require_once '../../config/config.php';

// Il config.php gestisce automaticamente tutti i controlli per i popup
// Sostituito: i dati utenti ora vengono caricati dalle API
$sampleUsers = [];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Utenti - Agenda</title>
    <link rel="stylesheet" href="../../assets/css/popup.css">
    <link rel="stylesheet" href="../../assets/css/scrollbar.css">
</head>
<body>

    <div class="popup-window-container">
        <div class="window-header">
            <span class="header-title">Gestione Utenti</span>
        </div>

        <div class="calendar-body" style="padding:4px;">
            <div class="schedules-section">

                <div class="schedules-toolbar">
                    <button id="add-user-btn" class="toolbar-btn">Nuovo Utente</button>
                    <button id="delete-selected-btn" class="toolbar-btn">Elimina Selezionati</button>
                </div>

                <div class="schedules-table-container">
                    <table class="excel-table" id="users-table">
                        <thead>
                            <tr>
                                <th class="select-col"><input type="checkbox" id="select-all-users"></th>
                                <th class="username-col" style="width: 120px;">Username</th>
                                <th class="password-col" style="width: 200px;">Password</th>
                                <th class="color-col">Colore</th>
                                <th class="status-col">Stato</th>
                                <th class="actions-col">Azioni</th>
                            </tr>
                        </thead>
                        <tbody id="users-list">
                            <!-- I dati vengono caricati via JS -->
                        </tbody>
                    </table>
                </div>

                <div class="users-stats">
                    <span>Totale utenti: <strong id="total-users"></strong></span>
                    <span>Selezionati: <strong id="selected-users">0</strong></span>
                </div>

                <div style="margin-top:20px; margin-bottom:20px; text-align:center;">
                    <button id="save-all-btn" class="save-btn">Salva Tutti gli Utenti</button>
                </div>

            </div>
        </div>
    </div>

    <script type="module">
        import { fetchUsers, saveAllUsers } from '../../api/frontend/user-api.js';

        let usersList = [];
        let userIdCounter = 1;

        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function generateUserRow(user) {
            const isNewUser = String(user.id).startsWith('temp_');
            const passwordPlaceholder = isNewUser ? "Inserisci password" : "Vuoto = mantieni";
            
            return `
                <tr data-user-id="${user.id}">
                    <td><input type="checkbox" class="row-select"></td>
                    <td><input type="text" value="${escapeHtml(user.username)}" class="cell-input"></td>
                    <td><input type="password" value="" placeholder="${passwordPlaceholder}" class="cell-input"></td>
                    <td><input type="color" value="${escapeHtml(user.color)}" class="cell-color"></td>
                    <td>
                        <select class="status-select">
                            <option value="attivo" ${user.is_active == 1 ? 'selected' : ''}>Attivo</option>
                            <option value="inattivo" ${user.is_active == 0 ? 'selected' : ''}>Inattivo</option>
                        </select>
                    </td>
                    <td class="actions-cell">
                        <button class="action-btn btn-delete-single" data-user-id="${user.id}" title="Elimina">Elimina</button>
                    </td>
                </tr>
            `;
        }

        function renderUsersTable() {
            const tbody = document.getElementById('users-list');
            if (!tbody) return;
            if (!usersList.length) {
                tbody.innerHTML = '<tr><td colspan="6">Nessun utente configurato</td></tr>';
                updateSelectionStats();
                return;
            }
            tbody.innerHTML = usersList.map(generateUserRow).join('');
            updateSelectionStats();
            

        }

        function updateSelectionStats() {
            const totalUsers = usersList.length;
            const selectedUsers = document.querySelectorAll('.row-select:checked').length;
            document.getElementById('total-users').textContent = totalUsers;
            document.getElementById('selected-users').textContent = selectedUsers;
        }

        async function loadUsersFromApi() {
            try {
                const data = await fetchUsers();
                if (data.success && Array.isArray(data.users)) {
                    usersList = data.users;
                    userIdCounter = usersList.length ? Math.max(...usersList.map(u => u.id)) + 1 : 1;
                }
            } catch (e) {
                console.error('Errore caricamento utenti:', e);
            }
        }

        function addNewRow() {
            const newUser = {
                id: 'temp_' + Date.now(),
                username: 'Nuovo Utente ' + (usersList.length + 1),
                color: '#3498db',
                is_active: 1,
                isPending: true
            };
            usersList.push(newUser);
            renderUsersTable();
        }

        function deleteRowOnly(userId) {
            usersList = usersList.filter(u => u.id != userId);
            renderUsersTable();
        }

        function deleteSelectedRows() {
            const selectedCheckboxes = document.querySelectorAll('.row-select:checked');
            if (selectedCheckboxes.length === 0) {
                alert('Nessuna riga selezionata');
                return;
            }
            
            if (confirm(`Eliminare ${selectedCheckboxes.length} righe selezionate dalla tabella?`)) {
                const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.closest('tr').dataset.userId);
                usersList = usersList.filter(u => !selectedIds.includes(String(u.id)));
                renderUsersTable();
                // Deseleziona il checkbox "seleziona tutto"
                document.getElementById('select-all-users').checked = false;
            }
        }

        function collectTableData() {
            const rows = document.querySelectorAll('#users-table tbody tr');
            const users = [];
            
            rows.forEach((row, index) => {
                if (row.cells.length < 6) return; // Salta righe vuote
                
                const username = row.cells[1].querySelector('input').value.trim();
                const password = row.cells[2].querySelector('input').value.trim();
                const color = row.cells[3].querySelector('input').value;
                const isActive = row.cells[4].querySelector('select').value === 'attivo' ? 1 : 0;
                const userId = row.dataset.userId;
                
                if (username) { // Solo se c'è un username
                    const userData = {
                        username,
                        color,
                        is_active: isActive
                    };
                    
                    // Includi password solo se è stata inserita
                    if (password) {
                        userData.password = password;
                    }
                    
                    // Se è un utente esistente, includi l'ID
                    if (userId && !userId.startsWith('temp_')) {
                        userData.id = userId;
                    }
                    
                    users.push(userData);
                }
            });
            
            return users;
        }

        function validateUsersData(users) {
            const errors = [];
            
            users.forEach((user, index) => {
                if (!user.username) {
                    errors.push(`Riga ${index + 1}: Username obbligatorio`);
                }
                
                // Controllo password solo se è stata inserita
                if (user.password) {
                    if (user.password.length < 4) {
                        errors.push(`Riga ${index + 1}: Password troppo corta (minimo 4 caratteri)`);
                    }
                }
                
                // Per nuovi utenti (senza ID), la password è obbligatoria
                if (!user.id && !user.password) {
                    errors.push(`Riga ${index + 1}: Password obbligatoria per nuovi utenti`);
                }
            });
            
            // Controlla username duplicati
            const usernames = users.map(u => u.username.toLowerCase());
            const duplicates = usernames.filter((name, index) => usernames.indexOf(name) !== index);
            if (duplicates.length > 0) {
                errors.push(`Username duplicati: ${[...new Set(duplicates)].join(', ')}`);
            }
            
            return errors;
        }

        async function saveAllUsersToDatabase() {
            const users = collectTableData();
            
            if (users.length === 0) {
                alert('Nessun utente da salvare');
                return;
            }
            
            const validationErrors = validateUsersData(users);
            if (validationErrors.length > 0) {
                alert('Errori di validazione:\n' + validationErrors.join('\n'));
                return;
            }
            
            if (!confirm(`Salvare ${users.length} utenti nel database?\n\nATTENZIONE: Questa operazione sostituirà tutti gli utenti esistenti.`)) {
                return;
            }
            
            const saveBtn = document.getElementById('save-all-btn');
            const originalText = saveBtn.textContent;
            saveBtn.textContent = 'Salvataggio...';
            saveBtn.disabled = true;
            
            try {
                const result = await saveAllUsers(users);
                
                if (result.success) {
                    alert('Utenti salvati con successo!');
                    // Ricarica i dati dal database
                    await loadUsersFromApi();
                    renderUsersTable();
                    
                    // Chiudi la finestra popup dopo il salvataggio
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

        document.addEventListener('click', e => {
            const deleteBtn = e.target.closest('.btn-delete-single');
            if (deleteBtn) {
                const userId = deleteBtn.dataset.userId;
                if (confirm('Eliminare questa riga dalla tabella?')) {
                    deleteRowOnly(userId);
                }
            }
        });

        document.addEventListener('change', e => {
            if (e.target.id === 'select-all-users') {
                const checked = e.target.checked;
                document.querySelectorAll('.row-select').forEach(cb => cb.checked = checked);
                updateSelectionStats();
            }
            
            if (e.target.classList.contains('row-select')) {
                updateSelectionStats();
            }
        });



        document.addEventListener('DOMContentLoaded', async () => {
            await loadUsersFromApi();
            renderUsersTable();
            document.getElementById('add-user-btn')?.addEventListener('click', addNewRow);
            document.getElementById('delete-selected-btn')?.addEventListener('click', deleteSelectedRows);
            document.getElementById('save-all-btn')?.addEventListener('click', saveAllUsersToDatabase);
        });
    </script>

</body>
</html>