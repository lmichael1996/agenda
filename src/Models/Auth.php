<?php
/**
 * Model Auth - Rappresenta i dati di autenticazione
 */

class Auth {
    public $id;
    public $username;
    public $password_hash;
    public $color;
    public $login_attempts;
    public $last_attempt;
    
    /**
     * Costruttore
     */
    public function __construct($data = []) {
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }
    
    /**
     * Popola l'oggetto con i dati
     */
    public function hydrate($data) {
        $this->id = $data['id'] ?? null;
        $this->username = $data['username'] ?? '';
        $this->password_hash = $data['password_hash'] ?? '';
        $this->color = $data['color'] ?? '#3b82f6';
        $this->login_attempts = $data['login_attempts'] ?? 0;
        $this->last_attempt = $data['last_attempt'] ?? 0;
    }
    
    /**
     * Converte l'oggetto in array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'color' => $this->color,
            'login_attempts' => $this->login_attempts,
            'last_attempt' => $this->last_attempt
        ];
    }
    
    /**
     * Converte in array senza dati sensibili (per sessione)
     */
    public function toSessionArray() {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'color' => $this->color
        ];
    }
    
    /**
     * Verifica la password
     */
    public function verifyPassword($password) {
        return password_verify($password, $this->password_hash);
    }
    
    /**
     * Valida i dati di autenticazione
     */
    public function validate() {
        $errors = [];
        
        if (empty(trim($this->username))) {
            $errors[] = 'Username è obbligatorio';
        }
        
        if (strlen($this->username) < 3) {
            $errors[] = 'Username deve essere almeno 3 caratteri';
        }
        
        return empty($errors) ? ['valid' => true] : ['valid' => false, 'errors' => $errors];
    }
}
