// Gestione date e vista del calendario
import { CalendarDateManager } from './classes/calendar-date-manager.js';

// Inizializzazione del gestore date
document.addEventListener('DOMContentLoaded', function() {
    console.log('Inizializzazione CalendarDateManager...');
    
    const dateManager = new CalendarDateManager();
    
    // Rendi l'istanza accessibile globalmente per debug (opzionale)
    window.dateManager = dateManager;
    
    console.log('CalendarDateManager inizializzato con successo!');
});