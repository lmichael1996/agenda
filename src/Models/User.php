<?php
/**
 * Model User - Rappresenta l'entità Utente
 */

class User {
    public $id;
    public $username;
    public $email;
    public $role;
    public $created_at;
    
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
        $this->email = $data['email'] ?? '';
        $this->role = $data['role'] ?? 'user';
        $this->created_at = $data['created_at'] ?? null;
    }
    
    /**
     * Converte l'oggetto in array (senza password)
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'created_at' => $this->created_at
        ];
    }
    
    /**
     * Valida i dati utente
     */
    public function validate() {
        $errors = [];
        
        if (empty(trim($this->username)) || strlen($this->username) < 3) {
            $errors[] = 'Username deve essere almeno 3 caratteri';
        }
        
        if (!empty($this->email) && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email non valida';
        }
        
        if (!in_array($this->role, ['user', 'admin'])) {
            $errors[] = 'Ruolo non valido';
        }
        
        return empty($errors) ? ['valid' => true] : ['valid' => false, 'errors' => $errors];
    }
    
    /**
     * Verifica se è admin
     */
    public function isAdmin() {
        return $this->role === 'admin';
    }
}
