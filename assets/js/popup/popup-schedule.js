// ========== GESTIONE ORARI ==========

// Configurazione orari centralizzata
let scheduleConfig = {
    openingTime: "08:00",
    closingTime: "18:00",
    lunchBreakEnabled: true,
    breakStart: "12:30",
    breakEnd: "13:30",
    workingDays: [true, true, true, true, true, false, false], // Lun-Ven
    timezone: "Europe/Rome"
};

// Genera HTML per il campo orario di apertura/chiusura
function generateTimeFields() {
    return `
        <div class="time-row">
            <div class="time-field">
                <label>Apertura</label>
                <input type="time" id="opening-time" value="${scheduleConfig.openingTime}">
            </div>
            <div class="time-field">
                <label>Chiusura</label>
                <input type="time" id="closing-time" value="${scheduleConfig.closingTime}">
            </div>
        </div>
    `;
}

// Genera HTML per la configurazione pausa pranzo
function generateBreakConfig() {
    return `
        <div class="break-toggle">
            <label>
                <input type="checkbox" id="lunch-break" ${scheduleConfig.lunchBreakEnabled ? 'checked' : ''}>
                <span>Abilita pausa pranzo</span>
            </label>
        </div>
        <div id="break-config" class="time-row">
            <div class="time-field">
                <label>Dalle</label>
                <input type="time" id="break-start" value="${scheduleConfig.breakStart}">
            </div>
            <div class="time-field">
                <label>Alle</label>
                <input type="time" id="break-end" value="${scheduleConfig.breakEnd}">
            </div>
        </div>
    `;
}

// Genera HTML per i giorni lavorativi
function generateWorkingDays() {
    const dayNames = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];
    return dayNames.map((day, index) => 
        `<label><input type="checkbox" ${scheduleConfig.workingDays[index] ? 'checked' : ''}> ${day}</label>`
    ).join('');
}

// Genera HTML per il fuso orario
function generateTimezoneSelect() {
    const timezones = [
        { value: "Europe/Rome", label: "Europa/Roma (UTC+1)" },
        { value: "Europe/London", label: "Europa/Londra (UTC+0)" },
        { value: "Europe/Paris", label: "Europa/Parigi (UTC+1)" },
        { value: "Europe/Berlin", label: "Europa/Berlino (UTC+1)" },
        { value: "Europe/Madrid", label: "Europa/Madrid (UTC+1)" },
        { value: "America/New_York", label: "America/New York (UTC-5)" },
        { value: "America/Los_Angeles", label: "America/Los Angeles (UTC-8)" },
        { value: "Asia/Tokyo", label: "Asia/Tokyo (UTC+9)" }
    ];
    
    return timezones.map(tz => 
        `<option value="${tz.value}" ${scheduleConfig.timezone === tz.value ? 'selected' : ''}>${tz.label}</option>`
    ).join('');
}

// Template HTML per il popup orari
function getSchedulePopupContent() {
    return `
        <div class="calendar-header">
            <h2>Configurazione Orari</h2>
            <p>Imposta i tuoi orari di lavoro</p>
        </div>
        
        <div class="calendar-body">
            <div class="time-section">
                <h3>Orari di Lavoro</h3>
                <div class="time-row">
                    <div class="time-field">
                        <label>Apertura</label>
                        <input type="time" id="opening-time" value="${scheduleConfig.openingTime}">
                    </div>
                    <div class="time-field">
                        <label>Chiusura</label>
                        <input type="time" id="closing-time" value="${scheduleConfig.closingTime}">
                    </div>
                </div>
            </div>

            <div class="break-section">
                <h3>Pausa Pranzo</h3>
                <div class="break-toggle">
                    <label>
                        <input type="checkbox" id="lunch-break" ${scheduleConfig.lunchBreakEnabled ? 'checked' : ''}>
                        <span>Abilita pausa pranzo</span>
                    </label>
                </div>
                <div id="break-config" class="time-row">
                    <div class="time-field">
                        <label>Dalle</label>
                        <input type="time" id="break-start" value="${scheduleConfig.breakStart}">
                    </div>
                    <div class="time-field">
                        <label>Alle</label>
                        <input type="time" id="break-end" value="${scheduleConfig.breakEnd}">
                    </div>
                </div>
            </div>

            <div class="days-section">
                <h3>Giorni Lavorativi</h3>
                            <div class="working-days">
                <h4>Giorni lavorativi</h4>
                ${generateWorkingDays()}
            </div>
            </div>

            <div class="timezone-section">
                <h3>Fuso Orario</h3>
                <select id="fuso-orario">
                    ${generateTimezoneSelect()}
                </select>
            </div>
        </div>
        
        <div class="calendar-footer">
            <button data-action="saveOrarioConfig" class="save-btn">
                Salva Configurazione
            </button>
        </div>
    `;
}

