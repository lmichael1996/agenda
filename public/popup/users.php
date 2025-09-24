<?php
/**
 * Popup per la gestione degli utenti - Finestra separata
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
header('X-Frame-Options: SAMEORIGIN');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Dati di esempio per gli utenti
$sampleUsers = [
    [
        'id' => 1,
        'username' => 'mario.rossi',
        'color' => '#3498db',
        'status' => 'attivo'
    ],
    [
        'id' => 2,
        'username' => 'laura.bianchi',
        'color' => '#e74c3c',
        'status' => 'attivo'
    ],
    [
        'id' => 3,
        'username' => 'giuseppe.verdi',
        'color' => '#2ecc71',
        'status' => 'attivo'
    ]
];
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
            <!-- Pulsante chiusura rimosso -->
        </div>

        <div class="calendar-body" style="padding:4px;">
            <div class="schedules-section">

                <div class="schedules-toolbar">
                    <button id="add-user-btn" class="toolbar-btn">➕ Nuovo Utente</button>
                    <button id="delete-selected-btn" class="toolbar-btn">🗑️ Elimina Selezionati</button>
                </div>

                <div class="schedules-table-container">
                    <table class="excel-table" id="users-table">
                        <thead>
                            <tr>
                                <th class="select-col"><input type="checkbox" id="select-all-users"></th>
                                <th class="username-col">Username</th>
                                <th class="password-col">Password</th>
                                <th class="confirm-password-col">Conferma Password</th>
                                <th class="color-col">Colore</th>
                                <th class="status-col">Stato</th>
                                <th class="actions-col">Azioni</th>
                            </tr>
                        </thead>
                        <tbody id="users-list">
                            <?php foreach ($sampleUsers as $user): ?>
                                <tr data-user-id="<?= $user['id'] ?>">
                                    <td><input type="checkbox" class="row-select"></td>
                                    <td><input type="text" value="<?= htmlspecialchars($user['username']) ?>" placeholder="Username..." class="cell-input"></td>
                                    <td><input type="password" value="" placeholder="Password" class="cell-input"></td>
                                    <td><input type="password" value="" placeholder="Conferma Password" class="cell-input"></td>
                                    <td><input type="color" value="<?= $user['color'] ?>" class="cell-color"></td>
                                    <td>
                                        <select class="status-select">
                                            <option value="attivo" <?= $user['status'] === 'attivo' ? 'selected' : '' ?>>Attivo</option>
                                            <option value="inattivo" <?= $user['status'] === 'inattivo' ? 'selected' : '' ?>>Inattivo</option>
                                            <option value="sospeso" <?= $user['status'] === 'sospeso' ? 'selected' : '' ?>>Sospeso</option>
                                        </select>
                                    </td>
                                    <td class="actions-cell"><button class="action-btn btn-delete-single" data-user-id="<?= $user['id'] ?>" title="Elimina">🗑️</button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="users-stats">
                    <span>Totale utenti: <strong id="total-users"><?= count($sampleUsers) ?></strong></span>
                    <span>Attivi: <strong id="active-users"><?= count(array_filter($sampleUsers, fn($u) => $u['status'] === 'attivo')) ?></strong></span>
                    <span>Selezionati: <strong id="selected-users">0</strong></span>
                </div>

                <div style="margin-top:20px; margin-bottom:20px; text-align:center;">
                    <button id="save-all-btn" class="save-btn">💾 Salva Tutti gli Utenti</button>
                </div>

            </div>
        </div>
    </div>

    <script>
        // ========== GESTIONE UTENTI ==========
        let usersList = <?= json_encode($sampleUsers) ?>;
        const STORAGE_KEY = 'users_data';
        let userIdCounter = Math.max(...usersList.map(u => u.id)) + 1;

        // Funzioni di utilità
        function loadUsersFromStorage() {
            try {
                const raw = localStorage.getItem(STORAGE_KEY);
                if (!raw) return;
                const parsed = JSON.parse(raw);
                if (Array.isArray(parsed.users)) {
                    usersList = parsed.users;
                    userIdCounter = parsed.counter || (Math.max(...usersList.map(u => u.id)) + 1);
                }
            } catch (e) {
                console.warn('Impossibile leggere storage utenti:', e);
            }
        }

        function saveUsersToStorage() {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify({
                    users: usersList,
                    counter: userIdCounter
                }));
            } catch (e) {
                console.error('Errore salvataggio storage utenti:', e);
            }
        }

        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function generateUserRow(user) {
            return `
                <tr data-user-id="${user.id}">
                    <td><input type="checkbox" class="row-select"></td>
                    <td><input type="text" value="${escapeHtml(user.username)}" class="cell-input"></td>
                    <td><input type="password" value="" placeholder="Password" class="cell-input"></td>
                    <td><input type="password" value="" placeholder="Conferma Password" class="cell-input"></td>
                    <td><input type="color" value="${escapeHtml(user.color)}" class="cell-color"></td>
                    <td>
                        <select class="status-select">
                            <option value="attivo" ${user.status === 'attivo' ? 'selected' : ''}>Attivo</option>
                            <option value="inattivo" ${user.status === 'inattivo' ? 'selected' : ''}>Inattivo</option>
                            <option value="sospeso" ${user.status === 'sospeso' ? 'selected' : ''}>Sospeso</option>
                        </select>
                    </td>
                    <td class="actions-cell"><button class="action-btn btn-delete-single" data-user-id="${user.id}" title="Elimina">🗑️</button></td>
                </tr>
            `;
        }

        function renderUsersTable() {
            const tbody = document.getElementById('users-list');
            if (!tbody) return;
            
            if (!usersList.length) {
                tbody.innerHTML = '<tr><td colspan="7">Nessun utente configurato</td></tr>';
                updateSelectionStats();
                return;
            }
            
            tbody.innerHTML = usersList.map(generateUserRow).join('');
            updateSelectionStats();
        }

        function updateSelectionStats() {
            const totalUsers = usersList.length;
            const activeUsers = usersList.filter(u => u.status === 'attivo').length;
            const selectedUsers = document.querySelectorAll('.row-select:checked').length;
            
            document.getElementById('total-users').textContent = totalUsers;
            document.getElementById('active-users').textContent = activeUsers;
            document.getElementById('selected-users').textContent = selectedUsers;
        }

        function syncRowToModel(row, id) {
            const user = usersList.find(u => u.id === id);
            if (!user) return;
            
            const inputs = row.querySelectorAll('.cell-input, .cell-color, .status-select');
            user.username = inputs[0]?.value || '';
            user.color = inputs[3]?.value || '#3498db';
            user.status = inputs[4]?.value || 'attivo';
            
            // Validazione password
            const password = inputs[1]?.value || '';
            const confirmPassword = inputs[2]?.value || '';
            if (password && password !== confirmPassword) {
                alert(`⚠️ Le password per ${user.username} non corrispondono!`);
                return false;
            }
            
            saveUsersToStorage();
            return true;
        }

        // Gestori eventi principali
        function onAddUser() {
            const newUser = {
                id: userIdCounter++,
                username: 'Nuovo Utente ' + userIdCounter,
                color: '#3498db',
                status: 'attivo'
            };
            
            usersList.push(newUser);
            renderUsersTable();
            saveUsersToStorage();
            
            setTimeout(() => {
                const newRow = document.querySelector(`tr[data-user-id="${newUser.id}"] input[type="text"]`);
                if (newRow) newRow.focus();
            }, 0);
        }

        function onDeleteSingle(id) {
            const user = usersList.find(u => u.id === id);
            if (!user) return;
            
            if (!confirm('Eliminare questo utente?')) return;
            
            usersList = usersList.filter(u => u.id !== id);
            renderUsersTable();
            saveUsersToStorage();
        }

        function onDeleteSelected() {
            const ids = Array.from(document.querySelectorAll('.row-select:checked'))
                .map(cb => parseInt(cb.closest('tr').dataset.userId));
            
            if (ids.length === 0) {
                alert('Nessun utente selezionato');
                return;
            }
            
            if (!confirm(`Eliminare ${ids.length} utente/i selezionato/i?`)) return;
            
            usersList = usersList.filter(u => !ids.includes(u.id));
            renderUsersTable();
            saveUsersToStorage();
            document.getElementById('select-all-users').checked = false;
        }

        function onSaveAll() {
            // Sincronizza tutti i dati dalla UI
            document.querySelectorAll('#users-list tr[data-user-id]').forEach(row => {
                syncRowToModel(row, parseInt(row.dataset.userId));
            });
            
            saveUsersToStorage();
            alert('Utenti salvati correttamente!');
        }

        // Event listeners
        document.addEventListener('click', e => {
            const deleteBtn = e.target.closest('.btn-delete-single');
            if (deleteBtn) {
                const id = parseInt(deleteBtn.dataset.userId);
                onDeleteSingle(id);
            }
        });

        document.addEventListener('input', e => {
            if (e.target.classList.contains('cell-input') || 
                e.target.classList.contains('cell-color') || 
                e.target.classList.contains('status-select')) {
                const row = e.target.closest('tr');
                if (row) {
                    syncRowToModel(row, parseInt(row.dataset.userId));
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

        document.addEventListener('DOMContentLoaded', () => {
            loadUsersFromStorage();
            renderUsersTable();
            
            document.getElementById('add-user-btn')?.addEventListener('click', onAddUser);
            document.getElementById('delete-selected-btn')?.addEventListener('click', onDeleteSelected);
            document.getElementById('save-all-btn')?.addEventListener('click', onSaveAll);
            
            document.getElementById('select-all-users')?.addEventListener('change', e => {
                const checked = e.target.checked;
                document.querySelectorAll('.row-select').forEach(cb => cb.checked = checked);
                updateSelectionStats();
            });
        });
    </script>

</body>
</html>