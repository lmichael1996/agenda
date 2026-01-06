<?php
/**
 * ServiceService - Logica business per la gestione dei servizi
 */

class ServiceService {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Ottieni tutti i servizi
     */
    public function getAll() {
        try {
            $stmt = $this->db->prepare('SELECT id, name, duration, price, description FROM services ORDER BY name');
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $services = [];
            foreach ($rows as $row) {
                $services[] = new Service($row);
            }
            
            return ['success' => true, 'data' => $services];
        } catch (PDOException $e) {
            error_log("ServiceService::getAll error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel recupero dei servizi'];
        }
    }
    
    /**
     * Ottieni un servizio per ID
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare('SELECT id, name, duration, price, description FROM services WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                return ['success' => true, 'data' => new Service($row)];
            } else {
                return ['success' => false, 'error' => 'Servizio non trovato'];
            }
        } catch (PDOException $e) {
            error_log("ServiceService::getById error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel recupero del servizio'];
        }
    }
    
    /**
     * Crea un nuovo servizio
     */
    public function create($data) {
        $service = new Service($data);
        
        // Valida i dati
        $validation = $service->validate();
        if (!$validation['valid']) {
            return ['success' => false, 'error' => implode(', ', $validation['errors'])];
        }
        
        try {
            $stmt = $this->db->prepare('INSERT INTO services (name, duration, price, description) VALUES (?, ?, ?, ?)');
            $stmt->execute([
                $service->name,
                $service->duration,
                $service->price,
                $service->description
            ]);
            
            $service->id = $this->db->lastInsertId();
            
            return ['success' => true, 'data' => $service, 'message' => 'Servizio creato con successo'];
        } catch (PDOException $e) {
            error_log("ServiceService::create error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nella creazione del servizio'];
        }
    }
    
    /**
     * Aggiorna un servizio esistente
     */
    public function update($id, $data) {
        $data['id'] = $id;
        $service = new Service($data);
        
        // Valida i dati
        $validation = $service->validate();
        if (!$validation['valid']) {
            return ['success' => false, 'error' => implode(', ', $validation['errors'])];
        }
        
        try {
            $stmt = $this->db->prepare('UPDATE services SET name = ?, duration = ?, price = ?, description = ? WHERE id = ?');
            $stmt->execute([
                $service->name,
                $service->duration,
                $service->price,
                $service->description,
                $id
            ]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'data' => $service, 'message' => 'Servizio aggiornato con successo'];
            } else {
                return ['success' => false, 'error' => 'Servizio non trovato o nessuna modifica'];
            }
        } catch (PDOException $e) {
            error_log("ServiceService::update error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nell\'aggiornamento del servizio'];
        }
    }
    
    /**
     * Elimina un servizio
     */
    public function delete($id) {
        try {
            // Protezione: non permettere eliminazione del servizio di default
            if ($id == 1) {
                return ['success' => false, 'error' => 'Il servizio di default non può essere eliminato'];
            }
            
            // Aggiorna gli appuntamenti che usano questo servizio al servizio di default (id=1)
            $stmt = $this->db->prepare('UPDATE appointments SET service_id = 1, note = CONCAT(COALESCE(note, ""), "\n[Servizio originale eliminato]") WHERE service_id = ?');
            $stmt->execute([$id]);
            $affectedAppointments = $stmt->rowCount();
            
            // Ora elimina il servizio
            $stmt = $this->db->prepare('DELETE FROM services WHERE id = ?');
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                $message = 'Servizio eliminato con successo';
                if ($affectedAppointments > 0) {
                    $message .= " ($affectedAppointments appuntamenti aggiornati al servizio generico)";
                }
                return ['success' => true, 'message' => $message];
            } else {
                return ['success' => false, 'error' => 'Servizio non trovato'];
            }
        } catch (PDOException $e) {
            error_log("ServiceService::delete error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nell\'eliminazione del servizio'];
        }
    }
}
