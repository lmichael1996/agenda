// Costanti essenziali
const SLOT_PX = 25; // px per 15 minuti
const MIN_DURATION = 15; // minuti

let draggedNote = null;

function calculateDivHeight(duration, parentSlot) {
    // Calcola l'altezza basata sui div effettivi del calendario
    if (!parentSlot) return ((duration / 15) * 105 - 4) + '%';
    
    const slotHeight = parentSlot.offsetHeight;
    const slotsNeeded = duration / 15; // numero di slot da 15 minuti
    const totalHeight = slotHeight * slotsNeeded;
    
    return totalHeight + 'px';
}

function createNote(text, duration, parentSlot = null) {
    const note = document.createElement('div');
    note.className = 'calendar-note';
    note.draggable = true;
    // Usa calcolo basato su div effettivi se disponibile
    if (parentSlot) {
        note.style.height = calculateDivHeight(duration, parentSlot);
    } else {
        note.style.height = ((duration / 15) * 105 - 4) + '%';
    }
    note.setAttribute('data-duration', duration);

    const noteText = document.createElement('span');
    noteText.className = 'calendar-note-text';
    noteText.textContent = text;
    note.appendChild(noteText);

    const resizeBtn = document.createElement('button');
    resizeBtn.className = 'resize-btn';
    resizeBtn.type = 'button';
    resizeBtn.title = 'Ridimensiona evento';
    resizeBtn.innerHTML = '&#x2195;';
    resizeBtn.setAttribute('tabindex', '0');
    note.appendChild(resizeBtn);

    note.addEventListener('dragstart', function(e) {
        if (e.target.closest && e.target.closest('.resize-btn')) {
            e.preventDefault();
            return;
        }
        draggedNote = note;
        e.dataTransfer.setData('text/plain', text);
    });

    let isResizing = false;
    let startY = 0;
    let startHeight = 0;

    // Resize logic: aggiungi e rimuovi i listener solo durante il resize
    function onMouseMove(e) {
        if (!isResizing) return;
        let delta = e.clientY - startY;
        let newHeight = Math.max(SLOT_PX, startHeight + delta);
        let newDuration = Math.round(newHeight / SLOT_PX) * 15;
        newDuration = Math.max(MIN_DURATION, newDuration);
        
        // Usa calcolo basato su div se il parent è disponibile
        const parentSlot = note.parentElement;
        if (parentSlot && parentSlot.classList.contains('day')) {
            note.style.height = calculateDivHeight(newDuration, parentSlot);
        } else {
            note.style.height = ((newDuration / 15) * 105 - 4) + '%';
        }
        note.setAttribute('data-duration', newDuration);
    }

    function onMouseUp(e) {
        if (isResizing) {
            isResizing = false;
            document.body.style.cursor = '';
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
        }
    }

    resizeBtn.addEventListener('pointerdown', function(e) {
        if (e.button !== 0) return;
        e.preventDefault();
        e.stopPropagation();
        isResizing = true;
        startY = e.clientY;
        startHeight = note.offsetHeight;
        document.body.style.cursor = 'ns-resize';
        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    });

    return note;
}

window.addEventListener('DOMContentLoaded', function() {
    // --- Inizializzazione note demo ---
    const slots = document.querySelectorAll('.calendar-grid .day');

    const slotMartedi = slots[1]; // martedì 8:00
    slotMartedi.style.position = 'relative';
    const eventoMartedi = createNote('Evento Martedì', 30, slotMartedi);
    slotMartedi.appendChild(eventoMartedi);

    const slotSabato = slots[5];  // sabato 8:00
    slotSabato.style.position = 'relative';
    const eventoSabato = createNote('Evento Sabato', 45, slotSabato);
    slotSabato.appendChild(eventoSabato);

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
                // Reset posizione assoluta della nota
                draggedNote.style.top = '0';
                draggedNote.style.left = '0';
                // Rimuovi la nota dal vecchio slot e aggiungila al nuovo
                slot.appendChild(draggedNote);
                draggedNote = null;
            }
        });
    });

    // --- Evidenzia slot ora attuale ---
    const now = new Date();
    // Data nel formato d-m-Y
    const day = String(now.getDate()).padStart(2, '0') + '-' +
        String(now.getMonth() + 1).padStart(2, '0') + '-' + now.getFullYear();

    // Arrotonda i minuti al quarto d'ora più vicino
    let min = now.getMinutes();
    min = Math.round(min / 15) * 15;
    
    if (min === 60) { min = 0; now.setHours(now.getHours() + 1); }
    const hour = String(now.getHours()).padStart(2, '0');
    const minStr = String(min).padStart(2, '0');
    const time = hour + ':' + minStr;
    
    // Seleziona lo slot corrispondente
    const selector = `.day[data-date='${day}'][data-time='${time}']`;
    const slot = document.querySelector(selector);
    if (slot) {
        slot.classList.add('current-time');
        slot.scrollIntoView({block: 'center', behavior: 'smooth'});
    }
});