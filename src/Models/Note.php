<?php
/**
 * Model Note - Rappresenta l'entità Nota
 */

class Note {
    public $id;
    public $title;
    public $content;
    public $user_id;
    public $for_all;
    public $note_date;
    
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
        $this->user_id = $data['user_id'] ?? null;
        $this->for_all = isset($data['for_all']) ? (bool)$data['for_all'] : false;
        $this->note_date = $data['note_date'] ?? null;
    }
    
    /**
     * Converte l'oggetto in array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'user_id' => $this->user_id,
            'for_all' => $this->for_all,
            'note_date' => $this->note_date
        ];
    }
    
    /**
     * Valida i dati della nota
     */
    public function validate() {
        $errors = [];
        
        // Almeno uno tra title e content deve essere presente
        if (empty(trim($this->title)) && empty(trim($this->content))) {
            $errors[] = 'Inserisci almeno titolo o contenuto';
        }
        
        // user_id obbligatorio
        if (empty($this->user_id)) {
            $errors[] = 'Utente obbligatorio';
        }
        
        // note_date obbligatoria
        if (empty($this->note_date)) {
            $errors[] = 'Data obbligatoria';
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
