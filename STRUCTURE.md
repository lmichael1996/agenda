# Agenda - Struttura Progetto

## 📁 Struttura Classica PHP/JavaScript

```
agenda/
│
├── public/                          # Document root (accessibile dal web)
│   ├── index.php                    # Entry point principale
│   ├── api.php                      # API endpoint (proxy verso src/api.php)
│   │
│   ├── assets/                      # Risorse statiche
│   │   ├── css/                     # Fogli di stile
│   │   ├── js/                      # JavaScript
│   │   │   ├── api/                 # Moduli API client-side
│   │   │   │   ├── clients-api.js
│   │   │   │   ├── services-api.js
│   │   │   │   ├── schedule-api.js
│   │   │   │   ├── notes-api.js
│   │   │   │   └── user-api.js
│   │   │   ├── classes/             # Classi JavaScript
│   │   │   └── utils/               # Utilità JavaScript
│   │   └── images/                  # Immagini
│   │
│   └── views/                       # Template e viste
│       ├── dashboard.php            # Dashboard calendario
│       ├── login.php                # Pagina login
│       ├── access-denied.php        # Accesso negato
│       └── popup/                   # Modal e popup
│           ├── clients.php
│           ├── services.php
│           ├── schedule.php
│           ├── notes.php
│           └── users.php
│
├── src/                             # Codice sorgente backend
│   ├── api.php                      # Router API REST principale
│   ├── Controllers/                 # Controller MVC
│   │   ├── ClientsController.php
│   │   ├── ServicesController.php
│   │   ├── ScheduleController.php
│   │   ├── NotesController.php
│   │   └── UsersController.php
│   ├── Models/                      # Model (da implementare)
│   └── Services/                    # Servizi business logic (da implementare)
│
├── config/                          # Configurazioni
│   ├── config.php                   # Configurazione principale
│   ├── db.php                       # Connessione database
│   ├── auth.php                     # Autenticazione
│   └── captcha.php                  # Gestione CAPTCHA
│
└── utils/                           # Utilità PHP
    ├── calendar_functions.php       # Funzioni calendario
    ├── logout.php                   # Gestione logout
    └── token_functions.php          # Gestione token
```

## 🎯 Vantaggi della Struttura

### 1. **Separazione Chiara**
- **`public/`**: Solo file accessibili dal web (index.php, api.php proxy, assets, views)
- **`src/`**: Codice sorgente protetto (API router, Controllers, Models, Services)
- **`config/`**: Configurazioni sensibili isolate

### 2. **Sicurezza**
- Document root su `public/` impedisce accesso diretto a src e config
- API principale in `src/api.php` (protetta), proxy pubblico in `public/api.php`
- Controllers e business logic completamente fuori dal web root
- Validazione centralizzata nel router API

### 3. **Manutenibilità**
- Struttura MVC standard
- File organizzati per responsabilità
- Facile navigazione del codice

### 4. **Scalabilità**
- Pronto per aggiungere Models
- Spazio per Services/business logic
- Pattern estendibile

## 🔧 Configurazione Web Server

### Apache

**httpd.conf o .htaccess:**
```apache
DocumentRoot "/path/to/agenda/public"

<Directory "/path/to/agenda/public">
    AllowOverride All
    Require all granted
</Directory>
```

### Nginx

**nginx.conf:**
```nginx
server {
    listen 80;
    server_name agenda.local;
    
    root /path/to/agenda/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php-fpm/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### PHP Built-in Server (Development)

```bash
cd public
php -S localhost:8000
```

## 📡 API Endpoints

Tutti gli endpoint sono accessibili tramite `public/api.php`:

```
GET    /api.php/clients          # Lista clienti
GET    /api.php/clients//123     # Dettaglio cliente
POST   /api.php/clients          # Crea cliente
PUT    /api.php/clients//123     # Aggiorna cliente
DELETE /api.php/clients//123     # Elimina cliente

# Stesso pattern per: services, schedule, notes, users
```

## 🚀 Prossimi Sviluppi

1. **Models**: Aggiungere modelli per gestione database
2. **Services**: Business logic separata dai controller
3. **Middleware**: Layer intermedio per auth/validation
4. **Tests**: Unit e integration tests
5. **Build Process**: Asset minification e bundling

## 📝 Note

- `public/index.php`: Unico entry point per pagine web
- `public/api.php`: Unico entry point per API REST
- Controllers in `src/Controllers/`: Logica applicativa
- Views in `public/views/`: Template e UI
- Assets in `public/assets/`: Risorse statiche ottimizzate
