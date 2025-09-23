<?php
/**
 * Logout con pulizia totale del sistema
 * Reset completo: sessioni, cookie, cache, file temporanei
 * Riporta il sistema allo stato iniziale "come nuovo"
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

// Pulizia completa e reset totale del sistema
$_SESSION = array();

// Cancella cookie di sessione se presente
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Cancella tutti i cookie dell'applicazione
$cookiesToClear = ['PHPSESSID', 'auth_token', 'user_pref', 'captcha_state'];
foreach ($cookiesToClear as $cookieName) {
    if (isset($_COOKIE[$cookieName])) {
        setcookie($cookieName, '', time() - 3600, '/');
        setcookie($cookieName, '', time() - 3600, '/', $_SERVER['HTTP_HOST']);
    }
}

// Distruggi la sessione
session_destroy();

// Pulizia file temporanei di sessione (se accessibili)
try {
    $sessionPath = session_save_path() ?: sys_get_temp_dir();
    $sessionName = session_name();
    $pattern = $sessionPath . '/sess_*';
    
    // Tenta di pulire i file di sessione dell'utente corrente
    $files = glob($pattern);
    if ($files) {
        foreach ($files as $file) {
            if (is_writable($file)) {
                @unlink($file);
            }
        }
    }
} catch (Exception $e) {
    // Ignora errori di pulizia file temporanei
}

// Cancella eventuali header di autenticazione
header_remove('Authorization');
header_remove('WWW-Authenticate');

// Headers di sicurezza e reset cache
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Cache-Control: no-cache, no-store, must-revalidate, private');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
header('Clear-Site-Data: "cache", "cookies", "storage"');

// Reset completo: torna all'index per ripartire da zero
header('Location: ../index.php?from=logout');
exit;
?>