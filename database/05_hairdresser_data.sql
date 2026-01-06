-- ============================================
-- Dati di test per parrucchieri
-- ============================================
-- File: 05_hairdresser_data.sql
-- Descrizione: Popolamento database con dati realistici per salone parrucchiere
-- ============================================

USE agenda_db;

-- Pulisci dati esistenti (mantieni solo entità di default id=1)
DELETE FROM appointments WHERE super_appointment_id > 0;
DELETE FROM super_appointments WHERE id > 0;
DELETE FROM notes WHERE id > 0;
DELETE FROM services WHERE id > 1;
DELETE FROM clients WHERE id > 1;
DELETE FROM users WHERE id > 1;

-- Reset auto increment
ALTER TABLE appointments AUTO_INCREMENT = 1;
ALTER TABLE super_appointments AUTO_INCREMENT = 1;
ALTER TABLE notes AUTO_INCREMENT = 1;
ALTER TABLE services AUTO_INCREMENT = 2;
ALTER TABLE clients AUTO_INCREMENT = 2;
ALTER TABLE users AUTO_INCREMENT = 2;

-- ============================================
-- UTENTI (Parrucchieri)
-- ============================================
INSERT INTO users (username, password_hash, color) VALUES
('giulia', '$2y$10$abcdefghijklmnopqrstuvwxyz123456789012345678901234', '#e74c3c'),  -- Rosso
('marco', '$2y$10$abcdefghijklmnopqrstuvwxyz123456789012345678901234', '#3498db'),   -- Blu
('sara', '$2y$10$abcdefghijklmnopqrstuvwxyz123456789012345678901234', '#2ecc71');    -- Verde

-- ============================================
-- CLIENTI
-- ============================================
INSERT INTO clients (first_name, last_name, phone, has_certificate, notes) VALUES
-- Clienti abituali
('Sofia', 'Romano', '+393331234567', 0, 'Taglio corto, rifila ogni mese'),
('Martina', 'Ferrari', '+393339876543', 0, 'Colore biondo, ritocchi ogni 6 settimane'),
('Chiara', 'Ricci', '+393335551234', 0, 'Piega ogni settimana, capelli lunghi'),
('Alessia', 'Marino', '+393337778899', 0, 'Cliente VIP, trattamenti premium'),
('Francesca', 'Greco', '+393334445566', 0, 'Taglia e piega mensile'),

-- Clienti occasionali
('Laura', 'Conti', '+393336667788', 0, 'Prima visita consigliata'),
('Valentina', 'De Luca', '+393338889900', 0, 'Meches bionde'),
('Elisa', 'Galli', '+393331112233', 0, 'Permanente ogni 3 mesi'),
('Federica', 'Fontana', '+393332223344', 0, 'Taglio tendenza'),
('Serena', 'Marini', '+393334556677', 0, 'Acconciatura sposa'),

-- Clienti uomo
('Luca', 'Bianchi', '+393335678901', 0, 'Taglio classico ogni 3 settimane'),
('Marco', 'Russo', '+393336789012', 0, 'Barba e capelli'),
('Andrea', 'Esposito', '+393337890123', 0, 'Sfumatura moderna'),
('Giovanni', 'Bruno', '+393338901234', 0, 'Rasatura completa'),
('Davide', 'Caruso', '+393339012345', 0, 'Taglio commerciale');

-- ============================================
-- SERVIZI PARRUCCHIERE
-- ============================================
INSERT INTO services (name, duration, price, description) VALUES
-- Tagli
('Taglio Donna', 30, 25.00, 'Taglio classico o moderno'),
('Taglio Uomo', 20, 15.00, 'Taglio maschile con rifinitura'),
('Taglio Bambino', 15, 12.00, 'Taglio per bambini fino a 12 anni'),

-- Piega e Styling
('Piega', 30, 20.00, 'Piega con phon e spazzola'),
('Piega Evento', 45, 35.00, 'Acconciatura per cerimonie ed eventi'),
('Stiratura', 45, 30.00, 'Stiratura lisciante professionale'),

-- Colorazione
('Colore', 60, 45.00, 'Colorazione completa'),
('Meches', 90, 65.00, 'Meches o colpi di sole'),
('Ritocco Ricrescita', 45, 35.00, 'Ritocco radici'),
('Shatush/Balayage', 120, 85.00, 'Tecnica sfumata naturale'),

-- Trattamenti
('Trattamento Ristrutturante', 30, 25.00, 'Maschera rigenerante'),
('Permanente', 90, 55.00, 'Permanente classica o digitale'),
('Stiratura Permanente', 120, 95.00, 'Stiratura brasiliana o cheratina'),

