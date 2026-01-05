<?php
/**
 * Admin - Generera produkter automatiskt
 * Skapar alla möjliga kombinationer av produktgrupper × symboler
 */
session_start();

require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$db = getDBConnection();
$stats = [
    'deleted' => 0,
    'created' => 0,
    'errors' => []
];

try {
    // STEG 1: Rensa alla befintliga produkter
    $stmt = $db->query("SELECT COUNT(*) FROM products");
    $stats['deleted'] = $stmt->fetchColumn();
    
    $db->exec("TRUNCATE TABLE products");
    
    // STEG 2: Hämta alla aktiva produktgrupper
    $stmt = $db->query("SELECT * FROM product_groups WHERE active = 1");
    $groups = $stmt->fetchAll();
    
    // STEG 3: Hämta alla aktiva symboler
    $stmt = $db->query("SELECT * FROM symbols WHERE active = 1");
    $symbols = $stmt->fetchAll();
    
    // STEG 4: Generera produkter för varje kombination
    foreach ($groups as $group) {
        foreach ($symbols as $symbol) {
            // Matcha baserat på symbol_category och category
            if ($group['symbol_category'] !== $symbol['category']) {
                continue;
            }
            
            // Generera artikelnummer och produktnamn
            // Format: PREFIX_SIZE_GROUPCODE-SYMBOLCODE (om group_code finns)
            // Format: PREFIX_SIZE_SYMBOLCODE (om group_code saknas, t.ex. PM, RC)
            // Exempel: VMS_210-300_M-M002 eller PM_210-300_PM002A
            if (!empty($group['group_code'])) {
                $article_number = $group['prefix'] . '_' . $group['size'] . '_' . $group['group_code'] . '-' . $symbol['symbol_code'];
                $product_name = $group['prefix'] . ' ' . $group['size'] . ' ' . $group['group_code'] . ' - ' . $symbol['symbol_code'];
            } else {
                $article_number = $group['prefix'] . '_' . $group['size'] . '_' . $symbol['symbol_code'];
                $product_name = $group['prefix'] . ' ' . $group['size'] . ' ' . $symbol['symbol_code'];
            }
            
            // Skapa produkten (ingen kontroll behövs eftersom vi rensat tabellen)
            try {
                $stmt = $db->prepare("
                    INSERT INTO products (product_group_id, symbol_id, article_number, product_name, active)
                    VALUES (?, ?, ?, ?, 1)
                ");
                $stmt->execute([
                    $group['id'],
                    $symbol['id'],
                    $article_number,
                    $product_name
                ]);
                $stats['created']++;
            } catch (PDOException $e) {
                $stats['errors'][] = "Fel vid skapande av {$article_number}: " . $e->getMessage();
            }
        }
    }
    
    // Redirect tillbaka med statistik
    $_SESSION['generate_stats'] = $stats;
    header('Location: index.php?generated=1');
    exit;
    
} catch (PDOException $e) {
    $_SESSION['generate_error'] = $e->getMessage();
    header('Location: index.php?error=1');
    exit;
}
