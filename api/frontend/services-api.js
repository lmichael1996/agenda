// JavaScript API wrapper for services-related operations

const PATH = '/api/backend/services-api.php';
    
export async function fetchServices() {
    const response = await fetch(PATH);
    const text = await response.text();
    console.log('Services response text:', text);
    try {
        return JSON.parse(text);
    } catch (e) {
        alert('Errore caricamento servizi: ' + text);
        console.error('Errore parsing JSON servizi:', text, e);
        return { success: false, services: [], error: 'Invalid JSON' };
    }
}

export async function saveAllServices(servicesData) {
    const response = await fetch(PATH, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ services: servicesData })
    });
    const text = await response.text();
    console.log('Save all services response:', text);
    try {
        return JSON.parse(text);
    } catch (e) {
        alert('Errore salvataggio servizi: ' + text);
        console.error('Errore parsing JSON saveAllServices:', text, e);
        return { success: false, error: 'Invalid JSON' };
    }
}

// Add more services API methods as needed