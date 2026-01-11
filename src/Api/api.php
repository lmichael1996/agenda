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
require_once __DIR__ . '/../Auth/AccessControl.php';
require_once __DIR__ . '/../Database/Connection.php';  // Contiene getDBConnection()

// Carica i controller
require_once __DIR__ . '/../Controllers/ClientsController.php';
require_once __DIR__ . '/../Controllers/ServicesController.php';
require_once __DIR__ . '/../Controllers/AppointmentController.php';
require_once __DIR__ . '/../Controllers/NotesController.php';
require_once __DIR__ . '/../Controllers/UsersController.php';
require_once __DIR__ . '/../Controllers/ProductsController.php';

// Ottieni connessione database
$db = getDBConnection();

// Inizializza i controller
$clientsController = new ClientsController($db);
$servicesController = new ServicesController($db);
$appointmentController = new AppointmentController($db);
$notesController = new NotesController($db);
$usersController = new UsersController($db);
$productsController = new ProductsController($db);

// Parse della richiesta - Solo query string
$method = $_SERVER['REQUEST_METHOD'];
$resource = $_GET['endpoint'] ?? '';
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

// Valida che ci sia un endpoint
if (empty($resource)) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'error' => 'Parametro endpoint richiesto',
        'usage' => 'api.php?endpoint=services',
        'available' => ['clients', 'services', 'schedule', 'notes', 'users']
    ]);
    exit;
}

// Gestisci input JSON per POST/PUT
$input = null;
if (in_array($method, ['POST', 'PUT'])) {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
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
            handleScheduleResource($appointmentController, $method, $action, $id, $input);
            break;
            
        case 'settings':
            handleSettingsResource($db, $method, $input);
            break;
            
        case 'notes':
            handleNotesResource($notesController, $method, $action, $id, $input);
            break;
            
        case 'users':
            handleUsersResource($usersController, $method, $action, $id, $input);
            break;
            
        case 'products':
            handleProductsResource($productsController, $method, $action, $id, $input);
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Risorsa non trovata']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Errore interno del server',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}

// ============= HANDLER PER OGNI RISORSA =============

