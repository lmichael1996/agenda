const dayNames = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];
const viewInput = document.getElementById('set-view');
const weekInput = document.getElementById('set-week');
const dateInput = document.getElementById('set-date');

function getMonday(w, y) {
    const simple = new Date(y, 0, 1 + (w - 1) * 7);
    const dow = simple.getDay();
    const monday = new Date(simple);
    if (dow === 0) monday.setDate(simple.getDate() + 1); // domenica -> lunedì
    else monday.setDate(simple.getDate() - dow + 1);
    return monday;
}

viewInput.addEventListener('change', function() {
    const val = this.value;
    if (val === 'week') {
        weekInput.hidden = false;
        dateInput.hidden = true;
    } else if (val === 'day') {
        weekInput.hidden = true;
        dateInput.hidden = false;
    }
});

weekInput.addEventListener('change', function() {
    const val = this.value; // formato: YYYY-Wnn
    const [year, week] = val.split('-W');

    const monday = getMonday(parseInt(week), parseInt(year));
    const today = new Date();

    // Aggiorna header giorni
    for (let i = 0; i < 7; i++) {
        const d = new Date(monday);
        d.setDate(monday.getDate() + i);
        const header = document.getElementById('header-day-' + i);

        header.innerHTML = dayNames[i] + '<br><span class="header-date">' + d.toLocaleDateString('it-IT') + '</span>';
        if (d.toDateString() === today.toDateString()) {
            header.classList.add('today');
        } else {
            header.classList.remove('today');
        }
    }

    // Aggiorna data-date, data-time e classe today degli slot
    const calendarGrid = document.querySelector('.calendar-grid');
    let slotIdx = 0;

    for (let t = 8; t <= 22; t++) {
        for (let m = 0; m < 60; m += 15) {
            for (let i = 0; i < 7; i++) {
                const d = new Date(monday);
                d.setDate(monday.getDate() + i);
                const slot = calendarGrid.querySelectorAll('div.day')[slotIdx];

                if (slot) {
                    const dateStr = ('0' + d.getDate()).slice(-2) + '-' + ('0' + (d.getMonth()+1)).slice(-2) + '-' + d.getFullYear();
                    const timeStr = ('0' + t).slice(-2) + ':' + ('0' + m).slice(-2);

                    slot.setAttribute('data-date', dateStr);
                    slot.setAttribute('data-time', timeStr);
                    // Gestione classe today
                    if (d.toDateString() === today.toDateString()) {
                        slot.classList.add('today');
                    } else {
                        slot.classList.remove('today');
                    }
                }
                slotIdx++;
            }
        }
    }
});