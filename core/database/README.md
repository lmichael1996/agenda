# Database Connection

File di configurazione centralizzata per le connessioni al database.

## 📁 File

- **connection.php** - File principale con credenziali e funzioni di connessione
- **config.example.php** - Template di configurazione (da copiare se necessario)

## 🔧 Configurazione

### Database Locale (Sviluppo)

Le credenziali di default in `connection.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'admin');
define('DB_PASSWORD', 'admin123');
define('DB_NAME', 'agenda_db');
```

### Database Online (Produzione)

Per utilizzare un database online, modifica le costanti in `connection.php`:

```php
// Commenta le credenziali locali
/*
define('DB_HOST', 'localhost');
define('DB_USER', 'admin');
define('DB_PASSWORD', 'admin123');
define('DB_NAME', 'agenda_db');
*/

// Decommenta e aggiorna le credenziali online
define('DB_HOST', 'your-online-host.com');
define('DB_USER', 'your-online-user');
define('DB_PASSWORD', 'your-online-password');
define('DB_NAME', 'your-online-database');
```

## 🚀 Setup Database Locale

### Prerequisiti

Installa MySQL/MariaDB:

```bash
# Arch Linux
sudo pacman -S mariadb
sudo mariadb-install-db --user=mysql --basedir=/usr --datadir=/var/lib/mysql
sudo systemctl start mariadb
sudo systemctl enable mariadb
sudo mariadb-secure-installation
```

### Creazione Database

```bash
cd /percorso/agenda/database
./setup.sh
```

Oppure manualmente:

```bash
mysql -u root -p < 01_create_database.sql
mysql -u root -p < 02_create_tables.sql
mysql -u root -p < 03_seed_data.sql
```

## 🔌 Utilizzo

### Connessione MySQLi (Legacy)

```php
require_once __DIR__ . '/core/database/connection.php';

// Usa la variabile globale $conn
$result = $conn->query("SELECT * FROM users");
```

### Connessione PDO (Moderna)

```php
require_once __DIR__ . '/core/database/connection.php';

// Ottieni connessione PDO
$db = getDBConnection();
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
```

## 🔒 Sicurezza

- **NON** committare credenziali di produzione su Git
- Usa variabili d'ambiente per credenziali sensibili
- Cambia password di default prima del deploy
- Limita i privilegi dell'utente database in produzione

## 📊 Verifica Connessione

```bash
# Test connessione locale
mysql -u admin -p agenda_db -e "SHOW TABLES;"

# Test credenziali
mysql -u admin -padmin123 agenda_db -e "SELECT COUNT(*) FROM users;"
```
