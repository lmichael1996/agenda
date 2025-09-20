// Date utility functions available from date-utils.js:
// - getMonday(week, year)
// - isToday(date)
// - formatDate(date)
// - formatTime(hour, minute)

// Gestione selezione vista (set-view) e aggiornamento calendario
const dayNames = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];
const viewInput = document.getElementById('set-view');
const weekInput = document.getElementById('set-week');
const dateInput = document.getElementById('set-date');

viewInput.addEventListener('change', function() {
    const isWeek = this.value === 'week';
    weekInput.hidden = !isWeek;
    dateInput.hidden = isWeek;
});

weekInput.addEventListener('change', function() {
    const [year, week] = this.value.split('-W').map(Number);
    const monday = getMonday(week, year);

    // Aggiorna header giorni
    for (let i = 0; i < 7; i++) {
        const date = new Date(monday);
        date.setDate(monday.getDate() + i);
        const header = document.getElementById('header-day-' + i);

        header.innerHTML = `${dayNames[i]}<br><span class="header-date">${date.toLocaleDateString('it-IT')}</span>`;
        header.classList.toggle('today', isToday(date));
    }

    // Aggiorna slot del calendario
    const slots = document.querySelectorAll('.calendar-grid .day');
    let slotIndex = 0;

    for (let hour = 8; hour <= 22; hour++) {
        for (let minute = 0; minute < 60; minute += 15) {
            for (let day = 0; day < 7; day++) {
                const date = new Date(monday);
                date.setDate(monday.getDate() + day);
                const slot = slots[slotIndex++];

                if (slot) {
                    slot.setAttribute('data-date', formatDate(date));
                    slot.setAttribute('data-time', formatTime(hour, minute));
                    slot.classList.toggle('today', isToday(date));
                }
            }
        }
    }
});