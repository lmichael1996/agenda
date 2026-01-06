import { Event } from './event.js';

class EventManager {
    constructor() {
        this.events = [];
        // Gli eventi vengono caricati dal database tramite calendar-events.js
    }
    
    // Crea un nuovo evento
    createEvent(text, duration, parentSlot = null) {
        const event = new Event(text, duration, parentSlot);
        this.events.push(event);
        return event;
    }
    
    // Aggiunge un evento a uno slot
    addEventToSlot(event, slot) {
        if (slot && event && event.element) {
            slot.style.position = 'relative';
            slot.appendChild(event.element);
        }
    }
    
    // Rimuove un evento
    removeEvent(event) {
        const index = this.events.indexOf(event);
        if (index > -1) {
            this.events.splice(index, 1);
            if (event.element && event.element.parentNode) {
                event.element.parentNode.removeChild(event.element);
            }
        }
    }
    
    // Ottiene tutti gli eventi
    getAllEvents() {
        return this.events;
    }
    
    // Ottiene eventi per durata
    getEventsByDuration(duration) {
        return this.events.filter(event => event.duration === duration);
    }
    
    // Ottiene eventi per testo
    getEventsByText(text) {
        return this.events.filter(event => event.text.includes(text));
    }
    
    // Pulisce tutti gli eventi
    clearAllEvents() {
        this.events.forEach(event => {
            if (event.element && event.element.parentNode) {
                event.element.parentNode.removeChild(event.element);
            }
        });
        this.events = [];
    }
}

export { EventManager };