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
    <link rel="stylesheet" href="../../assets/css/scheme-popup.css">
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
                                    <input type="date" id="note-date" class="cell-input date-input" value="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                                    <input type="number" id="note-hour" class="cell-input hour-input" value="9">
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
                            <td><input type="number" id="note-time" class="cell-input" min="15" max="1440" placeholder="Minuti" step="15" value="30"></td>
                        </tr>
                    </tbody>
                </table>
                <div class="note-save-container">
                    <button class="save-btn note-save-btn" id="save-note-btn">Salva Nota</button>
                </div>
            </form>
        </div>
    </div>
    <script type="module">
        import { saveNote, formatDateForAPI, validateNoteData } from '../../api/frontend/notes-api.js';
        import { fetchSchedule } from '../../api/frontend/schedule-api.js';
        
        // Carica orario di apertura dalla configurazione schedule
        async function loadOpeningHour() {
            try {
                const response = await fetchSchedule();
                const hourInput = document.getElementById('note-hour');
                
                console.log('Risposta API schedule:', response);
                
                // Gestisci diversi formati di risposta
                let openingTime, closingTime;
                
                if (response && response.success && response.data) {
                    // Formato API: { success: true, data: {...} }
                    openingTime = response.data.opening_time;
                    closingTime = response.data.closing_time;
                    console.log('Trovato formato API con data:', response.data);
                } else if (response && response.success && response.schedule) {
                    // Formato con success e schedule
                    openingTime = response.schedule.opening_time;
                    closingTime = response.schedule.closing_time;
                    console.log('Trovato formato con schedule:', response.schedule);
                } else if (response && response.opening_time) {
                    // Formato diretto
                    openingTime = response.opening_time;
                    closingTime = response.closing_time;
                    console.log('Trovato formato diretto:', { openingTime, closingTime });
                } else {
                    openingTime = null;
                    closingTime = null;
                    console.warn('Nessun formato riconosciuto nella risposta');
                }
                
                if (openingTime && closingTime) {
                    // Estrai l'ora dal formato HH:MM:SS
                    const openingHour = parseInt(openingTime.split(':')[0]);
                    const closingHour = parseInt(closingTime.split(':')[0]);
                    
                    // Validazione dei valori
                    if (openingHour >= 0 && openingHour <= 23 && closingHour >= 0 && closingHour <= 23 && openingHour < closingHour) {
                        // Imposta valore, min e max dell'input
                        hourInput.value = openingHour;
                        hourInput.min = openingHour;
                        hourInput.max = closingHour;
                        
                        console.log('Orari caricati con successo:', {
                            opening: openingTime,
                            closing: closingTime,
                            openingHour: openingHour,
                            closingHour: closingHour
                        });
                        return; // Esci dalla funzione se tutto va bene
                    } else {
                        console.warn('Orari non validi:', { openingHour, closingHour });
                    }
                } else {
                    console.warn('Orari non trovati nella risposta API');
                }
                
                // Fallback se non ci sono orari validi
                console.warn('Uso orari di default: 9 (8-18)');
                hourInput.value = 9;
                hourInput.min = 8;
                hourInput.max = 18;
                
            } catch (error) {
                console.error('Errore caricamento orario apertura:', error);
                // Fallback in caso di errore
                const hourInput = document.getElementById('note-hour');
                hourInput.value = 9;
                hourInput.min = 8;
                hourInput.max = 18;
                console.log('Applicati orari di fallback: 9 (8-18)');
            }
        }
        
        // Carica utenti tramite API
        async function loadUsers() {
            try {
                const response = await fetch('../../api/backend/user-api.php');
                const userSelect = document.getElementById('note-user');
                const data = await response.json();
                
                if (data.success && data.users) {
                    userSelect.innerHTML = '';
                    let firstActiveUser = null;
                    data.users.forEach(user => {
                        if (user.is_active == 1) { // Solo utenti attivi
                            const option = document.createElement('option');
                            option.value = user.id;
                            option.textContent = user.username;
                            userSelect.appendChild(option);
                            
                            // Memorizza il primo utente attivo
                            if (!firstActiveUser) {
                                firstActiveUser = user.id;
                            }
                        }
                    });
                    
                    // Seleziona automaticamente il primo utente attivo
                    if (firstActiveUser) {
                        userSelect.value = firstActiveUser;
                    }
                } else {
                    userSelect.innerHTML = '<option value="">Errore caricamento utenti</option>';
                }
            } catch (error) {
                console.error('Errore caricamento utenti:', error);
                document.getElementById('note-user').innerHTML = '<option value="">Errore caricamento utenti</option>';
            }
        }

        // Contatori caratteri
        function setupCharacterCounters() {
            const titleInput = document.getElementById('note-title');
            const contentInput = document.getElementById('note-content');
            
            // Aggiorna placeholder con contatore
            function updateTitleCounter() {
                titleInput.placeholder = `Titolo nota...`;
            }
            
            function updateContentCounter() {
                contentInput.placeholder = `Scrivi la nota...`;
            }
            
            titleInput.addEventListener('input', updateTitleCounter);
            contentInput.addEventListener('input', updateContentCounter);
            
            // Inizializza contatori
            updateTitleCounter();
            updateContentCounter();
        }
        
        // Inizializza al caricamento della pagina
        document.addEventListener('DOMContentLoaded', function() {
            loadUsers();
            loadOpeningHour();
            setupCharacterCounters();
            
            // Focus sul primo campo
            document.getElementById('note-title').focus();
        });
        
        // Rendi la funzione loadUsers disponibile globalmente per compatibilità
        window.loadUsers = loadUsers;

        // Gestione salvataggio nota
        document.getElementById('save-note-btn').addEventListener('click', async function(e) {
            e.preventDefault();
            
            const saveBtn = this;
            const originalText = saveBtn.textContent;
            
            try {
                // Disabilita il pulsante durante il salvataggio
                saveBtn.disabled = true;
                saveBtn.textContent = 'Salvataggio...';
                
                // Raccolta dati form
                const formData = {
                    title: document.getElementById('note-title').value.trim(),
                    content: document.getElementById('note-content').value.trim(),
                    user_id: document.getElementById('note-user').value,
                    for_all: document.getElementById('note-all').checked,
                    note_date: document.getElementById('note-date').value
                };
                
                // Validazione dati obbligatori
                if (!formData.user_id) {
                    alert('Seleziona un utente');
                    return;
                }
                
                if (!formData.note_date) {
                    alert('Seleziona una data');
                    return;
                }
                
                if (!formData.title && !formData.content) {
                    alert('Inserisci almeno il titolo o il contenuto della nota');
                    return;
                }
                
                // Validazione tramite API
                const validation = validateNoteData(formData);
                if (!validation.valid) {
                    alert('Errori di validazione:\n' + validation.errors.join('\n'));
                    return;
                }
                
                console.log('Invio dati nota:', formData);
                
                // Salvataggio tramite API
                const result = await saveNote(formData);
                
                if (result.success) {
                    alert('Nota salvata con successo!');
                    
                    // Reset form
                    document.getElementById('note-title').value = '';
                    document.getElementById('note-content').value = '';
                    document.getElementById('note-all').checked = false;
                    // Imposta data per il giorno dopo
                    const tomorrow = new Date();
                    tomorrow.setDate(tomorrow.getDate() + 1);
                    document.getElementById('note-date').value = tomorrow.toISOString().split('T')[0];
                    // Ricarica l'orario di apertura per il reset
                    await loadOpeningHour();
                    document.getElementById('note-minute').value = '00';
                    document.getElementById('note-time').value = '30';
                    
                    // Chiudi la finestra se è un popup
                    if (window.opener) {
                        window.close();
                    }
                } else {
                    alert('Errore durante il salvataggio:\n' + result.error);
                }
                
            } catch (error) {
                console.error('Errore salvataggio nota:', error);
                alert('Errore di connessione durante il salvataggio');
            } finally {
                // Riabilita il pulsante
                saveBtn.disabled = false;
                saveBtn.textContent = originalText;
            }
        });
    </script>
</body>
</html>
