<?php
/**
 * API för att hämta produkter med flerspråksstöd
 */

require_once '../includes/config.php';

header('Content-Type: application/json');

// Hämta parametrar
$kategori = isset($_GET['kategori']) ? sanitize($_GET['kategori']) : '';
$search = isset($_GET['sok']) ? sanitize($_GET['sok']) : '';
$lang = isset($_GET['lang']) ? sanitize($_GET['lang']) : 'sv';
$currency = isset($_GET['currency']) ? sanitize($_GET['currency']) : 'SEK';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 0;

// Sätt språk för översättningar
$_SESSION['lang'] = $lang;
$lang_file = __DIR__ . '/../lang/' . $lang . '.php';
$GLOBALS['lang'] = file_exists($lang_file) ? require $lang_file : require __DIR__ . '/../lang/sv.php';

try {
    $db = getDBConnection();
    
    // Hämta unika symboler istället för alla produktkombinationer
    // Gruppera per symbol och visa endast en gång
    // Välj en standardprodukt för varje symbol (t.ex. VM_210-300 eller RM_150-12)
    $sql = "SELECT DISTINCT
                s.id as symbol_id,
                s.symbol_code,
                s.category as symbol_category,
                s.name_sv as symbol_name_sv,
                s.name_en as symbol_name_en,
                s.images as symbol_images,
                MIN(pg.group_code) as group_code,
                MIN(pg.prefix) as prefix,
                (SELECT p2.article_number 
                 FROM products p2 
                 INNER JOIN product_groups pg2 ON p2.product_group_id = pg2.id
                 WHERE p2.symbol_id = s.id 
                 AND p2.active = 1 
                 AND pg2.active = 1
                 ORDER BY pg2.prefix ASC, pg2.size ASC
                 LIMIT 1) as article_number
            FROM symbols s
            INNER JOIN products p ON s.id = p.symbol_id
            INNER JOIN product_groups pg ON p.product_group_id = pg.id
            WHERE s.active = 1 AND p.active = 1 AND pg.active = 1";
    
    $params = [];
    
    // Filtrera på kategori (symbol_category)
    if ($kategori) {
        $sql .= " AND s.category = ?";
        $params[] = $kategori;
    }
    
    // Sök på symbolnamn eller symbolkod
    if ($search) {
        $sql .= " AND (s.name_sv LIKE ? OR s.name_en LIKE ? OR s.symbol_code LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $sql .= " GROUP BY s.id, s.symbol_code, s.category, s.name_sv, s.name_en, s.images
              ORDER BY s.symbol_code ASC";
    
    if ($limit > 0) {
        $sql .= " LIMIT " . $limit;
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $symboler = $stmt->fetchAll();
    
    // Konvertera till rätt språk
    $result = [];
    foreach ($symboler as $s) {
        // Kategorinamn baserat på symbol_category - använd språkfilen
        $kategoriMapping = [
            'W' => 'warning_signs',
            'M' => 'mandatory_signs',
            'P' => 'prohibition_signs',
            'F' => 'fire_symbols',
            'D' => 'hazard_symbols',
            'E' => 'emergency_signs',
            'B' => 'fire_protection',
            'BG' => 'flammable_gases',
            'BV' => 'flammable_liquids',
            'FG' => 'corrosive_toxic',
            'LV' => 'air_vacuum',
            'V' => 'water',
            'VA' => 'steam',
            'PM' => 'placement_signs',
            'RC' => 'recycling_signs'
        ];
        
        $symbolCategory = $s['symbol_category'] ?? '';
        $langKey = $kategoriMapping[$symbolCategory] ?? '';
        $kategoriNamn = $langKey ? __($langKey, $symbolCategory) : $symbolCategory;
        
        $result[] = [
            'symbol_id' => $s['symbol_id'],
            'symbol_code' => $s['symbol_code'],
            'symbol_name_sv' => $s['symbol_name_sv'],
            'symbol_name_en' => $s['symbol_name_en'],
            'symbol_images' => $s['symbol_images'],
            'symbol_category' => $symbolCategory,
            'group_code' => $s['group_code'],
            'prefix' => $s['prefix'],
            'article_number' => $s['article_number'],
            'kategori_namn' => $kategoriNamn
        ];
    }
    
    echo json_encode([
        'success' => true,
        'count' => count($result),
        'lang' => $lang,
        'currency' => $currency,
        'products' => $result
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Databasfel',
        'message' => $e->getMessage()
    ]);
}
