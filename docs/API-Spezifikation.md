# BC Marl Drinks - API Spezifikation

**Version:** 1.0  
**Datum:** 2025-08-31  
**Für:** Backend-Entwicklung  

---

## 1. Überblick

Diese API-Spezifikation definiert die REST-Schnittstelle für die BC Marl Drinks Vereins-Snack-App. Die API verbindet die iOS-App mit dem Backend-System und ermöglicht Benutzerauthentifizierung, Produktverwaltung, Buchungen und Transaktionen.

### 1.1 Basis-URL
```
https://api.bcmarl-drinks.de/v1
```

### 1.2 Authentifizierung
- **Typ:** Bearer Token (JWT)
- **Header:** `Authorization: Bearer <token>`
- **Token-Lebensdauer:** 24 Stunden
- **Refresh-Mechanismus:** Automatische Verlängerung bei aktiver Nutzung

---

## 2. Datenmodelle

### 2.1 User
```json
{
  "id": "string (uuid)",
  "firstName": "string",
  "lastName": "string", 
  "email": "string (email)",
  "mobile": "string",
  "role": "user|admin",
  "balanceCents": "integer",
  "lowBalanceThresholdCents": "integer",
  "locked": "boolean",
  "failedLoginAttempts": "integer",
  "createdAt": "string (iso8601)",
  "updatedAt": "string (iso8601)"
}
```

### 2.2 Product
```json
{
  "id": "string (uuid)",
  "name": "string",
  "icon": "string (emoji)",
  "priceCents": "integer",
  "category": "DRINKS|SNACKS|ACCESSORIES|MEMBERSHIP",
  "active": "boolean",
  "createdAt": "string (iso8601)",
  "updatedAt": "string (iso8601)"
}
```

### 2.3 Booking
```json
{
  "id": "string (uuid)",
  "userId": "string (uuid)",
  "productId": "string (uuid)",
  "quantity": "integer",
  "unitPriceCents": "integer",
  "totalCents": "integer",
  "timestamp": "string (iso8601)",
  "status": "booked|voided",
  "voidedByAdminId": "string (uuid, optional)",
  "voidedAt": "string (iso8601, optional)",
  "originalBookingId": "string (uuid, optional)"
}
```

### 2.4 Transaction
```json
{
  "id": "string (uuid)",
  "userId": "string (uuid)",
  "type": "DEPOSIT|DEBIT|REVERSAL",
  "amountCents": "integer",
  "reference": "string (optional)",
  "timestamp": "string (iso8601)",
  "enteredByAdminId": "string (uuid, optional)"
}
```

### 2.5 Favorite
```json
{
  "userId": "string (uuid)",
  "productId": "string (uuid)",
  "createdAt": "string (iso8601)"
}
```

---

## 3. Authentifizierung Endpunkte

### 3.1 Erstanmeldung
```http
POST /auth/first-login
Content-Type: application/json

{
  "email": "string",
  "mobile": "string",
  "pin": "string (4 digits)"
}
```

**Response 200:**
```json
{
  "success": true,
  "token": "string (jwt)",
  "user": "User Object",
  "message": "PIN erfolgreich gesetzt"
}
```

**Response 400:**
```json
{
  "success": false,
  "error": "INVALID_CREDENTIALS|USER_NOT_FOUND|INVALID_PIN_FORMAT",
  "message": "string"
}
```

### 3.2 Login
```http
POST /auth/login
Content-Type: application/json

{
  "email": "string",
  "mobile": "string", 
  "pin": "string (4 digits)"
}
```

**Response 200:**
```json
{
  "success": true,
  "token": "string (jwt)",
  "user": "User Object"
}
```

**Response 401:**
```json
{
  "success": false,
  "error": "INVALID_CREDENTIALS|ACCOUNT_LOCKED|TOO_MANY_ATTEMPTS",
  "message": "string",
  "attemptsRemaining": "integer (optional)"
}
```

### 3.3 PIN zurücksetzen (Anfrage)
```http
POST /auth/reset-pin-request
Content-Type: application/json

{
  "email": "string",
  "mobile": "string"
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Reset-E-Mail versendet"
}
```

