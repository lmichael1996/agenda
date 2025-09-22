<?php
/**
 * Logout sicuro con pulizia completa sessione
 */

// Inizia sessione se non già attiva
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log dell'evento di logout
if (isset($_SESSION['user_id'])) {
    $username = $_SESSION['user_id'];
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => 'logout',
        'username' => $username,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    // Log in sessione (in produzione usare file o database)
    if (!isset($_SESSION['auth_logs'])) {
        $_SESSION['auth_logs'] = [];
    }
    $_SESSION['auth_logs'][] = $logEntry;
}

// Pulizia completa della sessione
$_SESSION = array();

// Cancella cookie di sessione se presente
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Distruggi la sessione
session_destroy();

// Cancella eventuali header di autenticazione
header_remove('Authorization');

// Headers di sicurezza
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Inizia nuova sessione per flag autorizzazione
session_start();
$_SESSION['from_index'] = true;  // Autorizza accesso al login

// Redirect diretto al login
header('Location: login.php');
exit;
?>