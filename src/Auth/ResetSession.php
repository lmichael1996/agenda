<?php
/**
 * Reset Session - Resetta la sessione per debugging
 * File: src/Auth/ResetSession.php
 */

session_start();

// Distruggi completamente la sessione
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

echo "✅ Sessione resettata con successo!<br><br>";
echo "Tutti i tentativi di login sono stati azzerati.<br><br>";
echo "<a href='/public/views/login.php'>Torna al Login</a>";
?>
