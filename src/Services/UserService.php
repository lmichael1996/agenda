<?php
/**
 * UserService - Logica business per la gestione degli utenti
 */

class UserService {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Ottieni tutti gli utenti
     */
    public function getAll() {
        try {
            $stmt = $this->db->prepare('SELECT id, username, email, role, created_at FROM users ORDER BY username');
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $users = [];
            foreach ($rows as $row) {
                $users[] = new User($row);
            }
            
            return ['success' => true, 'data' => $users];
        } catch (PDOException $e) {
            error_log("UserService::getAll error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel recupero degli utenti'];
        }
    }
    
    /**
     * Ottieni un utente per ID
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare('SELECT id, username, email, role, created_at FROM users WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                return ['success' => true, 'data' => new User($row)];
            } else {
                return ['success' => false, 'error' => 'Utente non trovato'];
            }
        } catch (PDOException $e) {
            error_log("UserService::getById error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel recupero dell\'utente'];
        }
    }
    
    /**
     * Crea un nuovo utente
     */
    public function create($data) {
        $user = new User($data);
        
        // Valida i dati
        $validation = $user->validate();
        if (!$validation['valid']) {
            return ['success' => false, 'error' => implode(', ', $validation['errors'])];
        }
        
        // Valida password
        if (empty($data['password']) || strlen($data['password']) < 8) {
            return ['success' => false, 'error' => 'La password deve essere almeno 8 caratteri'];
        }
        
        try {
            // Verifica username univoco
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
            $stmt->execute([$user->username]);
            if ($stmt->fetchColumn() > 0) {
                return ['success' => false, 'error' => 'Username già in uso'];
            }
            
            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare('INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)');
            $stmt->execute([
                $user->username,
                $hashedPassword,
                $user->email,
                $user->role
            ]);
            
            $user->id = $this->db->lastInsertId();
            
            return ['success' => true, 'data' => $user, 'message' => 'Utente creato con successo'];
        } catch (PDOException $e) {
            error_log("UserService::create error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nella creazione dell\'utente'];
        }
    }
    
    /**
     * Aggiorna un utente esistente
     */
    public function update($id, $data) {
        $data['id'] = $id;
        $user = new User($data);
        
        // Valida i dati
        $validation = $user->validate();
        if (!$validation['valid']) {
            return ['success' => false, 'error' => implode(', ', $validation['errors'])];
        }
        
        try {
            // Verifica username univoco (escluso questo utente)
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE username = ? AND id != ?');
            $stmt->execute([$user->username, $id]);
            if ($stmt->fetchColumn() > 0) {
                return ['success' => false, 'error' => 'Username già in uso'];
            }
            
            // Se c'è una nuova password, aggiornala
            if (!empty($data['password'])) {
                if (strlen($data['password']) < 8) {
                    return ['success' => false, 'error' => 'La password deve essere almeno 8 caratteri'];
                }
                $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmt = $this->db->prepare('UPDATE users SET username = ?, password = ?, email = ?, role = ? WHERE id = ?');
                $stmt->execute([
                    $user->username,
                    $hashedPassword,
                    $user->email,
                    $user->role,
                    $id
                ]);
            } else {
                $stmt = $this->db->prepare('UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?');
                $stmt->execute([
                    $user->username,
                    $user->email,
                    $user->role,
                    $id
                ]);
            }
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'data' => $user, 'message' => 'Utente aggiornato con successo'];
            } else {
                return ['success' => false, 'error' => 'Utente non trovato o nessuna modifica'];
            }
        } catch (PDOException $e) {
            error_log("UserService::update error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nell\'aggiornamento dell\'utente'];
        }
    }
    
    /**
     * Elimina un utente
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Utente eliminato con successo'];
            } else {
                return ['success' => false, 'error' => 'Utente non trovato'];
            }
        } catch (PDOException $e) {
            error_log("UserService::delete error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nell\'eliminazione dell\'utente'];
        }
    }
}
