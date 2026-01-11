import { Slot } from './slot.js';
import { EventManager } from './event-manager.js';
import { formatDate, getCurrentTime } from '../utils/date-utils.js';

class Calendar {
    constructor(containerSelector = '.calendar-grid .day') {
        this.containerSelector = containerSelector;
        this.slotElements = null;
        this.slots = [];
        this.currentSlot = null;
        this.eventManager = new EventManager();
        
        this.init();
    }
    
    init() {
        this.getSlotElements();
        this.setupSlots();
        this.generateEvents();
        this.highlightCurrentTime();
    }
    
    getSlotElements() {
        this.slotElements = document.querySelectorAll(this.containerSelector);
        if (this.slotElements.length === 0) {
            console.warn(`Nessun elemento trovato con selettore: ${this.containerSelector}`);
        }
    }
    
    setupSlots() {
        if (!this.slotElements) return;
        
        // Configura tutti gli slot come drop targets usando la classe Slot
        this.slots = Slot.setupAllSlots(this.slotElements);
        console.log(`Configurati ${this.slots.length} slot del calendario`);
    }
    
    generateEvents() {
        if (!this.slotElements) return;
        
        // Gli eventi vengono caricati dal database tramite calendar-events.js
        console.log('Slot pronti per ricevere eventi dal database');
    }
    
    highlightCurrentTime() {
        // Seleziona lo slot corrispondente all'ora attuale
        const selector = `.day[data-date='${formatDate(new Date())}'][data-time='${getCurrentTime()}']`;
        this.currentSlot = document.querySelector(selector);
        
        if (this.currentSlot) {
            this.currentSlot.classList.add('current-time');
            this.currentSlot.scrollIntoView({block: 'center', behavior: 'smooth'});
            console.log('Slot corrente evidenziato e portato in vista');
        }
    }
    
    // Metodi utili per interagire con il calendario
    getSlotByIndex(index) {
        return this.slots[index] || null;
    }
    
    getAllSlots() {
        return this.slots;
    }
    
    getEmptySlots() {
        return this.slots.filter(slot => slot.isEmpty);
    }
    
    getOccupiedSlots() {
        return this.slots.filter(slot => !slot.isEmpty);
    }
    
    // Metodi per gestire gli eventi tramite EventManager
    createEvent(text, duration, parentSlot = null, superAppointmentId = null) {
        return this.eventManager.createEvent(text, duration, parentSlot, superAppointmentId);
    }
    
    getAllEvents() {
        return this.eventManager.getAllEvents();
    }
    
    removeEvent(event) {
        this.eventManager.removeEvent(event);
    }
    
    clearAllEvents() {
        this.eventManager.clearAllEvents();
    }
    
    // Refresh del calendario
    refresh() {
        console.log('Aggiornamento calendario...');
        this.init();
    }
}

export { Calendar };