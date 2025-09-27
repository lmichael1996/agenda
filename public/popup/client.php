<?php
/**
 * Popup per la gestione dei dati utente (client) - Finestra separata
 * Tabella verticale con tutti i dati: nome, cognome, telefono, nota, certificato
 */
require_once '../../config/config.php';

// Il config.php gestisce automaticamente tutti i controlli per i popup:
// - Autenticazione utente obbligatoria  
// - Headers di sicurezza
// - Controlli anti-hijacking
// - Gestione sessioni
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Cliente - Agenda</title>
    <link rel="stylesheet" href="../../assets/css/popup.css">
    <link rel="stylesheet" href="../../assets/css/client.css">
    <link rel="stylesheet" href="../../assets/css/scrollbar.css">
    <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #ccc;
            border-top: 2px solid #000;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        .error-message {
            color: #d32f2f;
            background-color: #ffebee;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #ffcdd2;
            margin: 10px 0;
        }
    </style>

</head>
<body>
    <div class="popup-window-container">
        <div class="window-header">
            <span class="header-title">Lista Clienti</span>
        </div>

        <div class="calendar-body">
            <!-- Unified Controls Section -->
            <div class="unified-controls">
                <div class="controls-row top-row">
                    <div class="search-section">
                        <input type="search" id="client-search" placeholder="Cerca cliente..." class="search-input">
                        <button id="search-btn" class="control-btn search-btn">🔍</button>
                        <button id="group-btn" class="control-btn group-btn">👥</button>
                    </div>
                    <button id="add-client-btn" class="add-client-btn">✚ Aggiungi Cliente</button>
                </div>
                
                <div class="controls-row bottom-row">
                    <div class="pagination-section">
                        <button id="prev-btn" class="nav-btn" disabled>‹</button>
                        <span id="client-range-label" class="range-info">0-0</span>
                        <button id="next-btn" class="nav-btn" disabled>›</button>
                    </div>
                    <div class="total-section">
                        <span id="client-total-label" class="total-info">Totale: 0</span>
                    </div>
                </div>
            </div>

            <div class="schedules-table-container">
                <!-- Indicatore di caricamento -->
                <div id="loading-indicator" style="text-align: center; padding: 20px; display: none;">
                    <div style="display: inline-block; width: 20px; height: 20px; border: 2px solid #ccc; border-top: 2px solid #000; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                    <p style="margin-top: 10px;">Caricamento clienti...</p>
                </div>
                
                <table class="excel-table client-table" id="clients-table">
                    <thead>
                        <tr>
                            <th class="client-name-col">Nome</th>
                            <th class="client-surname-col">Cognome</th>
                            <th class="client-phone-col">Telefono</th>
                            <th class="client-note-col">Nota</th>
                            <th class="client-cert-col">Certificato</th>
                        </tr>
                    </thead>
                    <tbody id="client-table-body">
                        <!-- HTML generato dinamicamente dal JavaScript -->
                    </tbody>
                </table>
                
                <!-- Messaggio di errore -->
                <div id="error-message" style="text-align: center; padding: 20px; color: #d32f2f; display: none;">
                    <p>Errore nel caricamento dei dati</p>
                </div>
            </div>
        </div>
    </div>
    <script type="module">
        // Importa l'API dei clienti
        import { ClientsUI } from '../../api/frontend/clients-api.js';
        
        // L'inizializzazione avviene automaticamente nell'API
        console.log('Client API caricata');
    </script>
</body>
</html>
