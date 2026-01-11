<?php
/**
 * Model Appointment - Rappresenta l'entità Appuntamento
 */

class Appointment {
    public $id;
    public $super_appointment_id;
    public $client_id;
    public $service_id;
    public $start_time;
    public $end_time;
    public $status;
    public $notes;
    
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
        $this->super_appointment_id = $data['super_appointment_id'] ?? null;
        $this->client_id = $data['client_id'] ?? null;
        $this->service_id = $data['service_id'] ?? null;
        $this->start_time = $data['start_time'] ?? null;
        $this->end_time = $data['end_time'] ?? null;
        $this->status = $data['status'] ?? 'scheduled';
        $this->notes = $data['notes'] ?? '';
    }
    
    /**
     * Converte l'oggetto in array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'super_appointment_id' => $this->super_appointment_id,
            'client_id' => $this->client_id,
            'service_id' => $this->service_id,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status,
            'notes' => $this->notes
        ];
    }
    
    /**
     * Valida i dati dell'appuntamento
     */
    public function validate() {
        $errors = [];
        
        if (empty($this->client_id)) {
            $errors[] = 'Cliente è obbligatorio';
        }
        
        if (empty($this->service_id)) {
            $errors[] = 'Servizio è obbligatorio';
        }
        
        if (empty($this->start_time)) {
            $errors[] = 'Ora di inizio è obbligatoria';
        }
        
        if (empty($this->end_time)) {
            $errors[] = 'Ora di fine è obbligatoria';
        }
        
        if (!empty($this->start_time) && !empty($this->end_time)) {
            $start = strtotime($this->start_time);
            $end = strtotime($this->end_time);
            if ($end <= $start) {
                $errors[] = 'L\'ora di fine deve essere successiva all\'ora di inizio';
            }
        }
        
        $validStatuses = ['scheduled', 'confirmed', 'completed', 'cancelled'];
        if (!in_array($this->status, $validStatuses)) {
            $errors[] = 'Status non valido';
        }
        
        return empty($errors) ? ['valid' => true] : ['valid' => false, 'errors' => $errors];
    }
    
    /**
     * Calcola la durata in minuti
     */
    public function getDuration() {
        if (empty($this->start_time) || empty($this->end_time)) {
            return 0;
        }
        $start = strtotime($this->start_time);
        $end = strtotime($this->end_time);
        return ($end - $start) / 60;
    }
    
    /**
     * Verifica se l'appuntamento è nel passato
     */
    public function isPast() {
        return !empty($this->start_time) && strtotime($this->start_time) < time();
    }
    
    /**
     * Verifica se l'appuntamento è oggi
     */
    public function isToday() {
        if (empty($this->start_time)) {
            return false;
        }
        return date('Y-m-d', strtotime($this->start_time)) === date('Y-m-d');
    }
}
