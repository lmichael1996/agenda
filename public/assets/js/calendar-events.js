/**
 * Calendar Events - Main Controller
 * Gestisce l'inizializzazione e la logica del calendario
 */

// === STATO GLOBALE ===
const state = {
    calendar: null,
    dateManager: null,
    isInitialized: false,
    CalendarClass: null
};

// === INIZIALIZZAZIONE ===
const init = async () => {
    console.log('Inizializzazione calendario...');
    
    try {
        await loadDependencies();
        await initializeCalendar();
        await initializeDateManager();
        setupEventListeners();
        setupResponsiveLayout();
        state.isInitialized = true;
        
        console.log('Calendario inizializzato con successo!');
    } catch (error) {
        console.error('Errore inizializzazione calendario:', error);
        initializeFallback();
    }
};

const loadDependencies = async () => {
    try {
        const { Calendar } = await import('./classes/calendar.js');
        state.CalendarClass = Calendar;
        console.log('Calendar caricato');
    } catch (error) {
        console.warn('Errore caricamento Calendar, uso fallback:', error);
        state.CalendarClass = null;
    }
};

const initializeCalendar = async () => {
    if (state.CalendarClass) {
        state.calendar = new state.CalendarClass('.calendar-grid .day');
        window.calendar = state.calendar;
        console.log('Calendario inizializzato');
        
        // Carica appuntamenti e note dal database
        loadAppointmentsFromDB();
        loadNotesFromDB();
    } else {
        throw new Error('Classe Calendar non disponibile');
    }
};

const initializeDateManager = async () => {
    state.dateManager = {
        getCurrentWeek: () => {
            const today = new Date();
            const startOfWeek = new Date(today);
            startOfWeek.setDate(today.getDate() - today.getDay() + 1);
            return startOfWeek;
        },
        
        formatDate: (date) => date.toLocaleDateString('it-IT'),
        
        navigateWeek: (direction) => console.log(`Navigazione settimana: ${direction}`)
    };
    
    window.dateManager = state.dateManager;
    console.log('Gestore date inizializzato');
};

// === EVENT LISTENERS ===
const setupEventListeners = () => {
    setupViewControls();
    setupSearchControls();
    setupAdvancedSearchControls();
    setupNotesControls();
    setupCalendarEvents();
};

const setupViewControls = () => {
    const viewSelect = document.getElementById('set-view');
    const weekInput = document.getElementById('set-week');
    const dateInput = document.getElementById('set-date');

    if (viewSelect) {
        viewSelect.addEventListener('change', (e) => {
            handleViewChange(e.target.value, weekInput, dateInput);
        });
    }

    if (weekInput) {
        weekInput.addEventListener('change', (e) => handleWeekChange(e.target.value));
    }

    if (dateInput) {
        dateInput.addEventListener('change', (e) => handleDateChange(e.target.value));
    }
};

const setupSearchControls = () => {
    const searchInput = document.getElementById('cerca');
    const searchBtn = document.querySelector('input[type="submit"]');
    const searchFieldSelect = document.getElementById('search-field-select');
    const searchTypeSelect = document.getElementById('search-type-select');

    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => handleClientSearch(), 300);
        });
    }

    if (searchBtn) {
        searchBtn.addEventListener('click', (e) => {
            e.preventDefault();
            handleClientSearch();
        });
    }

    if (searchFieldSelect) {
        searchFieldSelect.addEventListener('change', () => {
            if (searchInput && searchInput.value.trim()) {
                handleClientSearch();
            }
        });
    }

    if (searchTypeSelect) {
        searchTypeSelect.addEventListener('change', () => {
            if (searchInput && searchInput.value.trim()) {
                handleClientSearch();
            }
        });
    }
};

const setupAdvancedSearchControls = () => {
    const advancedSearchBtn = document.getElementById('advanced-search-btn');
    const advancedSearchControls = document.getElementById('advanced-search-controls');
    
    if (advancedSearchBtn && advancedSearchControls) {
        advancedSearchBtn.addEventListener('click', () => {
            const isVisible = advancedSearchControls.style.display !== 'none';
            
            advancedSearchControls.style.display = isVisible ? 'none' : 'flex';
            advancedSearchBtn.classList.toggle('active', !isVisible);
            advancedSearchBtn.title = isVisible ? 'Ricerca Avanzata' : 'Nascondi Ricerca Avanzata';
        });
        
        document.addEventListener('click', (e) => {
            if (!advancedSearchBtn.contains(e.target) && !advancedSearchControls.contains(e.target)) {
                advancedSearchControls.style.display = 'none';
                advancedSearchBtn.classList.remove('active');
                advancedSearchBtn.title = 'Ricerca Avanzata';
            }
        });
    }
};

