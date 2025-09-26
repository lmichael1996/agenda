<?php
/**
 * Pagina di accesso negato con controlli di sicurezza
 */

// Headers di sicurezza
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Log del tentativo di accesso bloccato
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
$referer = $_SERVER['HTTP_REFERER'] ?? 'none';
error_log("Access denied page viewed - IP: $ip, UA: $userAgent, Referer: $referer");
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Accesso Vietato - Agenda</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <div class="error-box">
        <h2>🚫 Accesso Vietato</h2>
        
        <div class="error-message">
            <strong>Accesso non autorizzato</strong><br>
            La richiesta non soddisfa i criteri di sicurezza.
        </div>
        
        <div>
            <p>Per accedere all'agenda, utilizza il link corretto:</p>
            <a href="../index.php" class="home-button">
                🏠 Torna alla Home
            </a>
        </div>
        
        <div class="error-details">
            <strong>Possibili cause:</strong><br>
            • Accesso da link esterno non autorizzato<br>
            • Parametri URL non validi<br>
            • Metodo di richiesta non consentito<br>
            • Browser o User-Agent non valido
        </div>
        
        <div class="support-text">
            Per assistenza contatta l'amministratore di sistema
        </div>
    </div>
</body>
</html>