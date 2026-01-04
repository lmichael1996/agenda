<?php
/**
 * Index - Punto di ingresso dell'applicazione Agenda
 * Gestisce routing iniziale: utenti autenticati → dashboard, altri → login
 */

// Definisce costante di protezione
define('AGENDA_APP', true);

// Carica il router per gestire autenticazione e routing
require_once __DIR__ . '/src/Auth/Router.php';

// Gestisce il routing automatico
handleRouting();
?>