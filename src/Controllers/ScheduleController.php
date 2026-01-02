<?php
/**
 * Controller per la gestione degli orari di lavoro
 */

class ScheduleController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Ottieni tutti gli orari di lavoro
     */
    public function getAll() {
        try {
            $stmt = $this->db->prepare('
                SELECT id, day_of_week, opening_time, closing_time, 
                       lunch_break_enabled, break_start, break_end, is_closed
                FROM schedule 
                ORDER BY FIELD(day_of_week, "lunedi", "martedi", "mercoledi", "giovedi", "venerdi", "sabato", "domenica")
            ');
            $stmt->execute();
            
            return [
                'success' => true,
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (PDOException $e) {
            error_log("Errore getAll schedule: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel recupero degli orari'];
        }
    }
    
    /**
     * Ottieni orario di un giorno specifico
     */
    public function getByDay($day) {
        try {
            $stmt = $this->db->prepare('
                SELECT id, day_of_week, opening_time, closing_time, 
                       lunch_break_enabled, break_start, break_end, is_closed
                FROM schedule 
                WHERE day_of_week = ?
            ');
            $stmt->execute([$day]);
            $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($schedule) {
                return ['success' => true, 'data' => $schedule];
            } else {
                return ['success' => false, 'error' => 'Orario non trovato'];
            }
        } catch (PDOException $e) {
            error_log("Errore getByDay schedule: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel recupero dell\'orario'];
        }
    }
    
    /**
     * Aggiorna l'orario di un giorno
     */
    public function updateDay($day, $data) {
        $errors = $this->validate($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            // Verifica se esiste già un record per questo giorno
            $stmt = $this->db->prepare('SELECT id FROM schedule WHERE day_of_week = ?');
            $stmt->execute([$day]);
            $exists = $stmt->fetch();
            
            if ($exists) {
                // Aggiorna
                $stmt = $this->db->prepare('
                    UPDATE schedule 
                    SET opening_time = ?, closing_time = ?, 
                        lunch_break_enabled = ?, break_start = ?, break_end = ?, is_closed = ?
                    WHERE day_of_week = ?
                ');
                $stmt->execute([
                    $data['opening_time'] ?? null,
                    $data['closing_time'] ?? null,
                    isset($data['lunch_break_enabled']) ? (int)$data['lunch_break_enabled'] : 0,
                    $data['break_start'] ?? null,
                    $data['break_end'] ?? null,
                    isset($data['is_closed']) ? (int)$data['is_closed'] : 0,
                    $day
                ]);
            } else {
                // Inserisci
                $stmt = $this->db->prepare('
                    INSERT INTO schedule (day_of_week, opening_time, closing_time, 
                                        lunch_break_enabled, break_start, break_end, is_closed)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ');
                $stmt->execute([
                    $day,
                    $data['opening_time'] ?? null,
                    $data['closing_time'] ?? null,
                    isset($data['lunch_break_enabled']) ? (int)$data['lunch_break_enabled'] : 0,
                    $data['break_start'] ?? null,
                    $data['break_end'] ?? null,
                    isset($data['is_closed']) ? (int)$data['is_closed'] : 0
                ]);
            }
            
            return ['success' => true, 'message' => 'Orario aggiornato con successo'];
        } catch (PDOException $e) {
            error_log("Errore updateDay schedule: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nell\'aggiornamento dell\'orario'];
        }
    }
    
    /**
     * Salvataggio batch di tutti gli orari
     */
    public function saveAll($schedules) {
        if (!is_array($schedules)) {
            return ['success' => false, 'error' => 'Dati orari non validi'];
        }
        
        try {
            $this->db->beginTransaction();
            
            $errors = [];
            foreach ($schedules as $schedule) {
                $day = $schedule['day_of_week'] ?? '';
                if (empty($day)) {
                    continue;
                }
                
                $validationErrors = $this->validate($schedule);
                if (!empty($validationErrors)) {
                    $errors = array_merge($errors, $validationErrors);
                    continue;
                }
                
                // Verifica esistenza
                $stmt = $this->db->prepare('SELECT id FROM schedule WHERE day_of_week = ?');
                $stmt->execute([$day]);
                $exists = $stmt->fetch();
                
                if ($exists) {
                    $stmt = $this->db->prepare('
                        UPDATE schedule 
                        SET opening_time = ?, closing_time = ?, 
                            lunch_break_enabled = ?, break_start = ?, break_end = ?, is_closed = ?
                        WHERE day_of_week = ?
                    ');
                    $stmt->execute([
                        $schedule['opening_time'] ?? null,
                        $schedule['closing_time'] ?? null,
                        isset($schedule['lunch_break_enabled']) ? (int)$schedule['lunch_break_enabled'] : 0,
                        $schedule['break_start'] ?? null,
                        $schedule['break_end'] ?? null,
                        isset($schedule['is_closed']) ? (int)$schedule['is_closed'] : 0,
                        $day
                    ]);
                } else {
                    $stmt = $this->db->prepare('
                        INSERT INTO schedule (day_of_week, opening_time, closing_time, 
                                            lunch_break_enabled, break_start, break_end, is_closed)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ');
                    $stmt->execute([
                        $day,
                        $schedule['opening_time'] ?? null,
                        $schedule['closing_time'] ?? null,
                        isset($schedule['lunch_break_enabled']) ? (int)$schedule['lunch_break_enabled'] : 0,
                        $schedule['break_start'] ?? null,
                        $schedule['break_end'] ?? null,
                        isset($schedule['is_closed']) ? (int)$schedule['is_closed'] : 0
                    ]);
                }
            }
            
            if (!empty($errors)) {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'Errori durante il salvataggio', 'details' => $errors];
            }
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Orari salvati con successo'];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Errore saveAll schedule: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel salvataggio degli orari'];
        }
    }
    
    /**
     * Validazione dati orario
     */
    private function validate($data) {
        $errors = [];
        
        // Se il giorno è chiuso, non serve validare gli orari
        if (isset($data['is_closed']) && $data['is_closed'] == 1) {
            return $errors;
        }
        
        // Validazione logica orari
        if (isset($data['opening_time']) && isset($data['closing_time'])) {
            if ($data['opening_time'] >= $data['closing_time']) {
                $errors[] = 'L\'orario di apertura deve essere precedente a quello di chiusura';
            }
        }
        
        // Validazione pausa pranzo (solo se abilitata)
        if (isset($data['lunch_break_enabled']) && $data['lunch_break_enabled'] == 1) {
            if (isset($data['break_start']) && isset($data['break_end'])) {
                if ($data['break_start'] >= $data['break_end']) {
                    $errors[] = 'L\'inizio pausa deve essere precedente alla fine pausa';
                }
                
                if (isset($data['opening_time']) && isset($data['closing_time'])) {
                    if ($data['break_start'] <= $data['opening_time'] || $data['break_end'] >= $data['closing_time']) {
                        $errors[] = 'La pausa pranzo deve essere compresa nell\'orario di lavoro';
                    }
                }
            }
        }
        
        return $errors;
    }
}
