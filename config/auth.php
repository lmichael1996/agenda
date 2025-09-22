<?php
/**
 * Autenticazione con sicurezza avanzata
 */

// Headers di sicurezza
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');

// Controllo accesso diretto
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../public/login.php');
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/captcha.php';

// Rate limiting base
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt'] = 0;
}

// Troppi tentativi (max 5 in 10 minuti)
if ($_SESSION['login_attempts'] >= 5 && (time() - $_SESSION['last_attempt']) < 600) {
    $_SESSION['login_error'] = 'Troppi tentativi. Riprova tra 10 minuti.';
    header('Location: ../public/login.php');
    exit;
}

try {
    // Verifica CSRF
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('Token di sicurezza non valido');
    }
    
    // Input con validazione avanzata
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        throw new Exception('Username e password obbligatori');
    }
    
    // Controlli di sicurezza input
    if (strlen($username) > 30 || strlen($password) > 100) {
        throw new Exception('Credenziali non valide');
    }
    
    if (preg_match('/[<>"\'\\/]/', $username)) {
        throw new Exception('Username contiene caratteri non validi');
    }
    
    // Verifica CAPTCHA
    $challengeId = $_POST['challenge_id'] ?? '';
    $captchaSolved = $_POST['captcha_solved'] ?? '';
    
    if (!CaptchaManager::verifyCaptcha($challengeId, $captchaSolved)) {
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt'] = time();
        throw new Exception('Verifica CAPTCHA fallita');
    }
    
    // Credenziali con hash sicuri
    $validUsers = [
        'admin' => password_hash('admin123', PASSWORD_ARGON2ID),
        'user' => password_hash('password', PASSWORD_ARGON2ID)
    ];
    
    if (!isset($validUsers[$username]) || !password_verify($password, $validUsers[$username])) {
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt'] = time();
        throw new Exception('Credenziali non valide');
    }
    
    // Login riuscito - reset tentativi
    unset($_SESSION['login_attempts'], $_SESSION['last_attempt']);
    
    // Sicurezza sessione
    session_regenerate_id(true);
    $_SESSION['user_id'] = $username;
    $_SESSION['login_time'] = time();
    $_SESSION['login_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $_SESSION['user_agent'] = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
    $_SESSION['just_authenticated'] = true;
    
    // Log sicurezza
    error_log("Successful login: $username from " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    
    // Redirect sicuro al dashboard
    ?>
    <html>
    <head>
        <title>Accesso in corso...</title>
        <meta http-equiv="X-Frame-Options" content="DENY">
        <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'unsafe-inline'">
    </head>
    <body>
        <script>
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
    error_log("Failed login attempt: " . ($username ?? 'unknown') . " from " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . " - " . $e->getMessage());
    
    $_SESSION['login_error'] = $e->getMessage();
    header('Location: ../public/login.php');
    exit;
}
?>