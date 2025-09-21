<?php
/**
 * Dashboard principale - Calendario settimanale
 */

require_once '../config/simple_config.php';
require_once '../config/calendar_functions.php';
setSecurityHeaders();

// Controllo autenticazione - redirect a index se non autenticato
if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Imposta flag per permettere accesso al login
    $_SESSION['from_index'] = true;
    header('Location: ../index.php');
    exit;
}

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
    <link rel="stylesheet" href="../assets/css/popup-styles.css">
</head>
<body>
    
    <!-- Sidebar con icona integrata -->
    <div id="sidebar" class="sidebar sidebar-hover">
        <div class="sidebar-toggle">
            <img src="../assets/images/menu-icon.png" alt="Menu" class="menu-icon">
        </div>
        <a href="#" onclick="openPopup('service')" data-popup="services">Servizi</a>
        <a href="#" onclick="openPopup('user')" data-popup="users">Utenti</a>
        <a href="#" onclick="openPopup('schedule')" data-popup="schedule">Orario</a>
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
            <input type="submit" onmouseover="this.style.cursor='pointer'" value="Cerca">
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
    <script type="module" src="../assets/js/calendar-date.js"></script>
    <script type="module" src="../assets/js/calendar-events.js"></script>
    <script type="module" src="../assets/js/calendar-popup.js"></script>

</body>
</html>