### 3.4 PIN zurücksetzen (Bestätigung)
```http
POST /auth/reset-pin-confirm
Content-Type: application/json

{
  "token": "string (reset-token)",
  "newPin": "string (4 digits)"
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "PIN erfolgreich zurückgesetzt"
}
```

### 3.5 Token validieren
```http
GET /auth/validate
Authorization: Bearer <token>
```

**Response 200:**
```json
{
  "valid": true,
  "user": "User Object"
}
```

---

## 4. Benutzer Endpunkte

### 4.1 Eigenes Profil abrufen
```http
GET /me
Authorization: Bearer <token>
```

**Response 200:**
```json
{
  "success": true,
  "user": "User Object"
}
```

### 4.2 Schwellenwert aktualisieren
```http
PATCH /me/threshold
Authorization: Bearer <token>
Content-Type: application/json

{
  "lowBalanceThresholdCents": "integer"
}
```

**Response 200:**
```json
{
  "success": true,
  "user": "User Object"
}
```

### 4.3 PIN ändern
```http
PATCH /me/pin
Authorization: Bearer <token>
Content-Type: application/json

{
  "currentPin": "string (4 digits)",
  "newPin": "string (4 digits)"
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "PIN erfolgreich geändert"
}
```

---

## 5. Produkt Endpunkte

### 5.1 Produkte abrufen
```http
GET /products?category=DRINKS&active=true
Authorization: Bearer <token>
```

**Query Parameter:**
- `category` (optional): DRINKS|SNACKS|ACCESSORIES|MEMBERSHIP
- `active` (optional): true|false (default: true)

**Response 200:**
```json
{
  "success": true,
  "products": ["Product Object", "..."],
  "total": "integer"
}
```

### 5.2 Einzelnes Produkt abrufen
```http
GET /products/{productId}
Authorization: Bearer <token>
```

**Response 200:**
```json
{
  "success": true,
  "product": "Product Object"
}
```

---

## 6. Favoriten Endpunkte

### 6.1 Favoriten abrufen
```http
GET /me/favorites
Authorization: Bearer <token>
```

**Response 200:**
```json
{
  "success": true,
  "products": ["Product Object", "..."]
}
```

### 6.2 Favorit hinzufügen
```http
POST /me/favorites
Authorization: Bearer <token>
Content-Type: application/json

{
  "productId": "string (uuid)"
}
```

**Response 201:**
```json
{
  "success": true,
  "message": "Favorit hinzugefügt"
}
```

### 6.3 Favorit entfernen
```http
DELETE /me/favorites/{productId}
Authorization: Bearer <token>
```

**Response 200:**
```json
{
  "success": true,
  "message": "Favorit entfernt"
}
```

---

## 7. Buchungs Endpunkte

### 7.1 Buchung erstellen
```http
POST /bookings
Authorization: Bearer <token>
Content-Type: application/json

{
  "productId": "string (uuid)",
  "quantity": "integer (min: 1)"
}
```

**Response 201:**
```json
{
  "success": true,
  "booking": "Booking Object",
  "transaction": "Transaction Object",
  "newBalance": "integer",
  "balanceBelowThreshold": "boolean"
}
```

**Response 400:**
```json
{
  "success": false,
  "error": "PRODUCT_NOT_FOUND|PRODUCT_INACTIVE|INVALID_QUANTITY",
  "message": "string"
}
```

### 7.2 Buchungen abrufen (User)
```http
GET /me/bookings?from=2025-08-01&to=2025-08-31&includeVoided=false
Authorization: Bearer <token>
```

**Query Parameter:**
- `from` (optional): ISO8601 Datum
- `to` (optional): ISO8601 Datum  
- `includeVoided` (optional): boolean (default: false)

**Response 200:**
```json
{
  "success": true,
  "bookings": ["Booking Object", "..."],
  "total": "integer"
}
```

### 7.3 Alle Buchungen abrufen (Admin)
```http
GET /bookings?userId=uuid&category=DRINKS&from=2025-08-01
Authorization: Bearer <token> (Admin erforderlich)
```

**Query Parameter:**
- `userId` (optional): string (uuid)
- `category` (optional): DRINKS|SNACKS|ACCESSORIES|MEMBERSHIP
- `from` (optional): ISO8601 Datum
- `to` (optional): ISO8601 Datum
- `includeVoided` (optional): boolean (default: false)

