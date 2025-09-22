<?php
require_once __DIR__ . '/../repositories/ScheduleRepository.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $scheduleRepo = new ScheduleRepository();
    $method = $_SERVER['REQUEST_METHOD'];
    $pathInfo = $_SERVER['PATH_INFO'] ?? '';
    $segments = explode('/', trim($pathInfo, '/'));
    $action = isset($segments[0]) ? $segments[0] : null;
    
    switch($method) {
        case 'GET':
            if ($action === 'reset') {
                // GET /api/schedule.php/reset - Reset ai valori di default
                $result = $scheduleRepo->resetToDefault();
                echo json_encode([
                    'success' => true,
                    'message' => 'Configurazione reset ai valori di default',
                    'data' => $result
                ]);
            } else if ($action === 'validate') {
                // GET /api/schedule.php/validate - Valida configurazione corrente
                $config = $scheduleRepo->getConfiguration();
                $isValid = $scheduleRepo->validateConfiguration($config);
                echo json_encode([
                    'success' => true,
                    'is_valid' => $isValid,
                    'data' => $config
                ]);
            } else {
                // GET /api/schedule.php - Ottieni configurazione
                $config = $scheduleRepo->getConfiguration();
                echo json_encode([
                    'success' => true,
                    'data' => $config
                ]);
            }
            break;
            
        case 'POST':
            // POST /api/schedule.php - Crea nuova configurazione (reset se esiste già)
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Dati non validi');
            }
            
            // Validazione configurazione
            if (!$scheduleRepo->validateConfiguration($input)) {
                throw new Exception('Configurazione non valida');
            }
            
            $result = $scheduleRepo->updateConfiguration($input);
            
            echo json_encode([
                'success' => true,
                'message' => 'Configurazione creata/aggiornata con successo',
                'data' => $result
            ]);
            break;
            
        case 'PUT':
            // PUT /api/schedule.php - Aggiorna configurazione esistente
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Dati non validi');
            }
            
            // Validazione configurazione
            if (!$scheduleRepo->validateConfiguration($input)) {
                throw new Exception('Configurazione non valida');
            }
            
            $result = $scheduleRepo->updateConfiguration($input);
            
            echo json_encode([
                'success' => true,
                'message' => 'Configurazione aggiornata con successo',
                'data' => $result
            ]);
            break;
            
        case 'DELETE':
            // DELETE /api/schedule.php - Reset configurazione ai valori di default
            $result = $scheduleRepo->resetToDefault();
            
            echo json_encode([
                'success' => true,
                'message' => 'Configurazione resettata ai valori di default',
                'data' => $result
            ]);
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