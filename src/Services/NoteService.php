<?php
/**
 * NoteService - Logica business per la gestione delle note
 */

class NoteService {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Ottieni tutte le note
     */
    public function getAll() {
        try {
            $stmt = $this->db->prepare('SELECT id, title, content, user_id, for_all, note_date FROM notes ORDER BY note_date DESC, id DESC');
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $notes = [];
            foreach ($rows as $row) {
                $notes[] = new Note($row);
            }
            
            return ['success' => true, 'data' => $notes];
        } catch (PDOException $e) {
            error_log("NoteService::getAll error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel recupero delle note'];
        }
    }
    
    /**
     * Ottieni una nota per ID
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare('SELECT id, title, content, user_id, for_all, note_date FROM notes WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                return ['success' => true, 'data' => new Note($row)];
            } else {
                return ['success' => false, 'error' => 'Nota non trovata'];
            }
        } catch (PDOException $e) {
            error_log("NoteService::getById error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel recupero della nota'];
        }
    }
    
    /**
     * Crea una nuova nota
     */
    public function create($data) {
        $note = new Note($data);
        
        // Valida i dati
        $validation = $note->validate();
        if (!$validation['valid']) {
            return ['success' => false, 'error' => implode(', ', $validation['errors'])];
        }
        
        try {
            $stmt = $this->db->prepare('INSERT INTO notes (title, content, user_id, for_all, note_date) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([
                $note->title,
                $note->content,
                $note->user_id,
                $note->for_all ? 1 : 0,
                $note->note_date
            ]);
            
            $note->id = $this->db->lastInsertId();
            
            return ['success' => true, 'data' => $note, 'message' => 'Nota creata con successo'];
        } catch (PDOException $e) {
            error_log("NoteService::create error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nella creazione della nota'];
        }
    }
    
    /**
     * Aggiorna una nota esistente
     */
    public function update($id, $data) {
        $data['id'] = $id;
        $note = new Note($data);
        
        // Valida i dati
        $validation = $note->validate();
        if (!$validation['valid']) {
            return ['success' => false, 'error' => implode(', ', $validation['errors'])];
        }
        
        try {
            $stmt = $this->db->prepare('UPDATE notes SET title = ?, content = ?, user_id = ?, for_all = ?, note_date = ? WHERE id = ?');
            $stmt->execute([
                $note->title,
                $note->content,
                $note->user_id,
                $note->for_all ? 1 : 0,
                $note->note_date,
                $id
            ]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'data' => $note, 'message' => 'Nota aggiornata con successo'];
            } else {
                return ['success' => false, 'error' => 'Nota non trovata o nessuna modifica'];
            }
        } catch (PDOException $e) {
            error_log("NoteService::update error: " . $e->getMessage());
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
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Nota eliminata con successo'];
            } else {
                return ['success' => false, 'error' => 'Nota non trovata'];
            }
        } catch (PDOException $e) {
            error_log("NoteService::delete error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nell\'eliminazione della nota'];
        }
    }
    
    /**
     * Cerca note per titolo o contenuto
     */
    public function search($query) {
        try {
            $searchTerm = '%' . $query . '%';
            $stmt = $this->db->prepare('SELECT id, title, content, user_id, for_all, note_date FROM notes WHERE title LIKE ? OR content LIKE ? ORDER BY note_date DESC, id DESC');
            $stmt->execute([$searchTerm, $searchTerm]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $notes = [];
            foreach ($rows as $row) {
                $notes[] = new Note($row);
            }
            
            return ['success' => true, 'data' => $notes];
        } catch (PDOException $e) {
            error_log("NoteService::search error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nella ricerca'];
        }
    }
}
