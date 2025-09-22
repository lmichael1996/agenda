// Gestione dello stato del drag and drop
let draggedEvent = null;

// Funzioni per gestire draggedEvent
function setDraggedEvent(event) {
    draggedEvent = event;
}

function getDraggedEvent() {
    return draggedEvent;
}

function clearDraggedEvent() {
    draggedEvent = null;
}

export { setDraggedEvent, getDraggedEvent, clearDraggedEvent };