<?php
/**
 * Utility per la gestione del calendario
 */

class CalendarUtils {
    
    /**
     * Calcola i giorni della settimana corrente (Lunedì-Domenica)
     */
    public static function getCurrentWeekDays($baseDate = null) {
        $today = $baseDate ? new DateTime($baseDate) : new DateTime();
        $weekDay = (int)$today->format('N'); // 1 (Mon) - 7 (Sun)
        $monday = clone $today;
        $monday->modify('-' . ($weekDay - 1) . ' days');
        
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $days[] = clone $monday;
            $monday->modify('+1 day');
        }
        
        return $days;
    }
    
    /**
     * Genera gli intervalli di tempo per il calendario
     */
    public static function generateTimeIntervals() {
        $intervals = [];
        for ($h = CALENDAR_START_HOUR; $h <= CALENDAR_END_HOUR; $h++) {
            for ($m = 0; $m < 60; $m += CALENDAR_INTERVAL_MINUTES) {
                $intervals[] = sprintf('%02d:%02d', $h, $m);
            }
        }
        return $intervals;
    }
    
    /**
     * Ottieni nomi dei giorni della settimana
     */
    public static function getDayNames() {
        return ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];
    }
    
    /**
     * Formatta data per gli attributi HTML
     */
    public static function formatDateForHtml($date) {
        return $date->format('d-m-Y');
    }
    
    /**
     * Controlla se è il giorno corrente
     */
    public static function isToday($date) {
        $today = new DateTime();
        return $date->format('d-m-Y') === $today->format('d-m-Y');
    }
}