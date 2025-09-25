<?php
// API per gestione servizi
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
        // Lista servizi con prepared statement
        $stmt = $conn->prepare('SELECT id, name_service as name, price, duration_minutes, description_service as description FROM services ORDER BY id');
        $stmt->execute();
        $result = $stmt->get_result();
        $services = [];
        while ($row = $result->fetch_assoc()) {
            $row['durationMinutes'] = (int)$row['duration_minutes']; // Converti per compatibilità frontend
            unset($row['duration_minutes']);
            $services[] = $row;
        }
        echo json_encode(['success' => true, 'services' => $services]);
        $stmt->close();
        break;
        
    case 'PUT':
        // Salvataggio batch di tutti i servizi
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['services']) || !is_array($data['services'])) {
            echo json_encode(['success' => false, 'error' => 'Dati servizi non validi']);
            break;
        }
        
        $conn->autocommit(false); // Inizia transazione
        $success = true;
        $errors = [];
        
        try {
            // Elimina tutti i servizi esistenti
            $conn->query('DELETE FROM services');
            
            // Inserisce tutti i nuovi servizi
            $stmt = $conn->prepare('INSERT INTO services (name_service, price, duration_minutes, description_service) VALUES (?, ?, ?, ?)');
            
            foreach ($data['services'] as $service) {
                $name = $service['name'] ?? '';
                $price = floatval($service['price'] ?? 0);
                $duration = intval($service['durationMinutes'] ?? 30);
                $description = $service['description'] ?? '';
                
                if (empty($name)) {
                    continue; // Salta servizi senza nome
                }
                
                // Validazione
                if ($price < 0 || $price > 9999.99) {
                    $errors[] = "Prezzo non valido per servizio {$name}";
                    $success = false;
                    continue;
                }
                
                if ($duration < 15 || $duration > 480 || $duration % 15 !== 0) {
                    $errors[] = "Durata non valida per servizio {$name}";
                    $success = false;
                    continue;
                }
                
                $stmt->bind_param('sdis', $name, $price, $duration, $description);
                if (!$stmt->execute()) {
                    $errors[] = "Errore inserimento servizio {$name}: " . $stmt->error;
                    $success = false;
                }
            }
            
            $stmt->close();
            
            if ($success) {
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Servizi salvati con successo']);
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