const setupNotesControls = () => {
    const noteBtn = document.getElementById('note-btn');
    if (noteBtn) {
        noteBtn.addEventListener('click', () => handleNotesToggle());
    }
};

const setupCalendarEvents = () => {
    window.addEventListener('calendarSlotSelected', (e) => handleSlotSelected(e.detail));
    window.addEventListener('calendarEventCreated', (e) => handleEventCreated(e.detail));
    window.addEventListener('calendarEventMoved', (e) => handleEventMoved(e.detail));
};

// === HANDLERS ===
const handleViewChange = (viewType, weekInput, dateInput) => {
    console.log(`Cambio vista: ${viewType}`);
    
    if (viewType === 'week') {
        if (weekInput) weekInput.hidden = false;
        if (dateInput) dateInput.hidden = true;
        document.body.classList.remove('day-view');
        showWeekView();
    } else if (viewType === 'day') {
        if (weekInput) weekInput.hidden = true;
        if (dateInput) dateInput.hidden = false;
        document.body.classList.add('day-view');
        showDayView();
    }

    if (state.calendar && state.calendar.setViewType) {
        state.calendar.setViewType(viewType);
    }

    dispatchCalendarEvent('viewChanged', { viewType });
};

const handleWeekChange = (weekValue) => {
    console.log(`Cambio settimana: ${weekValue}`);
    
    // Carica appuntamenti via AJAX
    loadAppointmentsForWeek(weekValue);
};

const handleDateChange = (dateValue) => {
    console.log(`Cambio data: ${dateValue}`);
    
    if (state.dateManager && state.dateManager.setDate) {
        state.dateManager.setDate(dateValue);
    }
    
    const viewSelect = document.getElementById('set-view');
    if (viewSelect && viewSelect.value === 'day') {
        showDayView();
    }

    dispatchCalendarEvent('dateChanged', { date: dateValue });
    updateDateDisplay(dateValue);
};

const updateWeekDisplay = (weekValue) => {
    const weekInput = document.getElementById('set-week');
    if (weekInput && weekInput.value !== weekValue) {
        weekInput.value = weekValue;
    }
};

const updateDateDisplay = (dateValue) => {
    const dateInput = document.getElementById('set-date');
    if (dateInput && dateInput.value !== dateValue) {
        dateInput.value = dateValue;
    }
};

const handleClientSearch = () => {
    const searchInput = document.getElementById('cerca');
    const searchFieldSelect = document.getElementById('search-field-select');
    const searchTypeSelect = document.getElementById('search-type-select');

    const searchTerm = searchInput ? searchInput.value.trim() : '';
    const searchField = searchFieldSelect ? searchFieldSelect.value : 'name';
    const searchType = searchTypeSelect ? searchTypeSelect.value : 'contains';

    if (!searchTerm || searchTerm.length < 2) {
        clearSearchResults();
        return;
    }

    console.log(`Ricerca: "${searchTerm}" campo: ${searchField} tipo: ${searchType}`);
    
    dispatchCalendarEvent('clientSearchRequested', { searchTerm, searchField, searchType });
    performLocalSearch(searchTerm, searchField, searchType);
};

const performLocalSearch = (searchTerm, searchField = 'name', searchType = 'contains') => {
    if (state.calendar && state.calendar.getAllEvents) {
        const events = state.calendar.getAllEvents();
        const matches = events.filter(event => {
            if (!event.text) return false;
            
            const text = event.text.toLowerCase();
            const term = searchTerm.toLowerCase();
            
            switch (searchType) {
                case 'starts': return text.startsWith(term);
                case 'ends': return text.endsWith(term);
                case 'exact': return text === term;
                case 'contains':
                default: return text.includes(term);
            }
        });
        
        console.log(`Trovati ${matches.length} risultati per "${searchTerm}"`);
        highlightSearchResults(matches);
    } else {
        const eventElements = document.querySelectorAll('.calendar-note');
        const matches = Array.from(eventElements).filter(element => {
            const text = (element.textContent || '').toLowerCase();
            const term = searchTerm.toLowerCase();
            
            switch (searchType) {
                case 'starts': return text.startsWith(term);
                case 'ends': return text.endsWith(term);
                case 'exact': return text === term;
                case 'contains':
                default: return text.includes(term);
            }
        });
        
        console.log(`Trovati ${matches.length} elementi per "${searchTerm}"`);
        highlightDOMResults(matches);
    }
};

