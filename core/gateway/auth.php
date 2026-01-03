<?php
/**
 * Authentication Handler - Endpoint per il login
 * Processa le richieste POST dal form di login
 */

// Avvia sessione
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Headers di sicurezza
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Solo POST accettato
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Auth: Blocked non-POST request from " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    http_response_code(405); // Method Not Allowed
    header('Allow: POST');
    exit('405 Method Not Allowed');
}

// Include dipendenze
require_once __DIR__ . '/../functions/captcha.php';
require_once __DIR__ . '/../functions/token.php';
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../../src/Controllers/AuthController.php';

// Inizializza controller
$authController = new AuthController($conn);

// ========== CONTROLLO RATE LIMIT ==========

$rateLimit = $authController->checkRateLimit();

if ($rateLimit['blocked']) {
    $remainingTime = $rateLimit['remaining_time'];
    $_SESSION['login_error'] = "Troppi tentativi. Riprova tra {$remainingTime} minuto" . ($remainingTime > 1 ? 'i' : '');
    error_log("Auth: Blocked due to rate limit from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    header('Location: ../../public/views/login.php');
    exit;
}

// ========== PROCESSO DI AUTENTICAZIONE ==========

try {
    // ========== VALIDAZIONE CSRF ==========
    
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('Token di sicurezza non valido');
    }
    
    // ========== VALIDAZIONE INPUT ==========
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $validation = $authController->validateInput($username, $password);
    if (!$validation['valid']) {
        throw new Exception($validation['error']);
    }
    
    // ========== VERIFICA CAPTCHA ==========
    
    $challengeId = $_POST['challenge_id'] ?? '';
    $captchaSolved = $_POST['captcha_solved'] ?? '';
    
    if (!CaptchaManager::verifyCaptcha($challengeId, $captchaSolved)) {
        $authController->incrementFailedAttempts();
        throw new Exception('Verifica anti-bot fallita');
    }
    
    // ========== AUTENTICAZIONE DATABASE ==========
    
    $user = $authController->authenticate($username, $password);
    
    if (!$user) {
        $authController->incrementFailedAttempts();
        throw new Exception('Credenziali non valide');
    }
    
    // ========== LOGIN RIUSCITO ==========
    
    // Reset tentativi e crea sessione
    $authController->resetAttempts();
    $authController->createSession($user);
    
    // Redirect alla dashboard
    header('Location: ../../public/views/dashboard.php');
    exit;
    
} catch (Exception $e) {
    // Log fallimento
    $attemptUser = $username ?? 'unknown';
    $attemptIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    error_log("Auth: Failed login attempt - user '$attemptUser' from $attemptIp - " . $e->getMessage());
    
    // Salva messaggio errore e redirect al login
    $_SESSION['login_error'] = $e->getMessage();
    header('Location: ../../public/views/login.php');
    exit;
}
?>