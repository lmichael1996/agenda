<?php
/**
 * Funzioni per la gestione dei token CSRF
 */

// Protezione accesso diretto
if (!defined('AGENDA_APP')) {
    define('AGENDA_APP', true);
}

/**
 * Genera un token CSRF sicuro
 * @return string Token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifica la validità di un token CSRF
 * @param string $token Token da verificare
 * @return bool True se valido, false altrimenti
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Rigenera il token CSRF (da usare dopo login/logout)
 * @return string Nuovo token CSRF
 */
function regenerateCSRFToken() {
    unset($_SESSION['csrf_token']);
    return generateCSRFToken();
}
?>