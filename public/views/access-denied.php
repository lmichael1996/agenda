<?php
/**
 * Pagina di Accesso Negato - Errore di Sicurezza
 * Mostra un messaggio di errore senza possibilità di tornare al login
 */

// Log del tentativo di accesso bloccato
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
$referer = $_SERVER['HTTP_REFERER'] ?? 'none';
$timestamp = date('Y-m-d H:i:s');

error_log("SECURITY: Access denied - IP: $ip, UA: $userAgent, Referer: $referer, Time: $timestamp");

// Impedisci cache della pagina di errore
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Status HTTP 403 Forbidden
http_response_code(403);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accesso Negato - Agenda</title>
    <link rel="stylesheet" href="/public/assets/css/login.css">
</head>
<body>
    <div class="login-box">
        <h2>🚫 Accesso Negato</h2>
        
        <div class="error-message">
            <strong>Non disponi dei permessi necessari</strong><br><br>
            Il tuo tentativo di accesso è stato registrato per motivi di sicurezza.
        </div>
        
        <div class="temporary-block">
            <p><strong>Codice Errore:</strong> 403 - Forbidden</p>
            <p><strong>IP:</strong> <?php echo htmlspecialchars($ip); ?></p>
            <small><?php echo htmlspecialchars($timestamp); ?></small>
        </div>
    </div>
</body>
</html>