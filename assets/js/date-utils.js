// Utility functions for date handling
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