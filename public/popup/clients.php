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
    <link rel="stylesheet" href="../../assets/css/scrollbar.css">

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
                        <select id="search-type-select" class="search-type-select">
                            <option value="starts">Inizia con</option>
                            <option value="contains">Contiene</option>
                            <option value="ends">Finisce con</option>
                            <option value="exact">Esatto</option>
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
                <div id="loading-indicator">
                    <div></div>
                    <p>Caricamento clienti...</p>
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
                <div id="error-message">
                    <p>Errore nel caricamento dei dati</p>
                </div>
            </div>
        </div>
    </div>
    <script type="module" src="../../assets/js/clients-popup.js"></script>
</body>
</html>
