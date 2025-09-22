<?php
/**
 * Configurazione agenda
 * @author Michael Leanza
 */

// Definisci costante solo se non già definita
if (!defined('AGENDA_APP')) {
    define('AGENDA_APP', true);
}

// Configurazione base
date_default_timezone_set('Europe/Rome');

// Inizializza sessione solo se non già attiva
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 0,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_httponly' => true,
        'cookie_samesite' => 'Strict',
        'use_strict_mode' => true,
        'sid_length' => 48,
        'sid_bits_per_character' => 6
    ]);
}

// Sistema sicurezza
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function initSecurity() {
    // Funzione placeholder per compatibilità
    return true;
}

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