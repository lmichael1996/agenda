<?php
/**
 * Autenticazione con CAPTCHA
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('AGENDA_APP')) {
    define('AGENDA_APP', true);
}

require_once __DIR__ . '/simple_config.php';
require_once __DIR__ . '/captcha.php';

// Controlli base
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['login_error'] = 'Metodo non valido';
    header('Location: ../public/login.php');
    exit;
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['login_error'] = 'Token non valido';
    header('Location: ../public/login.php');
    exit;
}

try {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $captchaType = $_POST['captcha_type'] ?? '';
    
    if (empty($username) || empty($password)) {
        throw new Exception('Username e password obbligatori');
    }
    
    // Verifica CAPTCHA
    if ($captchaType === 'checkbox') {
        if (empty($_POST['captcha_solved'])) {
            $_SESSION['failed_login_attempts'] = ($_SESSION['failed_login_attempts'] ?? 0) + 1;
            throw new Exception('Completa la verifica CAPTCHA');
        }
    }
    
    // Credenziali test
    $validUsers = [
        'admin' => password_hash('admin123', PASSWORD_ARGON2ID),
        'user' => password_hash('password', PASSWORD_ARGON2ID)
    ];
    
    if (!isset($validUsers[$username]) || !password_verify($password, $validUsers[$username])) {
        $_SESSION['failed_login_attempts'] = ($_SESSION['failed_login_attempts'] ?? 0) + 1;
        
        if ($_SESSION['failed_login_attempts'] >= 5) {
            $_SESSION['account_locked_until'] = time() + (15 * 60);
            throw new Exception('Account bloccato per 15 minuti');
        }
        
        throw new Exception('Credenziali non valide');
    }
    
    // Controllo blocco account
    if (isset($_SESSION['account_locked_until']) && time() < $_SESSION['account_locked_until']) {
        $remaining = $_SESSION['account_locked_until'] - time();
        throw new Exception("Account bloccato per altri " . ceil($remaining / 60) . " minuti");
    }
    
    // Login riuscito
    unset($_SESSION['failed_login_attempts'], $_SESSION['account_locked_until']);
    CaptchaManager::cleanupCaptcha();
    
    $_SESSION['user_id'] = $username;
    $_SESSION['user_authenticated'] = true;
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    
    header('Location: ../public/dashboard.php');
    exit;
    
} catch (Exception $e) {
    $_SESSION['login_error'] = $e->getMessage();
    header('Location: ../public/login.php');
    exit;
}
?>