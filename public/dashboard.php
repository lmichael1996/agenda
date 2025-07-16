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
</head>
<body>
    <div id="sidebar" class="sidebar">
        <button class="closebtn" onclick="closeSidebar()">&times;</button>
        <a href="#">Dashboard</a>
        <a href="#">Profilo</a>
        <a href="#">Impostazioni</a>
        <a href="#">Logout</a>
    </div>
    <button id="openSidebarBtn" class="openbtn" onclick="openSidebar()">&#9776; Menu</button>
    <div class="dashboard-container">
        <h2 class="dashboard-title">Calendario Settimanale</h2>
        <div class="calendar-scroll-x">
            <div class="calendar-header-row">
                <div class="hour-label-empty"></div>
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
    </div>
</style>
</style>
<script>
    // Sidebar right logic with overlay for click outside
    const sidebar = document.getElementById("sidebar");
    const openBtn = document.getElementById("openSidebarBtn");
    function openSidebar() {
        sidebar.style.width = "240px";
        sidebar.style.right = "0";
        sidebar.style.left = "auto";
        openBtn.style.display = "none";
        // Overlay for click outside
        let overlay = document.createElement('div');
        overlay.id = 'sidebarOverlay';
        overlay.style.position = 'fixed';
        overlay.style.top = 0;
        overlay.style.left = 0;
        overlay.style.width = '100vw';
        overlay.style.height = '100vh';
        overlay.style.zIndex = 999;
        overlay.style.background = 'rgba(0,0,0,0.0)';
        overlay.onclick = closeSidebar;
        document.body.appendChild(overlay);
    }
    function closeSidebar() {
        sidebar.style.width = "0";
        openBtn.style.display = "block";
        let overlay = document.getElementById('sidebarOverlay');
        if (overlay) overlay.remove();
    }
</script>
</style>
<style>
    .sidebar {
        height: 100%;
        width: 0;
        position: fixed;
        z-index: 1000;
        top: 0;
        right: 0;
        left: auto;
        background-color: #222;
        overflow-x: hidden;
        transition: 0.3s;
        padding-top: 60px;
        box-shadow: -2px 0 8px rgba(0,0,0,0.08);
    }
    .sidebar a {
        padding: 12px 24px;
        text-decoration: none;
        font-size: 1.1em;
        color: #fff;
        display: block;
        transition: 0.2s;
        font-family: 'Courier New', Courier, monospace;
    }
    .sidebar a:hover {
        background: #444;
    }
    .sidebar .closebtn {
        position: absolute;
        top: 10px;
        right: 18px;
        font-size: 2em;
        background: none;
        border: none;
        color: #fff;
        cursor: pointer;
    }
    .openbtn {
        font-size: 1.3em;
        cursor: pointer;
        background: #222;
        color: #fff;
        border: none;
        padding: 8px 18px;
        border-radius: 6px;
        position: fixed;
        top: 18px;
        right: 18px;
        left: auto;
        z-index: 1100;
        transition: background 0.2s;
    }
    .openbtn:hover {
        background: #444;
    }
    @media (max-width: 600px) {
        .sidebar { padding-top: 40px; }
        .openbtn { top: 8px; right: 8px; left: auto; padding: 6px 12px; font-size: 1em; }
    }
</style>
</body>
</html>
