<?php
/**
 * Model SuperAppointment - Rappresenta l'entità Super Appuntamento
 * Un SuperAppointment raggruppa più Appointments (servizi) per lo stesso cliente
 */

class SuperAppointment {
    public $id;
    public $client_id;
    public $start_time;
    public $note;
    public $created_at;
    
    // Campi aggiuntivi per join
    public $first_name;
    public $last_name;
    public $phone;
    public $client_notes;
    
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
        $this->client_id = $data['client_id'] ?? null;
        $this->start_time = $data['start_time'] ?? null;
        $this->note = $data['note'] ?? '';
        $this->created_at = $data['created_at'] ?? null;
        
        // Campi da join
        $this->first_name = $data['first_name'] ?? '';
        $this->last_name = $data['last_name'] ?? '';
        $this->phone = $data['phone'] ?? '';
        $this->client_notes = $data['client_notes'] ?? '';
    }
    
    /**
     * Converte l'oggetto in array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'start_time' => $this->start_time,
            'note' => $this->note,
            'created_at' => $this->created_at,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'client_notes' => $this->client_notes
        ];
    }
    
    /**
     * Ottieni il nome completo del cliente
     */
    public function getClientFullName() {
        return trim($this->first_name . ' ' . $this->last_name);
    }
    
    /**
     * Valida i dati del super appuntamento
     */
    public function validate() {
        $errors = [];
        
        if (empty($this->client_id)) {
            $errors[] = 'Il cliente è obbligatorio';
        }
        
        if (empty($this->start_time)) {
            $errors[] = 'La data e ora sono obbligatorie';
        }
        
        // Valida formato datetime
        if ($this->start_time) {
            $date = \DateTime::createFromFormat('Y-m-d H:i:s', $this->start_time);
            if (!$date || $date->format('Y-m-d H:i:s') !== $this->start_time) {
                $errors[] = 'Formato data/ora non valido (richiesto: Y-m-d H:i:s)';
            }
        }
        
        return empty($errors) ? ['valid' => true] : ['valid' => false, 'errors' => $errors];
    }
}
