<?php
/**
 * AuthService - Logica business per l'autenticazione
 */

class AuthService {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Autentica un utente verificando username e password
     */
    public function authenticate($username, $password) {
        try {
            // Query per trovare l'utente
            $stmt = $this->db->prepare('
                SELECT id, username, password_hash, color
                FROM users 
                WHERE username = ? 
                LIMIT 1
            ');
            
            $stmt->execute([$username]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$userData) {
                error_log("AuthService: User not found - '$username'");
                return ['success' => false, 'error' => 'Credenziali non valide'];
            }
            
            // Crea oggetto Auth
            $auth = new Auth($userData);
            
            // Verifica password
            if (!$auth->verifyPassword($password)) {
                error_log("AuthService: Invalid password for user '$username'");
                return ['success' => false, 'error' => 'Credenziali non valide'];
            }
            
            // Autenticazione riuscita
            return [
                'success' => true,
                'data' => $auth
            ];
            
        } catch (PDOException $e) {
            error_log("AuthService::authenticate error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore durante l\'autenticazione'];
        }
    }
    
    /**
     * Verifica rate limit per login
     */
    public function checkRateLimit() {
        // Inizializza contatori se non esistono
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
            $_SESSION['last_attempt'] = 0;
        }
        
        $attempts = $_SESSION['login_attempts'];
        $lastAttempt = $_SESSION['last_attempt'];
        $currentTime = time();
        
        // Calcola tempo di blocco
        $blockTime = 0;
        if ($attempts >= 5) {
            $blockTime = 300; // 5 minuti
        } elseif ($attempts >= 3) {
            $blockTime = 120; // 2 minuti
        }
        
        // Verifica se è ancora bloccato
        if ($blockTime > 0) {
            $timePassed = $currentTime - $lastAttempt;
            if ($timePassed < $blockTime) {
                return [
                    'blocked' => true,
                    'remaining_time' => $blockTime - $timePassed
                ];
            } else {
                // Reset se il tempo di blocco è passato
                $_SESSION['login_attempts'] = 0;
            }
        }
        
        return [
            'blocked' => false,
            'remaining_time' => 0
        ];
    }
    
    /**
     * Incrementa contatore tentativi falliti
     */
    public function incrementFailedAttempts() {
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
        }
        
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt'] = time();
        
        return $_SESSION['login_attempts'];
    }
    
    /**
     * Reset contatore tentativi
     */
    public function resetFailedAttempts() {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt'] = 0;
    }
    
    /**
     * Inizializza sessione utente
     */
    public function initializeSession($auth) {
        $_SESSION['user'] = $auth->toSessionArray();
        $_SESSION['user_id'] = $auth->id;
        $_SESSION['username'] = $auth->username;
        $_SESSION['user_color'] = $auth->color;
        
        // Reset tentativi falliti
        $this->resetFailedAttempts();
        
        return ['success' => true, 'message' => 'Sessione inizializzata'];
    }
    
    /**
     * Distrugge sessione utente (logout)
     */
    public function destroySession() {
        // Cancella tutte le variabili di sessione
        $_SESSION = [];
        
        // Distruggi il cookie di sessione
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Distruggi la sessione
        session_destroy();
        
        return ['success' => true, 'message' => 'Sessione terminata'];
    }
    
    /**
     * Verifica se l'utente è autenticato
     */
    public function isAuthenticated() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Ottieni utente dalla sessione
     */
    public function getSessionUser() {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        return $_SESSION['user'] ?? null;
    }
}
