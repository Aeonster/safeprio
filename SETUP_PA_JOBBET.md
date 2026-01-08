# SafePrio - Setup på Jobbdatorn 🚀

**Skapad:** 2026-01-06  
**Syfte:** Få igång exakt samma utvecklingsmiljö på jobbet som du har hemma

---

## 📋 Översikt

Detta projekt är en PHP-baserad webbutik för varselskyltar med följande teknologier:
- **PHP 8.2** med Apache webbserver
- **MySQL 8.0** databas
- **Docker & Docker Compose** för lokal utvecklingsmiljö
- **phpMyAdmin** för databashantering
- **Multi-språk** (Svenska/Engelska) och multi-valuta (SEK/EUR)

---

## 🔧 Mjukvara som behövs (Installera dessa först!)

### 1. Docker Desktop
- **Ladda ner från:** https://www.docker.com/products/docker-desktop
- Installera Docker Desktop för Windows
- Starta Docker Desktop (behöver köras i bakgrunden)
- **Verifiera installation:**
  ```powershell
  docker --version
  docker-compose --version
  ```
  Du ska se version för båda.

### 2. VS Code
Om inte redan installerat:
- **Ladda ner från:** https://code.visualstudio.com/
- Installera VS Code

### 3. VS Code Extensions (Rekommenderade)
Öppna VS Code och installera följande extensions:
- **Docker** (ms-azuretools.vscode-docker)
- **PHP Intelephense** (bmewburn.vscode-intelephense-client)
- **PHP Debug** (xdebug.php-debug)
- **MySQL** (cweijan.vscode-mysql-client2) [valfritt]

---

## 📁 Hämta Projektet (Git)

### Alternativ A: Om projektet finns på Git Repository
```powershell
cd C:\Users\[DITT_ANVÄNDARNAMN]\
git clone [REPOSITORY_URL] safeprio
cd safeprio
```

### Alternativ B: Om du inte använder Git än
1. Kopiera hela projektmappen från OneDrive/USB/Nätverksdisk
2. Placera den på lämplig plats på jobbdatorn (t.ex. `C:\Users\[NAMN]\Projekt\safeprio`)

---

## 🚀 Starta Utvecklingsmiljön

### Steg 1: Öppna projektet i VS Code
```powershell
# Navigera till projektmappen
cd C:\Users\[DITT_NAMN]\[SÖKVÄG]\safeprio

# Öppna i VS Code
code .
```

