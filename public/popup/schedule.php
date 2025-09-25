<?php
/**
 * Popup per la gestione dell'orario - Finestra separata
 * Struttura singleton come note.php
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

// Orario singleton - valori di default
$defaultSchedule = [
    'name' => 'Orario Standard',
    'startTime' => '09:00',
    'endTime' => '18:00',
    'lunchBreakEnabled' => false,
    'lunchStartTime' => '12:00',
    'lunchEndTime' => '13:00',
    'closureDays' => ['sabato', 'domenica']
];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Orario - Agenda</title>
    <link rel="stylesheet" href="../../assets/css/popup.css">
    <link rel="stylesheet" href="../../assets/css/scrollbar.css">
</head>
<body>
    <div class="popup-window-container">
        <div class="window-header">
            <span class="header-title">Gestione Orario</span>
        </div>
        <div class="calendar-body" style="padding:8px;">
            <form id="schedule-form" style="max-width:600px; margin:auto;">
                <table class="excel-table" style="width:100%; margin-bottom:24px;">
                    <tbody>
                        <tr>
                            <th style="font-size:13px; border: 1px solid white;">Ora Inizio</th>
                            <td>
                                <input type="number" id="schedule-start-hour" class="hour-input" min="0" max="23" value="<?= explode(':', $defaultSchedule['startTime'])[0] ?>"> :
                                <select id="schedule-start-minute" class="minute-select">
                                    <?php foreach (["00","15","30","45"] as $m): ?>
                                        <option value="<?= $m ?>" <?= (explode(':', $defaultSchedule['startTime'])[1] == $m ? 'selected' : '') ?>><?= $m ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th style="font-size:13px; border: 1px solid white;">Ora Fine</th>
                            <td>
                                <input type="number" id="schedule-end-hour" class="hour-input" min="0" max="23" value="<?= explode(':', $defaultSchedule['endTime'])[0] ?>"> :
                                <select id="schedule-end-minute" class="minute-select">
                                    <?php foreach (["00","15","30","45"] as $m): ?>
                                        <option value="<?= $m ?>" <?= (explode(':', $defaultSchedule['endTime'])[1] == $m ? 'selected' : '') ?>><?= $m ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th style="font-size:13px; border: 1px solid white;">Pausa Pranzo</th>
                            <td><input type="checkbox" id="lunch-break-enabled" class="cell-input" style="height:28px; width:28px;"></td>
                        </tr>
                        <tr class="lunch-time-row" id="lunch-start-row">
                            <th style="font-size:13px; border: 1px solid white;">Inizio Pausa</th>
                            <td>
                                <input type="number" id="lunch-start-hour" class="hour-input" min="0" max="23" value="<?= explode(':', $defaultSchedule['lunchStartTime'])[0] ?>"> :
                                <select id="lunch-start-minute" class="minute-select">
                                    <?php foreach (["00","15","30","45"] as $m): ?>
                                        <option value="<?= $m ?>" <?= (explode(':', $defaultSchedule['lunchStartTime'])[1] == $m ? 'selected' : '') ?>><?= $m ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr class="lunch-time-row" id="lunch-end-row">
                            <th style="font-size:13px; border: 1px solid white;">Fine Pausa</th>
                            <td>
                                <input type="number" id="lunch-end-hour" class="hour-input" min="0" max="23" value="<?= explode(':', $defaultSchedule['lunchEndTime'])[0] ?>"> :
                                <select id="lunch-end-minute" class="minute-select">
                                    <?php foreach (["00","15","30","45"] as $m): ?>
                                        <option value="<?= $m ?>" <?= (explode(':', $defaultSchedule['lunchEndTime'])[1] == $m ? 'selected' : '') ?>><?= $m ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th style="font-size:13px; border: 1px solid white;">Giorni Chiusura</th>
                            <td>
                                <select multiple id="schedule-closure-days" class="cell-select" style="width:98%; min-height:80px; font-size:13px;">
                                    <option value="lunedi" <?= in_array('lunedi', $defaultSchedule['closureDays']) ? 'selected' : '' ?>>Lunedì</option>
                                    <option value="martedi" <?= in_array('martedi', $defaultSchedule['closureDays']) ? 'selected' : '' ?>>Martedì</option>
                                    <option value="mercoledi" <?= in_array('mercoledi', $defaultSchedule['closureDays']) ? 'selected' : '' ?>>Mercoledì</option>
                                    <option value="giovedi" <?= in_array('giovedi', $defaultSchedule['closureDays']) ? 'selected' : '' ?>>Giovedì</option>
                                    <option value="venerdi" <?= in_array('venerdi', $defaultSchedule['closureDays']) ? 'selected' : '' ?>>Venerdì</option>
                                    <option value="sabato" <?= in_array('sabato', $defaultSchedule['closureDays']) ? 'selected' : '' ?>>Sabato</option>
                                    <option value="domenica" <?= in_array('domenica', $defaultSchedule['closureDays']) ? 'selected' : '' ?>>Domenica</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div style="text-align:center; border-top: 1px solid #ccc; padding-top: 12px;">
                    <button class="save-btn" id="save-schedule-btn" style="font-size:15px; padding:16px 32px;">💾 Salva Orario</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Gestione orario singleton - stile note.php
        const STORAGE_KEY = 'calendar_schedule_singleton';
        
        // Dati di default dal server
        let currentSchedule = <?= json_encode($defaultSchedule) ?>;
        
        // Carica dati dal localStorage
        function loadScheduleFromStorage() {
            try {
                const raw = localStorage.getItem(STORAGE_KEY);
                if (raw) {
                    const parsed = JSON.parse(raw);
                    if (parsed && typeof parsed === 'object') {
                        currentSchedule = { ...currentSchedule, ...parsed };
                    }
                }
            } catch (e) {
                console.warn('Impossibile leggere storage orario:', e);
            }
        }
        
        // Salva dati nel localStorage
        function saveScheduleToStorage() {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(currentSchedule));
            } catch (e) {
                console.error('Errore salvataggio storage orario:', e);
            }
        }
        
        // Gestisci visibilità campi pausa pranzo
        function toggleLunchBreakFields() {
            const isEnabled = document.getElementById('lunch-break-enabled').checked;
            const lunchRows = document.querySelectorAll('.lunch-time-row');
            
            lunchRows.forEach(row => {
                if (isEnabled) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        }
        
        // Carica i dati dai campi del form
        function loadFormData() {
            // Gestisci ora inizio
            const [startHour, startMinute] = (currentSchedule.startTime || '09:00').split(':');
            document.getElementById('schedule-start-hour').value = parseInt(startHour);
            document.getElementById('schedule-start-minute').value = startMinute;
            
            // Gestisci ora fine
            const [endHour, endMinute] = (currentSchedule.endTime || '18:00').split(':');
            document.getElementById('schedule-end-hour').value = parseInt(endHour);
            document.getElementById('schedule-end-minute').value = endMinute;
            
            // Gestisci pausa pranzo
            document.getElementById('lunch-break-enabled').checked = currentSchedule.lunchBreakEnabled || false;
            
            const [lunchStartHour, lunchStartMinute] = (currentSchedule.lunchStartTime || '12:00').split(':');
            document.getElementById('lunch-start-hour').value = parseInt(lunchStartHour);
            document.getElementById('lunch-start-minute').value = lunchStartMinute;
            
            const [lunchEndHour, lunchEndMinute] = (currentSchedule.lunchEndTime || '13:00').split(':');
            document.getElementById('lunch-end-hour').value = parseInt(lunchEndHour);
            document.getElementById('lunch-end-minute').value = lunchEndMinute;
            
            // Giorni di chiusura
            const closureSelect = document.getElementById('schedule-closure-days');
            Array.from(closureSelect.options).forEach(option => {
                option.selected = currentSchedule.closureDays && currentSchedule.closureDays.includes(option.value);
            });
            
            // Aggiorna visibilità campi pausa pranzo
            toggleLunchBreakFields();
        }
        
        // Salva i dati dai campi del form
        function saveFormData() {
            // Costruisci orari
            const startHour = document.getElementById('schedule-start-hour').value.padStart(2, '0');
            const startMinute = document.getElementById('schedule-start-minute').value;
            currentSchedule.startTime = `${startHour}:${startMinute}`;
            
            const endHour = document.getElementById('schedule-end-hour').value.padStart(2, '0');
            const endMinute = document.getElementById('schedule-end-minute').value;
            currentSchedule.endTime = `${endHour}:${endMinute}`;
            
            // Salva pausa pranzo
            currentSchedule.lunchBreakEnabled = document.getElementById('lunch-break-enabled').checked;
            
            const lunchStartHour = document.getElementById('lunch-start-hour').value.padStart(2, '0');
            const lunchStartMinute = document.getElementById('lunch-start-minute').value;
            currentSchedule.lunchStartTime = `${lunchStartHour}:${lunchStartMinute}`;
            
            const lunchEndHour = document.getElementById('lunch-end-hour').value.padStart(2, '0');
            const lunchEndMinute = document.getElementById('lunch-end-minute').value;
            currentSchedule.lunchEndTime = `${lunchEndHour}:${lunchEndMinute}`;
            
            // Giorni di chiusura
            const closureSelect = document.getElementById('schedule-closure-days');
            currentSchedule.closureDays = Array.from(closureSelect.selectedOptions).map(opt => opt.value);
            
            saveScheduleToStorage();
        }
        
        // Validazione base
        function validateForm() {
            // Valida orario di lavoro
            const startTime = `${document.getElementById('schedule-start-hour').value.padStart(2, '0')}:${document.getElementById('schedule-start-minute').value}`;
            const endTime = `${document.getElementById('schedule-end-hour').value.padStart(2, '0')}:${document.getElementById('schedule-end-minute').value}`;
            
            if (startTime >= endTime) {
                alert('L\'ora di inizio deve essere precedente all\'ora di fine');
                document.getElementById('schedule-start-hour').focus();
                return false;
            }
            
            // Valida pausa pranzo se abilitata
            const lunchEnabled = document.getElementById('lunch-break-enabled').checked;
            if (lunchEnabled) {
                const lunchStartTime = `${document.getElementById('lunch-start-hour').value.padStart(2, '0')}:${document.getElementById('lunch-start-minute').value}`;
                const lunchEndTime = `${document.getElementById('lunch-end-hour').value.padStart(2, '0')}:${document.getElementById('lunch-end-minute').value}`;
                
                if (lunchStartTime >= lunchEndTime) {
                    alert('L\'inizio pausa deve essere precedente alla fine pausa');
                    document.getElementById('lunch-start-hour').focus();
                    return false;
                }
                
                if (lunchStartTime <= startTime || lunchEndTime >= endTime) {
                    alert('La pausa pranzo deve essere compresa nell\'orario di lavoro');
                    document.getElementById('lunch-start-hour').focus();
                    return false;
                }
            }
            
            return true;
        }
        
        // Event listeners
        document.addEventListener('DOMContentLoaded', () => {
            loadScheduleFromStorage();
            loadFormData();
            
            // Auto-save su modifica campi
            document.querySelectorAll('#schedule-form input, #schedule-form select').forEach(field => {
                field.addEventListener('input', saveFormData);
                field.addEventListener('change', saveFormData);
            });
            
            // Gestione visibilità campi pausa pranzo
            document.getElementById('lunch-break-enabled').addEventListener('change', function() {
                toggleLunchBreakFields();
                saveFormData();
            });
            
            // Gestione salvataggio
            document.getElementById('save-schedule-btn').addEventListener('click', function(e) {
                e.preventDefault();
                
                if (!validateForm()) return;
                
                saveFormData();
                alert('✅ Orario salvato correttamente!');
                
                // Chiudi la finestra popup dopo il salvataggio
                setTimeout(() => {
                    window.close();
                }, 500);
            });
        });
    </script>

</body>
</html>
