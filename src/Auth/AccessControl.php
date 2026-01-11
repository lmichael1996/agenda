<?php
/**
 * Access Control - Sistema di sicurezza e autenticazione
 * 
 * Questo file gestisce:
 * - Headers di sicurezza HTTP
 * - Configurazione sessioni sicure
 * - Controllo accesso pagine pubbliche e protette
 * - Validazione autenticazione utenti
 * - Logging accessi
 */

// =================== CONFIGURAZIONE SICUREZZA ===================

// Costante di protezione accesso diretto
if (!defined('AGENDA_APP')) {
    define('AGENDA_APP', true);
}

// Headers di sicurezza HTTP
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Configurazione sessione sicura
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0);  // 0 per HTTP, 1 per HTTPS
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// Carica funzioni token CSRF
require_once __DIR__ . '/../Helpers/Token.php';

// =================== ANALISI CONTESTO ===================

$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));
$clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Determina percorsi redirect in base alla posizione
$isPopup = ($currentDir === 'popup');
$loginPath = $isPopup ? '../login.php' : 'login.php';
$indexPath = $isPopup ? '../../../index.php' : ($currentDir === 'views' ? '../../index.php' : '../index.php');

// =================== DEFINIZIONE PAGINE ===================

// Pagine pubbliche (accessibili senza autenticazione)
$publicPages = [
    'login.php',
    'access-denied.php',
    // Endpoint API (gestiscono internamente l'autenticazione)
    'services-api.php',
    'services-data.php'
];

// Pagine protette (richiedono autenticazione)
$protectedPages = [
    // Dashboard
    'dashboard.php',
    
    // Popup gestione dati
    'schedule.php',
    'users.php',
    'services.php',
    'notes.php',
    'clients.php',
    'appointment.php',
    'client-detail.php',
    'client-edit.php',
    'client-history.php',
    'get-client-appointments.php'
];

// =================== CONTROLLO ACCESSO PAGINE PUBBLICHE ===================

if (in_array($currentPage, $publicPages)) {
    
    // login.php richiede accesso tramite index.php (flag from_index)
    if ($currentPage === 'login.php' && empty($_SESSION['from_index'])) {
        error_log("Access Control: Blocked direct access to login.php from IP: $clientIp");
        header('Location: ' . $indexPath);
        exit;
    }
    
    // access-denied.php è sempre accessibile (pagina di errore)
    // Nessun controllo aggiuntivo richiesto
}

// =================== AGGIORNA ATTIVITÀ PER PAGINE PROTETTE ===================

else if (in_array($currentPage, $protectedPages)) {
    
    // AGGIORNA TIMESTAMP ULTIMA ATTIVITÀ (solo se autenticato)
    if (isset($_SESSION['user_id'])) {
        $_SESSION['last_activity'] = time();
        
        // LOGGING ATTIVITÀ UTENTE (ogni 5 minuti)
        $lastLog = $_SESSION['last_log'] ?? 0;
        if (time() - $lastLog > 300) {
            error_log("Access Control: User {$_SESSION['user_id']} active on $currentPage from IP: $clientIp");
            $_SESSION['last_log'] = time();
        }
    }
}
?>