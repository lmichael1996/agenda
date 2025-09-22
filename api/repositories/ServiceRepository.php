<?php
require_once __DIR__ . '/BaseRepository.php';

/**
 * ServiceRepository - Gestione servizi
 */
class ServiceRepository extends BaseRepository {
    protected $table = 'services';
    
    /**
     * Crea un nuovo servizio
     */
    public function create($data) {
        $query = "INSERT INTO {$this->table} (name_service, price, duration_minutes, description_service, is_active) 
                  VALUES (?, ?, ?, ?, ?)";
        
        return $this->insert($query, [
            $data['name_service'],
            $data['price'] ?? 0.00,
            $data['duration_minutes'] ?? 30,
            $data['description_service'] ?? '',
            $data['is_active'] ?? 1
        ]);
    }
    
    /**
     * Aggiorna un servizio
     */
    public function updateService($id, $data) {
        $fields = [];
        $params = [];
        
        if (isset($data['name_service'])) {
            $fields[] = 'name_service = ?';
            $params[] = $data['name_service'];
        }
        
        if (isset($data['price'])) {
            $fields[] = 'price = ?';
            $params[] = $data['price'];
        }
        
        if (isset($data['duration_minutes'])) {
            $fields[] = 'duration_minutes = ?';
            $params[] = $data['duration_minutes'];
        }
        
        if (isset($data['description_service'])) {
            $fields[] = 'description_service = ?';
            $params[] = $data['description_service'];
        }
        
        if (isset($data['is_active'])) {
            $fields[] = 'is_active = ?';
            $params[] = $data['is_active'];
        }
        
        if (empty($fields)) {
            throw new Exception("Nessun campo da aggiornare");
        }
        
        $params[] = $id;
        $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return $this->update($query, $params);
    }
    
    /**
     * Trova servizi attivi
     */
    public function findActiveServices() {
        $query = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY name_service";
        return $this->select($query);
    }
    
    /**
     * Trova servizi per prezzo
     */
    public function findByPriceRange($minPrice = null, $maxPrice = null) {
        $query = "SELECT * FROM {$this->table} WHERE is_active = 1";
        $params = [];
        
        if ($minPrice !== null) {
            $query .= " AND price >= ?";
            $params[] = $minPrice;
        }
        
        if ($maxPrice !== null) {
            $query .= " AND price <= ?";
            $params[] = $maxPrice;
        }
        
        $query .= " ORDER BY price ASC";
        
        return $this->select($query, $params);
    }
    
    /**
     * Trova servizi per durata
     */
    public function findByDuration($minDuration = null, $maxDuration = null) {
        $query = "SELECT * FROM {$this->table} WHERE is_active = 1";
        $params = [];
        
        if ($minDuration !== null) {
            $query .= " AND duration_minutes >= ?";
            $params[] = $minDuration;
        }
        
        if ($maxDuration !== null) {
            $query .= " AND duration_minutes <= ?";
            $params[] = $maxDuration;
        }
        
        $query .= " ORDER BY duration_minutes ASC";
        
        return $this->select($query, $params);
    }
    
    /**
     * Cerca servizi per nome
     */
    public function searchByName($searchTerm) {
        $query = "SELECT * FROM {$this->table} 
                  WHERE is_active = 1 
                  AND (name_service LIKE ? OR description_service LIKE ?)
                  ORDER BY name_service";
        
        $searchPattern = "%$searchTerm%";
        return $this->select($query, [$searchPattern, $searchPattern]);
    }
    
    /**
     * Attiva/disattiva servizio
     */
    public function setActive($id, $isActive) {
        $query = "UPDATE {$this->table} SET is_active = ? WHERE id = ?";
        return $this->update($query, [$isActive ? 1 : 0, $id]);
    }
    
    /**
     * Statistiche servizi
     */
    public function getStats() {
        $stats = [];
        
        // Conta servizi attivi
        $stats['total_active'] = $this->count('is_active = 1');
        
        // Conta servizi totali
        $stats['total_all'] = $this->count();
        
        // Prezzo medio
        $avgPrice = $this->selectOne(
            "SELECT AVG(price) as avg_price FROM {$this->table} WHERE is_active = 1"
        );
        $stats['avg_price'] = round($avgPrice['avg_price'] ?? 0, 2);
        
        // Durata media
        $avgDuration = $this->selectOne(
            "SELECT AVG(duration_minutes) as avg_duration FROM {$this->table} WHERE is_active = 1"
        );
        $stats['avg_duration'] = round($avgDuration['avg_duration'] ?? 0);
        
        return $stats;
    }
}
?>