**Response 200:**
```json
{
  "success": true,
  "bookings": ["Booking Object", "..."],
  "total": "integer"
}
```

### 7.4 Buchung stornieren (Admin)
```http
POST /bookings/{bookingId}/void
Authorization: Bearer <token> (Admin erforderlich)
Content-Type: application/json

{
  "reason": "string (optional)"
}
```

**Response 200:**
```json
{
  "success": true,
  "booking": "Booking Object",
  "reversalTransaction": "Transaction Object",
  "newBalance": "integer"
}
```

---

## 8. Transaktions Endpunkte

### 8.1 Eigene Transaktionen abrufen
```http
GET /me/transactions?from=2025-08-01&to=2025-08-31&type=DEPOSIT
Authorization: Bearer <token>
```

**Query Parameter:**
- `from` (optional): ISO8601 Datum
- `to` (optional): ISO8601 Datum
- `type` (optional): DEPOSIT|DEBIT|REVERSAL

**Response 200:**
```json
{
  "success": true,
  "transactions": ["Transaction Object", "..."],
  "total": "integer"
}
```

### 8.2 Alle Transaktionen abrufen (Admin)
```http
GET /transactions?userId=uuid
Authorization: Bearer <token> (Admin erforderlich)
```

**Response 200:**
```json
{
  "success": true,
  "transactions": ["Transaction Object", "..."],
  "total": "integer"
}
```

---

## 9. Admin Endpunkte

### 9.1 Benutzer verwalten

#### 9.1.1 Alle Benutzer abrufen
```http
GET /admin/users?search=anna&locked=false
Authorization: Bearer <token> (Admin erforderlich)
```

**Response 200:**
```json
{
  "success": true,
  "users": ["User Object", "..."],
  "total": "integer"
}
```

#### 9.1.2 Benutzer erstellen
```http
POST /admin/users
Authorization: Bearer <token> (Admin erforderlich)
Content-Type: application/json

{
  "firstName": "string",
  "lastName": "string",
  "email": "string (email)",
  "mobile": "string",
  "role": "user|admin"
}
```

**Response 201:**
```json
{
  "success": true,
  "user": "User Object"
}
```

#### 9.1.3 Benutzer entsperren
```http
POST /admin/users/{userId}/unlock
Authorization: Bearer <token> (Admin erforderlich)
```

**Response 200:**
```json
{
  "success": true,
  "user": "User Object"
}
```

#### 9.1.4 Guthaben einzahlen
```http
POST /admin/users/{userId}/deposit
Authorization: Bearer <token> (Admin erforderlich)
Content-Type: application/json

{
  "amountCents": "integer",
  "note": "string (optional)"
}
```

**Response 201:**
```json
{
  "success": true,
  "transaction": "Transaction Object",
  "newBalance": "integer"
}
```

### 9.2 Produkte verwalten

#### 9.2.1 Produkt erstellen
```http
POST /admin/products
Authorization: Bearer <token> (Admin erforderlich)
Content-Type: application/json

{
  "name": "string",
  "icon": "string (emoji)",
  "priceCents": "integer",
  "category": "DRINKS|SNACKS|ACCESSORIES|MEMBERSHIP"
}
```

**Response 201:**
```json
{
  "success": true,
  "product": "Product Object"
}
```

#### 9.2.2 Produkt aktualisieren
```http
PATCH /admin/products/{productId}
Authorization: Bearer <token> (Admin erforderlich)
Content-Type: application/json

{
  "name": "string (optional)",
  "priceCents": "integer (optional)",
  "active": "boolean (optional)"
}
```

**Response 200:**
```json
{
  "success": true,
  "product": "Product Object"
}
```

---

## 10. Statistik Endpunkte

### 10.1 Top Produkte
```http
GET /stats/top-products?from=2025-08-01&to=2025-08-31&limit=10
Authorization: Bearer <token> (Admin erforderlich)
```

**Response 200:**
```json
{
  "success": true,
  "topProducts": [
    {
      "product": "Product Object",
      "quantitySold": "integer",
      "totalRevenueCents": "integer"
    }
  ]
}
```

### 10.2 Umsatz-Statistiken
```http
GET /stats/revenue?from=2025-08-01&to=2025-08-31&groupBy=day
Authorization: Bearer <token> (Admin erforderlich)
```

