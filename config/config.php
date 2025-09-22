<?php
/**
 * Configurazione e sicurezza agenda
 */

// Protezione accesso diretto
if (!defined('AGENDA_APP')) {
    define('AGENDA_APP', true);
}

// Sessione semplice
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sistema di controllo accessi
$currentPage = basename($_SERVER['PHP_SELF']);

// Pagine pubbliche (richiedono passaggio da index)
$publicPages = ['login.php'];

// Pagine protette (richiedono autenticazione)
$protectedPages = ['dashboard.php'];

// Controllo per pagine pubbliche
if (in_array($currentPage, $publicPages) && empty($_SESSION['from_index'])) {
    header('Location: ../index.php');
    exit;
}

// Controllo per pagine protette - SICUREZZA MASSIMA
if (in_array($currentPage, $protectedPages)) {
    // Per massima sicurezza, elimina sempre l'autenticazione dopo ogni richiesta
    // Questo forza il re-login ad ogni accesso
    
    // Pulisce SEMPRE l'autenticazione (tranne nella stessa richiesta POST)
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['just_authenticated'])) {
        // Mantieni solo dati essenziali di sessione
        $csrf_token = $_SESSION['csrf_token'] ?? null;
        $from_index = $_SESSION['from_index'] ?? null;
        
        // Pulisci sessione mantenendo solo token sicurezza
        $_SESSION = array();
        if ($csrf_token) $_SESSION['csrf_token'] = $csrf_token;
        if ($from_index) $_SESSION['from_index'] = $from_index;
        
        $_SESSION['login_error'] = 'Per accedere inserisci le tue credenziali';
        header('Location: login.php');
        exit;
    }
    
    // Se arriva qui, è una richiesta POST con just_authenticated
    // Rimuovi il flag per la prossima richiesta
    unset($_SESSION['just_authenticated']);
}

// Token CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Costanti per il calendario
define('CALENDAR_START_HOUR', 8);
define('CALENDAR_END_HOUR', 22);
define('CALENDAR_INTERVAL_MINUTES', 15);
?>