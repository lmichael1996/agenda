<?php
/**
 * AppointmentService - Logica business per la gestione degli appuntamenti
 */

class AppointmentService {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Ottieni tutti gli appuntamenti
     */
    public function getAll() {
        try {
            $stmt = $this->db->prepare('
                SELECT a.*, 
                       sa.client_id,
                       sa.start_time,
                       sa.note as super_note,
                       c.first_name as client_first_name, 
                       c.last_name as client_last_name,
                       s.name as service_name,
                       s.duration as service_duration,
                       s.price,
                       u.username as user_name,
                       u.color as user_color
                FROM appointments a
                JOIN super_appointments sa ON a.super_appointment_id = sa.id
                LEFT JOIN clients c ON sa.client_id = c.id
                LEFT JOIN services s ON a.service_id = s.id
                LEFT JOIN users u ON a.user_id = u.id
                ORDER BY sa.start_time DESC
            ');
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $appointments = [];
            foreach ($rows as $row) {
                $appointment = new Appointment($row);
                // Aggiungi info aggiuntive
                $appointment->client_name = trim(($row['client_first_name'] ?? '') . ' ' . ($row['client_last_name'] ?? ''));
                $appointment->service_name = $row['service_name'] ?? '';
                $appointment->client_id = $row['client_id'] ?? null;
                $appointment->user_name = $row['user_name'] ?? '';
                $appointment->user_color = $row['user_color'] ?? '#3b82f6';
                $appointment->price = $row['price'] ?? 0;
                $appointment->note = $row['super_note'] ?? '';
                $appointments[] = $appointment;
            }
            
            return ['success' => true, 'data' => $appointments];
        } catch (PDOException $e) {
            error_log("AppointmentService::getAll error: " . $e->getMessage());
            return [
                'success' => false, 
                'error' => 'Errore nel recupero degli appuntamenti'
            ];
        }
    }
    
    /**
     * Ottieni appuntamenti per data
     */
    public function getByDate($date) {
        try {
            $stmt = $this->db->prepare('
                SELECT a.*, 
                       c.first_name as client_first_name, 
                       c.last_name as client_last_name,
                       s.name as service_name
                FROM appointments a
                LEFT JOIN clients c ON a.client_id = c.id
                LEFT JOIN services s ON a.service_id = s.id
                WHERE DATE(a.start_time) = ?
                ORDER BY a.start_time
            ');
            $stmt->execute([$date]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $appointments = [];
            foreach ($rows as $row) {
                $appointment = new Appointment($row);
                $appointment->client_name = ($row['client_first_name'] ?? '') . ' ' . ($row['client_last_name'] ?? '');
                $appointment->service_name = $row['service_name'] ?? '';
                $appointments[] = $appointment;
            }
            
            return ['success' => true, 'data' => $appointments];
        } catch (PDOException $e) {
            error_log("AppointmentService::getByDate error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel recupero degli appuntamenti'];
        }
    }
    
    /**
     * Ottieni appuntamenti per cliente
     */
    public function getByClient($clientId) {
        try {
            $stmt = $this->db->prepare('
                SELECT 
                    a.id,
                    a.super_appointment_id,
                    sa.client_id,
                    c.first_name,
                    c.last_name,
                    CONCAT(c.first_name, " ", c.last_name) as client_name,
                    s.name as service_name,
                    a.duration,
                    DATE_FORMAT(sa.start_time, "%d-%m-%Y") as date,
                    DATE_FORMAT(sa.start_time, "%H:%i") as time,
                    sa.start_time,
                    sa.note,
                    u.username as user_name
                FROM appointments a
                JOIN super_appointments sa ON a.super_appointment_id = sa.id
                JOIN clients c ON sa.client_id = c.id
                LEFT JOIN services s ON a.service_id = s.id
                LEFT JOIN users u ON a.user_id = u.id
                WHERE sa.client_id = :client_id
                ORDER BY sa.start_time DESC
            ');
            $stmt->bindParam(':client_id', $clientId, PDO::PARAM_INT);
            $stmt->execute();
            $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true, 
                'appointments' => $appointments,
                'count' => count($appointments)
            ];
        } catch (PDOException $e) {
            error_log("AppointmentService::getByClient error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel recupero degli appuntamenti del cliente'];
        }
    }
    
    /**
     * Ottieni un appuntamento per ID
     */
    public function getById($id) {
        try {
            // 1. Recupera super_appointment
            $stmt = $this->db->prepare('
                SELECT sa.*, 
                       c.first_name, 
                       c.last_name,
                       c.phone,
                       c.notes as client_notes
                FROM super_appointments sa
                LEFT JOIN clients c ON sa.client_id = c.id
                WHERE sa.id = ?
            ');
            $stmt->execute([$id]);
            $superAppData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$superAppData) {
                return ['success' => false, 'error' => 'Appuntamento non trovato'];
            }
            
            $superApp = new SuperAppointment($superAppData);
            
            // 2. Recupera tutti gli appointments collegati
            $stmt = $this->db->prepare('
                SELECT a.*, 
                       s.name as service_name,
                       s.duration as service_duration,
                       u.username
                FROM appointments a
                LEFT JOIN services s ON a.service_id = s.id
                LEFT JOIN users u ON a.user_id = u.id
                WHERE a.super_appointment_id = ?
            ');
            $stmt->execute([$id]);
            $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // 3. Componi risposta
            return [
                'success' => true,
                'data' => [
                    'super_appointment' => $superApp,
                    'appointments' => $appointments
                ]
            ];
        } catch (PDOException $e) {
            error_log("AppointmentService::getById error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel recupero dell\'appuntamento'];
        }
    }
    
    /**
     * Crea un nuovo appuntamento
     */
    public function create($data) {
        // Verifica parametri obbligatori
        if (!isset($data['client_id']) || $data['client_id'] === '') {
            return ['success' => false, 'error' => 'Client ID obbligatorio'];
        }
        if (!isset($data['service_id']) || $data['service_id'] === '') {
            return ['success' => false, 'error' => 'Service ID obbligatorio'];
        }
        if (empty($data['start_time'])) {
            return ['success' => false, 'error' => 'Start time obbligatorio'];
        }
        if (empty($data['end_time'])) {
            return ['success' => false, 'error' => 'End time obbligatorio'];
        }
        
        try {
            $this->db->beginTransaction();
            
            // 1. Crea super_appointment
            $superAppData = [
                'client_id' => $data['client_id'],
                'start_time' => $data['start_time'],
                'note' => $data['notes'] ?? ''
            ];
            
            $superApp = new SuperAppointment($superAppData);
            
            // Valida super appointment
            $validation = $superApp->validate();
            if (!$validation['valid']) {
                return ['success' => false, 'error' => implode(', ', $validation['errors'])];
            }
            
            $stmt = $this->db->prepare('INSERT INTO super_appointments (client_id, start_time, note) VALUES (?, ?, ?)');
            $stmt->execute([
                $superApp->client_id,
                $superApp->start_time,
                $superApp->note
            ]);
            
            $superApp->id = $this->db->lastInsertId();
            
            // 2. Calcola duration in minuti
            $start = new DateTime($data['start_time']);
            $end = new DateTime($data['end_time']);
            $duration = ($end->getTimestamp() - $start->getTimestamp()) / 60;
            
            // 3. Crea appointment con servizio
            $appointmentData = [
                'super_appointment_id' => $superApp->id,
                'service_id' => $data['service_id'],
                'user_id' => $data['user_id'] ?? 1,
                'duration' => $duration
            ];
            
            $appointment = new Appointment($appointmentData);
            
            $stmt = $this->db->prepare('INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES (?, ?, ?, ?)');
            $stmt->execute([
                $appointment->super_appointment_id,
                $appointment->service_id,
                $appointment->user_id,
                $appointment->duration
            ]);
            
            $appointment->id = $this->db->lastInsertId();
            
            $this->db->commit();
            
            return [
                'success' => true, 
                'data' => [
                    'super_appointment' => $superApp,
                    'appointment' => $appointment
                ],
                'message' => 'Appuntamento creato con successo'
            ];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return ['success' => false, 'error' => 'Errore nella creazione dell\'appuntamento: ' . $e->getMessage()];
        }
    }
    
    /**
     * Aggiorna un appuntamento esistente
     */
    public function update($id, $data) {
        $data['id'] = $id;
        $appointment = new Appointment($data);
        
        // Valida i dati
        $validation = $appointment->validate();
        if (!$validation['valid']) {
            return ['success' => false, 'error' => implode(', ', $validation['errors'])];
        }
        
        // Verifica sovrapposizioni (escluso questo appuntamento)
        $overlap = $this->checkOverlap($appointment->start_time, $appointment->end_time, $id);
        if (!$overlap['success']) {
            return $overlap;
        }
        
        try {
            $stmt = $this->db->prepare('UPDATE appointments SET client_id = ?, service_id = ?, start_time = ?, end_time = ?, status = ?, notes = ? WHERE id = ?');
            $stmt->execute([
                $appointment->client_id,
                $appointment->service_id,
                $appointment->start_time,
                $appointment->end_time,
                $appointment->status,
                $appointment->notes,
                $id
            ]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'data' => $appointment, 'message' => 'Appuntamento aggiornato con successo'];
            } else {
                return ['success' => false, 'error' => 'Appuntamento non trovato o nessuna modifica'];
            }
        } catch (PDOException $e) {
            error_log("AppointmentService::update error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nell\'aggiornamento dell\'appuntamento'];
        }
    }
    
    /**
     * Elimina un appuntamento
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare('DELETE FROM appointments WHERE id = ?');
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Appuntamento eliminato con successo'];
            } else {
                return ['success' => false, 'error' => 'Appuntamento non trovato'];
            }
        } catch (PDOException $e) {
            error_log("AppointmentService::delete error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nell\'eliminazione dell\'appuntamento'];
        }
    }
    
    /**
     * Verifica sovrapposizioni temporali
     */
    private function checkOverlap($start_time, $end_time, $excludeId = null) {
        try {
            $sql = 'SELECT COUNT(*) FROM appointments WHERE status != "cancelled" AND (
                (start_time < ? AND end_time > ?) OR
                (start_time < ? AND end_time > ?) OR
                (start_time >= ? AND end_time <= ?)
            )';
            
            $params = [$end_time, $start_time, $end_time, $end_time, $start_time, $end_time];
            
            if ($excludeId !== null) {
                $sql .= ' AND id != ?';
                $params[] = $excludeId;
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            if ($stmt->fetchColumn() > 0) {
                return ['success' => false, 'error' => 'Esiste già un appuntamento in questo orario'];
            }
            
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("AppointmentService::checkOverlap error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nella verifica delle sovrapposizioni'];
        }
    }
}
