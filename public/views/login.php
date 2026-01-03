<?php
/**
 * Login Page - Autenticazione utente con sistema anti-brute-force
 */

// Avvia sessione
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Carica configurazione (include token.php e gestisce controlli from_index)
require_once '../../core/gateway/access-control.php';

// ========== SISTEMA ANTI-BRUTE-FORCE ==========
$loginAttempts = $_SESSION['login_attempts'] ?? 0;
$lastAttempt = $_SESSION['last_attempt'] ?? 0;

// Blocco progressivo: 3-4 tentativi = 2 min, 5+ tentativi = 5 min
$blockTime = 0;
if ($loginAttempts >= 5) {
    $blockTime = 300; // 5 minuti
} elseif ($loginAttempts >= 3) {
    $blockTime = 120; // 2 minuti
}

$isBlocked = $blockTime > 0 && (time() - $lastAttempt) < $blockTime;
$remainingTime = $isBlocked ? ceil(($blockTime - (time() - $lastAttempt)) / 60) : 0;

// Reset tentativi se è passato abbastanza tempo
if (!$isBlocked && $loginAttempts > 0 && (time() - $lastAttempt) > 600) {
    $_SESSION['login_attempts'] = 0;
    $loginAttempts = 0;
}

// ========== GENERA TOKEN E CAPTCHA ==========
$csrfToken = generateCSRFToken();

try {
    require_once '../../core/functions/captcha.php';
    $captcha = CaptchaManager::generateCaptcha();
} catch (Exception $e) {
    error_log("Login: Captcha generation failed - " . $e->getMessage());
    $captcha = ['type' => 'simple', 'challenge_id' => uniqid()];
}

// ========== MESSAGGI DI ERRORE ==========
$loginError = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Agenda</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <div class="login-box">
        <h2>Login</h2>
        
        <?php if (!empty($loginError)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($loginError); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($isBlocked): ?>
            <div class="attempts-warning">
                � Accesso temporaneamente limitato<br>
                Riprova tra <?= $remainingTime ?> minuto<?= $remainingTime > 1 ? 'i' : '' ?>
            </div>
        <?php elseif ($loginAttempts > 0): ?>
            <div class="attempts-info">
                ⚠️ Tentativi: <?= $loginAttempts ?>/5
                <br><small>Sistema di sicurezza attivo</small>
            </div>
        <?php endif; ?>
        
        <?php if (!$isBlocked): ?>
        <form method="post" action="../../core/gateway/auth.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="text" name="username" placeholder="Username" required autocomplete="username">
            <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
            
            <!-- CAPTCHA Sempre Presente -->
            <div class="captcha-container">
                <div class="captcha-header" id="captcha-header">
                    <div class="recaptcha-checkbox">
                        <div class="checkbox-container">
                            <div class="custom-checkbox" id="custom-checkbox">
                                <div class="spinner" id="spinner"></div>
                            </div>
                        </div>
                        <span class="recaptcha-text">Non sono un robot</span>
                    </div>
                </div>
                
                <input type="hidden" name="captcha_solved" id="captcha-solved" value="">
                <input type="hidden" name="captcha_type" value="required">
                <input type="hidden" name="challenge_id" value="<?= $captcha['challenge_id'] ?? '' ?>">
            </div>
            
            <input type="submit" value="Accedi">
        </form>
        <?php else: ?>
            <div class="temporary-block">
                <p>🕐 Accesso temporaneamente limitato per sicurezza</p>
                <small>I tentativi di accesso verranno riattivati automaticamente</small>
            </div>
        <?php endif; ?>
    </div>

    <script src="../assets/js/calendar-login.js"></script>
</body>
</html>