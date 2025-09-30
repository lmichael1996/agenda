<?php
/**
 * Popup per la gestione dell'orario - Finestra separata
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
    <title>Gestione Orario - Agenda</title>
    <link rel="stylesheet" href="../../assets/css/scrollbar.css">
    <link rel="stylesheet" href="../../assets/css/scheme-popup.css">
</head>
<body>
    <div class="popup-window-container">
        <div class="window-header">
            <span class="header-title">Gestione Orario</span>
        </div>
        <div class="calendar-body schedule-calendar-body">
            <form id="schedule-form" class="schedule-form">
                <table class="excel-table schedule-table">
                    <tbody>
                        <tr>
                            <th class="schedule-table-th">Ora Inizio</th>
                            <td>
                                <div class="date-time-container">
                                    <input type="number" id="schedule-start-hour" class="cell-input hour-input" min="0" max="23" value="9">
                                    <select id="schedule-start-minute" class="cell-input minute-select">
                                        <?php foreach (["00","15","30","45"] as $m): ?>
                                            <option value="<?= $m ?>"><?= $m ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th class="schedule-table-th">Ora Fine</th>
                            <td>
                                <div class="date-time-container">
                                    <input type="number" id="schedule-end-hour" class="cell-input hour-input" min="0" max="23" value="18">
                                    <select id="schedule-end-minute" class="cell-input minute-select">
                                        <?php foreach (["00","15","30","45"] as $m): ?>
                                            <option value="<?= $m ?>"><?= $m ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th class="schedule-table-th">Pausa Pranzo</th>
                            <td><input type="checkbox" id="lunch-break-enabled" class="cell-input schedule-checkbox"></td>
                        </tr>
                        <tr class="lunch-time-row" id="lunch-start-row">
                            <th class="schedule-table-th">Inizio Pausa</th>
                            <td>
                                <div class="date-time-container">
                                    <input type="number" id="lunch-start-hour" class="cell-input hour-input" min="0" max="23" value="12">
                                    <select id="lunch-start-minute" class="cell-input minute-select">
                                        <?php foreach (["00","15","30","45"] as $m): ?>
                                            <option value="<?= $m ?>"><?= $m ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </td>
                        </tr>
                        <tr class="lunch-time-row" id="lunch-end-row">
                            <th class="schedule-table-th">Fine Pausa</th>
                            <td>
                                <div class="date-time-container">
                                    <input type="number" id="lunch-end-hour" class="cell-input hour-input" min="0" max="23" value="13">
                                    <select id="lunch-end-minute" class="cell-input minute-select">
                                        <?php foreach (["00","15","30","45"] as $m): ?>
                                            <option value="<?= $m ?>"><?= $m ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th class="schedule-table-th">Fuso Orario</th>
                            <td>
                                <select id="schedule-timezone" class="cell-input">
                                    <option value="UTC">UTC (GMT+0)</option>
                                    <optgroup label="Europa">
                                        <option value="Europe/London">Londra (GMT+0/+1)</option>
                                        <option value="Europe/Paris">Parigi (GMT+1/+2)</option>
                                        <option value="Europe/Berlin">Berlino (GMT+1/+2)</option>
                                        <option value="Europe/Rome" selected>Roma (GMT+1/+2)</option>
                                        <option value="Europe/Madrid">Madrid (GMT+1/+2)</option>
                                        <option value="Europe/Amsterdam">Amsterdam (GMT+1/+2)</option>
                                        <option value="Europe/Vienna">Vienna (GMT+1/+2)</option>
                                        <option value="Europe/Zurich">Zurigo (GMT+1/+2)</option>
                                        <option value="Europe/Athens">Atene (GMT+2/+3)</option>
                                        <option value="Europe/Helsinki">Helsinki (GMT+2/+3)</option>
                                        <option value="Europe/Moscow">Mosca (GMT+3)</option>
                                    </optgroup>
                                    <optgroup label="Nord America">
                                        <option value="America/Los_Angeles">Los Angeles (GMT-8/-7)</option>
                                        <option value="America/Denver">Denver (GMT-7/-6)</option>
                                        <option value="America/Chicago">Chicago (GMT-6/-5)</option>
                                        <option value="America/New_York">New York (GMT-5/-4)</option>
                                        <option value="America/Toronto">Toronto (GMT-5/-4)</option>
                                        <option value="America/Vancouver">Vancouver (GMT-8/-7)</option>
                                    </optgroup>
                                    <optgroup label="Sud America">
                                        <option value="America/Sao_Paulo">San Paolo (GMT-3)</option>
                                        <option value="America/Buenos_Aires">Buenos Aires (GMT-3)</option>
                                        <option value="America/Lima">Lima (GMT-5)</option>
                                        <option value="America/Mexico_City">Città del Messico (GMT-6/-5)</option>
                                    </optgroup>
                                    <optgroup label="Asia">
                                        <option value="Asia/Tokyo">Tokyo (GMT+9)</option>
                                        <option value="Asia/Seoul">Seoul (GMT+9)</option>
                                        <option value="Asia/Shanghai">Shanghai (GMT+8)</option>
                                        <option value="Asia/Hong_Kong">Hong Kong (GMT+8)</option>
                                        <option value="Asia/Singapore">Singapore (GMT+8)</option>
                                        <option value="Asia/Bangkok">Bangkok (GMT+7)</option>
                                        <option value="Asia/Mumbai">Mumbai (GMT+5:30)</option>
                                        <option value="Asia/Dubai">Dubai (GMT+4)</option>
                                        <option value="Asia/Tehran">Tehran (GMT+3:30/+4:30)</option>
                                    </optgroup>
                                    <optgroup label="Africa">
                                        <option value="Africa/Cairo">Il Cairo (GMT+2)</option>
                                        <option value="Africa/Johannesburg">Johannesburg (GMT+2)</option>
                                        <option value="Africa/Lagos">Lagos (GMT+1)</option>
                                        <option value="Africa/Casablanca">Casablanca (GMT+0/+1)</option>
                                    </optgroup>
                                    <optgroup label="Oceania">
                                        <option value="Australia/Sydney">Sydney (GMT+10/+11)</option>
                                        <option value="Australia/Melbourne">Melbourne (GMT+10/+11)</option>
                                        <option value="Australia/Perth">Perth (GMT+8)</option>
                                        <option value="Pacific/Auckland">Auckland (GMT+12/+13)</option>
                                        <option value="Pacific/Honolulu">Honolulu (GMT-10)</option>
                                    </optgroup>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th class="schedule-table-th">Giorni Chiusura</th>
                            <td>
                                <select multiple id="schedule-closure-days" class="cell-input schedule-closure-select">
                                    <option value="lunedi">Lunedì</option>
                                    <option value="martedi">Martedì</option>
                                    <option value="mercoledi">Mercoledì</option>
                                    <option value="giovedi">Giovedì</option>
                                    <option value="venerdi">Venerdì</option>
                                    <option value="sabato">Sabato</option>
                                    <option value="domenica">Domenica</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="schedule-save-container">
                    <button class="save-btn schedule-save-btn" id="save-schedule-btn">Salva Orario</button>
                </div>
            </form>
        </div>
    </div>

    <script type="module">
        import { fetchSchedule, saveSchedule, convertFromApiFormat } from '../../api/frontend/schedule-api.js';
        
        let schedule = {};
        const closePopup = (msg) => { 
            if(msg) alert(msg); 
            setTimeout(() => window.close(), 200); 
        };

        // API functions
        const loadData = async () => {
            try {
                const res = await fetchSchedule();
                if (res.success && res.data) {
                    schedule = convertFromApiFormat(res.data);
                } else {
                    // Se non ci sono dati nel database, chiudi finestra
                    closePopup('Nessun orario configurato');
                    return;
                }
            } catch (e) { 
                closePopup('Errore caricamento: ' + e.message); 
            }
        };
        
        const saveData = async () => {
            try {
                return (await saveSchedule(schedule)).success;
            } catch (e) {
                const msg = e.message.includes('401') ? 'Sessione scaduta' : e.message.includes('400') ? 'Dati non validi' : 'Errore connessione';
                closePopup(msg);
                return false;
            }
        };
        
        // UI functions
        const toggleLunch = () => document.querySelectorAll('.lunch-time-row').forEach(
            row => row.classList.toggle('hidden', !document.getElementById('lunch-break-enabled').checked)
        );
        
        const setTime = (id, time) => {
            const [h, m] = time.split(':');
            document.getElementById(id + '-hour').value = parseInt(h);
            document.getElementById(id + '-minute').value = m;
        };
        
        const getTime = (id) => `${document.getElementById(id + '-hour').value.padStart(2, '0')}:${document.getElementById(id + '-minute').value}`;
        
        const loadForm = () => {
            setTime('schedule-start', schedule.startTime || '08:00');
            setTime('schedule-end', schedule.endTime || '18:00');
            setTime('lunch-start', schedule.lunchStartTime || '12:30');
            setTime('lunch-end', schedule.lunchEndTime || '13:30');
            document.getElementById('lunch-break-enabled').checked = schedule.lunchBreakEnabled || false;
            document.getElementById('schedule-timezone').value = schedule.timezone || 'Europe/Rome';
            Array.from(document.getElementById('schedule-closure-days').options).forEach(opt => opt.selected = schedule.closureDays?.includes(opt.value));
            toggleLunch();
        };
        
        const saveForm = () => {
            schedule = {
                startTime: getTime('schedule-start'),
                endTime: getTime('schedule-end'),
                lunchBreakEnabled: document.getElementById('lunch-break-enabled').checked,
                lunchStartTime: getTime('lunch-start'),
                lunchEndTime: getTime('lunch-end'),
                timezone: document.getElementById('schedule-timezone').value,
                closureDays: Array.from(document.getElementById('schedule-closure-days').selectedOptions).map(opt => opt.value)
            };
        };
        
        const validate = () => {
            const start = getTime('schedule-start'), end = getTime('schedule-end');
            if (start >= end) return closePopup('Ora inizio deve essere precedente a fine'), false;
            
            if (document.getElementById('lunch-break-enabled').checked) {
                const lStart = getTime('lunch-start'), lEnd = getTime('lunch-end');
                if (lStart >= lEnd) return closePopup('Inizio pausa deve essere precedente a fine pausa'), false;
                if (lStart <= start || lEnd >= end) return closePopup('Pausa pranzo deve essere compresa nell\'orario'), false;
            }
            return true;
        };
        
        // Init
        document.addEventListener('DOMContentLoaded', async () => {
            const btn = document.getElementById('save-schedule-btn');
            btn.disabled = true;
            btn.textContent = 'Caricamento...';
            
            await loadData();
            loadForm();
            
            btn.disabled = false;
            btn.textContent = 'Salva Orario';
            
            document.getElementById('lunch-break-enabled').addEventListener('change', toggleLunch);
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                if (!validate()) return;
                
                saveForm();
                btn.disabled = true;
                btn.textContent = 'Salvando...';
                
                if (await saveData()) closePopup('Orario aggiornato con successo!');
                else { 
                    btn.disabled = false; 
                    btn.textContent = 'Salva Orario'; 
                }
            });
        });
    </script>

</body>
</html>
