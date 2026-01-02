<?php
/**
 * Controller per la gestione dei servizi
 */

class ServicesController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Ottieni tutti i servizi
     */
    public function getAll() {
        try {
            $stmt = $this->db->prepare('SELECT id, name_service as name, price, duration_minutes, description_service as description FROM services ORDER BY id');
            $stmt->execute();
            $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Converti per compatibilità frontend
            foreach ($services as &$service) {
                $service['durationMinutes'] = (int)$service['duration_minutes'];
                unset($service['duration_minutes']);
            }
            
            return ['success' => true, 'services' => $services];
        } catch (PDOException $e) {
            error_log("Errore getAll services: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel recupero dei servizi'];
        }
    }
    
    /**
     * Salvataggio batch di tutti i servizi
     */
    public function saveAll($services) {
        if (!is_array($services)) {
            return ['success' => false, 'error' => 'Dati servizi non validi'];
        }
        
        try {
            $this->db->beginTransaction();
            
            // Elimina tutti i servizi esistenti
            $this->db->exec('DELETE FROM services');
            
            // Inserisce tutti i nuovi servizi
            $stmt = $this->db->prepare('INSERT INTO services (name_service, price, duration_minutes, description_service) VALUES (?, ?, ?, ?)');
            
            $errors = [];
            foreach ($services as $service) {
                $name = $service['name'] ?? '';
                $price = floatval($service['price'] ?? 0);
                $duration = intval($service['durationMinutes'] ?? 30);
                $description = $service['description'] ?? '';
                
                if (empty($name)) {
                    continue; // Salta servizi senza nome
                }
                
                // Validazione
                if ($price < 0 || $price > 9999.99) {
                    $errors[] = "Prezzo non valido per servizio {$name}";
                    continue;
                }
                
                if ($duration < 15 || $duration > 480 || $duration % 15 !== 0) {
                    $errors[] = "Durata non valida per servizio {$name}";
                    continue;
                }
                
                $stmt->execute([$name, $price, $duration, $description]);
            }
            
            if (!empty($errors)) {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'Errori durante il salvataggio', 'details' => $errors];
            }
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Servizi salvati con successo'];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Errore saveAll services: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel salvataggio dei servizi'];
        }
    }
    
    /**
     * Crea un nuovo servizio
     */
    public function create($data) {
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $stmt = $this->db->prepare('INSERT INTO services (name_service, price, duration_minutes, description_service) VALUES (?, ?, ?, ?)');
            $stmt->execute([
                trim($data['name']),
                floatval($data['price']),
                intval($data['durationMinutes'] ?? 30),
                $data['description'] ?? ''
            ]);
            
            return [
                'success' => true,
                'id' => $this->db->lastInsertId(),
                'message' => 'Servizio creato con successo'
            ];
        } catch (PDOException $e) {
            error_log("Errore create service: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nella creazione del servizio'];
        }
    }
    
    /**
     * Aggiorna un servizio esistente
     */
    public function update($id, $data) {
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $stmt = $this->db->prepare('UPDATE services SET name_service = ?, price = ?, duration_minutes = ?, description_service = ? WHERE id = ?');
            $stmt->execute([
                trim($data['name']),
                floatval($data['price']),
                intval($data['durationMinutes'] ?? 30),
                $data['description'] ?? '',
                $id
            ]);
            
            return ['success' => true, 'message' => 'Servizio aggiornato con successo'];
        } catch (PDOException $e) {
            error_log("Errore update service: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nell\'aggiornamento del servizio'];
        }
    }
    
    /**
     * Elimina un servizio
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare('DELETE FROM services WHERE id = ?');
            $stmt->execute([$id]);
            
            return ['success' => true, 'message' => 'Servizio eliminato con successo'];
        } catch (PDOException $e) {
            error_log("Errore delete service: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nell\'eliminazione del servizio'];
        }
    }
    
    /**
     * Validazione dati servizio
     */
    private function validate($data) {
        $errors = [];
        
        if (empty(trim($data['name'] ?? ''))) {
            $errors[] = 'Il nome del servizio è obbligatorio';
        }
        
        $price = floatval($data['price'] ?? 0);
        if ($price < 0 || $price > 9999.99) {
            $errors[] = 'Il prezzo deve essere tra 0 e 9999.99';
        }
        
        $duration = intval($data['durationMinutes'] ?? 0);
        if ($duration < 15 || $duration > 480) {
            $errors[] = 'La durata deve essere tra 15 e 480 minuti';
        }
        if ($duration % 15 !== 0) {
            $errors[] = 'La durata deve essere un multiplo di 15 minuti';
        }
        
        return $errors;
    }
}
