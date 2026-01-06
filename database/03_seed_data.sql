-- ============================================
-- Script di inserimento dati iniziali
-- ============================================
-- File: 03_seed_data.sql
-- Descrizione: Dati di esempio per testing
-- ============================================

USE agenda_db;

-- Inserimento utente admin di default
-- NOTA: L'utente con id=1 è l'utente DEFAULT e non può essere eliminato
-- Username: admin
-- Password: admin123
INSERT INTO users (username, password_hash, type_role, color) VALUES
('admin', '$2y$12$K0NVVsiWYa7d/8GUhC8mtuagDQL5CIwDlw7fM2N.WaOvrpiuXxvmC', 'admin', '#e74c3c');

-- Inserimento utenti operatori di esempio
INSERT INTO users (username, password_hash, type_role, color) VALUES
('mario', '$2y$12$K0NVVsiWYa7d/8GUhC8mtuagDQL5CIwDlw7fM2N.WaOvrpiuXxvmC', 'user', '#3498db');

-- Inserimento clienti di esempio
INSERT INTO clients (first_name, last_name, phone, has_certificate, notes) VALUES
('Giovanni', 'Rossi', '+39 348 1234567', 1, 'Cliente storico, preferisce appuntamenti mattutini'),
('Maria', 'Bianchi', '+39 340 9876543', 0, 'Nuovo cliente'),
('Luca', 'Verdi', '+39 333 5551234', 1, 'Ha certificato medico valido'),
('Anna', 'Neri', '+39 347 4445678', 0, NULL),
('Paolo', 'Gialli', '+39 339 7778899', 1, 'Appuntamenti fissi il martedì');

-- Inserimento servizi di esempio
-- NOTA: Il primo servizio (id=1) è il servizio di DEFAULT e non dovrebbe essere eliminato
INSERT INTO services (name, duration, price, description) VALUES
('Servizio Generico', 30, 0.00, 'Servizio di default (NON ELIMINARE)'),
('Massaggio Rilassante', 60, 50.00, 'Massaggio completo per rilassamento muscolare'),
('Massaggio Sportivo', 45, 45.00, 'Massaggio per sportivi con tecniche specifiche'),
('Riflessologia Plantare', 30, 35.00, 'Trattamento riflessologico dei piedi'),
('Massaggio Cervicale', 30, 30.00, 'Trattamento specifico zona collo e spalle'),
('Consulenza Posturale', 45, 40.00, 'Analisi posturale e consigli personalizzati');

-- Inserimento orari di lavoro di default (Lun-Ven 9-18, pausa pranzo 13-14)
INSERT INTO schedule (day_of_week, opening_time, closing_time, lunch_break_enabled, break_start, break_end, is_closed) VALUES
('lunedi', '09:00:00', '18:00:00', 1, '13:00:00', '14:00:00', 0),
('martedi', '09:00:00', '18:00:00', 1, '13:00:00', '14:00:00', 0),
('mercoledi', '09:00:00', '18:00:00', 1, '13:00:00', '14:00:00', 0),
('giovedi', '09:00:00', '18:00:00', 1, '13:00:00', '14:00:00', 0),
('venerdi', '09:00:00', '18:00:00', 1, '13:00:00', '14:00:00', 0),
('sabato', '09:00:00', '13:00:00', 0, NULL, NULL, 0),
('domenica', NULL, NULL, 0, NULL, NULL, 1);

-- Inserimento note di esempio
INSERT INTO notes (title, content, user_id, for_all, note_date) VALUES
('Chiusura estiva', 'Lo studio sarà chiuso dal 10 al 25 agosto', 1, 1, '2026-08-01'),
('Nuovi orari', 'Da settembre orario continuato 9-17', 1, 1, '2026-09-01'),
('Promemoria personale', 'Ordinare nuovo materiale sanitario', 2, 0, CURDATE());

-- Inserimento super appuntamenti di esempio (settimana 6-10 gennaio 2026)
INSERT INTO super_appointments (client_id, start_time) VALUES
(1, '2026-01-06 09:00:00'),  -- Lunedì: Giovanni
(2, '2026-01-06 10:30:00'),  -- Lunedì: Maria
(3, '2026-01-07 14:00:00'),  -- Martedì: Luca
(4, '2026-01-08 09:00:00'),  -- Mercoledì: Anna
(1, '2026-01-08 15:00:00'),  -- Mercoledì: Giovanni (ritorna)
(5, '2026-01-09 10:00:00'),  -- Giovedì: Paolo
(2, '2026-01-10 09:30:00'),  -- Venerdì: Maria
(3, '2026-01-10 14:30:00');  -- Venerdì: Luca

-- Inserimento servizi per gli appuntamenti
-- Super appuntamento 1: Giovanni prende Massaggio Rilassante (60 min) con admin
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration, note) VALUES
(1, 2, 1, 60, 'Cliente abituale');

-- Super appuntamento 2: Maria prende Massaggio Sportivo (45 min) con mario
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration, note) VALUES
(2, 3, 2, 45, NULL);

-- Super appuntamento 3: Luca prende due servizi
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration, note) VALUES
(3, 4, 1, 30, 'Ha certificato medico'),  -- Riflessologia (30 min) con admin
(3, 5, 2, 30, NULL);                      -- Massaggio Cervicale (30 min) con mario

-- Super appuntamento 4: Anna prende Consulenza (45 min) con admin
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration, note) VALUES
(4, 6, 1, 45, 'Prima visita');

-- Super appuntamento 5: Giovanni ritorna per Massaggio Cervicale (30 min) con mario
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration, note) VALUES
(5, 5, 2, 30, 'Follow-up');

-- Super appuntamento 6: Paolo prende Massaggio Rilassante (60 min) con admin
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration, note) VALUES
(6, 2, 1, 60, 'Appuntamento fisso settimanale');

-- Super appuntamento 7: Maria prende Riflessologia (30 min) con mario
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration, note) VALUES
(7, 4, 2, 30, NULL);

-- Super appuntamento 8: Luca prende Massaggio Sportivo (45 min) con admin
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration, note) VALUES
(8, 3, 1, 45, 'Post allenamento');