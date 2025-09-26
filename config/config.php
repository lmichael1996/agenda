<?php
/**
 * Configurazione e sicurezza agenda - Controlli per tutte le pagine
 */

// Protezione accesso diretto
if (!defined('AGENDA_APP')) {
    define('AGENDA_APP', true);
}

// Headers di sicurezza completi per tutte le pagine  
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Configurazione sessione sicura
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

// Include funzioni token
require_once __DIR__ . '/../utils/token_functions.php';

// Sistema di controllo accessi universale
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

// Pagine pubbliche che richiedono autorizzazione da index
$publicPages = ['login.php', 'access-denied.php'];

// Pagine protette che richiedono autenticazione
$protectedPages = ['dashboard.php'];

// Popup pages che richiedono autenticazione
$popupPages = ['schedule.php', 'users.php', 'services.php', 'note.php', 'client.php'];

// =================== CONTROLLI DI ACCESSO ===================

// Controllo per pagine pubbliche (richiedono passaggio da index)
if (in_array($currentPage, $publicPages)) {
    if (empty($_SESSION['from_index'])) {
        error_log("Blocked direct access to $currentPage from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        header('Location: ' . ($currentDir === 'public' ? '../index.php' : '../index.php'));
        exit;
    }
    
    // Verifica validità temporale dell'accesso (max 10 minuti)
    $accessTime = $_SESSION['access_time'] ?? 0;
    if (time() - $accessTime > 600) {
        unset($_SESSION['from_index'], $_SESSION['access_time']);
        error_log("Expired access token for $currentPage");
        header('Location: ' . ($currentDir === 'public' ? '../index.php' : '../index.php'));
        exit;
    }
}

// Controllo per pagine protette (dashboard)
if (in_array($currentPage, $protectedPages)) {
    if (!isset($_SESSION['user_id'])) {
        error_log("Unauthenticated access to $currentPage from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        $_SESSION['login_error'] = 'Accesso richiesto per visualizzare questa pagina';
        header('Location: login.php');
        exit;
    }
    
    // Verifica scadenza sessione (max 2 ore)
    $loginTime = $_SESSION['login_time'] ?? 0;
    if (time() - $loginTime > 7200) {
        error_log("Session expired for user {$_SESSION['user_id']} accessing $currentPage");
        session_destroy();
        session_start();
        $_SESSION['login_error'] = 'Sessione scaduta, effettua nuovamente il login';
        header('Location: login.php');
        exit;
    }
    
    // Anti session hijacking completo - IP e User Agent
    $currentIp = $_SERVER['REMOTE_ADDR'] ?? '';
    $sessionIp = $_SESSION['login_ip'] ?? '';
    
    // Controllo User Agent (anti session hijacking)
    if (isset($_SESSION['user_agent_hash'])) {
        $currentUserAgentHash = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
        if (!hash_equals($_SESSION['user_agent_hash'], $currentUserAgentHash)) {
            error_log("User Agent mismatch for user {$_SESSION['user_id']}, forcing re-login");
            session_destroy();
            session_start();
            $_SESSION['login_error'] = 'Sessione non valida per motivi di sicurezza';
            header('Location: login.php');
            exit;
        }
    }
    
    // Solo se l'IP di sessione è impostato e diverso
    if (!empty($sessionIp) && $currentIp !== $sessionIp) {
        // Log ma non bloccare immediatamente - potrebbe essere cambio rete legittimo
        error_log("IP change detected for user {$_SESSION['user_id']}: $sessionIp -> $currentIp");
        
        // Verifica se il cambio IP è troppo frequente (possibile hijacking)
        $ipChanges = $_SESSION['ip_changes'] ?? 0;
        $lastIpChange = $_SESSION['last_ip_change'] ?? 0;
        
        if (time() - $lastIpChange < 300) { // Meno di 5 minuti dall'ultimo cambio
            $ipChanges++;
        } else {
            $ipChanges = 1; // Reset se è passato molto tempo
        }
        
        $_SESSION['ip_changes'] = $ipChanges;
        $_SESSION['last_ip_change'] = time();
        $_SESSION['login_ip'] = $currentIp; // Aggiorna IP corrente
        
        // Solo dopo 3 cambi IP rapidi, considera sospetto
        if ($ipChanges >= 3) {
            error_log("Suspicious IP changes for user {$_SESSION['user_id']}, forcing re-login");
            session_destroy();
            session_start();
            $_SESSION['login_error'] = 'Attività sospetta rilevata. Effettua nuovamente il login.';
            header('Location: login.php');
            exit;
        }
    }
    
    // Gestione flag di autenticazione POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['just_authenticated'])) {
        unset($_POST['just_authenticated']);
        error_log("User {$_SESSION['user_id']} successfully accessed dashboard after authentication");
    }
}

// Controllo per popup (richiedono autenticazione)
if (($currentDir === 'popup' && in_array($currentPage, $popupPages)) || 
    (in_array($currentPage, $popupPages))) {
    
    if (!isset($_SESSION['user_id'])) {
        error_log("Blocked popup access to $currentPage from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        echo '<script>alert("Sessione scaduta"); window.close();</script>';
        exit;
    }
    
    // Verifica scadenza sessione per popup
    $loginTime = $_SESSION['login_time'] ?? 0;
    if (time() - $loginTime > 7200) {
        session_destroy();
        error_log("Session expired for popup $currentPage");
        echo '<script>alert("Sessione scaduta"); window.close();</script>';
        exit;
    }
}

// =================== LOGGING E MONITORAGGIO ===================

// Log accessi validi per monitoraggio
if (isset($_SESSION['user_id'])) {
    $lastActivity = $_SESSION['last_activity'] ?? 0;
    if (time() - $lastActivity > 300) { // Log ogni 5 minuti
        error_log("User activity: {$_SESSION['user_id']} on $currentPage from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        $_SESSION['last_activity'] = time();
    }
}

// Rate limiting globale per IP
$globalRateKey = 'global_rate_' . hash('sha256', $_SERVER['REMOTE_ADDR'] ?? '');
$globalRequests = $_SESSION[$globalRateKey] ?? 0;
$globalRateTime = $_SESSION[$globalRateKey . '_time'] ?? 0;

if (time() - $globalRateTime > 3600) {
    $_SESSION[$globalRateKey] = 1;
    $_SESSION[$globalRateKey . '_time'] = time();
} else {
    $_SESSION[$globalRateKey]++;
}

// Blocco per eccesso di richieste (max 100/ora)
if ($_SESSION[$globalRateKey] > 100) {
    error_log("Global rate limit exceeded from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    header('HTTP/1.1 429 Too Many Requests');
    echo '<h1>429 - Too Many Requests</h1><p>Limite di richieste superato. Riprova più tardi.</p>';
    exit;
}
?>