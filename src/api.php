<?php
/**
 * Router API centralizzato
 * Sostituisce la cartella api/ con un sistema unificato
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

// Avvia sessione
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Carica configurazione e database
require_once __DIR__ . '/../core/gateway/access-control.php';
require_once __DIR__ . '/../core/database/connection.php';  // Contiene getDBConnection()

// Carica i controller
require_once __DIR__ . '/Controllers/ClientsController.php';
require_once __DIR__ . '/Controllers/ServicesController.php';
require_once __DIR__ . '/Controllers/ScheduleController.php';
require_once __DIR__ . '/Controllers/NotesController.php';
require_once __DIR__ . '/Controllers/UsersController.php';

// Ottieni connessione database
$db = getDBConnection();

// Inizializza i controller
$clientsController = new ClientsController($db);
$servicesController = new ServicesController($db);
$scheduleController = new ScheduleController($db);
$notesController = new NotesController($db);
$usersController = new UsersController($db);

// Parse della richiesta
$requestUri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Rimuovi query string
$path = parse_url($requestUri, PHP_URL_PATH);

// Determina il controller e l'azione
$pathParts = array_filter(explode('/', $path));
$pathParts = array_values($pathParts); // Reindicizza

// Formato: /api.php/resource/action/id
if (count($pathParts) < 2) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Richiesta non valida']);
    exit;
}

$resource = $pathParts[1] ?? ''; // clients, services, schedule, notes, users
$action = $pathParts[2] ?? '';   // Azione specifica (opzionale)
$id = $pathParts[3] ?? null;     // ID specifico (opzionale)

// Gestisci input JSON per POST/PUT
$input = null;
if (in_array($method, ['POST', 'PUT'])) {
    $input = json_decode(file_get_contents('php://input'), true);
}

// Routing per risorsa
try {
    switch ($resource) {
        case 'clients':
            handleClientsResource($clientsController, $method, $action, $id, $input);
            break;
            
        case 'services':
            handleServicesResource($servicesController, $method, $action, $id, $input);
            break;
            
        case 'schedule':
            handleScheduleResource($scheduleController, $method, $action, $id, $input);
            break;
            
        case 'notes':
            handleNotesResource($notesController, $method, $action, $id, $input);
            break;
            
        case 'users':
            handleUsersResource($usersController, $method, $action, $id, $input);
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Risorsa non trovata']);
    }
} catch (Exception $e) {
    error_log("Errore API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore interno del server']);
}

// ============= HANDLER PER OGNI RISORSA =============

function handleClientsResource($controller, $method, $action, $id, $input) {
    switch ($method) {
        case 'GET':
            if (!empty($id)) {
                echo json_encode($controller->getById($id));
            } elseif (!empty($_GET['search'])) {
                echo json_encode($controller->search($_GET['search']));
            } else {
                echo json_encode($controller->getAll());
            }
            break;
            
        case 'POST':
            echo json_encode($controller->create($input));
            break;
            
        case 'PUT':
            if (!empty($id)) {
                echo json_encode($controller->update($id, $input));
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID richiesto per aggiornamento']);
            }
            break;
            
        case 'DELETE':
            if (!empty($id)) {
                echo json_encode($controller->delete($id));
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID richiesto per eliminazione']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Metodo non supportato']);
    }
}

function handleServicesResource($controller, $method, $action, $id, $input) {
    switch ($method) {
        case 'GET':
            echo json_encode($controller->getAll());
            break;
            
        case 'POST':
            echo json_encode($controller->create($input));
            break;
            
        case 'PUT':
            // Supporta sia aggiornamento singolo che batch
            if (!empty($id)) {
                echo json_encode($controller->update($id, $input));
            } elseif (isset($input['services'])) {
                // Salvataggio batch
                echo json_encode($controller->saveAll($input['services']));
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID o array services richiesto']);
            }
            break;
            
        case 'DELETE':
            if (!empty($id)) {
                echo json_encode($controller->delete($id));
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID richiesto per eliminazione']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Metodo non supportato']);
    }
}

function handleScheduleResource($controller, $method, $action, $id, $input) {
    switch ($method) {
        case 'GET':
            if (!empty($action)) {
                // Get per giorno specifico: /schedule/lunedi
                echo json_encode($controller->getByDay($action));
            } else {
                echo json_encode($controller->getAll());
            }
            break;
            
        case 'PUT':
            if (!empty($action)) {
                // Update per giorno specifico: /schedule/lunedi
                echo json_encode($controller->updateDay($action, $input));
            } elseif (isset($input['schedules'])) {
                // Salvataggio batch
                echo json_encode($controller->saveAll($input['schedules']));
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Giorno o array schedules richiesto']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Metodo non supportato']);
    }
}

function handleNotesResource($controller, $method, $action, $id, $input) {
    switch ($method) {
        case 'GET':
            if (!empty($id)) {
                echo json_encode($controller->getById($id));
            } else {
                // Filtri opzionali da query string
                $filters = [
                    'user_id' => $_GET['user_id'] ?? null,
                    'for_all' => isset($_GET['for_all']) ? (bool)$_GET['for_all'] : null,
                    'date_from' => $_GET['date_from'] ?? null,
                    'date_to' => $_GET['date_to'] ?? null
                ];
                // Rimuovi filtri null
                $filters = array_filter($filters, function($v) { return $v !== null; });
                echo json_encode($controller->getAll($filters));
            }
            break;
            
        case 'POST':
            echo json_encode($controller->create($input));
            break;
            
        case 'PUT':
            if (!empty($id)) {
                echo json_encode($controller->update($id, $input));
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID richiesto per aggiornamento']);
            }
            break;
            
        case 'DELETE':
            if (!empty($id)) {
                echo json_encode($controller->delete($id));
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID richiesto per eliminazione']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Metodo non supportato']);
    }
}

function handleUsersResource($controller, $method, $action, $id, $input) {
    switch ($method) {
        case 'GET':
            if (!empty($id)) {
                echo json_encode($controller->getById($id));
            } else {
                echo json_encode($controller->getAll());
            }
            break;
            
        case 'POST':
            if ($action === 'authenticate') {
                // Autenticazione
                $username = $input['username'] ?? '';
                $password = $input['password'] ?? '';
                echo json_encode($controller->authenticate($username, $password));
            } else {
                echo json_encode($controller->create($input));
            }
            break;
            
        case 'PUT':
            if (!empty($id)) {
                echo json_encode($controller->update($id, $input));
            } elseif (isset($input['users'])) {
                // Salvataggio batch
                echo json_encode($controller->saveAll($input['users']));
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID o array users richiesto']);
            }
            break;
            
        case 'DELETE':
            if (!empty($id)) {
                echo json_encode($controller->delete($id));
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID richiesto per eliminazione']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Metodo non supportato']);
    }
}
