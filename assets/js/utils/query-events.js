// Funzione per generare eventi di demo
import { Event } from '../classes/event.js';

function generateDemoEvents(slots) {
    const events = [
        { slotIndex: 1, text: 'Evento Martedì', duration: 30 },
        { slotIndex: 5, text: 'Evento Sabato', duration: 45 },
        { slotIndex: 14, text: 'Riunione Team', duration: 60 },
        { slotIndex: 21, text: 'Call Cliente', duration: 90 }
    ];

    events.forEach(event => {
        const slot = slots[event.slotIndex];
        if (slot) {
            slot.style.position = 'relative';
            const evento = new Event(event.text, event.duration, slot);
            slot.appendChild(evento.element);
        }
    });

    return slots;
}

export { generateDemoEvents };