function handleClientsResource($controller, $method, $action, $id, $input) {
    switch ($method) {
        case 'GET':
            if (!empty($id)) {
                echo json_encode($controller->getById($id));
            } elseif (!empty($_GET['search'])) {
                // Ricerca con paginazione
                $page = intval($_GET['page'] ?? 1);
                $limit = intval($_GET['limit'] ?? 50);
                $searchField = $_GET['search_field'] ?? 'all';
                $searchType = $_GET['search_type'] ?? 'contains';
                $sort = $_GET['sort'] ?? 'last_name_asc';
                
                $result = $controller->getAll();
                
                if ($result['success'] && isset($result['data'])) {
                    $clients = $result['data'];
                    $searchQuery = $_GET['search'];
                    
                    // Filtra clienti in base alla ricerca
                    $filtered = array_filter($clients, function($client) use ($searchQuery, $searchField, $searchType) {
                        $query = strtolower(trim($searchQuery));
                        
                        // Determina il campo su cui cercare
                        $searchIn = [];
                        if ($searchField === 'all' || $searchField === 'name') {
                            $searchIn[] = strtolower($client['first_name'] . ' ' . $client['last_name']);
                        }
                        if ($searchField === 'first_name' || $searchField === 'all') {
                            $searchIn[] = strtolower($client['first_name']);
                        }
                        if ($searchField === 'last_name' || $searchField === 'all') {
                            $searchIn[] = strtolower($client['last_name']);
                        }
                        if ($searchField === 'phone' || $searchField === 'all') {
                            $searchIn[] = strtolower($client['phone'] ?? '');
                        }
                        if ($searchField === 'notes' || $searchField === 'all') {
                            $searchIn[] = strtolower($client['notes'] ?? '');
                        }
                        
                        // Applica il tipo di ricerca
                        foreach ($searchIn as $text) {
                            $match = false;
                            switch ($searchType) {
                                case 'starts':
                                    $match = strpos($text, $query) === 0;
                                    break;
                                case 'ends':
                                    $match = substr($text, -strlen($query)) === $query;
                                    break;
                                case 'exact':
                                    $match = $text === $query;
                                    break;
                                case 'contains':
                                default:
                                    $match = strpos($text, $query) !== false;
                                    break;
                            }
                            if ($match) return true;
                        }
                        return false;
                    });
                    
                    // Ordina i risultati
                    usort($filtered, function($a, $b) use ($sort) {
                        switch ($sort) {
                            case 'first_name_asc':
                                return strcasecmp($a['first_name'], $b['first_name']);
                            case 'first_name_desc':
                                return strcasecmp($b['first_name'], $a['first_name']);
                            case 'last_name_desc':
                                return strcasecmp($b['last_name'], $a['last_name']);
                            case 'last_name_asc':
                            default:
                                return strcasecmp($a['last_name'], $b['last_name']);
                        }
                    });
                    
                    // Paginazione
                    $total = count($filtered);
                    $totalPages = ceil($total / $limit);
                    $totalPages = max(1, $totalPages); // Almeno 1 pagina
                    $offset = ($page - 1) * $limit;
                    $paginatedClients = array_slice($filtered, $offset, $limit);
                    
                    // Calcola start e end per visualizzazione
                    $start = $total > 0 ? $offset + 1 : 0;
                    $end = min($offset + $limit, $total);
                    
                    echo json_encode([
                        'success' => true,
                        'data' => array_values($paginatedClients),
                        'pagination' => [
                            'page' => $page,
                            'limit' => $limit,
                            'total' => $total,
                            'totalPages' => $totalPages,
                            'start' => $start,
                            'end' => $end
                        ]
                    ]);
                } else {
                    echo json_encode($result);
                }
            } else {
                // Lista completa con paginazione
                $page = intval($_GET['page'] ?? 1);
                $limit = intval($_GET['limit'] ?? 50);
                $sort = $_GET['sort'] ?? 'last_name_asc';
                
                $result = $controller->getAll();
                
                if ($result['success'] && isset($result['data'])) {
                    $clients = $result['data'];
                    
                    // Ordina
                    usort($clients, function($a, $b) use ($sort) {
                        switch ($sort) {
                            case 'first_name_asc':
                                return strcasecmp($a['first_name'], $b['first_name']);
                            case 'first_name_desc':
                                return strcasecmp($b['first_name'], $a['first_name']);
                            case 'last_name_desc':
                                return strcasecmp($b['last_name'], $a['last_name']);
                            case 'last_name_asc':
                            default:
                                return strcasecmp($a['last_name'], $b['last_name']);
                        }
                    });
                    
                    // Paginazione
                    $total = count($clients);
                    $totalPages = ceil($total / $limit);
                    $totalPages = max(1, $totalPages); // Almeno 1 pagina
                    $offset = ($page - 1) * $limit;
                    $paginatedClients = array_slice($clients, $offset, $limit);
                    
                    // Calcola start e end per visualizzazione
                    $start = $total > 0 ? $offset + 1 : 0;
                    $end = min($offset + $limit, $total);
                    
                    echo json_encode([
                        'success' => true,
                        'data' => array_values($paginatedClients),
                        'pagination' => [
                            'page' => $page,
                            'limit' => $limit,
                            'total' => $total,
                            'totalPages' => $totalPages,
                            'start' => $start,
                            'end' => $end
                        ]
                    ]);
                } else {
                    echo json_encode($result);
                }
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
            // Check for id parameter (super_appointment_id)
            if (!empty($id)) {
                echo json_encode($controller->getById($id));
            } elseif (!empty($_GET['client_id'])) {
                // Check for client_id parameter
                echo json_encode($controller->getByClient($_GET['client_id']));
            } elseif (!empty($action)) {
                // Get per giorno specifico: /schedule/lunedi
                echo json_encode($controller->getByDay($action));
            } else {
                echo json_encode($controller->getAll());
            }
            break;
            
        case 'POST':
            // Crea nuovo appuntamento
            $result = $controller->create($input);
            http_response_code($result['success'] ? 200 : 400);
            echo json_encode($result);
            flush();
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

function handleSettingsResource($db, $method, $input) {
    switch ($method) {
        case 'GET':
            try {
                $stmt = $db->prepare('SELECT * FROM settings WHERE id = 1');
                $stmt->execute();
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($data) {
                    echo json_encode(['success' => true, 'data' => $data]);
                } else {
                    // Restituisci valori di default se non esiste il record
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'opening_time' => '09:00:00',
                            'closing_time' => '18:00:00',
                            'lunch_break_enabled' => 0,
                            'break_start' => '12:30:00',
                            'break_end' => '13:30:00',
                            'timezone' => 'Europe/Rome',
                            'closed_monday' => 0,
                            'closed_tuesday' => 0,
                            'closed_wednesday' => 0,
                            'closed_thursday' => 0,
                            'closed_friday' => 0,
                            'closed_saturday' => 1,
                            'closed_sunday' => 1
                        ]
                    ]);
                }
            } catch (PDOException $e) {
                error_log("Settings GET error: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Errore nel recupero delle impostazioni']);
            }
            break;
            
        case 'PUT':
            try {
                // Aggiorna o inserisci il record con id=1
                $stmt = $db->prepare('INSERT INTO settings (id, opening_time, closing_time, lunch_break_enabled, break_start, break_end, 
                                      closed_monday, closed_tuesday, closed_wednesday, closed_thursday, closed_friday, closed_saturday, closed_sunday) 
                                      VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
                                      ON DUPLICATE KEY UPDATE 
                                      opening_time = VALUES(opening_time),
                                      closing_time = VALUES(closing_time),
                                      lunch_break_enabled = VALUES(lunch_break_enabled),
                                      break_start = VALUES(break_start),
                                      break_end = VALUES(break_end),
                                      closed_monday = VALUES(closed_monday),
                                      closed_tuesday = VALUES(closed_tuesday),
                                      closed_wednesday = VALUES(closed_wednesday),
                                      closed_thursday = VALUES(closed_thursday),
                                      closed_friday = VALUES(closed_friday),
                                      closed_saturday = VALUES(closed_saturday),
                                      closed_sunday = VALUES(closed_sunday)');
                
                $stmt->execute([
                    $input['opening_time'] ?? '09:00:00',
                    $input['closing_time'] ?? '18:00:00',
                    $input['lunch_break_enabled'] ?? 0,
                    $input['break_start'] ?? '12:30:00',
                    $input['break_end'] ?? '13:30:00',
                    $input['closed_monday'] ?? 0,
                    $input['closed_tuesday'] ?? 0,
                    $input['closed_wednesday'] ?? 0,
                    $input['closed_thursday'] ?? 0,
                    $input['closed_friday'] ?? 0,
                    $input['closed_saturday'] ?? 1,
                    $input['closed_sunday'] ?? 1
                ]);
                
                echo json_encode(['success' => true, 'message' => 'Impostazioni salvate con successo']);
            } catch (PDOException $e) {
                error_log("Settings PUT error: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Errore nel salvataggio delle impostazioni']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Metodo non supportato']);
    }
}

/**
 * Handler per la risorsa products
 */
function handleProductsResource($controller, $method, $action, $id, $input) {
    switch ($method) {
        case 'GET':
            if (!empty($id)) {
                echo json_encode($controller->getById($id));
            } else {
                echo json_encode($controller->getAll());
            }
            break;
            
        case 'POST':
            // Supporta sia creazione singola che batch
            if (isset($input['products'])) {
                // Salvataggio batch
                echo json_encode($controller->saveAll($input['products']));
            } else {
                // Creazione singola
                echo json_encode($controller->create($input));
            }
            break;
            
        case 'PUT':
            // Supporta sia aggiornamento singolo che batch
            if (!empty($id)) {
                echo json_encode($controller->update($id, $input));
            } elseif (isset($input['products'])) {
                // Salvataggio batch
                echo json_encode($controller->saveAll($input['products']));
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID o array products richiesto']);
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
