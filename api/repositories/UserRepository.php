<?php
require_once __DIR__ . '/BaseRepository.php';

/**
 * UserRepository - Gestione utenti
 */
class UserRepository extends BaseRepository {
    protected $table = 'users';
    
    /**
     * Trova un utente per username
     */
    public function findByUsername($username) {
        $query = "SELECT * FROM {$this->table} WHERE username = ?";
        return $this->selectOne($query, [$username]);
    }
    
    /**
     * Crea un nuovo utente
     */
    public function create($data) {
        $query = "INSERT INTO {$this->table} (username, password_hash, color, is_active) 
                  VALUES (?, ?, ?, ?)";
        
        return $this->insert($query, [
            $data['username'],
            $data['password_hash'],
            $data['color'] ?? '#3498db',
            $data['is_active'] ?? 1
        ]);
    }
    
    /**
     * Aggiorna un utente
     */
    public function updateUser($id, $data) {
        $fields = [];
        $params = [];
        
        if (isset($data['username'])) {
            $fields[] = 'username = ?';
            $params[] = $data['username'];
        }
        
        if (isset($data['password_hash'])) {
            $fields[] = 'password_hash = ?';
            $params[] = $data['password_hash'];
        }
        
        if (isset($data['color'])) {
            $fields[] = 'color = ?';
            $params[] = $data['color'];
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
     * Trova utenti attivi
     */
    public function findActiveUsers() {
        $query = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY username";
        return $this->select($query);
    }
    
    /**
     * Verifica se uno username esiste già
     */
    public function usernameExists($username, $excludeId = null) {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE username = ?";
        $params = [$username];
        
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->selectOne($query, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Cambia password utente
     */
    public function changePassword($id, $newPasswordHash) {
        $query = "UPDATE {$this->table} SET password_hash = ? WHERE id = ?";
        return $this->update($query, [$newPasswordHash, $id]);
    }
    
    /**
     * Attiva/disattiva utente
     */
    public function setActive($id, $isActive) {
        $query = "UPDATE {$this->table} SET is_active = ? WHERE id = ?";
        return $this->update($query, [$isActive ? 1 : 0, $id]);
    }
}
?>