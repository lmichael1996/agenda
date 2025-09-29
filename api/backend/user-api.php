<?php
// API semplice per gestione utenti
header('Content-Type: application/json');

require_once '../../config/db.php';

// Test connessione DB
if (!isset($conn) || $conn->connect_errno) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Errore connessione DB: ' . ($conn ? $conn->connect_error : 'connessione non inizializzata')
    ]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Check if requesting a specific user by ID
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            // Get single user details
            $userId = (int)$_GET['id'];
            $stmt = $conn->prepare('SELECT id, username, color, is_active FROM users WHERE id = ?');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($user = $result->fetch_assoc()) {
                echo json_encode(['success' => true, 'user' => $user]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Utente non trovato']);
            }
            $stmt->close();
        } else {
            // Lista utenti con prepared statement
            $stmt = $conn->prepare('SELECT id, username, color, is_active FROM users');
            $stmt->execute();
            $result = $stmt->get_result();
            $users = [];
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            // Stampa la query e il risultato su console (solo per debug)
            error_log('QUERY: SELECT id, username, color, is_active FROM users');
            error_log('RISULTATO: ' . print_r($users, true));
            echo json_encode(['success' => true, 'users' => $users]);
            $stmt->close();
        }
        break;
    case 'PUT':
        // Salvataggio batch di tutti gli utenti
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['users']) || !is_array($data['users'])) {
            echo json_encode(['success' => false, 'error' => 'Dati utenti non validi']);
            break;
        }
        
        $conn->autocommit(false); // Inizia transazione
        $success = true;
        $errors = [];
        
        try {
            // Carica le password esistenti per preservarle se vuote
            $existingPassStmt = $conn->prepare('SELECT username, password_hash FROM users');
            $existingPassStmt->execute();
            $result = $existingPassStmt->get_result();
            $existingPasswords = [];
            while ($row = $result->fetch_assoc()) {
                $existingPasswords[$row['username']] = $row['password_hash'];
            }
            $existingPassStmt->close();
            
            // Elimina tutti gli utenti esistenti
            $conn->query('DELETE FROM users');
            
            // Inserisce tutti i nuovi utenti
            $stmt = $conn->prepare('INSERT INTO users (username, password_hash, color, is_active) VALUES (?, ?, ?, ?)');
            
            foreach ($data['users'] as $user) {
                $username = $user['username'] ?? '';
                $password = $user['password'] ?? '';
                $color = $user['color'] ?? '#3498db';
                $is_active = isset($user['is_active']) ? (int)$user['is_active'] : 1;
                
                if (empty($username)) {
                    continue; // Salta utenti senza username
                }
                
                // Se la password è vuota, usa quella esistente (se presente)
                if (empty($password) && isset($existingPasswords[$username])) {
                    $password_hash = $existingPasswords[$username];
                } elseif (!empty($password)) {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                } else {
                    // Nuovo utente senza password - salta
                    $errors[] = "Utente {$username}: Password obbligatoria per nuovi utenti";
                    $success = false;
                    continue;
                }
                
                $stmt->bind_param('sssi', $username, $password_hash, $color, $is_active);
                if (!$stmt->execute()) {
                    $errors[] = "Errore inserimento utente {$username}: " . $stmt->error;
                    $success = false;
                }
            }
            
            $stmt->close();
            
            if ($success) {
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Utenti salvati con successo']);
            } else {
                $conn->rollback();
                echo json_encode(['success' => false, 'error' => 'Errori durante il salvataggio', 'details' => $errors]);
            }
            
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['success' => false, 'error' => 'Errore transazione: ' . $e->getMessage()]);
        }
        
        $conn->autocommit(true); // Ripristina autocommit
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Metodo non supportato']);
}
