/**
 * Database Manager - Gestione operazioni database di alto livello
 * Utilizza ApiClient per operazioni specifiche del business logic
 */

import { ApiClient } from './api-client.js';

class DatabaseManager {
    constructor() {
        this.api = window.apiClient || new ApiClient();
        this.cache = new Map();
        this.cacheTTL = 5 * 60 * 1000; // 5 minuti
    }

    /**
     * Cache management
     */
    _getCacheKey(method, params = {}) {
        return `${method}:${JSON.stringify(params)}`;
    }

    _setCache(key, data) {
        this.cache.set(key, {
            data,
            timestamp: Date.now()
        });
    }

    _getCache(key) {
        const cached = this.cache.get(key);
        if (!cached) return null;

        const isExpired = Date.now() - cached.timestamp > this.cacheTTL;
        if (isExpired) {
            this.cache.delete(key);
            return null;
        }

        return cached.data;
    }

    _clearCache(pattern = null) {
        if (pattern) {
            for (const key of this.cache.keys()) {
                if (key.includes(pattern)) {
                    this.cache.delete(key);
                }
            }
        } else {
            this.cache.clear();
        }
    }

    // ========== USER OPERATIONS ==========

    /**
     * Gestione completa utenti con validazione
     */
    async getAllUsers(useCache = true) {
        const cacheKey = this._getCacheKey('users');
        
        if (useCache) {
            const cached = this._getCache(cacheKey);
            if (cached) return cached;
        }

        try {
            const response = await this.api.getUsers();
            if (response.success) {
                this._setCache(cacheKey, response.data);
                return response.data;
            }
            throw new Error(response.message || 'Errore nel recupero utenti');
        } catch (error) {
            throw new Error(ApiUtils.handleError(error, 'Impossibile caricare gli utenti'));
        }
    }

    /**
     * Cerca utente con validazione e gestione errori
     */
    async findUser(identifier) {
        try {
            let response;
            
            // Determina se è un ID numerico o username
            if (/^\d+$/.test(identifier)) {
                response = await this.api.getUserById(parseInt(identifier));
            } else {
                response = await this.api.getUserByUsername(identifier);
            }

            if (response.success) {
                return response.data;
            }
            
            return null; // Utente non trovato
        } catch (error) {
            if (error.status === 404) {
                return null;
            }
            throw new Error(ApiUtils.handleError(error, 'Errore nella ricerca utente'));
        }
    }

    /**
     * Crea utente con validazione completa
     */
    async createUser(userData) {
        // Validazione dati
        const errors = this._validateUserData(userData);
        if (errors.length > 0) {
            throw new Error('Dati non validi: ' + errors.join(', '));
        }

        try {
            const response = await this.api.createUser(userData);
            if (response.success) {
                this._clearCache('users'); // Invalida cache utenti
                return response.data;
            }
            throw new Error(response.message || 'Errore nella creazione utente');
        } catch (error) {
            throw new Error(ApiUtils.handleError(error, 'Impossibile creare l\'utente'));
        }
    }

    /**
     * Aggiorna utente esistente
     */
    async updateUser(id, userData) {
        const errors = this._validateUserData(userData, false); // false = non richiede tutti i campi
        if (errors.length > 0) {
            throw new Error('Dati non validi: ' + errors.join(', '));
        }

        try {
            const response = await this.api.updateUser(id, userData);
            if (response.success) {
                this._clearCache('users');
                return response.data;
            }
            throw new Error(response.message || 'Errore nell\'aggiornamento utente');
        } catch (error) {
            throw new Error(ApiUtils.handleError(error, 'Impossibile aggiornare l\'utente'));
        }
    }

    /**
     * Elimina utente con conferma
     */
    async deleteUser(id, confirm = false) {
        if (!confirm) {
            throw new Error('Eliminazione non confermata');
        }

        try {
            const response = await this.api.deleteUser(id);
            if (response.success) {
                this._clearCache('users');
                return true;
            }
            throw new Error(response.message || 'Errore nell\'eliminazione utente');
        } catch (error) {
            throw new Error(ApiUtils.handleError(error, 'Impossibile eliminare l\'utente'));
        }
    }

    // ========== SERVICE OPERATIONS ==========

    /**
     * Gestione servizi con filtri avanzati
     */
    async getServices(filters = {}, useCache = true) {
        const cacheKey = this._getCacheKey('services', filters);
        
        if (useCache && Object.keys(filters).length === 0) {
            const cached = this._getCache(cacheKey);
            if (cached) return cached;
        }

        try {
            const response = await this.api.getServices(filters);
            if (response.success) {
                if (Object.keys(filters).length === 0) {
                    this._setCache(cacheKey, response.data);
                }
                return response.data;
            }
            throw new Error(response.message || 'Errore nel recupero servizi');
        } catch (error) {
            throw new Error(ApiUtils.handleError(error, 'Impossibile caricare i servizi'));
        }
    }

    /**
     * Cerca servizi con logica avanzata
     */
    async searchServices(searchTerm, filters = {}) {
        if (!searchTerm || searchTerm.trim().length < 2) {
            throw new Error('Il termine di ricerca deve contenere almeno 2 caratteri');
        }

        try {
            const response = await this.api.searchServices(searchTerm.trim());
            if (response.success) {
                let results = response.data;

                // Applica filtri aggiuntivi
                if (filters.minPrice) {
                    results = results.filter(service => service.price >= filters.minPrice);
                }
                if (filters.maxPrice) {
                    results = results.filter(service => service.price <= filters.maxPrice);
                }
                if (filters.activeOnly) {
                    results = results.filter(service => service.is_active);
                }

                return results;
            }
            throw new Error(response.message || 'Errore nella ricerca servizi');
        } catch (error) {
            throw new Error(ApiUtils.handleError(error, 'Impossibile cercare i servizi'));
        }
    }

