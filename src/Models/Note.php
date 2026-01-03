<?php
/**
 * Model Note - Rappresenta l'entità Nota
 */

class Note {
    public $id;
    public $title;
    public $content;
    public $created_at;
    public $updated_at;
    
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
        $this->title = $data['title'] ?? '';
        $this->content = $data['content'] ?? '';
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }
    
    /**
     * Converte l'oggetto in array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
    
    /**
     * Valida i dati della nota
     */
    public function validate() {
        $errors = [];
        
        if (empty(trim($this->title))) {
            $errors[] = 'Il titolo è obbligatorio';
        }
        
        if (empty(trim($this->content))) {
            $errors[] = 'Il contenuto è obbligatorio';
        }
        
        return empty($errors) ? ['valid' => true] : ['valid' => false, 'errors' => $errors];
    }
    
    /**
     * Ottieni anteprima del contenuto
     */
    public function getPreview($length = 100) {
        if (strlen($this->content) <= $length) {
            return $this->content;
        }
        return substr($this->content, 0, $length) . '...';
    }
}