### Steg 2: Starta Docker-containrarna
Öppna terminal i VS Code (Ctrl + Ö eller Ctrl + `) och kör:

```powershell
docker-compose up -d
```

**Första gången tar det 2-5 minuter** medan Docker:
- Bygger PHP/Apache-containern (installerar PHP 8.2, extensions, Apache)
- Laddar ner MySQL 8.0 image
- Laddar ner phpMyAdmin image
- Skapar nätverk och volumes

**Output du bör se:**
```
Creating network "safeprio_safeprio_network" with driver "bridge"
Creating volume "safeprio_mysql_data" with default driver
Building web
...
Creating safeprio_mysql ... done
Creating safeprio_web ... done
Creating safeprio_phpmyadmin ... done
```

### Steg 3: Verifiera att allt körs
```powershell
docker-compose ps
```

Du ska se tre containrar i status "Up":
- `safeprio_web` - Port 8000
- `safeprio_mysql` - Port 3307
- `safeprio_phpmyadmin` - Port 8080

---

## 🌐 Öppna Applikationen

Efter att containrarna startat, öppna i webbläsaren:

- **Webbplats:** http://localhost:8000
- **phpMyAdmin:** http://localhost:8080
  - Användare: `root`
  - Lösenord: `root_password`

---

## 🗄️ Importera Databas

### Metod 1: Via phpMyAdmin (Enklast!)
1. Gå till http://localhost:8080
2. Logga in med `root` / `root_password`
3. Välj databasen `safeprio_db` (skapas automatiskt)
4. Klicka på fliken "Import"
5. Välj filen `backup_20260105_183450.sql` från projektmappen
6. Klicka "Go/Kör"

### Metod 2: Via Terminal (Docker exec)
```powershell
# Kopiera SQL-filen till MySQL-containern
docker cp backup_20260105_183450.sql safeprio_mysql:/backup.sql

# Importera till databasen
docker exec -i safeprio_mysql mysql -uroot -proot_password safeprio_db < backup_20260105_183450.sql
```

### Alternativ om du inte har backup-fil
Om backup-filen saknas, behöver du manuellt:
1. Skapa databastabeller (se `database.sql` om sådan finns)
2. Eller använd admin-gränssnittet för att skapa produkter

---

## ⚙️ Konfiguration

### Databaskonfiguration är redan korrekt!
Filen `includes/config.php` är redan konfigurerad för Docker:

```php
define('DB_HOST', 'mysql');          // Namnet på MySQL-containern
define('DB_NAME', 'safeprio_db');    
define('DB_USER', 'root');
define('DB_PASS', 'root_password');
```

**Du behöver INTE ändra något här!** 🎉

---

## 🛠️ Docker Kommandon du kommer behöva

### Starta miljön (när Docker Desktop kör)
```powershell
docker-compose up -d
```

### Stoppa containrarna (pause utan att ta bort data)
```powershell
docker-compose stop
```

### Starta igen (snabbare än `up`)
```powershell
docker-compose start
```

### Se loggar (felsökning)
```powershell
docker-compose logs -f
# Ctrl+C för att avsluta
```

### Starta om containrar (efter config-ändringar)
```powershell
docker-compose restart
```

### Stäng ner helt (tar bort containrar men INTE data)
```powershell
docker-compose down
```

### Bygga om containrarna (om du ändrat Dockerfile)
```powershell
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Se status på containrar
```powershell
docker-compose ps
```

### Gå in i en container (för debugging)
```powershell
# PHP/Apache container
docker exec -it safeprio_web bash

# MySQL container
docker exec -it safeprio_mysql bash
```

---

## 📂 Projektstruktur

```
safeprio/
├── docker-compose.yml          # Docker konfiguration
├── Dockerfile                  # PHP/Apache container definition
├── index.php                   # Startsida
├── produkter.php              # Produktlista
├── produkt.php                # Produktdetaljer
├── kassa.php                  # Checkout
├── kontakt.php                # Kontaktformulär
├── om-oss.php                 # Om oss-sida
│
├── admin/                     # Admin-panel
│   ├── index.php             # Dashboard/produktlista
│   ├── login.php             # Admin-inloggning
│   ├── produktgrupper.php    # Hantera produktgrupper
│   ├── symboler.php          # Hantera symboler
│   ├── stafflingar.php       # Hantera stafflingar
│   ├── generate_products.php # Generera produkter
│   └── ...
│
├── api/                      # REST API endpoints
│   ├── order.php            # Order API
│   ├── produkter.php        # Produkt API
│   └── kontakt.php          # Kontakt API
│
├── includes/                 # Gemensamma filer
│   ├── config.php          # Databas & inställningar
│   ├── header.php          # Header template
│   └── footer.php          # Footer template
│
├── lang/                     # Språkfiler
│   ├── sv.php              # Svenska texter
│   └── en.php              # Engelska texter
│
├── css/                      # Stilmallar
│   └── style.css
│
├── js/                       # JavaScript
│   └── app.js
│
├── images/                   # Bilder
│   └── icons/
│
├── uploads/                  # Uppladdade filer
│   ├── products/           # Produktbilder
│   ├── product_groups/     # Produktgruppbilder
│   └── symbols/            # Symbolbilder
│
└── backup_20260105_183450.sql  # Databas backup
```

---

## 🔐 Admin-panel

### Logga in i Admin
- **URL:** http://localhost:8000/admin/
- **Uppgifter:** (kolla i databasen eller kod för inloggningsuppgifter)
  - Du kan behöva kolla `admin/login.php` för hårdkodade credentials
  - Eller kontrollera om det finns en `users` tabell i databasen

### Admin-funktioner
- Hantera produktgrupper
- Hantera symboler/varselskyltar
- Hantera stafflingar (materialbas)
- Generera produkter (kombinationer)
- Se order

---

## 🌍 Funktioner i Projektet

### Multi-språk
- Svenska (default)
- Engelska
- Byt via: `?lang=sv` eller `?lang=en`

### Multi-valuta
- SEK (default)
- EUR
- Byt via: `?currency=SEK` eller `?currency=EUR`

### Produktsystem
Produkterna genereras från kombinationer av:
- **Produktgrupper** (kategori, form, material)
- **Symboler** (varselskyltar)
- **Stafflingar** (storleksvariation)

---

## 🐛 Felsökning

### Problem: Port 8000 redan används
**Lösning:** Ändra port i `docker-compose.yml`:
```yaml
web:
  ports:
    - "8001:80"  # Ändra från 8000
```
Sedan: `docker-compose down && docker-compose up -d`

### Problem: "Cannot connect to Docker daemon"
**Lösning:** Starta Docker Desktop

### Problem: MySQL anslutning misslyckas
**Kontrollera:**
1. Är MySQL-containern igång? `docker-compose ps`
2. Är `DB_HOST` satt till `mysql` i `includes/config.php`?
3. Kör: `docker-compose logs mysql` för att se MySQL-loggar

### Problem: Sidan visar inte UTF-8 tecken korrekt (svenska bokstäver)
**Lösning:** Detta är redan fixat i Docker-konfigurationen, men om det uppstår:
- Kontrollera att MySQL kör med UTF-8: `docker-compose logs mysql`
- Verifiera i `docker-compose.yml` att charset är satt korrekt

### Problem: Ändringar i PHP-kod syns inte
**Lösning:** 
- Containern har en volume-mount, så ändringar syns direkt
- Om inte: `docker-compose restart web`

### Problem: Databas försvinner när jag stoppar Docker
**Detta ska INTE hända!** Data sparas i Docker volume `mysql_data`.
- För att verkligen ta bort databasen: `docker-compose down -v`

---

## 💾 Backup & Data

### Skapa databas-backup
```powershell
docker exec safeprio_mysql mysqldump -uroot -proot_password safeprio_db > backup_$(Get-Date -Format "yyyyMMdd_HHmmss").sql
```

### Databasvolume
- MySQL data sparas i Docker volume: `mysql_data`
- Data bevaras även när containrar stoppas/startas om
- Data tas INTE bort vid `docker-compose down`
- För att ta bort ALLT (inkl data): `docker-compose down -v`

---

## 🔄 Git Workflow (om du använder Git)

### Första gången på jobbet:
```powershell
git clone [REPO_URL]
cd safeprio
docker-compose up -d
```

### Vid arbete:
```powershell
# Hämta senaste ändringar från hemma
git pull

# Jobba med koden...

# Commit och push
git add .
git commit -m "Beskrivning av ändringar"
git push
```

---

## 📝 Viktiga Kommandon - Snabbreferens

```powershell
# Starta allt
docker-compose up -d

# Stoppa allt
docker-compose stop

# Se status
docker-compose ps

# Se loggar
docker-compose logs -f

# Starta om
docker-compose restart

# Stäng ner
docker-compose down

# Öppna webbplats
start http://localhost:8000

# Öppna phpMyAdmin
start http://localhost:8080
```

---

## ✅ Checklista för Första Dagen på Jobbet

- [ ] Installera Docker Desktop
- [ ] Starta Docker Desktop
- [ ] Installera VS Code (om inte redan finns)
- [ ] Installera VS Code Docker Extension
- [ ] Hämta projekt (Git clone eller kopiera)
- [ ] Öppna projekt i VS Code
- [ ] Kör `docker-compose up -d`
- [ ] Vänta medan allt bygger (2-5 min första gången)
- [ ] Verifiera: `docker-compose ps`
- [ ] Öppna http://localhost:8000
- [ ] Öppna http://localhost:8080 (phpMyAdmin)
- [ ] Importera databas via phpMyAdmin
- [ ] Testa webbplatsen
- [ ] Testa admin-panel
- [ ] Klart! 🎉

---

## 🆘 Om Något Går Fel

### Steg 1: Kolla loggar
```powershell
docker-compose logs
```

### Steg 2: Starta om allt
```powershell
docker-compose restart
```

### Steg 3: Bygg om från scratch
```powershell
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Steg 4: Kolla att Docker Desktop körs
- Kolla system tray (Windows) att Docker-ikonen finns
- Om den visar rött: starta om Docker Desktop

---

## 📞 Kontakt & Support

**Vid problem:**
1. Läs felsökningsavsnittet ovan
2. Kolla Docker-loggar: `docker-compose logs`
3. Fråga AI (ge denna fil som kontext!)
4. Google felmeddelandet

**Användbara resurser:**
- Docker Docs: https://docs.docker.com/
- PHP Manual: https://www.php.net/manual/en/
- MySQL Docs: https://dev.mysql.com/doc/

---

## 🎯 Nästa Steg

När du har allt igång:
1. Bekanta dig med admin-panelen
2. Testa skapa/redigera produkter
3. Testa språkväxling (SV/EN)
4. Testa valutaväxling (SEK/EUR)
5. Fortsätt utveckla där du slutade hemma!

---

**Lycka till på jobbet imorgon! 🚀**
