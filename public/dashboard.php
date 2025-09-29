<?php
/**
 * Dashboard principale - Calendario settimanale
 * Accesso riservato agli utenti autenticati
 */

// Carica configurazione e controlli di sicurezza
require_once '../config/config.php';

// Il file config.php gestisce automaticamente tutti i controlli di sicurezza:
// - Autenticazione utente
// - Anti session hijacking (IP + User Agent)
// - Scadenza sessione
// - Headers di sicurezza
// - Rate limiting
// - Logging accessi

// Carica funzioni calendario (dopo verifiche sicurezza)
require_once '../utils/calendar_functions.php';

// Genera dati per il calendario
$days = getCurrentWeekDays();
$dayNames = getDayNames();
$intervals = generateTimeIntervals();
$today = new DateTime();

// Calcola ora attuale per evidenziazione
$nowHour = (int)$today->format('H');
$nowMin = (int)$today->format('i');
$roundedMin = floor($nowMin / CALENDAR_INTERVAL_MINUTES) * CALENDAR_INTERVAL_MINUTES;
$nowTime = sprintf('%02d:%02d', $nowHour, $roundedMin);

// Variabili per i controlli della UI
$currentWeek = $today->format('Y-\WW');
$currentDate = $today->format('Y-m-d');
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda Settimanale</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/top-header.css">
    <link rel="stylesheet" href="../assets/css/scrollbar.css">
    <link rel="stylesheet" href="../assets/css/calendar-events.css">
    <link rel="stylesheet" href="../assets/css/week-calendar.css">
    <link rel="stylesheet" href="../assets/css/lateral-menu.css">
</head>
<body>
    <!-- Sidebar con icona integrata -->
    <div id="sidebar" class="sidebar sidebar-hover">
        <div class="sidebar-toggle">
            <img src="../assets/images/menu-icon.png" alt="Menu" class="menu-icon">
        </div>
        <a href="#" data-popup-window="services" class="sidebar-link">
            <span class="sidebar-text">Servizi</span>
        </a>
        <a href="#" data-popup-window="users" class="sidebar-link">
            <span class="sidebar-text">Utenti</span>
        </a>
        <a href="#" data-popup-window="schedule" class="sidebar-link">
            <span class="sidebar-text">Orario</span>
        </a>
        <a href="../utils/logout.php" class="logout sidebar-link" onclick="return confirm('Sei sicuro di voler uscire?')">
            <span class="sidebar-text">Logout</span>
        </a>
    </div>

    <!-- Top Menu Controls -->
    <div class="dashboard-controls">
        <div class="controls-left">
            <select id="set-view" onmouseover="this.style.cursor='pointer'">
                <option value="week">Settimana</option>
                <option value="day">Giorno</option>
            </select>
            <input type="week" id="set-week" onmouseover="this.style.cursor='pointer'" value="<?= $currentWeek ?>">
            <input type="date" id="set-date" onmouseover="this.style.cursor='pointer'" value="<?= $currentDate ?>" hidden>
        </div>
        
        <div class="controls-center">
            <label for="cerca" class="control-label">Cliente:</label>
            <input type="search" autocomplete="on" id="cerca" placeholder="Cerca cliente...">
            <select id="search-field-select" class="search-field-select">
                <option value="name">Nome e Cognome</option>
                <option value="first_name">Nome</option>
                <option value="last_name">Cognome</option>
                <option value="phone">Telefono</option>
                <option value="notes">Note</option>
            </select>
            <input type="submit" id="client-search" onmouseover="this.style.cursor='pointer'" value="🔍">
            <button type="button" id="note-btn" title="Note" onmouseover="this.style.cursor='pointer'">📝</button>
        </div>
        
        <div class="controls-right">
            <marquee> Non è mai troppo tardi per essere ciò che vuoi essere! </marquee>
        </div>
    </div>

    <!-- Main Calendar -->
    <div class="dashboard-container">
        
        <!-- Calendar Header -->
        <div class="calendar-header-row">
            <div class="hour-label-empty">Orario:</div>
            <?php foreach ($days as $idx => $day): ?>
                <?php 
                $dayName = htmlspecialchars($dayNames[$idx]);
                $formattedDate = $day->format('d/m/Y');
                $isTodayClass = isToday($day) ? ' today' : '';
                ?>
                <div class="header-day<?= $isTodayClass ?>" 
                     id="header-day-<?= $idx ?>">
                    <?= $dayName ?><br>
                    <span class="header-date"><?= $formattedDate ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Calendar Grid -->
        <div class="calendar-grid">
            <?php foreach ($intervals as $time): ?>
                <?php $isPastOrNow = ($time <= $nowTime); ?>
                <div class="hour-label<?= $isPastOrNow ? ' hour-label-past' : '' ?>">
                    <span class="hour-label-time"><?= $time ?></span>
                </div>

                <?php foreach ($days as $day): ?>
                    <?php 
                    $dayDate = $day->format('d-m-Y');
                    $isTodayClass = isToday($day) ? ' today' : '';
                    ?>
                    <div class="day<?= $isTodayClass ?>"
                         data-date="<?= $dayDate ?>"
                         data-time="<?= $time ?>">
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>

    </div>

    <!-- JavaScript -->
    <script src="../assets/js/lateral-menu.js"></script>
    <script src="../assets/js/top-menu.js"></script>
    <script type="module" src="../assets/js/calendar-events.js"></script>
</body>
</html>