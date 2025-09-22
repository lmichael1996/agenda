<?php
/**
 * Gestione autenticazione
 */

// Carica configurazione
require_once __DIR__ . '/simple_config.php';

// MODALITÀ TEST - Per sviluppo, autenticazione sempre attiva
$TEST_MODE = true;

if ($TEST_MODE) {
    // Modalità test - login automatico
    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = 'test_user';
    header('Location: ../public/dashboard.php');
    exit;
}

// Modalità produzione - autenticazione vera
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    try {
        // Validazione base
        if (empty($username) || empty($password)) {
            throw new Exception('Username e password sono obbligatori');
        }
        
        // Credenziali di test (sostituire con database)
        $valid_users = [
            'admin' => 'password',
            'user' => 'test123'
        ];
        
        if (isset($valid_users[$username]) && $valid_users[$username] === $password) {
            // Login riuscito
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['login_time'] = time();
            
            // Rimuovi flag from_index se presente
            unset($_SESSION['from_index']);
            
            header('Location: ../public/dashboard.php');
            exit;
        } else {
            throw new Exception('Credenziali non valide');
        }
        
    } catch (Exception $e) {
        $error = htmlspecialchars($e->getMessage());
        // Redirect al login con errore
        $_SESSION['login_error'] = $error;
        header('Location: ../public/login.php');
        exit;
    }
} else {
    // Accesso diretto senza POST - redirect al login
    header('Location: ../public/login.php');
    exit;
}
?>