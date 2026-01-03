<?php
/**
 * Index - Punto di ingresso dell'applicazione Agenda
 * Gestisce routing iniziale: utenti autenticati → dashboard, altri → login
 */

// Carica gateway di sicurezza e controlli
require_once __DIR__ . '/core/gateway/index.php';

// Redirect al login
header('Location: public/views/login.php');
exit;
?>