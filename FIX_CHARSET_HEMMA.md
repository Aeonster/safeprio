# 🔧 FIX CHARSET PROBLEM - KÖR DETTA HEMMA

**Skapad:** 2026-01-08  
**Problem:** Databasen på jobbdatorn har fel encoding - alla åäö visas som ??  
**Orsak:** Backup-filen `backup_20260105_183450.sql` skapades med fel charset (dubbel-encoding problem)

---

## 🎯 MÅLET

Skapa en NY backup från hemma-miljön med KORREKT UTF-8 encoding så den kan importeras på jobbet utan charset-problem.

---

## ✅ STEG ATT GÖRA (HEMMA)

### Steg 1: Verifiera att hemma-miljön har korrekt data

Kör detta för att kontrollera att åäö ser rätt ut:

```bash
docker exec safeprio_mysql mysql -uroot -proot_password -e "SELECT designation_sv FROM product_groups LIMIT 3;" safeprio_db
```

**Förväntat resultat:**  
Du ska se ord som "Varselm**ä**rkning" med korrekta åäö (inte ??)

---

### Steg 2: Skapa KORREKT backup med UTF-8

```bash
docker exec safeprio_mysql mysqldump \
  -uroot \
  -proot_password \
  --default-character-set=utf8mb4 \
  --result-file=/tmp/safeprio_backup_CORRECT_UTF8.sql \
  safeprio_db
```

### Steg 3: Kopiera backup-filen ut från containern

```bash
docker cp safeprio_mysql:/tmp/safeprio_backup_CORRECT_UTF8.sql ./safeprio_backup_CORRECT_UTF8.sql
```

### Steg 4: Verifiera att nya filen har korrekt encoding

```bash
grep -a "Varselm" safeprio_backup_CORRECT_UTF8.sql | head -1
```

**Du ska se:**  
- `Valselmärkning` med korrekta svenska tecken
- **INTE** `Varselm├ñrkning` eller `Varselm??rkning`

---

### Steg 5: Synka till OneDrive

Flytta filen till OneDrive-mappen så den synkar till jobbdatorn:

```bash
cp safeprio_backup_CORRECT_UTF8.sql /path/to/OneDrive/safeprio/
```

Eller för Windows PowerShell:
```powershell
Copy-Item safeprio_backup_CORRECT_UTF8.sql "C:\Users\[DITT_NAMN]\OneDrive - Kortsystem i Gislaved AB\Localhost\safeprio\"
```

---

## 💻 SEDAN PÅ JOBBET (Efter att filen synkat)

### Steg 1: Ta bort gammal databas

```powershell
cd "C:\Users\Patricio Santiago\OneDrive - Kortsystem i Gislaved AB\Localhost\safeprio"
docker-compose down -v
docker-compose up -d
Start-Sleep -Seconds 10
```

### Steg 2: Importera den nya korrekta backupen

```powershell
Get-Content safeprio_backup_CORRECT_UTF8.sql | docker exec -i safeprio_mysql mysql -uroot -proot_password safeprio_db
```

### Steg 3: Verifiera att åäö nu är korrekta

```powershell
docker exec safeprio_mysql mysql -uroot -proot_password -e "SELECT designation_sv FROM product_groups LIMIT 3;" safeprio_db
```

**Du ska nu se:**
```
Valselmärkning 150x210 mm, vinyletikett, påbud, enl.spec.
Valselmärkning 150x210 mm, vinyletikett, varning, enl.spec.
Valselmärkning 150x210 mm, vinyletikett, förbud, enl.spec.
```

---

## 🔍 TEKNISK FÖRKLARING (för AI/utvecklare)

### Vad gick fel?

1. Original backup (`backup_20260105_183450.sql`) innehåller redan korrupt data
2. Svenska tecken (åäö) är dubbel-encodade:
   - `å` → `├Ñ` 
   - `ä` → `├ñ`
   - `ö` → `├Â`
3. Detta sker när UTF-8 data tolkas som Latin-1 och sedan sparas som UTF-8 igen

### Varför kan vi inte fixa automatiskt?

- Data är redan korrupt i SQL-filen
- Automatiska konverteringar kan inte skilja på faktiska tecken och korrupta bytes
- Risk för att förstöra data ytterligare

### Lösning

Skapa ny backup från källan (hemma-miljön) där data är korrekt

### Docker och MySQL charset-konfiguration

Docker-compose är redan korrekt konfigurerad:
```yaml
mysql:
  command: --character-set-server=utf8mb4 --collation-server=utf8mb4_unicode_ci
```

PHP PDO är också korrekt (i `includes/config.php`):
```php
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
$pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
```

Problemet är ENDAST att backup-filen innehåller korrupt data.

---

## 📝 CHECKLIST

- [ ] Hemma: Verifiera att data är korrekt
- [ ] Hemma: Skapa ny backup med `mysqldump --default-character-set=utf8mb4`
- [ ] Hemma: Kopiera backup från container
- [ ] Hemma: Verifiera encoding i backupen
- [ ] Hemma: Synka till OneDrive
- [ ] Jobbet: Radera gammal databas (`docker-compose down -v`)
- [ ] Jobbet: Importera ny backup
- [ ] Jobbet: Verifiera att åäö visas korrekt

---

## 🎉 KLART!

När du har gjort allt ovan kommer alla svenska tecken att visas korrekt i hela applikationen.

**Tips:** Spara gamla backupen för säkerhets skull innan du kör `down -v` på jobbet!
