<?php
/**
 * ClientService - Logica business per la gestione dei clienti
 */

class ClientService {
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
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $clients = [];
            foreach ($rows as $row) {
                $clients[] = new Client($row);
            }
            
            return ['success' => true, 'data' => $clients];
        } catch (PDOException $e) {
            error_log("ClientService::getAll error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel recupero dei clienti'];
        }
    }
    
    /**
     * Ottieni un cliente per ID
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare('SELECT id, first_name, last_name, phone, notes, has_certificate FROM clients WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                return ['success' => true, 'data' => new Client($row)];
            } else {
                return ['success' => false, 'error' => 'Cliente non trovato'];
            }
        } catch (PDOException $e) {
            error_log("ClientService::getById error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel recupero del cliente'];
        }
    }
    
    /**
     * Crea un nuovo cliente
     */
    public function create($data) {
        $client = new Client($data);
        
        // Valida i dati
        $validation = $client->validate();
        if (!$validation['valid']) {
            return ['success' => false, 'error' => implode(', ', $validation['errors'])];
        }
        
        try {
            $stmt = $this->db->prepare('INSERT INTO clients (first_name, last_name, phone, notes, has_certificate) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([
                $client->first_name,
                $client->last_name,
                $client->phone,
                $client->notes,
                $client->has_certificate
            ]);
            
            $client->id = $this->db->lastInsertId();
            
            return ['success' => true, 'data' => $client, 'message' => 'Cliente creato con successo'];
        } catch (PDOException $e) {
            error_log("ClientService::create error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nella creazione del cliente'];
        }
    }
    
    /**
     * Aggiorna un cliente esistente
     */
    public function update($id, $data) {
        $data['id'] = $id;
        $client = new Client($data);
        
        // Valida i dati
        $validation = $client->validate();
        if (!$validation['valid']) {
            return ['success' => false, 'error' => implode(', ', $validation['errors'])];
        }
        
        try {
            $stmt = $this->db->prepare('UPDATE clients SET first_name = ?, last_name = ?, phone = ?, notes = ?, has_certificate = ? WHERE id = ?');
            $stmt->execute([
                $client->first_name,
                $client->last_name,
                $client->phone,
                $client->notes,
                $client->has_certificate,
                $id
            ]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'data' => $client, 'message' => 'Cliente aggiornato con successo'];
            } else {
                return ['success' => false, 'error' => 'Cliente non trovato o nessuna modifica'];
            }
        } catch (PDOException $e) {
            error_log("ClientService::update error: " . $e->getMessage());
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
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Cliente eliminato con successo'];
            } else {
                return ['success' => false, 'error' => 'Cliente non trovato'];
            }
        } catch (PDOException $e) {
            error_log("ClientService::delete error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nell\'eliminazione del cliente'];
        }
    }
    
    /**
     * Cerca clienti per nome o telefono
     */
    public function search($query) {
        try {
            $searchTerm = '%' . $query . '%';
            $stmt = $this->db->prepare('SELECT id, first_name, last_name, phone, notes, has_certificate FROM clients WHERE first_name LIKE ? OR last_name LIKE ? OR phone LIKE ? ORDER BY last_name, first_name');
            $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $clients = [];
            foreach ($rows as $row) {
                $clients[] = new Client($row);
            }
            
            return ['success' => true, 'data' => $clients];
        } catch (PDOException $e) {
            error_log("ClientService::search error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nella ricerca'];
        }
    }
}