**Response 200:**
```json
{
  "success": true,
  "revenue": [
    {
      "date": "string (iso8601)",
      "revenueCents": "integer",
      "bookingCount": "integer"
    }
  ],
  "totalRevenueCents": "integer"
}
```

### 10.3 Kategorie-Breakdown
```http
GET /stats/categories?from=2025-08-01&to=2025-08-31
Authorization: Bearer <token> (Admin erforderlich)
```

**Response 200:**
```json
{
  "success": true,
  "categories": [
    {
      "category": "DRINKS",
      "quantitySold": "integer",
      "revenueCents": "integer",
      "percentage": "float"
    }
  ]
}
```

---

## 11. Export Endpunkte

### 11.1 Buchungen exportieren
```http
GET /export/bookings.csv?from=2025-08-01&to=2025-08-31&userId=uuid
Authorization: Bearer <token> (Admin erforderlich)
```

**Response 200:**
```
Content-Type: text/csv
Content-Disposition: attachment; filename="bookings_2025-08.csv"

timestamp,userId,userName,productId,productName,category,quantity,unitPriceCents,totalCents,bookingId,status,voidedByAdminId
...
```

### 11.2 Transaktionen exportieren
```http
GET /export/transactions.csv?from=2025-08-01&to=2025-08-31
Authorization: Bearer <token> (Admin erforderlich)
```

**Response 200:**
```
Content-Type: text/csv
Content-Disposition: attachment; filename="transactions_2025-08.csv"

timestamp,userId,type,amountCents,referenceId,enteredByAdminId
...
```

---

## 12. Fehlerbehandlung

### 12.1 Standard Fehlercodes
- `400` - Bad Request (ungültige Parameter)
- `401` - Unauthorized (fehlendes/ungültiges Token)
- `403` - Forbidden (unzureichende Berechtigung)  
- `404` - Not Found (Ressource nicht gefunden)
- `409` - Conflict (Datenkonflikt)
- `422` - Unprocessable Entity (Validierungsfehler)
- `429` - Too Many Requests (Rate Limiting)
- `500` - Internal Server Error

### 12.2 Fehler Format
```json
{
  "success": false,
  "error": "ERROR_CODE",
  "message": "Benutzerfreundliche Fehlermeldung",
  "details": {
    "field": "Spezifische Validierungsfehler"
  }
}
```

---

## 13. Rate Limiting

### 13.1 Limits
- **Login-Versuche:** 5 pro Minute pro E-Mail
- **API-Aufrufe:** 1000 pro Stunde pro Benutzer
- **Buchungen:** 10 pro Minute pro Benutzer

### 13.2 Headers
```http
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1630512000
```

---

## 14. Versionierung

### 14.1 URL-Versioning
- Aktuelle Version: `v1`
- Format: `/v{version}`
- Rückwärtskompatibilität für mindestens 12 Monate

### 14.2 Breaking Changes
- Neue Major-Version bei Breaking Changes
- Deprecation-Warnings 3 Monate vor Änderung
- Migration-Guides werden bereitgestellt

---

## 15. Sicherheit

### 15.1 HTTPS
- Alle Endpunkte erfordern HTTPS
- TLS 1.2 minimum

### 15.2 PIN-Sicherheit
- PINs werden mit bcrypt gehasht (min. 12 rounds)
- Salt pro PIN
- Niemals im Klartext übertragen oder gespeichert

### 15.3 JWT Tokens
- HS256 Signierung minimum
- Kurze Lebensdauer (24h)
- Enthält: userId, role, iat, exp

### 15.4 Input Validation
- Alle Eingaben werden serverseitig validiert
- SQL Injection Schutz
- XSS Schutz in JSON Responses

---

## 16. Monitoring & Logging

### 16.1 Logging
- Alle API-Aufrufe loggen
- Fehler mit Stack Traces
- Performance-Metriken
- KEINE sensitiven Daten (PINs, Tokens)

### 16.2 Monitoring
- Health Check: `GET /health`
- Metriken: `GET /metrics` (Admin only)
- Response Times < 200ms (95. Perzentil)
- Uptime > 99.9%

---

Diese API-Spezifikation bildet die Grundlage für die Backend-Entwicklung der BC Marl Drinks App und ist vollständig kompatibel mit der implementierten iOS-App.
