<?php
/**
 * Redirect per accesso negato
 * Invece di mostrare una pagina di errore, reindirizza al login con messaggio
 */

// Avvia sessione se necessario
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log del tentativo di accesso bloccato
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
$referer = $_SERVER['HTTP_REFERER'] ?? 'none';
error_log("Access denied - redirecting to login - IP: $ip, UA: $userAgent, Referer: $referer");

// Imposta messaggio di errore
$_SESSION['login_error'] = 'Accesso non autorizzato. Effettua il login per continuare.';

// Redirect al login
header('Location: login.php');
exit;
