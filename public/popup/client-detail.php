<?php
/**
 * Popup per i dettagli cliente - Finestra separata
 * Stile nota con tabella verticale
 */
require_once '../../config/config.php';

// Il config.php gestisce automaticamente tutti i controlli per i popup
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dettagli Cliente - Agenda</title>
    <link rel="stylesheet" href="../../assets/css/popup.css">
    <link rel="stylesheet" href="../../assets/css/scrollbar.css">
    <style>
        .client-detail-calendar-body {
            padding: 20px;
        }
        
        .client-detail-table {
            width: 100%;
            margin-bottom: 20px;
        }
        
        .client-detail-table th {
            background: #000000;
            color: #ffffff;
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
            text-align: left;
            padding: 12px 15px;
            width: 30%;
            border: 1px solid #000000;
        }
        
        .client-detail-table td {
            background: #ffffff;
            color: #000000;
            font-family: 'Courier New', Courier, monospace;
            padding: 12px 15px;
            border: 1px solid #ddd;
            vertical-align: middle;
        }
        
        .certificate-display {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            display: inline-block;
        }
        
        .certificate-yes {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .certificate-no {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f1b0b7;
        }
        
        .client-id-display {
            font-weight: bold;
            font-size: 16px;
            color: #000000;
        }
        
        .notes-display {
            max-height: 100px;
            overflow-y: auto;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #e0e0e0;
            font-style: italic;
        }
        
        .loading-container {
            text-align: center;
            padding: 40px;
        }
        
        .loading-spinner {
            display: inline-block;
            width: 30px;
            height: 30px;
            border: 3px solid #ccc;
            border-top: 3px solid #000;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 15px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .error-container {
            text-align: center;
            padding: 40px;
            color: #d32f2f;
        }
        
        .error-message {
            background: #ffebee;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #ffcdd2;
            font-family: 'Courier New', Courier, monospace;
        }
        
        .close-btn-container {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: 4px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
            background: #fff;
            cursor: pointer;
            font-family: 'Courier New', Courier, monospace;
            border: 2px solid;
            transition: all 0.2s ease;
        }
        
        .edit-btn {
            background: #ffffff;
            color: #007bff;
            border-color: #007bff;
        }
        
        .edit-btn:hover {
            background: #007bff;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,123,255,0.3);
        }
        
        .delete-btn {
            background: #ffffff;
            color: #dc3545;
            border-color: #dc3545;
        }
        
        .delete-btn:hover {
            background: #dc3545;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(220,53,69,0.3);
        }
        
        .close-detail-btn {
            background: #ffffff;
            color: #000000;
            border: 2px solid #000000;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: 4px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
            cursor: pointer;
            font-family: 'Courier New', Courier, monospace;
        }
        
        .close-detail-btn:hover {
            background: #000000;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }
        
        @media (max-width: 600px) {
            .close-btn-container {
                flex-direction: column;
                align-items: center;
            }
            
            .action-btn, .close-detail-btn {
                width: 100%;
                max-width: 200px;
            }
        }
        
        .phone-display {
            font-weight: bold;
            color: #007bff;
            text-decoration: none;
        }
        
        .phone-display:hover {
            text-decoration: underline;
        }
        
        /* Appointment History Section */
        .appointment-history-section {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="popup-window-container">
        <div class="window-header">
            <span class="header-title">Dettagli Cliente</span>
        </div>
        
        <div class="calendar-body client-detail-calendar-body">
            <!-- Loading indicator -->
            <div id="loading-container" class="loading-container">
                <div class="loading-spinner"></div>
                <p>Caricamento dettagli cliente...</p>
            </div>
            
            <!-- Error container -->
            <div id="error-container" class="error-container" style="display: none;">
                <div class="error-message" id="error-message-text">
                    Errore nel caricamento dei dati
                </div>
            </div>
            
            <!-- Client details table -->
            <div id="client-details-container" style="display: none;">
                <table class="excel-table client-detail-table">
                    <tbody>
                        <tr>
                            <th>ID Cliente</th>
                            <td><span id="detail-client-id" class="client-id-display">-</span></td>
                        </tr>
                        <tr>
                            <th>Nome</th>
                            <td><span id="detail-first-name">-</span></td>
                        </tr>
                        <tr>
                            <th>Cognome</th>
                            <td><span id="detail-last-name">-</span></td>
                        </tr>
                        <tr>
                            <th>Nome Completo</th>
                            <td><span id="detail-full-name">-</span></td>
                        </tr>
                        <tr>
                            <th>Telefono</th>
                            <td>
                                <a href="#" id="detail-phone-link" class="phone-display">-</a>
                            </td>
                        </tr>
                        <tr>
                            <th>Note</th>
                            <td>
                                <div id="detail-notes" class="notes-display">Nessuna nota</div>
                            </td>
                        </tr>
                        <tr>
                            <th>Certificato</th>
                            <td>
                                <span id="detail-certificate" class="certificate-display">-</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
        <!-- Appointment History Section -->
        <div class="appointment-history-section">
            <h3 style="font-family: 'Courier New', monospace; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #000;">Storico Appuntamenti</h3>
            <div id="appointment-history-content">
                <div id="appointment-history-placeholder" style="color: #666; font-style: italic; padding: 20px; text-align: center;">Storico appuntamenti del cliente (in sviluppo).</div>
            </div>
        </div>                <div class="close-btn-container">
                    <button id="edit-client-btn" class="action-btn edit-btn">Modifica Cliente</button>
                    <button id="delete-client-btn" class="action-btn delete-btn">Elimina Cliente</button>
                </div>
            </div>
        </div>
    </div>

    <script type="module">
        import { fetchClientDetails } from '../../api/frontend/clients-api.js';
        
        // Get client ID from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const clientId = urlParams.get('clientId');
        
        async function loadClientDetails() {
            if (!clientId) {
                showError('ID cliente non specificato');
                return;
            }
            
            try {
                const response = await fetchClientDetails(clientId);
                
                if (response.success && response.data) {
                    // Store client data globally for edit/delete functions
                    currentClient = response.data;
                    displayClientDetails(response.data);
                } else {
                    showError(response.error || 'Cliente non trovato');
                }
            } catch (error) {
                console.error('Errore caricamento dettagli cliente:', error);
                showError('Errore di connessione durante il caricamento');
            }
        }
        
        function displayClientDetails(client) {
            // Hide loading
            document.getElementById('loading-container').style.display = 'none';
            
            // Show details container
            document.getElementById('client-details-container').style.display = 'block';
            
            // Populate data
            document.getElementById('detail-client-id').textContent = client.id;
            document.getElementById('detail-first-name').textContent = client.first_name || '-';
            document.getElementById('detail-last-name').textContent = client.last_name || '-';
            
            // Full name
            const fullName = [client.first_name, client.last_name].filter(Boolean).join(' ') || '-';
            document.getElementById('detail-full-name').textContent = fullName;
            
            // Phone with tel: link
            const phoneElement = document.getElementById('detail-phone-link');
            if (client.phone) {
                phoneElement.textContent = client.phone;
                phoneElement.href = `tel:${client.phone}`;
                phoneElement.onclick = () => {
                    if (confirm(`Chiamare ${client.phone}?`)) {
                        window.location.href = `tel:${client.phone}`;
                    }
                    return false;
                };
            } else {
                phoneElement.textContent = 'Non disponibile';
                phoneElement.href = '#';
                phoneElement.onclick = () => false;
            }
            
            // Notes
            const notesElement = document.getElementById('detail-notes');
            if (client.notes && client.notes.trim()) {
                notesElement.textContent = client.notes;
                notesElement.style.fontStyle = 'normal';
            } else {
                notesElement.textContent = 'Nessuna nota disponibile';
                notesElement.style.fontStyle = 'italic';
            }
            
            // Certificate badge
            const certificateElement = document.getElementById('detail-certificate');
            if (client.has_certificate == 1) {
                certificateElement.textContent = 'Presente';
                certificateElement.className = 'certificate-display certificate-yes';
            } else {
                certificateElement.textContent = 'Non Presente';
                certificateElement.className = 'certificate-display certificate-no';
            }
            
            // Update window title
            document.title = `Dettagli Cliente: ${fullName} - Agenda`;
        }
        
        function showError(message) {
            // Hide loading
            document.getElementById('loading-container').style.display = 'none';
            
            // Show error
            document.getElementById('error-container').style.display = 'block';
            document.getElementById('error-message-text').textContent = message;
        }
        
        // Global variable to store current client data
        let currentClient = null;
        
        function editClient() {
            if (!currentClient) {
                alert('Dati cliente non disponibili');
                return;
            }
            
            // Create edit form popup or redirect to edit page
            const editUrl = `client-edit.php?clientId=${currentClient.id}`;
            const windowFeatures = [
                'width=700',
                'height=600',
                'left=' + (screen.width - 700) / 2,
                'top=' + (screen.height - 600) / 2,
                'scrollbars=yes',
                'resizable=yes',
                'menubar=no',
                'toolbar=no',
                'location=no',
                'status=no'
            ].join(',');
            
            const editWindow = window.open(editUrl, 'EditClient_' + currentClient.id, windowFeatures);
            
            if (editWindow) {
                editWindow.focus();
                
                // Listen for edit completion to refresh data
                const checkClosed = setInterval(() => {
                    if (editWindow.closed) {
                        clearInterval(checkClosed);
                        // Refresh client details
                        loadClientDetails();
                    }
                }, 1000);
            } else {
                alert('Impossibile aprire la finestra di modifica. Controlla le impostazioni del browser.');
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
                // Import deleteClient function
                const { deleteClient: deleteClientAPI } = await import('../../api/frontend/clients-api.js');
                
                const result = await deleteClientAPI(currentClient.id);
                
                if (result.success) {
                    alert('Cliente eliminato con successo!');
                    
                    // Close this window and refresh parent if possible
                    if (window.opener && !window.opener.closed) {
                        try {
                            // Try to refresh parent window's client list
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
        

        
        // Load data when page is ready
        document.addEventListener('DOMContentLoaded', () => {
            loadClientDetails();
            
            // Add event listeners for buttons
            document.getElementById('edit-client-btn')?.addEventListener('click', editClient);
            document.getElementById('delete-client-btn')?.addEventListener('click', deleteClient);
        });
        
        // Handle window focus (refresh data when window comes back into focus)
        window.addEventListener('focus', () => {
            if (document.getElementById('client-details-container').style.display !== 'none') {
                loadClientDetails();
            }
        });
    </script>
</body>
</html>