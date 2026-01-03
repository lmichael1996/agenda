<?php
/**
 * Model Client - Rappresenta l'entità Cliente
 */

class Client {
    public $id;
    public $first_name;
    public $last_name;
    public $phone;
    public $notes;
    public $has_certificate;
    
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
        $this->first_name = $data['first_name'] ?? '';
        $this->last_name = $data['last_name'] ?? '';
        $this->phone = $data['phone'] ?? '';
        $this->notes = $data['notes'] ?? '';
        $this->has_certificate = $data['has_certificate'] ?? 0;
    }
    
    /**
     * Converte l'oggetto in array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'notes' => $this->notes,
            'has_certificate' => $this->has_certificate
        ];
    }
    
    /**
     * Ottieni il nome completo
     */
    public function getFullName() {
        return trim($this->first_name . ' ' . $this->last_name);
    }
    
    /**
     * Valida i dati del cliente
     */
    public function validate() {
        $errors = [];
        
        if (empty(trim($this->first_name))) {
            $errors[] = 'Il nome è obbligatorio';
        }
        
        if (empty(trim($this->last_name))) {
            $errors[] = 'Il cognome è obbligatorio';
        }
        
        if (!empty($this->phone) && !preg_match('/^[\d\s\+\-\(\)]+$/', $this->phone)) {
            $errors[] = 'Il numero di telefono non è valido';
        }
        
        return empty($errors) ? ['valid' => true] : ['valid' => false, 'errors' => $errors];
    }
}
