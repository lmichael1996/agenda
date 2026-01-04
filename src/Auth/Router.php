<?php
/**
 * Router - Gestione routing e controllo autenticazione iniziale
 * 
 * Questo file gestisce:
 * - Verifica stato autenticazione utente
 * - Validazione sessioni attive
 * - Routing verso dashboard o login
 * - Gestione sessioni scadute
 */

// Costante di protezione accesso diretto
if (!defined('AGENDA_APP')) {
    define('AGENDA_APP', true);
}

/**
 * Inizializza e configura la sessione
 */
function initializeSession() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 0);  // 0 per HTTP, 1 per HTTPS
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Lax');
        session_start();
    }
}

/**
 * Verifica se l'utente è autenticato
 * 
 * @return bool True se l'utente è autenticato e la sessione è valida
 */
function isUserAuthenticated() {
    return isset($_SESSION['user_id']) && isset($_SESSION['login_time']);
}

/**
 * Verifica se la sessione è scaduta (timeout: 2 ore)
 * 
 * @return bool True se la sessione è scaduta
 */
function isSessionExpired() {
    if (!isset($_SESSION['login_time'])) {
        return true;
    }
    
    $sessionAge = time() - $_SESSION['login_time'];
    return $sessionAge > 7200; // 2 ore
}

/**
 * Pulisce la sessione scaduta
 */
function cleanExpiredSession() {
    $userId = $_SESSION['user_id'] ?? 'unknown';
    $sessionAge = isset($_SESSION['login_time']) ? (time() - $_SESSION['login_time']) : 0;
    
    error_log("Router: Expired session for user $userId (age: {$sessionAge}s)");
    
    session_destroy();
    session_start();
    $_SESSION['from_index'] = true;
    $_SESSION['login_error'] = 'Sessione scaduta, effettua nuovamente il login';
}

/**
 * Reindirizza alla dashboard
 */
function redirectToDashboard() {
    $userId = $_SESSION['user_id'] ?? 'unknown';
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    error_log("Router: User $userId redirected to dashboard from IP: $clientIp");
    header('Location: public/views/dashboard.php');
    exit;
}

/**
 * Reindirizza al login
 */
function redirectToLogin() {
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    error_log("Router: Guest access from IP: $clientIp - Redirecting to login");
    $_SESSION['from_index'] = true;
    header('Location: public/views/login.php');
    exit;
}

/**
 * Gestisce il routing iniziale dell'applicazione
 */
function handleRouting() {
    initializeSession();
    
    // Imposta flag per permettere accesso a login.php
    $_SESSION['from_index'] = true;
    
    // Se l'utente è autenticato
    if (isUserAuthenticated()) {
        
        // Verifica se la sessione è scaduta
        if (isSessionExpired()) {
            cleanExpiredSession();
            redirectToLogin();
        } else {
            // Sessione valida - vai alla dashboard
            redirectToDashboard();
        }
        
    } else {
        // Utente non autenticato - vai al login
        redirectToLogin();
    }
}
?>
