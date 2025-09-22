<?php
// Carica configurazione
require_once '../config/simple_config.php';

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
    <title>Login - Agenda Settimanale</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <div class="login-box">
        <h2>Login</h2>
        <form method="post" action="../config/auth.php">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" value="Accedi">
        </form>
    </div>
</body>
</html>