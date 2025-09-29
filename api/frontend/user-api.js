// JavaScript API wrapper for user-related operations
// Example: fetchUsers, addUser, updateUser, deleteUser

const PATH = '/api/backend/user-api.php';
    
export async function fetchUsers() {
    // Usa percorso assoluto per evitare problemi di fetch
    const response = await fetch(PATH);
    const text = await response.text();
    console.log('Response text:', text);
    try {
        return JSON.parse(text);
    } catch (e) {
        alert('Errore caricamento utenti: ' + text);
        console.error('Errore parsing JSON utenti:', text, e);
        return { success: false, users: [], error: 'Invalid JSON' };
    }
}

export async function fetchUserDetails(userId) {
    const response = await fetch(`${PATH}?id=${userId}`);
    const text = await response.text();
    console.log('User details response:', text);
    try {
        return JSON.parse(text);
    } catch (e) {
        alert('Errore caricamento dettagli utente: ' + text);
        console.error('Errore parsing JSON user details:', text, e);
        return { success: false, user: null, error: 'Invalid JSON' };
    }
}

export async function saveAllUsers(usersData) {
    const response = await fetch(PATH, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ users: usersData })
    });
    const text = await response.text();
    console.log('Save all users response:', text);
    try {
        return JSON.parse(text);
    } catch (e) {
        alert('Errore salvataggio utenti: ' + text);
        console.error('Errore parsing JSON saveAllUsers:', text, e);
        return { success: false, error: 'Invalid JSON' };
    }
}

// Add more user API methods as needed
