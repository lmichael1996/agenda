<?php
// Connessione al database MySQL locale
$host = 'localhost';
$user = 'admin';
$password = 'admin123';
$dbname = 'agenda_db';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_errno) {
    error_log('Errore connessione DB: ' . $conn->connect_error);
}
// Non chiudere la connessione qui, usala negli altri file