const highlightSearchResults = (matches) => {
    clearSearchResults();
    matches.forEach(event => {
        if (event.element) {
            event.element.classList.add('search-highlight');
        }
    });
};

const highlightDOMResults = (matches) => {
    clearSearchResults();
    matches.forEach(element => element.classList.add('search-highlight'));
};

const clearSearchResults = () => {
    const highlighted = document.querySelectorAll('.search-highlight');
    highlighted.forEach(el => el.classList.remove('search-highlight'));
};

const handleNotesToggle = () => {
    console.log('Toggle note');
    dispatchCalendarEvent('notesToggleRequested');
    
    const notesPanel = document.querySelector('.notes-panel');
    if (notesPanel) {
        notesPanel.classList.toggle('visible');
    }
};

const handleSlotSelected = (detail) => {
    console.log('Slot selezionato:', detail);
    
    if (detail.slot) {
        clearSelectedSlots();
        detail.slot.classList.add('selected');
    }
};

const handleEventCreated = (detail) => {
    console.log('Evento creato:', detail);
    updateEventCounters();
};

const handleEventMoved = (detail) => {
    console.log('Evento spostato:', detail);
    updateEventCounters();
};

// === RESPONSIVE ===
const setupResponsiveLayout = () => {
    const handleResize = () => {
        const isMobile = window.innerWidth <= 768;
        const isTablet = window.innerWidth <= 1024;
        
        document.body.classList.toggle('mobile-layout', isMobile);
        document.body.classList.toggle('tablet-layout', isTablet && !isMobile);
        
        if (state.calendar && state.calendar.handleResize) {
            state.calendar.handleResize();
        }
    };

    window.addEventListener('resize', handleResize);
    handleResize();
};

// === FALLBACK ===
const initializeFallback = () => {
    console.log('Inizializzazione fallback...');
    
    setupEventListeners();
    setupResponsiveLayout();
    
    const calendarSlots = document.querySelectorAll('.calendar-grid .day');
    if (calendarSlots.length > 0) {
        calendarSlots.forEach((slot) => {
            slot.addEventListener('click', (e) => handleSlotClick(e, slot));
        });
        
        console.log(`Calendario fallback: ${calendarSlots.length} slot`);
    }
    
    state.isInitialized = true;
};

const handleSlotClick = (event, slot) => {
    const date = slot.dataset.date;
    const time = slot.dataset.time;
    
    console.log(`Slot cliccato: ${date} ${time}`);
    dispatchCalendarEvent('slotSelected', { date, time, slot });
};

// === UTILITY ===
const dispatchCalendarEvent = (eventType, detail = {}) => {
    window.dispatchEvent(new CustomEvent(`calendar${eventType.charAt(0).toUpperCase() + eventType.slice(1)}`, {
        detail: detail
    }));
};

const clearSelectedSlots = () => {
    const selected = document.querySelectorAll('.day.selected');
    selected.forEach(slot => slot.classList.remove('selected'));
};

const updateEventCounters = () => {
    const events = document.querySelectorAll('.calendar-note');
    console.log(`Eventi totali: ${events.length}`);
};

// === VISTE ===
const showWeekView = () => {
    console.log('Vista settimana');
    
    const headerRow = document.querySelector('.calendar-header-row');
    
    if (headerRow) {
        const headerDays = headerRow.querySelectorAll('.header-day');
        headerDays.forEach(day => day.style.display = 'block');
    }
    
    const allDayCells = document.querySelectorAll('.day');
    allDayCells.forEach(cell => cell.style.display = 'block');
};

