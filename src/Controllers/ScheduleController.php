<?php
/**
 * Controller per la gestione degli appuntamenti
 * Delega la logica business a ScheduleService
 */

require_once __DIR__ . '/../Models/Appointment.php';
require_once __DIR__ . '/../Services/ScheduleService.php';

class ScheduleController {
    private $service;
    
    public function __construct($db) {
        $this->service = new ScheduleService($db);
    }
    
    /**
     * Ottieni tutti gli appuntamenti
     */
    public function getAll() {
        $result = $this->service->getAll();
        
        if ($result['success'] && isset($result['data'])) {
            $result['data'] = array_map(function($appointment) {
                return $appointment->toArray();
            }, $result['data']);
        }
        
        return $result;
    }
    
    /**
     * Ottieni appuntamenti per data
     */
    public function getByDate($date) {
        $result = $this->service->getByDate($date);
        
        if ($result['success'] && isset($result['data'])) {
            $result['data'] = array_map(function($appointment) {
                return $appointment->toArray();
            }, $result['data']);
        }
        
        return $result;
    }
    
    /**
     * Ottieni un appuntamento specifico
     */
    public function getById($id) {
        $result = $this->service->getById($id);
        
        if ($result['success'] && isset($result['data'])) {
            $result['data'] = $result['data']->toArray();
        }
        
        return $result;
    }
    
    /**
     * Crea un nuovo appuntamento
     */
    public function create($data) {
        $result = $this->service->create($data);
        
        if ($result['success'] && isset($result['data'])) {
            $result['data'] = $result['data']->toArray();
        }
        
        return $result;
    }
    
    /**
     * Aggiorna un appuntamento esistente
     */
    public function update($id, $data) {
        $result = $this->service->update($id, $data);
        
        if ($result['success'] && isset($result['data'])) {
            $result['data'] = $result['data']->toArray();
        }
        
        return $result;
    }
    
    /**
     * Elimina un appuntamento
     */
    public function delete($id) {
        return $this->service->delete($id);
    }
}
