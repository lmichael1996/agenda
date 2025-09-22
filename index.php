<?php
/**
 * Punto di ingresso applicazione Agenda
 * @author Michael Leanza
 */

// Inizializza sessione solo se non già attiva
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Header sicurezza
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header('X-XSS-Protection: 1; mode=block');

// Controlli accesso base
$isGet = $_SERVER['REQUEST_METHOD'] === 'GET';
$noParams = empty($_GET);
$noReferer = empty($_SERVER['HTTP_REFERER']);
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

if ($isGet && $noParams && $noReferer && !empty(trim($userAgent))) {
    $_SESSION['from_index'] = true;
    header('Location: public/login.php');
    exit;
}

http_response_code(403);
echo 'Accesso non autorizzato.';
exit;
?>
