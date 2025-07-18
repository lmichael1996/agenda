<?php
/*
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}
*/
// Calculate current week days (Monday-Sunday)
$today = new DateTime();
$weekDay = (int)$today->format('N'); // 1 (Mon) - 7 (Sun)
$monday = clone $today;
$monday->modify('-' . ($weekDay - 1) . ' days');
$days = [];
for ($i = 0; $i < 7; $i++) {
    $days[] = clone $monday;
    $monday->modify('+1 day');
}

// Day names
$dayNames = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];

// 15-minute intervals from 8:00 to 22:00
$intervals = [];
for ($h = 8; $h <= 22; $h++) {
    for ($m = 0; $m < 60; $m += 15) {
        $intervals[] = sprintf('%02d:%02d', $h, $m);
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Calendario Settimanale</title>

    <link rel="stylesheet" href="style/top-menu.css">
    <link rel="stylesheet" href="style/calendar-style.css">
    <link rel="stylesheet" href="style/lateral-menu-style.css">
</head>
<body>
    
    <div id="sidebar" class="sidebar sidebar-hover">
        <a href="#">Dashboard</a>
        <a href="#">Profilo</a>
        <a href="#">Impostazioni</a>
        <a href="#">Logout</a>
    </div>

    <div class="dashboard-controls">

        <div class="controls-left">
            <select id="set-view" onmouseover="this.style.cursor='pointer'">
                <option value="week">Settimana</option>
                <option value="day">Giorno</option>
            </select>
            <input type="week" id="set-week" onmouseover="this.style.cursor='pointer'" value="<?= $today->format('Y-\WW') ?>">
            <input type="date" id="set-date" onmouseover="this.style.cursor='pointer'" value="<?= $today->format('Y-m-d') ?>" hidden>
        </div>
        
        <div class="controls-center">
            <label for="cerca" class="control-label">Cliente:</label>
            <input type="search" autocomplete="on" id="cerca" placeholder="Cerca cliente...">
            <input type="submit" onmouseover="this.style.cursor='pointer'" value="Cerca">
        </div>
        
        <div class="controls-right">
            <marquee> Non è mai troppo tardi per essere ciò che vuoi essere! </marquee>
        </div>
    
    </div>

    <div class="dashboard-container">

        <div class="calendar-header-row">
            <div class="hour-label-empty">Orario:</div>

            <?php foreach ($days as $idx => $day): ?>
                <div class = "header-day<?= $day->format('d-m-Y') === $today->format('d-m-Y') ? ' today' : '' ?>" 
                    id = "header-day-<?= $idx ?>">
                    <?= htmlspecialchars($dayNames[$idx]) ?><br>
                    <span class="header-date"> <?= $day->format('d/m/Y') ?> </span>
                </div>
            <?php endforeach; ?>

        </div>

        <div class="calendar-grid">

            <?php foreach ($intervals as $time): ?>
                <div class="hour-label"><span class="hour-label-time"><?= $time ?></span></div>

                <?php foreach ($days as $day): ?>
                    <div class = "day<?= $day->format('d-m-Y') === $today->format('d-m-Y') ? ' today' : '' ?>"
                        data-date = "<?= $day->format('d-m-Y') ?>"
                        data-time ="<?= $time ?>">
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        
        </div>

    </div>

<script src="js/lateral-menu.js"></script>
<script src="js/calendar-date.js"></script>

</body>
</html>
