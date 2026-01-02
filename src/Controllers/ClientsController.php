<?php
/**
 * Controller per la gestione dei clienti
 */

class ClientsController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Ottieni tutti i clienti
     */
    public function getAll() {
        try {
            $stmt = $this->db->prepare('SELECT id, first_name, last_name, phone, notes, has_certificate FROM clients ORDER BY last_name, first_name');
            $stmt->execute();
            return [
                'success' => true,
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (PDOException $e) {
            error_log("Errore getAll clients: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel recupero dei clienti'];
        }
    }
    
    /**
     * Ottieni un cliente specifico
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare('SELECT id, first_name, last_name, phone, notes, has_certificate FROM clients WHERE id = ?');
            $stmt->execute([$id]);
            $client = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($client) {
                return ['success' => true, 'data' => $client];
            } else {
                return ['success' => false, 'error' => 'Cliente non trovato'];
            }
        } catch (PDOException $e) {
            error_log("Errore getById client: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel recupero del cliente'];
        }
    }
    
    /**
     * Crea un nuovo cliente
     */
    public function create($data) {
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $stmt = $this->db->prepare('INSERT INTO clients (first_name, last_name, phone, notes, has_certificate) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([
                trim($data['first_name']),
                trim($data['last_name']),
                $data['phone'] ?? null,
                $data['notes'] ?? null,
                isset($data['has_certificate']) ? (int)$data['has_certificate'] : 0
            ]);
            
            return [
                'success' => true,
                'id' => $this->db->lastInsertId(),
                'message' => 'Cliente creato con successo'
            ];
        } catch (PDOException $e) {
            error_log("Errore create client: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nella creazione del cliente'];
        }
    }
    
    /**
     * Aggiorna un cliente esistente
     */
    public function update($id, $data) {
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $stmt = $this->db->prepare('UPDATE clients SET first_name = ?, last_name = ?, phone = ?, notes = ?, has_certificate = ? WHERE id = ?');
            $stmt->execute([
                trim($data['first_name']),
                trim($data['last_name']),
                $data['phone'] ?? null,
                $data['notes'] ?? null,
                isset($data['has_certificate']) ? (int)$data['has_certificate'] : 0,
                $id
            ]);
            
            return ['success' => true, 'message' => 'Cliente aggiornato con successo'];
        } catch (PDOException $e) {
            error_log("Errore update client: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nell\'aggiornamento del cliente'];
        }
    }
    
    /**
     * Elimina un cliente
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare('DELETE FROM clients WHERE id = ?');
            $stmt->execute([$id]);
            
            return ['success' => true, 'message' => 'Cliente eliminato con successo'];
        } catch (PDOException $e) {
            error_log("Errore delete client: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nell\'eliminazione del cliente'];
        }
    }
    
    /**
     * Ricerca clienti per nome/cognome/telefono
     */
    public function search($query) {
        try {
            $searchTerm = "%{$query}%";
            $stmt = $this->db->prepare('
                SELECT id, first_name, last_name, phone, notes, has_certificate 
                FROM clients 
                WHERE first_name LIKE ? OR last_name LIKE ? OR phone LIKE ?
                ORDER BY last_name, first_name
            ');
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
            
            return [
                'success' => true,
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (PDOException $e) {
            error_log("Errore search clients: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nella ricerca'];
        }
    }
    
    /**
     * Validazione dati cliente
     */
    private function validate($data) {
        $errors = [];
        
        if (empty(trim($data['first_name'] ?? ''))) {
            $errors[] = 'Il nome è obbligatorio';
        }
        
        if (empty(trim($data['last_name'] ?? ''))) {
            $errors[] = 'Il cognome è obbligatorio';
        }
        
        if (!empty($data['phone'])) {
            $phone = preg_replace('/[^\d+]/', '', $data['phone']);
            if (strlen($phone) < 8 || strlen($phone) > 15) {
                $errors[] = 'Il numero di telefono deve essere tra 8 e 15 cifre';
            }
        }
        
        return $errors;
    }
}
