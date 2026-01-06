<?php
/**
 * Funzioni utility per il calendario
 */

// Carica impostazioni dal database
function loadCalendarSettings() {
    static $settings = null;
    
    if ($settings === null) {
        try {
            require_once __DIR__ . '/../Database/Connection.php';
            $db = getDBConnection();
            $stmt = $db->prepare('SELECT * FROM settings WHERE id = 1');
            $stmt->execute();
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$settings) {
                // Valori di default se non ci sono impostazioni
                $settings = [
                    'opening_time' => '09:00:00',
                    'closing_time' => '18:00:00',
                    'lunch_break_enabled' => 0,
                    'break_start' => '12:30:00',
                    'break_end' => '13:30:00'
                ];
            }
        } catch (Exception $e) {
            error_log("Errore caricamento impostazioni calendario: " . $e->getMessage());
            // Valori di default in caso di errore
            $settings = [
                'opening_time' => '09:00:00',
                'closing_time' => '18:00:00',
                'lunch_break_enabled' => 0,
                'break_start' => '12:30:00',
                'break_end' => '13:30:00'
            ];
        }
    }
    
    return $settings;
}

// Definizione costanti calendario basate su impostazioni DB
$calendarSettings = loadCalendarSettings();
$startTime = explode(':', $calendarSettings['opening_time']);
$endTime = explode(':', $calendarSettings['closing_time']);

if (!defined('CALENDAR_START_HOUR')) {
    define('CALENDAR_START_HOUR', (int)$startTime[0]);
}
if (!defined('CALENDAR_END_HOUR')) {
    define('CALENDAR_END_HOUR', (int)$endTime[0]);
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
    $settings = loadCalendarSettings();
    
    // Estrae ora e minuti da opening_time e closing_time
    list($startHour, $startMinute) = explode(':', $settings['opening_time']);
    list($endHour, $endMinute) = explode(':', $settings['closing_time']);
    
    $startHour = (int)$startHour;
    $startMinute = (int)$startMinute;
    $endHour = (int)$endHour;
    $endMinute = (int)$endMinute;
    
    $intervals = [];
    
    for ($h = $startHour; $h <= $endHour; $h++) {
        $minuteStart = ($h === $startHour) ? $startMinute : 0;
        $minuteEnd = ($h === $endHour) ? $endMinute : 60;
        
        for ($m = $minuteStart; $m < $minuteEnd; $m += CALENDAR_INTERVAL_MINUTES) {
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

function isClosedDay($date) {
    $settings = loadCalendarSettings();
    $dayOfWeek = (int)$date->format('N'); // 1=lunedì, 7=domenica
    
    $dayMapping = [
        1 => 'closed_monday',
        2 => 'closed_tuesday',
        3 => 'closed_wednesday',
        4 => 'closed_thursday',
        5 => 'closed_friday',
        6 => 'closed_saturday',
        7 => 'closed_sunday'
    ];
    
    $closedField = $dayMapping[$dayOfWeek];
    return isset($settings[$closedField]) && $settings[$closedField] == 1;
}

function isLunchBreak($time) {
    $settings = loadCalendarSettings();
    
    // Se la pausa pranzo non è abilitata, ritorna false
    if (!isset($settings['lunch_break_enabled']) || $settings['lunch_break_enabled'] != 1) {
        return false;
    }
    
    // Estrae ora e minuti dal time (formato HH:MM)
    list($hour, $minute) = explode(':', $time);
    $timeMinutes = (int)$hour * 60 + (int)$minute;
    
    // Estrae ora inizio e fine pausa pranzo
    list($breakStartHour, $breakStartMinute) = explode(':', $settings['break_start']);
    $breakStartMinutes = (int)$breakStartHour * 60 + (int)$breakStartMinute;
    
    list($breakEndHour, $breakEndMinute) = explode(':', $settings['break_end']);
    $breakEndMinutes = (int)$breakEndHour * 60 + (int)$breakEndMinute;
    
    // Verifica se è durante la pausa pranzo
    return $timeMinutes >= $breakStartMinutes && $timeMinutes < $breakEndMinutes;
}

function isOutsideWorkHours($time) {
    $settings = loadCalendarSettings();
    
    // Estrae ora e minuti dal time (formato HH:MM)
    list($hour, $minute) = explode(':', $time);
    $timeMinutes = (int)$hour * 60 + (int)$minute;
    
    // Estrae ora inizio e fine
    list($startHour, $startMinute) = explode(':', $settings['opening_time']);
    $startMinutes = (int)$startHour * 60 + (int)$startMinute;
    
    list($endHour, $endMinute) = explode(':', $settings['closing_time']);
    $endMinutes = (int)$endHour * 60 + (int)$endMinute;
    
    // Verifica se è prima dell'apertura o dopo la chiusura
    if ($timeMinutes < $startMinutes || $timeMinutes > $endMinutes) {
        return true;
    }
    
    // Verifica pausa pranzo se abilitata
    if (isset($settings['lunch_break_enabled']) && $settings['lunch_break_enabled'] == 1) {
        list($breakStartHour, $breakStartMinute) = explode(':', $settings['break_start']);
        $breakStartMinutes = (int)$breakStartHour * 60 + (int)$breakStartMinute;
        
        list($breakEndHour, $breakEndMinute) = explode(':', $settings['break_end']);
        $breakEndMinutes = (int)$breakEndHour * 60 + (int)$breakEndMinute;
        
        // Verifica se è durante la pausa pranzo
        if ($timeMinutes >= $breakStartMinutes && $timeMinutes < $breakEndMinutes) {
            return true;
        }
    }
    
    return false;
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