<?php
/**
 * AuthController - Gestisce l'autenticazione degli utenti
 */

class AuthController {
    private $conn;
    
    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }
    
    /**
     * Autentica un utente verificando username e password nel database
     * @param string $username Username da verificare
     * @param string $password Password in chiaro da verificare
     * @return array|false Array con dati utente se successo, false altrimenti
     */
    public function authenticate($username, $password) {
        // Verifica connessione database
        if (!isset($this->conn) || $this->conn->connect_errno) {
            error_log('AuthController: Database connection error - ' . 
                ($this->conn ? $this->conn->connect_error : 'not initialized'));
            return false;
        }
        
        // Query preparata per sicurezza
        $stmt = $this->conn->prepare(
            'SELECT id, username, password_hash, role, is_active 
             FROM users 
             WHERE username = ? 
             LIMIT 1'
        );
        
        if (!$stmt) {
            error_log('AuthController: Query preparation failed - ' . $this->conn->error);
            return false;
        }
        
        $stmt->bind_param('s', $username);
        
        if (!$stmt->execute()) {
            error_log('AuthController: Query execution failed - ' . $stmt->error);
            $stmt->close();
            return false;
        }
        
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        // Utente non trovato
        if (!$user) {
            error_log("AuthController: User not found - '$username'");
            return false;
        }
        
        // Utente disattivato
        if (!$user['is_active']) {
            error_log("AuthController: User inactive - '$username'");
            return false;
        }
        
        // Verifica password
        if (!password_verify($password, $user['password_hash'])) {
            error_log("AuthController: Invalid password for user '$username'");
            return false;
        }
        
        // Restituisci dati utente (senza password_hash)
        unset($user['password_hash']);
        return $user;
    }
    
    /**
     * Verifica se l'utente è bloccato per troppi tentativi
     * @return array ['blocked' => bool, 'remaining_time' => int]
     */
    public function checkRateLimit() {
        // Inizializza contatori se non esistono
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
            $_SESSION['last_attempt'] = 0;
        }
        
        // Calcola tempo di blocco (3-4 tentativi = 2min, 5+ = 5min)
        $blockTime = 0;
        if ($_SESSION['login_attempts'] >= 5) {
            $blockTime = 300; // 5 minuti
        } elseif ($_SESSION['login_attempts'] >= 3) {
            $blockTime = 120; // 2 minuti
        }
        
        // Verifica se è bloccato
        $timeSinceLastAttempt = time() - $_SESSION['last_attempt'];
        $isBlocked = $blockTime > 0 && $timeSinceLastAttempt < $blockTime;
        $remainingTime = $isBlocked ? ceil(($blockTime - $timeSinceLastAttempt) / 60) : 0;
        
        return [
            'blocked' => $isBlocked,
            'remaining_time' => $remainingTime,
            'attempts' => $_SESSION['login_attempts']
        ];
    }
    
    /**
     * Incrementa il contatore dei tentativi falliti
     */
    public function incrementFailedAttempts() {
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
        $_SESSION['last_attempt'] = time();
    }
    
    /**
     * Reset dei tentativi di login
     */
    public function resetAttempts() {
        unset($_SESSION['login_attempts'], $_SESSION['last_attempt']);
    }
    
    /**
     * Crea una sessione sicura per l'utente autenticato
     * @param array $user Dati utente
     */
    public function createSession($user) {
        // Rigenera ID sessione per sicurezza
        session_regenerate_id(true);
        
        // Salva dati utente in sessione
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'] ?? 'user';
        $_SESSION['login_time'] = time();
        $_SESSION['login_ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $_SESSION['user_agent_hash'] = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
        
        // Log successo
        error_log("AuthController: Session created for user '{$user['username']}' from {$_SESSION['login_ip']}");
    }
    
    /**
     * Valida input username e password
     * @param string $username
     * @param string $password
     * @return array ['valid' => bool, 'error' => string]
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
