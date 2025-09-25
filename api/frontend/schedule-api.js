/**
 * API Frontend per la gestione degli orari di lavoro
 * Wrapper JavaScript per le chiamate REST all'API schedule
 */

const PATH = '../../api/backend/schedule-api.php';

/**
 * Recupera la configurazione orario corrente (singleton)
 */
export async function fetchSchedule() {
    try {
        const response = await fetch(PATH, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const text = await response.text();
        console.log('Schedule response text:', text);
        
        // Controlla se la risposta HTTP è ok
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${text}`);
        }
        
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Errore parsing JSON orario:', text, e);
            throw new Error('Risposta non valida dal server: ' + text);
        }
    } catch (error) {
        console.error('Errore caricamento orario:', error);
        throw error;
    }
}

/**
 * Salva/aggiorna la configurazione orario
 */
export async function saveSchedule(scheduleData) {
    try {
        // Converti i dati dal formato frontend a quello API
        const apiData = convertToApiFormat(scheduleData);
        
        const response = await fetch(PATH, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(apiData)
        });
        
        const text = await response.text();
        console.log('Save schedule response:', text);
        
        // Controlla se la risposta HTTP è ok
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${text}`);
        }
        
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Errore parsing JSON saveSchedule:', text, e);
            throw new Error('Risposta non valida dal server: ' + text);
        }
    } catch (error) {
        console.error('Errore salvataggio orario:', error);
        throw error;
    }
}

/**
 * Elimina la configurazione orario
 */
export async function deleteSchedule() {
    try {
        const response = await fetch(PATH, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const text = await response.text();
        console.log('Delete schedule response:', text);
        
        // Controlla se la risposta HTTP è ok
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${text}`);
        }
        
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Errore parsing JSON deleteSchedule:', text, e);
            throw new Error('Risposta non valida dal server: ' + text);
        }
    } catch (error) {
        console.error('Errore eliminazione orario:', error);
        throw error;
    }
}

/**
 * Converte i dati dal formato frontend al formato API
 */
export function convertToApiFormat(frontendData) {
    const apiData = {};
    
    // Converti orari da HH:MM a HH:MM:SS
    if (frontendData.startTime) {
        apiData.opening_time = frontendData.startTime + ':00';
    }
    
    if (frontendData.endTime) {
        apiData.closing_time = frontendData.endTime + ':00';
    }
    
    if (frontendData.lunchStartTime) {
        apiData.break_start = frontendData.lunchStartTime + ':00';
    }
    
    if (frontendData.lunchEndTime) {
        apiData.break_end = frontendData.lunchEndTime + ':00';
    }
    
    // Converti boolean
    if (typeof frontendData.lunchBreakEnabled !== 'undefined') {
        apiData.lunch_break_enabled = frontendData.lunchBreakEnabled ? 1 : 0;
    }
    
    // Converti array giorni
    if (frontendData.closureDays) {
        apiData.working_days_array = convertClosureDaysToWorking(frontendData.closureDays);
    }
    
    // Timezone
    if (frontendData.timezone) {
        apiData.timezone = frontendData.timezone;
    }
    
    return apiData;
}

/**
 * Converte i dati dal formato API al formato frontend
 */
export function convertFromApiFormat(apiData) {
    const frontendData = {};
    
    // Converti orari da HH:MM:SS a HH:MM
    if (apiData.opening_time) {
        frontendData.startTime = apiData.opening_time.substring(0, 5);
    }
    
    if (apiData.closing_time) {
        frontendData.endTime = apiData.closing_time.substring(0, 5);
    }
    
    if (apiData.break_start) {
        frontendData.lunchStartTime = apiData.break_start.substring(0, 5);
    }
    
    if (apiData.break_end) {
        frontendData.lunchEndTime = apiData.break_end.substring(0, 5);
    }
    
    // Converti boolean
    frontendData.lunchBreakEnabled = apiData.lunch_break_enabled == 1;
    
    // Converti array giorni lavorativi in giorni di chiusura
    if (apiData.working_days_array) {
        frontendData.closureDays = convertWorkingDaysToClosures(apiData.working_days_array);
    }
    
    // Timezone
    if (apiData.timezone) {
        frontendData.timezone = apiData.timezone;
    }
    
    return frontendData;
}

/**
 * Converte giorni di chiusura in giorni lavorativi
 */
export function convertClosureDaysToWorking(closureDays) {
    const allDays = ['lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato', 'domenica'];
    return allDays.filter(day => !closureDays.includes(day));
}

/**
 * Converte giorni lavorativi in giorni di chiusura
 */
export function convertWorkingDaysToClosures(workingDays) {
    const allDays = ['lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato', 'domenica'];
    return allDays.filter(day => !workingDays.includes(day));
}

/**
 * Valida i dati dell'orario lato client
 */
export function validateScheduleData(data) {
    const errors = [];
    
    // Validazione orari
    if (data.startTime && data.endTime && data.startTime >= data.endTime) {
        errors.push('L\'orario di apertura deve essere precedente a quello di chiusura');
    }
    
    // Validazione pausa pranzo
    if (data.lunchBreakEnabled) {
        if (data.lunchStartTime && data.lunchEndTime && data.lunchStartTime >= data.lunchEndTime) {
            errors.push('L\'inizio pausa deve essere precedente alla fine pausa');
        }
        
        if (data.startTime && data.endTime && data.lunchStartTime && data.lunchEndTime) {
            if (data.lunchStartTime <= data.startTime || data.lunchEndTime >= data.endTime) {
                errors.push('La pausa pranzo deve essere compresa nell\'orario di lavoro');
            }
        }
    }
    
    return errors;
}

/**
 * Utility per formattare gli orari per la visualizzazione
 */
export function formatTimeForDisplay(timeString) {
    if (timeString && timeString.length >= 5) {
        return timeString.substring(0, 5); // HH:MM
    }
    return timeString;
}

/**
 * Utility per ottenere il nome del timezone
 */
export function getTimezoneDisplayName(timezone) {
    const timezoneNames = {
        'Europe/Rome': 'Roma (GMT+1/+2)',
        'Europe/London': 'Londra (GMT+0/+1)',
        'Europe/Paris': 'Parigi (GMT+1/+2)',
        'Europe/Berlin': 'Berlino (GMT+1/+2)',
        'America/New_York': 'New York (GMT-5/-4)',
        'America/Los_Angeles': 'Los Angeles (GMT-8/-7)',
        'Asia/Tokyo': 'Tokyo (GMT+9)',
        'UTC': 'UTC (GMT+0)'
    };
    
    return timezoneNames[timezone] || timezone;
}

// Add more schedule API methods as needed