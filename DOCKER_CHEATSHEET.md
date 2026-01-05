# Docker Cheat Sheet - SafePrio

## Starta & Stoppa

```bash
# Starta servern
docker-compose up -d

# Stoppa servern (behåller data)
docker-compose stop

# Starta igen (snabbt)
docker-compose start

# Starta om allt
docker-compose restart

# Stäng ner (tar bort containers)
docker-compose down

# Stäng ner + radera databas
docker-compose down -v
```

## Status & Loggar

```bash
# Se alla containers
docker-compose ps

# Se loggar (alla services)
docker-compose logs

# Se loggar (specifik service)
docker-compose logs web
docker-compose logs mysql

# Följ loggar live
docker-compose logs -f
```

## Databas - Export & Import

### Exportera databas
```bash
# Exportera till fil (struktur + all data - STANDARD)
docker exec safeprio_mysql mysqldump -u root -proot_password safeprio_db > backup_safeprio.sql

# Exportera med datum (struktur + all data)
docker exec safeprio_mysql mysqldump -u root -proot_password safeprio_db > backup_$(Get-Date -Format "yyyyMMdd_HHmmss").sql

# Bara struktur (utan data)
docker exec safeprio_mysql mysqldump -u root -proot_password --no-data safeprio_db > backup_structure_only.sql

# Bara data (utan struktur)
docker exec safeprio_mysql mysqldump -u root -proot_password --no-create-info safeprio_db > backup_data_only.sql
```

### Importera databas
```bash
# Importera från fil
docker exec -i safeprio_mysql mysql -u root -proot_password safeprio_db < backup_safeprio.sql

# Alternativ: Via phpMyAdmin
# 1. Öppna http://localhost:8080
# 2. Välj safeprio_db
# 3. Gå till "Import"
# 4. Välj din .sql fil
```

## Bygga Om

```bash
# Bygga om efter ändringar i Dockerfile
docker-compose build

# Bygga om utan cache (ren build)
docker-compose build --no-cache

# Bygga om + starta
docker-compose up -d --build
```

## Rensa & Fixa Problem

```bash
# Radera allt (containers + volumes)
docker-compose down -v

# Radera allt + bygga om från scratch
docker-compose down -v
docker-compose build --no-cache
docker-compose up -d

# Rensa gamla Docker images (frigör utrymme)
docker system prune -a
```

## Köra Kommandon i Containers

```bash
# Öppna MySQL shell
docker exec -it safeprio_mysql mysql -u root -proot_password safeprio_db

# Öppna bash i web-container
docker exec -it safeprio_web bash

# Kolla PHP version
docker exec safeprio_web php -v

# Lista filer i container
docker exec safeprio_web ls -la /var/www/html
```

## Access Points

- **Webbplats**: http://localhost:8000
- **phpMyAdmin**: http://localhost:8080
  - User: `root`
  - Pass: `root_password`
- **MySQL Port**: 3307 (från host)

## Vanliga Problem

### Port redan används
Ändra port i docker-compose.yml:
```yaml
ports:
  - "8001:80"  # Ändra från 8000 till 8001
```

### Databasen visar fel data
```bash
docker-compose down -v
docker-compose up -d
```

### UTF-8 problem (åäö)
Se till att SQL-filerna har `SET NAMES utf8mb4;` i början.

### Container startar inte
```bash
docker-compose logs [service-name]
```
