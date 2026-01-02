/**
 * API Frontend per la gestione dei clienti
 * Fornisce solo le funzioni per recuperare dati dal backend
 * La parte grafica è gestita da clients-popup.js
 */

const PATH = '/api.php/clients';

/**
 * Recupera la lista dei clienti con paginazione, ricerca e ordinamento
 */
export async function fetchClients(page = 1, limit = 50, search = '', searchField = 'all', searchType = 'contains', sort = 'last_name_asc') {
    try {
        const params = new URLSearchParams();
        if (page > 1) params.set('page', page);
        if (limit !== 50) params.set('limit', limit);
        if (search.trim()) {
            params.set('search', search.trim());
            params.set('search_type', searchType);
        }
        if (searchField !== 'all') params.set('search_field', searchField);
        if (sort !== 'last_name_asc') params.set('sort', sort);
        
        const url = PATH + (params.toString() ? '?' + params.toString() : '');
        
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const text = await response.text();
        console.log('Clients response text:', text);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${text}`);
        }
        
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Errore parsing JSON clienti:', text, e);
            throw new Error('Risposta non valida dal server: ' + text);
        }
    } catch (error) {
        console.error('Errore caricamento clienti:', error);
        throw error;
    }
}

/**
 * Crea un nuovo cliente
 */
export async function createClient(clientData) {
    try {
        const response = await fetch(PATH, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(clientData)
        });
        
        const text = await response.text();
        console.log('Create client response:', text);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${text}`);
        }
        
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Errore parsing JSON createClient:', text, e);
            throw new Error('Risposta non valida dal server: ' + text);
        }
    } catch (error) {
        console.error('Errore creazione cliente:', error);
        throw error;
    }
}

/**
 * Aggiorna un cliente esistente
 */
export async function updateClient(clientData) {
    try {
        const clientId = clientData.id;
        if (!clientId) {
            throw new Error('ID cliente richiesto per aggiornamento');
        }
        
        const response = await fetch(`${PATH}//${clientId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(clientData)
        });
        
        const text = await response.text();
        console.log('Update client response:', text);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${text}`);
        }
        
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Errore parsing JSON updateClient:', text, e);
            throw new Error('Risposta non valida dal server: ' + text);
        }
    } catch (error) {
        console.error('Errore aggiornamento cliente:', error);
        throw error;
    }
}

/**
 * Recupera i dettagli di un cliente specifico
 */
export async function fetchClientDetails(clientId) {
    try {
        const response = await fetch(`${PATH}//${clientId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const text = await response.text();
        console.log('Client details response:', text);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${text}`);
        }
        
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Errore parsing JSON fetchClientDetails:', text, e);
            throw new Error('Risposta non valida dal server: ' + text);
        }
    } catch (error) {
        console.error('Errore caricamento dettagli cliente:', error);
        throw error;
    }
}

/**
 * Elimina un cliente
 */
export async function deleteClient(clientId) {
    try {
        const response = await fetch(`${PATH}//${clientId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const text = await response.text();
        console.log('Delete client response:', text);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${text}`);
        }
        
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Errore parsing JSON deleteClient:', text, e);
            throw new Error('Risposta non valida dal server: ' + text);
        }
    } catch (error) {
        console.error('Errore eliminazione cliente:', error);
        throw error;
    }
}
