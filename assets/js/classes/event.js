import { setDraggedEvent, getDraggedEvent, clearDraggedEvent } from '../utils/drag-state.js';
import { SLOT_PX, MIN_DURATION } from '../utils/constants.js';

class Event {
    constructor(text, duration, parentSlot = null) {
        this.text = text;
        this.duration = duration;
        this.parentSlot = parentSlot;
        this.isResizing = false;
        this.startY = 0;
        this.startHeight = 0;
        
        this.element = this.createElement();
        this.setupEventListeners();
        
        // Configura slot come drop target se fornito
        if (parentSlot) {
            this.setupDropTarget(parentSlot);
        }
    }
    
    // Configura uno slot come drop target per eventi
    setupDropTarget(slot) {
        slot.style.position = 'relative';
        
        if (!slot.hasAttribute('data-drop-configured')) {
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
                let draggedEvent = getDraggedEvent();
                if (draggedEvent) {
                    // Reset posizione assoluta dell'evento
                    draggedEvent.style.top = '0';
                    draggedEvent.style.left = '0';
                    // Rimuovi l'evento dal vecchio slot e aggiungilo al nuovo
                    slot.appendChild(draggedEvent);
                    clearDraggedEvent();
                }
            });
            
            slot.setAttribute('data-drop-configured', 'true');
        }
    }
    
    // Metodo statico per configurare tutti gli slot come drop targets
    static setupAllDropTargets(slots) {
        slots.forEach(slot => {
            const event = new Event('', 0);
            event.setupDropTarget(slot);
        });
    }
    
    calculateDivHeight(duration, parentSlot) {
        // Calcola l'altezza basata sui div effettivi del calendario
        if (!parentSlot) return ((duration / 15) * 105 - 4) + '%';
        
        const slotHeight = parentSlot.offsetHeight;
        const slotsNeeded = duration / 15; // numero di slot da 15 minuti
        const totalHeight = slotHeight * slotsNeeded;
        
        return totalHeight + 'px';
    }
    
    createElement() {
        const event = document.createElement('div');
        event.className = 'calendar-note';
        event.draggable = true;
        
        // Calcola altezza
        if (this.parentSlot) {
            event.style.height = this.calculateDivHeight(this.duration, this.parentSlot);
        } else {
            event.style.height = ((this.duration / 15) * 105 - 4) + '%';
        }
        event.setAttribute('data-duration', this.duration);

        // Aggiungi testo
        const eventText = document.createElement('span');
        eventText.className = 'calendar-note-text';
        eventText.textContent = this.text;
        event.appendChild(eventText);

        // Aggiungi pulsante resize
        const resizeBtn = document.createElement('button');
        resizeBtn.className = 'resize-btn';
        resizeBtn.type = 'button';
        resizeBtn.title = 'Ridimensiona evento';
        resizeBtn.innerHTML = '&#x2195;';
        resizeBtn.setAttribute('tabindex', '0');
        event.appendChild(resizeBtn);
        
        this.resizeBtn = resizeBtn;
        return event;
    }
    
    setupEventListeners() {
        // Drag eventi
        this.element.addEventListener('dragstart', (e) => {
            if (e.target.closest && e.target.closest('.resize-btn')) {
                e.preventDefault();
                return;
            }
            setDraggedEvent(this.element);
            e.dataTransfer.setData('text/plain', this.text);
        });

        // Resize eventi
        this.resizeBtn.addEventListener('pointerdown', (e) => {
            if (e.button !== 0) return;
            e.preventDefault();
            e.stopPropagation();
            this.startResize(e);
        });
    }
    
    startResize(e) {
        this.isResizing = true;
        this.startY = e.clientY;
        this.startHeight = this.element.offsetHeight;
        document.body.style.cursor = 'ns-resize';
        
        document.addEventListener('mousemove', this.onMouseMove.bind(this));
        document.addEventListener('mouseup', this.onMouseUp.bind(this));
    }
    
    onMouseMove(e) {
        if (!this.isResizing) return;
        
        let delta = e.clientY - this.startY;
        let newHeight = Math.max(SLOT_PX, this.startHeight + delta);
        let newDuration = Math.round(newHeight / SLOT_PX) * 15;
        newDuration = Math.max(MIN_DURATION, newDuration);
        
        // Aggiorna altezza
        const parentSlot = this.element.parentElement;
        if (parentSlot && parentSlot.classList.contains('day')) {
            this.element.style.height = this.calculateDivHeight(newDuration, parentSlot);
        } else {
            this.element.style.height = ((newDuration / 15) * 105 - 4) + '%';
        }
        
        this.duration = newDuration;
        this.element.setAttribute('data-duration', newDuration);
    }
    
    onMouseUp() {
        if (this.isResizing) {
            this.isResizing = false;
            document.body.style.cursor = '';
            document.removeEventListener('mousemove', this.onMouseMove.bind(this));
            document.removeEventListener('mouseup', this.onMouseUp.bind(this));
        }
    }
}

// Export della classe Event
export { Event };