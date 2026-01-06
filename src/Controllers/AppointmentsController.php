<?php
/**
 * Controller per la gestione degli appuntamenti
 */

require_once __DIR__ . '/../Database/Connection.php';

class AppointmentsController {
    private $pdo;

    public function __construct() {
        $this->pdo = getDBConnection();
    }

    /**
     * Ottiene tutti gli appuntamenti per una settimana specifica
     * @param string $weekStart Data inizio settimana (formato Y-m-d)
     * @param string $weekEnd Data fine settimana (formato Y-m-d)
     * @return array Array di appuntamenti
     */
    public function getWeekAppointments($weekStart, $weekEnd) {
        $sql = "
            SELECT 
                sa.id as super_appointment_id,
                sa.start_time,
                c.id as client_id,
                c.first_name,
                c.last_name,
                c.phone,
                a.id as appointment_id,
                a.duration,
                a.note,
                s.id as service_id,
                s.name as service_name,
                s.price as service_price,
                u.id as user_id,
                u.username as user_name,
                u.color as user_color
            FROM super_appointments sa
            JOIN clients c ON sa.client_id = c.id
            JOIN appointments a ON a.super_appointment_id = sa.id
            JOIN services s ON a.service_id = s.id
            LEFT JOIN users u ON a.user_id = u.id
            WHERE DATE(sa.start_time) BETWEEN :week_start AND :week_end
            ORDER BY sa.start_time, a.id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'week_start' => $weekStart,
            'week_end' => $weekEnd
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Formatta gli appuntamenti per il calendario
     * @param array $appointments Array di appuntamenti dal database
     * @return array Array di eventi formattati per il calendario
     */
    public function formatForCalendar($appointments) {
        $events = [];
        $currentSuperAppointment = null;
        $accumulatedMinutes = 0;
        
        foreach ($appointments as $apt) {
            // Se cambia super_appointment, resetta l'accumulo
            if ($currentSuperAppointment !== $apt['super_appointment_id']) {
                $currentSuperAppointment = $apt['super_appointment_id'];
                $accumulatedMinutes = 0;
            }
            
            // Calcola l'orario di questo servizio
            $startTime = new DateTime($apt['start_time']);
            $startTime->modify("+{$accumulatedMinutes} minutes");
            
            $duration = (int)$apt['duration'];
            
            $events[] = [
                'id' => $apt['appointment_id'],
                'super_appointment_id' => $apt['super_appointment_id'],
                'date' => $startTime->format('d-m-Y'),
                'time' => $startTime->format('H:i'),
                'datetime' => $startTime->format('Y-m-d H:i:s'),
                'client_id' => $apt['client_id'],
                'client_name' => $apt['first_name'] . ' ' . $apt['last_name'],
                'client_phone' => $apt['phone'],
                'service_id' => $apt['service_id'],
                'service_name' => $apt['service_name'],
                'duration' => $duration,
                'price' => $apt['service_price'],
                'user_id' => $apt['user_id'],
                'user_name' => $apt['user_name'] ?? 'Non assegnato',
                'user_color' => $apt['user_color'] ?? '#95a5a6',
                'note' => $apt['note']
            ];
            
            // Accumula la durata per il prossimo servizio
            $accumulatedMinutes += $duration;
        }
        
        return $events;
    }

    /**
     * Ottiene gli appuntamenti formattati per la settimana corrente
     * @return string JSON degli appuntamenti
     */
    public function getCurrentWeekAppointmentsJSON() {
        $today = new DateTime();
        $weekStart = clone $today;
        $weekStart->modify('monday this week');
        $weekEnd = clone $weekStart;
        $weekEnd->modify('+6 days');

        $appointments = $this->getWeekAppointments(
            $weekStart->format('Y-m-d'),
            $weekEnd->format('Y-m-d')
        );

        $formatted = $this->formatForCalendar($appointments);
        
        return json_encode($formatted, JSON_UNESCAPED_UNICODE);
    }
}
