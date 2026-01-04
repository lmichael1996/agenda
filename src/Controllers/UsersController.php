<?php
/**
 * Controller per la gestione degli utenti
 * Delega la logica business a UserService
 */

require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Services/UserService.php';

class UsersController {
    private $service;
    
    public function __construct($db) {
        $this->service = new UserService($db);
    }
    
    /**
     * Ottieni tutti gli utenti
     */
    public function getAll() {
        $result = $this->service->getAll();
        
        if ($result['success'] && isset($result['data'])) {
            $users = array_map(function($user) {
                return $user->toArray();
            }, $result['data']);
            
            // Formato per compatibilità frontend
            return [
                'success' => true,
                'users' => $users
            ];
        }
        
        return $result;
    }
    
    /**
     * Ottieni un utente specifico
     */
    public function getById($id) {
        $result = $this->service->getById($id);
        
        if ($result['success'] && isset($result['data'])) {
            $result['data'] = $result['data']->toArray();
        }
        
        return $result;
    }
    
    /**
     * Crea un nuovo utente
     */
    public function create($data) {
        $result = $this->service->create($data);
        
        if ($result['success'] && isset($result['data'])) {
            $result['data'] = $result['data']->toArray();
        }
        
        return $result;
    }
    
    /**
     * Aggiorna un utente esistente
     */
    public function update($id, $data) {
        $result = $this->service->update($id, $data);
        
        if ($result['success'] && isset($result['data'])) {
            $result['data'] = $result['data']->toArray();
        }
        
        return $result;
    }
    
    /**
     * Elimina un utente
     */
    public function delete($id) {
        return $this->service->delete($id);
    }
}
