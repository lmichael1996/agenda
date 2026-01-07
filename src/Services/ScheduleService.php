<?php
/**
 * ScheduleService - Logica business per la gestione degli appuntamenti
 */

class ScheduleService {
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
                       c.first_name as client_first_name, 
                       c.last_name as client_last_name,
                       s.name as service_name
                FROM appointments a
                LEFT JOIN clients c ON a.client_id = c.id
                LEFT JOIN services s ON a.service_id = s.id
                ORDER BY a.start_time DESC
            ');
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $appointments = [];
            foreach ($rows as $row) {
                $appointment = new Appointment($row);
                // Aggiungi info aggiuntive
                $appointment->client_name = ($row['client_first_name'] ?? '') . ' ' . ($row['client_last_name'] ?? '');
                $appointment->service_name = $row['service_name'] ?? '';
                $appointments[] = $appointment;
            }
            
            return ['success' => true, 'data' => $appointments];
        } catch (PDOException $e) {
            error_log("ScheduleService::getAll error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel recupero degli appuntamenti'];
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
            error_log("ScheduleService::getByDate error: " . $e->getMessage());
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
            error_log("ScheduleService::getByClient error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel recupero degli appuntamenti del cliente'];
        }
    }
    
    /**
     * Ottieni un appuntamento per ID
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare('
                SELECT a.*, 
                       c.first_name as client_first_name, 
                       c.last_name as client_last_name,
                       s.name as service_name
                FROM appointments a
                LEFT JOIN clients c ON a.client_id = c.id
                LEFT JOIN services s ON a.service_id = s.id
                WHERE a.id = ?
            ');
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                $appointment = new Appointment($row);
                $appointment->client_name = ($row['client_first_name'] ?? '') . ' ' . ($row['client_last_name'] ?? '');
                $appointment->service_name = $row['service_name'] ?? '';
                return ['success' => true, 'data' => $appointment];
            } else {
                return ['success' => false, 'error' => 'Appuntamento non trovato'];
            }
        } catch (PDOException $e) {
            error_log("ScheduleService::getById error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel recupero dell\'appuntamento'];
        }
    }
    
    /**
     * Crea un nuovo appuntamento
     */
    public function create($data) {
        $appointment = new Appointment($data);
        
        // Valida i dati
        $validation = $appointment->validate();
        if (!$validation['valid']) {
            return ['success' => false, 'error' => implode(', ', $validation['errors'])];
        }
        
        // Verifica sovrapposizioni
        $overlap = $this->checkOverlap($appointment->start_time, $appointment->end_time);
        if (!$overlap['success']) {
            return $overlap;
        }
        
        try {
            $stmt = $this->db->prepare('INSERT INTO appointments (client_id, service_id, start_time, end_time, status, notes) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $appointment->client_id,
                $appointment->service_id,
                $appointment->start_time,
                $appointment->end_time,
                $appointment->status,
                $appointment->notes
            ]);
            
            $appointment->id = $this->db->lastInsertId();
            
            return ['success' => true, 'data' => $appointment, 'message' => 'Appuntamento creato con successo'];
        } catch (PDOException $e) {
            error_log("ScheduleService::create error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nella creazione dell\'appuntamento'];
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
            error_log("ScheduleService::update error: " . $e->getMessage());
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
            error_log("ScheduleService::delete error: " . $e->getMessage());
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
            error_log("ScheduleService::checkOverlap error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nella verifica delle sovrapposizioni'];
        }
    }
}
