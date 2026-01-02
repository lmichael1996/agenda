/**
 * API Frontend per gestione note
 */

const NOTES_API_URL = '/api.php/notes';

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
            headers: { 'Content-Type': 'application/json' },
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

export async function saveNote(noteData) {
    try {
        if (!noteData.user_id) {
            throw new Error('ID utente obbligatorio');
        }
        
        if (!noteData.note_date) {
            throw new Error('Data nota obbligatoria');
        }
        
        const payload = {
            title: noteData.title || '',
            content: noteData.content || '',
            for_all: Boolean(noteData.for_all),
            note_date: noteData.note_date,
            user_id: parseInt(noteData.user_id)
        };
        
        const response = await fetch(NOTES_API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
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

export function formatDateForAPI(date) {
    if (date instanceof Date) {
        return date.toISOString().split('T')[0];
    }
    
    if (typeof date === 'string') {
        if (/^\d{4}-\d{2}-\d{2}$/.test(date)) {
            return date;
        }
        
        const parsed = new Date(date);
        if (!isNaN(parsed)) {
            return parsed.toISOString().split('T')[0];
        }
    }
    
    return new Date().toISOString().split('T')[0];
}

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
