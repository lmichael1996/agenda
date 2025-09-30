/**
 * API Frontend per gestione note
 * Funzioni JavaScript per interagire con l'API backend delle note
 */

const NOTES_API_URL = '../../api/backend/notes-api.php';

/**
 * Recupera tutte le note o filtra per parametri
 * @param {Object} filters - Filtri opzionali
 * @param {number} filters.user_id - ID utente
 * @param {boolean} filters.for_all - Solo note pubbliche
 * @param {string} filters.date_from - Data inizio (YYYY-MM-DD)
 * @param {string} filters.date_to - Data fine (YYYY-MM-DD)
 * @returns {Promise<Object>} Response con array di note
 */
export async function fetchNotes(filters = {}) {
    try {
        const params = new URLSearchParams();
        
        if (filters.user_id) params.append('user_id', filters.user_id);
        if (filters.for_all !== undefined) params.append('for_all', filters.for_all);
        if (filters.date_from) params.append('date_from', filters.date_from);
        if (filters.date_to) params.append('date_to', filters.date_to);
        
        const url = params.toString() ? `${NOTES_API_URL}?${params.toString()}` : NOTES_API_URL;
        
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Errore sconosciuto nel recupero delle note');
        }
        
        return {
            success: true,
            notes: data.notes || []
        };
        
    } catch (error) {
        console.error('Errore fetchNotes:', error);
        return {
            success: false,
            error: error.message,
            notes: []
        };
    }
}

/**
 * Recupera una singola nota per ID
 * @param {number} noteId - ID della nota
 * @returns {Promise<Object>} Response con dati della nota
 */
export async function fetchNoteById(noteId) {
    try {
        if (!noteId) {
            throw new Error('ID nota obbligatorio');
        }
        
        const response = await fetch(`${NOTES_API_URL}?id=${noteId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Nota non trovata');
        }
        
        return {
            success: true,
            note: data.note
        };
        
    } catch (error) {
        console.error('Errore fetchNoteById:', error);
        return {
            success: false,
            error: error.message,
            note: null
        };
    }
}

/**
 * Salva una nuova nota
 * @param {Object} noteData - Dati della nota
 * @param {string} noteData.title - Titolo della nota
 * @param {string} noteData.content - Contenuto della nota
 * @param {boolean} noteData.for_all - Visibile a tutti
 * @param {string} noteData.note_date - Data della nota (YYYY-MM-DD)
 * @param {number} noteData.user_id - ID utente creatore
 * @returns {Promise<Object>} Response con risultato operazione
 */
export async function saveNote(noteData) {
    try {
        // Validazione dati obbligatori
        if (!noteData.user_id) {
            throw new Error('ID utente obbligatorio');
        }
        
        if (!noteData.note_date) {
            throw new Error('Data nota obbligatoria');
        }
        
        // Preparazione dati
        const payload = {
            title: noteData.title || '',
            content: noteData.content || '',
            for_all: Boolean(noteData.for_all),
            note_date: noteData.note_date,
            user_id: parseInt(noteData.user_id)
        };
        
        const response = await fetch(NOTES_API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Errore durante il salvataggio');
        }
        
        return {
            success: true,
            message: data.message,
            note_id: data.note_id
        };
        
    } catch (error) {
        console.error('Errore saveNote:', error);
        return {
            success: false,
            error: error.message
        };
    }
}

/**
 * Aggiorna una nota esistente
 * @param {number} noteId - ID della nota da aggiornare
 * @param {Object} noteData - Nuovi dati della nota
 * @returns {Promise<Object>} Response con risultato operazione
 */
export async function updateNote(noteId, noteData) {
    try {
        if (!noteId) {
            throw new Error('ID nota obbligatorio');
        }
        
        // Preparazione dati
        const payload = {
            id: parseInt(noteId),
            title: noteData.title || '',
            content: noteData.content || '',
            for_all: Boolean(noteData.for_all)
        };
        
        // Aggiungi data solo se fornita
        if (noteData.note_date) {
            payload.note_date = noteData.note_date;
        }
        
        const response = await fetch(NOTES_API_URL, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Errore durante l\'aggiornamento');
        }
        
        return {
            success: true,
            message: data.message
        };
        
    } catch (error) {
        console.error('Errore updateNote:', error);
        return {
            success: false,
            error: error.message
        };
    }
}

/**
 * Elimina una nota
 * @param {number} noteId - ID della nota da eliminare
 * @returns {Promise<Object>} Response con risultato operazione
 */
export async function deleteNote(noteId) {
    try {
        if (!noteId) {
            throw new Error('ID nota obbligatorio');
        }
        
        const response = await fetch(`${NOTES_API_URL}?id=${noteId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Errore durante l\'eliminazione');
        }
        
        return {
            success: true,
            message: data.message
        };
        
    } catch (error) {
        console.error('Errore deleteNote:', error);
        return {
            success: false,
            error: error.message
        };
    }
}

/**
 * Formatta una data per l'API (YYYY-MM-DD)
 * @param {Date|string} date - Data da formattare
 * @returns {string} Data formattata
 */
export function formatDateForAPI(date) {
    if (date instanceof Date) {
        return date.toISOString().split('T')[0];
    }
    
    if (typeof date === 'string') {
        // Verifica se è già nel formato corretto
        if (/^\d{4}-\d{2}-\d{2}$/.test(date)) {
            return date;
        }
        
        // Prova a parsare e riformattare
        const parsed = new Date(date);
        if (!isNaN(parsed)) {
            return parsed.toISOString().split('T')[0];
        }
    }
    
    // Fallback alla data odierna
    return new Date().toISOString().split('T')[0];
}

/**
 * Valida i dati di una nota
 * @param {Object} noteData - Dati da validare
 * @returns {Object} Risultato validazione
 */
export function validateNoteData(noteData) {
    const errors = [];
    
    if (!noteData.user_id || isNaN(parseInt(noteData.user_id))) {
        errors.push('ID utente non valido');
    }
    
    if (!noteData.note_date || !/^\d{4}-\d{2}-\d{2}$/.test(noteData.note_date)) {
        errors.push('Data nota non valida (formato YYYY-MM-DD richiesto)');
    }
    
    if (noteData.title && noteData.title.length > 200) {
        errors.push('Titolo troppo lungo (massimo 200 caratteri)');
    }
    
    if (noteData.content && noteData.content.length > 500) {
        errors.push('Contenuto troppo lungo (massimo 500 caratteri)');
    }
    
    return {
        valid: errors.length === 0,
        errors: errors
    };
}