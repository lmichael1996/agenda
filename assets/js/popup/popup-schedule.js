/**
 * Gestione popup configurazione orari
 * @author Michael Leanza
 */

// Import moduli
import { dbManager } from '../api/database-manager.js';
import { apiClient, ApiUtils } from '../api/api-client.js';

// Configurazioni
const DAY_NAMES = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];
const DAY_MAP = {
    'Lunedì': 'monday', 'Martedì': 'tuesday', 'Mercoledì': 'wednesday',
    'Giovedì': 'thursday', 'Venerdì': 'friday', 'Sabato': 'saturday', 'Domenica': 'sunday'
};

const TIMEZONES = [
    { value: "Europe/London", label: "Londra (UTC+0)" },
    { value: "Europe/Rome", label: "Roma (UTC+1)" },
    { value: "Europe/Paris", label: "Parigi (UTC+1)" },
    { value: "Europe/Berlin", label: "Berlino (UTC+1)" },
    { value: "Europe/Madrid", label: "Madrid (UTC+1)" },
    { value: "Europe/Athens", label: "Atene (UTC+2)" },
    { value: "Europe/Moscow", label: "Mosca (UTC+3)" },
    { value: "America/New_York", label: "New York (UTC-5)" },
    { value: "America/Chicago", label: "Chicago (UTC-6)" },
    { value: "America/Los_Angeles", label: "Los Angeles (UTC-8)" },
    { value: "America/Sao_Paulo", label: "San Paolo (UTC-3)" },
    { value: "Asia/Dubai", label: "Dubai (UTC+4)" },
    { value: "Asia/Mumbai", label: "Mumbai (UTC+5:30)" },
    { value: "Asia/Shanghai", label: "Shanghai (UTC+8)" },
    { value: "Asia/Tokyo", label: "Tokyo (UTC+9)" },
    { value: "Australia/Sydney", label: "Sydney (UTC+10)" },
    { value: "UTC", label: "UTC (Universal)" }
];

// Configurazione default
let scheduleConfig = {
    working_days: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
    start_time: '09:00',
    end_time: '18:00',
    lunch_break_start: '12:30',
    lunch_break_end: '13:30'
};

// Funzioni API database
async function getScheduleConfig() {
    try {
        const result = await dbManager.getScheduleConfiguration();
        return { success: true, data: result };
    } catch (error) {
        console.error('Database Error:', error);
        // Fallback configurazione default
        const defaultConfig = {
            working_days: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            start_time: '09:00', end_time: '18:00',
            lunch_break_start: '12:30', lunch_break_end: '13:30',
            timezone: 'Europe/Rome'
        };
        return { success: true, data: defaultConfig };
    }
}

async function updateScheduleConfig(configData) {
    try {
        const result = await dbManager.updateScheduleConfiguration(configData);
        return { success: true, data: result };
    } catch (error) {
        console.error('Database Error:', error);
        // Fallback localStorage
        try {
            const legacyConfig = {
                giorniLavorativi: convertWorkingDaysToLegacy(
                    typeof configData.working_days === 'string' ? 
                    JSON.parse(configData.working_days) : configData.working_days
                ),
                apertura: configData.start_time, chiusura: configData.end_time,
                pausaInizio: configData.lunch_break_start, pausaFine: configData.lunch_break_end,
                fusoOrario: configData.timezone
            };
            localStorage.setItem('orarioConfig', JSON.stringify(legacyConfig));
            return { success: true, data: configData };
        } catch (e) {
            throw new Error('Impossibile salvare la configurazione');
        }
    }
}

async function resetScheduleConfig() {
    try {
        console.log('🔄 Resetting schedule configuration to defaults...');
        const result = await dbManager.resetScheduleConfiguration();
        return { success: true, data: result };
    } catch (error) {
        console.error('❌ Database Error:', error);
        
        // Fallback: reset ai valori di default hardcoded
        console.log('⚠️ Fallback to hardcoded defaults');
        const defaultConfig = {
            working_days: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            start_time: '09:00',
            end_time: '18:00',
            lunch_break_start: '12:30',
            lunch_break_end: '13:30',
            timezone: 'Europe/Rome'
        };
        
        // Rimuovi la configurazione da localStorage
        localStorage.removeItem('orarioConfig');
        
        return { success: true, data: defaultConfig };
    }
}

