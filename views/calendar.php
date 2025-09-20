<?php
/**
 * Vista del calendario settimanale
 */

$days = CalendarUtils::getCurrentWeekDays();
$dayNames = CalendarUtils::getDayNames();
$intervals = CalendarUtils::generateTimeIntervals();
$today = new DateTime();

// Calcola ora attuale arrotondata al quarto d'ora precedente
$nowHour = (int)$today->format('H');
$nowMin = (int)$today->format('i');
$roundedMin = floor($nowMin / CALENDAR_INTERVAL_MINUTES) * CALENDAR_INTERVAL_MINUTES;
$nowTime = sprintf('%02d:%02d', $nowHour, $roundedMin);
?>

<div class="calendar-header-row">
    <div class="hour-label-empty">Orario:</div>
    
    <?php foreach ($days as $idx => $day): ?>
        <div class="header-day<?= CalendarUtils::isToday($day) ? ' today' : '' ?>" 
             id="header-day-<?= $idx ?>">
            <?= htmlspecialchars($dayNames[$idx]) ?><br>
            <span class="header-date"><?= $day->format('d/m/Y') ?></span>
        </div>
    <?php endforeach; ?>
</div>

<div class="calendar-grid">
    <?php foreach ($intervals as $time): ?>
        <?php $isPastOrNow = ($time <= $nowTime); ?>
        <div class="hour-label<?= $isPastOrNow ? ' hour-label-past' : '' ?>">
            <span class="hour-label-time"><?= $time ?></span>
        </div>

        <?php foreach ($days as $day): ?>
            <div class="day<?= CalendarUtils::isToday($day) ? ' today' : '' ?>"
                 data-date="<?= CalendarUtils::formatDateForHtml($day) ?>"
                 data-time="<?= $time ?>">
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>