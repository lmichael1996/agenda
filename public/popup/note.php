<?php
/**
 * Popup per la gestione delle note - Finestra separata
 * Struttura orizzontale, stile uniforme con popup.css
 */
require_once '../../config/config.php';

// Il config.php gestisce automaticamente tutti i controlli per i popup
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Note - Agenda</title>
    <link rel="stylesheet" href="../../assets/css/popup.css">
    <link rel="stylesheet" href="../../assets/css/scrollbar.css">
    <style>
        /* Stili uniformi con schedule.php */
        .note-calendar-body {
            padding: 4px;
        }
        
        .note-form {
            max-width: 450px;
            margin: auto;
        }
        
        .note-table {
            width: 100%;
            margin-bottom: 24px;
        }
        
        .note-table-th {
            width: 140px;
            font-size: 13px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
        }
        
        .date-input {
            width: 48%;
            display: inline-block;
        }
        
        .note-checkbox {
            height: 28px;
            width: 28px;
        }
        
        .note-save-container {
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 12px;
        }
        
        .note-save-btn {
            font-size: 15px;
            padding: 16px 32px;
        }
    </style>
</head>
<body>
    <div class="popup-window-container">
        <div class="window-header">
            <span class="header-title">Gestione Nota</span>
        </div>
        <div class="calendar-body note-calendar-body">
            <form id="note-form" class="note-form">
                <table class="excel-table note-table">
                    <tbody>
                        <tr>
                            <th class="note-table-th">Titolo</th>
                            <td><input type="text" id="note-title" class="cell-input" placeholder="Titolo nota..." maxlength="100"></td>
                        </tr>
                        <tr>
                            <th class="note-table-th">Nota</th>
                            <td><textarea id="note-content" class="cell-textarea" placeholder="Scrivi la nota..." rows="3" maxlength="500"></textarea></td>
                        </tr>
                        <tr>
                            <th class="note-table-th">Utente</th>
                            <td><input type="text" id="note-user" class="cell-input" placeholder="Utente" maxlength="50"></td>
                        </tr>
                        <tr>
                            <th class="note-table-th">Tutti</th>
                            <td><input type="checkbox" id="note-all" class="cell-input note-checkbox"></td>
                        </tr>
                        <tr>
                            <th class="note-table-th">Data e ora</th>
                            <td>
                                <input type="date" id="note-date" class="cell-input date-input" value="<?= date('Y-m-d') ?>">
                                <input type="number" id="note-hour" class="hour-input" min="0" max="23" value="12"> :
                                <select id="note-minute" class="minute-select">
                                    <?php foreach (["00","15","30","45"] as $m): ?>
                                        <option value="<?= $m ?>"><?= $m ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th class="note-table-th">Tempo (min)</th>
                            <td><input type="number" id="note-time" class="cell-input" min="0" max="1440" placeholder="Minuti" step="15" value="30"></td>
                        </tr>
                    </tbody>
                </table>
                <div class="note-save-container">
                    <button class="save-btn note-save-btn" id="save-note-btn">Salva Nota</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        // Qui puoi aggiungere la logica JS per salvataggio, validazione, ecc.
        document.getElementById('save-note-btn').addEventListener('click', function(e) {
            e.preventDefault();
            // ...salva nota...
            alert('Nota salvata!');
        });
    </script>
</body>
</html>
