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
     */
    public function saveAll($services) {
        if (!is_array($services)) {
            error_log("saveAll: services non è un array - tipo: " . gettype($services));
            return ['success' => false, 'error' => 'Dati servizi non validi'];
        }
        
        error_log("saveAll: Ricevuti " . count($services) . " servizi");
        
        try {
            $this->db->beginTransaction();
            
            // Prima aggiorna tutti gli appuntamenti che usano servizi diversi dal default al servizio di default
            $updateStmt = $this->db->prepare('UPDATE appointments SET service_id = 1 WHERE service_id != 1');
            $updateStmt->execute();
            error_log("saveAll: Aggiornati " . $updateStmt->rowCount() . " appuntamenti al servizio default");
            
            // Ora elimina tutti i servizi TRANNE quello di default (id=1)
            $deleteResult = $this->db->exec('DELETE FROM services WHERE id != 1');
            error_log("saveAll: Eliminati $deleteResult servizi dal database");
            
            // Inserisce tutti i nuovi servizi (saltando quello con id=1 se presente)
            $stmt = $this->db->prepare('INSERT INTO services (name, duration, price, description) VALUES (?, ?, ?, ?)');
            
            $errors = [];
            $inserted = 0;
            foreach ($services as $service) {
                // Salta il servizio di default se presente nell'array
                if (isset($service['id']) && $service['id'] == 1) {
                    error_log("saveAll: Saltato servizio default id=1");
                    continue;
                }
                
                $name = $service['name'] ?? '';
                $price = floatval($service['price'] ?? 0);
                $duration = intval($service['durationMinutes'] ?? 30);
                $description = $service['description'] ?? '';
                
                error_log("saveAll: Processando servizio '$name' - price=$price, duration=$duration");
                
                if (empty($name)) {
                    error_log("saveAll: Saltato servizio senza nome");
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
                
                $stmt->execute([$name, $duration, $price, $description]);
                $inserted++;
            }
            
            error_log("saveAll: Inseriti $inserted servizi");
            
            if (!empty($errors)) {
                $this->db->rollBack();
                error_log("saveAll: Rollback per errori: " . implode(', ', $errors));
                return ['success' => false, 'error' => 'Errori durante il salvataggio', 'details' => $errors];
            }
            
            $this->db->commit();
            error_log("saveAll: Commit completato con successo");
            return ['success' => true, 'message' => 'Servizi salvati con successo'];
            
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
