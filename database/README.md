# Database Setup - Agenda

Questa cartella contiene tutti gli script SQL per configurare il database dell'applicazione Agenda.

## 📁 Struttura File

```
database/
├── 01_create_database.sql    # Crea database e utente
├── 02_create_tables.sql       # Crea tutte le tabelle
├── 03_seed_data.sql           # Inserisce dati di esempio
├── 99_reset_database.sql      # Reset completo (ATTENZIONE!)
└── README.md                  # Questo file
```

## 🚀 Setup Iniziale

### Opzione 1: Setup Completo (Consigliato)

Esegui gli script in ordine numerico:

```bash
# Connettiti a MySQL come root
mysql -u root -p

# Esegui gli script
source /percorso/agenda/database/01_create_database.sql
source /percorso/agenda/database/02_create_tables.sql
source /percorso/agenda/database/03_seed_data.sql
```

### Opzione 2: Setup con Un Comando

```bash
mysql -u root -p < /percorso/agenda/database/01_create_database.sql
mysql -u root -p < /percorso/agenda/database/02_create_tables.sql
mysql -u root -p < /percorso/agenda/database/03_seed_data.sql
```

### Opzione 3: Reset Completo (Solo se necessario)

```bash
# ATTENZIONE: Cancella tutti i dati!
mysql -u root -p < /percorso/agenda/database/99_reset_database.sql
```

## 📊 Credenziali di Default

### Database
- **Host**: `localhost`
- **Database**: `agenda_db`
- **User**: `admin`
- **Password**: `admin123`

### Utenti Applicazione
Dopo il seed, puoi accedere con:

| Username | Password | Ruolo |
|----------|----------|-------|
| admin    | admin123 | admin |
| mario    | admin123 | user  |
| lucia    | admin123 | user  |

> ⚠️ **IMPORTANTE**: Cambia queste password in produzione!

## 📋 Tabelle Create

### `users`
Utenti del sistema (operatori e admin)
- Campi: id, username, password_hash, email, role, color, is_active
- Indici: username, is_active

### `clients`
Clienti/Pazienti
- Campi: id, first_name, last_name, phone, notes, has_certificate
- Indici: fullname, phone

### `services`
Servizi offerti
- Campi: id, name, duration (minuti), price, description
- Indice: name

### `appointments`
Appuntamenti
- Campi: id, client_id, service_id, user_id, start_time, end_time, status, notes
- Foreign keys: clients, services, users
- Indici: start_time, client_id, service_id, status

### `notes`
Note e promemoria
- Campi: id, title, content, user_id, for_all, note_date
- Foreign key: users
- Indici: note_date, user_id

### `schedule`
Orari di lavoro settimanali
- Campi: day_of_week, opening_time, closing_time, lunch_break, is_closed
- Unique: day_of_week

## 🔧 Manutenzione

### Backup Database

```bash
# Backup completo
mysqldump -u admin -p agenda_db > backup_$(date +%Y%m%d).sql

# Backup solo struttura
mysqldump -u admin -p --no-data agenda_db > schema_backup.sql

# Backup solo dati
mysqldump -u admin -p --no-create-info agenda_db > data_backup.sql
```

### Ripristino da Backup

```bash
mysql -u admin -p agenda_db < backup_20260103.sql
```

### Verifica Installazione

```bash
mysql -u admin -p agenda_db -e "SHOW TABLES;"
mysql -u admin -p agenda_db -e "SELECT COUNT(*) FROM users;"
```

## 📈 Dati di Esempio

Lo script `03_seed_data.sql` inserisce:
- 3 utenti (1 admin + 2 operatori)
- 5 clienti di esempio
- 5 servizi (massaggi e consulenze)
- 7 configurazioni orari settimanali
- 3 note
- 4 appuntamenti nei prossimi giorni

## 🔒 Sicurezza

### In Produzione

1. **Cambia le password di default**:
   ```sql
   ALTER USER 'admin'@'localhost' IDENTIFIED BY 'password_sicura_complessa';
   ```

2. **Limita i privilegi**:
   ```sql
   REVOKE ALL PRIVILEGES ON *.* FROM 'admin'@'localhost';
   GRANT SELECT, INSERT, UPDATE, DELETE ON agenda_db.* TO 'admin'@'localhost';
   ```

3. **Usa connessioni SSL** quando possibile

4. **Backup regolari** (giornalieri per dati critici)

## ❓ Troubleshooting

### Errore: Access denied

Assicurati di essere connesso come root o con un utente con privilegi CREATE:
```bash
mysql -u root -p
```

### Errore: Database già esistente

Elimina il database esistente:
```sql
DROP DATABASE IF EXISTS agenda_db;
```

### Errore: Utente già esistente

Elimina l'utente esistente:
```sql
DROP USER IF EXISTS 'admin'@'localhost';
```

## 📞 Supporto

Per problemi o domande, consulta la documentazione principale del progetto.
