<?php
/**
 * Popup per la gestione degli orari - Finestra separata
 * Versione pulita e corretta
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

// Dati di esempio per gli orari
$sampleSchedules = [
    [
        'id' => 2,
        'name' => 'Orario Estivo',
        'startDate' => '2025-06-01',
        'endDate' => '2025-08-31',
        'startTime' => '08:00',
        'endTime' => '17:00',
        'closureDays' => ['sabato', 'domenica']
    ]
];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Orari - Agenda</title>
    <link rel="stylesheet" href="../../assets/css/popup.css">
    <link rel="stylesheet" href="../../assets/css/scrollbar.css">
</head>
<body>

    <div class="popup-window-container">
        <div class="window-header">
            <span class="header-title">Gestione Orario</span>
        </div>

        <div class="calendar-body" style="padding:4px;">
            <div class="schedules-section">

                <div class="schedules-toolbar">
                    <button id="add-schedule-btn" class="toolbar-btn">➕ Nuovo Orario</button>
                    <button id="delete-selected-btn" class="toolbar-btn">🗑️ Elimina Selezionati</button>
                </div>

                <div class="schedules-table-container">
                    <table class="excel-table" id="schedules-table">
                        <thead>
                            <tr>
                                <th class="select-col"><input type="checkbox" id="select-all-schedules"></th>
                                <th class="name-col">Nome Orario</th>
                                <th class="start-date-col">Data Inizio</th>
                                <th class="end-date-col">Data Fine</th>
                                <th class="start-time-col">Ora Inizio</th>
                                <th class="end-time-col">Ora Fine</th>
                                <th class="closure-days-col">Giorni Chiusura</th>
                                <th class="actions-col">Azioni</th>
                            </tr>
                        </thead>
                        <tbody id="schedules-list">
                            <?php foreach ($sampleSchedules as $schedule): ?>
                                <tr data-schedule-id="<?= $schedule['id'] ?>">
                                    <td><input type="checkbox" class="row-select"></td>
                                    <td><input type="text" value="<?= htmlspecialchars($schedule['name']) ?>" placeholder="Nome orario..." class="cell-input"></td>
                                    <td><input type="date" value="<?= $schedule['startDate'] ?>" class="cell-input"></td>
                                    <td><input type="date" value="<?= $schedule['endDate'] ?>" class="cell-input"></td>
                                    <td style="white-space:nowrap;">
                                        <input type="number" min="0" max="23" value="<?= explode(':', $schedule['startTime'])[0] ?>" class="hour-input" style="width:44px; display:inline-block;">:<select class="minute-select" style="width:44px; display:inline-block; margin-left:2px; margin-right:0;">
                                            <?php foreach (["00","15","30","45"] as $m): ?>
                                                <option value="<?= $m ?>" <?= (explode(':', $schedule['startTime'])[1] == $m ? 'selected' : '') ?>><?= $m ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td style="white-space:nowrap;">
                                        <input type="number" min="0" max="23" value="<?= explode(':', $schedule['endTime'])[0] ?>" class="hour-input" style="width:44px; display:inline-block;">:<select class="minute-select" style="width:44px; display:inline-block; margin-left:2px; margin-right:0;">
                                            <?php foreach (["00","15","30","45"] as $m): ?>
                                                <option value="<?= $m ?>" <?= (explode(':', $schedule['endTime'])[1] == $m ? 'selected' : '') ?>><?= $m ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select multiple class="cell-select closure-days-select">
                                            <option value="lunedi" <?= in_array('lunedi', $schedule['closureDays']) ? 'selected' : '' ?>>Lunedì</option>
                                            <option value="martedi" <?= in_array('martedi', $schedule['closureDays']) ? 'selected' : '' ?>>Martedì</option>
                                            <option value="mercoledi" <?= in_array('mercoledi', $schedule['closureDays']) ? 'selected' : '' ?>>Mercoledì</option>
                                            <option value="giovedi" <?= in_array('giovedi', $schedule['closureDays']) ? 'selected' : '' ?>>Giovedì</option>
                                            <option value="venerdi" <?= in_array('venerdi', $schedule['closureDays']) ? 'selected' : '' ?>>Venerdì</option>
                                            <option value="sabato" <?= in_array('sabato', $schedule['closureDays']) ? 'selected' : '' ?>>Sabato</option>
                                            <option value="domenica" <?= in_array('domenica', $schedule['closureDays']) ? 'selected' : '' ?>>Domenica</option>
                                        </select>
                                    </td>
                                    <td class="actions-cell"><button class="action-btn btn-delete-single" data-schedule-id="<?= $schedule['id'] ?>" title="Elimina">🗑️</button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="schedules-stats">
                    <span>Totale orari: <strong id="total-schedules"><?= count($sampleSchedules) ?></strong></span>
                    <span>Selezionati: <strong id="selected-schedules">0</strong></span>
                </div>

                <div style="margin-top:20px; margin-bottom:20px; text-align:center;">
                    <button id="save-all-btn" class="save-btn">💾 Salva Tutti gli Orari</button>
                </div>

            </div>
        </div>
    </div>

    <script>
        // inizializzazione dati
        let schedulesList = <?= json_encode($sampleSchedules) ?>;
        const STORAGE_KEY = 'calendar_schedules';
        let scheduleIdCounter = Math.max(...schedulesList.map(s => s.id)) + 1;

        function loadSchedulesFromStorage(){ try{ const raw = localStorage.getItem(STORAGE_KEY); if(!raw) return; const parsed = JSON.parse(raw); if(Array.isArray(parsed.schedules)){ schedulesList = parsed.schedules; scheduleIdCounter = parsed.counter || (Math.max(...schedulesList.map(s=>s.id))+1); } }catch(e){console.warn('Impossibile leggere storage',e);} }
        function saveSchedulesToStorage(){ try{ localStorage.setItem(STORAGE_KEY, JSON.stringify({schedules: schedulesList, counter: scheduleIdCounter})); }catch(e){console.error('Errore salvataggio storage',e);} }

        function escapeHtml(str){ if(str===null||str===undefined) return ''; return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

        function generateScheduleRow(schedule){
            const closureOptions = ['lunedi','martedi','mercoledi','giovedi','venerdi','sabato','domenica'].map(d=>`<option value="${d}" ${Array.isArray(schedule.closureDays)&&schedule.closureDays.includes(d)?'selected':''}>${d.charAt(0).toUpperCase()+d.slice(1)}</option>`).join('');
            const [startHour, startMinute] = String(schedule.startTime||'09:00').split(':');
            const [endHour, endMinute] = String(schedule.endTime||'18:00').split(':');
            return `
            <tr data-schedule-id="${schedule.id}">
                <td><input type="checkbox" class="row-select"></td>
                <td><input type="text" value="${escapeHtml(schedule.name)}" class="cell-input"></td>
                <td><input type="date" value="${escapeHtml(schedule.startDate)}" class="cell-input"></td>
                <td><input type="date" value="${escapeHtml(schedule.endDate)}" class="cell-input"></td>
                <td style="white-space:nowrap;">
                    <input type="number" min="0" max="23" value="${escapeHtml(startHour)}" class="hour-input" style="width:44px; display:inline-block;">:<select class="minute-select" style="width:44px; display:inline-block; margin-left:2px; margin-right:0;">
                        <option value="00" ${startMinute==="00"?"selected":""}>00</option>
                        <option value="15" ${startMinute==="15"?"selected":""}>15</option>
                        <option value="30" ${startMinute==="30"?"selected":""}>30</option>
                        <option value="45" ${startMinute==="45"?"selected":""}>45</option>
                    </select>
                </td>
                <td style="white-space:nowrap;">
                    <input type="number" min="0" max="23" value="${escapeHtml(endHour)}" class="hour-input" style="width:44px; display:inline-block;">:<select class="minute-select" style="width:44px; display:inline-block; margin-left:2px; margin-right:0;">
                        <option value="00" ${endMinute==="00"?"selected":""}>00</option>
                        <option value="15" ${endMinute==="15"?"selected":""}>15</option>
                        <option value="30" ${endMinute==="30"?"selected":""}>30</option>
                        <option value="45" ${endMinute==="45"?"selected":""}>45</option>
                    </select>
                </td>
                <td><select multiple class="cell-select closure-days-select">${closureOptions}</select></td>
                <td class="actions-cell"><button class="action-btn btn-delete-single" data-schedule-id="${schedule.id}">🗑️</button></td>
            </tr>`;
        }

        function renderSchedulesTable(){ const tbody = document.getElementById('schedules-list'); if(!tbody) return; if(!schedulesList.length){ tbody.innerHTML='<tr><td colspan="8">Nessun orario configurato</td></tr>'; updateSelectionStats(); return; } tbody.innerHTML = schedulesList.map(generateScheduleRow).join(''); updateSelectionStats(); }

        function updateSelectionStats(){ document.getElementById('selected-schedules').textContent = document.querySelectorAll('.row-select:checked').length; document.getElementById('total-schedules').textContent = schedulesList.length; }

        function syncRowToModel(row,id){
            const schedule = schedulesList.find(s=>s.id===id); if(!schedule) return;
            const inputs = row.querySelectorAll('.cell-input');
            schedule.name = inputs[0]?.value||'';
            if(inputs[1]?.type==='date') schedule.startDate = inputs[1].value||'';
            if(inputs[2]?.type==='date') schedule.endDate = inputs[2].value||'';
            // start time custom
            const startHour = inputs[3]?.value.padStart(2,'0')||'09';
            const startMinute = row.querySelectorAll('.minute-select')[0]?.value||'00';
            schedule.startTime = `${startHour}:${startMinute}`;
            // end time custom
            const endHour = inputs[4]?.value.padStart(2,'0')||'18';
            const endMinute = row.querySelectorAll('.minute-select')[1]?.value||'00';
            schedule.endTime = `${endHour}:${endMinute}`;
            saveSchedulesToStorage();
        }

        function onAddSchedule(){ const newSchedule = { id: scheduleIdCounter++, name: 'Nuovo Orario '+scheduleIdCounter, startDate:'', endDate:'', startTime:'09:00', endTime:'18:00', closureDays:[] }; schedulesList.push(newSchedule); renderSchedulesTable(); saveSchedulesToStorage(); setTimeout(()=>document.querySelector(`tr[data-schedule-id="${newSchedule.id}"] input[type=text]`)?.focus(),0); }

        function onDeleteSingle(id){ const schedule = schedulesList.find(s=>s.id===id); if(!schedule) return; if(!confirm('Eliminare questo orario?')) return; schedulesList = schedulesList.filter(s=>s.id!==id); renderSchedulesTable(); saveSchedulesToStorage(); }

        function onDeleteSelected(){ const ids = Array.from(document.querySelectorAll('.row-select:checked')).map(cb=>parseInt(cb.closest('tr').dataset.scheduleId)); if(ids.length===0){ alert('Nessun orario selezionato'); return; } if(!confirm(`Eliminare ${ids.length} orario/i selezionato/i?`)) return; schedulesList = schedulesList.filter(s=>!ids.includes(s.id)); renderSchedulesTable(); saveSchedulesToStorage(); document.getElementById('select-all-schedules').checked=false; }

        function onSaveAll(){ document.querySelectorAll('#schedules-list tr[data-schedule-id]').forEach(r=>syncRowToModel(r,parseInt(r.dataset.scheduleId))); saveSchedulesToStorage(); alert('Orari salvati correttamente!'); }

        document.addEventListener('click', e=>{ const del = e.target.closest('.btn-delete-single, .action-btn.btn-delete-single'); if(del){ const id = parseInt(del.dataset.scheduleId); onDeleteSingle(id); } });
        document.addEventListener('input', e=>{ if(e.target.classList.contains('cell-input')){ const row = e.target.closest('tr'); if(!row) return; syncRowToModel(row, parseInt(row.dataset.scheduleId)); } });
        document.addEventListener('change', e=>{ if(e.target.classList.contains('closure-days-select')){ const row = e.target.closest('tr'); const id = parseInt(row.dataset.scheduleId); const schedule = schedulesList.find(s=>s.id===id); if(schedule) schedule.closureDays = Array.from(e.target.selectedOptions).map(o=>o.value); saveSchedulesToStorage(); } if(e.target.id==='select-all-schedules'){ const checked = e.target.checked; document.querySelectorAll('.row-select:not([disabled])').forEach(cb=>cb.checked=checked); updateSelectionStats(); } if(e.target.classList.contains('row-select')) updateSelectionStats(); });

        document.addEventListener('DOMContentLoaded', ()=>{ loadSchedulesFromStorage(); renderSchedulesTable(); document.getElementById('add-schedule-btn')?.addEventListener('click', onAddSchedule); document.getElementById('delete-selected-btn')?.addEventListener('click', onDeleteSelected); document.getElementById('save-all-btn')?.addEventListener('click', onSaveAll); document.getElementById('select-all-schedules')?.addEventListener('change', e=>{ const checked = e.target.checked; document.querySelectorAll('.row-select:not([disabled])').forEach(cb=>cb.checked=checked); updateSelectionStats(); }); });
    </script>

    <script>
        // dati iniziali dal server
        let schedulesList = <?= json_encode($sampleSchedules) ?>;
        const STORAGE_KEY = 'calendar_schedules';
        let scheduleIdCounter = Math.max(...schedulesList.map(s => s.id)) + 1;

        function loadSchedulesFromStorage() {
            try { const raw = localStorage.getItem(STORAGE_KEY); if (!raw) return; const parsed = JSON.parse(raw); if (Array.isArray(parsed.schedules)) { schedulesList = parsed.schedules; scheduleIdCounter = parsed.counter || (Math.max(...schedulesList.map(s=>s.id))+1); } } catch(e){ console.warn('Impossibile leggere storage:', e); }
        }

        function saveSchedulesToStorage(){ try{ localStorage.setItem(STORAGE_KEY, JSON.stringify({schedules: schedulesList, counter: scheduleIdCounter})); } catch(e){ console.error('Errore salvataggio storage:', e); } }

        function escapeHtml(str){ if(str===null||str===undefined) return ''; return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
        function capitalize(s){ return s.charAt(0).toUpperCase()+s.slice(1); }

        function generateScheduleRow(schedule){ const isBlocked = schedule.id===1 && (!schedule.startDate || !schedule.endDate); const disabledAttr = isBlocked? 'disabled': ''; const startDateCell = isBlocked? '<td><span class="empty-cell">-</span></td>' : `<td><input type="date" value="${escapeHtml(schedule.startDate)}" class="cell-input"></td>`; const endDateCell = isBlocked? '<td><span class="empty-cell">-</span></td>' : `<td><input type="date" value="${escapeHtml(schedule.endDate)}" class="cell-input"></td>`; const closureOptions = ['lunedi','martedi','mercoledi','giovedi','venerdi','sabato','domenica'].map(d=>`<option value="${d}" ${Array.isArray(schedule.closureDays)&&schedule.closureDays.includes(d)?'selected':''}>${capitalize(d)}</option>`).join(''); const deleteBtn = isBlocked? '' : `<button class="action-btn btn-delete-single" data-schedule-id="${schedule.id}" title="Elimina">🗑️</button>`; return `
            <tr data-schedule-id="${schedule.id}" class="${isBlocked? 'blocked-row':''}">
                <td><input type="checkbox" class="row-select" ${isBlocked? 'disabled':''}></td>
                <td><input type="text" value="${escapeHtml(schedule.name)}" class="cell-input" ${disabledAttr}></td>
                ${startDateCell}
                ${endDateCell}
                <td><input type="time" value="${escapeHtml(schedule.startTime)}" class="cell-input" step="900"></td>
                <td><input type="time" value="${escapeHtml(schedule.endTime)}" class="cell-input" step="900"></td>
                <td><select multiple class="cell-select closure-days-select">${closureOptions}</select></td>
                <td class="actions-cell">${deleteBtn}</td>
            </tr>`;
        }

        function renderSchedulesTable(){ const tbody = document.getElementById('schedules-list'); if(!tbody) return; if(!schedulesList.length){ tbody.innerHTML = '<tr><td colspan="8" class="no-data">Nessun orario configurato</td></tr>'; updateSelectionStats(); return; } tbody.innerHTML = schedulesList.map(generateScheduleRow).join(''); updateSelectionStats(); }

        function getSelectedScheduleIds(){ return Array.from(document.querySelectorAll('.row-select:checked')).map(cb=>parseInt(cb.closest('tr').dataset.scheduleId)); }
        function updateSelectionStats(){ const sel = document.querySelectorAll('.row-select:checked').length; const tot = schedulesList.length; document.getElementById('selected-schedules').textContent = sel; document.getElementById('total-schedules').textContent = tot; }

        function syncRowToModel(row,id){ const schedule = schedulesList.find(s=>s.id===id); if(!schedule) return; const inputs = row.querySelectorAll('.cell-input'); schedule.name = inputs[0]?.value||''; if(inputs[1]?.type==='date') schedule.startDate = inputs[1].value||''; if(inputs[2]?.type==='date') schedule.endDate = inputs[2].value||''; schedule.startTime = inputs[3]?.value||''; schedule.endTime = inputs[4]?.value||''; saveSchedulesToStorage(); }
        function syncSchedulesFromUI(){ document.querySelectorAll('#schedules-list tr[data-schedule-id]').forEach(row=>syncRowToModel(row,parseInt(row.dataset.scheduleId))); }

        function onAddSchedule(){ const newSchedule={ id: scheduleIdCounter++, name: 'Nuovo Orario '+scheduleIdCounter, startDate:'', endDate:'', startTime:'09:00', endTime:'18:00', closureDays:[] }; schedulesList.push(newSchedule); renderSchedulesTable(); saveSchedulesToStorage(); setTimeout(()=>document.querySelector(`tr[data-schedule-id="${newSchedule.id}"] input[type=text]`)?.focus(),0); }
        function onDeleteSingle(id){ const schedule = schedulesList.find(s=>s.id===id); if(!schedule) return; if(!confirm('Eliminare questo orario?')) return; schedulesList = schedulesList.filter(s=>s.id!==id); renderSchedulesTable(); saveSchedulesToStorage(); }
        function onDeleteSelected(){ const ids = getSelectedScheduleIds(); if(ids.length===0){ alert('Nessun orario selezionato'); return; } if(!confirm(`Eliminare ${ids.length} orario/i selezionato/i?`)) return; schedulesList = schedulesList.filter(s=>!ids.includes(s.id)); renderSchedulesTable(); saveSchedulesToStorage(); document.getElementById('select-all-schedules').checked=false; }
        function onSaveAll(){ syncSchedulesFromUI(); saveSchedulesToStorage(); alert('Orari salvati correttamente!'); }
        function onToggleSelectAll(e){ const checked = e.target.checked; document.querySelectorAll('.row-select').forEach(cb=>cb.checked=checked); updateSelectionStats(); }

        document.addEventListener('click', e=>{ const b = e.target.closest('.btn-delete-single'); if(b) onDeleteSingle(parseInt(b.dataset.scheduleId)); });
        // Funzione per validare e correggere orari (solo minuti 00, 15, 30, 45)
        function validateTimeInput(input) {
            if (input.type !== 'time') return;
            const value = input.value;
            if (!value) return;
            
            const [hours, minutes] = value.split(':');
            const allowedMinutes = ['00', '15', '30', '45'];
            
            if (!allowedMinutes.includes(minutes)) {
                // Trova il minuto più vicino consentito
                const minuteNum = parseInt(minutes);
                let closestMinute = '00';
                
                if (minuteNum <= 7) closestMinute = '00';
                else if (minuteNum <= 22) closestMinute = '15';
                else if (minuteNum <= 37) closestMinute = '30';
                else if (minuteNum <= 52) closestMinute = '45';
                else closestMinute = '00';
                
                input.value = `${hours}:${closestMinute}`;
            }
        }

        document.addEventListener('input', e=>{ 
            if(e.target.classList.contains('cell-input')){ 
                // Valida input time
                if(e.target.type === 'time') {
                    validateTimeInput(e.target);
                }
                const row = e.target.closest('tr'); 
                if(!row) return; 
                syncRowToModel(row, parseInt(row.dataset.scheduleId)); 
            } 
        });
        
        document.addEventListener('change', e=>{ 
            // Valida input time anche su change
            if(e.target.type === 'time') {
                validateTimeInput(e.target);
            }
            
            if(e.target.classList.contains('closure-days-select')){ 
                const row = e.target.closest('tr'); 
                const id = parseInt(row.dataset.scheduleId); 
                const schedule = schedulesList.find(s=>s.id===id); 
                if(schedule) schedule.closureDays = Array.from(e.target.selectedOptions).map(o=>o.value); 
                saveSchedulesToStorage(); 
            } 
            if(e.target.classList.contains('row-select')||e.target.id==='select-all-schedules') updateSelectionStats(); 
        });

        document.addEventListener('DOMContentLoaded', ()=>{ loadSchedulesFromStorage(); renderSchedulesTable(); document.getElementById('add-schedule-btn')?.addEventListener('click', onAddSchedule); document.getElementById('delete-selected-btn')?.addEventListener('click', onDeleteSelected); document.getElementById('save-all-btn')?.addEventListener('click', onSaveAll); document.getElementById('select-all-schedules')?.addEventListener('change', onToggleSelectAll); });
    </script>

</body>
</html>
