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
        * { font-family: 'Courier New', monospace; box-sizing: border-box; }
        
        body {
            margin: 0;
            padding: 0;
            background: #f5f5f5 url('../../assets/images/background.png') center center no-repeat fixed;
            background-size: auto;
            color: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        
        .popup-window-container {
            max-width: 1000px;
            width: min(95vw, 100%);
            background: #fff;
            border: 2px solid #000;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }
        
        .window-header {
            background: linear-gradient(135deg, #000 0%, #333 100%);
            color: #fff;
            padding: 6px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        
        .header-title {
            flex: 1;
            text-align: center;
            margin: 0;
        }
        
        button {
            background: #fff;
            color: #000;
            border: 1px solid #000;
            border-radius: 3px;
            cursor: pointer;
            font: inherit;
            font-weight: 500;
        }
        
        button:hover {
            background: #000;
            color: #fff;
        }
        
        .close-btn { 
            padding: 4px 8px; 
            font-size: 10px; 
            border: 1px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.1);
            color: #fff;
            border-radius: 4px;
            transition: all 0.2s ease;
            font-weight: bold;
        }
        
        .close-btn:hover {
            background: rgba(255,255,255,0.2);
            border-color: rgba(255,255,255,0.5);
            color: #fff;
            transform: scale(1.05);
        }
        
        .toolbar-btn { 
            padding: 10px 16px; 
            font-size: 10px; 
            margin: 0 3px;
            font-weight: 600;
            border-radius: 4px;
            text-transform: uppercase;
        }
        
        .action-btn { 
            padding: 6px 8px; 
            font-size: 8px; 
            width: 100%;
            border-color: #333;
        }
        
        .save-btn { 
            padding: 12px 24px; 
            font-size: 12px;
            font-weight: 700;
            border-radius: 6px;
            text-transform: uppercase;
        }
        
        .schedules-toolbar {
            display: flex;
            gap: 12px;
            padding: 8px 16px;
            background: #f8f8f8;
            justify-content: center;
            border-bottom: 1px solid #ddd;
        }
        
        .schedules-table-container {
            padding: 8px;
            overflow-x: auto;
            background: #fff;
            border-radius: 0 0 6px 6px;
        }
        
        .excel-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 280px;
            border: 2px solid #000;
            border-radius: 4px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .excel-table th {
            background: #000;
            color: #fff;
            padding: 8px 6px;
            font-size: 9px;
            text-align: center;
            border-right: 1px solid #fff;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .excel-table th:last-child {
            border-right: none;
        }
        
        .excel-table td {
            padding: 4px 3px;
            font-size: 9px;
            border: 1px solid rgba(0,0,0,0.15);
            text-align: center;
            vertical-align: middle;
            height: 28px;
            background: #fff;
            transition: all 0.15s ease;
        }
        
        .excel-table td:last-child {
            border-right: none;
        }
        
        .excel-table tr:nth-child(even) td {
            background: rgba(0,0,0,0.03);
        }
        
        .excel-table tr:hover td {
            background: rgba(0,0,0,0.06) !important;
        }
        
        .cell-input, .closure-days-select {
            width: 100%;
            padding: 3px 5px;
            border: 1px solid rgba(0,0,0,0.2);
            border-radius: 3px;
            text-align: center;
            font-size: 9px;
            background: #fff;
            font-family: inherit;
            transition: all 0.2s ease;
        }
        
        .cell-input:focus, .closure-days-select:focus {
            border-color: black;
            border-width: 2px;
            outline: none;
            background: rgba(0,0,0,0.03);
            box-shadow: 0 0 0 2px rgba(0,0,0,0.1);
        }
        
        .cell-input:hover, .closure-days-select:hover {
            border-color: #333;
            background: rgba(0,0,0,0.01);
        }
        
        input[type="text"].cell-input { font-size: 9px; font-weight: 500; }
        
        .name-col input[type="text"] { 
            font-size: 11px; 
            font-weight: 700; 
            text-align: left;
            padding-left: 6px;
            height: 24px;
        }
        
        input[type="checkbox"] { 
            width: 16px; 
            height: 16px; 
            margin: 2px;
            border-radius: 2px;
            border: 1px solid rgba(0,0,0,0.3);
            transition: all 0.2s ease;
        }
        
        input[type="checkbox"]:hover {
            border-color: black;
            transform: scale(1.1);
        }
        
        input[type="checkbox"]:checked {
            background: black;
            border-color: black;
        }
        
        /* Column widths using CSS Grid approach */
        .select-col { width: 30px; }
        .name-col { width: 180px; }
        .start-date-col, .end-date-col { width: 85px; }
        .start-time-col, .end-time-col { width: 65px; }
        .closure-days-col { width: 100px; }
        .actions-col { width: 45px; }
        
        .schedules-stats {
            padding: 12px 20px;
            font-size: 11px;
            display: flex;
            gap: 20px;
            background: #f5f5f5;
            border-top: 1px solid #000;
            color: #333;
            font-weight: 600;
            justify-content: center;
            text-transform: uppercase;
        }
        
        .schedules-stats strong {
            color: #333;
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
            <span class="header-title">Gestione Orario</span>
            <button class="close-btn" onclick="window.close()" title="Chiudi finestra">✖</button>
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
