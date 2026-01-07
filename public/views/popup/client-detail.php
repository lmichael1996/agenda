<?php
/**
 * Popup per i dettagli cliente - Finestra separata
 * Stile nota con tabella verticale
 */
require_once '../../../src/Auth/AccessControl.php';

// Il file access-control.php gestisce automaticamente tutti i controlli per i popup
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dettagli Cliente - Agenda</title>
    <link rel="stylesheet" href="../../assets/css/scheme-popup.css">
    <link rel="stylesheet" href="../../assets/css/client-detail.css">
    <link rel="stylesheet" href="../../assets/css/scrollbar.css">
</head>
<body>
    <div class="popup-window-container">
        <div class="window-header">
            <span class="header-title">Dettagli Cliente</span>
        </div>
        
        <div class="calendar-body note-calendar-body">
            <!-- Error container -->
            <div id="error-container">
                <div id="error-message-text">
                    Errore nel caricamento dei dati
                </div>
            </div>
            
            <!-- Client details table -->
            <div id="client-details-container">
                <table class="excel-table note-table">
                    <tbody>
                        <tr>
                            <th class="note-table-th">Nome</th>
                            <td><input type="text" id="edit-first-name" class="cell-input" required></td>
                        </tr>
                        <tr>
                            <th class="note-table-th">Cognome</th>
                            <td><input type="text" id="edit-last-name" class="cell-input" required></td>
                        </tr>
                        <tr>
                            <th class="note-table-th">Telefono</th>
                            <td><input type="tel" id="edit-phone" class="cell-input"></td>
                        </tr>
                        <tr>
                            <th class="note-table-th">Note</th>
                            <td><textarea id="edit-notes" class="cell-textarea" rows="3"></textarea></td>
                        </tr>
                        <tr>
                            <th class="note-table-th">Certificato</th>
                            <td>
                                <input type="checkbox" id="edit-has-certificate" class="cell-input note-checkbox">
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="note-save-container">
                    <button class="save-btn note-save-btn" id="save-client-btn">Salva Utente</button>
                    <button class="save-btn note-save-btn delete-btn" id="delete-client-btn">Elimina Cliente</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Get client ID from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const clientId = urlParams.get('clientId');
        const isNewClient = clientId === '0';
        
        // Update title if creating new client
        if (isNewClient) {
            document.querySelector('.header-title').textContent = 'Nuovo Cliente';
        }
        
        // Global variable to store current client data
        let currentClient = null;
        
        // Fetch client details from API
        async function fetchClientDetails(id) {
            try {
                const response = await fetch(`../../../src/Api/api.php?endpoint=clients&id=${id}`);
                const data = await response.json();
                return data;
            } catch (error) {
                console.error('Errore fetch cliente:', error);
                return { success: false, error: 'Errore di connessione' };
            }
        }
        
        async function updateClient(clientData) {
            try {
                const response = await fetch(`../../../src/Api/api.php?endpoint=clients&id=${clientData.id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(clientData)
                });
                
                const text = await response.text();
                console.log('Response text:', text);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${text}`);
                }
                
                const data = JSON.parse(text);
                return data;
            } catch (error) {
                console.error('Errore update cliente:', error);
                return { success: false, error: error.message || 'Errore di connessione' };
            }
        }
        
        async function loadClientDetails() {
            // If creating new client, just show empty form
            if (isNewClient) {
                document.getElementById('client-details-container').style.display = 'block';
                document.getElementById('error-container').style.display = 'none';
                return;
            }
            
            // Otherwise load existing client data
            if (!clientId) {
                showError('ID cliente non specificato');
                return;
            }
            
            try {
                const response = await fetchClientDetails(clientId);
                
                if (response.success && response.data) {
                    currentClient = response.data;
                    populateForm(response.data);
                } else {
                    showError(response.error || 'Cliente non trovato');
                }
            } catch (error) {
                console.error('Errore caricamento dettagli cliente:', error);
                showError('Errore di connessione durante il caricamento');
            }
        }
        
        function populateForm(client) {
            // Show details container
            document.getElementById('client-details-container').style.display = 'block';
            
            // Populate edit fields
            document.getElementById('edit-first-name').value = client.first_name || '';
            document.getElementById('edit-last-name').value = client.last_name || '';
            document.getElementById('edit-phone').value = client.phone || '';
            document.getElementById('edit-notes').value = client.notes || '';
            document.getElementById('edit-has-certificate').checked = !!client.has_certificate;
            
            // Update window title
            const fullName = [client.first_name, client.last_name].filter(Boolean).join(' ') || 'Cliente';
            document.title = `Dettagli: ${fullName} - Agenda`;
        }
        
        async function saveClientChanges() {
            // Validate
            const firstName = document.getElementById('edit-first-name').value.trim();
            const lastName = document.getElementById('edit-last-name').value.trim();
            
            if (!firstName || !lastName) {
                alert('Nome e cognome sono obbligatori');
                return;
            }
            
            const phone = document.getElementById('edit-phone').value.trim();
            const notes = document.getElementById('edit-notes').value.trim();
            const hasCertificate = document.getElementById('edit-has-certificate').checked ? 1 : 0;
            
            // If creating new client, use POST
            if (isNewClient) {
                const newClient = {
                    first_name: firstName,
                    last_name: lastName,
                    phone: phone,
                    notes: notes,
                    has_certificate: hasCertificate
                };
                
                console.log('Creating new client:', newClient);
                
                try {
                    const response = await fetch('../../../src/Api/api.php?endpoint=clients', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(newClient)
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        alert('Cliente creato con successo!');
                        
                        // Refresh parent window
                        if (window.opener && !window.opener.closed) {
                            try {
                                if (window.opener.clientsUI && typeof window.opener.clientsUI.loadClients === 'function') {
                                    window.opener.clientsUI.loadClients();
                                }
                            } catch (e) {
                                console.log('Could not refresh parent window');
                            }
                        }
                        
                        window.close();
                    } else {
                        alert('Errore durante la creazione: ' + (result.error || 'Errore sconosciuto'));
                    }
                } catch (error) {
                    console.error('Errore creazione cliente:', error);
                    alert('Errore di connessione durante la creazione');
                }
                
                return;
            }
            
            // Otherwise update existing client
            if (!currentClient) {
                alert('Dati cliente non disponibili');
                return;
            }
            
            if (!firstName || !lastName) {
                alert('Nome e cognome sono obbligatori');
                return;
            }
            
            const updatedClient = {
                id: currentClient.id,
                first_name: firstName,
                last_name: lastName,
                phone: document.getElementById('edit-phone').value.trim(),
                notes: document.getElementById('edit-notes').value.trim(),
                has_certificate: document.getElementById('edit-has-certificate').checked ? 1 : 0
            };
            
            console.log('Saving client:', updatedClient);
            
            try {
                const result = await updateClient(updatedClient);
                
                console.log('Update result:', result);
                
                if (result.success) {
                    alert('Cliente aggiornato con successo!');
                    
                    // Refresh data
                    currentClient = updatedClient;
                    
                    // Update window title
                    const fullName = [firstName, lastName].join(' ');
                    document.title = `Dettagli: ${fullName} - Agenda`;
                    
                    // Try to refresh parent window
                    if (window.opener && !window.opener.closed) {
                        try {
                            if (window.opener.clientsUI && typeof window.opener.clientsUI.loadClients === 'function') {
                                window.opener.clientsUI.loadClients();
                            }
                        } catch (e) {
                            console.log('Could not refresh parent window');
                        }
                    }
                } else {
                    alert('Errore durante il salvataggio: ' + (result.error || 'Errore sconosciuto'));
                }
            } catch (error) {
                console.error('Errore salvataggio cliente:', error);
                alert('Errore di connessione durante il salvataggio: ' + error.message);
            }
        }
        
        async function deleteClient() {
            if (!currentClient) {
                alert('Dati cliente non disponibili');
                return;
            }
            
            const clientName = [currentClient.first_name, currentClient.last_name].filter(Boolean).join(' ');
            const confirmMessage = `Sei sicuro di voler eliminare il cliente "${clientName}"?\n\nQuesta operazione non può essere annullata.`;
            
            if (!confirm(confirmMessage)) {
                return;
            }
            
            try {
                const response = await fetch(`../../../src/Api/api.php?endpoint=clients&id=${currentClient.id}`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' }
                });
                const result = await response.json();
                
                if (result.success) {
                    alert('Cliente eliminato con successo!');
                    
                    // Try to refresh parent window
                    if (window.opener && !window.opener.closed) {
                        try {
                            if (window.opener.clientsUI && typeof window.opener.clientsUI.loadClients === 'function') {
                                window.opener.clientsUI.loadClients();
                            }
                        } catch (e) {
                            console.log('Could not refresh parent window');
                        }
                    }
                    
                    window.close();
                } else {
                    alert('Errore durante l\'eliminazione: ' + (result.error || 'Errore sconosciuto'));
                }
            } catch (error) {
                console.error('Errore eliminazione cliente:', error);
                alert('Errore di connessione durante l\'eliminazione');
            }
        }
        
        function showError(message) {
            document.getElementById('error-container').style.display = 'block';
            document.getElementById('error-message-text').textContent = message;
        }
        
        // Load data when page is ready
        document.addEventListener('DOMContentLoaded', () => {
            // Hide delete button if creating new client
            if (isNewClient) {
                const deleteBtn = document.getElementById('delete-client-btn');
                if (deleteBtn) {
                    deleteBtn.style.display = 'none';
                }
            }
            
            loadClientDetails();
            
            // Add event listeners
            document.getElementById('save-client-btn')?.addEventListener('click', saveClientChanges);
            
            // Only add delete listener if not creating new client
            if (!isNewClient) {
                document.getElementById('delete-client-btn')?.addEventListener('click', deleteClient);
            }
        });
    </script>
</body>
</html>