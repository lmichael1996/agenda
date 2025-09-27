<?php
/**
 * Test API per debug - versione semplificata
 */

// Headers per JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gestione preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Test connessione database
    require_once '../../config/config.php';
    
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Test query semplice
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Verifica se la tabella clients esiste
        $stmt = $pdo->query("SHOW TABLES LIKE 'clients'");
        $tableExists = $stmt->rowCount() > 0;
        
        if ($tableExists) {
            // Conta i record
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM clients");
            $count = $stmt->fetch()['count'];
            
            // Recupera alcuni record di esempio
            $stmt = $pdo->query("SELECT * FROM clients LIMIT 5");
            $clients = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'message' => 'Database connection successful',
                'table_exists' => true,
                'total_clients' => $count,
                'sample_clients' => $clients
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Clients table does not exist',
                'table_exists' => false
            ]);
        }
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'API is working',
            'method' => $_SERVER['REQUEST_METHOD']
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>