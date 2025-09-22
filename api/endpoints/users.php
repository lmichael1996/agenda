<?php
require_once __DIR__ . '/../repositories/UserRepository.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $userRepo = new UserRepository();
    $method = $_SERVER['REQUEST_METHOD'];
    $pathInfo = $_SERVER['PATH_INFO'] ?? '';
    $segments = explode('/', trim($pathInfo, '/'));
    $userId = isset($segments[0]) && is_numeric($segments[0]) ? (int)$segments[0] : null;
    
    switch($method) {
        case 'GET':
            if ($userId) {
                // GET /api/users.php/123 - Singolo utente
                $user = $userRepo->findById($userId);
                if ($user) {
                    // Rimuovi password_hash dalla risposta per sicurezza
                    unset($user['password_hash']);
                    echo json_encode([
                        'success' => true,
                        'data' => $user
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Utente non trovato'
                    ]);
                }
            } else {
                // GET /api/users.php - Tutti gli utenti
                $activeOnly = isset($_GET['active']) && $_GET['active'] === 'true';
                
                if ($activeOnly) {
                    $users = $userRepo->findActiveUsers();
                } else {
                    $users = $userRepo->findAll();
                }
                
                // Rimuovi password_hash da tutti gli utenti
                foreach ($users as &$user) {
                    unset($user['password_hash']);
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $users,
                    'count' => count($users)
                ]);
            }
            break;
            
        case 'POST':
            // POST /api/users.php - Crea nuovo utente
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Dati non validi');
            }
            
            // Validazione
            if (empty($input['username'])) {
                throw new Exception('Username obbligatorio');
            }
            
            if (empty($input['password'])) {
                throw new Exception('Password obbligatoria');
            }
            
            // Verifica che l'username non esista già
            if ($userRepo->usernameExists($input['username'])) {
                throw new Exception('Username già esistente');
            }
            
            // Hash della password
            $input['password_hash'] = password_hash($input['password'], PASSWORD_DEFAULT);
            unset($input['password']);
            
            $newUserId = $userRepo->create($input);
            
            echo json_encode([
                'success' => true,
                'message' => 'Utente creato con successo',
                'data' => ['id' => $newUserId]
            ]);
            break;
            
        case 'PUT':
            // PUT /api/users.php/123 - Aggiorna utente
            if (!$userId) {
                throw new Exception('ID utente richiesto per l\'aggiornamento');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Dati non validi');
            }
            
            // Verifica che l'utente esista
            $existingUser = $userRepo->findById($userId);
            if (!$existingUser) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Utente non trovato'
                ]);
                break;
            }
            
            // Se viene cambiato l'username, verifica che non esista già
            if (isset($input['username']) && $userRepo->usernameExists($input['username'], $userId)) {
                throw new Exception('Username già esistente');
            }
            
            // Se viene cambiata la password, fai l'hash
            if (isset($input['password'])) {
                $input['password_hash'] = password_hash($input['password'], PASSWORD_DEFAULT);
                unset($input['password']);
            }
            
            $updatedRows = $userRepo->updateUser($userId, $input);
            
            echo json_encode([
                'success' => true,
                'message' => 'Utente aggiornato con successo',
                'updated_rows' => $updatedRows
            ]);
            break;
            
        case 'DELETE':
            // DELETE /api/users.php/123 - Elimina utente
            if (!$userId) {
                throw new Exception('ID utente richiesto per l\'eliminazione');
            }
            
            $deletedRows = $userRepo->deleteById($userId);
            
            if ($deletedRows > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Utente eliminato con successo'
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Utente non trovato'
                ]);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Metodo non supportato'
            ]);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>