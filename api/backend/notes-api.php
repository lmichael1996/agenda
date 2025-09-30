<?php
// API per gestione note
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
        // Check if requesting a specific note by ID
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            // Get single note details
            $noteId = (int)$_GET['id'];
            $stmt = $conn->prepare('SELECT id, title, content, for_all, note_date, user_id FROM notes WHERE id = ?');
            $stmt->bind_param('i', $noteId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($note = $result->fetch_assoc()) {
                echo json_encode(['success' => true, 'note' => $note]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Nota non trovata']);
            }
            $stmt->close();
        } else {
            // Lista note con parametri opzionali
            $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
            $for_all = isset($_GET['for_all']) ? (bool)$_GET['for_all'] : null;
            $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : null;
            $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : null;
            
            // Costruisci query dinamica
            $query = 'SELECT id, title, content, for_all, note_date, user_id FROM notes WHERE 1=1';
            $params = [];
            $types = '';
            
            if ($user_id) {
                $query .= ' AND user_id = ?';
                $params[] = $user_id;
                $types .= 'i';
            }
            
            if ($for_all !== null) {
                $query .= ' AND for_all = ?';
                $params[] = $for_all ? 1 : 0;
                $types .= 'i';
            }
            
            if ($date_from) {
                $query .= ' AND note_date >= ?';
                $params[] = $date_from;
                $types .= 's';
            }
            
            if ($date_to) {
                $query .= ' AND note_date <= ?';
                $params[] = $date_to;
                $types .= 's';
            }
            
            $query .= ' ORDER BY note_date DESC, id DESC';
            
            $stmt = $conn->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $notes = [];
            while ($row = $result->fetch_assoc()) {
                $notes[] = $row;
            }
            
            // Debug log
            error_log('NOTES QUERY: ' . $query);
            error_log('NOTES RESULT: ' . print_r($notes, true));
            
            echo json_encode(['success' => true, 'notes' => $notes]);
            $stmt->close();
        }
        break;
        
    case 'POST':
        // Crea nuova nota
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validazione input
        if (!isset($input['user_id']) || empty($input['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'User ID obbligatorio']);
            break;
        }
        
        if (!isset($input['note_date']) || empty($input['note_date'])) {
            echo json_encode(['success' => false, 'error' => 'Data nota obbligatoria']);
            break;
        }
        
        $title = isset($input['title']) ? trim($input['title']) : '';
        $content = isset($input['content']) ? trim($input['content']) : '';
        $for_all = isset($input['for_all']) ? (bool)$input['for_all'] : false;
        $note_date = $input['note_date'];
        $user_id = (int)$input['user_id'];
        
        // Validazione data
        $date = DateTime::createFromFormat('Y-m-d', $note_date);
        if (!$date || $date->format('Y-m-d') !== $note_date) {
            echo json_encode(['success' => false, 'error' => 'Formato data non valido (YYYY-MM-DD)']);
            break;
        }
        
        try {
            $stmt = $conn->prepare('INSERT INTO notes (title, content, for_all, note_date, user_id) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('ssisi', $title, $content, $for_all, $note_date, $user_id);
            
            if ($stmt->execute()) {
                $noteId = $conn->insert_id;
                echo json_encode([
                    'success' => true, 
                    'message' => 'Nota creata con successo',
                    'note_id' => $noteId
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Errore durante l\'inserimento: ' . $stmt->error]);
            }
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Errore database: ' . $e->getMessage()]);
        }
        break;
        
    case 'PUT':
        // Aggiorna nota esistente
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['id']) || empty($input['id'])) {
            echo json_encode(['success' => false, 'error' => 'ID nota obbligatorio']);
            break;
        }
        
        $id = (int)$input['id'];
        $title = isset($input['title']) ? trim($input['title']) : '';
        $content = isset($input['content']) ? trim($input['content']) : '';
        $for_all = isset($input['for_all']) ? (bool)$input['for_all'] : false;
        $note_date = isset($input['note_date']) ? $input['note_date'] : null;
        
        // Validazione data se fornita
        if ($note_date) {
            $date = DateTime::createFromFormat('Y-m-d', $note_date);
            if (!$date || $date->format('Y-m-d') !== $note_date) {
                echo json_encode(['success' => false, 'error' => 'Formato data non valido (YYYY-MM-DD)']);
                break;
            }
        }
        
        try {
            if ($note_date) {
                $stmt = $conn->prepare('UPDATE notes SET title = ?, content = ?, for_all = ?, note_date = ? WHERE id = ?');
                $stmt->bind_param('ssisi', $title, $content, $for_all, $note_date, $id);
            } else {
                $stmt = $conn->prepare('UPDATE notes SET title = ?, content = ?, for_all = ? WHERE id = ?');
                $stmt->bind_param('ssii', $title, $content, $for_all, $id);
            }
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Nota aggiornata con successo']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Nota non trovata o nessuna modifica effettuata']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Errore durante l\'aggiornamento: ' . $stmt->error]);
            }
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Errore database: ' . $e->getMessage()]);
        }
        break;
        
    case 'DELETE':
        // Elimina nota
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            echo json_encode(['success' => false, 'error' => 'ID nota obbligatorio']);
            break;
        }
        
        $id = (int)$_GET['id'];
        
        try {
            $stmt = $conn->prepare('DELETE FROM notes WHERE id = ?');
            $stmt->bind_param('i', $id);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Nota eliminata con successo']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Nota non trovata']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Errore durante l\'eliminazione: ' . $stmt->error]);
            }
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Errore database: ' . $e->getMessage()]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Metodo non supportato']);
        break;
}

$conn->close();
?>