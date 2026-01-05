# Produktsystem - Regler och Struktur

## Del 1: Artikelnummer

### Grundprincip
Alla våra produkter har **unika artikelnummer**. Det finns ingen produkt som har olika egenskaper (varianter). Varje kombination av storlek, symbol och material får sitt eget unika artikelnummer.

### Struktur
- **Ett artikelnummer = En specifik produkt**
- Storlek, symbol och material är alla inkluderade i artikelnumret
- Ingen produkt har separata varianter eller alternativ

### Exempel
```
VMS_210-300_M-M002
```

Detta artikelnummer representerar EN specifik produkt med:
- Specifik storlek
- Specifikt symbol
- Specifikt material

## Del 2: Artikelnummer Struktur - Första Segmentet

### Produkttyp och Material (Prefix)

Det första segmentet i artikelnumret består av 2-3 bokstäver som anger både produkttyp och material.

#### Produkttyper (Bas)
- **VM** = Varselmärkning
- **PM** = Placeringsdekal
- **RC** = Återvinningsdekal
- **RM** = Rörmärkning
- *(och fler...)*

#### Materialsuffix
- **(inget suffix)** = Vinyldekal/Etikett
- **S** = Skylt i hårdplast (Skylt)
- **A** = Skylt i aluminium (Aluminium)
- **F** = Golvdekal (Floor)

#### Exempel - Varselmärkning
- **VM** = Varselmärkning Vinyldekal/Etikett
- **VMS** = Varselmärkning Skylt i Hårdplast
- **VMA** = Varselmärkning Skylt i Aluminium
- **VMF** = Varselmärkning Vinyldekal/Etikett för Golv

#### Exempel - Placeringsdekal
- **PM** = Placeringsdekal Vinyldekal/Etikett
- **PMS** = Placeringsdekal Skylt i Hårdplast
- **PMA** = Placeringsdekal Skylt i Aluminium
- **PMF** = Placeringsdekal Vinyldekal/Etikett för Golv

*Samma system gäller för RC, RM och alla andra produkttyper.*

## Del 3: Storlekssegment

### Grundstruktur
Efter prefix (VMS, VMA, PM, etc.) kommer storleken i formatet: **längd-höjd**

- **Första siffran** = Längden (mm)
- **Andra siffran** = Höjden (mm)

### Standardstorlekar (VM, RC, PM, etc.)

Produkterna finns **endast i stående format**:

| Format | Faktisk storlek | Förenklad beteckning |
|--------|----------------|---------------------|
| A5 stående | 148 × 210 mm | **150-210** |
| A4 stående | 210 × 297 mm | **210-300** |

**Exempel:**
- `VMS_210-300` = Varselmärkning Skylt i Hårdplast, A4 stående (210×300mm)
- `PM_150-210` = Placeringsdekal Vinyldekal, A5 stående (150×210mm)

> **OBS:** Det finns **inga liggande format** för dessa produkttyper.

### Rörmärkning (RM) - Unika storlekar

Rörmärkning har **4 grundstorlekar** som skiljer sig från standardformaten.

Rörmärkning säljs i **ARK** och varje ark innehåller olika antal dekaler/etiketter beroende på storleken:

| Storlek (längd × höjd) | Dekaler per ark |
|------------------------|----------------|
| **150-12** mm | 20 st/ark |
| **250-25** mm | 10 st/ark |
| **350-40** mm | 7 st/ark |
| **450-57** mm | 5 st/ark |

**Exempel:**
- `RM_150-12` = Rörmärkning 150mm × 12mm (20 dekaler per ark)
- `RM_250-25` = Rörmärkning 250mm × 25mm (10 dekaler per ark)
- `RM_350-40` = Rörmärkning 350mm × 40mm (7 dekaler per ark)
- `RM_450-57` = Rörmärkning 450mm × 57mm (5 dekaler per ark)

## Del 4: Grupp och Symbol (Sista segmentet)

Det sista segmentet består av **grupp** och **symbolnummer** i formatet: **Grupp-Symbol###**

### Varselmärkning (VM) - Gruppindelning

VM-produkter har följande gruppindelningar:

| Kod | Grupp | Beskrivning |
|-----|-------|-------------|
| **M** | Påbud | Mandatory |
| **W** | Varning | Warning |
| **P** | Förbud | Prohibition |
| **E** | Nöd | Emergency |
| **F** | Brand | Fire |
| **D** | Fara | Danger |

**Symbol-exempel:**
- `M-M002` = Påbudsgrupp, Symbol M002 (Läs bruksanvisningen)
- `M-M003` = Påbudsgrupp, Symbol M003 (Använd hörselskydd)
- `W-W015` = Varningsgrupp, Symbol W015 (Använd varselklädset)
- `P-P008` = Förbudsgrupp, Symbol P008 (Ej metallföremål eller klockor)

**Komplett artikelnummer:**
```
VMS_210-300_M-M002
```
= Varselmärkning Skylt i Hårdplast, 210×300mm, Påbud, Symbol M002

### Placeringsdekal (PM) och Återvinningsdekal (RC)

**Ingen gruppindelning** - sortimentet är för litet.

Dessa produkter har symboler direkt utan gruppprefix.

**Format:** `PREFIX_STORLEK_SYMBOL###`

**Exempel:**
- `PMS_210-300_PM001` = Placeringsdekal Skylt i Hårdplast, 210×300mm, Symbol PM001
- `RC_150-210_RC015` = Återvinningsdekal Vinyldekal, 150×210mm, Symbol RC015
- `PMA_210-300_PM042` = Placeringsdekal Skylt i Aluminium, 210×300mm, Symbol PM042

### Rörmärkning (RM) - Egen gruppindelning

