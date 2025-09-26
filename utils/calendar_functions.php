<?php
/**
 * Funzioni utility per il calendario
 */

// Definizione costanti calendario
if (!defined('CALENDAR_START_HOUR')) {
    define('CALENDAR_START_HOUR', 8);
}
if (!defined('CALENDAR_END_HOUR')) {
    define('CALENDAR_END_HOUR', 22);
}
if (!defined('CALENDAR_INTERVAL_MINUTES')) {
    define('CALENDAR_INTERVAL_MINUTES', 15);
}

function getCurrentWeekDays($baseDate = null) {
    $today = $baseDate ? new DateTime($baseDate) : new DateTime();
    $weekDay = (int)$today->format('N');
    $monday = clone $today;
    $monday->modify('-' . ($weekDay - 1) . ' days');
    
    $days = [];
    for ($i = 0; $i < 7; $i++) {
        $days[] = clone $monday;
        $monday->modify('+1 day');
    }
    return $days;
}

function generateTimeIntervals() {
    $intervals = [];
    for ($h = CALENDAR_START_HOUR; $h <= CALENDAR_END_HOUR; $h++) {
        for ($m = 0; $m < 60; $m += CALENDAR_INTERVAL_MINUTES) {
            $intervals[] = sprintf('%02d:%02d', $h, $m);
        }
    }
    return $intervals;
}

function getDayNames() {
    return ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];
}

function isToday($date) {
    $today = new DateTime();
    return $date->format('d-m-Y') === $today->format('d-m-Y');
}

function formatDateForHtml($date) {
    return $date->format('d-m-Y');
}

function getCurrentTime() {
    $now = new DateTime();
    $nowHour = (int)$now->format('H');
    $nowMin = (int)$now->format('i');
    $roundedMin = floor($nowMin / CALENDAR_INTERVAL_MINUTES) * CALENDAR_INTERVAL_MINUTES;
    return sprintf('%02d:%02d', $nowHour, $roundedMin);
}
?>