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
    
    if (state.dateManager && state.dateManager.setWeek) {
        state.dateManager.setWeek(weekValue);
    }

    dispatchCalendarEvent('weekChanged', { week: weekValue });
    updateWeekDisplay(weekValue);
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

// === AVVIO ===
document.addEventListener('DOMContentLoaded', init);