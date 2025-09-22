<?php
/**
 * Classe base Repository
 * @author Michael Leanza
 */

require_once __DIR__ . '/../../config/database.php';

abstract class BaseRepository {
    protected $db;
    protected $table;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        $this->db = Database::getConnection();
    }
    
    protected function select($query, $params = []) {
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log("Select query failed: " . $e->getMessage());
            throw new Exception("Errore durante la lettura dei dati");
        }
    }
    
    /**
     * Esegue una query SELECT per un singolo record
     */
    protected function selectOne($query, $params = []) {
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log("SelectOne query failed: " . $e->getMessage());
            throw new Exception("Errore durante la lettura del dato");
        }
    }
    
    /**
     * Esegue una query INSERT
     */
    protected function insert($query, $params = []) {
        try {
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute($params);
            return $this->db->lastInsertId();
        } catch(PDOException $e) {
            error_log("Insert query failed: " . $e->getMessage());
            throw new Exception("Errore durante l'inserimento");
        }
    }
    
    /**
     * Esegue una query UPDATE
     */
    protected function update($query, $params = []) {
        try {
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute($params);
            return $stmt->rowCount();
        } catch(PDOException $e) {
            error_log("Update query failed: " . $e->getMessage());
            throw new Exception("Errore durante l'aggiornamento");
        }
    }
    
    /**
     * Esegue una query DELETE
     */
    protected function delete($query, $params = []) {
        try {
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute($params);
            return $stmt->rowCount();
        } catch(PDOException $e) {
            error_log("Delete query failed: " . $e->getMessage());
            throw new Exception("Errore durante l'eliminazione");
        }
    }
    
    /**
     * Trova un record per ID
     */
    public function findById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->selectOne($query, [$id]);
    }
    
    /**
     * Trova tutti i record
     */
    public function findAll() {
        $query = "SELECT * FROM {$this->table}";
        return $this->select($query);
    }
    
    /**
     * Elimina un record per ID
     */
    public function deleteById($id) {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->delete($query, [$id]);
    }
    
    /**
     * Conta i record nella tabella
     */
    public function count($where = '', $params = []) {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        if ($where) {
            $query .= " WHERE $where";
        }
        $result = $this->selectOne($query, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Inizia una transazione
     */
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }
    
    /**
     * Conferma una transazione
     */
    public function commit() {
        return $this->db->commit();
    }
    
    /**
     * Annulla una transazione
     */
    public function rollback() {
        return $this->db->rollback();
    }
}
?>