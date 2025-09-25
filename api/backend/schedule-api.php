<?php
/**
 * API per la gestione degli orari di lavoro
 * Supporta operazioni CRUD per la tabella schedule
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

// Funzione per validare i dati dell'orario (controlli essenziali)
function validateScheduleData($data) {
    $errors = [];
    
    // Validazione logica orari (solo controlli necessari)
    if (isset($data['opening_time']) && isset($data['closing_time'])) {
        if ($data['opening_time'] >= $data['closing_time']) {
            $errors[] = 'L\'orario di apertura deve essere precedente a quello di chiusura';
        }
    }
    
    // Validazione pausa pranzo (solo se abilitata)
    if (isset($data['lunch_break_enabled']) && $data['lunch_break_enabled'] == 1) {
        if (isset($data['break_start']) && isset($data['break_end'])) {
            if ($data['break_start'] >= $data['break_end']) {
                $errors[] = 'L\'inizio pausa deve essere precedente alla fine pausa';
            }
            
            if (isset($data['opening_time']) && isset($data['closing_time'])) {
                if ($data['break_start'] <= $data['opening_time'] || $data['break_end'] >= $data['closing_time']) {
                    $errors[] = 'La pausa pranzo deve essere compresa nell\'orario di lavoro';
                }
            }
        }
    }
    
    return $errors;
}

// Funzione per convertire giorni da array a stringa bit
function convertWorkingDaysToString($days) {
    $daysBit = '0000000'; // Lunedì a Domenica
    $dayMap = [
        'lunedi' => 0,
        'martedi' => 1, 
        'mercoledi' => 2,
        'giovedi' => 3,
        'venerdi' => 4,
        'sabato' => 5,
        'domenica' => 6
    ];
    
    if (is_array($days)) {
        $bits = str_split($daysBit);
        foreach ($days as $day) {
            if (isset($dayMap[$day])) {
                $bits[$dayMap[$day]] = '1';
            }
        }
        return implode('', $bits);
    }
    
    return $daysBit;
}

// Funzione per convertire stringa bit a array giorni
function convertWorkingDaysFromString($daysBit) {
    $dayMap = ['lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato', 'domenica'];
    $days = [];
    
    if (strlen($daysBit) === 7) {
        for ($i = 0; $i < 7; $i++) {
            if ($daysBit[$i] === '1') {
                $days[] = $dayMap[$i];
            }
        }
    }
    
    return $days;
}



try {
    $method = $_SERVER['REQUEST_METHOD'];
    error_log("Schedule API: Metodo $method, Session ID: " . (session_id() ?: 'none'));
    $pdo = getDBConnection();
    
    switch ($method) {
        case 'GET':
            // Verifica esistenza tabella schedule_config
            try {
                $stmt = $pdo->query("SHOW TABLES LIKE 'schedule_config'");
                if ($stmt->rowCount() == 0) {
                    error_log("Tabella 'schedule_config' non esiste");
                    throw new Exception("Tabella 'schedule_config' non trovata");
                }
            } catch (PDOException $e) {
                error_log("Errore verifica tabella: " . $e->getMessage());
                throw $e;
            }
            
            // Recupera configurazione orario (singleton)
            $stmt = $pdo->prepare("SELECT * FROM schedule_config ORDER BY id DESC LIMIT 1");
            $stmt->execute();
            $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($schedule) {
                // Converti working_days da bit string ad array
                $schedule['working_days_array'] = convertWorkingDaysFromString($schedule['working_days']);
            } else {
                // Restituisci valori di default se non esistono
                $schedule = [
                    'id' => null,
                    'opening_time' => '08:00:00',
                    'closing_time' => '18:00:00',
                    'lunch_break_enabled' => 1,
                    'break_start' => '12:30:00',
                    'break_end' => '13:30:00',
                    'working_days' => '1111100',
                    'working_days_array' => ['lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi'],
                    'timezone' => 'Europe/Rome'
                ];
            }
            
            echo json_encode(['success' => true, 'data' => $schedule]);
            break;
            
        case 'POST':
        case 'PUT':
            requireAuth();
            
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                throw new Exception('Dati JSON non validi');
            }
            
            // Prepara i dati per l'inserimento/aggiornamento
            $data = [
                'opening_time' => $input['opening_time'] ?? '08:00:00',
                'closing_time' => $input['closing_time'] ?? '18:00:00',
                'lunch_break_enabled' => array_key_exists('lunch_break_enabled', $input) ? (int)$input['lunch_break_enabled'] : 1,
                'break_start' => $input['break_start'] ?? '12:30:00',
                'break_end' => $input['break_end'] ?? '13:30:00',
                'working_days' => isset($input['working_days_array']) 
                    ? convertWorkingDaysToString($input['working_days_array'])
                    : '1111100',
                'timezone' => $input['timezone'] ?? 'Europe/Rome'
            ];
            
            // Validazione logica essenziale
            $errors = validateScheduleData($data);
            if (!empty($errors)) {
                http_response_code(400);
                echo json_encode(['error' => 'Dati non validi', 'details' => $errors]);
                break;
            }
            
            // Controlla se esiste già un record (singleton)
            $stmt = $pdo->prepare("SELECT id FROM schedule_config LIMIT 1");
            $stmt->execute();
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Aggiorna il record esistente
                $stmt = $pdo->prepare("
                    UPDATE schedule_config SET 
                        opening_time = :opening_time,
                        closing_time = :closing_time,
                        lunch_break_enabled = :lunch_break_enabled,
                        break_start = :break_start,
                        break_end = :break_end,
                        working_days = :working_days,
                        timezone = :timezone
                    WHERE id = :id
                ");
                $data['id'] = $existing['id'];
                $stmt->execute($data);
                $scheduleId = $existing['id'];
            } else {
                // Inserisci nuovo record
                $stmt = $pdo->prepare("
                    INSERT INTO schedule_config (opening_time, closing_time, lunch_break_enabled, break_start, break_end, working_days, timezone)
                    VALUES (:opening_time, :closing_time, :lunch_break_enabled, :break_start, :break_end, :working_days, :timezone)
                ");
                $stmt->execute($data);
                $scheduleId = $pdo->lastInsertId();
            }
            
            // Recupera il record aggiornato
            $stmt = $pdo->prepare("SELECT * FROM schedule_config WHERE id = :id");
            $stmt->execute(['id' => $scheduleId]);
            $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
            $schedule['working_days_array'] = convertWorkingDaysFromString($schedule['working_days']);
            
            echo json_encode(['success' => true, 'data' => $schedule, 'message' => 'Orario salvato con successo']);
            break;
            
        case 'DELETE':
            requireAuth();
            
            // Elimina tutti i record (reset configurazione)
            $stmt = $pdo->prepare("DELETE FROM schedule_config");
            $stmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'Configurazione orario eliminata']);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Metodo non supportato']);
            break;
    }
    
} catch (PDOException $e) {
    error_log("Errore database schedule API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Errore del database', 'details' => $e->getMessage()]);
} catch (Exception $e) {
    error_log("Errore schedule API: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>