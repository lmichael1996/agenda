<?php
/**
 * API per la gestione dei clienti
 * Supporta operazioni CRUD per la tabella clients
 */

// Headers di sicurezza e CORS
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gestione preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Carica configurazione
require_once '../../config/config.php';
require_once '../../config/db.php';

// Funzione per ottenere connessione PDO
function getDBConnection() {
    // Definisci direttamente i parametri di connessione
    $host = 'localhost';
    $user = 'admin';
    $password = 'admin123';
    $dbname = 'agenda_db';
    try {
        error_log("Tentativo connessione DB: host=$host, user=$user, db=$dbname");
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        error_log("Connessione DB riuscita");
        return $pdo;
    } catch (PDOException $e) {
        error_log("Errore connessione PDO: " . $e->getMessage());
        throw $e;
    }
}

// Verifica autenticazione per operazioni di modifica
function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Non autorizzato']);
        exit;
    }
}

// Funzione per validare i dati del cliente (controlli essenziali)
function validateClientData($data) {
    $errors = [];
    
    // Validazione nome (obbligatorio)
    if (empty(trim($data['first_name'] ?? ''))) {
        $errors[] = 'Il nome è obbligatorio';
    }
    
    // Validazione cognome (obbligatorio)
    if (empty(trim($data['last_name'] ?? ''))) {
        $errors[] = 'Il cognome è obbligatorio';
    }
    
    // Validazione telefono (formato base)
    if (!empty($data['phone'])) {
        $phone = preg_replace('/[^\d+]/', '', $data['phone']);
        if (strlen($phone) < 8 || strlen($phone) > 15) {
            $errors[] = 'Il numero di telefono deve essere tra 8 e 15 cifre';
        }
    }
    
    return $errors;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    error_log("Clients API: Metodo $method, Session ID: " . (session_id() ?: 'none'));
    $pdo = getDBConnection();
    
    switch ($method) {
        case 'GET':
            // Check if requesting a specific client by ID
            if (isset($_GET['id']) && !empty($_GET['id'])) {
                // Get single client details
                $clientId = (int)$_GET['id'];
                $stmt = $pdo->prepare('SELECT id, first_name, last_name, phone, notes, has_certificate FROM clients WHERE id = ?');
                $stmt->execute([$clientId]);
                
                if ($client = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo json_encode(['success' => true, 'data' => $client]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Cliente non trovato']);
                }
                break;
            }
            
            // Verifica esistenza tabella clients
            try {
                $stmt = $pdo->query("SHOW TABLES LIKE 'clients'");
                if ($stmt->rowCount() == 0) {
                    error_log("Tabella 'clients' non esiste");
                    throw new Exception("Tabella 'clients' non trovata");
                }
            } catch (PDOException $e) {
                error_log("Errore verifica tabella: " . $e->getMessage());
                throw $e;
            }
            
            // Parametri di paginazione e ricerca
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $limit = isset($_GET['limit']) ? max(1, min(100, intval($_GET['limit']))) : 50;
            $offset = ($page - 1) * $limit;
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            $searchField = isset($_GET['search_field']) ? trim($_GET['search_field']) : 'all';
            $sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'last_name_asc';
            
            // Costruzione query con ricerca
            $searchCondition = '';
            $params = [];
            
            if (!empty($search)) {
                switch ($searchField) {
                    case 'name':
                        $searchCondition = "WHERE (first_name LIKE :search OR last_name LIKE :search OR CONCAT(first_name, ' ', last_name) LIKE :search)";
                        break;
                    case 'first_name':
                        $searchCondition = "WHERE first_name LIKE :search";
                        break;
                    case 'last_name':
                        $searchCondition = "WHERE last_name LIKE :search";
                        break;
                    case 'phone':
                        $searchCondition = "WHERE phone LIKE :search";
                        break;
                    case 'notes':
                        $searchCondition = "WHERE notes LIKE :search";
                        break;
                    case 'all':
                    default:
                        $searchCondition = "WHERE first_name LIKE :search OR last_name LIKE :search OR phone LIKE :search OR notes LIKE :search";
                        break;
                }
                $params['search'] = "%$search%";
            }
            
            // Costruzione ORDER BY
            $orderBy = 'ORDER BY first_name, last_name'; // default
            switch ($sort) {
                case 'first_name_desc':
                    $orderBy = 'ORDER BY first_name DESC, last_name DESC';
                    break;
                case 'last_name_asc':
                    $orderBy = 'ORDER BY last_name ASC, first_name ASC';
                    break;
                case 'last_name_desc':
                    $orderBy = 'ORDER BY last_name DESC, first_name DESC';
                    break;
                default:
                    $orderBy = 'ORDER BY last_name ASC, first_name ASC';
            }
            
            // Query per recuperare clienti
            $sql = "SELECT id, first_name, last_name, phone, notes, has_certificate 
                    FROM clients 
                    $searchCondition 
                    $orderBy 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Conta totale per paginazione
            $countSql = "SELECT COUNT(*) as total FROM clients $searchCondition";
            $countStmt = $pdo->prepare($countSql);
            foreach ($params as $key => $value) {
                $countStmt->bindValue(":$key", $value);
            }
            $countStmt->execute();
            $total = $countStmt->fetch()['total'];
            
            // Calcola informazioni paginazione
            $totalPages = ceil($total / $limit);
            $start = $offset + 1;
            $end = min($offset + $limit, $total);
            
            echo json_encode([
                'success' => true,
                'data' => $clients,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'totalPages' => $totalPages,
                    'start' => $start,
                    'end' => $end
                ]
            ]);
            break;
            
        case 'POST':
            requireAuth();
            
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                throw new Exception('Dati JSON non validi');
            }
            
            // Prepara i dati per l'inserimento
            $data = [
                'first_name' => trim($input['first_name'] ?? ''),
                'last_name' => trim($input['last_name'] ?? ''),
                'phone' => trim($input['phone'] ?? ''),
                'notes' => trim($input['notes'] ?? ''),
                'has_certificate' => isset($input['has_certificate']) ? (int)$input['has_certificate'] : 0
            ];
            
            // Validazione dati
            $errors = validateClientData($data);
            if (!empty($errors)) {
                http_response_code(400);
                echo json_encode(['error' => 'Dati non validi', 'details' => $errors]);
                break;
            }
            
            // Inserisci nuovo cliente
            $stmt = $pdo->prepare("
                INSERT INTO clients (first_name, last_name, phone, notes, has_certificate)
                VALUES (:first_name, :last_name, :phone, :notes, :has_certificate)
            ");
            $stmt->execute($data);
            $clientId = $pdo->lastInsertId();
            
            // Recupera il cliente inserito
            $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = :id");
            $stmt->execute(['id' => $clientId]);
            $client = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $client, 'message' => 'Cliente creato con successo']);
            break;
            
        case 'PUT':
            requireAuth();
            
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['id'])) {
                throw new Exception('ID cliente mancante');
            }
            
            $clientId = intval($input['id']);
            
            // Verifica esistenza cliente
            $stmt = $pdo->prepare("SELECT id FROM clients WHERE id = :id");
            $stmt->execute(['id' => $clientId]);
            if (!$stmt->fetch()) {
                http_response_code(404);
                echo json_encode(['error' => 'Cliente non trovato']);
                break;
            }
            
            // Prepara i dati per l'aggiornamento
            $data = [
                'id' => $clientId,
                'first_name' => trim($input['first_name'] ?? ''),
                'last_name' => trim($input['last_name'] ?? ''),
                'phone' => trim($input['phone'] ?? ''),
                'notes' => trim($input['notes'] ?? ''),
                'has_certificate' => isset($input['has_certificate']) ? (int)$input['has_certificate'] : 0
            ];
            
            // Validazione dati
            $errors = validateClientData($data);
            if (!empty($errors)) {
                http_response_code(400);
                echo json_encode(['error' => 'Dati non validi', 'details' => $errors]);
                break;
            }
            
            // Aggiorna cliente
            $stmt = $pdo->prepare("
                UPDATE clients SET 
                    first_name = :first_name,
                    last_name = :last_name,
                    phone = :phone,
                    notes = :notes,
                    has_certificate = :has_certificate
                WHERE id = :id
            ");
            $stmt->execute($data);
            
            // Recupera il cliente aggiornato
            $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = :id");
            $stmt->execute(['id' => $clientId]);
            $client = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $client, 'message' => 'Cliente aggiornato con successo']);
            break;
            
        case 'DELETE':
            requireAuth();
            
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['id'])) {
                // Se non c'è JSON, prova con GET parameter
                $clientId = isset($_GET['id']) ? intval($_GET['id']) : null;
                if (!$clientId) {
                    throw new Exception('ID cliente mancante');
                }
            } else {
                $clientId = intval($input['id']);
            }
            
            // Verifica esistenza cliente
            $stmt = $pdo->prepare("SELECT id FROM clients WHERE id = :id");
            $stmt->execute(['id' => $clientId]);
            if (!$stmt->fetch()) {
                http_response_code(404);
                echo json_encode(['error' => 'Cliente non trovato']);
                break;
            }
            
            // Elimina cliente
            $stmt = $pdo->prepare("DELETE FROM clients WHERE id = :id");
            $stmt->execute(['id' => $clientId]);
            
            echo json_encode(['success' => true, 'message' => 'Cliente eliminato con successo']);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Metodo non supportato']);
            break;
    }
    
} catch (PDOException $e) {
    error_log("Errore database clients API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Errore del database', 'details' => $e->getMessage()]);
} catch (Exception $e) {
    error_log("Errore clients API: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
