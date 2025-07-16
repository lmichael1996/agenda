<?php
/*
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}
*/
// Calcolo giorni della settimana corrente (lunedì-domenica)
$oggi = new DateTime();
$giornoSettimana = (int)$oggi->format('N'); // 1 (lun) - 7 (dom)
$lunedi = clone $oggi;
$lunedi->modify('-' . ($giornoSettimana - 1) . ' days');
$giorni = [];
for ($i = 0; $i < 7; $i++) {
    $giorni[] = clone $lunedi;
    $lunedi->modify('+1 day');
}

// Nomi giorni
$nomiGiorni = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];

// Intervalli da 15 minuti tra le 8:00 e le 22:00
$intervalli = [];
for ($h = 8; $h <= 22; $h++) {
    for ($m = 0; $m < 60; $m += 15) {
        $intervalli[] = sprintf('%02d:%02d', $h, $m);
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Calendario Settimanale</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: transparent;
        }
        .dashboard-container {
            width: 100vw;
            min-height: 100vh;
            background: transparent;
            margin: 0;
            padding: 0;
            overflow-x: auto;
            overflow-y: auto;
        }
        .calendar-scroll-x {
            width: 100vw;
            max-width: 100vw;
            overflow-x: auto;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            display: block;
            scrollbar-width: thin;
            scrollbar-color: #888 #eee;
        }
        .dashboard-title {
            text-align: center;
            margin: 32px 0 16px 0;
            font-family: 'Courier New', Courier, monospace;
            font-size: 2.2rem;
            letter-spacing: 1px;
        }
        .calendar-header {
            display: grid;
            grid-template-columns: 80px repeat(7, 1fr);
            min-width: 900px;
            margin: 0 auto;
            background: transparent;
        }
        .calendar-header .header-day {
            text-align: center;
            font-weight: 500;
            color: #222;
            margin-bottom: 4px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 1.1em;
            padding-bottom: 4px;
            border-bottom: 2px solid #222;
            background: #fff;
        }
        .calendar-header .header-day.today {
            color: #fff;
            background: #222;
        }
        .calendar-header .header-day .header-date {
            font-size: 0.95em;
            color: #888;
        }
        .calendar-header .header-day.today .header-date {
            color: #fff;
        }
        .calendar {
            display: grid;
            grid-template-columns: 80px repeat(7, 1fr);
            gap: 2px;
            min-width: 900px;
            margin: 0 auto;
            background: #eee;
        }
        .calendar .hour-label {
            text-align: right;
            color: #888;
            font-size: 1em;
            padding: 6px 4px;
            font-family: 'Courier New', Courier, monospace;
            background: #fff;
            border-right: 2px solid #eee;
        }
        .calendar .day {
            min-height: 38px;
            padding: 6px 2px;
            background: #fff;
        }
        .calendar .day.today {
            border: 2px solid #222;
            background: #f3f3f3;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h2 class="dashboard-title">Calendario Settimanale</h2>
        <div class="calendar-scroll-x">
            <div class="calendar-header-row">
                <div class="hour-label-empty"></div>
                <?php foreach ($giorni as $idx => $g): ?>
                    <div class="header-day<?= $g->format('Y-m-d') === $oggi->format('Y-m-d') ? ' today' : '' ?>">
                        <?= htmlspecialchars($nomiGiorni[$idx]) ?><br>
                        <span class="header-date"> <?= $g->format('d/m') ?> </span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="calendar-grid">
                <?php foreach ($intervalli as $idx => $ora): ?>
                    <div class="hour-label">
                        <?= ($idx % 4 === 0) ? $ora : '' ?>
                    </div>
                    <?php foreach ($giorni as $g): ?>
                        <div class="day<?= $g->format('Y-m-d') === $oggi->format('Y-m-d') ? ' today' : '' ?>"></div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <style>
        .calendar-scroll-x {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            display: block;
        }
        .calendar-header-row {
            display: grid;
            grid-template-columns: 60px repeat(7, 1fr);
            min-width: 1100px;
            background: #eee;
            border-radius: 6px 6px 0 0;
        }
        .hour-label-empty {
            background: transparent;
        }
        .header-day {
            text-align: center;
            font-weight: 500;
            font-size: 1.1em;
            border-bottom: 2px solid #222;
            background: #fff;
            font-family: 'Courier New', Courier, monospace;
            padding: 6px 2px 2px 2px;
            border-radius: 6px 6px 0 0;
        }
        .header-day.today {
            color: #fff;
            background: #222;
        }
        .header-date {
            font-size: 0.95em;
            color: #888;
        }
        .header-day.today .header-date {
            color: #fff;
        }
        .calendar-grid {
            display: grid;
            grid-template-columns: 60px repeat(7, 1fr);
            min-width: 1100px;
            background: #eee;
            border-radius: 0 0 6px 6px;
        }
        .hour-label {
            text-align: right;
            color: #888;
            font-size: 1.05em;
            background: #fff;
            border-right: 2px solid #eee;
            font-family: 'Courier New', Courier, monospace;
            min-width: 56px;
            max-width: 60px;
            padding: 10px 6px;
            border-bottom: 1px solid #eee;
        }
        .day {
            min-height: 36px;
            background: #fff;
            border: 1px solid #eee;
            border-radius: 0;
            transition: background 0.2s;
        }
        .day.today {
            border: 2px solid #222;
            background: #f3f3f3;
        }
        @media (max-width: 900px) {
            .calendar-header-row, .calendar-grid {
                min-width: 500px;
            }
            .header-day, .day {
                font-size: 0.95em;
                min-width: 40px;
            }
        }
    </style>
</body>
</html>
