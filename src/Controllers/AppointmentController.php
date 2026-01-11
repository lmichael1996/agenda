<?php
/**
 * Controller per la gestione degli appuntamenti
 * Delega la logica business a AppointmentService
 */

require_once __DIR__ . '/../Models/Appointment.php';
require_once __DIR__ . '/../Models/SuperAppointment.php';
require_once __DIR__ . '/../Services/AppointmentService.php';

class AppointmentController {
    private $service;
    
    public function __construct($db) {
        $this->service = new AppointmentService($db);
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
     * Ottieni appuntamenti per cliente
     */
    public function getByClient($clientId) {
        return $this->service->getByClient($clientId);
    }
    
    /**
     * Ottieni un appuntamento specifico (super_appointment con tutti gli appointments)
     */
    public function getById($id) {
        $result = $this->service->getById($id);
        
        // Converti SuperAppointment Model in array
        if ($result['success'] && isset($result['data']['super_appointment'])) {
            $result['data']['super_appointment'] = $result['data']['super_appointment']->toArray();
        }
        
        return $result;
    }
    
    /**
     * Crea un nuovo appuntamento
     */
    public function create($data) {
        $result = $this->service->create($data);
        
        // Converti Models in array per JSON
        if ($result['success'] && isset($result['data'])) {
            if (isset($result['data']['super_appointment'])) {
                $result['data']['super_appointment'] = $result['data']['super_appointment']->toArray();
            }
            if (isset($result['data']['appointment'])) {
                $result['data']['appointment'] = $result['data']['appointment']->toArray();
            }
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
