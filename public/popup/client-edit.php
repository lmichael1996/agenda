<?php
/**
 * Popup per la modifica di un cliente
 * Carica i dati del cliente e permette la modifica
 */

// Verifica sessione
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../access-denied.php');
    exit();
}

// Get client ID from URL
$clientId = isset($_GET['clientId']) ? intval($_GET['clientId']) : 0;

if (!$clientId) {
    echo "<script>alert('ID cliente non valido'); window.close();</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica Cliente</title>
    
    <style>
        /* Reset e base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            background-color: #ffffff;
            color: #000000;
            line-height: 1.6;
            font-size: 14px;
            overflow-x: hidden;
        }
        
        .edit-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 2px solid #000000;
            background-color: #ffffff;
            box-shadow: 3px 3px 0px #000000;
        }
        
        .edit-header {
            text-align: center;
            border-bottom: 2px solid #000000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .edit-title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .edit-form {
            display: grid;
            gap: 15px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-label {
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-size: 12px;
        }
        
        .form-input,
        .form-textarea,
        .form-select {
            padding: 8px;
            border: 2px solid #000000;
            background-color: #ffffff;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            width: 100%;
        }
        
        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            outline: none;
            background-color: #f0f0f0;
            box-shadow: inset 2px 2px 4px rgba(0,0,0,0.1);
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .form-checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-checkbox {
            width: 18px;
            height: 18px;
            border: 2px solid #000000;
            background-color: #ffffff;
        }
        
        .action-buttons {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 2px solid #000000;
        }
        
        .btn {
            padding: 10px 20px;
            border: 2px solid #000000;
            background-color: #ffffff;
            color: #000000;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            cursor: pointer;
            letter-spacing: 1px;
            transition: all 0.2s;
        }
        
        .btn:hover {
            background-color: #000000;
            color: #ffffff;
            transform: translateY(-1px);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn-save {
            background-color: #28a745;
            color: #ffffff;
            border-color: #28a745;
        }
        
        .btn-save:hover {
            background-color: #218838;
            border-color: #218838;
        }
        
        .btn-cancel {
            background-color: #6c757d;
            color: #ffffff;
            border-color: #6c757d;
        }
        
        .btn-cancel:hover {
            background-color: #5a6268;
            border-color: #5a6268;
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
            background-color: #f8d7da;
            color: #721c24;
            border: 2px solid #f1b0b7;
            padding: 15px;
            margin: 20px;
            text-align: center;
        }
        
        .validation-error {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
        }
        
        /* Responsive */
        @media (max-width: 600px) {
            .edit-container {
                margin: 10px;
                padding: 15px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Loading container -->
    <div id="loading-container" class="loading-container">
        <div class="loading-spinner"></div>
        <p>Caricamento dati cliente...</p>
    </div>
    
    <!-- Error container -->
    <div id="error-container" class="error-container" style="display: none;">
        <p id="error-message">Errore nel caricamento</p>
    </div>
    
    <!-- Edit form container -->
    <div id="edit-container" class="edit-container" style="display: none;">
        <div class="edit-header">
            <h2 class="edit-title">Modifica Cliente</h2>
            <p id="client-id-display">ID: <span id="edit-client-id">-</span></p>
        </div>
        
        <form id="client-edit-form" class="edit-form">
            <div class="form-group">
                <label class="form-label" for="edit-first-name">Nome *</label>
                <input type="text" id="edit-first-name" name="first_name" class="form-input" required>
                <div id="first-name-error" class="validation-error" style="display: none;"></div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="edit-last-name">Cognome *</label>
                <input type="text" id="edit-last-name" name="last_name" class="form-input" required>
                <div id="last-name-error" class="validation-error" style="display: none;"></div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="edit-phone">Telefono</label>
                <input type="tel" id="edit-phone" name="phone" class="form-input">
                <div id="phone-error" class="validation-error" style="display: none;"></div>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="edit-notes">Note</label>
                <textarea id="edit-notes" name="notes" class="form-textarea" placeholder="Note aggiuntive sul cliente..."></textarea>
            </div>
            
            <div class="form-group">
                <div class="form-checkbox-group">
                    <input type="checkbox" id="edit-has-certificate" name="has_certificate" class="form-checkbox">
                    <label class="form-label" for="edit-has-certificate">Ha Certificato</label>
                </div>
            </div>
            
            <div class="action-buttons">
                <button type="button" id="cancel-btn" class="btn btn-cancel">Annulla</button>
                <button type="submit" id="save-btn" class="btn btn-save">Salva Modifiche</button>
            </div>
        </form>
    </div>
    
    <script>
        // Client ID from URL
        const clientId = <?php echo $clientId; ?>;
        let currentClient = null;
        
        // Import API functions
        async function fetchClientDetails(clientId) {
            const PATH = '../../api/backend/clients-api.php';
            const response = await fetch(`${PATH}?id=${clientId}`, {
                method: 'GET',
                headers: { 'Content-Type': 'application/json' }
            });
            
            const text = await response.text();
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${text}`);
            }
            
            return JSON.parse(text);
        }
        
        async function updateClient(clientData) {
            const PATH = '../../api/backend/clients-api.php';
            const response = await fetch(PATH, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(clientData)
            });
            
            const text = await response.text();
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${text}`);
            }
            
            return JSON.parse(text);
        }
        
        // Load client data
        async function loadClientData() {
            try {
                const response = await fetchClientDetails(clientId);
                
                if (response.success && response.data) {
                    currentClient = response.data;
                    populateForm(response.data);
                    showEditForm();
                } else {
                    showError(response.error || 'Cliente non trovato');
                }
            } catch (error) {
                console.error('Errore caricamento dati cliente:', error);
                showError('Errore di connessione durante il caricamento');
            }
        }
        
        // Populate form with client data
        function populateForm(client) {
            document.getElementById('edit-client-id').textContent = client.id;
            document.getElementById('edit-first-name').value = client.first_name || '';
            document.getElementById('edit-last-name').value = client.last_name || '';
            document.getElementById('edit-phone').value = client.phone || '';
            document.getElementById('edit-notes').value = client.notes || '';
            document.getElementById('edit-has-certificate').checked = !!client.has_certificate;
        }
        
        // Form validation
        function validateForm() {
            let isValid = true;
            
            // Clear previous errors
            document.querySelectorAll('.validation-error').forEach(el => {
                el.style.display = 'none';
                el.textContent = '';
            });
            
            // Validate first name
            const firstName = document.getElementById('edit-first-name').value.trim();
            if (!firstName) {
                showValidationError('first-name-error', 'Il nome è obbligatorio');
                isValid = false;
            }
            
            // Validate last name
            const lastName = document.getElementById('edit-last-name').value.trim();
            if (!lastName) {
                showValidationError('last-name-error', 'Il cognome è obbligatorio');
                isValid = false;
            }
            
            // Validate phone (optional but format check if provided)
            const phone = document.getElementById('edit-phone').value.trim();
            if (phone) {
                const phoneRegex = /^[\d\s\+\-\(\)]{8,15}$/;
                if (!phoneRegex.test(phone.replace(/\s/g, ''))) {
                    showValidationError('phone-error', 'Formato telefono non valido');
                    isValid = false;
                }
            }
            
            return isValid;
        }
        
        function showValidationError(elementId, message) {
            const errorElement = document.getElementById(elementId);
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
        
        // Save client changes
        async function saveClient(event) {
            event.preventDefault();
            
            if (!validateForm()) {
                return;
            }
            
            const formData = {
                id: currentClient.id,
                first_name: document.getElementById('edit-first-name').value.trim(),
                last_name: document.getElementById('edit-last-name').value.trim(),
                phone: document.getElementById('edit-phone').value.trim(),
                notes: document.getElementById('edit-notes').value.trim(),
                has_certificate: document.getElementById('edit-has-certificate').checked ? 1 : 0
            };
            
            try {
                // Disable form during save
                const form = document.getElementById('client-edit-form');
                form.style.opacity = '0.6';
                form.style.pointerEvents = 'none';
                
                const result = await updateClient(formData);
                
                if (result.success) {
                    alert('Cliente aggiornato con successo!');
                    
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
                    throw new Error(result.error || 'Errore durante il salvataggio');
                }
            } catch (error) {
                console.error('Errore salvataggio cliente:', error);
                alert('Errore durante il salvataggio: ' + error.message);
                
                // Re-enable form
                const form = document.getElementById('client-edit-form');
                form.style.opacity = '1';
                form.style.pointerEvents = 'auto';
            }
        }
        
        // UI helper functions
        function showEditForm() {
            document.getElementById('loading-container').style.display = 'none';
            document.getElementById('edit-container').style.display = 'block';
        }
        
        function showError(message) {
            document.getElementById('loading-container').style.display = 'none';
            document.getElementById('error-message').textContent = message;
            document.getElementById('error-container').style.display = 'block';
        }
        
        function cancelEdit() {
            if (confirm('Sei sicuro di voler annullare le modifiche?')) {
                window.close();
            }
        }
        
        // Event listeners
        document.addEventListener('DOMContentLoaded', () => {
            loadClientData();
            
            document.getElementById('client-edit-form').addEventListener('submit', saveClient);
            document.getElementById('cancel-btn').addEventListener('click', cancelEdit);
            
            // Handle Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    cancelEdit();
                }
            });
        });
        
        // Handle window focus (refresh data when window comes back into focus)
        window.addEventListener('focus', () => {
            if (document.getElementById('edit-container').style.display !== 'none') {
                loadClientData();
            }
        });
    </script>
</body>
</html>