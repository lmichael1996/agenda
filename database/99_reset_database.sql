-- ============================================
-- Script di reset completo database
-- ============================================
-- File: 99_reset_database.sql
-- Descrizione: Elimina e ricrea tutto
-- ATTENZIONE: Questo script cancella tutti i dati!
-- ============================================

-- Drop database se esistente
DROP DATABASE IF EXISTS agenda_db;

-- Drop utente se esistente
DROP USER IF EXISTS 'admin'@'localhost';

-- Ricrea tutto
SOURCE 01_create_database.sql;
SOURCE 02_create_tables.sql;
SOURCE 03_seed_data.sql;
