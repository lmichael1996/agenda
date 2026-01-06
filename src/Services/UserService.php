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
            $stmt = $this->db->prepare('SELECT id, username, color FROM users ORDER BY id');
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
            $stmt = $this->db->prepare('SELECT id, username, color FROM users WHERE id = ?');
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
            
            $stmt = $this->db->prepare('INSERT INTO users (username, password_hash, color) VALUES (?, ?, ?)');
            $stmt->execute([
                $user->username,
                $hashedPassword,
                $user->color
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
                $stmt = $this->db->prepare('UPDATE users SET username = ?, password_hash = ?, color = ? WHERE id = ?');
                $stmt->execute([
                    $user->username,
                    $hashedPassword,
                    $user->color,
                    $id
                ]);
            } else {
                $stmt = $this->db->prepare('UPDATE users SET username = ?, color = ? WHERE id = ?');
                $stmt->execute([
                    $user->username,
                    $user->color,
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
     * NOTA: L'utente con id=1 è l'utente default e non può essere eliminato
     */
    public function delete($id) {
        try {
            // Impedisci l'eliminazione dell'utente default
            if ($id == 1) {
                return ['success' => false, 'error' => 'Impossibile eliminare l\'utente di default'];
            }
            
            // Aggiorna gli appuntamenti assegnati a questo utente all'utente default
            $updateStmt = $this->db->prepare('UPDATE appointments SET user_id = 1 WHERE user_id = ?');
            $updateStmt->execute([$id]);
            $updatedAppointments = $updateStmt->rowCount();
            
            // Elimina l'utente
            $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                $message = 'Utente eliminato con successo';
                if ($updatedAppointments > 0) {
                    $message .= " ($updatedAppointments appuntamenti assegnati all'utente default)";
                }
                return ['success' => true, 'message' => $message];
            } else {
                return ['success' => false, 'error' => 'Utente non trovato'];
            }
        } catch (PDOException $e) {
            error_log("UserService::delete error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nell\'eliminazione dell\'utente'];
        }
    }
    
    /**
     * Salvataggio batch di tutti gli utenti
     * Strategia: mantiene gli utenti presenti nell'array, rimuove gli altri
     * Fa UPDATE per utenti esistenti, INSERT per nuovi
     * NOTA: L'utente con id=1 è l'utente default e non può essere eliminato
     */
    public function saveAll($users) {
        if (!is_array($users)) {
            return ['success' => false, 'error' => 'Dati utenti non validi'];
        }
        
        try {
            $this->db->beginTransaction();
            
            // Ottieni gli ID degli utenti che vogliamo mantenere
            $keepIds = array_filter(array_map(function($u) {
                return !empty($u['id']) && !str_starts_with($u['id'], 'temp_') ? $u['id'] : null;
            }, $users));
            
            // Assicurati che l'utente default (id=1) sia sempre presente
            if (!in_array(1, $keepIds)) {
                $keepIds[] = 1;
            }
            
            // Aggiorna gli appuntamenti degli utenti che verranno eliminati all'utente default
            $placeholders = implode(',', array_fill(0, count($keepIds), '?'));
            $updateStmt = $this->db->prepare("UPDATE appointments SET user_id = 1 WHERE user_id NOT IN ($placeholders)");
            $updateStmt->execute($keepIds);
            $updatedAppointments = $updateStmt->rowCount();
            if ($updatedAppointments > 0) {
                error_log("UserService::saveAll: $updatedAppointments appuntamenti assegnati all'utente default");
            }
            
            // Elimina gli utenti che NON sono nella lista (tranne l'utente default)
            if (!empty($keepIds)) {
                $deleteStmt = $this->db->prepare("DELETE FROM users WHERE id NOT IN ($placeholders) AND id != 1");
                $deleteStmt->execute($keepIds);
                error_log("UserService::saveAll: Eliminati " . $deleteStmt->rowCount() . " utenti non presenti nella lista");
            }
            
            $insertStmt = $this->db->prepare('INSERT INTO users (username, password_hash, type_role, color) VALUES (?, ?, ?, ?)');
            $updateStmt = $this->db->prepare('UPDATE users SET username = ?, type_role = ?, color = ? WHERE id = ?');
            $updatePassStmt = $this->db->prepare('UPDATE users SET username = ?, password_hash = ?, type_role = ?, color = ? WHERE id = ?');
            
            $errors = [];
            $inserted = 0;
            $updated = 0;
            
            foreach ($users as $user) {
                $username = $user['username'] ?? '';
                $password = $user['password'] ?? '';
                $role = $user['role'] ?? 'user';
                $color = $user['color'] ?? '#3498db';
                $userId = $user['id'] ?? null;
                
                if (empty($username)) {
                    continue;
                }
                
                // Validazione
                if (strlen($username) < 3) {
                    $errors[] = "Username troppo corto per $username";
                    continue;
                }
                
                if (!in_array($role, ['user', 'admin'])) {
                    $errors[] = "Ruolo non valido per $username";
                    continue;
                }
                
                $isNewUser = empty($userId) || str_starts_with($userId, 'temp_');
                
                if ($isNewUser) {
                    // NUOVO UTENTE: password obbligatoria
                    if (empty($password)) {
                        $errors[] = "Password obbligatoria per nuovo utente $username";
                        continue;
                    }
                    
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $insertStmt->execute([$username, $passwordHash, $role, $color]);
                    $inserted++;
                } else {
                    // UTENTE ESISTENTE: UPDATE
                    if (!empty($password)) {
                        // Con nuova password
                        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                        $updatePassStmt->execute([$username, $passwordHash, $role, $color, $userId]);
                    } else {
                        // Senza nuova password (mantieni quella esistente)
                        $updateStmt->execute([$username, $role, $color, $userId]);
                    }
                    $updated++;
                }
            }
            
            if (!empty($errors)) {
                $this->db->rollBack();
                error_log("UserService::saveAll errors: " . implode(', ', $errors));
                return ['success' => false, 'error' => 'Errori durante il salvataggio', 'details' => $errors];
            }
            
            if ($inserted === 0 && $updated === 0) {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'Nessun utente valido da salvare'];
            }
            
            $this->db->commit();
            error_log("UserService::saveAll: Inseriti $inserted, aggiornati $updated utenti");
            return ['success' => true, 'message' => "Salvati con successo: $inserted nuovi, $updated aggiornati"];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("UserService::saveAll error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Errore nel salvataggio degli utenti', 'details' => $e->getMessage()];
        }
    }
}
