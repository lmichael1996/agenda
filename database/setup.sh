#!/bin/bash

# ============================================
# Script di setup automatico database Agenda
# ============================================

set -e  # Exit on error

# Colori per output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Directory script
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

echo -e "${BLUE}=================================${NC}"
echo -e "${BLUE}  Setup Database Agenda${NC}"
echo -e "${BLUE}=================================${NC}"
echo ""

# Controlla se MySQL è installato
if ! command -v mysql &> /dev/null; then
    echo -e "${RED}✗ Errore: MySQL non trovato${NC}"
    echo "Installa MySQL/MariaDB prima di continuare"
    exit 1
fi

# Menu principale
echo "Scegli un'opzione:"
echo "1) Setup completo (crea database, tabelle e dati di esempio)"
echo "2) Solo database e tabelle (senza dati di esempio)"
echo "3) Solo dati di esempio (richiede database esistente)"
echo "4) Reset completo (ATTENZIONE: cancella tutti i dati!)"
echo "5) Esci"
echo ""
read -p "Scelta [1-5]: " choice

case $choice in
    1)
        echo -e "${YELLOW}Setup completo...${NC}"
        MODE="full"
        ;;
    2)
        echo -e "${YELLOW}Setup base (senza seed)...${NC}"
        MODE="base"
        ;;
    3)
        echo -e "${YELLOW}Solo seed data...${NC}"
        MODE="seed"
        ;;
    4)
        echo -e "${RED}ATTENZIONE: Stai per cancellare TUTTI i dati!${NC}"
        read -p "Sei sicuro? (scrivi 'CONFERMA' per continuare): " confirm
        if [ "$confirm" != "CONFERMA" ]; then
            echo -e "${YELLOW}Operazione annullata${NC}"
            exit 0
        fi
        MODE="reset"
        ;;
    5)
        echo -e "${YELLOW}Uscita...${NC}"
        exit 0
        ;;
    *)
        echo -e "${RED}Scelta non valida${NC}"
        exit 1
        ;;
esac

echo ""
read -p "MySQL root password: " -s MYSQL_ROOT_PASSWORD
echo ""

# Funzione per eseguire script SQL
execute_sql() {
    local file=$1
    local description=$2
    
    echo -e "${BLUE}→${NC} $description..."
    
    if mysql -u root -p"$MYSQL_ROOT_PASSWORD" < "$SCRIPT_DIR/$file" 2>&1; then
        echo -e "${GREEN}✓${NC} $description completato"
    else
        echo -e "${RED}✗${NC} Errore durante: $description"
        exit 1
    fi
}

# Esegui operazioni in base alla scelta
case $MODE in
    full)
        execute_sql "01_create_database.sql" "Creazione database"
        execute_sql "02_create_tables.sql" "Creazione tabelle"
        execute_sql "03_seed_data.sql" "Inserimento dati di esempio"
        ;;
    base)
        execute_sql "01_create_database.sql" "Creazione database"
        execute_sql "02_create_tables.sql" "Creazione tabelle"
        ;;
    seed)
        execute_sql "03_seed_data.sql" "Inserimento dati di esempio"
        ;;
    reset)
        # Per reset, esegui gli script separatamente
        echo -e "${BLUE}→${NC} Eliminazione database esistente..."
        mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "DROP DATABASE IF EXISTS agenda_db;" 2>&1
        mysql -u root -p"$MYSQL_ROOT_PASSWORD" -e "DROP USER IF EXISTS 'admin'@'localhost';" 2>&1
        echo -e "${GREEN}✓${NC} Database eliminato"
        
        execute_sql "01_create_database.sql" "Creazione database"
        execute_sql "02_create_tables.sql" "Creazione tabelle"
        execute_sql "03_seed_data.sql" "Inserimento dati di esempio"
        ;;
esac

echo ""
echo -e "${GREEN}=================================${NC}"
echo -e "${GREEN}✓ Setup completato con successo!${NC}"
echo -e "${GREEN}=================================${NC}"
echo ""
echo "Credenziali database:"
echo "  Host: localhost"
echo "  Database: agenda_db"
echo "  User: admin"
echo "  Password: admin123"
echo ""
echo "Credenziali login applicazione:"
echo "  Username: admin"
echo "  Password: admin123"
echo ""
echo -e "${YELLOW}⚠️  Ricorda di cambiare le password in produzione!${NC}"
echo ""
