<?php
require_once __DIR__ . '/../repositories/ServiceRepository.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $serviceRepo = new ServiceRepository();
    $method = $_SERVER['REQUEST_METHOD'];
    $pathInfo = $_SERVER['PATH_INFO'] ?? '';
    $segments = explode('/', trim($pathInfo, '/'));
    $serviceId = isset($segments[0]) && is_numeric($segments[0]) ? (int)$segments[0] : null;
    $action = isset($segments[1]) ? $segments[1] : null;
    
    switch($method) {
        case 'GET':
            if ($serviceId && $action === 'stats') {
                // GET /api/services.php/stats - Statistiche servizi
                $stats = $serviceRepo->getStats();
                echo json_encode([
                    'success' => true,
                    'data' => $stats
                ]);
            } else if ($serviceId) {
                // GET /api/services.php/123 - Singolo servizio
                $service = $serviceRepo->findById($serviceId);
                if ($service) {
                    echo json_encode([
                        'success' => true,
                        'data' => $service
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Servizio non trovato'
                    ]);
                }
            } else {
                // GET /api/services.php - Lista servizi con filtri
                $activeOnly = isset($_GET['active']) && $_GET['active'] === 'true';
                $search = $_GET['search'] ?? null;
                $minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
                $maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;
                $minDuration = isset($_GET['min_duration']) ? (int)$_GET['min_duration'] : null;
                $maxDuration = isset($_GET['max_duration']) ? (int)$_GET['max_duration'] : null;
                
                if ($search) {
                    $services = $serviceRepo->searchByName($search);
                } else if ($minPrice !== null || $maxPrice !== null) {
                    $services = $serviceRepo->findByPriceRange($minPrice, $maxPrice);
                } else if ($minDuration !== null || $maxDuration !== null) {
                    $services = $serviceRepo->findByDuration($minDuration, $maxDuration);
                } else if ($activeOnly) {
                    $services = $serviceRepo->findActiveServices();
                } else {
                    $services = $serviceRepo->findAll();
                }
                
                echo json_encode([
                    'success' => true,
                    'data' => $services,
                    'count' => count($services)
                ]);
            }
            break;
            
        case 'POST':
            // POST /api/services.php - Crea nuovo servizio
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Dati non validi');
            }
            
            // Validazione
            if (empty($input['name_service'])) {
                throw new Exception('Nome servizio obbligatorio');
            }
            
            $newServiceId = $serviceRepo->create($input);
            
            echo json_encode([
                'success' => true,
                'message' => 'Servizio creato con successo',
                'data' => ['id' => $newServiceId]
            ]);
            break;
            
        case 'PUT':
            // PUT /api/services.php/123 - Aggiorna servizio
            if (!$serviceId) {
                throw new Exception('ID servizio richiesto per l\'aggiornamento');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Dati non validi');
            }
            
            // Verifica che il servizio esista
            $existingService = $serviceRepo->findById($serviceId);
            if (!$existingService) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Servizio non trovato'
                ]);
                break;
            }
            
            $updatedRows = $serviceRepo->updateService($serviceId, $input);
            
            echo json_encode([
                'success' => true,
                'message' => 'Servizio aggiornato con successo',
                'updated_rows' => $updatedRows
            ]);
            break;
            
        case 'DELETE':
            // DELETE /api/services.php/123 - Elimina servizio
            if (!$serviceId) {
                throw new Exception('ID servizio richiesto per l\'eliminazione');
            }
            
            $deletedRows = $serviceRepo->deleteById($serviceId);
            
            if ($deletedRows > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Servizio eliminato con successo'
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Servizio non trovato'
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