const showDayView = () => {
    console.log('Vista giorno');
    
    const dateInput = document.getElementById('set-date');
    const selectedDate = dateInput ? dateInput.value : null;
    
    if (!selectedDate) {
        console.warn('Nessuna data selezionata');
        return;
    }
    
    const headerRow = document.querySelector('.calendar-header-row');
    
    if (headerRow) {
        const headerDays = headerRow.querySelectorAll('.header-day');
        headerDays.forEach((day, index) => {
            const dayDate = getDateForDayIndex(index, selectedDate);
            day.style.display = isSameDate(dayDate, selectedDate) ? 'block' : 'none';
        });
    }
    
    const allDayCells = document.querySelectorAll('.day');
    allDayCells.forEach(cell => {
        const cellDate = cell.getAttribute('data-date');
        if (cellDate) {
            const displayDate = formatDateForComparison(selectedDate);
            cell.style.display = cellDate === displayDate ? 'block' : 'none';
        }
    });
};

const getDateForDayIndex = (dayIndex, referenceDate) => {
    const date = new Date(referenceDate);
    const startOfWeek = new Date(date);
    const dayOfWeek = date.getDay();
    const mondayOffset = dayOfWeek === 0 ? -6 : 1 - dayOfWeek;
    startOfWeek.setDate(date.getDate() + mondayOffset + dayIndex);
    return startOfWeek;
};

const isSameDate = (date1, date2) => {
    if (typeof date2 === 'string') {
        const compareDate = new Date(date2);
        return date1.toDateString() === compareDate.toDateString();
    }
    return date1.toDateString() === date2.toDateString();
};

const formatDateForComparison = (dateString) => {
    const date = new Date(dateString);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}-${month}-${year}`;
};

// === API PUBBLICA ===
window.calendarAPI = {
    getCalendar: () => state.calendar,
    getDateManager: () => state.dateManager,
    isReady: () => state.isInitialized,
    refresh: () => {
        console.log('Refresh calendario...');
        if (state.calendar && state.calendar.refresh) {
            state.calendar.refresh();
        }
    },
    createEvent: (text, duration, parentSlot = null) => {
        if (state.calendar && state.calendar.createEvent) {
            return state.calendar.createEvent(text, duration, parentSlot);
        }
        return null;
    },
    getAllEvents: () => {
        if (state.calendar && state.calendar.getAllEvents) {
            return state.calendar.getAllEvents();
        }
        return [];
    }
};

// === GESTIONE APPUNTAMENTI ===
// Pulisce tutti gli eventi dal calendario
const clearAllEvents = () => {
    if (state.calendar && state.calendar.eventManager) {
        state.calendar.eventManager.clearAllEvents();
        console.log('✅ Eventi rimossi dal calendario');
    }
};

// Aggiorna le date nell'header del calendario
const updateCalendarHeader = (weekValue) => {
    // Parse formato settimana: 2026-W02
    const match = weekValue.match(/(\d{4})-W(\d{2})/);
    if (!match) return;
    
    const year = parseInt(match[1]);
    const weekNumber = parseInt(match[2]);
    
    // Calcola il lunedì di quella settimana ISO
    const jan4 = new Date(year, 0, 4); // 4 gennaio è sempre nella settimana 1
    const jan4Day = jan4.getDay() || 7; // domenica = 7
    const mondayWeek1 = new Date(jan4);
    mondayWeek1.setDate(jan4.getDate() - jan4Day + 1);
    
    // Aggiungi le settimane
    const monday = new Date(mondayWeek1);
    monday.setDate(mondayWeek1.getDate() + (weekNumber - 1) * 7);
    
    const dayNames = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];
    
    // Aggiorna ogni colonna dell'header
    for (let i = 0; i < 7; i++) {
        const headerDay = document.getElementById(`header-day-${i}`);
        if (headerDay) {
            const currentDate = new Date(monday);
            currentDate.setDate(monday.getDate() + i);
            
            const day = String(currentDate.getDate()).padStart(2, '0');
            const month = String(currentDate.getMonth() + 1).padStart(2, '0');
            const yearStr = currentDate.getFullYear();
            
            headerDay.innerHTML = `${dayNames[i]}<br><span class="header-date">${day}/${month}/${yearStr}</span>`;
        }
    }
    
    console.log('✅ Header calendario aggiornato');
};

// Aggiorna gli attributi data-date degli slot del calendario
const updateCalendarSlots = (weekValue) => {
    // Parse formato settimana: 2026-W02
    const match = weekValue.match(/(\d{4})-W(\d{2})/);
    if (!match) return;
    
    const year = parseInt(match[1]);
    const weekNumber = parseInt(match[2]);
    
    // Calcola il lunedì di quella settimana ISO
    const jan4 = new Date(year, 0, 4); // 4 gennaio è sempre nella settimana 1
    const jan4Day = jan4.getDay() || 7; // domenica = 7
    const mondayWeek1 = new Date(jan4);
    mondayWeek1.setDate(jan4.getDate() - jan4Day + 1);
    
    // Aggiungi le settimane
    const monday = new Date(mondayWeek1);
    monday.setDate(mondayWeek1.getDate() + (weekNumber - 1) * 7);
    
    // Aggiorna data-date per ogni colonna
    const allSlots = document.querySelectorAll('.calendar-grid .day');
    const slotsPerDay = allSlots.length / 7;
    
    for (let day = 0; day < 7; day++) {
        const currentDate = new Date(monday);
        currentDate.setDate(monday.getDate() + day);
        
        const dayStr = String(currentDate.getDate()).padStart(2, '0');
        const monthStr = String(currentDate.getMonth() + 1).padStart(2, '0');
        const yearStr = currentDate.getFullYear();
        const dateStr = `${dayStr}-${monthStr}-${yearStr}`;
        
        // Aggiorna tutti gli slot di quel giorno
        for (let slot = 0; slot < slotsPerDay; slot++) {
            const slotIndex = slot * 7 + day;
            if (allSlots[slotIndex]) {
                allSlots[slotIndex].setAttribute('data-date', dateStr);
            }
        }
    }
    
    console.log('✅ Slot calendario aggiornati');
};

// Carica appuntamenti per una settimana specifica via AJAX
const loadAppointmentsForWeek = async (weekValue) => {
    console.log(`📅 Caricamento appuntamenti per settimana: ${weekValue}`);
    
    try {
        const response = await fetch(`../api/appointments.php?week=${weekValue}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const appointments = await response.json();
        
        if (appointments.error) {
            console.error('❌ Errore dal server:', appointments.error);
            return;
        }
        
        console.log(`✅ Ricevuti ${appointments.length} appuntamenti dal server`);
        
        // Pulisci eventi esistenti
        clearAllEvents();
        
        // Aggiorna header del calendario
        updateCalendarHeader(weekValue);
        
        // Aggiorna data-date degli slot
        updateCalendarSlots(weekValue);
        
        // Carica nuovi eventi (appuntamenti e note)
        loadAppointmentsFromDB(appointments);
        loadNotesForWeek(weekValue);
        
    } catch (error) {
        console.error('❌ Errore durante il caricamento degli appuntamenti:', error);
    }
};