// Load schedule configuration from database
async function loadScheduleConfig() {
    try {
        const response = await getScheduleConfig();
        
        if (response.success && response.data) {
            // Se riceviamo un array, prendi il primo elemento
            const config = Array.isArray(response.data) ? response.data[0] : response.data;
            
            if (config) {
                // Converti i working_days da stringa a array se necessario
                if (typeof config.working_days === 'string') {
                    try {
                        config.working_days = JSON.parse(config.working_days);
                    } catch (e) {
                        // Se il parsing fallisce, prova a splittare per virgola
                        config.working_days = config.working_days.split(',').map(day => day.trim());
                    }
                }
                
                scheduleConfig = {
                    working_days: config.working_days || ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                    start_time: config.start_time || '09:00',
                    end_time: config.end_time || '18:00',
                    lunch_break_start: config.lunch_break_start || null,
                    lunch_break_end: config.lunch_break_end || null,
                    timezone: config.timezone || 'Europe/Rome'
                };
                
                console.log('✅ Schedule config loaded from database:', scheduleConfig);
                return;
            }
        }
        
        console.log('⚠️ No schedule config found in database, using defaults');
        
    } catch (error) {
        console.error('❌ Failed to load schedule config from database:', error);
        
        // Fallback to localStorage if database fails
        const savedConfig = localStorage.getItem('orarioConfig');
        if (savedConfig) {
            try {
                const config = JSON.parse(savedConfig);
                // Convert old format to new format
                scheduleConfig = convertOldConfig(config);
                console.log('✅ Fallback: loaded from localStorage:', scheduleConfig);
                return;
            } catch (e) {
                console.error('❌ Failed to parse localStorage config:', e);
            }
        }
        
        console.log('⚠️ Using default schedule configuration');
    }
}

// Convert old localStorage format to new API format
function convertOldConfig(oldConfig) {
    const workingDaysList = [];
    const dayNames = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    
    if (oldConfig.giorniLavorativi && Array.isArray(oldConfig.giorniLavorativi)) {
        oldConfig.giorniLavorativi.forEach((isWorking, index) => {
            if (isWorking && dayNames[index]) {
                workingDaysList.push(dayNames[index]);
            }
        });
    }
    
    return {
        working_days: workingDaysList.length > 0 ? workingDaysList : ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
        start_time: oldConfig.apertura || '09:00',
        end_time: oldConfig.chiusura || '18:00',
        lunch_break_start: oldConfig.pausaInizio || '12:30',
        lunch_break_end: oldConfig.pausaFine || '13:30'
    };
}

// Save schedule configuration to database
async function saveScheduleConfig(config) {
    try {
        // Assicurati che working_days sia un array serializzato correttamente
        const configToSave = {
            ...config,
            working_days: Array.isArray(config.working_days) ? 
                JSON.stringify(config.working_days) : config.working_days
        };
        
        const response = await updateScheduleConfig(configToSave);
        
        if (response.success) {
            console.log('✅ Schedule config saved to database successfully:', response);
            scheduleConfig = config; // Aggiorna la configurazione locale
            return true;
        }
        
        throw new Error(response.message || 'Failed to save configuration');
        
    } catch (error) {
        console.error('❌ Failed to save schedule config to database:', error);
        
        // Fallback to localStorage
        try {
            // Converti il formato per localStorage (compatibilità)
            const legacyConfig = {
                giorniLavorativi: convertWorkingDaysToLegacy(config.working_days),
                apertura: config.start_time,
                chiusura: config.end_time,
                pausaInizio: config.lunch_break_start,
                pausaFine: config.lunch_break_end,
                fusoOrario: config.timezone
            };
            
            localStorage.setItem('orarioConfig', JSON.stringify(legacyConfig));
            console.log('✅ Saved to localStorage as fallback');
            scheduleConfig = config; // Aggiorna la configurazione locale
            return true;
        } catch (e) {
            console.error('❌ Failed to save to localStorage:', e);
            return false;
        }
    }
}

// Converti working_days per compatibilità con il formato legacy
function convertWorkingDaysToLegacy(workingDays) {
    const dayNames = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    return dayNames.map(day => workingDays.includes(day));
}

// Generate working days HTML
function generateWorkingDays() {
    return DAY_NAMES.map((day, index) => {
        const dayKey = DAY_MAP[day];
        const isChecked = scheduleConfig.working_days.includes(dayKey);
        return `<label><input type="checkbox" data-day="${dayKey}" ${isChecked ? 'checked' : ''}> ${day}</label>`;
    }).join('');
}

// Generate timezone select HTML
function generateTimezoneSelect() {
    const currentTimezone = scheduleConfig.timezone || 'Europe/Rome';
    return TIMEZONES.map(tz => 
        `<option value="${tz.value}" ${currentTimezone === tz.value ? 'selected' : ''}>${tz.label}</option>`
    ).join('');
}

