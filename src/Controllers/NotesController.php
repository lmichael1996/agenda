<?php
/**
 * Controller per la gestione delle note
 * Delega la logica business a NoteService
 */

require_once __DIR__ . '/../Models/Note.php';
require_once __DIR__ . '/../Services/NoteService.php';

class NotesController {
    private $service;
    
    public function __construct($db) {
        $this->service = new NoteService($db);
    }
    
    /**
     * Ottieni tutte le note
     */
    public function getAll() {
        $result = $this->service->getAll();
        
        if ($result['success'] && isset($result['data'])) {
            $result['data'] = array_map(function($note) {
                return $note->toArray();
            }, $result['data']);
        }
        
        return $result;
    }
    
    /**
     * Ottieni una nota specifica
     */
    public function getById($id) {
        $result = $this->service->getById($id);
        
        if ($result['success'] && isset($result['data'])) {
            $result['data'] = $result['data']->toArray();
        }
        
        return $result;
    }
    
    /**
     * Crea una nuova nota
     */
    public function create($data) {
        $result = $this->service->create($data);
        
        if ($result['success'] && isset($result['data'])) {
            $result['data'] = $result['data']->toArray();
        }
        
        return $result;
    }
    
    /**
     * Aggiorna una nota esistente
     */
    public function update($id, $data) {
        $result = $this->service->update($id, $data);
        
        if ($result['success'] && isset($result['data'])) {
            $result['data'] = $result['data']->toArray();
        }
        
        return $result;
    }
    
    /**
     * Elimina una nota
     */
    public function delete($id) {
        return $this->service->delete($id);
    }
    
    /**
     * Ricerca note
     */
    public function search($query) {
        $result = $this->service->search($query);
        
        if ($result['success'] && isset($result['data'])) {
            $result['data'] = array_map(function($note) {
                return $note->toArray();
            }, $result['data']);
        }
        
        return $result;
    }
}
