<?php
/**
 * Index Gateway - Controlli di sicurezza per l'accesso a index.php
 * Verifica metodo, user agent, IP, parametri GET e gestisce logout
 */

// Avvia sessione se non già attiva
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Headers di sicurezza
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Variabili di controllo
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? '';
$fromLogout = isset($_GET['from']) && $_GET['from'] === 'logout';

// ========== CONTROLLI DI SICUREZZA BASE ==========

// 1. Solo GET
if ($requestMethod !== 'GET') {
    error_log("Index: Blocked non-GET request ($requestMethod) from IP: $remoteAddr");
    header('Location: public/views/access-denied.php');
    exit;
}

// 2. User Agent valido (anti-bot base)
$hasValidUserAgent = !empty(trim($userAgent)) && strlen($userAgent) > 10 && strlen($userAgent) < 500;
if (!$hasValidUserAgent) {
    error_log("Index: Blocked invalid user agent from IP: $remoteAddr");
    header('Location: public/views/access-denied.php');
    exit;
}

// 3. IP valido
if (!filter_var($remoteAddr, FILTER_VALIDATE_IP)) {
    error_log("Index: Invalid IP format: $remoteAddr");
    header('Location: public/views/access-denied.php');
    exit;
}

// 4. Parametri GET limitati (solo 'from' opzionale)
$allowedParams = ['from'];
$extraParams = array_diff(array_keys($_GET), $allowedParams);
if (!empty($extraParams)) {
    error_log("Index: Extra GET parameters detected from IP: $remoteAddr - " . implode(', ', $extraParams));
    header('Location: public/views/access-denied.php');
    exit;
}

// ========== ROUTING LOGICO ==========

// Se logout esplicito, pulisci completamente la sessione
if ($fromLogout) {
    session_destroy();
    session_start(); // Riavvia sessione pulita
    error_log("Index: Logout processed for IP: $remoteAddr");
}

// Flag di autorizzazione per accesso alle pagine pubbliche (login, access-denied)
$_SESSION['from_index'] = true;
$_SESSION['access_time'] = time();
$_SESSION['access_ip'] = $remoteAddr;

// ROUTING INTELLIGENTE: 
// - Utenti autenticati → dashboard
// - Altri → login
if (isset($_SESSION['user_id']) && !$fromLogout) {
    error_log("Index: Authenticated user {$_SESSION['user_id']}, redirecting to dashboard");
    header('Location: public/views/dashboard.php');
} else {
    error_log("Index: Redirecting to login from IP: $remoteAddr");
    header('Location: public/views/login.php');
}
exit;
?>