    /**
     * Crea servizio con validazione
     */
    async createService(serviceData) {
        const errors = this._validateServiceData(serviceData);
        if (errors.length > 0) {
            throw new Error('Dati non validi: ' + errors.join(', '));
        }

        try {
            const response = await this.api.createService(serviceData);
            if (response.success) {
                this._clearCache('services');
                return response.data;
            }
            throw new Error(response.message || 'Errore nella creazione servizio');
        } catch (error) {
            throw new Error(ApiUtils.handleError(error, 'Impossibile creare il servizio'));
        }
    }

    /**
     * Ottieni statistiche servizi
     */
    async getServiceStats() {
        const cacheKey = this._getCacheKey('service-stats');
        const cached = this._getCache(cacheKey);
        if (cached) return cached;

        try {
            const response = await this.api.getServicesStats();
            if (response.success) {
                this._setCache(cacheKey, response.data);
                return response.data;
            }
            throw new Error(response.message || 'Errore nel recupero statistiche');
        } catch (error) {
            throw new Error(ApiUtils.handleError(error, 'Impossibile caricare le statistiche'));
        }
    }

    // ========== SCHEDULE OPERATIONS ==========

    /**
     * Gestione configurazione orari
     */
    async getScheduleConfiguration() {
        const cacheKey = this._getCacheKey('schedule-config');
        const cached = this._getCache(cacheKey);
        if (cached) return cached;

        try {
            const response = await this.api.getScheduleConfig();
            if (response.success) {
                this._setCache(cacheKey, response.data);
                return response.data;
            }
            throw new Error(response.message || 'Errore nel recupero configurazione');
        } catch (error) {
            throw new Error(ApiUtils.handleError(error, 'Impossibile caricare la configurazione orari'));
        }
    }

    /**
     * Aggiorna configurazione orari con validazione
     */
    async updateScheduleConfiguration(configData) {
        const errors = this._validateScheduleConfig(configData);
        if (errors.length > 0) {
            throw new Error('Configurazione non valida: ' + errors.join(', '));
        }

        try {
            const response = await this.api.updateScheduleConfig(configData);
            if (response.success) {
                this._clearCache('schedule');
                return response.data;
            }
            throw new Error(response.message || 'Errore nell\'aggiornamento configurazione');
        } catch (error) {
            throw new Error(ApiUtils.handleError(error, 'Impossibile aggiornare la configurazione'));
        }
    }

    // ========== VALIDATION METHODS ==========

    /**
     * Valida dati utente
     */
    _validateUserData(userData, requireAll = true) {
        const errors = [];

        if (requireAll && !userData.username) {
            errors.push('Username obbligatorio');
        }
        if (userData.username && (userData.username.length < 3 || userData.username.length > 50)) {
            errors.push('Username deve essere tra 3 e 50 caratteri');
        }

        if (requireAll && !userData.password) {
            errors.push('Password obbligatoria');
        }
        if (userData.password && userData.password.length < 6) {
            errors.push('Password deve essere di almeno 6 caratteri');
        }

        if (userData.color && !/^#[0-9A-F]{6}$/i.test(userData.color)) {
            errors.push('Colore deve essere in formato esadecimale (#RRGGBB)');
        }

        return errors;
    }

    /**
     * Valida dati servizio
     */
    _validateServiceData(serviceData) {
        const errors = [];

        if (!serviceData.name_service) {
            errors.push('Nome servizio obbligatorio');
        }
        if (serviceData.name_service && serviceData.name_service.length > 100) {
            errors.push('Nome servizio troppo lungo (max 100 caratteri)');
        }

        if (serviceData.price && (serviceData.price < 0 || serviceData.price > 10000)) {
            errors.push('Prezzo deve essere tra 0 e 10000');
        }

        if (serviceData.duration_minutes && (serviceData.duration_minutes < 5 || serviceData.duration_minutes > 480)) {
            errors.push('Durata deve essere tra 5 e 480 minuti');
        }

        if (serviceData.description_service && serviceData.description_service.length > 500) {
            errors.push('Descrizione troppo lunga (max 500 caratteri)');
        }

        return errors;
    }

    /**
     * Valida configurazione orari
     */
    _validateScheduleConfig(configData) {
        const errors = [];

        if (!ApiUtils.isValidTime(configData.start_time)) {
            errors.push('Orario di apertura non valido');
        }
        if (!ApiUtils.isValidTime(configData.end_time)) {
            errors.push('Orario di chiusura non valido');
        }

        if (configData.start_time && configData.end_time && configData.start_time >= configData.end_time) {
            errors.push('Orario di chiusura deve essere successivo all\'apertura');
        }

        if (!configData.working_days || configData.working_days.length === 0) {
            errors.push('Almeno un giorno lavorativo deve essere selezionato');
        }

        const validDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        const invalidDays = configData.working_days?.filter(day => !validDays.includes(day));
        if (invalidDays && invalidDays.length > 0) {
            errors.push('Giorni lavorativi non validi: ' + invalidDays.join(', '));
        }

        return errors;
    }
}

// Istanza globale del database manager
const dbManager = new DatabaseManager();

// Esporta per uso in moduli ES6
export { DatabaseManager, dbManager };

// Esporta per uso in moduli CommonJS
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { DatabaseManager, dbManager };
}

// Rende disponibile globalmente
window.DatabaseManager = DatabaseManager;
window.dbManager = dbManager;