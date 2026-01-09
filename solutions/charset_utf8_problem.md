# Charset/UTF-8 Problem - Svenska tecken (åäö) visas som ??

**Datum för lösning:** 2026-01-09  
**Problem ID:** charset-utf8-import  
**Status:** ✅ Löst

---

## 📋 Problemets Symptom

- Svenska tecken (å, ä, ö) visas som `??` på webbsidan
- I databasen lagras tecknen som `?` istället för korrekta svenska tecken
- Både frontend och phpMyAdmin visar felaktiga tecken
- Hex-kontroll visar `3F3F` (= två frågetecken) istället för UTF-8 bytes

---

## 🔍 Grundorsak

**PowerShell förstör UTF-8 encoding vid piping till Docker**

När man använder:
```powershell
Get-Content backup.sql | docker exec -i container mysql ...
```

Så konverterar PowerShell automatiskt innehållet till sin egen encoding, vilket förstör UTF-8 tecken innan de når MySQL-containern.

Detta händer **ÄVEN** om:
- SQL-filen är korrekt UTF-8 kodad
- MySQL är konfigurerad med `utf8mb4`
- Docker-compose har rätt charset-inställningar
- PHP använder `charset=utf8mb4` i PDO

---

## ✅ Lösning (Steg-för-Steg)

### Steg 1: Ta bort gammal databas
```powershell
docker-compose down -v
docker-compose up -d
Start-Sleep -Seconds 10
```

### Steg 2: Kopiera SQL-filen DIREKT till Docker-containern
```powershell
docker cp safeprio_backup_CORRECT_UTF8.sql safeprio_mysql:/tmp/backup.sql
```

**VIKTIGT:** Använd `docker cp` istället för `Get-Content | docker exec`

### Steg 3: Importera FRÅN containern
```powershell
docker exec safeprio_mysql mysql -uroot -proot_password --default-character-set=utf8mb4 safeprio_db -e "source /tmp/backup.sql"
```

### Steg 4: Verifiera
```powershell
docker exec safeprio_mysql mysql -uroot -proot_password --default-character-set=utf8mb4 -e "SELECT designation_sv FROM product_groups LIMIT 3;" safeprio_db
```

Du ska nu se korrekta svenska tecken i terminalen.

---

## 🚫 FEL METOD (Fungerar INTE)

```powershell
# ANVÄND INTE DENNA METOD - den förstör encoding!
Get-Content backup.sql | docker exec -i safeprio_mysql mysql -uroot -proot_password safeprio_db
```

Detta fungerar inte eftersom PowerShell konverterar encoding under piping.

---

## 🔧 Teknisk Förklaring

### Varför händer detta?

1. **PowerShell's encoding-problem:**
   - PowerShell använder UTF-16 internt
   - Vid piping konverteras data genom PowerShell's encoding-lager
   - UTF-8 bytes tolkas felaktigt och konverteras till `?` för okända tecken

2. **MySQL tar emot korrupt data:**
   - När data når MySQL är UTF-8 tecken redan förstörda
   - MySQL lagrar `?` tecken (hex: `3F`) istället för korrekta bytes
   - Data är permanent korrupt i databasen

### Varför fungerar docker cp?

- `docker cp` kopierar filen på byte-nivå utan encoding-konvertering
- Filen behåller sin ursprungliga UTF-8 encoding
- MySQL kan sedan läsa filen direkt med korrekt charset

---

## 🎯 Sammanfattning

**Problem:**  
PowerShell förstör UTF-8 encoding vid piping till Docker

**Lösning:**  
Använd alltid `docker cp` för att kopiera SQL-filer till containern innan import

**Kommando:**
```powershell
docker cp filnamn.sql container:/tmp/temp.sql
docker exec container mysql -uroot -ppassword --default-character-set=utf8mb4 databas -e "source /tmp/temp.sql"
```

---

## 📝 Relaterade Filer

- `FIX_CHARSET_HEMMA.md` - Instruktioner för att skapa korrekt backup från hemma-miljön
- `docker-compose.yml` - Innehåller MySQL charset-konfiguration
- `includes/config.php` - PHP PDO charset-inställningar

---

## 🔄 När använder man denna lösning?

- Vid import av SQL-backuper med svenska tecken
- När man flyttar databas mellan miljöer (hemma ↔ jobb ↔ produktion)
- Efter `docker-compose down -v` när man behöver återställa data
- Vid deployment till webbserver

---

## ⚠️ Viktiga Påminnelser

1. **Verifiera alltid source-filen först:**
   ```powershell
   [System.IO.File]::ReadAllText("$PWD\backup.sql", [System.Text.Encoding]::UTF8) -split "`n" | Select-String "Varselm" | Select-Object -First 1
   ```
   - Om du ser korrekta åäö här är source-filen OK
   - Om du ser `├ñ` eller liknande är source-filen redan korrupt

2. **Använd ALLTID `--default-character-set=utf8mb4`** vid import

3. **Testa på en testrad efter import:**
   ```powershell
   docker exec safeprio_mysql mysql -uroot -proot_password --default-character-set=utf8mb4 -e "SELECT designation_sv FROM product_groups LIMIT 1;" safeprio_db
   ```

---

## 🆘 Om problemet kvarstår

Om svenska tecken fortfarande visar fel EFTER korrekt import:

### Kolla MySQL charset-variabler:
```powershell
docker exec safeprio_mysql mysql -uroot -proot_password -e "SHOW VARIABLES LIKE 'char%';" safeprio_db
```

Ska visa:
- `character_set_server: utf8mb4`
- `character_set_database: utf8mb4`

### Kolla hex-data:
```powershell
docker exec safeprio_mysql mysql -uroot -proot_password -e "SELECT HEX(SUBSTRING(designation_sv, 1, 20)) FROM product_groups LIMIT 1;" safeprio_db
```

Ska INTE innehålla `3F3F` (= ??)

### Kolla PHP PDO connection i includes/config.php:
```php
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
$pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
```

---

**Lycka till! 🚀**
