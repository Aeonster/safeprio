# 🔧 FIX CHARSET PROBLEM - KÖR DETTA PÅ JOBBET

**Skapad:** 2026-01-08  
**Problem:** Databasen på jobbdatorn har fel encoding - alla åäö visas som ??  
**Lösning:** Importera korrekt UTF-8 backup som skapades hemma

---

## ⚠️ INNAN DU BÖRJAR

**VIKTIGT:** Denna fil använder du **EFTER** att du har skapat en korrekt backup hemma enligt instruktionerna i `FIX_CHARSET_HEMMA.md`.

**Säkerställ att:**
- ✅ Filen `safeprio_backup_CORRECT_UTF8.sql` finns i denna mapp
- ✅ Filen har synkats via OneDrive från hemma-datorn
- ✅ Du har verifierat att backupen innehåller korrekta åäö (inte ??)

---

## 🎯 MÅLET

Radera den gamla databasen med korrupt charset och importera den nya korrekta backupen från hemma.

---

## ✅ STEG ATT GÖRA (PÅ JOBBET)

### Steg 1: Gå till projektmappen

```powershell
cd "C:\Users\Patricio Santiago\OneDrive - Kortsystem i Gislaved AB\Localhost\safeprio"
```

---

### Steg 2: Verifiera att backup-filen finns

```powershell
Test-Path safeprio_backup_CORRECT_UTF8.sql
```

**Förväntat resultat:** `True`

Om du får `False` - vänta tills OneDrive har synkat filen från hemma!

---

### Steg 3: (VALFRITT) Skapa säkerhetskopia av nuvarande databas

Om du vill spara den gamla databasen innan du raderar den:

```powershell
docker exec safeprio_mysql mysqldump -uroot -proot_password safeprio_db > backup_BEFORE_FIX_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql
```

---

### Steg 4: Stoppa och radera gammal databas

**⚠️ VARNING:** Detta raderar all data i databasen!

```powershell
docker-compose down -v
```

**Vad gör `-v` flaggan?**  
Raderar alla volumes (inklusive databasen), så vi får en helt ren start.

---

### Steg 5: Starta om containrarna

```powershell
docker-compose up -d
```

---

### Steg 6: Vänta på att MySQL startar

```powershell
Start-Sleep -Seconds 15
```

MySQL behöver några sekunder för att initialisera databasen.

---

### Steg 7: Importera den korrekta backupen

```powershell
Get-Content safeprio_backup_CORRECT_UTF8.sql | docker exec -i safeprio_mysql mysql -uroot -proot_password safeprio_db
```

**Detta kan ta 10-30 sekunder beroende på backup-storlek.**

---

### Steg 8: Verifiera att åäö nu är korrekta

```powershell
docker exec safeprio_mysql mysql -uroot -proot_password -e "SELECT designation_sv FROM product_groups LIMIT 3;" safeprio_db
```

**Du ska nu se:**
```
Valselmärkning 150x210 mm, vinyletikett, påbud, enl.spec.
Valselmärkning 150x210 mm, vinyletikett, varning, enl.spec.
Valselmärkning 150x210 mm, vinyletikett, förbud, enl.spec.
```

**INTE:**
```
Valselmã¤rkning 150x210 mm, vinyletikett, pã¥bud, enl.spec.
Valselmã¤rkning 150x210 mm, vinyletikett, varning, enl.spec.
```

---

### Steg 9: Testa webbsidan

Öppna webbläsaren och gå till: http://localhost:8080

Kontrollera att:
- ✅ Produktgrupper visar korrekta svenska tecken (åäö)
- ✅ Produktbeskrivningar är korrekta
- ✅ Inga ?? eller konstiga tecken

---

## 🔍 OM NÅGOT GÅR FEL

### Problem: "Test-Path" returnerar False

**Lösning:**
1. Kontrollera att OneDrive är aktivt och synkat
2. Vänta några minuter och testa igen
3. Kontrollera att filen finns i `FIX_CHARSET_HEMMA.md` hemma

---

### Problem: MySQL startar inte efter "docker-compose up -d"

**Lösning:**
```powershell
# Kolla loggar
docker logs safeprio_mysql

# Vänta lite längre och försök igen
Start-Sleep -Seconds 30
docker exec safeprio_mysql mysql -uroot -proot_password -e "SELECT 1;"
```

---

### Problem: Import ger fel "Access denied" eller "Database doesn't exist"

**Lösning:**
```powershell
# Skapa databasen manuellt om den inte finns
docker exec safeprio_mysql mysql -uroot -proot_password -e "CREATE DATABASE IF NOT EXISTS safeprio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Försök importera igen
Get-Content safeprio_backup_CORRECT_UTF8.sql | docker exec -i safeprio_mysql mysql -uroot -proot_password safeprio_db
```

---

### Problem: Teckenkodningen är fortfarande fel efter import

**Möjliga orsaker:**
1. Backup-filen innehåller fortfarande korrupt data
   - **Lösning:** Skapa ny backup hemma enligt `FIX_CHARSET_HEMMA.md`

2. Webbläsaren cachar gammal data
   - **Lösning:** Hårt ladda om sidan (`Ctrl + Shift + R`)

3. PHP saknar charset-konfiguration
   - **Lösning:** Kontrollera att `includes/config.php` innehåller:
     ```php
     $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
     $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
     ```

---

## 📝 SNABB CHECKLIST

När du sitter på jobbet:

- [ ] Filen `safeprio_backup_CORRECT_UTF8.sql` finns i projektmappen
- [ ] Kör `docker-compose down -v` (raderar gammal databas)
- [ ] Kör `docker-compose up -d` (startar containrar)
- [ ] Vänta 15 sekunder
- [ ] Importera backup: `Get-Content safeprio_backup_CORRECT_UTF8.sql | docker exec -i safeprio_mysql mysql -uroot -proot_password safeprio_db`
- [ ] Verifiera med `docker exec safeprio_mysql mysql -uroot -proot_password -e "SELECT designation_sv FROM product_groups LIMIT 3;" safeprio_db`
- [ ] Testa på http://localhost:8080

---

## 🎉 KLART!

När alla åäö visas korrekt är problemet löst! Du behöver inte göra detta igen så länge du skapar backuper med rätt charset.

**Tips för framtiden:**  
Använd alltid `--default-character-set=utf8mb4` när du skapar backuper:
```bash
mysqldump --default-character-set=utf8mb4 ...
```

---

## 🆘 BEHÖVER DU HJÄLP?

Om något inte fungerar, kolla först:
1. Docker Desktop är igång
2. Inga andra containrar använder port 8080 eller 3306
3. OneDrive har synkat backup-filen korrekt

**Debug-kommando:**
```powershell
docker ps  # Visa aktiva containrar
docker logs safeprio_mysql  # Visa MySQL-loggar
docker logs safeprio_web  # Visa webserver-loggar
```