RM-produkter har **7 specifika grupper**:

| Kod | Grupp | Beskrivning |
|-----|-------|-------------|
| **B** | Brandskydd | |
| **BG** | Brandfarliga gaser | |
| **BV** | Brandfarliga vätskor | |
| **FG** | Frätande och giftiga | |
| **LV** | Luft och vakuum | |
| **V** | Vatten | |
| **VA** | Vattenånga | |

**Symbol-exempel:**
- `BG-BG001` = Brandfarliga gaser, Symbol BG001
- `V-V025` = Vatten, Symbol V025

**Komplett artikelnummer:**
```
RM_150-12_BG-BG001
```
= Rörmärkning, 150×12mm, Brandfarliga gaser, Symbol BG001

---

## Priser och Stafflingar

### Grundprincip
Varje produkt har **5 stycken priser** för både SEK och EUR, baserade på olika stafflingar.

**Viktigt:**
- **VM, PM, RC**: Säljs **per styck** (1 st = 1 dekal/skylt)
- **RM**: Säljs **per ark** (varje ark innehåller flera dekaler beroende på storlek)

### VM, PM och RC - Stafflingsmodell (Per styck)

Priser sätts baserat på antal köpta enheter:

| Staffling | Antal | Prisexempel (VM_210-300_M) |
|-----------|-------|---------------------------|
| **Staffling 1** | 1 st | 199,00 kr/st |
| **Staffling 2** | 2 st | 135,00 kr/st |
| **Staffling 3** | 3 st | 114,00 kr/st |
| **Staffling 4** | 4 st | 103,00 kr/st |
| **Staffling 5** | 5+ st | 97,00 kr/st |

**Exempel:**
- Köper kund 1 st = de betalar 199,00 kr/st
- Köper kund 3 st = de betalar 114,00 kr/st (per styck)
- Köper kund 10 st = de betalar 97,00 kr/st (per styck)

### RM - Stafflingsmodell (Per ark)

Rörmärkning säljs i **ARK** med egen stafflingsstruktur:

| Staffling | Antal ark | Prisexempel (RM_350-40_LV) |
|-----------|-----------|---------------------------|
| **Staffling 1** | 1-4 ark | 361,00 kr/ark |
| **Staffling 2** | 5-9 ark | 185,00 kr/ark |
| **Staffling 3** | 10-24 ark | 114,00 kr/ark |
| **Staffling 4** | 25-49 ark | 89,00 kr/ark |
| **Staffling 5** | 50+ ark | 71,00 kr/ark |

**Exempel:**
- Köper kund 3 ark = de betalar 361,00 kr/ark
- Köper kund 8 ark = de betalar 185,00 kr/ark
- Köper kund 15 ark = de betalar 114,00 kr/ark
- Köper kund 100 ark = de betalar 71,00 kr/ark

### Valuta
- Alla produkter har priser i både **SEK** och **EUR**
- Båda valutorna följer samma stafflingsstruktur (5 prisnivåer)

---

## Teknisk Implementation

### 1. URL-hantering med Artikelnummer

Hemsidan ska använda **artikelnummer** istället för ID i URL:er.

**Format:**
```
domän.se/produkt.php?artno=VMS_210-300_M-M002
```

**Exempel:**
- `produkt.php?artno=VMS_210-300_M-M002` → Visar Varselmärkning Skylt 210×300, Påbud M002
- `produkt.php?artno=RM_350-40_BG-BG001` → Visar Rörmärkning 350×40, Brandfarliga gaser BG001

**Fördel:** Kunden hamnar på exakt rätt produkt, storlek och symbol direkt via länken.

### 2. Artikelnummer vs Produktnamn

**Artikelnummer (artno):**
- Använder **underscore** (_) för att undvika problem med webbadresser
- Format: `VMS_210-300_M-M002`

**Produktnamn (name):**
- Använder **mellanslag och bindestreck** för bättre läsbarhet
- Format: `VMS 210-300 M - M002`

**Viktigt:**
- Både artikelnummer och produktnamn är **samma för svenska och engelska**
- Endast produktbeskrivningar översätts, inte själva artno/name

**Exempel:**
```
artno: VMS_210-300_M-M002
name:  VMS 210-300 M - M002
```

### 3. Databasstruktur - Undvik Upprepning

För att undvika massiv dataduplikation behövs en **uppdelad databasstruktur**.

#### Problemet
Om varje symbol (M002, M003, M004...) för samma produktgrupp (VMS_210-300_M) ska ha:
- Samma priser (alla 5 stafflingar)
- Samma beskrivning
- Samma material
- Samma storlek
- Samma grupp

...blir det **enorm upprepning** av identisk data.

#### Lösningen: Två separata tabeller

**Tabell 1: Produktgrupper (Bas)**
- Innehåller gemensam data för produktgruppen
- Exempel: `VMS_210-300_M`
- Data: Priser, beskrivning, material, storlek, grupp

**Tabell 2: Symboler**
- Innehåller unika symboldata
- Exempel: `M002`, `M003`, `M004`
- Data: Symbolnamn, symbolbild
- Länkas till produktgruppen

**Relation:**
```
Produktgrupp (VMS_210-300_M)
  ├── Symbol M002 (Läs bruksanvisningen)
  ├── Symbol M003 (Använd hörselskydd)
  ├── Symbol M004 (...)
  └── Symbol M005 (...)
```

**Fördelar:**
- ✅ Inga dubbletter av priser och beskrivningar
- ✅ Lätt att uppdatera pris för hela produktgruppen
- ✅ Symboler läggs till utan att duplicera produktdata
- ✅ Effektiv databasstruktur

---

*Dokumentet är nu komplett och uppdaterat.*
*Senast uppdaterad: 2026-01-03*
