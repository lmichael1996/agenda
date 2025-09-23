class WorkspaceController {
    constructor() {
        this.calendar = null;
        this.dateManager = null;
        this.isInitialized = false;
        this.init();
    }

    async init() {
        console.log('Inizializzazione WorkspaceController...');
        
        try {
            await this.loadDependencies();
            await this.initializeCalendar();
            await this.initializeDateManager();
            this.setupEventListeners();
            this.setupResponsiveLayout();
            this.isInitialized = true;
            
            console.log('WorkspaceController inizializzato con successo!');
        } catch (error) {
            console.error('Errore inizializzazione WorkspaceController:', error);
            this.initializeFallback();
        }
    }

    /**
     * Carica le dipendenze necessarie
     */
    async loadDependencies() {
        try {
            // Importa Calendar locale
            const { Calendar } = await import('./calendar.js');
            this.CalendarClass = Calendar;
            console.log('Calendar locale caricato');
            
        } catch (error) {
            console.warn('Errore caricamento Calendar locale, uso fallback:', error);
            this.CalendarClass = null;
        }
    }

    /**
     * Inizializza il calendario principale
     */
    async initializeCalendar() {
        if (this.CalendarClass) {
            console.log('Inizializzazione calendario...');
            this.calendar = new this.CalendarClass('.calendar-grid .day');
            
            // Rendi accessibile globalmente per debug
            window.calendar = this.calendar;
            
            console.log('Calendario inizializzato con successo!');
        } else {
            throw new Error('Classe Calendar non disponibile');
        }
    }

    /**
     * Inizializza il gestore date
     */
    async initializeDateManager() {
        // Usa sempre gestione date semplificata locale
        console.log('Inizializzazione gestore date locale...');
        this.initializeSimpleDateManager();
    }

    /**
     * Gestore date semplificato di fallback
     */
    initializeSimpleDateManager() {
        this.dateManager = {
            getCurrentWeek: () => {
                const today = new Date();
                const startOfWeek = new Date(today);
                startOfWeek.setDate(today.getDate() - today.getDay() + 1);
                return startOfWeek;
            },
            
            formatDate: (date) => {
                return date.toLocaleDateString('it-IT');
            },
            
            navigateWeek: (direction) => {
                console.log(`Navigazione settimana: ${direction}`);
                // Implementazione semplificata
            }
        };
        
        window.dateManager = this.dateManager;
        console.log('Gestore date semplificato inizializzato');
    }

    /**
     * Setup event listeners per l'interfaccia
     */
    setupEventListeners() {
        // Gestione controlli vista
        this.setupViewControls();
        
        // Gestione ricerca
        this.setupSearchControls();
        
        // Gestione note
        this.setupNotesControls();
        
        // Gestione eventi calendario
        this.setupCalendarEvents();
    }

    /**
     * Setup controlli di visualizzazione
     */
    setupViewControls() {
        const viewSelect = document.getElementById('set-view');
        const weekInput = document.getElementById('set-week');
        const dateInput = document.getElementById('set-date');

        if (viewSelect) {
            viewSelect.addEventListener('change', (e) => {
                this.handleViewChange(e.target.value, weekInput, dateInput);
            });
        }

        if (weekInput) {
            weekInput.addEventListener('change', (e) => {
                this.handleWeekChange(e.target.value);
            });
        }

        if (dateInput) {
            dateInput.addEventListener('change', (e) => {
                this.handleDateChange(e.target.value);
            });
        }
    }

    /**
     * Setup controlli di ricerca
     */
    setupSearchControls() {
        const searchInput = document.getElementById('cerca');
        const searchBtn = document.querySelector('input[type="submit"]');

        if (searchInput) {
            // Debounce della ricerca
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.handleClientSearch(e.target.value);
                }, 300);
            });
        }

        if (searchBtn) {
            searchBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const searchTerm = searchInput ? searchInput.value : '';
                this.handleClientSearch(searchTerm);
            });
        }
    }

    /**
     * Setup controlli note
     */
    setupNotesControls() {
        const noteBtn = document.getElementById('note-btn');
        
        if (noteBtn) {
            noteBtn.addEventListener('click', () => {
                this.handleNotesToggle();
            });
        }
    }

    /**
     * Setup eventi calendario personalizzati
     */
    setupCalendarEvents() {
        // Eventi personalizzati per comunicazione tra moduli
        window.addEventListener('calendarSlotSelected', (e) => {
            this.handleSlotSelected(e.detail);
        });

        window.addEventListener('calendarEventCreated', (e) => {
            this.handleEventCreated(e.detail);
        });

        window.addEventListener('calendarEventMoved', (e) => {
            this.handleEventMoved(e.detail);
        });
    }

    /**
     * Gestione cambio vista
     */
    handleViewChange(viewType, weekInput, dateInput) {
        console.log(`Cambio vista: ${viewType}`);
        
        if (viewType === 'week') {
            if (weekInput) weekInput.hidden = false;
            if (dateInput) dateInput.hidden = true;
            this.showWeekView();
        } else if (viewType === 'day') {
            if (weekInput) weekInput.hidden = true;
            if (dateInput) dateInput.hidden = false;
            this.showDayView();
        }

        // Aggiorna calendario se disponibile
        if (this.calendar && this.calendar.setViewType) {
            this.calendar.setViewType(viewType);
        }

        // Trigger evento personalizzato
        this.dispatchCalendarEvent('viewChanged', { viewType });
    }

    /**
     * Gestione cambio settimana
     */
    handleWeekChange(weekValue) {
        console.log(`Cambio settimana: ${weekValue}`);
        
        // Aggiorna solo localmente
        if (this.dateManager && this.dateManager.setWeek) {
            this.dateManager.setWeek(weekValue);
        }

        // Trigger evento locale
        this.dispatchCalendarEvent('weekChanged', { week: weekValue });
        
        // Aggiorna visualizzazione locale
        this.updateWeekDisplay(weekValue);
    }

    /**
     * Gestione cambio data
     */
    handleDateChange(dateValue) {
        console.log(`Cambio data: ${dateValue}`);
        
        // Aggiorna solo localmente
        if (this.dateManager && this.dateManager.setDate) {
            this.dateManager.setDate(dateValue);
        }
        
        // Se siamo in vista giorno, aggiorna la visualizzazione
        const viewSelect = document.getElementById('set-view');
        if (viewSelect && viewSelect.value === 'day') {
            this.showDayView();
        }

        // Trigger evento locale
        this.dispatchCalendarEvent('dateChanged', { date: dateValue });
        
        // Aggiorna visualizzazione locale
        this.updateDateDisplay(dateValue);
    }

    /**
     * Aggiorna visualizzazione settimana
     */
    updateWeekDisplay(weekValue) {
        // Aggiorna solo gli elementi visivi locali
        const weekInput = document.getElementById('set-week');
        if (weekInput && weekInput.value !== weekValue) {
            weekInput.value = weekValue;
        }
    }

    /**
     * Aggiorna visualizzazione data
     */
    updateDateDisplay(dateValue) {
        // Aggiorna solo gli elementi visivi locali
        const dateInput = document.getElementById('set-date');
        if (dateInput && dateInput.value !== dateValue) {
            dateInput.value = dateValue;
        }
    }

    /**
     * Gestione ricerca clienti
     */
    handleClientSearch(searchTerm) {
        if (!searchTerm || searchTerm.length < 2) {
            this.clearSearchResults();
            return;
        }

        console.log(`Ricerca cliente: ${searchTerm}`);
        
        // Trigger evento per moduli esterni
        this.dispatchCalendarEvent('clientSearchRequested', { searchTerm });
        
        // Implementazione ricerca locale semplificata
        this.performLocalSearch(searchTerm);
    }

    /**
     * Esegue ricerca locale negli eventi del calendario
     */
    performLocalSearch(searchTerm) {
        // Ricerca solo negli eventi esistenti del calendario
        if (this.calendar && this.calendar.getAllEvents) {
            const events = this.calendar.getAllEvents();
            const matches = events.filter(event => 
                event.text && event.text.toLowerCase().includes(searchTerm.toLowerCase())
            );
            
            console.log(`Trovati ${matches.length} risultati locali per "${searchTerm}"`);
            this.highlightSearchResults(matches);
        } else {
            // Ricerca semplificata negli elementi DOM
            const eventElements = document.querySelectorAll('.calendar-note');
            const matches = Array.from(eventElements).filter(element => {
                const text = element.textContent || '';
                return text.toLowerCase().includes(searchTerm.toLowerCase());
            });
            
            console.log(`Trovati ${matches.length} elementi che corrispondono a "${searchTerm}"`);
            this.highlightDOMResults(matches);
        }
    }

    /**
     * Evidenzia risultati ricerca negli eventi
     */
    highlightSearchResults(matches) {
        // Rimuovi evidenziazioni precedenti
        this.clearSearchResults();
        
        // Evidenzia nuovi risultati
        matches.forEach(event => {
            if (event.element) {
                event.element.classList.add('search-highlight');
            }
        });
    }

    /**
     * Evidenzia risultati ricerca negli elementi DOM
     */
    highlightDOMResults(matches) {
        // Rimuovi evidenziazioni precedenti
        this.clearSearchResults();
        
        // Evidenzia elementi DOM
        matches.forEach(element => {
            element.classList.add('search-highlight');
        });
    }

    /**
     * Pulisce risultati ricerca
     */
    clearSearchResults() {
        const highlighted = document.querySelectorAll('.search-highlight');
        highlighted.forEach(el => el.classList.remove('search-highlight'));
    }

    /**
     * Gestione toggle note
     */
    handleNotesToggle() {
        console.log('Toggle note richiesto');
        this.dispatchCalendarEvent('notesToggleRequested');
        
        // Implementazione semplificata
        const notesPanel = document.querySelector('.notes-panel');
        if (notesPanel) {
            notesPanel.classList.toggle('visible');
        }
    }

    /**
     * Gestione selezione slot
     */
    handleSlotSelected(detail) {
        console.log('Slot selezionato:', detail);
        
        // Logica per gestire selezione slot
        if (detail.slot) {
            // Evidenzia slot selezionato
            this.clearSelectedSlots();
            detail.slot.classList.add('selected');
        }
    }

    /**
     * Gestione creazione evento
     */
    handleEventCreated(detail) {
        console.log('Evento creato:', detail);
        
        // Aggiorna interfaccia se necessario
        this.updateEventCounters();
    }

    /**
     * Gestione spostamento evento
     */
    handleEventMoved(detail) {
        console.log('Evento spostato:', detail);
        
        // Aggiorna interfaccia se necessario
        this.updateEventCounters();
    }

    /**
     * Setup layout responsive
     */
    setupResponsiveLayout() {
        const handleResize = () => {
            const isMobile = window.innerWidth <= 768;
            const isTablet = window.innerWidth <= 1024;
            
            document.body.classList.toggle('mobile-layout', isMobile);
            document.body.classList.toggle('tablet-layout', isTablet && !isMobile);
            
            // Aggiorna calendario per layout responsive
            if (this.calendar && this.calendar.handleResize) {
                this.calendar.handleResize();
            }
        };

        window.addEventListener('resize', handleResize);
        handleResize(); // Esegui immediatamente
    }

    /**
     * Inizializzazione di fallback
     */
    initializeFallback() {
        console.log('Inizializzazione fallback...');
        
        // Setup base senza dipendenze
        this.setupEventListeners();
        this.setupResponsiveLayout();
        
        // Calendario semplificato
        const calendarSlots = document.querySelectorAll('.calendar-grid .day');
        if (calendarSlots.length > 0) {
            calendarSlots.forEach((slot, index) => {
                slot.addEventListener('click', (e) => {
                    this.handleSlotClick(e, slot);
                });
            });
            
            console.log(`Calendario fallback: ${calendarSlots.length} slot configurati`);
        }
        
        this.isInitialized = true;
    }

    /**
     * Gestione click slot in modalità fallback
     */
    handleSlotClick(event, slot) {
        const date = slot.dataset.date;
        const time = slot.dataset.time;
        
        console.log(`Slot cliccato: ${date} ${time}`);
        
        this.dispatchCalendarEvent('slotSelected', { date, time, slot });
    }

    /**
     * Utility per dispatch eventi personalizzati
     */
    dispatchCalendarEvent(eventType, detail = {}) {
        window.dispatchEvent(new CustomEvent(`calendar${eventType.charAt(0).toUpperCase() + eventType.slice(1)}`, {
            detail: detail
        }));
    }

    /**
     * Metodi di utilità
     */
    
    clearSelectedSlots() {
        const selected = document.querySelectorAll('.day.selected');
        selected.forEach(slot => slot.classList.remove('selected'));
    }

    updateEventCounters() {
        const events = document.querySelectorAll('.calendar-note');
        console.log(`Eventi totali: ${events.length}`);
    }

    /**
     * API pubbliche
     */
    
    getCalendar() {
        return this.calendar;
    }

    getDateManager() {
        return this.dateManager;
    }

    isReady() {
        return this.isInitialized;
    }

    refresh() {
        console.log('Refresh WorkspaceController...');
        
        if (this.calendar && this.calendar.refresh) {
            this.calendar.refresh();
        }
        
        if (this.dateManager && this.dateManager.refresh) {
            this.dateManager.refresh();
        }
    }

    // Metodi di accesso delegati al calendario
    createEvent(text, duration, parentSlot = null) {
        if (this.calendar && this.calendar.createEvent) {
            return this.calendar.createEvent(text, duration, parentSlot);
        }
        return null;
    }

    getAllEvents() {
        if (this.calendar && this.calendar.getAllEvents) {
            return this.calendar.getAllEvents();
        }
        return [];
    }

    /**
     * Mostra vista settimana completa
     */
    showWeekView() {
        console.log('Attivazione vista settimana');
        
        const calendarGrid = document.querySelector('.calendar-grid');
        const headerRow = document.querySelector('.calendar-header-row');
        
        if (calendarGrid) {
            calendarGrid.style.gridTemplateColumns = 'auto repeat(7, 1fr)';
        }
        
        // Mostra tutte le colonne del header
        if (headerRow) {
            const headerDays = headerRow.querySelectorAll('.header-day');
            headerDays.forEach(day => {
                day.style.display = 'block';
            });
        }
        
        // Mostra tutte le celle del calendario
        const allDayCells = document.querySelectorAll('.day');
        allDayCells.forEach(cell => {
            cell.style.display = 'block';
        });
    }

    /**
     * Mostra vista giorno singolo
     */
    showDayView() {
        console.log('Attivazione vista giorno');
        
        const dateInput = document.getElementById('set-date');
        const selectedDate = dateInput ? dateInput.value : null;
        
        if (!selectedDate) {
            console.warn('Nessuna data selezionata per vista giorno');
            return;
        }
        
        const calendarGrid = document.querySelector('.calendar-grid');
        const headerRow = document.querySelector('.calendar-header-row');
        
        // Modifica griglia per una sola colonna + orari
        if (calendarGrid) {
            calendarGrid.style.gridTemplateColumns = 'auto 1fr';
        }
        
        // Nasconde tutti i giorni del header tranne quello selezionato
        if (headerRow) {
            const headerDays = headerRow.querySelectorAll('.header-day');
            headerDays.forEach((day, index) => {
                const dayDate = this.getDateForDayIndex(index, selectedDate);
                day.style.display = this.isSameDate(dayDate, selectedDate) ? 'block' : 'none';
            });
        }
        
        // Nasconde tutte le celle tranne quelle del giorno selezionato
        const allDayCells = document.querySelectorAll('.day');
        allDayCells.forEach(cell => {
            const cellDate = cell.getAttribute('data-date');
            if (cellDate) {
                const displayDate = this.formatDateForComparison(selectedDate);
                cell.style.display = cellDate === displayDate ? 'block' : 'none';
            }
        });
    }

    /**
     * Ottiene la data per l'indice del giorno
     */
    getDateForDayIndex(dayIndex, referenceDate) {
        const date = new Date(referenceDate);
        const startOfWeek = new Date(date);
        const dayOfWeek = date.getDay();
        const mondayOffset = dayOfWeek === 0 ? -6 : 1 - dayOfWeek;
        startOfWeek.setDate(date.getDate() + mondayOffset + dayIndex);
        return startOfWeek;
    }

    /**
     * Verifica se due date sono uguali
     */
    isSameDate(date1, date2) {
        if (typeof date2 === 'string') {
            const compareDate = new Date(date2);
            return date1.toDateString() === compareDate.toDateString();
        }
        return date1.toDateString() === date2.toDateString();
    }

    /**
     * Formatta data per comparazione con data-date
     */
    formatDateForComparison(dateString) {
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}-${month}-${year}`;
    }
}

export { WorkspaceController };