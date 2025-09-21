// ========== GESTIONE ORARI ==========

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
                        <input type="time" id="opening-time" value="08:00">
                    </div>
                    <div class="time-field">
                        <label>Chiusura</label>
                        <input type="time" id="closing-time" value="18:00">
                    </div>
                </div>
            </div>

            <div class="break-section">
                <h3>Pausa Pranzo</h3>
                <div class="break-toggle">
                    <label>
                        <input type="checkbox" id="lunch-break" checked>
                        <span>Abilita pausa pranzo</span>
                    </label>
                </div>
                <div id="break-config" class="time-row">
                    <div class="time-field">
                        <label>Dalle</label>
                        <input type="time" id="break-start" value="12:30">
                    </div>
                    <div class="time-field">
                        <label>Alle</label>
                        <input type="time" id="break-end" value="13:30">
                    </div>
                </div>
            </div>

            <div class="days-section">
                <h3>Giorni Lavorativi</h3>
                <div class="days-list">
                    <label><input type="checkbox" checked> Lunedì</label>
                    <label><input type="checkbox" checked> Martedì</label>
                    <label><input type="checkbox" checked> Mercoledì</label>
                    <label><input type="checkbox" checked> Giovedì</label>
                    <label><input type="checkbox" checked> Venerdì</label>
                    <label><input type="checkbox"> Sabato</label>
                    <label><input type="checkbox"> Domenica</label>
                </div>
            </div>

            <div class="timezone-section">
                <h3>Fuso Orario</h3>
                <select id="fuso-orario">
                    <option value="Europe/Rome" selected>Europa/Roma (UTC+1)</option>
                    <option value="Europe/London">Europa/Londra (UTC+0)</option>
                    <option value="Europe/Paris">Europa/Parigi (UTC+1)</option>
                    <option value="Europe/Berlin">Europa/Berlino (UTC+1)</option>
                    <option value="Europe/Madrid">Europa/Madrid (UTC+1)</option>
                    <option value="Europe/Amsterdam">Europa/Amsterdam (UTC+1)</option>
                    <option value="Europe/Vienna">Europa/Vienna (UTC+1)</option>
                    <option value="Europe/Zurich">Europa/Zurigo (UTC+1)</option>
                    <option value="Europe/Brussels">Europa/Bruxelles (UTC+1)</option>
                    <option value="Europe/Stockholm">Europa/Stoccolma (UTC+1)</option>
                    <option value="Europe/Oslo">Europa/Oslo (UTC+1)</option>
                    <option value="Europe/Copenhagen">Europa/Copenaghen (UTC+1)</option>
                    <option value="Europe/Helsinki">Europa/Helsinki (UTC+2)</option>
                    <option value="Europe/Athens">Europa/Atene (UTC+2)</option>
                    <option value="Europe/Warsaw">Europa/Varsavia (UTC+1)</option>
                    <option value="Europe/Prague">Europa/Praga (UTC+1)</option>
                    <option value="Europe/Budapest">Europa/Budapest (UTC+1)</option>
                    <option value="Europe/Bucharest">Europa/Bucarest (UTC+2)</option>
                    <option value="Europe/Sofia">Europa/Sofia (UTC+2)</option>
                    <option value="Europe/Kiev">Europa/Kiev (UTC+2)</option>
                    <option value="Europe/Moscow">Europa/Mosca (UTC+3)</option>
                    <option value="Europe/Istanbul">Europa/Istanbul (UTC+3)</option>
                    <option value="Europe/Dublin">Europa/Dublino (UTC+0)</option>
                    <option value="Europe/Lisbon">Europa/Lisbona (UTC+0)</option>
                    <option value="America/New_York">America/New York (UTC-5)</option>
                    <option value="America/Los_Angeles">America/Los Angeles (UTC-8)</option>
                    <option value="America/Chicago">America/Chicago (UTC-6)</option>
                    <option value="America/Denver">America/Denver (UTC-7)</option>
                    <option value="America/Phoenix">America/Phoenix (UTC-7)</option>
                    <option value="America/Toronto">America/Toronto (UTC-5)</option>
                    <option value="America/Vancouver">America/Vancouver (UTC-8)</option>
                    <option value="America/Mexico_City">America/Città del Messico (UTC-6)</option>
                    <option value="America/Sao_Paulo">America/San Paolo (UTC-3)</option>
                    <option value="America/Buenos_Aires">America/Buenos Aires (UTC-3)</option>
                    <option value="America/Lima">America/Lima (UTC-5)</option>
                    <option value="America/Bogota">America/Bogotà (UTC-5)</option>
                    <option value="America/Santiago">America/Santiago (UTC-4)</option>
                    <option value="Asia/Tokyo">Asia/Tokyo (UTC+9)</option>
                    <option value="Asia/Shanghai">Asia/Shanghai (UTC+8)</option>
                    <option value="Asia/Hong_Kong">Asia/Hong Kong (UTC+8)</option>
                    <option value="Asia/Singapore">Asia/Singapore (UTC+8)</option>
                    <option value="Asia/Seoul">Asia/Seoul (UTC+9)</option>
                    <option value="Asia/Bangkok">Asia/Bangkok (UTC+7)</option>
                    <option value="Asia/Mumbai">Asia/Mumbai (UTC+5:30)</option>
                    <option value="Asia/Dubai">Asia/Dubai (UTC+4)</option>
                    <option value="Asia/Tehran">Asia/Tehran (UTC+3:30)</option>
                    <option value="Asia/Jerusalem">Asia/Gerusalemme (UTC+2)</option>
                    <option value="Asia/Riyadh">Asia/Riyadh (UTC+3)</option>
                    <option value="Asia/Karachi">Asia/Karachi (UTC+5)</option>
                    <option value="Asia/Dhaka">Asia/Dhaka (UTC+6)</option>
                    <option value="Asia/Jakarta">Asia/Jakarta (UTC+7)</option>
                    <option value="Asia/Manila">Asia/Manila (UTC+8)</option>
                    <option value="Australia/Sydney">Australia/Sydney (UTC+10)</option>
                    <option value="Australia/Melbourne">Australia/Melbourne (UTC+10)</option>
                    <option value="Australia/Perth">Australia/Perth (UTC+8)</option>
                    <option value="Australia/Brisbane">Australia/Brisbane (UTC+10)</option>
                    <option value="Pacific/Auckland">Pacifico/Auckland (UTC+12)</option>
                    <option value="Pacific/Honolulu">Pacifico/Honolulu (UTC-10)</option>
                    <option value="Pacific/Fiji">Pacifico/Fiji (UTC+12)</option>
                    <option value="Africa/Cairo">Africa/Il Cairo (UTC+2)</option>
                    <option value="Africa/Lagos">Africa/Lagos (UTC+1)</option>
                    <option value="Africa/Johannesburg">Africa/Johannesburg (UTC+2)</option>
                    <option value="Africa/Casablanca">Africa/Casablanca (UTC+0)</option>
                    <option value="Africa/Nairobi">Africa/Nairobi (UTC+3)</option>
                    <option value="Atlantic/Reykjavik">Atlantico/Reykjavik (UTC+0)</option>
                    <option value="GMT">GMT (UTC+0)</option>
                    <option value="UTC">UTC (UTC+0)</option>
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