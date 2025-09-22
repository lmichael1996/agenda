<?php
require_once __DIR__ . '/BaseRepository.php';

/**
 * ScheduleRepository - Gestione configurazione orari (Singleton)
 */
class ScheduleRepository extends BaseRepository {
    protected $table = 'schedule_config';
    
    /**
     * Ottiene la configurazione singleton
     */
    public function getConfig() {
        $query = "SELECT * FROM {$this->table} LIMIT 1";
        $config = $this->selectOne($query);
        
        if (!$config) {
            // Crea configurazione di default se non esiste
            $this->createDefaultConfig();
            $config = $this->selectOne($query);
        }
        
        return $config;
    }
    
    /**
     * Crea configurazione di default
     */
    private function createDefaultConfig() {
        $query = "INSERT INTO {$this->table} 
                  (opening_time, closing_time, lunch_break_enabled, break_start, break_end, working_days, timezone) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        return $this->insert($query, [
            '08:00:00',
            '18:00:00',
            1,
            '12:30:00',
            '13:30:00',
            '1111100',
            'Europe/Rome'
        ]);
    }
    
    /**
     * Aggiorna la configurazione singleton
     */
    public function updateConfig($data) {
        $config = $this->getConfig();
        
        if ($config) {
            // Aggiorna configurazione esistente
            $query = "UPDATE {$this->table} SET 
                      opening_time = ?, 
                      closing_time = ?, 
                      lunch_break_enabled = ?, 
                      break_start = ?, 
                      break_end = ?, 
                      working_days = ?, 
                      timezone = ? 
                      WHERE id = ?";
            
            return $this->update($query, [
                $data['opening_time'],
                $data['closing_time'],
                $data['lunch_break_enabled'] ? 1 : 0,
                $data['break_start'],
                $data['break_end'],
                $data['working_days'],
                $data['timezone'],
                $config['id']
            ]);
        } else {
            // Crea nuova configurazione
            $query = "INSERT INTO {$this->table} 
                      (opening_time, closing_time, lunch_break_enabled, break_start, break_end, working_days, timezone) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            return $this->insert($query, [
                $data['opening_time'],
                $data['closing_time'],
                $data['lunch_break_enabled'] ? 1 : 0,
                $data['break_start'],
                $data['break_end'],
                $data['working_days'],
                $data['timezone']
            ]);
        }
    }
    
    /**
     * Verifica se un giorno è lavorativo
     */
    public function isWorkingDay($dayOfWeek) {
        $config = $this->getConfig();
        
        if (!$config || !isset($config['working_days'])) {
            return false;
        }
        
        // $dayOfWeek: 0=Domenica, 1=Lunedì, ..., 6=Sabato
        // Conversione per il nostro formato: 0=Lunedì, ..., 6=Domenica
        $index = ($dayOfWeek + 6) % 7;
        
        return isset($config['working_days'][$index]) && $config['working_days'][$index] === '1';
    }
    
    /**
     * Ottiene gli orari di lavoro per un giorno
     */
    public function getWorkingHours($dayOfWeek = null) {
        $config = $this->getConfig();
        
        if (!$config) {
            return null;
        }
        
        $result = [
            'opening_time' => substr($config['opening_time'], 0, 5), // HH:MM
            'closing_time' => substr($config['closing_time'], 0, 5),  // HH:MM
            'lunch_break_enabled' => (bool)$config['lunch_break_enabled'],
            'break_start' => substr($config['break_start'], 0, 5),    // HH:MM
            'break_end' => substr($config['break_end'], 0, 5),        // HH:MM
            'timezone' => $config['timezone']
        ];
        
        // Se viene specificato un giorno, verifica se è lavorativo
        if ($dayOfWeek !== null) {
            $result['is_working_day'] = $this->isWorkingDay($dayOfWeek);
        }
        
        return $result;
    }
    
    /**
     * Ottiene tutti i giorni lavorativi della settimana
     */
    public function getWorkingDays() {
        $config = $this->getConfig();
        
        if (!$config || !isset($config['working_days'])) {
            return [];
        }
        
        $days = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];
        $workingDays = [];
        
        for ($i = 0; $i < 7; $i++) {
            if (isset($config['working_days'][$i]) && $config['working_days'][$i] === '1') {
                $workingDays[] = $days[$i];
            }
        }
        
        return $workingDays;
    }
    
    /**
     * Verifica se un orario è nell'intervallo lavorativo
     */
    public function isWorkingTime($time, $dayOfWeek = null) {
        $hours = $this->getWorkingHours($dayOfWeek);
        
        if (!$hours || ($dayOfWeek !== null && !$hours['is_working_day'])) {
            return false;
        }
        
        // Converti gli orari in timestamp per il confronto
        $timeStamp = strtotime($time);
        $openingStamp = strtotime($hours['opening_time']);
        $closingStamp = strtotime($hours['closing_time']);
        
        $isInWorkingHours = $timeStamp >= $openingStamp && $timeStamp <= $closingStamp;
        
        // Se la pausa pranzo è abilitata, verifica che non sia nell'intervallo di pausa
        if ($isInWorkingHours && $hours['lunch_break_enabled']) {
            $breakStartStamp = strtotime($hours['break_start']);
            $breakEndStamp = strtotime($hours['break_end']);
            
            if ($timeStamp >= $breakStartStamp && $timeStamp <= $breakEndStamp) {
                return false; // È nella pausa pranzo
            }
        }
        
        return $isInWorkingHours;
    }
    
    /**
     * Reset alla configurazione di default
     */
    public function resetToDefault() {
        // Elimina configurazione esistente
        $this->delete("DELETE FROM {$this->table}");
        
        // Crea configurazione di default
        return $this->createDefaultConfig();
    }
}
?>