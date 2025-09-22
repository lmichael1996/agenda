/**
 * Esempi di utilizzo del Database Manager
 * Questo file mostra come utilizzare i nuovi metodi per comunicare con il database
 */

// ========== ESEMPI DI UTILIZZO ==========

/**
 * Esempio 1: Gestione Utenti
 */
async function exempiUtenti() {
    try {
        console.log('=== ESEMPI GESTIONE UTENTI ===');
        
        // Ottieni tutti gli utenti
        const utenti = await dbManager.getAllUsers();
        console.log('Tutti gli utenti:', utenti);
        
        // Cerca un utente specifico
        const utente = await dbManager.findUser('mario');
        console.log('Utente trovato:', utente);
        
        // Crea nuovo utente
        const nuovoUtente = await dbManager.createUser({
            username: 'testuser',
            password: 'password123',
            color: '#FF5733',
            is_active: true
        });
        console.log('Nuovo utente creato:', nuovoUtente);
        
        // Aggiorna utente
        const utenteAggiornato = await dbManager.updateUser(1, {
            color: '#00FF00'
        });
        console.log('Utente aggiornato:', utenteAggiornato);
        
    } catch (error) {
        console.error('Errore gestione utenti:', error.message);
        ApiUtils.showError(error.message);
    }
}

/**
 * Esempio 2: Gestione Servizi
 */
async function esempiServizi() {
    try {
        console.log('=== ESEMPI GESTIONE SERVIZI ===');
        
        // Ottieni tutti i servizi
        const servizi = await dbManager.getServices();
        console.log('Tutti i servizi:', servizi);
        
        // Cerca servizi con filtri
        const serviziFiltrati = await dbManager.getServices({
            active: true,
            min_price: 20,
            max_price: 100
        });
        console.log('Servizi filtrati:', serviziFiltrati);
        
        // Cerca servizi per nome
        const serviziCercati = await dbManager.searchServices('taglio');
        console.log('Servizi trovati:', serviziCercati);
        
        // Crea nuovo servizio
        const nuovoServizio = await dbManager.createService({
            name_service: 'Servizio Test',
            price: 45.50,
            duration_minutes: 60,
            description_service: 'Descrizione del servizio test',
            color: '#0066CC',
            is_active: true
        });
        console.log('Nuovo servizio creato:', nuovoServizio);
        
        // Ottieni statistiche
        const statistiche = await dbManager.getServiceStats();
        console.log('Statistiche servizi:', statistiche);
        
    } catch (error) {
        console.error('Errore gestione servizi:', error.message);
        ApiUtils.showError(error.message);
    }
}

/**
 * Esempio 3: Gestione Configurazione Orari
 */
async function esempiOrari() {
    try {
        console.log('=== ESEMPI GESTIONE ORARI ===');
        
        // Ottieni configurazione attuale
        const config = await dbManager.getScheduleConfiguration();
        console.log('Configurazione attuale:', config);
        
        // Aggiorna configurazione
        const nuovaConfig = await dbManager.updateScheduleConfiguration({
            working_days: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            start_time: '08:30',
            end_time: '18:30',
            lunch_break_start: '12:00',
            lunch_break_end: '13:00',
            timezone: 'Europe/Rome'
        });
        console.log('Configurazione aggiornata:', nuovaConfig);
        
        ApiUtils.showSuccess('Configurazione orari aggiornata con successo!');
        
    } catch (error) {
        console.error('Errore gestione orari:', error.message);
        ApiUtils.showError(error.message);
    }
}

/**
 * Esempio 4: Gestione Errori
 */
async function esempiGestioneErrori() {
    try {
        console.log('=== ESEMPI GESTIONE ERRORI ===');
        
        // Tentativo di creare utente con dati non validi
        await dbManager.createUser({
            username: 'u', // troppo corto
            password: '123', // troppo corta
            color: 'red' // formato non valido
        });
        
    } catch (error) {
        console.log('Errore catturato correttamente:', error.message);
        
        // Gestione user-friendly dell'errore
        const messaggioUtente = ApiUtils.handleError(error);
        console.log('Messaggio per utente:', messaggioUtente);
        
        // Mostra errore all'utente
        ApiUtils.showError(messaggioUtente);
    }
}