// Gestione configurazione orari
window.saveOrarioConfig = function() {
    const config = {
        apertura: document.getElementById('opening-time')?.value || '08:00',
        chiusura: document.getElementById('closing-time')?.value || '18:00',
        pausaPranzo: document.getElementById('lunch-break')?.checked || false,
        pausaInizio: document.getElementById('break-start')?.value || '12:30',
        pausaFine: document.getElementById('break-end')?.value || '13:30',
        fusoOrario: document.getElementById('fuso-orario')?.value || 'Europe/Rome',
        giorniLavorativi: Array.from(document.querySelectorAll('.days-list input[type="checkbox"]'))
            .map(cb => cb.checked)
    };
    
    localStorage.setItem('orarioConfig', JSON.stringify(config));
    alert('✅ Configurazione salvata!');
    closePopup('popup-schedule');
};

// Gestione pausa pranzo
function setupLunchBreakToggle() {
    const lunchBreakCheckbox = document.getElementById('lunch-break');
    const breakConfig = document.getElementById('break-config');
    
    if (lunchBreakCheckbox && breakConfig) {
        lunchBreakCheckbox.addEventListener('change', function() {
            breakConfig.style.display = this.checked ? 'flex' : 'none';
        });
        
        // Imposta lo stato iniziale
        breakConfig.style.display = lunchBreakCheckbox.checked ? 'flex' : 'none';
    }
}

// Carica configurazione salvata
function loadSavedScheduleConfig() {
    const savedConfig = localStorage.getItem('orarioConfig');
    if (savedConfig) {
        try {
            const config = JSON.parse(savedConfig);
            
            // Carica orari di lavoro
            const openingTime = document.getElementById('opening-time');
            const closingTime = document.getElementById('closing-time');
            if (openingTime) openingTime.value = config.apertura || '08:00';
            if (closingTime) closingTime.value = config.chiusura || '18:00';
            
            // Carica configurazione pausa pranzo
            const lunchBreak = document.getElementById('lunch-break');
            const breakStart = document.getElementById('break-start');
            const breakEnd = document.getElementById('break-end');
            if (lunchBreak) lunchBreak.checked = config.pausaPranzo || false;
            if (breakStart) breakStart.value = config.pausaInizio || '12:30';
            if (breakEnd) breakEnd.value = config.pausaFine || '13:30';
            
            // Carica fuso orario
            const timezone = document.getElementById('fuso-orario');
            if (timezone) timezone.value = config.fusoOrario || 'Europe/Rome';
            
            // Carica giorni lavorativi
            const dayCheckboxes = document.querySelectorAll('.days-list input[type="checkbox"]');
            if (config.giorniLavorativi && dayCheckboxes.length > 0) {
                dayCheckboxes.forEach((checkbox, index) => {
                    if (config.giorniLavorativi[index] !== undefined) {
                        checkbox.checked = config.giorniLavorativi[index];
                    }
                });
            }
            
            // Applica la logica della pausa pranzo
            setupLunchBreakToggle();
            
            console.log('Configurazione orari caricata:', config);
        } catch (error) {
            console.error('Errore nel caricamento della configurazione orari:', error);
        }
    }
}

// Validazione orari
function validateScheduleConfig() {
    const openingTime = document.getElementById('opening-time')?.value;
    const closingTime = document.getElementById('closing-time')?.value;
    const breakStart = document.getElementById('break-start')?.value;
    const breakEnd = document.getElementById('break-end')?.value;
    const lunchBreakEnabled = document.getElementById('lunch-break')?.checked;
    
    // Verifica che l'orario di chiusura sia dopo quello di apertura
    if (openingTime && closingTime && openingTime >= closingTime) {
        alert('⚠️ L\'orario di chiusura deve essere successivo a quello di apertura!');
        return false;
    }
    
    // Verifica gli orari della pausa pranzo se abilitata
    if (lunchBreakEnabled && breakStart && breakEnd) {
        if (breakStart >= breakEnd) {
            alert('⚠️ L\'orario di fine pausa deve essere successivo a quello di inizio!');
            return false;
        }
        
        if (openingTime && breakStart < openingTime) {
            alert('⚠️ La pausa pranzo non può iniziare prima dell\'apertura!');
            return false;
        }
        
        if (closingTime && breakEnd > closingTime) {
            alert('⚠️ La pausa pranzo non può finire dopo la chiusura!');
            return false;
        }
    }
    
    // Verifica che almeno un giorno sia selezionato
    const selectedDays = document.querySelectorAll('.days-list input[type="checkbox"]:checked');
    if (selectedDays.length === 0) {
        alert('⚠️ Seleziona almeno un giorno lavorativo!');
        return false;
    }
    
    return true;
}

// Override della funzione di salvataggio con validazione
const originalSaveOrarioConfig = window.saveOrarioConfig;
window.saveOrarioConfig = function() {
    if (validateScheduleConfig()) {
        originalSaveOrarioConfig();
    }
};

// Inizializza quando il popup orari viene aperto
document.addEventListener('DOMContentLoaded', function() {
    // Auto-carica la configurazione quando viene aperto il popup
    document.addEventListener('click', function(e) {
        if (e.target.getAttribute('data-action') === 'openPopup' && 
            e.target.getAttribute('data-popup-type') === 'schedule') {
            setTimeout(() => {
                loadSavedScheduleConfig();
                setupLunchBreakToggle();
            }, 100);
        }
    });
});