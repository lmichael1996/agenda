<?php
/**
 * Configurazione principale dell'applicazione Agenda
 */

// Configurazione calendario
define('CALENDAR_START_HOUR', 8);
define('CALENDAR_END_HOUR', 22);
define('CALENDAR_INTERVAL_MINUTES', 15);

// Configurazione sicurezza
define('SESSION_TIMEOUT', 3600); // 1 ora
define('MAX_LOGIN_ATTEMPTS', 5);

// Configurazione app
define('APP_NAME', 'Agenda Settimanale');
define('APP_VERSION', '1.0.0');
define('TIMEZONE', 'Europe/Rome');

// Configurazione paths
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('ASSETS_PATH', ROOT_PATH . '/assets');

// URLs base
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
// Calcola il percorso base relativo alla directory public
$scriptPath = $_SERVER['SCRIPT_NAME'] ?? '';
if (strpos($scriptPath, '/public/') !== false) {
    $basePath = dirname(dirname($scriptPath));
} else {
    $basePath = dirname($scriptPath);
}
define('BASE_URL', $protocol . '://' . $host . $basePath);
define('ASSETS_URL', BASE_URL . '/assets');

// Impostazioni PHP
date_default_timezone_set(TIMEZONE);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.use_strict_mode', 1);

// Headers di sicurezza globali
function setSecurityHeaders() {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: no-referrer');
    header('X-XSS-Protection: 1; mode=block');
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// Auto-include dei file essenziali
require_once CONFIG_PATH . '/db.php';
require_once CONFIG_PATH . '/auth.php';