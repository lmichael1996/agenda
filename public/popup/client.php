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
    <style>
        /* Stili uniformi con gli altri popup */
        .client-calendar-body {
            padding: 8px;
        }
        
        .client-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 8px;
            position: relative;
        }
        
        .client-search-group {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .client-search-input {
            width: 180px;
            font-size: 12px;
            border-radius: 4px;
            border: 1px solid #000;
            background: #fff;
        }
        
        .client-btn {
            font-size: 12px;
            padding: 6px 12px;
            border-radius: 5px;
            background: #fff;
            color: #000;
        }
        
        .client-total-label {
            position: absolute;
            right: 0;
            font-size: 11px;
            color: #222;
            font-family: 'Courier New', monospace;
            background: #fff;
            border-radius: 4px;
            padding: 3px 10px;
            border: 1px solid #eee;
            font-weight: 600;
        }
        
        .client-pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 8px;
        }
        
        .client-range-label {
            font-size: 11px;
            color: #222;
            font-family: 'Courier New', monospace;
            background: #fff;
            border-radius: 4px;
            padding: 3px 10px;
            border: 1px solid #eee;
            font-weight: 600;
        }
        
        .client-nav-btn {
            font-size: 12px;
            padding: 6px 10px;
            border-radius: 5px;
            background: #fff;
            color: #000;
        }
        
        .client-table {
            width: 100%;
            margin-bottom: 24px;
        }
        
        .client-name-col, .client-surname-col {
            width: 20%;
        }
        
        .client-phone-col {
            width: 25%;
        }
        
        .client-note-col {
            width: 25%;
        }
        
        .client-cert-col {
            width: 10%;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="popup-window-container">
        <div class="window-header">
            <span class="header-title">Lista Clienti</span>
        </div>
        <div class="calendar-body client-calendar-body">
            <div class="client-controls">
                <div class="client-search-group">
                    <input type="search" id="client-search" class="cell-input client-search-input" placeholder="Cerca...">
                    <button id="search-btn" class="save-btn client-btn">Cerca</button>
                    <button id="group-btn" class="save-btn client-btn">Gruppo</button>
                </div>
                <span id="client-total-label" class="client-total-label">Totale: 100</span>
            </div>
            <div class="client-pagination">
                <button id="prev-btn" class="save-btn client-nav-btn">◀</button>
                <span id="client-range-label" class="client-range-label">da 1 a 50</span>
                <button id="next-btn" class="save-btn client-nav-btn">▶</button>
            </div>
            <div class="schedules-table-container">
                <table class="excel-table client-table">
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
                        <!-- Generato da JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        // Genera 100 clienti di esempio
        const nomi = ["Mario", "Luisa", "Giovanni", "Anna", "Paolo", "Sara", "Luca", "Francesca", "Marco", "Elena"];
        const cognomi = ["Rossi", "Bianchi", "Verdi", "Neri", "Gialli", "Blu", "Marroni", "Viola", "Grigi", "Rosa"];
        const note = ["Cliente storico", "Preferisce mattina", "Ha allergie", "Nuovo cliente", "Pagamenti puntuali", "Richiede fattura", "Senza certificato", "Da ricontattare", "VIP", "Nessuna nota"];
        const tbody = document.getElementById('client-table-body');
        // Paginazione clienti
        let clienti = [];
        for(let i=1; i<=100; i++) {
            const nome = nomi[Math.floor(Math.random()*nomi.length)];
            const cognome = cognomi[Math.floor(Math.random()*cognomi.length)];
            const telefono = '3' + Math.floor(100000000 + Math.random()*899999999);
            const nota = note[Math.floor(Math.random()*note.length)];
            const certificato = Math.random() > 0.5;
            clienti.push({nome, cognome, telefono, nota, certificato});
        }
        let pagina = 0;
        function renderClienti() {
            tbody.innerHTML = '';
            const start = pagina * 50;
            const end = Math.min(start + 50, clienti.length);
            for(let i=start; i<end; i++) {
                const c = clienti[i];
                const tr = document.createElement('tr');
                tr.innerHTML = `<td><input type='text' class='cell-input' value='${c.nome}'></td><td><input type='text' class='cell-input' value='${c.cognome}'></td><td><input type='text' class='cell-input' value='${c.telefono}'></td><td><input type='text' class='cell-input' value='${c.nota}'></td><td><input type='checkbox' ${c.certificato ? 'checked' : ''}></td>`;
                tbody.appendChild(tr);
            }
            // Aggiorna label intervallo e totale
            document.getElementById('client-range-label').textContent = `da ${start+1} a ${end}`;
            document.getElementById('client-total-label').textContent = `Totale: ${clienti.length}`;
        }
        document.getElementById('prev-btn').onclick = function() {
            if(pagina > 0) { pagina--; renderClienti(); }
        };
        document.getElementById('next-btn').onclick = function() {
            if((pagina+1)*50 < clienti.length) { pagina++; renderClienti(); }
        };
        renderClienti();
    </script>
</body>
</html>
