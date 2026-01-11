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
    
    /**
     * Ottiene tutti gli appuntamenti per una settimana specifica
     * @param string $weekStart Data inizio settimana (formato Y-m-d)
     * @param string $weekEnd Data fine settimana (formato Y-m-d)
     * @return array Array di appuntamenti formattati per il calendario
     */
    public function getWeekAppointments($weekStart, $weekEnd) {
        $result = $this->service->getAll();
        
        if (!$result['success']) {
            return [];
        }
        
        $appointments = $result['data'];
        
        // Filtra per settimana
        $filtered = array_filter($appointments, function($apt) use ($weekStart, $weekEnd) {
            if (!isset($apt->start_time)) {
                return false;
            }
            
            $aptDate = date('Y-m-d', strtotime($apt->start_time));
            return $aptDate >= $weekStart && $aptDate <= $weekEnd;
        });
        
        return $this->formatForCalendar($filtered);
    }
    
    /**
     * Formatta gli appuntamenti per il calendario
     * @param array $appointments Array di oggetti Appointment
     * @return array Array di eventi formattati per il calendario
     */
    private function formatForCalendar($appointments) {
        $events = [];
        
        foreach ($appointments as $apt) {
            $startTime = new DateTime($apt->start_time);
            
            $events[] = [
                'id' => $apt->id,
                'super_appointment_id' => $apt->super_appointment_id ?? null,
                'date' => $startTime->format('d-m-Y'),
                'time' => $startTime->format('H:i'),
                'datetime' => $startTime->format('Y-m-d H:i:s'),
                'client_id' => $apt->client_id ?? null,
                'client_name' => $apt->client_name ?? '',
                'client_phone' => $apt->client_phone ?? '',
                'service_id' => $apt->service_id ?? null,
                'service_name' => $apt->service_name ?? '',
                'duration' => $apt->duration ?? 30,
                'price' => $apt->price ?? 0,
                'user_id' => $apt->user_id ?? null,
                'user_name' => $apt->user_name ?? 'Non assegnato',
                'user_color' => $apt->user_color ?? '#95a5a6',
                'note' => $apt->note ?? ''
            ];
        }
        
        return $events;
    }
    
    /**
     * Ottiene gli appuntamenti formattati per la settimana corrente
     * @return string JSON degli appuntamenti
     */
    public function getCurrentWeekAppointmentsJSON() {
        $today = new DateTime();
        $weekStart = clone $today;
        $weekStart->modify('monday this week');
        $weekEnd = clone $weekStart;
        $weekEnd->modify('+6 days');

        $appointments = $this->getWeekAppointments(
            $weekStart->format('Y-m-d'),
            $weekEnd->format('Y-m-d')
        );
        
        return json_encode($appointments, JSON_UNESCAPED_UNICODE);
    }
}
