// Utility functions for date handling
const dayNames = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];

function getMonday(week, year) {
    const date = new Date(year, 0, 1 + (week - 1) * 7);
    const day = date.getDay();
    const offset = day === 0 ? 1 : 1 - day;
    date.setDate(date.getDate() + offset);
    return date;
}

function isToday(date) {
    return date.toDateString() === new Date().toDateString();
}

function formatDate(date) {
    return ('0' + date.getDate()).slice(-2) + '-' +
           ('0' + (date.getMonth() + 1)).slice(-2) + '-' +
           date.getFullYear();
}

function formatTime(hour, minute) {
    return ('0' + hour).slice(-2) + ':' + ('0' + minute).slice(-2);
}

// Funzione per ottenere ora attuale arrotondata ai 15 minuti
function getCurrentTime() {
    const now = new Date();
    let min = Math.round(now.getMinutes() / 15) * 15;
    let hour = now.getHours();
    
    if (min === 60) { 
        min = 0; 
        hour += 1; 
    }
    
    return formatTime(hour, min);
}

// Export functions for ES6 modules
export { dayNames, getCurrentTime, getMonday, isToday, formatDate, formatTime };