/**
 * Esempio 5: Utilizzo della Cache
 */
async function esempiCache() {
    console.log('=== ESEMPI UTILIZZO CACHE ===');
    
    // Prima chiamata - va al server
    console.time('Prima chiamata (server)');
    const utenti1 = await dbManager.getAllUsers();
    console.timeEnd('Prima chiamata (server)');
    
    // Seconda chiamata - usa la cache
    console.time('Seconda chiamata (cache)');
    const utenti2 = await dbManager.getAllUsers();
    console.timeEnd('Seconda chiamata (cache)');
    
    console.log('Dati identici:', JSON.stringify(utenti1) === JSON.stringify(utenti2));
    
    // Forza refresh senza cache
    console.time('Terza chiamata (no cache)');
    const utenti3 = await dbManager.getAllUsers(false);
    console.timeEnd('Terza chiamata (no cache)');
}

/**
 * Esempio 6: Operazioni in Batch
 */
async function operazioniBatch() {
    try {
        console.log('=== OPERAZIONI IN BATCH ===');
        
        // Carica dati in parallelo
        const [utenti, servizi, config] = await Promise.all([
            dbManager.getAllUsers(),
            dbManager.getServices(),
            dbManager.getScheduleConfiguration()
        ]);
        
        console.log('Dati caricati:', {
            utenti: utenti.length,
            servizi: servizi.length,
            configOrari: !!config
        });
        
        // Crea dashboard summary
        const dashboard = {
            totalUsers: utenti.length,
            activeUsers: utenti.filter(u => u.is_active).length,
            totalServices: servizi.length,
            activeServices: servizi.filter(s => s.is_active).length,
            avgServicePrice: servizi.reduce((sum, s) => sum + (s.price || 0), 0) / servizi.length,
            workingDays: config.working_days.length
        };
        
        console.log('Dashboard Summary:', dashboard);
        
    } catch (error) {
        console.error('Errore operazioni batch:', error.message);
    }
}

// ========== UTILITY DI TEST ==========

/**
 * Testa tutte le funzionalità
 */
async function testCompleto() {
    console.log('🚀 Avvio test completo del Database Manager...');
    
    // Verifica che le utilities siano caricate
    const status = window.checkUtilities();
    if (!status.allLoaded) {
        console.error('❌ Utilities non completamente caricate');
        return;
    }
    
    try {
        await exempiUtenti();
        await esempiServizi();
        await esempiOrari();
        await esempiGestioneErrori();
        await esempiCache();
        await operazioniBatch();
        
        console.log('✅ Test completo terminato con successo!');
        ApiUtils.showSuccess('Test database completato con successo!');
        
    } catch (error) {
        console.error('❌ Errore durante il test:', error);
        ApiUtils.showError('Errore durante il test: ' + error.message);
    }
}

/**
 * Test rapido per verificare la connettività
 */
async function testConnessione() {
    try {
        console.log('🔍 Test connessione database...');
        
        const config = await dbManager.getScheduleConfiguration();
        console.log('✅ Connessione OK - Config ricevuta:', config);
        
        ApiUtils.showSuccess('Connessione database OK!');
        return true;
        
    } catch (error) {
        console.error('❌ Test connessione fallito:', error);
        ApiUtils.showError('Connessione database fallita: ' + error.message);
        return false;
    }
}

// Rende disponibili le funzioni di test globalmente
window.testDatabase = {
    completo: testCompleto,
    connessione: testConnessione,
    utenti: exempiUtenti,
    servizi: esempiServizi,
    orari: esempiOrari,
    errori: esempiGestioneErrori,
    cache: esempiCache,
    batch: operazioniBatch
};

// Auto-esegue test di connessione quando le utilities sono caricate
document.addEventListener('utilitiesLoaded', () => {
    console.log('🔧 Utilities caricate, eseguo test di connessione...');
    setTimeout(testConnessione, 1000);
});

console.log('📚 Esempi database caricati. Usa window.testDatabase per i test.');
console.log('   - window.testDatabase.connessione() - Test connessione');
console.log('   - window.testDatabase.completo() - Test completo');
console.log('   - window.testDatabase.utenti() - Test gestione utenti');
console.log('   - window.testDatabase.servizi() - Test gestione servizi');