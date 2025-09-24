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
    <style>
        :root {
            --primary-color: #000;
            --bg-color: #fff;
            --gray-medium: #ccc;
            --gray-dark: #666;
            --gray-darker: #333;
            --border-radius: 6px;
            --border-radius-small: 3px;
        }
        
        * { font-family: 'Courier New', monospace; box-sizing: border-box; }
        
        body {
            margin: 0;
            padding: 0;
            background: #f5f5f5 url('../../assets/images/background.png') center center no-repeat fixed;
            background-size: auto;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        
        .popup-window-container {
            max-width: 820px;
            width: min(88vw, 100%);
            background: var(--bg-color);
            border: 1px solid black;
            border-radius: var(--border-radius);
            overflow: hidden;
        }
        
        .window-header {
            background: var(--primary-color);
            color: var(--bg-color);
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid black;
        }
        
        .window-title {
            flex: 1;
            text-align: center;
            margin: 0;
            font: normal 15px/1.2 inherit;
            letter-spacing: 0.5px;
            font-weight: 500;
        }
        
        button {
            background: var(--bg-color);
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
            border-radius: var(--border-radius-small);
            cursor: pointer;
            transition: var(--transition);
            font: inherit;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }
        
        button:hover {
            background: var(--primary-color);
            color: var(--bg-color);
        }
        
        .close-btn { 
            padding: 6px 10px; 
            font-size: 9px; 
            border-color: var(--gray-dark);
            color: var(--gray-dark);
        }
        
        .close-btn:hover {
            background: var(--gray-dark);
            color: var(--bg-color);
        }
        
        .toolbar-btn { 
            padding: 8px 12px; 
            font-size: 9px; 
            margin: 0 2px;
            box-shadow: var(--shadow);
        }
        
        .action-btn { 
            padding: 6px 8px; 
            font-size: 8px; 
            width: 100%;
            border-color: var(--gray-darker);
        }
        
        .save-btn { 
            padding: 10px 16px; 
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.3px;
            box-shadow: var(--shadow);
        }
        
        .schedules-toolbar {
            display: flex;
            gap: 10px;
            padding: 14px 16px;
            background: var(--gray-light);
            border-bottom: 1px solid var(--primary-color);
            justify-content: center;
        }
        
        .schedules-table-container {
            padding: 12px;
            overflow-x: auto;
            background: var(--bg-color);
            border-radius: 0 0 var(--border-radius) var(--border-radius);
        }
        
        .excel-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 340px;
            border: 1px solid black;
            border-radius: var(--border-radius-small);
            overflow: hidden;
            background: var(--bg-color);
        }
        
        .excel-table th {
            background: var(--primary-color);
            color: var(--bg-color);
            padding: 10px 6px;
            font-size: 10px;
            text-align: center;
            border-right: 1px solid white;
            font-weight: 600;
        }
        
        .excel-table th:last-child {
            border-right: none;
        }
        
        .excel-table td {
            padding: 8px 6px;
            font-size: 9px;
            border-bottom: 1px solid var(--primary-color);
            border-right: 1px solid var(--primary-color);
            text-align: center;
            vertical-align: middle;
            height: 32px;
            background: var(--bg-color);
            position: relative;
        }
        
        .excel-table td:last-child {
            border-right: none;
        }
        
        .excel-table tr:nth-child(even) td {
            background: rgba(0,0,0,0.02);
        }
        
        .excel-table tr:hover td {
            background: rgba(0,0,0,0.04) !important;
        }
        
        .cell-input, .closure-days-select {
            width: 100%;
            padding: 6px 8px;
            border: 1px solid black;
            border-radius: var(--border-radius-small);
            text-align: center;
            font-size: 9px;
            background: var(--bg-color);
            font-family: inherit;
        }
        
        .cell-input:focus, .closure-days-select:focus {
            border-color: var(--primary-color);
            border-width: 3px;
            outline: none;
            background: rgba(0,0,0,0.02);
            box-shadow: var(--shadow-focus), inset 0 1px 2px rgba(0,0,0,0.05), 0 0 0 1px var(--bg-color);
            transform: scale(1.02);
        }
        
        .cell-input:hover, .closure-days-select:hover {
            border-color: var(--gray-darker);
            background: rgba(0,0,0,0.01);
        }
        
        input[type="text"].cell-input { font-size: 10px; font-weight: 500; }
        input[type="checkbox"] { 
            width: 18px; 
            height: 18px; 
            margin: 2px;
            border-radius: var(--border-radius-small);
            border: 1px solid black;
        }
        
        /* Column widths using CSS Grid approach */
        .select-col { width: 40px; }
        .name-col { width: 120px; }
        .start-date-col, .end-date-col { width: 100px; }
        .start-time-col, .end-time-col { width: 80px; }
        .closure-days-col { width: 120px; }
        .actions-col { width: 60px; }
        

        
        .schedules-stats {
            padding: 12px 16px;
            font-size: 11px;
            display: flex;
            gap: 16px;
            background: var(--bg-color);
            border-top: 1px solid black;
            color: var(--primary-color);
            font-weight: 500;
        }
        
        .schedules-stats strong {
            color: var(--gray-darker);
            font-weight: 600;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .popup-window-container { width: 95vw; }
            .schedules-toolbar { flex-wrap: wrap; gap: 6px; }
        }
        
        @media (max-width: 480px) {
            .popup-window-container { 
                width: 100vw; 
                border-radius: 0; 
                margin: 0;
            }
            .excel-table { min-width: 300px; }
            .schedules-toolbar { padding: 8px; }
        }
    </style>
