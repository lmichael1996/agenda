<?php
/**
 * Controller per la gestione dei servizi
 * Delega la logica business a ServiceService
 */

require_once __DIR__ . '/../Models/Service.php';
require_once __DIR__ . '/../Services/ServiceService.php';

class ServicesController {
    private $service;
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
        $this->service = new ServiceService($db);
    }
    
    /**
     * Ottieni tutti i servizi
     */
    public function getAll() {
        $result = $this->service->getAll();
        
        // Converti oggetti Model in array per la risposta JSON
        if ($result['success'] && isset($result['data'])) {
            $services = array_map(function($service) {
                $data = $service->toArray();
                // Compatibilità frontend
                $data['durationMinutes'] = $data['duration'];
                unset($data['duration']);
                return $data;
            }, $result['data']);
            
            return ['success' => true, 'services' => $services];
        }
        
        return $result;
    }
    
    /**
     * Salvataggio batch di tutti i servizi
     * Aggiorna servizi esistenti, inserisce nuovi, elimina quelli rimossi
     */
    public function saveAll($services) {
        if (!is_array($services)) {
            error_log("saveAll: services non è un array - tipo: " . gettype($services));
            return ['success' => false, 'error' => 'Dati servizi non validi'];
        }
        
        error_log("saveAll: Ricevuti " . count($services) . " servizi");
        
        try {
            $this->db->beginTransaction();
            
            // Ottieni tutti gli ID dei servizi nel database (escluso default)
            $existingIds = [];
            $stmt = $this->db->query('SELECT id FROM services WHERE id != 1');
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $existingIds[] = (int)$row['id'];
            }
            error_log("saveAll: Servizi esistenti nel DB: " . implode(', ', $existingIds));
            
            // Raccogli gli ID dei servizi che arrivano dal frontend
            $incomingIds = [];
            $newServices = [];
            
            foreach ($services as $service) {
                $id = $service['id'] ?? null;
                
                // Salta il servizio di default se presente
                if ($id == 1) {
                    error_log("saveAll: Saltato servizio default id=1");
                    continue;
                }
                
                // Se l'ID è numerico, è un servizio esistente
                if (is_numeric($id)) {
                    $incomingIds[] = (int)$id;
                } else {
                    // ID temporaneo tipo "temp_..." = servizio nuovo
                    $newServices[] = $service;
                }
            }
            
            error_log("saveAll: Servizi in arrivo: " . implode(', ', $incomingIds));
            error_log("saveAll: Nuovi servizi: " . count($newServices));
            
            // ELIMINA i servizi che non sono più nell'elenco
            $toDelete = array_diff($existingIds, $incomingIds);
            if (!empty($toDelete)) {
                error_log("saveAll: Da eliminare: " . implode(', ', $toDelete));
                
                // Prima aggiorna gli appuntamenti che usano questi servizi al servizio default
                $placeholders = implode(',', array_fill(0, count($toDelete), '?'));
                $updateStmt = $this->db->prepare("UPDATE appointments SET service_id = 1 WHERE service_id IN ($placeholders)");
                $updateStmt->execute(array_values($toDelete));
                error_log("saveAll: Aggiornati " . $updateStmt->rowCount() . " appuntamenti al servizio default");
                
                // Ora elimina i servizi
                $deleteStmt = $this->db->prepare("DELETE FROM services WHERE id IN ($placeholders)");
                $deleteStmt->execute(array_values($toDelete));
                error_log("saveAll: Eliminati " . $deleteStmt->rowCount() . " servizi");
            }
            
            // AGGIORNA i servizi esistenti
            $updateStmt = $this->db->prepare('UPDATE services SET name = ?, duration = ?, price = ?, description = ? WHERE id = ?');
            $errors = [];
            $updated = 0;
            
            foreach ($services as $service) {
                $id = $service['id'] ?? null;
                
                // Solo servizi esistenti (ID numerico)
                if (!is_numeric($id) || $id == 1) {
                    continue;
                }
                
                $name = $service['name'] ?? '';
                $price = floatval($service['price'] ?? 0);
                $duration = intval($service['durationMinutes'] ?? 30);
                $description = $service['description'] ?? '';
                
                error_log("saveAll: Aggiornamento servizio ID=$id '$name' - price=$price, duration=$duration");
                
                if (empty($name)) {
                    error_log("saveAll: Saltato servizio senza nome ID=$id");
                    continue;
                }
                
                // Validazione
                if ($price < 0 || $price > 9999.99) {
                    $errors[] = "Prezzo non valido per servizio {$name}";
                    error_log("saveAll: Errore validazione prezzo per '$name'");
                    continue;
                }
                
                if ($duration < 15 || $duration > 480 || $duration % 15 !== 0) {
                    $errors[] = "Durata non valida per servizio {$name}";
                    error_log("saveAll: Errore validazione durata per '$name': $duration");
                    continue;
                }
                
                $updateStmt->execute([$name, $duration, $price, $description, $id]);
                $updated++;
            }
            
            error_log("saveAll: Aggiornati $updated servizi");
            
            // INSERISCI i nuovi servizi
            $insertStmt = $this->db->prepare('INSERT INTO services (name, duration, price, description) VALUES (?, ?, ?, ?)');
            $inserted = 0;
            
            foreach ($newServices as $service) {
                $name = $service['name'] ?? '';
                $price = floatval($service['price'] ?? 0);
                $duration = intval($service['durationMinutes'] ?? 30);
                $description = $service['description'] ?? '';
                
                error_log("saveAll: Inserimento nuovo servizio '$name' - price=$price, duration=$duration");
                
                if (empty($name)) {
                    error_log("saveAll: Saltato nuovo servizio senza nome");
                    continue;
                }
                
                // Validazione
                if ($price < 0 || $price > 9999.99) {
                    $errors[] = "Prezzo non valido per servizio {$name}";
                    error_log("saveAll: Errore validazione prezzo per '$name'");
                    continue;
                }
                
                if ($duration < 15 || $duration > 480 || $duration % 15 !== 0) {
                    $errors[] = "Durata non valida per servizio {$name}";
                    error_log("saveAll: Errore validazione durata per '$name': $duration");
                    continue;
                }
                
                $insertStmt->execute([$name, $duration, $price, $description]);
                $inserted++;
            }
            
            error_log("saveAll: Inseriti $inserted nuovi servizi");
            
            if (!empty($errors)) {
                $this->db->rollBack();
                error_log("saveAll: Rollback per errori: " . implode(', ', $errors));
                return ['success' => false, 'error' => 'Errori durante il salvataggio', 'details' => $errors];
            }
            
            $this->db->commit();
            error_log("saveAll: Commit completato - $updated aggiornati, $inserted inseriti, " . count($toDelete) . " eliminati");
            return ['success' => true, 'message' => "Servizi salvati: $updated aggiornati, $inserted nuovi, " . count($toDelete) . " eliminati"];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("saveAll: Errore PDO - " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel salvataggio dei servizi', 'details' => $e->getMessage()];
        }
    }
    
    /**
     * Crea un nuovo servizio
     */
    public function create($data) {
        $result = $this->service->create($data);
        
        if ($result['success'] && isset($result['data'])) {
            $serviceData = $result['data']->toArray();
            $serviceData['durationMinutes'] = $serviceData['duration'];
            unset($serviceData['duration']);
            $result['data'] = $serviceData;
        }
        
        return $result;
    }
    
    /**
     * Aggiorna un servizio esistente
     */
    public function update($id, $data) {
        $result = $this->service->update($id, $data);
        
        if ($result['success'] && isset($result['data'])) {
            $serviceData = $result['data']->toArray();
            $serviceData['durationMinutes'] = $serviceData['duration'];
            unset($serviceData['duration']);
            $result['data'] = $serviceData;
        }
        
        return $result;
    }
    
    /**
     * Elimina un servizio
     */
    public function delete($id) {
        return $this->service->delete($id);
    }
}
