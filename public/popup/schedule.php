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
        'id' => 1,
        'name' => 'Orario Bloccato',
        'startDate' => '',
        'endDate' => '',
        'startTime' => '09:00',
        'endTime' => '18:00',
        'closureDays' => ['sabato', 'domenica']
    ],
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
        * { font-family:'Courier New'; }
        body { margin:0; padding:0; background:#f5f5f5; color:#000; display:flex; align-items:center; justify-content:center; min-height:100vh; }
        .popup-window-container { max-width:800px; width:85vw; margin:auto; background:#fff; border:1px solid #000; border-radius:4px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.15); }
        .window-header{ background:#000; color:#fff; padding:6px 10px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #000; border-radius:4px 4px 0 0; }
        .window-title{ margin:0; font-size:14px; font-weight:normal; flex:1; text-align:center; }
        .close-btn{ background:#fff; color:#000; border:1px solid #000; padding:3px 6px; font-size:8px; cursor:pointer; border-radius:2px; box-shadow:0 1px 2px rgba(0,0,0,0.1); }
        .close-btn:hover{ background:#000; color:#fff; box-shadow:0 4px 8px rgba(0,0,0,0.2); }

        .schedules-toolbar{ display:flex; gap:12px; padding:8px 15px; background:#f8f9fa; border-bottom:1px solid #000; box-shadow:inset 0 -1px 3px rgba(0,0,0,0.05); justify-content:center; }
        .toolbar-btn{ padding:3px 6px; font-size:8px; border:1px solid #000; background:#fff; color:#000; cursor:pointer; border-radius:2px; box-shadow:0 1px 2px rgba(0,0,0,0.1); margin:0 4px; }
        .toolbar-btn:hover{ background:#000; color:#fff; box-shadow:0 4px 8px rgba(0,0,0,0.2); transform:translateY(-1px); }

        .schedules-table-container{ padding:0 8px 8px 8px; overflow-x:auto; background:#fff; border-radius:0 0 4px 4px; }
        .excel-table{ width:100%; border-collapse:collapse; min-width:320px; border:1px solid #000; border-radius:3px; overflow:hidden; box-shadow:0 2px 4px rgba(0,0,0,0.15); }
        .excel-table thead th{ background:#000; color:#fff; padding:4px 2px; font-size:9px; text-align:center; position:sticky; top:0; border-bottom:1px solid #000; border-right:1px solid #333; font-weight:bold; }
        .excel-table td{ padding:2px; font-size:8px; border-bottom:1px solid #ccc; border-right:1px solid #ccc; background:#fff; text-align:center; vertical-align:middle; }
        .excel-table tr{ height:26px; }
        .excel-table tr:hover td{ background:#f8f9fa; transition:background-color 0.2s ease; }

        /* Input base styles */
        .cell-input, .closure-days-select{ border:1px solid #666; padding:2px 3px; margin:1px; background:#fff; width:calc(100% - 2px); box-sizing:border-box; text-align:center; font-size:7px; border-radius:2px; box-shadow:inset 0 1px 1px rgba(0,0,0,0.1); transition:all 0.2s ease; }
        .cell-input:focus, .closure-days-select:focus{ border:2px solid #000; outline:none; background:#f9f9f9; box-shadow:inset 0 1px 2px rgba(0,0,0,0.1), 0 0 3px rgba(0,0,0,0.2); transform:scale(1.02); }
        
        /* Input specifici per tipo */
        input[type="text"].cell-input{ text-align:center; padding:4px 6px; margin:2px; border-radius:3px; font-size:9px; }
        input[type="time"].cell-input{ text-align:center; padding:3px 4px; margin:2px; font-size:8px; border-radius:3px; }
        input[type="date"].cell-input{ text-align:center; padding:3px 4px; margin:2px; font-size:8px; border-radius:3px; }
        input[type="number"].cell-input{ text-align:center; padding:3px 4px; margin:2px; border-radius:3px; font-size:8px; }
        
        /* Select dropdown */
        select.closure-days-select{ text-align:center; padding:4px 6px; margin:2px; font-size:10px; border-radius:4px; width:calc(100% - 4px); }
        
        /* Action buttons */
        .actions-cell .action-btn{ border:1px solid #000; padding:3px 6px; cursor:pointer; background:#fff; color:#000; width:100%; box-sizing:border-box; font-size:8px; border-radius:2px; box-shadow:0 1px 2px rgba(0,0,0,0.1); }
        .actions-cell .action-btn:hover{ background:#000; color:#fff; box-shadow:0 2px 4px rgba(0,0,0,0.15); transform:translateY(-1px); }

        /* Date inputs specifici */
        .start-date-col input[type="date"], .end-date-col input[type="date"]{ width:calc(100% - 4px); padding:4px 5px; margin:2px; font-size:9px; text-align:center; border-radius:3px; }

        .select-col{ width:12px; text-align:center; padding:2px; }
        .name-col{ width:80px; text-align:center; padding:1px; }
        .start-date-col, .end-date-col{ width:8px; text-align:center; padding:1px; }
        .start-time-col, .end-time-col{ width:10px; text-align:center; padding:1px; }
        .closure-days-col{ width:100px; text-align:center; padding:2px; }
        .actions-col{ width:16px; text-align:center; padding:1px; }
        
        /* Override specifici per colonne */
        .name-col .cell-input{ text-align:center; padding:3px 6px; margin:1px; font-size:9px; }
        .select-col input[type="checkbox"]{ width:14px; height:14px; margin:2px; border-radius:2px; }
        .closure-days-col .closure-days-select{ font-size:8px; padding:2px 4px; }
        
        /* Hover effects per input */
        input.cell-input:hover{ border-color:#333; }
        select.closure-days-select:hover{ border-color:#333; }

        .schedules-stats{ padding:6px 10px; font-size:10px; display:flex; gap:10px; background:#f8f9fa; border-top:1px solid #000; color:#000; border-radius:0 0 4px 4px; box-shadow:inset 0 1px 3px rgba(0,0,0,0.05); }
        .save-btn{ padding:4px 8px; font-size:10px; border:1px solid #000; background:#fff; color:#000; cursor:pointer; border-radius:2px; box-shadow:0 1px 2px rgba(0,0,0,0.1); }
        .save-btn:hover{ background:#000; color:#fff; box-shadow:0 4px 8px rgba(0,0,0,0.2); transform:translateY(-1px); }

        @media (max-width:800px){ .popup-window-container{ max-width:700px; width:90vw; } .excel-table{ min-width:350px; } }
        @media (max-width:700px){ .popup-window-container{ max-width:600px; width:95vw; } .excel-table{ min-width:320px; } }
        @media (max-width:600px){ .popup-window-container{ max-width:500px; width:98vw; } .excel-table{ min-width:300px; } }
        @media (max-width:500px){ .popup-window-container{ width:100vw; margin:0; border-radius:0; } .excel-table{ min-width:280px; } }
    </style>
</head>
<body>

    <div class="popup-window-container">
        <div class="window-header">
            <h1 class="window-title">🕒 Gestione Orario</h1>
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
                                <?php $isBlocked = $schedule['id'] === 1 && empty($schedule['startDate']) && empty($schedule['endDate']); ?>
                                <tr data-schedule-id="<?= $schedule['id'] ?>" class="<?= $isBlocked ? 'blocked-row' : '' ?>">
                                    <td><input type="checkbox" class="row-select" <?= $isBlocked ? 'disabled' : '' ?>></td>
                                    <td><input type="text" value="<?= htmlspecialchars($schedule['name']) ?>" placeholder="Nome orario..." class="cell-input" <?= $isBlocked ? 'disabled' : '' ?>></td>
                                    <td><?php if ($isBlocked): ?><span class="empty-cell">-</span><?php else: ?><input type="date" value="<?= $schedule['startDate'] ?>" class="cell-input"><?php endif; ?></td>
                                    <td><?php if ($isBlocked): ?><span class="empty-cell">-</span><?php else: ?><input type="date" value="<?= $schedule['endDate'] ?>" class="cell-input"><?php endif; ?></td>
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
                                    <td class="actions-cell"><?php if (!$isBlocked): ?><button class="action-btn btn-delete-single" data-schedule-id="<?= $schedule['id'] ?>" title="Elimina">🗑️</button><?php endif; ?></td>
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

        function generateScheduleRow(schedule){ const isBlocked = schedule.id===1 && (!schedule.startDate||!schedule.endDate); const disabled = isBlocked?'disabled':''; const startDateCell = isBlocked?'<td><span class="empty-cell">-</span></td>':`<td><input type="date" value="${escapeHtml(schedule.startDate)}" class="cell-input"></td>`; const endDateCell = isBlocked?'<td><span class="empty-cell">-</span></td>':`<td><input type="date" value="${escapeHtml(schedule.endDate)}" class="cell-input"></td>`; const closureOptions = ['lunedi','martedi','mercoledi','giovedi','venerdi','sabato','domenica'].map(d=>`<option value="${d}" ${Array.isArray(schedule.closureDays)&&schedule.closureDays.includes(d)?'selected':''}>${d.charAt(0).toUpperCase()+d.slice(1)}</option>`).join(''); const deleteBtn = isBlocked?'':`<button class="action-btn btn-delete-single" data-schedule-id="${schedule.id}">🗑️</button>`; return `
            <tr data-schedule-id="${schedule.id}" class="${isBlocked?'blocked-row':''}">
                <td><input type="checkbox" class="row-select" ${isBlocked?'disabled':''}></td>
                <td><input type="text" value="${escapeHtml(schedule.name)}" class="cell-input" ${disabled}></td>
                ${startDateCell}
                ${endDateCell}
                <td><input type="time" value="${escapeHtml(schedule.startTime)}" class="cell-input"></td>
                <td><input type="time" value="${escapeHtml(schedule.endTime)}" class="cell-input"></td>
                <td><select multiple class="cell-select closure-days-select">${closureOptions}</select></td>
                <td class="actions-cell">${deleteBtn}</td>
            </tr>`; }

        function renderSchedulesTable(){ const tbody = document.getElementById('schedules-list'); if(!tbody) return; if(!schedulesList.length){ tbody.innerHTML='<tr><td colspan="8">Nessun orario configurato</td></tr>'; updateSelectionStats(); return; } tbody.innerHTML = schedulesList.map(generateScheduleRow).join(''); updateSelectionStats(); }

        function updateSelectionStats(){ document.getElementById('selected-schedules').textContent = document.querySelectorAll('.row-select:checked').length; document.getElementById('total-schedules').textContent = schedulesList.length; }

        function syncRowToModel(row,id){ const schedule = schedulesList.find(s=>s.id===id); if(!schedule) return; const inputs = row.querySelectorAll('.cell-input'); schedule.name = inputs[0]?.value||''; if(inputs[1]?.type==='date') schedule.startDate = inputs[1].value||''; if(inputs[2]?.type==='date') schedule.endDate = inputs[2].value||''; schedule.startTime = inputs[3]?.value||''; schedule.endTime = inputs[4]?.value||''; saveSchedulesToStorage(); }

        function onAddSchedule(){ const newSchedule = { id: scheduleIdCounter++, name: 'Nuovo Orario '+scheduleIdCounter, startDate:'', endDate:'', startTime:'09:00', endTime:'18:00', closureDays:[] }; schedulesList.push(newSchedule); renderSchedulesTable(); saveSchedulesToStorage(); setTimeout(()=>document.querySelector(`tr[data-schedule-id="${newSchedule.id}"] input[type=text]`)?.focus(),0); }

        function onDeleteSingle(id){ const schedule = schedulesList.find(s=>s.id===id); if(!schedule) return; if(schedule.id===1 && (!schedule.startDate||!schedule.endDate)){ alert('Non è possibile eliminare l\'orario bloccato'); return; } if(!confirm('Eliminare questo orario?')) return; schedulesList = schedulesList.filter(s=>s.id!==id); renderSchedulesTable(); saveSchedulesToStorage(); }

        function onDeleteSelected(){ const ids = Array.from(document.querySelectorAll('.row-select:checked')).map(cb=>parseInt(cb.closest('tr').dataset.scheduleId)); if(ids.length===0){ alert('Nessun orario selezionato'); return; } if(ids.includes(1)){ const first = schedulesList.find(s=>s.id===1); if(first && (!first.startDate||!first.endDate)){ alert('Non è possibile eliminare l\'orario bloccato con date vuote dalla selezione'); return; } } if(!confirm(`Eliminare ${ids.length} orario/i selezionato/i?`)) return; schedulesList = schedulesList.filter(s=>!ids.includes(s.id)); renderSchedulesTable(); saveSchedulesToStorage(); document.getElementById('select-all-schedules').checked=false; }

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
        function onDeleteSingle(id){ const schedule = schedulesList.find(s=>s.id===id); if(!schedule) return; if(schedule.id===1 && (!schedule.startDate||!schedule.endDate)){ alert('Non è possibile eliminare l\'orario bloccato'); return; } if(!confirm('Eliminare questo orario?')) return; schedulesList = schedulesList.filter(s=>s.id!==id); renderSchedulesTable(); saveSchedulesToStorage(); }
        function onDeleteSelected(){ const ids = getSelectedScheduleIds(); if(ids.length===0){ alert('Nessun orario selezionato'); return; } if(ids.includes(1)){ const first = schedulesList.find(s=>s.id===1); if(first && (!first.startDate||!first.endDate)){ alert('Non è possibile eliminare l\'orario bloccato con date vuote dalla selezione'); return; } } if(!confirm(`Eliminare ${ids.length} orario/i selezionato/i?`)) return; schedulesList = schedulesList.filter(s=>!ids.includes(s.id)); renderSchedulesTable(); saveSchedulesToStorage(); document.getElementById('select-all-schedules').checked=false; }
        function onSaveAll(){ syncSchedulesFromUI(); saveSchedulesToStorage(); alert('Orari salvati correttamente!'); }
        function onToggleSelectAll(e){ const checked = e.target.checked; document.querySelectorAll('.row-select:not([disabled])').forEach(cb=>cb.checked=checked); updateSelectionStats(); }

        document.addEventListener('click', e=>{ const b = e.target.closest('.btn-delete-single'); if(b) onDeleteSingle(parseInt(b.dataset.scheduleId)); });
        document.addEventListener('input', e=>{ if(e.target.classList.contains('cell-input')){ const row = e.target.closest('tr'); if(!row) return; syncRowToModel(row, parseInt(row.dataset.scheduleId)); } });
        document.addEventListener('change', e=>{ if(e.target.classList.contains('closure-days-select')){ const row = e.target.closest('tr'); const id = parseInt(row.dataset.scheduleId); const schedule = schedulesList.find(s=>s.id===id); if(schedule) schedule.closureDays = Array.from(e.target.selectedOptions).map(o=>o.value); saveSchedulesToStorage(); } if(e.target.classList.contains('row-select')||e.target.id==='select-all-schedules') updateSelectionStats(); });

        document.addEventListener('DOMContentLoaded', ()=>{ loadSchedulesFromStorage(); renderSchedulesTable(); document.getElementById('add-schedule-btn')?.addEventListener('click', onAddSchedule); document.getElementById('delete-selected-btn')?.addEventListener('click', onDeleteSelected); document.getElementById('save-all-btn')?.addEventListener('click', onSaveAll); document.getElementById('select-all-schedules')?.addEventListener('change', onToggleSelectAll); });
    </script>

</body>
</html>
