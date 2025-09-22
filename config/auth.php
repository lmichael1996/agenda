<?php
/**
 * Sistema di autenticazione sicuro con CAPTCHA
 */

// Controllo accesso diretto
if (!isset($_POST) || empty($_POST)) {
    header('Location: ../public/login.php');
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/captcha.php';

// Protezioni aggiuntive
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Controlli base di sicurezza
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['login_error'] = 'Metodo di richiesta non valido';
    header('Location: ../public/login.php');
    exit;
}

// Verifica CSRF con logging
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    // Log tentativo sospetto
    error_log("CSRF Attack attempt from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    $_SESSION['login_error'] = 'Token di sicurezza non valido';
    header('Location: ../public/login.php');
    exit;
}

// Rate limiting per IP (protezione brute force)
if (!checkLoginRateLimit()) {
    $_SESSION['login_error'] = 'Troppi tentativi di accesso. Riprova tra 15 minuti.';
    header('Location: ../public/login.php');
    exit;
}

try {
    // Sanitizzazione input
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $captchaType = $_POST['captcha_type'] ?? '';
    
    // Validazione input avanzata
    if (empty($username) || empty($password)) {
        throw new Exception('Username e password sono obbligatori');
    }
    
    // Controllo lunghezza per prevenire attacchi
    if (strlen($username) > 50 || strlen($password) > 128) {
        throw new Exception('Credenziali non valide');
    }
    
    // Controllo caratteri pericolosi
    if (preg_match('/[<>"\']/', $username)) {
        throw new Exception('Username contiene caratteri non validi');
    }
    
    // Verifica CAPTCHA con nuovo sistema sicuro
    if ($captchaType === 'checkbox') {
        $challengeId = $_POST['challenge_id'] ?? '';
        $captchaSolved = $_POST['captcha_solved'] ?? '';
        
        if (!CaptchaManager::verifyCaptcha($challengeId, $captchaSolved)) {
            incrementFailedAttempts();
            throw new Exception('Verifica CAPTCHA fallita. Riprova.');
        }
    } else {
        throw new Exception('Tipo CAPTCHA non valido');
    }
    
    // Sistema credenziali migliorato
    $validUsers = getUserCredentials();
    
    if (!authenticateUser($username, $password, $validUsers)) {
        incrementFailedAttempts();
        
        $attempts = $_SESSION['failed_login_attempts'] ?? 0;
        if ($attempts >= 5) {
            $_SESSION['account_locked_until'] = time() + (15 * 60);
            throw new Exception('Account temporaneamente bloccato per sicurezza');
        }
        
        // Messaggio generico per non rivelare info
        throw new Exception('Credenziali non valide');
    }
    
    // Controllo blocco account esistente
    if (isAccountLocked()) {
        $remaining = $_SESSION['account_locked_until'] - time();
        throw new Exception("Account bloccato. Riprova tra " . ceil($remaining / 60) . " minuti");
    }
    
    // Login riuscito - configurazione per accesso immediato sicuro
    cleanupFailedAttempts();
    CaptchaManager::cleanupCaptcha();
    
    // Rigenera session ID per prevenire session fixation
    session_regenerate_id(true);
    
    // Imposta variabili di sessione per accesso immediato al dashboard
    $_SESSION['user_id'] = $username;
    $_SESSION['user_authenticated'] = true;
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['login_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $_SESSION['user_agent_hash'] = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
    
    // Flag speciale per permettere l'accesso immediato (solo per questa richiesta)
    $_SESSION['just_authenticated'] = true;
    
    // Log accesso riuscito
    logSecurityEvent('successful_login', $username);
    
    // Redirect con metodo POST per mantenere autenticazione temporanea
    ?>
    <html>
    <head><title>Accesso in corso...</title></head>
    <body>
        <script>
            // Redirect automatico con POST per mantenere flag autenticazione
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '../public/dashboard.php';
            
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'just_authenticated';
            input.value = '1';
            form.appendChild(input);
            
            document.body.appendChild(form);
            form.submit();
        </script>
        <p>Accesso in corso...</p>
    </body>
    </html>
    <?php
    exit;
    
} catch (Exception $e) {
    // Log tentativo fallito
    logSecurityEvent('failed_login', $username ?? 'unknown', $e->getMessage());
    
    $_SESSION['login_error'] = $e->getMessage();
    header('Location: ../public/login.php');
    exit;
}

/**
 * Funzioni di supporto per sicurezza
 */

function getUserCredentials() {
    // In produzione questi dati dovrebbero venire da database sicuro
    return [
        'admin' => [
            'password' => password_hash('admin123', PASSWORD_ARGON2ID),
            'role' => 'administrator',
            'last_login' => null
        ],
        'user' => [
            'password' => password_hash('password', PASSWORD_ARGON2ID),
            'role' => 'user',
            'last_login' => null
        ]
    ];
}

function authenticateUser($username, $password, $validUsers) {
    if (!isset($validUsers[$username])) {
        // Simula tempo di verifica per prevenire timing attacks
        password_verify('dummy', '$argon2id$v=19$m=65536,t=4,p=3$fake$hash');
        return false;
    }
    
    return password_verify($password, $validUsers[$username]['password']);
}

function incrementFailedAttempts() {
    $_SESSION['failed_login_attempts'] = ($_SESSION['failed_login_attempts'] ?? 0) + 1;
    $_SESSION['last_failed_attempt'] = time();
}

function cleanupFailedAttempts() {
    unset(
        $_SESSION['failed_login_attempts'],
        $_SESSION['account_locked_until'],
        $_SESSION['last_failed_attempt']
    );
}

function isAccountLocked() {
    return isset($_SESSION['account_locked_until']) && time() < $_SESSION['account_locked_until'];
}

function checkLoginRateLimit() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = 'login_attempts_' . md5($ip);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [];
    }
    
    // Pulisci tentativi vecchi (oltre 15 minuti)
    $_SESSION[$key] = array_filter($_SESSION[$key], function($time) {
        return (time() - $time) < 900;
    });
    
    // Controlla limite (max 10 tentativi per IP in 15 minuti)
    if (count($_SESSION[$key]) >= 10) {
        return false;
    }
    
    // Registra tentativo corrente
    $_SESSION[$key][] = time();
    return true;
}

function logSecurityEvent($event, $username = null, $details = null) {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'username' => $username,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'details' => $details
    ];
    
    // In produzione salvare su file o database
    if (!isset($_SESSION['auth_logs'])) {
        $_SESSION['auth_logs'] = [];
    }
    
    $_SESSION['auth_logs'][] = $logEntry;
    
    // Mantieni solo ultimi 100 log
    if (count($_SESSION['auth_logs']) > 100) {
        $_SESSION['auth_logs'] = array_slice($_SESSION['auth_logs'], -100);
    }
}
?>