</head>
<body>

    <div class="popup-window-container">
        <div class="window-header">
            <h1 class="window-title">Gestione Orario</h1>
            <button class="close-btn" onclick="window.close()" title="Chiudi finestra">✖</button>
        </div>

        <div class="calendar-body" style="padding:16px;">
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
                                    <td><input type="time" value="<?= $schedule['startTime'] ?>" class="cell-input"></td>
                                    <td><input type="time" value="<?= $schedule['endTime'] ?>" class="cell-input"></td>
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

                <div style="margin-top:18px; text-align:center;">
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

        function generateScheduleRow(schedule){ const closureOptions = ['lunedi','martedi','mercoledi','giovedi','venerdi','sabato','domenica'].map(d=>`<option value="${d}" ${Array.isArray(schedule.closureDays)&&schedule.closureDays.includes(d)?'selected':''}>${d.charAt(0).toUpperCase()+d.slice(1)}</option>`).join(''); return `
            <tr data-schedule-id="${schedule.id}">
                <td><input type="checkbox" class="row-select"></td>
                <td><input type="text" value="${escapeHtml(schedule.name)}" class="cell-input"></td>
                <td><input type="date" value="${escapeHtml(schedule.startDate)}" class="cell-input"></td>
                <td><input type="date" value="${escapeHtml(schedule.endDate)}" class="cell-input"></td>
                <td><input type="time" value="${escapeHtml(schedule.startTime)}" class="cell-input"></td>
                <td><input type="time" value="${escapeHtml(schedule.endTime)}" class="cell-input"></td>
                <td><select multiple class="cell-select closure-days-select">${closureOptions}</select></td>
                <td class="actions-cell"><button class="action-btn btn-delete-single" data-schedule-id="${schedule.id}">🗑️</button></td>
            </tr>`; }

        function renderSchedulesTable(){ const tbody = document.getElementById('schedules-list'); if(!tbody) return; if(!schedulesList.length){ tbody.innerHTML='<tr><td colspan="8">Nessun orario configurato</td></tr>'; updateSelectionStats(); return; } tbody.innerHTML = schedulesList.map(generateScheduleRow).join(''); updateSelectionStats(); }

        function updateSelectionStats(){ document.getElementById('selected-schedules').textContent = document.querySelectorAll('.row-select:checked').length; document.getElementById('total-schedules').textContent = schedulesList.length; }

        function syncRowToModel(row,id){ const schedule = schedulesList.find(s=>s.id===id); if(!schedule) return; const inputs = row.querySelectorAll('.cell-input'); schedule.name = inputs[0]?.value||''; if(inputs[1]?.type==='date') schedule.startDate = inputs[1].value||''; if(inputs[2]?.type==='date') schedule.endDate = inputs[2].value||''; schedule.startTime = inputs[3]?.value||''; schedule.endTime = inputs[4]?.value||''; saveSchedulesToStorage(); }

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
                <td><input type="time" value="${escapeHtml(schedule.startTime)}" class="cell-input"></td>
                <td><input type="time" value="${escapeHtml(schedule.endTime)}" class="cell-input"></td>
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
        document.addEventListener('input', e=>{ if(e.target.classList.contains('cell-input')){ const row = e.target.closest('tr'); if(!row) return; syncRowToModel(row, parseInt(row.dataset.scheduleId)); } });
        document.addEventListener('change', e=>{ if(e.target.classList.contains('closure-days-select')){ const row = e.target.closest('tr'); const id = parseInt(row.dataset.scheduleId); const schedule = schedulesList.find(s=>s.id===id); if(schedule) schedule.closureDays = Array.from(e.target.selectedOptions).map(o=>o.value); saveSchedulesToStorage(); } if(e.target.classList.contains('row-select')||e.target.id==='select-all-schedules') updateSelectionStats(); });

        document.addEventListener('DOMContentLoaded', ()=>{ loadSchedulesFromStorage(); renderSchedulesTable(); document.getElementById('add-schedule-btn')?.addEventListener('click', onAddSchedule); document.getElementById('delete-selected-btn')?.addEventListener('click', onDeleteSelected); document.getElementById('save-all-btn')?.addEventListener('click', onSaveAll); document.getElementById('select-all-schedules')?.addEventListener('change', onToggleSelectAll); });
    </script>

</body>
</html>
