<?php
/**
 * Controller per la gestione delle note
 */

class NotesController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Ottieni tutte le note con filtri opzionali
     */
    public function getAll($filters = []) {
        try {
            $query = 'SELECT id, title, content, for_all, note_date, user_id FROM notes WHERE 1=1';
            $params = [];
            
            if (isset($filters['user_id'])) {
                $query .= ' AND user_id = ?';
                $params[] = $filters['user_id'];
            }
            
            if (isset($filters['for_all'])) {
                $query .= ' AND for_all = ?';
                $params[] = $filters['for_all'] ? 1 : 0;
            }
            
            if (isset($filters['date_from'])) {
                $query .= ' AND note_date >= ?';
                $params[] = $filters['date_from'];
            }
            
            if (isset($filters['date_to'])) {
                $query .= ' AND note_date <= ?';
                $params[] = $filters['date_to'];
            }
            
            $query .= ' ORDER BY note_date DESC, id DESC';
            
            $stmt = $this->db->prepare($query);
            if (!empty($params)) {
                $stmt->execute($params);
            } else {
                $stmt->execute();
            }
            
            return [
                'success' => true,
                'notes' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (PDOException $e) {
            error_log("Errore getAll notes: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel recupero delle note'];
        }
    }
    
    /**
     * Ottieni una nota specifica
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare('SELECT id, title, content, for_all, note_date, user_id FROM notes WHERE id = ?');
            $stmt->execute([$id]);
            $note = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($note) {
                return ['success' => true, 'note' => $note];
            } else {
                return ['success' => false, 'error' => 'Nota non trovata'];
            }
        } catch (PDOException $e) {
            error_log("Errore getById note: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel recupero della nota'];
        }
    }
    
    /**
     * Crea una nuova nota
     */
    public function create($data) {
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $stmt = $this->db->prepare('INSERT INTO notes (title, content, for_all, note_date, user_id) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([
                trim($data['title'] ?? ''),
                trim($data['content'] ?? ''),
                isset($data['for_all']) ? (int)$data['for_all'] : 0,
                $data['note_date'],
                (int)$data['user_id']
            ]);
            
            return [
                'success' => true,
                'note_id' => $this->db->lastInsertId(),
                'message' => 'Nota creata con successo'
            ];
        } catch (PDOException $e) {
            error_log("Errore create note: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nella creazione della nota'];
        }
    }
    
    /**
     * Aggiorna una nota esistente
     */
    public function update($id, $data) {
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $stmt = $this->db->prepare('UPDATE notes SET title = ?, content = ?, for_all = ?, note_date = ? WHERE id = ?');
            $stmt->execute([
                trim($data['title'] ?? ''),
                trim($data['content'] ?? ''),
                isset($data['for_all']) ? (int)$data['for_all'] : 0,
                $data['note_date'],
                $id
            ]);
            
            return ['success' => true, 'message' => 'Nota aggiornata con successo'];
        } catch (PDOException $e) {
            error_log("Errore update note: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nell\'aggiornamento della nota'];
        }
    }
    
    /**
     * Elimina una nota
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare('DELETE FROM notes WHERE id = ?');
            $stmt->execute([$id]);
            
            return ['success' => true, 'message' => 'Nota eliminata con successo'];
        } catch (PDOException $e) {
            error_log("Errore delete note: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nell\'eliminazione della nota'];
        }
    }
    
    /**
     * Validazione dati nota
     */
    private function validate($data) {
        $errors = [];
        
        if (!isset($data['user_id']) || empty($data['user_id'])) {
            $errors[] = 'User ID obbligatorio';
        }
        
        if (!isset($data['note_date']) || empty($data['note_date'])) {
            $errors[] = 'Data nota obbligatoria';
        } else {
            // Validazione formato data
            $date = DateTime::createFromFormat('Y-m-d', $data['note_date']);
            if (!$date || $date->format('Y-m-d') !== $data['note_date']) {
                $errors[] = 'Formato data non valido (YYYY-MM-DD)';
            }
        }
        
        return $errors;
    }
}
