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
            return ['success' => false, 'error' => 'Dati servizi non validi'];
        }
        
        try {
            $this->db->beginTransaction();
            
            // Elimina tutti i servizi esistenti
            $this->db->exec('DELETE FROM services');
            
            // Inserisce tutti i nuovi servizi
            $stmt = $this->db->prepare('INSERT INTO services (name, duration, price, description) VALUES (?, ?, ?, ?)');
            
            $errors = [];
            foreach ($services as $service) {
                $name = $service['name'] ?? '';
                $price = floatval($service['price'] ?? 0);
                $duration = intval($service['durationMinutes'] ?? 30);
                $description = $service['description'] ?? '';
                
                if (empty($name)) {
                    continue;
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
                
                $stmt->execute([$name, $duration, $price, $description]);
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
