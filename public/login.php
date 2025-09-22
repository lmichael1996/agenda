<?php
// Carica configurazione
require_once '../config/config.php';

// Genera token CSRF
$csrfToken = generateCSRFToken();

// Genera CAPTCHA
try {
    require_once '../config/captcha.php';
    $captcha = CaptchaManager::generateCaptcha();
} catch (Exception $e) {
    $captcha = ['type' => 'simple'];
}

// Controlla se c'è un errore di login
$loginError = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);

// Conta tentativi falliti (stesso sistema di auth.php)
$loginAttempts = $_SESSION['login_attempts'] ?? 0;
$lastAttempt = $_SESSION['last_attempt'] ?? 0;

// Calcola se è bloccato
$isBlocked = $loginAttempts >= 5 && (time() - $lastAttempt) < 600;
$remainingTime = $isBlocked ? ceil((600 - (time() - $lastAttempt)) / 60) : 0;
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
                🚫 Account bloccato per sicurezza<br>
                Riprova tra <?= $remainingTime ?> minuti
            </div>
        <?php elseif ($loginAttempts > 0): ?>
            <div class="attempts-warning">
                ⚠️ Tentativi falliti: <?= $loginAttempts ?>/5
                <?php if ($loginAttempts >= 3): ?>
                    <br>Sicurezza rafforzata attivata.
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!$isBlocked): ?>
        <form method="post" action="../config/auth.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="text" name="username" placeholder="Username" required autocomplete="username">
            <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
            
            <!-- CAPTCHA Essenziale -->
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
                <input type="hidden" name="captcha_type" value="checkbox">
                <input type="hidden" name="challenge_id" value="<?= $captcha['challenge_id'] ?? '' ?>">
            </div>
            
            <input type="submit" value="Accedi">
        </form>
        <?php else: ?>
            <p style="text-align: center; color: #666; margin-top: 20px;">
                Form di login temporaneamente disabilitato per sicurezza
            </p>
        <?php endif; ?>
    </div>

    <script src="../assets/js/calendar-login.js"></script>
</body>
</html>