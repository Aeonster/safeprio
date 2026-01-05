# Copilot Regler för SafePrio Projekt

## Allmänna Regler

### Språk och Kommunikation
- Projektet är på svenska - använd svenska för kommentarer, dokumentation och kommunikation
- Kod och tekniska termer kan vara på engelska (standard i webbutveckling)

### Kodningsstil
- Använd PHP för backend-logik
- Använd moderna CSS-tekniker utan externa ramverk (ren CSS)
- JavaScript används för dynamisk funktionalitet på klientsidan
- Filstrukturen ska bibehållas som den är (includes/, api/, admin/, css/, js/, images/, etc.)

### Filanvändning
- **Bilder**: Alla bilder finns i `/images/` mappen
  - Logotyp: `logo.svg` (används i header)
  - Hero-bakgrund: `hero_001.jpg` (används på startsidan)
  - Symbolbilder: `/uploads/symbols/`
  - Produktbilder: `/uploads/products/`
  - Produktgruppsbilder: `/uploads/product_groups/`

### Designprinciper
- Håll designen ren och professionell
- Använd befintliga CSS-variabler för färger (definierade i style.css)
- Se till att responsiv design fungerar på mobila enheter

### CSS-regler
- Alla stiländringar görs i `/css/style.css`
- Använd befintliga CSS-klasser och variabler när det är möjligt
- Lägg till kommentarer för nya sektioner

### PHP-struktur
- `includes/config.php` - Konfiguration och språkfunktioner
- `includes/header.php` - Sidhuvud med navigation
- `includes/footer.php` - Sidfot
- `lang/sv.php` och `lang/en.php` - Språkfiler

## Specifika Projektregler

### Hero-sektion
- Höjd: 680px (min-height)
- Bakgrundsbild: `hero_001.jpg` med dark overlay (rgba(0, 0, 0, 0.5))
- Ingen vertikal centrering av innehåll
- Inga knappar i hero-sektionen (har tagits bort)

### Logotyp
- Använd `logo.svg` från images-mappen
- Storlek: 100px hög, auto-bredd
- Placering: I header till vänster

### Navigation
- Dropdown-meny för produktkategorier
- Språkväljare (Svenska/English)
- Kundvagn-ikon med antal artiklar

## Workflow
- Läs alltid relevanta filer innan du gör ändringar
- Använd `multi_replace_string_in_file` för flera oberoende ändringar
- Testa att ändringar inte bryter befintlig funktionalitet
- Bekräfta ändringar kort och koncist

## Databasrelaterat
- Database schema finns i `database.sql`
- Språkuppdateringar i `database_language_update.sql`
- Använd PDO för databasanslutningar

## Admin-sektionen
- Skyddad med inloggning
- Hanterar produkter, kategorier och ordrar
- Separata CSS-filer för admin-gränssnittet

---

**Senast uppdaterad**: 2026-01-01
**Version**: 1.0

*Denna fil ska läsas i början av varje ny session för att säkerställa konsistens i arbetet.*
