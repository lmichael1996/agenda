<?php
/**
 * API Endpoint per note
 * Restituisce le note per una settimana specifica
 */

header('Content-Type: application/json');

require_once '../../src/Controllers/NotesController.php';
require_once '../../src/Database/Connection.php';

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

// Carica note
$db = getDBConnection();
$controller = new NotesController($db);

// Ottieni tutte le note
$result = $controller->getAll();

if (!$result['success'] || !isset($result['data'])) {
    echo json_encode([], JSON_UNESCAPED_UNICODE);
    exit;
}

// Filtra le note per l'intervallo di date
$notes = array_filter($result['data'], function($note) use ($weekStart, $weekEnd) {
    $noteDate = new DateTime($note['note_date']);
    return $noteDate >= $weekStart && $noteDate <= $weekEnd;
});

// Formatta per il calendario
$formatted = [];
foreach ($notes as $note) {
    $date = new DateTime($note['note_date']);
    
    $formatted[] = [
        'id' => $note['id'],
        'type' => 'note',
        'date' => $date->format('d-m-Y'),
        'time' => '08:00', // Orario fisso per le note
        'title' => $note['title'] ?: '',
        'content' => $note['content'] ?: '',
        'user_id' => $note['user_id'],
        'for_all' => (bool)$note['for_all']
    ];
}

echo json_encode($formatted, JSON_UNESCAPED_UNICODE);
