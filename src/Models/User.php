<?php
/**
 * Model User - Rappresenta l'entità Utente
 */

class User {
    public $id;
    public $username;
    public $color;
    
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
        $this->color = $data['color'] ?? '#3498db';
    }
    
    /**
     * Converte l'oggetto in array (senza password)
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'color' => $this->color
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
        
        return empty($errors) ? ['valid' => true] : ['valid' => false, 'errors' => $errors];
    }
}
