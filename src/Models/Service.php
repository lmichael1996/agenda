<?php
/**
 * Model Service - Rappresenta l'entità Servizio
 */

class Service {
    public $id;
    public $name;
    public $duration;
    public $price;
    public $description;
    
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
        $this->name = $data['name'] ?? '';
        $this->duration = $data['duration'] ?? 0;
        $this->price = $data['price'] ?? 0.0;
        $this->description = $data['description'] ?? '';
    }
    
    /**
     * Converte l'oggetto in array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'duration' => $this->duration,
            'price' => $this->price,
            'description' => $this->description
        ];
    }
    
    /**
     * Valida i dati del servizio
     */
    public function validate() {
        $errors = [];
        
        if (empty(trim($this->name))) {
            $errors[] = 'Il nome del servizio è obbligatorio';
        }
        
        if ($this->duration <= 0) {
            $errors[] = 'La durata deve essere maggiore di zero';
        }
        
        if ($this->price < 0) {
            $errors[] = 'Il prezzo non può essere negativo';
        }
        
        return empty($errors) ? ['valid' => true] : ['valid' => false, 'errors' => $errors];
    }
    
    /**
     * Formatta il prezzo
     */
    public function getFormattedPrice() {
        return '€ ' . number_format($this->price, 2, ',', '.');
    }
    
    /**
     * Formatta la durata
     */
    public function getFormattedDuration() {
        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;
        
        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}min";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}min";
        }
    }
}
