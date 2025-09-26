<?php
/**
 * Gateway di sicurezza - Punto di ingresso agenda
 * Controlla accessi e autorizza il passaggio al login
 */

// Avvia sessione
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Headers di sicurezza avanzati
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header('X-XSS-Protection: 1; mode=block');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\'; style-src \'self\' \'unsafe-inline\'');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Controlli accesso avanzati
$isGet = $_SERVER['REQUEST_METHOD'] === 'GET';           // Solo richieste GET
$fromLogout = isset($_GET['from']) && $_GET['from'] === 'logout'; // Parametro logout
$noOtherParams = count($_GET) <= 1;                      // Solo parametro from (opzionale)
$referer = $_SERVER['HTTP_REFERER'] ?? '';               // Referer
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';          // Browser valido
$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';             // IP del client
$requestUri = $_SERVER['REQUEST_URI'] ?? '';             // URI richiesta

// Controlli anti-bot e sicurezza
$hasValidUserAgent = !empty(trim($userAgent)) && strlen($userAgent) > 10 && strlen($userAgent) < 500;
$noSuspiciousChars = !preg_match('/[<>"\'\(\){}]/', $requestUri);
$validIpFormat = filter_var($remoteAddr, FILTER_VALIDATE_IP) !== false;

// Rate limiting semplice (max 10 richieste per minuto per IP)
$rateLimitKey = 'rate_limit_' . md5($remoteAddr);
$currentRequests = $_SESSION[$rateLimitKey] ?? 0;
$lastRequestTime = $_SESSION[$rateLimitKey . '_time'] ?? 0;

if (time() - $lastRequestTime > 60) {
    $_SESSION[$rateLimitKey] = 1;
    $_SESSION[$rateLimitKey . '_time'] = time();
} else {
    $_SESSION[$rateLimitKey]++;
}

$withinRateLimit = $_SESSION[$rateLimitKey] <= 10;

// Accesso diretto o da logout
$isDirectAccess = empty($_GET) && empty($referer);
$isFromLogout = $fromLogout || (!empty($referer) && (
    strpos($referer, '/logout.php') !== false ||
    strpos($referer, '/public/logout.php') !== false
));

// Se tutti i controlli passano, autorizza l'accesso
if ($isGet && $noOtherParams && ($isDirectAccess || $isFromLogout) && 
    $hasValidUserAgent && $noSuspiciousChars && $validIpFormat && $withinRateLimit) {
    
    // Log accesso autorizzato
    error_log("Authorized access from IP: $remoteAddr, User-Agent: " . substr($userAgent, 0, 100));
    
    $_SESSION['from_index'] = true;  // Flag di autorizzazione
    $_SESSION['access_time'] = time(); // Timestamp accesso
    $_SESSION['access_ip'] = $remoteAddr; // IP di accesso
    header('Location: public/login.php');
    exit;
}

// Log e blocca accessi non autorizzati
$blockReason = '';
if (!$isGet) $blockReason .= 'Method: ' . $_SERVER['REQUEST_METHOD'] . ' ';
if (!$noOtherParams) $blockReason .= 'ExtraParams ';
if (!$hasValidUserAgent) $blockReason .= 'InvalidUA ';
if (!$noSuspiciousChars) $blockReason .= 'SuspiciousChars ';
if (!$validIpFormat) $blockReason .= 'InvalidIP ';
if (!$withinRateLimit) $blockReason .= 'RateLimit ';

error_log("Blocked access from IP: $remoteAddr, Reason: $blockReason, UA: " . substr($userAgent, 0, 100));

header('Location: public/access-denied.php');
exit;
?>
