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
        
        .sort-select, .search-field-select {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: white;
            font-size: 14px;
            margin-right: 8px;
            cursor: pointer;
        }
        
        .sort-select {
            min-width: 150px;
        }
        
        .search-field-select {
            min-width: 130px;
        }
        
        .sort-select:focus, .search-field-select:focus {
            outline: none;
            border-color: #000;
            box-shadow: 0 0 0 2px rgba(0,0,0,0.1);
        }

        .unified-controls {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .controls-row {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .controls-row:last-child {
            margin-bottom: 0;
        }

        /* Prima riga - Ricerca e Filtri */
        .search-row {
            justify-content: center;
            gap: 30px;
        }

        .search-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sort-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Seconda riga - Azioni e Navigazione */
        .action-row {
            justify-content: space-between;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }

        .info-section {
            display: flex;
            align-items: center;
        }

        .pagination-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .add-client-btn {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .add-client-btn:hover {
            background: linear-gradient(135deg, #45a049 0%, #3d8b40 100%);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.4);
            transform: translateY(-2px);
        }

        .add-client-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 6px rgba(76, 175, 80, 0.3);
        }

        .btn-icon {
            font-size: 16px;
            font-weight: bold;
        }

        .btn-text {
            font-size: 14px;
        }
        
        /* Action buttons styling */
        .client-actions-col {
            width: 140px;
            text-align: center;
        }
        
        .actions-cell {
            text-align: center;
            white-space: nowrap;
            padding: 4px;
        }
        
        .action-btn {
            padding: 6px 12px;
            margin: 2px;
            font-size: 11px;
            background: #000;
            color: #fff;
            border: 1.5px solid #000;
            cursor: pointer;
            font-family: 'Segoe UI', 'Arial', sans-serif;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .action-btn:hover {
            background: #fff;
            color: #000;
            border-color: #000;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }
        
        /* Stile specifico per il button Appuntamento */
        .btn-add-appointment {
            background: #000;
            color: #fff;
            border: 1.5px solid #000;
            font-weight: 600;
            font-family: 'Courier New', monospace;
            position: relative;
            overflow: hidden;
        }
        
        .btn-add-appointment:hover {
            background: #fff;
            color: #000;
            border-color: #000;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .btn-add-appointment:active {
            transform: translateY(0);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }
        
        /* Effetto icona per il button appuntamento */
        .btn-add-appointment::before {
            content: '📅';
            margin-right: 4px;
            font-size: 10px;
        }
        
        /* Responsive styling for smaller screens */
        @media (max-width: 768px) {
            .client-actions-col {
                width: 140px;
            }
            
            .actions-cell {
                padding: 2px;
            }
            
            .action-btn {
                padding: 5px 10px;
                font-size: 10px;
                margin: 1px;
            }
            
            .btn-add-appointment::before {
                font-size: 9px;
                margin-right: 2px;
            }
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
                <!-- Prima riga: Ricerca e Filtri -->
                <div class="controls-row search-row">
                    <div class="search-group">
                        <input type="search" id="client-search" placeholder="Cerca cliente..." class="search-input">
                        <select id="search-field-select" class="search-field-select">
                            <option value="name">Nome e Cognome</option>
                            <option value="first_name">Solo Nome</option>
                            <option value="last_name">Solo Cognome</option>
                            <option value="phone">Telefono</option>
                            <option value="notes">Note</option>
                        </select>
                        <select id="sort-select" class="sort-select">
                            <option value="first_name_asc">Nome A-Z</option>
                            <option value="first_name_desc">Nome Z-A</option>
                            <option value="last_name_asc">Cognome A-Z</option>
                            <option value="last_name_desc">Cognome Z-A</option>
                        </select>
                        <button id="search-btn" class="control-btn search-btn">🔍</button>
                        <button id="group-btn" class="control-btn group-btn" title="Mostra tutti i clienti">👥</button>
                    </div>
                </div>
                
                <!-- Seconda riga: Azioni e Navigazione -->
                <div class="controls-row action-row">
                    <div class="pagination-section">
                        <button id="prev-btn" class="nav-btn" disabled>‹</button>
                        <span id="client-range-label" class="range-info">0-0</span>
                        <button id="next-btn" class="nav-btn" disabled>›</button>
                    </div>
                    
                    <div class="info-section">
                        <span id="client-total-label" class="total-info">Totale: 0</span>
                    </div>
                    
                    <button id="add-client-btn" class="add-client-btn">
                        <span class="btn-icon">➕</span>
                        <span class="btn-text">Aggiungi Cliente</span>
                    </button>

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
                            <th class="client-actions-col">Azioni</th>
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
        
        // Leggi parametri URL per ricerca automatica
        const urlParams = new URLSearchParams(window.location.search);
        const searchText = urlParams.get('search') || '';
        const searchField = urlParams.get('searchField') || 'name';
        
        // Aspetta che il DOM sia caricato
        document.addEventListener('DOMContentLoaded', function() {
            // Se ci sono parametri di ricerca, imposta i valori e esegui la ricerca
            if (searchText || searchField !== 'name') {
                const searchInput = document.getElementById('client-search');
                const searchFieldSelect = document.getElementById('search-field-select');
                
                if (searchInput && searchText) {
                    searchInput.value = searchText;
                }
                
                if (searchFieldSelect && searchField) {
                    // Mappa i valori dal dashboard al popup
                    const fieldMapping = {
                        'name': 'name',
                        'first_name': 'first_name', 
                        'last_name': 'last_name',
                        'phone': 'phone',
                        'notes': 'notes'
                    };
                    
                    const mappedField = fieldMapping[searchField] || 'name';
                    searchFieldSelect.value = mappedField;
                }
            }
        });
        
        // L'inizializzazione avviene automaticamente nell'API
        console.log('Client API caricata con parametri:', { searchText, searchField });
    </script>
</body>
</html>
