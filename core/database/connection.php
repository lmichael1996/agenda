<?php
/**
 * Database Connection - Credenziali e connessioni centralizzate
 * File: core/database/connection.php
 * 
 * Fornisce:
 * - Costanti per credenziali database
 * - Connessione MySQLi ($conn) per codice legacy
 * - Funzione getDBConnection() per connessioni PDO moderne
 */

// ============================================
// CONFIGURAZIONE DATABASE
// ============================================

// Database LOCALE (sviluppo)
define('DB_HOST', 'localhost');
define('DB_USER', 'admin');
define('DB_PASSWORD', 'admin123');
define('DB_NAME', 'agenda_db');

// Database ONLINE (produzione) - Decommentare quando necessario
/*
define('DB_HOST', 'your-online-host.com');
define('DB_USER', 'your-online-user');
define('DB_PASSWORD', 'your-online-password');
define('DB_NAME', 'your-online-database');
*/

// ============================================
// SETUP DATABASE LOCALE
// ============================================
// Per creare il database locale esegui:
// cd /home/mich/Software/agenda/database
// ./setup.sh
// 
// Oppure manualmente:
// mysql -u root -p < 01_create_database.sql
// mysql -u root -p < 02_create_tables.sql
// mysql -u root -p < 03_seed_data.sql
// ============================================

/**
 * Connessione MySQLi (per codice legacy)
 * @global mysqli $conn
 */
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if ($conn->connect_errno) {
    error_log('DB MySQLi Error: ' . $conn->connect_error);
    die('Errore di connessione al database');
}

$conn->set_charset('utf8mb4');

/**
 * Crea connessione PDO (per Controllers e API moderna)
 * @return PDO
 */
function getDBConnection() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASSWORD,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log("DB PDO Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Errore connessione database']);
        exit;
    }
}
?>