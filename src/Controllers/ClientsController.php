<?php
/**
 * Controller per la gestione dei clienti
 * Delega la logica business a ClientService
 */

require_once __DIR__ . '/../Models/Client.php';
require_once __DIR__ . '/../Services/ClientService.php';

class ClientsController {
    private $service;
    
    public function __construct($db) {
        $this->service = new ClientService($db);
    }
    
    /**
     * Ottieni tutti i clienti
     */
    public function getAll() {
        $result = $this->service->getAll();
        
        // Converti oggetti Model in array per la risposta JSON
        if ($result['success'] && isset($result['data'])) {
            $result['data'] = array_map(function($client) {
                return $client->toArray();
            }, $result['data']);
        }
        
        return $result;
    }
    
    /**
     * Ottieni un cliente specifico
     */
    public function getById($id) {
        $result = $this->service->getById($id);
        
        // Converti oggetto Model in array per la risposta JSON
        if ($result['success'] && isset($result['data'])) {
            $result['data'] = $result['data']->toArray();
        }
        
        return $result;
    }
    
    /**
     * Crea un nuovo cliente
     */
    public function create($data) {
        $result = $this->service->create($data);
        
        // Converti oggetto Model in array per la risposta JSON
        if ($result['success'] && isset($result['data'])) {
            $result['data'] = $result['data']->toArray();
        }
        
        return $result;
    }
    
    /**
     * Aggiorna un cliente esistente
     */
    public function update($id, $data) {
        $result = $this->service->update($id, $data);
        
        // Converti oggetto Model in array per la risposta JSON
        if ($result['success'] && isset($result['data'])) {
            $result['data'] = $result['data']->toArray();
        }
        
        return $result;
    }
    
    /**
     * Elimina un cliente
     */
    public function delete($id) {
        return $this->service->delete($id);
    }
    
    /**
     * Ricerca clienti per nome/cognome/telefono
     */
    public function search($query) {
        $result = $this->service->search($query);
        
        // Converti oggetti Model in array per la risposta JSON
        if ($result['success'] && isset($result['data'])) {
            $result['data'] = array_map(function($client) {
                return $client->toArray();
            }, $result['data']);
        }
        
        return $result;
    }
}
