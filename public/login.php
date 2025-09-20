<?php
// Carica configurazione
require_once '../config/config.php';

session_start();
if (empty($_SESSION['from_index'])) {
    http_response_code(403);
    echo 'Accesso non autorizzato.';
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Login - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="login-box">
        <h2>Login</h2>
        <form method="post" action="login-connection.php">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" value="Accedi">
        </form>
    </div>
</body>
</html>