// Template HTML for schedule popup
function getSchedulePopupContent() {
    const lunchBreakEnabled = scheduleConfig.lunch_break_start && scheduleConfig.lunch_break_end;
    
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
                        <input type="time" id="opening-time" value="${scheduleConfig.start_time}">
                    </div>
                    <div class="time-field">
                        <label>Chiusura</label>
                        <input type="time" id="closing-time" value="${scheduleConfig.end_time}">
                    </div>
                </div>
            </div>

            <div class="break-section">
                <h3>Pausa Pranzo</h3>
                <div class="break-toggle">
                    <label>
                        <input type="checkbox" id="lunch-break" ${lunchBreakEnabled ? 'checked' : ''}>
                        <span>Abilita pausa pranzo</span>
                    </label>
                </div>
                <div id="break-config" class="time-row">
                    <div class="time-field">
                        <label>Dalle</label>
                        <input type="time" id="break-start" value="${scheduleConfig.lunch_break_start}">
                    </div>
                    <div class="time-field">
                        <label>Alle</label>
                        <input type="time" id="break-end" value="${scheduleConfig.lunch_break_end}">
                    </div>
                </div>
            </div>

            <div class="days-section">
                <h3>Giorni Lavorativi</h3>
                <div class="working-days">
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
            <button data-action="resetOrarioConfig" class="reset-btn" style="margin-left: 10px; background-color: #f44336;">
                Reset Default
            </button>
        </div>
    `;
}

// Gestione configurazione orari
window.saveOrarioConfig = async function() {
    try {
        // Raccogli i valori dal form
        const workingDays = [];
        document.querySelectorAll('.working-days input[type="checkbox"]:checked').forEach(checkbox => {
            workingDays.push(checkbox.getAttribute('data-day'));
        });
        
        const config = {
            working_days: workingDays,
            start_time: document.getElementById('opening-time')?.value || '09:00',
            end_time: document.getElementById('closing-time')?.value || '18:00',
            lunch_break_start: document.getElementById('lunch-break')?.checked ? 
                (document.getElementById('break-start')?.value || '12:30') : null,
            lunch_break_end: document.getElementById('lunch-break')?.checked ? 
                (document.getElementById('break-end')?.value || '13:30') : null,
            timezone: document.getElementById('fuso-orario')?.value || 'Europe/Rome'
        };
        
        // Validazione
        if (!validateScheduleConfig(config)) {
            return;
        }
        
        // Salva via API
        const success = await saveScheduleConfig(config);
        
        if (success) {
            scheduleConfig = config;
            ApiUtils.showSuccess('Configurazione salvata con successo!');
            closePopup('popup-schedule');
        } else {
            ApiUtils.showError('Errore nel salvataggio della configurazione');
        }
        
    } catch (error) {
        console.error('Error saving schedule config:', error);
        ApiUtils.showError('Errore nel salvataggio: ' + ApiUtils.handleError(error));
    }
};

// Reset configurazione ai valori di default
window.resetOrarioConfig = async function() {
    if (confirm('Sei sicuro di voler ripristinare la configurazione di default?')) {
        try {
            const response = await resetScheduleConfig();
            
            if (response.success && response.data) {
                // Se riceviamo un array, prendi il primo elemento
                const config = Array.isArray(response.data) ? response.data[0] : response.data;
                
                if (config) {
                    // Converti i working_days da stringa a array se necessario
                    if (typeof config.working_days === 'string') {
                        try {
                            config.working_days = JSON.parse(config.working_days);
                        } catch (e) {
                            config.working_days = config.working_days.split(',').map(day => day.trim());
                        }
                    }
                    
                    scheduleConfig = {
                        working_days: config.working_days || ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                        start_time: config.start_time || '09:00',
                        end_time: config.end_time || '18:00',
                        lunch_break_start: config.lunch_break_start || null,
                        lunch_break_end: config.lunch_break_end || null,
                        timezone: config.timezone || 'Europe/Rome'
                    };
                    
                    console.log('✅ Configuration reset successfully:', scheduleConfig);
                    alert('✅ Configurazione ripristinata ai valori di default!');
                    
                    // Ricarica il popup con i nuovi valori
                    const popup = document.getElementById('popup-schedule');
                    if (popup) {
                        popup.innerHTML = getSchedulePopupContent();
                        setupLunchBreakToggle();
                    }
                } else {
                    throw new Error('No data received from reset database call');
                }
            } else {
                throw new Error('Reset database call failed');
            }
        } catch (error) {
            console.error('❌ Error resetting schedule config:', error);
            
            // Fallback: reset to hardcoded defaults
            scheduleConfig = {
                working_days: ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
                start_time: '09:00',
                end_time: '18:00',
                lunch_break_start: '12:30',
                lunch_break_end: '13:30',
                timezone: 'Europe/Rome'
            };
            
            alert('⚠️ Ripristino ai valori di default locali (errore database: ' + error.message + ')');
            
            // Ricarica il popup con i nuovi valori
            const popup = document.getElementById('popup-schedule');
            if (popup) {
                popup.innerHTML = getSchedulePopupContent();
                setupLunchBreakToggle();
            }
        }
    }
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
async function loadSavedScheduleConfig() {
    await loadScheduleConfig();
    
    try {
        // Carica orari di lavoro
        const openingTime = document.getElementById('opening-time');
        const closingTime = document.getElementById('closing-time');
        if (openingTime) openingTime.value = scheduleConfig.start_time || '09:00';
        if (closingTime) closingTime.value = scheduleConfig.end_time || '18:00';
        
        // Carica configurazione pausa pranzo
        const lunchBreak = document.getElementById('lunch-break');
        const breakStart = document.getElementById('break-start');
        const breakEnd = document.getElementById('break-end');
        const hasLunchBreak = scheduleConfig.lunch_break_start && scheduleConfig.lunch_break_end;
        
        if (lunchBreak) lunchBreak.checked = hasLunchBreak;
        if (breakStart) breakStart.value = scheduleConfig.lunch_break_start || '12:30';
        if (breakEnd) breakEnd.value = scheduleConfig.lunch_break_end || '13:30';
        
        // Carica fuso orario
        const timezone = document.getElementById('fuso-orario');
        if (timezone) timezone.value = scheduleConfig.timezone || 'Europe/Rome';
        
        // Carica giorni lavorativi
        const dayCheckboxes = document.querySelectorAll('.working-days input[type="checkbox"]');
        dayCheckboxes.forEach(checkbox => {
            const dayKey = checkbox.getAttribute('data-day');
            checkbox.checked = scheduleConfig.working_days.includes(dayKey);
        });
        
        // Applica la logica della pausa pranzo
        setupLunchBreakToggle();
        
        console.log('Configurazione orari caricata:', scheduleConfig);
    } catch (error) {
        console.error('Errore nel caricamento della configurazione orari:', error);
    }
}

// Validazione orari
function validateScheduleConfig(config = null) {
    const configToValidate = config || {
        start_time: document.getElementById('opening-time')?.value,
        end_time: document.getElementById('closing-time')?.value,
        lunch_break_start: document.getElementById('break-start')?.value,
        lunch_break_end: document.getElementById('break-end')?.value,
        working_days: Array.from(document.querySelectorAll('.working-days input[type="checkbox"]:checked'))
            .map(cb => cb.getAttribute('data-day'))
    };
    
    // Verifica che l'orario di chiusura sia dopo quello di apertura
    if (configToValidate.start_time && configToValidate.end_time && 
        configToValidate.start_time >= configToValidate.end_time) {
        alert('⚠️ L\'orario di chiusura deve essere successivo a quello di apertura!');
        return false;
    }
    
    // Verifica gli orari della pausa pranzo se abilitata
    const lunchBreakEnabled = document.getElementById('lunch-break')?.checked;
    if (lunchBreakEnabled && configToValidate.lunch_break_start && configToValidate.lunch_break_end) {
        if (configToValidate.lunch_break_start >= configToValidate.lunch_break_end) {
            alert('⚠️ L\'orario di fine pausa deve essere successivo a quello di inizio!');
            return false;
        }
        
        if (configToValidate.start_time && configToValidate.lunch_break_start < configToValidate.start_time) {
            alert('⚠️ La pausa pranzo non può iniziare prima dell\'apertura!');
            return false;
        }
        
        if (configToValidate.end_time && configToValidate.lunch_break_end > configToValidate.end_time) {
            alert('⚠️ La pausa pranzo non può finire dopo la chiusura!');
            return false;
        }
    }
    
    // Verifica che almeno un giorno sia selezionato
    if (!configToValidate.working_days || configToValidate.working_days.length === 0) {
        alert('⚠️ Seleziona almeno un giorno lavorativo!');
        return false;
    }
    
    return true;
}

// Inizializza quando il popup orari viene aperto
document.addEventListener('DOMContentLoaded', function() {
    // Auto-carica la configurazione quando viene aperto il popup
    document.addEventListener('click', function(e) {
        if (e.target.getAttribute('data-action') === 'openPopup' && 
            e.target.getAttribute('data-popup-type') === 'schedule') {
            setTimeout(async () => {
                await loadSavedScheduleConfig();
                setupLunchBreakToggle();
            }, 100);
        }
    });
    
    // Carica la configurazione iniziale
    setTimeout(async () => {
        await loadScheduleConfig();
        console.log('📅 Schedule popup initialized with config:', scheduleConfig);
    }, 500);
});