import { getDraggedEvent, clearDraggedEvent } from '../utils/drag-state.js';

class Slot {
    constructor(element) {
        this.element = element;
        this.isConfigured = false;
        this.setupDropTarget();
    }
    
    setupDropTarget() {
        if (this.isConfigured || this.element.hasAttribute('data-drop-configured')) {
            return;
        }
        
        this.element.style.position = 'relative';
        
        // Event listeners per drag and drop
        this.element.addEventListener('dragover', this.handleDragOver.bind(this));
        this.element.addEventListener('dragleave', this.handleDragLeave.bind(this));
        this.element.addEventListener('drop', this.handleDrop.bind(this));
        
        this.element.setAttribute('data-drop-configured', 'true');
        this.isConfigured = true;
    }
    
    handleDragOver(e) {
        e.preventDefault();
        this.element.style.background = '#e0f7fa';
    }
    
    handleDragLeave() {
        this.element.style.background = '';
    }
    
    handleDrop(e) {
        e.preventDefault();
        this.element.style.background = '';
        
        let draggedEvent = getDraggedEvent();
        if (draggedEvent) {
            // Reset posizione assoluta dell'evento
            draggedEvent.style.top = '0';
            draggedEvent.style.left = '0';
            // Rimuovi l'evento dal vecchio slot e aggiungilo al nuovo
            this.element.appendChild(draggedEvent);
            clearDraggedEvent();
        }
    }
    
    // Metodo statico per configurare tutti gli slot
    static setupAllSlots(slotElements) {
        return Array.from(slotElements).map(element => new Slot(element));
    }
    
    // Getter per verificare se lo slot è vuoto
    get isEmpty() {
        return this.element.children.length === 0;
    }
    
    // Metodo per aggiungere un evento allo slot
    appendChild(eventElement) {
        this.element.appendChild(eventElement);
    }
    
    // Metodo per ottenere tutti gli eventi nello slot
    getEvents() {
        return Array.from(this.element.children).filter(child => 
            child.classList.contains('calendar-note')
        );
    }
}

export { Slot };