-- Uomo
('Barba', 15, 10.00, 'Rifinitura e cura barba'),
('Rasatura Completa', 20, 15.00, 'Rasatura tradizionale con rasoio');

-- ============================================
-- NOTE
-- ============================================
INSERT INTO notes (title, content, user_id, for_all, note_date) VALUES
('Chiusura Ferragosto', 'Il salone sarà chiuso dal 12 al 23 agosto', 1, 1, '2026-08-01'),
('Nuovi prodotti', 'Arrivata nuova linea biologica per trattamenti', 2, 1, '2026-01-15'),
('Promemoria Sara', 'Ordinare tinte per fine mese', 3, 0, CURDATE());

-- ============================================
-- APPUNTAMENTI SETTIMANA 6-12 GENNAIO 2026
-- ============================================

-- === LUNEDÌ 6 GENNAIO 2026 ===
-- 09:00 - Sofia: Taglio + Piega (Giulia)
INSERT INTO super_appointments (client_id, start_time) VALUES (2, '2026-01-06 09:00:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(1, 2, 2, 30),  -- Taglio Donna
(1, 5, 2, 30);  -- Piega

-- 10:00 - Martina: Ritocco Ricrescita (Marco)
INSERT INTO super_appointments (client_id, start_time) VALUES (3, '2026-01-06 10:00:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(2, 10, 3, 45);  -- Ritocco Ricrescita

-- 11:00 - Luca: Taglio Uomo (Sara)
INSERT INTO super_appointments (client_id, start_time) VALUES (12, '2026-01-06 11:00:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(3, 3, 4, 20);  -- Taglio Uomo

-- 14:00 - Chiara: Piega (Giulia)
INSERT INTO super_appointments (client_id, start_time) VALUES (4, '2026-01-06 14:00:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(4, 5, 2, 30);  -- Piega

-- 15:00 - Alessia: Shatush + Piega Evento (Marco)
INSERT INTO super_appointments (client_id, start_time) VALUES (5, '2026-01-06 15:00:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(5, 11, 3, 120),  -- Shatush
(5, 6, 3, 45);    -- Piega Evento

-- === MARTEDÌ 7 GENNAIO 2026 ===
-- 09:00 - Marco: Taglio + Barba (Sara)
INSERT INTO super_appointments (client_id, start_time) VALUES (13, '2026-01-07 09:00:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(6, 3, 4, 20),   -- Taglio Uomo
(6, 15, 4, 15);  -- Barba

-- 10:00 - Valentina: Meches (Giulia)
INSERT INTO super_appointments (client_id, start_time) VALUES (8, '2026-01-07 10:00:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(7, 9, 2, 90);  -- Meches

-- 11:45 - Andrea: Taglio Uomo (Sara)
INSERT INTO super_appointments (client_id, start_time) VALUES (14, '2026-01-07 11:45:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(8, 3, 4, 20);  -- Taglio Uomo

-- 14:00 - Francesca: Taglio + Piega (Marco)
INSERT INTO super_appointments (client_id, start_time) VALUES (6, '2026-01-07 14:00:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(9, 2, 3, 30),  -- Taglio Donna
(9, 5, 3, 30);  -- Piega

-- 15:30 - Elisa: Permanente (Giulia)
INSERT INTO super_appointments (client_id, start_time) VALUES (9, '2026-01-07 15:30:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(10, 13, 2, 90);  -- Permanente

-- === MERCOLEDÌ 8 GENNAIO 2026 ===
-- 09:00 - Sofia: Colore completo (Marco)
INSERT INTO super_appointments (client_id, start_time) VALUES (2, '2026-01-08 09:00:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(11, 8, 3, 60);  -- Colore

-- 10:30 - Giovanni: Rasatura Completa (Sara)
INSERT INTO super_appointments (client_id, start_time) VALUES (15, '2026-01-08 10:30:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(12, 16, 4, 20);  -- Rasatura Completa

-- 11:00 - Chiara: Piega (Giulia)
INSERT INTO super_appointments (client_id, start_time) VALUES (4, '2026-01-08 11:00:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(13, 5, 2, 30);  -- Piega

-- 14:00 - Laura: Taglio + Colore (Marco)
INSERT INTO super_appointments (client_id, start_time) VALUES (7, '2026-01-08 14:00:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(14, 2, 3, 30),  -- Taglio Donna
(14, 8, 3, 60);  -- Colore

-- 16:00 - Davide: Taglio Uomo (Sara)
INSERT INTO super_appointments (client_id, start_time) VALUES (16, '2026-01-08 16:00:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(15, 3, 4, 20);  -- Taglio Uomo

-- === GIOVEDÌ 9 GENNAIO 2026 ===
-- 09:00 - Martina: Trattamento Ristrutturante + Piega (Giulia)
INSERT INTO super_appointments (client_id, start_time) VALUES (3, '2026-01-09 09:00:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(16, 12, 2, 30),  -- Trattamento
(16, 5, 2, 30);   -- Piega

-- 10:30 - Luca: Taglio Uomo (Marco)
INSERT INTO super_appointments (client_id, start_time) VALUES (12, '2026-01-09 10:30:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(17, 3, 3, 20);  -- Taglio Uomo

-- 11:00 - Federica: Taglio + Stiratura (Sara)
INSERT INTO super_appointments (client_id, start_time) VALUES (10, '2026-01-09 11:00:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(18, 2, 4, 30),  -- Taglio Donna
(18, 7, 4, 45);  -- Stiratura

-- 14:00 - Alessia: Ritocco Ricrescita + Piega (Giulia)
INSERT INTO super_appointments (client_id, start_time) VALUES (5, '2026-01-09 14:00:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(19, 10, 2, 45),  -- Ritocco
(19, 5, 2, 30);   -- Piega

-- 16:00 - Marco: Taglio + Barba (Marco)
INSERT INTO super_appointments (client_id, start_time) VALUES (13, '2026-01-09 16:00:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(20, 3, 3, 20),   -- Taglio Uomo
(20, 15, 3, 15);  -- Barba

-- === VENERDÌ 10 GENNAIO 2026 ===
-- 09:00 - Serena: Piega Evento (acconciatura sposa) (Giulia + Marco)
INSERT INTO super_appointments (client_id, start_time) VALUES (11, '2026-01-10 09:00:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(21, 6, 2, 45);  -- Piega Evento

-- 10:00 - Chiara: Piega settimanale (Sara)
INSERT INTO super_appointments (client_id, start_time) VALUES (4, '2026-01-10 10:00:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(22, 5, 4, 30);  -- Piega

-- 11:00 - Andrea: Taglio Uomo (Marco)
INSERT INTO super_appointments (client_id, start_time) VALUES (14, '2026-01-10 11:00:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(23, 3, 3, 20);  -- Taglio Uomo

-- 14:00 - Valentina: Colore + Piega (Giulia)
INSERT INTO super_appointments (client_id, start_time) VALUES (8, '2026-01-10 14:00:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(24, 8, 2, 60),  -- Colore
(24, 5, 2, 30);  -- Piega

-- 16:00 - Giovanni: Taglio + Barba (Sara)
INSERT INTO super_appointments (client_id, start_time) VALUES (15, '2026-01-10 16:00:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(25, 3, 4, 20),   -- Taglio Uomo
(25, 15, 4, 15);  -- Barba

-- === SABATO 11 GENNAIO 2026 (Giorno intenso) ===
-- 09:00 - Sofia: Taglio + Piega (Giulia)
INSERT INTO super_appointments (client_id, start_time) VALUES (2, '2026-01-11 09:00:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(26, 2, 2, 30),  -- Taglio Donna
(26, 5, 2, 30);  -- Piega

-- 09:00 - Luca: Taglio Uomo (Marco)
INSERT INTO super_appointments (client_id, start_time) VALUES (12, '2026-01-11 09:00:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(27, 3, 3, 20);  -- Taglio Uomo (slot contemporaneo)

-- 10:00 - Francesca: Colore + Piega (Sara)
INSERT INTO super_appointments (client_id, start_time) VALUES (6, '2026-01-11 10:00:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(28, 8, 4, 60),  -- Colore
(28, 5, 4, 30);  -- Piega

-- 10:30 - Davide: Taglio Uomo (Marco)
INSERT INTO super_appointments (client_id, start_time) VALUES (16, '2026-01-11 10:30:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(29, 3, 3, 20);  -- Taglio Uomo

-- 11:00 - Elisa: Piega (Giulia)
INSERT INTO super_appointments (client_id, start_time) VALUES (9, '2026-01-11 11:00:00');
INSERT INTO appointments (super_appointment_id, service_id, user_id, duration) VALUES
(30, 5, 2, 30);  -- Piega

-- ============================================
-- CONFIGURAZIONE SALONE
-- ============================================
UPDATE settings SET 
    opening_time = '09:00:00',
    closing_time = '18:00:00',
    lunch_break_enabled = 1,
    break_start = '13:00:00',
    break_end = '14:00:00',
    closed_monday = 0,
    closed_tuesday = 0,
    closed_wednesday = 0,
    closed_thursday = 0,
    closed_friday = 0,
    closed_saturday = 0,
    closed_sunday = 1,
    timezone = 'Europe/Rome'
WHERE id = 1;
