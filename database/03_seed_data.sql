-- ============================================
-- Script di inserimento dati iniziali
-- ============================================
-- File: 03_seed_data.sql
-- Descrizione: Dati di esempio per testing
-- ============================================

USE agenda_db;

-- Inserimento utente admin di default
-- Username: admin
-- Password: admin123
INSERT INTO users (username, password_hash, type_role, color, is_active) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '#e74c3c', 1);

-- Inserimento utenti operatori di esempio
INSERT INTO users (username, password_hash, type_role, color, is_active) VALUES
('mario', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '#3498db', 1),
('lucia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '#2ecc71', 1);

-- Inserimento clienti di esempio
INSERT INTO clients (first_name, last_name, phone, has_certificate, notes) VALUES
('Giovanni', 'Rossi', '+39 348 1234567', 1, 'Cliente storico, preferisce appuntamenti mattutini'),
('Maria', 'Bianchi', '+39 340 9876543', 0, 'Nuovo cliente'),
('Luca', 'Verdi', '+39 333 5551234', 1, 'Ha certificato medico valido'),
('Anna', 'Neri', '+39 347 4445678', 0, NULL),
('Paolo', 'Gialli', '+39 339 7778899', 1, 'Appuntamenti fissi il martedì');

-- Inserimento servizi di esempio
INSERT INTO services (name, duration, price, description) VALUES
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