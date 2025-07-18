<?php
/*
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}
*/
// Calculate current week days (Monday-Sunday)
$today = new DateTime();
$weekDay = (int)$today->format('N'); // 1 (Mon) - 7 (Sun)
$monday = clone $today;
$monday->modify('-' . ($weekDay - 1) . ' days');
$days = [];
for ($i = 0; $i < 7; $i++) {
    $days[] = clone $monday;
    $monday->modify('+1 day');
}

// Day names
$dayNames = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];

// 15-minute intervals from 8:00 to 22:00
$intervals = [];
for ($h = 8; $h <= 22; $h++) {
    for ($m = 0; $m < 60; $m += 15) {
        $intervals[] = sprintf('%02d:%02d', $h, $m);
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Calendario Settimanale</title>

    <link rel="stylesheet" href="style/top-menu.css">
    <link rel="stylesheet" href="style/week-calendar-style.css">
    <link rel="stylesheet" href="style/lateral-menu-style.css">
</head>
<body>
    
    <div id="sidebar" class="sidebar sidebar-hover">
        <a href="#">Servizi</a>
        <a href="#">Utenti</a>
        <a href="#">Orario</a>
    </div>

    <div class="dashboard-controls">

        <div class="controls-left">
            <select id="set-view" onmouseover="this.style.cursor='pointer'">
                <option value="week">Settimana</option>
                <option value="day">Giorno</option>
            </select>
            <input type="week" id="set-week" onmouseover="this.style.cursor='pointer'" value="<?= $today->format('Y-\WW') ?>">
            <input type="date" id="set-date" onmouseover="this.style.cursor='pointer'" value="<?= $today->format('Y-m-d') ?>" hidden>
        </div>
        
        <div class="controls-center">
            <label for="cerca" class="control-label">Cliente:</label>
            <input type="search" autocomplete="on" id="cerca" placeholder="Cerca cliente...">
            <input type="submit" onmouseover="this.style.cursor='pointer'" value="Cerca">
            <button type="button" id="note-btn" title="Note" onmouseover="this.style.cursor='pointer'">📝</button>
        </div>
        
        <div class="controls-right">
            <marquee> Non è mai troppo tardi per essere ciò che vuoi essere! </marquee>
        </div>
    
    </div>

    <div class="dashboard-container">

        <div class="calendar-header-row">
            <div class="hour-label-empty">Orario:</div>

            <?php foreach ($days as $idx => $day): ?>
                <div class = "header-day<?= $day->format('d-m-Y') === $today->format('d-m-Y') ? ' today' : '' ?>" 
                    id = "header-day-<?= $idx ?>">
                    <?= htmlspecialchars($dayNames[$idx]) ?><br>
                    <span class="header-date"> <?= $day->format('d/m/Y') ?> </span>
                </div>
            <?php endforeach; ?>

        </div>

        <div class="calendar-grid">

            <?php foreach ($intervals as $time): ?>
                <div class="hour-label"><span class="hour-label-time"><?= $time ?></span></div>

                <?php foreach ($days as $day): ?>
                    <div class = "day<?= $day->format('d-m-Y') === $today->format('d-m-Y') ? ' today' : '' ?>"
                        data-date = "<?= $day->format('d-m-Y') ?>"
                        data-time ="<?= $time ?>">
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        
        </div>

    </div>

<script src="js/lateral-menu.js"></script>
<script src="js/calendar-date.js"></script>
<script>
// Drag & drop note su slot calendario
let draggedNote = null;

// Crea una nota di esempio
function createNote(text, duration) {
    const note = document.createElement('div');
    note.className = 'calendar-note';
    note.draggable = true;
    note.style.background = '#ffe';
    note.style.border = '1px solid #222';
    note.style.padding = '2px 6px 10px 6px'; // spazio extra sotto per handle
    note.style.borderRadius = '4px';
    note.style.cursor = 'grab';
    note.style.fontSize = '13px';
    note.style.width = '100%';
    note.style.boxSizing = 'border-box';
    note.style.display = 'flex';
    note.style.flexDirection = 'column';
    note.style.alignItems = 'stretch';
    note.style.justifyContent = 'flex-end';
    note.style.position = 'absolute';
    note.style.left = '0';
    note.style.top = '0';
    note.style.zIndex = '20';
    // Altezza proporzionale alla durata (es: 15min=100%, 30min=200%, 45min=300%, 60min=400%)
    note.style.height = (duration / 15 * 100) + '%';
    note.setAttribute('data-duration', duration);

    // Testo nota
    const noteText = document.createElement('span');
    noteText.textContent = text;
    noteText.style.width = '100%';
    noteText.style.textAlign = 'center';
    noteText.style.flex = '0 0 auto';
    noteText.style.marginBottom = 'auto';
    note.appendChild(noteText);

    // Button di resize
    const resizeBtn = document.createElement('button');
    resizeBtn.className = 'resize-btn';
    resizeBtn.type = 'button';
    resizeBtn.title = 'Ridimensiona evento';
    resizeBtn.innerHTML = '&#x2195;'; // icona ↕
    resizeBtn.style.width = '22px';
    resizeBtn.style.height = '18px';
    resizeBtn.style.cursor = 'ns-resize';
    resizeBtn.style.textAlign = 'center';
    resizeBtn.style.userSelect = 'none';
    resizeBtn.style.fontSize = '15px';
    resizeBtn.style.lineHeight = '16px';
    resizeBtn.style.margin = '0 auto 0 auto';
    resizeBtn.style.marginTop = '2px';
    resizeBtn.style.color = '#888';
    resizeBtn.style.background = 'transparent';
    resizeBtn.style.border = 'none';
    resizeBtn.style.flex = '0 0 auto';
    resizeBtn.style.padding = '0';
    resizeBtn.setAttribute('tabindex', '0');
    note.appendChild(resizeBtn);

    // Drag & drop
    note.addEventListener('dragstart', function(e) {
        // Se il drag parte dal resize button, ignora
        if (e.target.closest && e.target.closest('.resize-btn')) {
            e.preventDefault();
            return;
        }
        draggedNote = note;
        e.dataTransfer.setData('text/plain', text);
    });

    // Resize logica
    let isResizing = false;
    let startY = 0;
    let startHeight = 0;
    resizeBtn.addEventListener('pointerdown', function(e) {
        if (e.button !== 0) return; // solo click sinistro
        e.preventDefault();
        e.stopPropagation();
        isResizing = true;
        startY = e.clientY;
        startHeight = note.offsetHeight;
        document.body.style.cursor = 'ns-resize';
    });
    document.addEventListener('mousemove', function(e) {
        if (!isResizing) return;
        let delta = e.clientY - startY;
        let newHeight = Math.max(24, startHeight + delta); // min 1 slot
        // Calcola nuova durata: ogni 24px = 15min (adatta se slot è 24px)
        let slotPx = 24; // puoi adattare se slot diverso
        let newDuration = Math.round(newHeight / slotPx) * 15;
        newDuration = Math.max(15, newDuration); // solo limite minimo 15min
        note.style.height = (newDuration / 15 * 100) + '%';
        note.setAttribute('data-duration', newDuration);
    });
    document.addEventListener('mouseup', function(e) {
        if (isResizing) {
            isResizing = false;
            document.body.style.cursor = '';
        }
    });

    return note;
}

window.addEventListener('DOMContentLoaded', function() {
    const slots = document.querySelectorAll('.calendar-grid .day');
    // Esempio: 3 note di durata diversa
    if (slots[0]) slots[0].style.position = 'relative';
    if (slots[0]) slots[0].appendChild(createNote('Evento breve', 15));
    if (slots[7]) slots[7].style.position = 'relative';
    if (slots[7]) slots[7].appendChild(createNote('Evento medio', 30));
    if (slots[14]) slots[14].style.position = 'relative';
    if (slots[14]) slots[14].appendChild(createNote('Evento lungo', 45));
    // Rendi tutti gli slot drop target
    slots.forEach(slot => {
        slot.style.position = 'relative';
        slot.addEventListener('dragover', function(e) {
            e.preventDefault();
            slot.style.background = '#e0f7fa';
        });
        slot.addEventListener('dragleave', function() {
            slot.style.background = '';
        });
        slot.addEventListener('drop', function(e) {
            e.preventDefault();
            slot.style.background = '';
            if (draggedNote) {
                slot.appendChild(draggedNote);
                draggedNote = null;
            }
        });
    });
});
</script>

</body>
</html>
