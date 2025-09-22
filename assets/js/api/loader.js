/**
 * Loader per utilities JavaScript
 * Include automaticamente tutti i file di utility necessari
 */

(function() {
    'use strict';

    // Lista dei file di utility da caricare
    const utilityFiles = [
        '/assets/js/api/api-client.js',
        '/assets/js/api/database-manager.js'
    ];

    // Funzione per caricare un script dinamicamente
    function loadScript(src) {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            script.async = false; // Mantiene l'ordine di caricamento
            
            script.onload = () => {
                console.log(`✅ Loaded: ${src}`);
                resolve();
            };
            
            script.onerror = () => {
                console.error(`❌ Failed to load: ${src}`);
                reject(new Error(`Failed to load script: ${src}`));
            };
            
            document.head.appendChild(script);
        });
    }

    // Carica tutti i file di utility in sequenza
    async function loadUtilities() {
        try {
            console.log('🔄 Loading utility scripts...');
            
            for (const file of utilityFiles) {
                await loadScript(file);
            }
            
            console.log('✅ All utility scripts loaded successfully');
            
            // Emette evento personalizzato quando tutto è caricato
            const event = new CustomEvent('utilitiesLoaded', {
                detail: {
                    loadedFiles: utilityFiles,
                    timestamp: new Date().toISOString()
                }
            });
            document.dispatchEvent(event);
            
        } catch (error) {
            console.error('❌ Error loading utility scripts:', error);
            
            // Emette evento di errore
            const errorEvent = new CustomEvent('utilitiesError', {
                detail: {
                    error: error.message,
                    timestamp: new Date().toISOString()
                }
            });
            document.dispatchEvent(errorEvent);
        }
    }

    // Avvia il caricamento quando il DOM è pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadUtilities);
    } else {
        // DOM già pronto
        loadUtilities();
    }

    // Utility globale per ricaricare gli script se necessario
    window.reloadUtilities = loadUtilities;

})();

/**
 * Helper per verificare se le utilities sono caricate
 */
window.checkUtilities = function() {
    const checks = {
        apiClient: typeof window.apiClient !== 'undefined',
        dbManager: typeof window.dbManager !== 'undefined',
        ApiClient: typeof window.ApiClient !== 'undefined',
        DatabaseManager: typeof window.DatabaseManager !== 'undefined',
        ApiUtils: typeof window.ApiUtils !== 'undefined'
    };
    
    const allLoaded = Object.values(checks).every(Boolean);
    
    console.log('🔍 Utilities Status:', checks);
    console.log(allLoaded ? '✅ All utilities loaded' : '⚠️ Some utilities missing');
    
    return {
        allLoaded,
        individual: checks
    };
};

/**
 * Helper per debug delle utilities
 */
window.debugUtilities = function() {
    console.group('🔧 Utilities Debug Info');
    
    if (window.apiClient) {
        console.log('🌐 API Client Base URL:', window.apiClient.baseUrl);
        console.log('🌐 API Client Methods:', Object.getOwnPropertyNames(Object.getPrototypeOf(window.apiClient)));
    }
    
    if (window.dbManager) {
        console.log('💾 Database Manager Cache Size:', window.dbManager.cache.size);
        console.log('💾 Database Manager Methods:', Object.getOwnPropertyNames(Object.getPrototypeOf(window.dbManager)));
    }
    
    if (window.ApiUtils) {
        console.log('🛠️ API Utils Methods:', Object.getOwnPropertyNames(window.ApiUtils));
    }
    
    console.groupEnd();
};