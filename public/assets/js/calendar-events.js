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
    
    // Listener per doppio click su appuntamenti
    document.addEventListener('dblclick', (e) => {
        const appointmentElement = e.target.closest('.calendar-note');
        if (appointmentElement) {
            // Non aprire se si clicca sul resize button
            if (e.target.closest('.resize-btn')) {
                return;
            }
            
            const superAppointmentId = appointmentElement.getAttribute('data-super-appointment-id');
            if (superAppointmentId) {
                openEditAppointmentPopup(superAppointmentId);
            }
        }
    });
    
    // Listener per click su slot vuoti del calendario
    document.addEventListener('click', (e) => {
        const slot = e.target.closest('.day');
        if (slot) {
            // Non gestire se c'è un evento (gestito dal doppio click)
            if (e.target.closest('.calendar-note')) {
                return;
            }
            handleSlotClick(e, slot);
        }
    });
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
    console.log(`🔄 === CAMBIO SETTIMANA === `);
    console.log(`📅 Nuova settimana selezionata: ${weekValue}`);
    
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

const openEditAppointmentPopup = (superAppointmentId) => {
    const width = 1300;
    const height = 700;
    const left = (screen.width - width) / 2;
    const top = (screen.height - height) / 2;
    
    const url = `/public/views/popup/appointment.php?superAppointmentId=${superAppointmentId}`;
    window.open(
        url,
        'EditAppointment',
        `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`
    );
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
    console.log('=== handleSlotClick chiamato ===');
    console.log('Event:', event);
    console.log('Slot:', slot);
    
    // Se lo slot contiene già un evento, non fare nulla (gestito dal doppio click sull'evento)
    const hasEvent = slot.querySelector('.calendar-note');
    console.log('Slot ha evento:', hasEvent);
    
    if (hasEvent) {
        console.log('Slot contiene evento, ignoro click');
        return;
    }
    
    const date = slot.dataset.date;
    const time = slot.dataset.time;
    
    console.log(`Slot vuoto cliccato: ${date} ${time}`);
    console.log('Apertura popup clienti...');
    
    // Apri popup clienti con data e ora come parametri GET
    const popupUrl = `/public/views/popup/clients.php?date=${encodeURIComponent(date)}&time=${encodeURIComponent(time)}`;
    const popup = window.open(popupUrl, 'ClientsPopup', 'width=1000,height=700,scrollbars=yes,resizable=yes');
    console.log('Popup aperto:', popup);
    console.log('URL:', popupUrl);
    
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
    createEvent: (text, duration, parentSlot = null, superAppointmentId = null) => {
        if (state.calendar && state.calendar.createEvent) {
            return state.calendar.createEvent(text, duration, parentSlot, superAppointmentId);
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
// Filtra appuntamenti per settimana
const filterAppointmentsByWeek = (appointments, weekValue) => {
    // Parse formato settimana: 2026-W02
    const match = weekValue.match(/(\d{4})-W(\d{2})/);
    if (!match) return appointments;
    
    const year = parseInt(match[1]);
    const weekNumber = parseInt(match[2]);
    
    // Calcola il lunedì di quella settimana ISO
    const jan4 = new Date(year, 0, 4);
    const jan4Day = jan4.getDay() || 7;
    const mondayWeek1 = new Date(jan4);
    mondayWeek1.setDate(jan4.getDate() - jan4Day + 1);
    
    const monday = new Date(mondayWeek1);
    monday.setDate(mondayWeek1.getDate() + (weekNumber - 1) * 7);
    
    // Calcola la domenica
    const sunday = new Date(monday);
    sunday.setDate(monday.getDate() + 6);
    
    // Formatta date per confronto
    const formatDate = (date) => {
        const d = String(date.getDate()).padStart(2, '0');
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const y = date.getFullYear();
        return `${d}-${m}-${y}`;
    };
    
    const mondayStr = formatDate(monday);
    const sundayStr = formatDate(sunday);
    
    console.log(`Filtraggio appuntamenti da ${mondayStr} a ${sundayStr}`);
    
    return appointments.filter(apt => {
        if (!apt.date) return false;
        
        // Converti data appuntamento in oggetto Date per confronto
        const [day, month, year] = apt.date.split('-').map(Number);
        const aptDate = new Date(year, month - 1, day);
        
        return aptDate >= monday && aptDate <= sunday;
    });
};

// Pulisce tutti gli eventi dal calendario
const clearAllEvents = () => {
    console.log('🧹 Pulizia eventi esistenti...');
    
    // Rimuovi tutti gli elementi .calendar-note dal DOM
    const allEvents = document.querySelectorAll('.calendar-note');
    allEvents.forEach(event => {
        event.remove();
    });
    console.log(`✅ Rimossi ${allEvents.length} eventi dal DOM`);
    
    // Pulisci anche l'event manager se disponibile
    if (state.calendar && state.calendar.eventManager) {
        state.calendar.eventManager.clearAllEvents();
        console.log('✅ Event manager pulito');
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
    
    console.log(`   📅 Lunedì settimana ${weekNumber}: ${monday.toLocaleDateString('it-IT')}`);
    
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
    
    console.log('   ✅ Header aggiornato');
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
    
    console.log('   ✅ Slot aggiornati');
};

// Carica appuntamenti per una settimana specifica via AJAX
const loadAppointmentsForWeek = async (weekValue) => {
    console.log(`\n📅 === CARICAMENTO SETTIMANA ${weekValue} ===`);
    
    try {
        // Usa l'API corretta
        const response = await fetch(`/src/Api/api.php?endpoint=schedule`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (!data.success) {
            console.error('❌ Errore dal server:', data.error);
            return;
        }
        
        const appointments = data.data || [];
        
        console.log(`✅ Ricevuti ${appointments.length} appuntamenti totali dal server`);
        
        // Filtra appuntamenti per la settimana selezionata
        const filteredAppointments = filterAppointmentsByWeek(appointments, weekValue);
        console.log(`✅ Filtrati ${filteredAppointments.length} appuntamenti per questa settimana`);
        
        // 1. Pulisci eventi esistenti
        console.log('\n🧹 Fase 1: Pulizia eventi...');
        clearAllEvents();
        
        // 2. Aggiorna header del calendario
        console.log('\n📋 Fase 2: Aggiornamento header...');
        updateCalendarHeader(weekValue);
        
        // 3. Aggiorna data-date degli slot
        console.log('\n🔄 Fase 3: Aggiornamento slot...');
        updateCalendarSlots(weekValue);
        
        // 4. Carica nuovi eventi (appuntamenti e note)
        console.log('\n📦 Fase 4: Caricamento eventi...');
        loadAppointmentsFromDB(filteredAppointments);
        loadNotesForWeek(weekValue);
        
        console.log(`\n✅ === SETTIMANA ${weekValue} CARICATA ===\n`);
        
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
    
    let successCount = 0;
    let errorCount = 0;
    
    appointments.forEach((apt) => {
        // Trova lo slot corretto per data e ora
        const selector = `.day[data-date="${apt.date}"][data-time="${apt.time}"]`;
        const slot = document.querySelector(selector);
        
        if (slot && state.calendar) {
            // Crea l'evento con il nome del cliente e servizio
            const eventText = `${apt.client_name} - ${apt.service_name}`;
            const event = state.calendar.createEvent(eventText, apt.duration, slot, apt.super_appointment_id);
            
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
                
                successCount++;
            } else {
                console.warn(`⚠️ Impossibile creare evento per: ${apt.client_name} alle ${apt.time}`);
                errorCount++;
            }
        } else {
            if (!slot) {
                console.warn(`⚠️ Appuntamento fuori orario: ${apt.client_name} - ${apt.date} ${apt.time}`);
                errorCount++;
            }
            if (!state.calendar) {
                console.error('❌ state.calendar non disponibile');
            }
        }
    });
    
    if (errorCount > 0) {
        console.log(`✅ Caricati ${successCount}/${appointments.length} appuntamenti (${errorCount} fuori orario lavorativo)`);
    } else {
        console.log(`✅ Caricati ${successCount} appuntamenti con successo`);
    }
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
    // Use parameter or fallback to window.notesData
    const notes = notesData || window.notesData;
    
    if (!notes) {
        console.warn('⚠️ Nessun dato note disponibile');
        return;
    }
    
    if (notes.length === 0) {
        console.warn('⚠️ Array note vuoto');
        return;
    }
    
    let successCount = 0;
    let errorCount = 0;
    
    notes.forEach((note) => {
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
                
                successCount++;
            } else {
                console.warn(`⚠️ Impossibile creare nota: ${note.title}`);
                errorCount++;
            }
        } else {
            if (!slot) {
                console.warn(`⚠️ Nota fuori orario: ${note.title || 'Senza titolo'} - ${note.date} ${note.time}`);
                errorCount++;
            }
            if (!state.calendar) {
                console.error('❌ state.calendar non disponibile');
            }
        }
    });
    
    if (errorCount > 0) {
        console.log(`✅ Caricate ${successCount}/${notes.length} note (${errorCount} fuori orario lavorativo)`);
    } else {
        console.log(`✅ Caricate ${successCount} note con successo`);
    }
};


// === AVVIO ===
document.addEventListener('DOMContentLoaded', init);