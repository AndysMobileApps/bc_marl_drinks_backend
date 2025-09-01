# BC Marl Drinks - Backend API

REST API Backend für die BC Marl Drinks Vereins-Snack-App.

## 🚀 Quick Start

### Voraussetzungen
- Docker & Docker Compose
- Git

### Installation

```bash
# Repository klonen
git clone https://github.com/AndysMobileApps/bc_marl_drinks_backend.git
cd bc_marl_drinks_backend

# Environment konfigurieren
cp .env.example .env

# Docker Container starten
docker-compose up -d

# Composer Dependencies installieren
docker-compose exec api composer install

# Datenbank migrieren (automatisch beim ersten Start)
# MySQL wird automatisch mit Schema und Beispieldaten initialisiert
```

### URLs nach dem Start
- **API:** http://localhost:8080
- **phpMyAdmin:** http://localhost:8081
- **Health Check:** http://localhost:8080/v1/health

## 🏗️ Architektur

### Tech Stack
- **PHP 8.2+** mit Slim Framework
- **MySQL 8.0** Datenbank
- **Docker** Containerisierung
- **JWT** Authentifizierung
- **Eloquent ORM** für Datenbankzugriff

### Projektstruktur
```
├── docker-compose.yml          # Docker Services
├── api/                        # PHP API
│   ├── public/index.php        # Entry Point
│   ├── src/
│   │   ├── Controllers/        # API Controller
│   │   ├── Models/            # Eloquent Models
│   │   ├── Services/          # Business Logic
│   │   └── Middleware/        # HTTP Middleware
│   └── composer.json          # PHP Dependencies
├── mysql/init/                # Database Schema
└── nginx/conf.d/             # nginx Configuration
```

## 🔑 API Endpoints

### Authentifizierung
- `POST /v1/auth/first-login` - PIN setzen
- `POST /v1/auth/login` - Anmeldung
- `POST /v1/auth/reset-pin-request` - PIN Reset
- `GET /v1/auth/validate` - Token validieren

### Benutzer
- `GET /v1/me` - Eigenes Profil
- `PATCH /v1/me/threshold` - Schwellenwert ändern
- `PATCH /v1/me/pin` - PIN ändern

### Produkte
- `GET /v1/products` - Produkte abrufen
- `GET /v1/me/favorites` - Favoriten abrufen
- `POST /v1/me/favorites` - Favorit hinzufügen

### Buchungen
- `POST /v1/bookings` - Buchung erstellen
- `GET /v1/me/bookings` - Eigene Buchungen
- `GET /v1/bookings` - Alle Buchungen (Admin)
- `POST /v1/bookings/{id}/void` - Buchung stornieren (Admin)

### Admin
- `GET /v1/admin/users` - Benutzer verwalten
- `POST /v1/admin/users` - Benutzer erstellen
- `POST /v1/admin/users/{id}/deposit` - Guthaben einzahlen

### Statistiken
- `GET /v1/stats/top-products` - Top Produkte
- `GET /v1/stats/revenue` - Umsätze
- `GET /v1/stats/categories` - Kategorie-Breakdown

## 🧪 Testing

### Demo-Daten
Nach dem ersten Start sind folgende Test-Benutzer verfügbar:
- **Admin:** `admin@bcmarl.de`, Mobile: `01234567890`
- **User:** `anna@example.com`, Mobile: `01111111111`

### API Testen
```bash
# Health Check
curl http://localhost:8080/v1/health

# Login (PIN beim ersten Mal setzen)
curl -X POST http://localhost:8080/v1/auth/first-login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@bcmarl.de","mobile":"01234567890","pin":"1234"}'

# Produkte abrufen
curl http://localhost:8080/v1/products
```

## 🐳 Docker Services

### api (PHP 8.2-FPM)
- **Port:** 8080
- **Framework:** Slim 4
- **Features:** JWT Auth, CORS, Logging

### mysql (MySQL 8.0)
- **Port:** 3306
- **Database:** bcmarl_drinks
- **User:** bcmarl_user
- **Auto-Schema:** Beim Start initialisiert

### phpmyadmin
- **Port:** 8081
- **Zugang:** bcmarl_user / bcmarl_pass_2025

### nginx (Reverse Proxy)
- **Port:** 80
- **Features:** Gzip, Security Headers

## 🔧 Entwicklung

### Commands
```bash
# Container neu starten
docker-compose restart

# Logs anzeigen
docker-compose logs -f api

# In PHP Container einloggen
docker-compose exec api bash

# Composer Dependencies aktualisieren
docker-compose exec api composer update

# Datenbank zurücksetzen
docker-compose down -v && docker-compose up -d
```

### Database Zugang
```bash
# MySQL CLI
docker-compose exec mysql mysql -u bcmarl_user -p bcmarl_drinks
```

## 🚀 Production Deployment

### Environment Variables
Wichtige Variablen für Production:
- `API_ENV=production`
- `JWT_SECRET=sehr_sicherer_schluessel`
- `DB_*` Konfiguration für Production-DB
- `CORS_ORIGIN=https://yourdomain.com`

### Security Checklist
- [ ] JWT_SECRET ändern
- [ ] Database Credentials ändern
- [ ] CORS_ORIGIN auf spezifische Domain setzen
- [ ] HTTPS einrichten
- [ ] Rate Limiting aktivieren
- [ ] Backup-Strategie implementieren

## 📋 iOS App Integration

Die API ist vollständig kompatibel mit der iOS App:
- Gleiche Datenmodelle
- Identische API-Spezifikation  
- JWT-Token Authentifizierung
- CORS für mobile Apps konfiguriert



