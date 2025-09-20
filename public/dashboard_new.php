<?php
/**
 * Dashboard principale - Calendario settimanale
 */

// Carica configurazione e dipendenze
require_once '../config/config.php';
require_once '../includes/CalendarUtils.php';

// Controlla autenticazione (commentato per sviluppo)
/*
session_start();
if (empty($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}
*/

// Dati per la vista
$pageTitle = 'Dashboard - ' . APP_NAME;

// Genera il contenuto del calendario
ob_start();
include '../views/calendar.php';
$content = ob_get_clean();

// Render del layout principale
include '../views/layout.php';
?>