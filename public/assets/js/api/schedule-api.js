/**
 * API Frontend per la gestione degli orari di lavoro
 */

const PATH = '/api.php/schedule';

export async function fetchSchedule() {
    try {
        const response = await fetch(PATH, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        });
        
        const text = await response.text();
        console.log('Schedule response text:', text);
        
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

export async function saveSchedule(scheduleData) {
    try {
        const apiData = convertToApiFormat(scheduleData);
        
        const response = await fetch(PATH, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(apiData)
        });
        
        const text = await response.text();
        console.log('Save schedule response:', text);
        
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

export function convertToApiFormat(frontendData) {
    const apiData = {};
    
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
    
    if (typeof frontendData.lunchBreakEnabled !== 'undefined') {
        apiData.lunch_break_enabled = frontendData.lunchBreakEnabled ? 1 : 0;
    }
    
    if (frontendData.closureDays) {
        apiData.working_days_array = convertClosureDaysToWorking(frontendData.closureDays);
    }
    
    if (frontendData.timezone) {
        apiData.timezone = frontendData.timezone;
    }
    
    return apiData;
}

export function convertFromApiFormat(apiData) {
    const frontendData = {};
    
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
    
    frontendData.lunchBreakEnabled = apiData.lunch_break_enabled == 1;
    
    if (apiData.working_days_array) {
        frontendData.closureDays = convertWorkingDaysToClosures(apiData.working_days_array);
    }
    
    if (apiData.timezone) {
        frontendData.timezone = apiData.timezone;
    }
    
    return frontendData;
}

export function convertClosureDaysToWorking(closureDays) {
    const allDays = ['lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato', 'domenica'];
    return allDays.filter(day => !closureDays.includes(day));
}

export function convertWorkingDaysToClosures(workingDays) {
    const allDays = ['lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato', 'domenica'];
    return allDays.filter(day => !workingDays.includes(day));
}