// === CARICAMENTO DATI DAL DATABASE ===
const loadAppointmentsFromDB = (appointmentsData = null) => {
    console.log('=== CARICAMENTO APPUNTAMENTI DAL DATABASE ===');
    
    // Use parameter or fallback to window.appointmentsData
    const appointments = appointmentsData || window.appointmentsData;
    
    if (!appointments) {
        console.warn('⚠️ Nessun dato appuntamenti disponibile');
        return;
    }
    
    console.log(`✅ Trovati ${appointments.length} appuntamenti nel database`);
    
    if (appointments.length === 0) {
        console.warn('⚠️ Array appuntamenti vuoto');
        return;
    }
    
    appointments.forEach((apt, index) => {
        console.log(`\n--- Appuntamento ${index + 1}/${appointments.length} ---`);
        console.log(`Cliente: ${apt.client_name}, Servizio: ${apt.service_name}`);
        console.log(`Data: ${apt.date}, Ora: ${apt.time}, Durata: ${apt.duration} min`);
        
        // Trova lo slot corretto per data e ora
        const slot = document.querySelector(`.day[data-date="${apt.date}"][data-time="${apt.time}"]`);
        
        if (slot && state.calendar) {
            // Crea l'evento con il nome del cliente e servizio
            const eventText = `${apt.client_name} - ${apt.service_name}`;
            const event = state.calendar.createEvent(eventText, apt.duration, slot);
            
            if (event && event.element) {
                // Aggiungi l'evento allo slot (IMPORTANTE!)
                state.calendar.eventManager.addEventToSlot(event, slot);
                
                // Aggiungi colore dell'operatore
                event.element.style.backgroundColor = apt.user_color;
                event.element.style.borderColor = apt.user_color;
                
                // Aggiungi dati custom all'elemento
                event.element.dataset.appointmentId = apt.id;
                event.element.dataset.clientId = apt.client_id;
                event.element.dataset.serviceId = apt.service_id;
                event.element.dataset.userId = apt.user_id;
                event.element.dataset.price = apt.price;
                
                // Aggiungi tooltip con info complete
                event.element.title = `Cliente: ${apt.client_name}\nServizio: ${apt.service_name}\nOperatore: ${apt.user_name}\nDurata: ${apt.duration} min\nPrezzo: €${apt.price}${apt.note ? '\nNote: ' + apt.note : ''}`;
                
                console.log(`✅ Evento creato con successo`);
            } else {
                console.error(`❌ Impossibile creare evento`);
            }
        } else {
            if (!slot) {
                console.error(`❌ Slot non trovato per data="${apt.date}" time="${apt.time}"`);
            }
            if (!state.calendar) {
                console.error('❌ state.calendar non disponibile');
            }
        }
    });
    
    console.log('\n=== CARICAMENTO COMPLETATO ===');
    console.log(`Eventi totali nel calendario: ${state.calendar ? state.calendar.getAllEvents().length : 0}`);
};

