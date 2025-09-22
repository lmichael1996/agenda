<?php
/**
 * Configurazione semplificata Agenda
 */

// Impostazioni base
date_default_timezone_set('Europe/Rome');
session_start();

// Costanti calendario
define('CALENDAR_START_HOUR', 8);
define('CALENDAR_END_HOUR', 22);
define('CALENDAR_INTERVAL_MINUTES', 15);

// Funzioni utility calendario
function setSecurityHeaders() {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: no-referrer');
    header('X-XSS-Protection: 1; mode=block');
}
?>