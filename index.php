<?php
/**
 * Gateway di sicurezza - Punto di ingresso agenda
 * Controlla accessi e autorizza il passaggio al login
 */

// Avvia sessione
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Headers di sicurezza
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header('X-XSS-Protection: 1; mode=block');

// Controlli accesso
$isGet = $_SERVER['REQUEST_METHOD'] === 'GET';           // Solo richieste GET
$noParams = empty($_GET);                                // Nessun parametro
$noReferer = empty($_SERVER['HTTP_REFERER']);            // Accesso diretto
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';          // Browser valido

// Se tutti i controlli passano, autorizza l'accesso
if ($isGet && $noParams && $noReferer && !empty(trim($userAgent))) {
    $_SESSION['from_index'] = true;  // Flag di autorizzazione
    header('Location: public/login.php');
    exit;
}

// Blocca accessi non autorizzati
header('Location: public/access-denied.php');
exit;
?>