// Carica note per una settimana specifica via AJAX
const loadNotesForWeek = async (weekValue) => {
    console.log(`📝 Caricamento note per settimana: ${weekValue}`);
    
    try {
        const response = await fetch(`../api/notes.php?week=${weekValue}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const notes = await response.json();
        
        if (notes.error) {
            console.error('❌ Errore dal server:', notes.error);
            return;
        }
        
        console.log(`✅ Ricevute ${notes.length} note dal server`);
        
        // Carica le note
        loadNotesFromDB(notes);
        
    } catch (error) {
        console.error('❌ Errore durante il caricamento delle note:', error);
    }
};

// === CARICAMENTO NOTE DAL DATABASE ===
const loadNotesFromDB = (notesData = null) => {
    console.log('=== CARICAMENTO NOTE DAL DATABASE ===');
    
    // Use parameter or fallback to window.notesData
    const notes = notesData || window.notesData;
    
    if (!notes) {
        console.warn('⚠️ Nessun dato note disponibile');
        return;
    }
    
    console.log(`✅ Trovate ${notes.length} note nel database`);
    
    if (notes.length === 0) {
        console.warn('⚠️ Array note vuoto');
        return;
    }
    
    notes.forEach((note, index) => {
        console.log(`\n--- Nota ${index + 1}/${notes.length} ---`);
        console.log(`Titolo: ${note.title || '(senza titolo)'}`);
        console.log(`Data: ${note.date}, Ora: ${note.time}`);
        
        // Trova lo slot corretto per data e ora (già formattate dal backend)
        const slot = document.querySelector(`.day[data-date="${note.date}"][data-time="${note.time}"]`);
        
        if (slot && state.calendar) {
            // Crea l'evento per la nota
            const noteText = note.title || note.content.substring(0, 30);
            const event = state.calendar.createEvent(`📝 ${noteText}`, 30, slot); // 30 minuti di default per le note
            
            if (event && event.element) {
                // Aggiungi l'evento allo slot
                state.calendar.eventManager.addEventToSlot(event, slot);
                
                // Stile distintivo per le note (giallo)
                event.element.style.backgroundColor = '#fff3cd';
                event.element.style.borderColor = '#ffc107';
                event.element.style.color = '#856404';
                event.element.style.fontWeight = 'bold';
                
                // Aggiungi dati custom all'elemento
                event.element.dataset.noteId = note.id;
                event.element.dataset.noteType = 'note';
                event.element.dataset.userId = note.user_id;
                event.element.dataset.forAll = note.for_all;
                
                // Aggiungi tooltip con info complete
                const tooltipText = `NOTA\nTitolo: ${note.title || '(nessun titolo)'}\n${note.content}\n${note.for_all ? 'Per tutti gli utenti' : 'Nota personale'}`;
                event.element.title = tooltipText;
                
                console.log(`✅ Nota creata con successo`);
            } else {
                console.error(`❌ Impossibile creare nota`);
            }
        } else {
            if (!slot) {
                console.error(`❌ Slot non trovato per data="${note.date}" time="${note.time}"`);
            }
            if (!state.calendar) {
                console.error('❌ state.calendar non disponibile');
            }
        }
    });
    
    console.log('\n=== CARICAMENTO NOTE COMPLETATO ===');
};


// === AVVIO ===
document.addEventListener('DOMContentLoaded', init);