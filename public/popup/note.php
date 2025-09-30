<?php
/**
 * Popup per la gestione delle note - Finestra separata
 * Struttura orizzontale, stile uniforme con popup.css
 */
require_once '../../config/config.php';

// Il config.php gestisce automaticamente tutti i controlli per i popup
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Note - Agenda</title>
    <link rel="stylesheet" href="../../assets/css/note-popup.css">
</head>
<body>
    <div class="popup-window-container">
        <div class="window-header">
            <span class="header-title">Gestione Nota</span>
        </div>
        <div class="calendar-body note-calendar-body">
            <form id="note-form" class="note-form">
                <table class="excel-table note-table">
                    <tbody>
                        <tr>
                            <th class="note-table-th">Titolo</th>
                            <td><input type="text" id="note-title" class="cell-input" placeholder="Titolo nota..." maxlength="100"></td>
                        </tr>
                        <tr>
                            <th class="note-table-th">Nota</th>
                            <td><textarea id="note-content" class="cell-textarea" placeholder="Scrivi la nota..." rows="3" maxlength="500"></textarea></td>
                        </tr>
                        <tr>
                            <th class="note-table-th">Utente</th>
                            <td>
                                <select id="note-user" class="cell-input">
                                    <option value="">Caricamento utenti...</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th class="note-table-th">Tutti</th>
                            <td><input type="checkbox" id="note-all" class="cell-input note-checkbox"></td>
                        </tr>
                        <tr>
                            <th class="note-table-th">Data e ora</th>
                            <td>
                                <div class="date-time-container">
                                    <input type="date" id="note-date" class="cell-input date-input" value="<?= date('Y-m-d') ?>">
                                    <input type="number" id="note-hour" class="cell-input hour-input" min="0" max="23" value="12">
                                    <select id="note-minute" class="cell-input minute-select">
                                        <?php foreach (["00","15","30","45"] as $m): ?>
                                            <option value="<?= $m ?>"><?= $m ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th class="note-table-th">Tempo (min)</th>
                            <td><input type="number" id="note-time" class="cell-input" min="0" max="1440" placeholder="Minuti" step="15" value="30"></td>
                        </tr>
                    </tbody>
                </table>
                <div class="note-save-container">
                    <button class="save-btn note-save-btn" id="save-note-btn">Salva Nota</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        // Carica utenti tramite API
        async function loadUsers() {
            try {
                const response = await fetch('../../api/backend/user-api.php');
                const userSelect = document.getElementById('note-user');
                const data = await response.json();
                
                if (data.success && data.users) {
                    userSelect.innerHTML = '<option value="">Seleziona utente...</option>';
                    data.users.forEach(user => {
                        if (user.is_active == 1) { // Solo utenti attivi
                            const option = document.createElement('option');
                            option.value = user.id;
                            option.textContent = user.username;
                            userSelect.appendChild(option);
                        }
                    });
                } else {
                    userSelect.innerHTML = '<option value="">Errore caricamento utenti</option>';
                }
            } catch (error) {
                console.error('Errore caricamento utenti:', error);
                document.getElementById('note-user').innerHTML = '<option value="">Errore caricamento utenti</option>';
            }
        }

        // Inizializza al caricamento della pagina
        document.addEventListener('DOMContentLoaded', function() {
            loadUsers();
        });

        // Gestione salvataggio nota
        document.getElementById('save-note-btn').addEventListener('click', function(e) {
            e.preventDefault();
            
            // Raccolta dati form
            const noteData = {
                title: document.getElementById('note-title').value,
                content: document.getElementById('note-content').value,
                user_id: document.getElementById('note-user').value,
                all_users: document.getElementById('note-all').checked,
                date: document.getElementById('note-date').value,
                hour: document.getElementById('note-hour').value,
                minute: document.getElementById('note-minute').value,
                duration: document.getElementById('note-time').value
            };
            
            console.log('Dati nota:', noteData);
            // TODO: Implementare salvataggio tramite API
            alert('Nota salvata!');
        });
    </script>
</body>
</html>
