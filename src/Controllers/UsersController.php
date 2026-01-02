<?php
/**
 * Controller per la gestione degli utenti
 */

class UsersController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Ottieni tutti gli utenti
     */
    public function getAll() {
        try {
            $stmt = $this->db->prepare('SELECT id, username, color, is_active FROM users');
            $stmt->execute();
            
            return [
                'success' => true,
                'users' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (PDOException $e) {
            error_log("Errore getAll users: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel recupero degli utenti'];
        }
    }
    
    /**
     * Ottieni un utente specifico
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare('SELECT id, username, color, is_active FROM users WHERE id = ?');
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                return ['success' => true, 'user' => $user];
            } else {
                return ['success' => false, 'error' => 'Utente non trovato'];
            }
        } catch (PDOException $e) {
            error_log("Errore getById user: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel recupero dell\'utente'];
        }
    }
    
    /**
     * Crea un nuovo utente
     */
    public function create($data) {
        $errors = $this->validate($data, true);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare('INSERT INTO users (username, password_hash, color, is_active) VALUES (?, ?, ?, ?)');
            $stmt->execute([
                trim($data['username']),
                $passwordHash,
                $data['color'] ?? '#3498db',
                isset($data['is_active']) ? (int)$data['is_active'] : 1
            ]);
            
            return [
                'success' => true,
                'id' => $this->db->lastInsertId(),
                'message' => 'Utente creato con successo'
            ];
        } catch (PDOException $e) {
            error_log("Errore create user: " . $e->getMessage());
            if ($e->getCode() == 23000) {
                return ['success' => false, 'error' => 'Username già esistente'];
            }
            return ['success' => false, 'error' => 'Errore nella creazione dell\'utente'];
        }
    }
    
    /**
     * Aggiorna un utente esistente
     */
    public function update($id, $data) {
        // Password non obbligatoria per l'aggiornamento
        $requirePassword = !empty($data['password']);
        $errors = $this->validate($data, $requirePassword);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        try {
            // Se c'è una nuova password, aggiornala
            if (!empty($data['password'])) {
                $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmt = $this->db->prepare('UPDATE users SET username = ?, password_hash = ?, color = ?, is_active = ? WHERE id = ?');
                $stmt->execute([
                    trim($data['username']),
                    $passwordHash,
                    $data['color'] ?? '#3498db',
                    isset($data['is_active']) ? (int)$data['is_active'] : 1,
                    $id
                ]);
            } else {
                // Mantieni la password esistente
                $stmt = $this->db->prepare('UPDATE users SET username = ?, color = ?, is_active = ? WHERE id = ?');
                $stmt->execute([
                    trim($data['username']),
                    $data['color'] ?? '#3498db',
                    isset($data['is_active']) ? (int)$data['is_active'] : 1,
                    $id
                ]);
            }
            
            return ['success' => true, 'message' => 'Utente aggiornato con successo'];
        } catch (PDOException $e) {
            error_log("Errore update user: " . $e->getMessage());
            if ($e->getCode() == 23000) {
                return ['success' => false, 'error' => 'Username già esistente'];
            }
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
            
            return ['success' => true, 'message' => 'Utente eliminato con successo'];
        } catch (PDOException $e) {
            error_log("Errore delete user: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nell\'eliminazione dell\'utente'];
        }
    }
    
    /**
     * Salvataggio batch di tutti gli utenti
     */
    public function saveAll($users) {
        if (!is_array($users)) {
            return ['success' => false, 'error' => 'Dati utenti non validi'];
        }
        
        try {
            $this->db->beginTransaction();
            
            // Carica le password esistenti per preservarle se vuote
            $stmt = $this->db->prepare('SELECT username, password_hash FROM users');
            $stmt->execute();
            $existingPasswords = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $existingPasswords[$row['username']] = $row['password_hash'];
            }
            
            // Elimina tutti gli utenti esistenti
            $this->db->exec('DELETE FROM users');
            
            // Inserisce tutti i nuovi utenti
            $stmt = $this->db->prepare('INSERT INTO users (username, password_hash, color, is_active) VALUES (?, ?, ?, ?)');
            
            $errors = [];
            foreach ($users as $user) {
                $username = $user['username'] ?? '';
                $password = $user['password'] ?? '';
                $color = $user['color'] ?? '#3498db';
                $is_active = isset($user['is_active']) ? (int)$user['is_active'] : 1;
                
                if (empty($username)) {
                    continue; // Salta utenti senza username
                }
                
                // Gestione password
                if (empty($password) && isset($existingPasswords[$username])) {
                    $password_hash = $existingPasswords[$username];
                } elseif (!empty($password)) {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                } else {
                    $errors[] = "Utente {$username}: Password obbligatoria per nuovi utenti";
                    continue;
                }
                
                $stmt->execute([$username, $password_hash, $color, $is_active]);
            }
            
            if (!empty($errors)) {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'Errori durante il salvataggio', 'details' => $errors];
            }
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Utenti salvati con successo'];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Errore saveAll users: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel salvataggio degli utenti'];
        }
    }
    
    /**
     * Autenticazione utente
     */
    public function authenticate($username, $password) {
        try {
            $stmt = $this->db->prepare('SELECT id, username, password_hash, color, is_active FROM users WHERE username = ? AND is_active = 1');
            $stmt->execute([trim($username)]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password_hash'])) {
                unset($user['password_hash']);
                return ['success' => true, 'user' => $user];
            } else {
                return ['success' => false, 'error' => 'Credenziali non valide'];
            }
        } catch (PDOException $e) {
            error_log("Errore authenticate user: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nell\'autenticazione'];
        }
    }
    
    /**
     * Validazione dati utente
     */
    private function validate($data, $requirePassword = true) {
        $errors = [];
        
        if (empty(trim($data['username'] ?? ''))) {
            $errors[] = 'Username obbligatorio';
        }
        
        if ($requirePassword && empty($data['password'])) {
            $errors[] = 'Password obbligatoria';
        }
        
        if (!empty($data['password']) && strlen($data['password']) < 4) {
            $errors[] = 'La password deve essere di almeno 4 caratteri';
        }
        
        if (!empty($data['color']) && !preg_match('/^#[0-9A-Fa-f]{6}$/', $data['color'])) {
            $errors[] = 'Formato colore non valido (es: #3498db)';
        }
        
        return $errors;
    }
}
