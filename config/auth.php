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
require_once __DIR__ . '/db.php';

/**
 * Autentica un utente verificando username e password nel database
 * @param string $username
 * @param string $password
 * @return bool
 */
function authenticateUser($username, $password) {
    global $conn;
    
    // Controllo connessione database
    if (!isset($conn) || $conn->connect_errno) {
        error_log('Errore connessione DB in authenticateUser: ' . ($conn ? $conn->connect_error : 'connessione non inizializzata'));
        return false;
    }
    
    // Prepared statement per sicurezza
    $stmt = $conn->prepare('SELECT password_hash, is_active FROM users WHERE username = ? LIMIT 1');
    if (!$stmt) {
        error_log('Errore preparazione query: ' . $conn->error);
        return false;
    }
    
    $stmt->bind_param('s', $username);
    if (!$stmt->execute()) {
        error_log('Errore esecuzione query: ' . $stmt->error);
        $stmt->close();
        return false;
    }
    
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Verifica se utente esiste
    if (!$user) {
        error_log("Login failed: User '$username' not found");
        return false;
    }
    
    // Verifica se utente è attivo
    if (!$user['is_active']) {
        error_log("Login failed: User '$username' is not active");
        return false;
    }
    
    // Verifica password
    return password_verify($password, $user['password_hash']);
}

// Sistema di sicurezza equilibrato
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt'] = 0;
}

// Blocco progressivo: 3 tentativi = 2 min, 5+ tentativi = 5 min
$blockTime = $_SESSION['login_attempts'] >= 5 ? 300 : ($_SESSION['login_attempts'] >= 3 ? 120 : 0);
if ($_SESSION['login_attempts'] >= 3 && (time() - $_SESSION['last_attempt']) < $blockTime) {
    $waitTime = ceil(($blockTime - (time() - $_SESSION['last_attempt'])) / 60);
    $_SESSION['login_error'] = "Accesso limitato. Riprova tra {$waitTime} minuto" . ($waitTime > 1 ? 'i' : '') . ".";
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
    
    // Verifica CAPTCHA (sempre richiesta)
    $challengeId = $_POST['challenge_id'] ?? '';
    $captchaSolved = $_POST['captcha_solved'] ?? '';
    $captchaType = $_POST['captcha_type'] ?? 'required';
    
    if (!CaptchaManager::verifyCaptcha($challengeId, $captchaSolved)) {
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt'] = time();
        throw new Exception('Verifica anti-bot fallita');
    }
    
    // Verifica credenziali tramite database usando user-api.php
    if (!authenticateUser($username, $password)) {
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