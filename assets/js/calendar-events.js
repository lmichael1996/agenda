// Inizializzazione del calendario
import { Calendar } from './classes/calendar.js';

window.addEventListener('DOMContentLoaded', function() {
    console.log('Inizializzazione calendario...');
    
    // Crea una nuova istanza del calendario
    const calendar = new Calendar('.calendar-grid .day');

    // Rendi l'istanza accessibile globalmente solo per debug (opzionale)
    window.calendar = calendar;

    console.log('Calendario inizializzato con successo!');
});