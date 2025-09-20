<?php
// Carica configurazione
require_once 'config/config.php';

// Imposta headers di sicurezza
setSecurityHeaders();

session_start();

$isGet = $_SERVER['REQUEST_METHOD'] === 'GET';
$noParams = empty($_GET);
$noReferer = empty($_SERVER['HTTP_REFERER']);
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$userAgentOk = strlen(trim($userAgent)) > 0;

if ($isGet && $noParams && $noReferer && $userAgentOk) {
    $_SESSION['from_index'] = true;
    header('Location: public/login.php');
    exit;
} else {
    http_response_code(403);
    echo 'Accesso non autorizzato.';
    exit;
}
?>
