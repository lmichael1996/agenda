-- ============================================
-- Script di creazione tabelle
-- ============================================
-- File: 02_create_tables.sql
-- Descrizione: Creazione di tutte le tabelle
-- ============================================

USE agenda_db;

-- Tabella utenti
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    color VARCHAR(7) DEFAULT '#3498db'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella clienti
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    notes TEXT,
    has_certificate TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella servizi
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    duration INT NOT NULL COMMENT 'Durata in minuti',
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella super appuntamenti (appuntamento principale)
CREATE TABLE IF NOT EXISTS super_appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL DEFAULT 1,
    start_time DATETIME NOT NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella appuntamenti (servizi dell'appuntamento)
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    super_appointment_id INT NOT NULL,
    service_id INT NOT NULL DEFAULT 1,
    user_id INT NOT NULL DEFAULT 1,
    duration INT NOT NULL COMMENT 'Durata in minuti',
    note TEXT,
    FOREIGN KEY (super_appointment_id) REFERENCES super_appointments(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- NOTA: MySQL/MariaDB non supporta ON DELETE SET DEFAULT.
-- L'aggiornamento ai valori default è gestito a livello applicativo nel controller prima dell'eliminazione:
-- - Cliente default: id=1 (per super_appointments)
-- - Servizio default: id=1 (per appointments)
-- - Utente default: id=1 (per appointments)

-- Tabella note
CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    user_id INT,
    for_all TINYINT(1) DEFAULT 0 COMMENT 'Visibile a tutti gli utenti',
    note_date DATE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabella orari di lavoro
CREATE TABLE IF NOT EXISTS schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    day_of_week ENUM('lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato', 'domenica') NOT NULL UNIQUE,
    opening_time TIME,
    closing_time TIME,
    lunch_break_enabled TINYINT(1) DEFAULT 0,
    break_start TIME,
    break_end TIME,
    is_closed TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
