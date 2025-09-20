# Agenda Settimanale

Un sistema di gestione calendario settimanale con interfaccia web sviluppato in PHP, JavaScript e CSS.

## 🚀 Caratteristiche

- **Calendario settimanale** con vista giornaliera
- **Drag & Drop** per spostare eventi tra slot temporali
- **Resize** degli eventi con gestione dinamica della durata
- **Interfaccia responsive** per desktop e mobile
- **Login sicuro** con protezione delle sessioni
- **Design minimalista** con font Courier New
- **Menu laterale** e controlli di navigazione
- **Evidenziazione** dell'ora corrente

## 📁 Struttura del Progetto

```
agenda/
├── config/                 # File di configurazione
│   ├── config.php         # Configurazione principale
│   ├── db.php             # Configurazione database
│   └── auth.php           # Configurazione autenticazione
├── includes/               # Classi e utility PHP
│   ├── CalendarUtils.php  # Utility per gestione calendario
│   └── date-functions.php # Funzioni per date (legacy)
├── views/                  # Template HTML/PHP
│   ├── layout.php         # Layout principale
│   └── calendar.php       # Vista calendario
├── assets/                 # File statici
│   ├── css/               # Fogli di stile
│   │   ├── style.css
│   │   ├── top-menu.css
│   │   ├── lateral-menu-style.css
│   │   ├── week-calendar-style.css
│   │   ├── calendar-events.css
│   │   └── calendar-style.css
│   ├── js/                # JavaScript
│   │   ├── calendar-events.js
│   │   ├── calendar-date.js
│   │   └── lateral-menu.js
│   └── images/            # Immagini
│       ├── background.png
│       └── background-image.jpg
├── public/                 # File pubblici
│   ├── login.php          # Pagina di login
│   ├── login-connection.php # Gestione login
│   ├── dashboard.php      # Dashboard principale (legacy)
│   └── dashboard_new.php  # Dashboard con nuova struttura
├── api/                    # Endpoint API (futuro)
├── index.php              # Entry point principale
└── README.md              # Documentazione
```

## 🛠️ Installazione

1. **Clone del repository**
   ```bash
   git clone <repository-url>
   cd agenda
   ```

2. **Configurazione del server web**
   - Punta la document root su `/path/to/agenda/`
   - Assicurati che PHP sia abilitato
   - Configura il database in `config/db.php`

3. **Permessi**
   ```bash
   chmod 755 public/
   chmod 644 config/*.php
   ```

## 📋 Configurazione

### File config/config.php

Principali impostazioni configurabili:

```php
// Orari calendario
define('CALENDAR_START_HOUR', 8);
define('CALENDAR_END_HOUR', 22);
define('CALENDAR_INTERVAL_MINUTES', 15);

// Sicurezza
define('SESSION_TIMEOUT', 3600);
define('MAX_LOGIN_ATTEMPTS', 5);

// Timezone
define('TIMEZONE', 'Europe/Rome');
```

## 🎨 Personalizzazione CSS

I file CSS sono organizzati per funzionalità:

- `style.css` - Stili base e login
- `top-menu.css` - Menu superiore e controlli
- `lateral-menu-style.css` - Menu laterale
- `week-calendar-style.css` - Griglia calendario
- `calendar-events.css` - Stili eventi e note

## 📱 JavaScript

### calendar-events.js
- Gestione drag & drop
- Resize eventi
- Creazione note dinamiche
- Evidenziazione ora corrente

### calendar-date.js
- Navigazione date
- Widget settimana/giorno

### lateral-menu.js
- Controllo sidebar
- Animazioni menu

## 🔒 Sicurezza

- Headers di sicurezza automatici
- Protezione CSRF
- Validazione input
- Gestione sessioni sicura
- Controllo accessi

## 🚦 Utilizzo

1. **Accesso**: Naviga su `http://yourserver/` per accedere al login
2. **Login**: Usa le credenziali configurate (attualmente bypass attivo)
3. **Calendario**: Visualizza e gestisci eventi nella dashboard
4. **Eventi**: Drag & drop per spostare, resize per modificare durata
5. **Navigazione**: Usa i controlli in alto per cambiare settimana/giorno

## 🔄 API Future

La directory `api/` è predisposta per futuri endpoint REST:

- `GET /api/events` - Lista eventi
- `POST /api/events` - Crea evento
- `PUT /api/events/{id}` - Modifica evento
- `DELETE /api/events/{id}` - Elimina evento

## 🐛 Debug

Per attivare il debug:

1. Modifica `config/config.php`:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```

2. Controlla i log del server web per errori

## 📝 TODO

- [ ] Implementazione backend per salvataggio eventi
- [ ] API REST complete
- [ ] Sistema di notifiche
- [ ] Export/import calendario
- [ ] Multi-utente avanzato
- [ ] Mobile app companion

## 📄 Licenza

Progetto privato - Tutti i diritti riservati

## 👨‍💻 Sviluppo

Per modifiche allo stile o funzionalità:

1. **CSS**: Modifica i file in `assets/css/`
2. **JavaScript**: Modifica i file in `assets/js/`
3. **PHP**: Modifica template in `views/` o logica in `includes/`
4. **Configurazione**: Modifica `config/config.php`

Mantieni la separazione tra logica, presentazione e configurazione.