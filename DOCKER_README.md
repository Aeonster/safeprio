# SafePrio - Docker Setup

Detta projekt använder Docker och Docker Compose för att köra en lokal utvecklingsmiljö med PHP, Apache och MySQL.

## Förutsättningar

- [Docker Desktop](https://www.docker.com/products/docker-desktop) installerat
- VSCode med Docker extension (rekommenderat)

## Installation & Start

### 1. Starta miljön

Öppna terminal i VSCode och kör:

```bash
docker-compose up -d
```

Första gången tar det några minuter då Docker bygger containrarna och laddar ner nödvändiga images.

### 2. Uppdatera databaskonfiguration

Öppna `includes/config.php` och uppdatera databasinställningarna:

```php
define('DB_HOST', 'mysql');  // Ändra från 'localhost' till 'mysql'
define('DB_NAME', 'varsel_db');
define('DB_USER', 'root');
define('DB_PASS', 'root_password');  // Ändra från '' till 'root_password'
```

### 3. Öppna webbplatsen

- **Webbplats**: http://localhost:8000
- **phpMyAdmin**: http://localhost:8080
  - Användare: `root`
  - Lösenord: `root_password`

## Användning

### Stoppa miljön
```bash
docker-compose stop
```

### Starta igen (snabbt)
```bash
docker-compose start
```

### Stäng ner och ta bort containrar
```bash
docker-compose down
```

### Se loggar
```bash
docker-compose logs -f
```

### Starta om efter ändringar
```bash
docker-compose restart
```

## Portar

- **8000** - Apache webbserver
- **3307** - MySQL databas (istället för 3306 för att undvika konflikter)
- **8080** - phpMyAdmin

## Databas

Databasen initieras automatiskt med SQL-filerna:
- `database.sql` - Grundstruktur
- `database_language_update.sql` - Språkuppdateringar

Data sparas i en Docker volume som bevaras även när containrarna tas bort.

## Felsökning

### MySQL anslutningsproblem
Kontrollera att `DB_HOST` är satt till `mysql` (inte `localhost`) i config.php.

### Port redan används
Om port 8000, 8080 eller 3307 redan används, ändra portarna i `docker-compose.yml`:
```yaml
ports:
  - "8001:80"  # Ändra 8000 till 8001 (eller annan ledig port)
```

### Bygga om containrar
```bash
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Ta bort allt (inkl. databas)
```bash
docker-compose down -v
```

## Fördelar med Docker

- ✅ Ingen installation av Apache/MySQL på Windows
- ✅ Samma miljö på alla datorer
- ✅ Inga konflikter med andra projekt
- ✅ Enkel start/stopp från VSCode terminal
- ✅ Databas återställs automatiskt
- ✅ phpMyAdmin inkluderat för databashantering

## Tips

- Använd VSCode Docker extension för att se containrar och loggar visuellt
- Alla filer synkas automatiskt - inga manuella kopiering behövs
- Stäng inte av Docker Desktop när du utvecklar
