<?php
/**
 * API Endpoint per appuntamenti
 * Restituisce gli appuntamenti per una settimana specifica
 */

header('Content-Type: application/json');

require_once '../../src/Controllers/AppointmentsController.php';

// Verifica parametri
$weekParam = isset($_GET['week']) ? $_GET['week'] : null;

if (!$weekParam) {
    echo json_encode(['error' => 'Parametro week mancante'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Parse formato settimana: 2026-W02
if (!preg_match('/(\d{4})-W(\d{2})/', $weekParam, $matches)) {
    echo json_encode(['error' => 'Formato settimana non valido'], JSON_UNESCAPED_UNICODE);
    exit;
}

$year = $matches[1];
$week = $matches[2];

// Calcola lunedì della settimana
$baseDate = new DateTime();
$baseDate->setISODate($year, $week, 1);

$weekStart = clone $baseDate;
$weekEnd = clone $baseDate;
$weekEnd->modify('+6 days');

// Carica appuntamenti
$controller = new AppointmentsController();
$appointments = $controller->getWeekAppointments(
    $weekStart->format('Y-m-d'),
    $weekEnd->format('Y-m-d')
);

$formatted = $controller->formatForCalendar($appointments);

echo json_encode($formatted, JSON_UNESCAPED_UNICODE);
