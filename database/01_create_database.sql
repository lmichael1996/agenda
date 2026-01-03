-- ============================================
-- Script di creazione database Agenda
-- ============================================
-- File: 01_create_database.sql
-- Descrizione: Creazione database e utente
-- ============================================

-- Creazione database
CREATE DATABASE IF NOT EXISTS agenda_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

-- Creazione utente dedicato
CREATE USER IF NOT EXISTS 'admin'@'localhost' IDENTIFIED BY 'admin123';

-- Assegnazione privilegi
GRANT ALL PRIVILEGES ON agenda_db.* TO 'admin'@'localhost';

-- Applica modifiche
FLUSH PRIVILEGES;

-- Seleziona database
USE agenda_db;
