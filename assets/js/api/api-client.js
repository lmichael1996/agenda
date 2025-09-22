/**
 * API Client - Utility per comunicazione con il database
 * Fornisce metodi standardizzati per interagire con le API REST
 */

class ApiClient {
    constructor(baseUrl = '/api/endpoints/') {
        this.baseUrl = baseUrl;
        this.defaultHeaders = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };
    }

    /**
     * Metodo generico per fare richieste HTTP
     */
    async request(url, options = {}) {
        try {
            const config = {
                headers: {
                    ...this.defaultHeaders,
                    ...options.headers
                },
                ...options
            };

            const response = await fetch(`${this.baseUrl}${url}`, config);
            
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new ApiError(
                    errorData.message || `HTTP ${response.status}: ${response.statusText}`,
                    response.status,
                    errorData
                );
            }

            const data = await response.json();
            return data;

        } catch (error) {
            if (error instanceof ApiError) {
                throw error;
            }
            
            console.error('API Request Error:', error);
            throw new ApiError(
                'Errore di connessione. Verifica la tua connessione internet.',
                0,
                { originalError: error.message }
            );
        }
    }

    /**
     * Metodi HTTP standard
     */
    async get(url, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const fullUrl = queryString ? `${url}?${queryString}` : url;
        
        return this.request(fullUrl, {
            method: 'GET'
        });
    }

    async post(url, data = {}) {
        return this.request(url, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    async put(url, data = {}) {
        return this.request(url, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    async delete(url) {
        return this.request(url, {
            method: 'DELETE'
        });
    }

    // ========== USERS API ==========

    /**
     * Ottieni tutti gli utenti
     */
    async getUsers(activeOnly = false) {
        const params = activeOnly ? { active: 'true' } : {};
        return this.get('users.php', params);
    }

    /**
     * Ottieni utente per ID
     */
    async getUserById(id) {
        return this.get(`users.php/${id}`);
    }

    /**
     * Ottieni utente per username
     */
    async getUserByUsername(username) {
        return this.get(`users.php/username/${username}`);
    }

    /**
     * Crea nuovo utente
     */
    async createUser(userData) {
        return this.post('users.php', userData);
    }

    /**
     * Aggiorna utente esistente
     */
    async updateUser(id, userData) {
        return this.put(`users.php/${id}`, userData);
    }

    /**
     * Elimina utente
     */
    async deleteUser(id) {
        return this.delete(`users.php/${id}`);
    }

    // ========== SERVICES API ==========

    /**
     * Ottieni tutti i servizi con filtri opzionali
     */
    async getServices(filters = {}) {
        return this.get('services.php', filters);
    }

    /**
     * Ottieni servizio per ID
     */
    async getServiceById(id) {
        return this.get(`services.php/${id}`);
    }

    /**
     * Cerca servizi per nome
     */
    async searchServices(searchTerm) {
        return this.get('services.php', { search: searchTerm });
    }

    /**
     * Ottieni servizi per range di prezzo
     */
    async getServicesByPriceRange(minPrice, maxPrice) {
        return this.get('services.php', { 
            min_price: minPrice, 
            max_price: maxPrice 
        });
    }

    /**
     * Ottieni servizi per durata
     */
    async getServicesByDuration(minDuration, maxDuration) {
        return this.get('services.php', { 
            min_duration: minDuration, 
            max_duration: maxDuration 
        });
    }

    /**
     * Ottieni statistiche servizi
     */
    async getServicesStats() {
        return this.get('services.php/stats');
    }

    /**
     * Crea nuovo servizio
     */
    async createService(serviceData) {
        return this.post('services.php', serviceData);
    }

    /**
     * Aggiorna servizio esistente
     */
    async updateService(id, serviceData) {
        return this.put(`services.php/${id}`, serviceData);
    }

    /**
     * Elimina servizio
     */
    async deleteService(id) {
        return this.delete(`services.php/${id}`);
    }

    // ========== SCHEDULE API ==========

    /**
     * Ottieni configurazione orari
     */
    async getScheduleConfig() {
        return this.get('schedule.php');
    }

    /**
     * Aggiorna configurazione orari
     */
    async updateScheduleConfig(configData) {
        return this.put('schedule.php', configData);
    }

    /**
     * Reset configurazione ai valori di default
     */
    async resetScheduleConfig() {
        return this.get('schedule.php/reset');
    }

    /**
     * Valida configurazione orari
     */
    async validateScheduleConfig() {
        return this.get('schedule.php/validate');
    }

    /**
     * Elimina configurazione (reset ai default)
     */
    async deleteScheduleConfig() {
        return this.delete('schedule.php');
    }
}

/**
 * Classe per gestire errori API
 */
class ApiError extends Error {
    constructor(message, status = 0, data = {}) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
        this.data = data;
    }

    get isNetworkError() {
        return this.status === 0;
    }

    get isClientError() {
        return this.status >= 400 && this.status < 500;
    }

    get isServerError() {
        return this.status >= 500;
    }
}

/**
 * Utility helper functions
 */
const ApiUtils = {
    /**
     * Gestisce errori API in modo user-friendly
     */
    handleError(error, defaultMessage = 'Si è verificato un errore') {
        if (error instanceof ApiError) {
            switch (error.status) {
                case 400:
                    return 'Dati non validi. Controlla i campi inseriti.';
                case 401:
                    return 'Accesso non autorizzato. Effettua il login.';
                case 403:
                    return 'Non hai i permessi per questa operazione.';
                case 404:
                    return 'Risorsa non trovata.';
                case 409:
                    return 'Conflitto: la risorsa esiste già.';
                case 422:
                    return 'Dati non validi: ' + (error.data.message || 'controlla i campi');
                case 500:
                    return 'Errore del server. Riprova più tardi.';
                default:
                    return error.message || defaultMessage;
            }
        }
        
        return error.message || defaultMessage;
    },

    /**
     * Mostra notifica di successo
     */
    showSuccess(message) {
        // Può essere sostituito con un sistema di notifiche più sofisticato
        alert('✅ ' + message);
    },

    /**
     * Mostra notifica di errore
     */
    showError(message) {
        // Può essere sostituito con un sistema di notifiche più sofisticato
        alert('❌ ' + message);
    },

    /**
     * Valida email
     */
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },

    /**
     * Valida formato tempo (HH:MM)
     */
    isValidTime(time) {
        const timeRegex = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;
        return timeRegex.test(time);
    },

    /**
     * Formatta prezzo
     */
    formatPrice(price) {
        return new Intl.NumberFormat('it-IT', {
            style: 'currency',
            currency: 'EUR'
        }).format(price);
    },

    /**
     * Formatta durata in minuti
     */
    formatDuration(minutes) {
        if (minutes < 60) {
            return `${minutes} min`;
        }
        
        const hours = Math.floor(minutes / 60);
        const remainingMinutes = minutes % 60;
        
        if (remainingMinutes === 0) {
            return `${hours}h`;
        }
        
        return `${hours}h ${remainingMinutes}min`;
    }
};

// Istanza globale del client API
const apiClient = new ApiClient();

// Esporta per uso in moduli
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { ApiClient, ApiError, ApiUtils, apiClient };
}

// Rende disponibile globalmente per uso diretto
window.ApiClient = ApiClient;
window.ApiError = ApiError;
window.ApiUtils = ApiUtils;
window.apiClient = apiClient;