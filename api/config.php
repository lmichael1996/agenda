<?php
/**
 * Configurazione API Sistema Agenda
 * @author Michael Leanza
 */

// Informazioni API
define('API_VERSION', '1.0.0');
define('API_DEBUG', true);
define('API_BASE_URL', '/api/');

// Limiti repository
define('DEFAULT_LIMIT', 100);
define('MAX_LIMIT', 1000);

// Validazioni
define('MIN_PASSWORD_LENGTH', 6);
define('MAX_USERNAME_LENGTH', 50);
define('MAX_SERVICE_NAME_LENGTH', 100);
define('MAX_DESCRIPTION_LENGTH', 500);

// Orari
define('MIN_WORKING_HOUR', 6);
define('MAX_WORKING_HOUR', 23);

// Giorni della settimana (per validazione)
define('VALID_WEEKDAYS', [
    'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'
]);

// Configurazione di default per gli orari
define('DEFAULT_SCHEDULE_CONFIG', [
    'working_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
    'start_time' => '09:00',
    'end_time' => '18:00',
    'lunch_break_start' => '12:30',
    'lunch_break_end' => '13:30'
]);

// Messaggi di errore standard
define('ERROR_MESSAGES', [
    'INVALID_JSON' => 'Formato JSON non valido',
    'MISSING_REQUIRED_FIELD' => 'Campo obbligatorio mancante',
    'INVALID_EMAIL' => 'Email non valida',
    'PASSWORD_TOO_SHORT' => 'Password troppo corta (minimo ' . MIN_PASSWORD_LENGTH . ' caratteri)',
    'USERNAME_TOO_LONG' => 'Username troppo lungo (massimo ' . MAX_USERNAME_LENGTH . ' caratteri)',
    'SERVICE_NAME_TOO_LONG' => 'Nome servizio troppo lungo (massimo ' . MAX_SERVICE_NAME_LENGTH . ' caratteri)',
    'DESCRIPTION_TOO_LONG' => 'Descrizione troppo lunga (massimo ' . MAX_DESCRIPTION_LENGTH . ' caratteri)',
    'INVALID_TIME_FORMAT' => 'Formato orario non valido (usa HH:MM)',
    'INVALID_WORKING_HOURS' => 'Orari di lavoro non validi',
    'INVALID_WEEKDAY' => 'Giorno della settimana non valido',
    'DATABASE_ERROR' => 'Errore nel database',
    'NOT_FOUND' => 'Risorsa non trovata',
    'UNAUTHORIZED' => 'Accesso non autorizzato',
    'METHOD_NOT_ALLOWED' => 'Metodo non supportato'
]);

// Funzioni helper per l'API
function validateTimeFormat($time) {
    return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time);
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function sanitizeInput($input) {
    if (is_string($input)) {
        return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
    }
    return $input;
}

function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function sendErrorResponse($message, $statusCode = 400) {
    sendJsonResponse([
        'success' => false,
        'message' => $message
    ], $statusCode);
}

function sendSuccessResponse($data = null, $message = null) {
    $response = ['success' => true];
    
    if ($message !== null) {
        $response['message'] = $message;
    }
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    sendJsonResponse($response);
}
?>