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
    
    /**
     * Ottiene le note formattate per la settimana corrente
     * @return string JSON delle note
     */
    public function getCurrentWeekNotesJSON() {
        $today = new DateTime();
        $weekStart = clone $today;
        $weekStart->modify('monday this week');
        $weekEnd = clone $weekStart;
        $weekEnd->modify('+6 days');

        $notes = $this->getWeekNotes(
            $weekStart->format('Y-m-d'),
            $weekEnd->format('Y-m-d')
        );

        $formatted = $this->formatForCalendar($notes);
        
        return json_encode($formatted, JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Ottiene le note per un intervallo di date
     * @param string $startDate Data inizio (Y-m-d)
     * @param string $endDate Data fine (Y-m-d)
     * @return array Array di note
     */
    private function getWeekNotes($startDate, $endDate) {
        $result = $this->service->getAll();
        
        if (!$result['success'] || !isset($result['data'])) {
            return [];
        }
        
        // Filtra le note per l'intervallo di date
        $notes = array_filter($result['data'], function($note) use ($startDate, $endDate) {
            $noteArray = is_array($note) ? $note : $note->toArray();
            return $noteArray['note_date'] >= $startDate && $noteArray['note_date'] <= $endDate;
        });
        
        return array_map(function($note) {
            return is_array($note) ? $note : $note->toArray();
        }, $notes);
    }
    
    /**
     * Formatta le note per il calendario
     * @param array $notes Array di note dal database
     * @return array Array di note formattate per il calendario
     */
    private function formatForCalendar($notes) {
        $events = [];
        
        foreach ($notes as $note) {
            // Converti la data dal formato YYYY-MM-DD a DD-MM-YYYY
            $date = new DateTime($note['note_date']);
            
            $events[] = [
                'id' => $note['id'],
                'type' => 'note',
                'date' => $date->format('d-m-Y'),
                'time' => '08:00', // Orario fisso per le note
                'title' => $note['title'] ?: '',
                'content' => $note['content'] ?: '',
                'user_id' => $note['user_id'],
                'for_all' => (bool)$note['for_all']
            ];
        }
        
        return $events;
    }
}

