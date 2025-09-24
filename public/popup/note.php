<?php
/**
 * Popup per la gestione delle note - Finestra separata
 * Struttura orizzontale, stile uniforme con popup.css
 */
require_once '../../config/config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Cache-Control: no-cache, no-store, must-revalidate');
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Note - Agenda</title>
    <link rel="stylesheet" href="../../assets/css/popup.css">
    <link rel="stylesheet" href="../../assets/css/scrollbar.css">
</head>
<body>
    <div class="popup-window-container">
        <div class="window-header">
            <span class="header-title">Gestione Nota</span>
        </div>
        <div class="calendar-body" style="padding:8px;">
            <form id="note-form" style="max-width:600px; margin:auto;">
                <table class="excel-table" style="width:100%; margin-bottom:24px;">
                    <tbody>
                        <tr>
                            <th style="width:140px; font-size:13px; border: 1px solid white;">Titolo</th>
                            <td><input type="text" id="note-title" class="cell-input" style="width:98%; font-size:13px;" placeholder="Titolo nota..."></td>
                        </tr>
                        <tr>
                            <th style="font-size:13px; border: 1px solid white;">Nota</th>
                            <td><textarea id="note-content" class="cell-textarea" style="width:98%; min-height:80px; font-size:13px;" placeholder="Scrivi la nota..."></textarea></td>
                        </tr>
                        <tr>
                            <th style="font-size:13px; border: 1px solid white;">Utente</th>
                            <td><input type="text" id="note-user" class="cell-input" style="width:98%; font-size:13px;" placeholder="Utente"></td>
                        </tr>
                        <tr>
                            <th style="font-size:13px; border: 1px solid white;">Tutti</th>
                            <td><input type="checkbox" id="note-all" class="cell-input" style="height:28px; width:28px;"></td>
                        </tr>
                        <tr>
                            <th style="font-size:13px; border: 1px solid white;">Data e ora</th>
                            <td>
                                <input type="date" id="note-date" class="cell-input" style="width:48%; font-size:13px; display:inline-block;">
                                <input type="number" id="note-hour" class="hour-input" min="0" max="23" value="" style="width:44px; display:inline-block; margin-left:8px;">:
                                <select id="note-minute" class="minute-select" style="width:44px; display:inline-block; margin-left:2px;">
                                    <option value="00">00</option>
                                    <option value="15">15</option>
                                    <option value="30">30</option>
                                    <option value="45">45</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th style="font-size:13px; border: 1px solid white;">Tempo</th>
                            <td><input type="number" id="note-time" class="cell-input" min="0" max="1440" style="width:98%; font-size:13px;" placeholder="Minuti"></td>
                        </tr>
                    </tbody>
                </table>
                <div style="text-align:center; border-top: 1px solid #ccc; padding-top: 12px;">
                    <button class="save-btn" id="save-note-btn" style="font-size:15px; padding:16px 32px;">💾 Salva Nota</button>
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
