<?php
/**
 * AuthController - Gestisce l'autenticazione degli utenti
 * Delega la logica business a AuthService
 */

require_once __DIR__ . '/../Models/Auth.php';
require_once __DIR__ . '/../Services/AuthService.php';

class AuthController {
    private $service;
    
    public function __construct($db) {
        $this->service = new AuthService($db);
    }
    
    /**
     * Autentica un utente verificando username e password nel database
     * Returns user array on success, false on failure (for backward compatibility)
     */
    public function authenticate($username, $password) {
        $result = $this->service->authenticate($username, $password);
        
        // Backward compatibility: return user array directly or false
        if ($result['success'] && isset($result['data'])) {
            return $result['data']->toSessionArray();
        }
        
        return false;
    }
    
    /**
     * Verifica se l'utente è bloccato per troppi tentativi
     */
    /**
     * Verifica se l'utente è bloccato per troppi tentativi
     */
    public function checkRateLimit() {
        return $this->service->checkRateLimit();
    }
    
    /**
     * Incrementa il contatore dei tentativi falliti
     */
    public function incrementFailedAttempts() {
        return $this->service->incrementFailedAttempts();
    }
    
    /**
     * Reset dei tentativi di login
     */
    public function resetAttempts() {
        $this->service->resetFailedAttempts();
    }
    
    /**
     * Crea una sessione sicura per l'utente autenticato
     * @param array $authData - User array from authenticate() ['id', 'username', 'color']
     */
    public function createSession($authData) {
        // Rigenera ID sessione per sicurezza
        session_regenerate_id(true);
        
        // Crea Auth Model da array per passare al Service
        $auth = new Auth($authData);
        $this->service->initializeSession($auth);
        
        // Log successo
        $_SESSION['login_time'] = time();
        $_SESSION['login_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $_SESSION['user_agent_hash'] = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
        
        error_log("AuthController: Session created for user '{$authData['username']}' from {$_SESSION['login_ip']}");
    }
    
    /**
     * Valida input username e password
     */
    public function validateInput($username, $password) {
        // Verifica campi vuoti
        if (empty($username) || empty($password)) {
            return ['valid' => false, 'error' => 'Username e password obbligatori'];
        }
        
        // Validazione lunghezza
        if (strlen($username) > 50 || strlen($password) > 100) {
            return ['valid' => false, 'error' => 'Credenziali non valide'];
        }
        
        // Validazione caratteri username
        if (preg_match('/[<>"\'\\\\]/', $username)) {
            return ['valid' => false, 'error' => 'Username contiene caratteri non validi'];
        }
        
        return ['valid' => true, 'error' => ''];
    }
}
?>
