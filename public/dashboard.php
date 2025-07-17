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
    <title>Dashboard - Calendario Settimanale</title>
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

    <div class="dashboard-container">

        <div class="calendar-header-row">
            <div class="hour-label-empty">Orario:</div>
            <?php foreach ($days as $idx => $day): ?>
                <div class="header-day<?= $day->format('Y-m-d') === $today->format('Y-m-d') ? ' today' : '' ?>">
                    <?= htmlspecialchars($dayNames[$idx]) ?><br>
                    <span class="header-date"> <?= $day->format('d/m/Y') ?> </span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="calendar-grid">
            <?php foreach ($intervals as $time): ?>
                <div class="hour-label"><span class="hour-label-time"><?= $time ?></span></div>
                <?php foreach ($days as $day): ?>
                    <div class="day<?= $day->format('Y-m-d') === $today->format('Y-m-d') ? ' today' : '' ?>"></div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>

    </div>

<script src="js/lateral-menu.js"></script>

</body>
</html>
