<?php
header('Content-Type: text/html; charset=UTF-8');
require_once 'includes/config.php';

// Hämta artikelnummer från URL
$artno = isset($_GET['artno']) ? sanitize($_GET['artno']) : '';

if (!$artno) {
    header('Location: produkter.php');
    exit;
}

// Hämta produkt från nya databasen baserat på artikelnummer
try {
    $db = getDBConnection();
    
    // Använd view för att få all produktinformation
    $stmt = $db->prepare("SELECT * FROM view_products_full WHERE article_number = ? AND active = 1");
    $stmt->execute([$artno]);
    $produkt = $stmt->fetch();
    
    if (!$produkt) {
        header('Location: produkter.php');
        exit;
    }
    
    // Välj rätt språkversion
    $beskrivning = (CURRENT_LANG === 'en' && !empty($produkt['description_en'])) 
        ? $produkt['description_en'] 
        : $produkt['description_sv'];
    
    $symbolnamn = (CURRENT_LANG === 'en' && !empty($produkt['symbol_name_en'])) 
        ? $produkt['symbol_name_en'] 
        : $produkt['symbol_name_sv'];
    
    $material = (CURRENT_LANG === 'en' && !empty($produkt['material_en'])) 
        ? $produkt['material_en'] 
        : $produkt['material_sv'];
    
    $benamning = (CURRENT_LANG === 'en' && !empty($produkt['designation_en'])) 
        ? $produkt['designation_en'] 
        : $produkt['designation_sv'];
    
    // Hämta alla varianter för samma symbol (olika storlekar och material)
    $stmt = $db->prepare("
        SELECT DISTINCT 
            p.article_number,
            pg.prefix,
            pg.size,
            pg.material_sv,
            pg.material_en
        FROM products p
        JOIN product_groups pg ON p.product_group_id = pg.id
        WHERE p.symbol_id = ? AND p.active = 1 AND pg.active = 1
        ORDER BY pg.prefix, pg.size
    ");
    $stmt->execute([$produkt['symbol_id']]);
    $allVarianter = $stmt->fetchAll();
    
    // Organisera varianter per storlek och material
    $storlekar = [];
    $material_typer = [];
    $currentPrefix = $produkt['prefix'];
    $currentSize = $produkt['size'];
    
    foreach ($allVarianter as $variant) {
        // Samla unika storlekar
        if (!in_array($variant['size'], $storlekar)) {
            $storlekar[] = $variant['size'];
        }
        // Samla unika material (prefix)
        if (!isset($material_typer[$variant['prefix']])) {
            $material_typer[$variant['prefix']] = [
                'sv' => $variant['material_sv'],
                'en' => $variant['material_en']
            ];
        }
    }
    
    // Funktion för att bygga artikelnummer baserat på prefix, size och symbol_code
    function buildArticleNumber($allVarianter, $targetPrefix, $targetSize) {
        foreach ($allVarianter as $v) {
            if ($v['prefix'] === $targetPrefix && $v['size'] === $targetSize) {
                return $v['article_number'];
            }
        }
        return null;
    }
    
} catch (PDOException $e) {
    die("Databasfel: " . $e->getMessage());
}

$pageTitle = $produkt['product_name'] . ' - ' . $symbolnamn;
$pageDescription = substr(strip_tags($beskrivning), 0, 160);

require_once 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?php echo SITE_URL; ?>"><?php echo __('home'); ?></a>
            <span>/</span>
            <a href="produkter.php"><?php echo __('products'); ?></a>
            <span>/</span>
            <span><?php echo $produkt['product_name']; ?></span>
        </nav>
        <h1><?php echo sanitize($symbolnamn); ?></h1>
    </div>
</section>

<section class="product-detail">
    <div class="container">
        <div class="product-detail-grid">
            <!-- Produktbild med galleri -->
            <div class="product-gallery">
                <?php
                // Samla alla bilder från symbols, products, product_groups (i prioritetsordning)
                $allaBilder = [];
                
                // 1. Symbol bilder (högst prioritet)
                if (!empty($produkt['symbol_images'])) {
                    $symbolBilder = explode(',', $produkt['symbol_images']);
                    foreach ($symbolBilder as $bild) {
                        $bild = trim($bild);
                        if ($bild) {
                            $allaBilder[] = ['path' => SITE_URL . '/uploads/symbols/' . $bild, 'alt' => 'Symbol'];
                        }
                    }
                }
                
                // 2. Product bilder (mellan prioritet)
                if (!empty($produkt['product_images'])) {
                    $productBilder = explode(',', $produkt['product_images']);
                    foreach ($productBilder as $bild) {
                        $bild = trim($bild);
                        if ($bild) {
                            $allaBilder[] = ['path' => SITE_URL . '/uploads/products/' . $bild, 'alt' => 'Produkt'];
                        }
                    }
                }
                
                // 3. Product group bilder (lägst prioritet)
                if (!empty($produkt['product_group_images'])) {
                    $groupBilder = explode(',', $produkt['product_group_images']);
                    foreach ($groupBilder as $bild) {
                        $bild = trim($bild);
                        if ($bild) {
                            $allaBilder[] = ['path' => SITE_URL . '/uploads/product_groups/' . $bild, 'alt' => 'Material/Miljö'];
                        }
                    }
                }
                
                // Fallback om inga bilder finns
                if (empty($allaBilder)) {
                    $allaBilder[] = ['path' => SITE_URL . '/uploads/placeholder.svg', 'alt' => 'Placeholder'];
                }
                ?>
                
                <div class="product-main-image">
                    <img src="<?php echo $allaBilder[0]['path']; ?>" 
                         alt="<?php echo sanitize($produkt['product_name'] . ' - ' . $symbolnamn); ?>"
                         id="mainImage">
                </div>
                
                <?php if (count($allaBilder) > 1 || count($allaBilder) === 1): ?>
                <div class="product-thumbnails">
                    <?php foreach ($allaBilder as $index => $bildInfo): ?>
                    <button class="thumbnail-btn <?php echo $index === 0 ? 'active' : ''; ?>" 
                            data-image="<?php echo $bildInfo['path']; ?>"
                            onclick="changeMainImage(this)">
                        <img src="<?php echo $bildInfo['path']; ?>" 
                             alt="<?php echo sanitize($produkt['product_name']); ?> - <?php echo $bildInfo['alt']; ?> <?php echo $index + 1; ?>">
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Produktinfo -->
            <div class="product-info">
                <h1><?php echo sanitize($produkt['product_name']); ?></h1>
                <?php if ($benamning): ?>
                <p class="product-designation"><?php echo sanitize($benamning); ?></p>
                <?php endif; ?>
                <?php if ($produkt['sheets_per_unit']): ?>
                <p class="product-sheets-info"><strong><?php echo __('sheets_per_unit'); ?>:</strong> <?php echo $produkt['sheets_per_unit']; ?> st/ark</p>
                <?php endif; ?>

                <div class="product-description">
                    <?php echo nl2br(sanitize($beskrivning)); ?>
                </div>

                <!-- Storlek- och materialval -->
                <?php if (count($storlekar) > 1 || count($material_typer) > 1): ?>
                <div class="product-variants">
                    
                    <!-- Storleksval -->
                    <?php if (count($storlekar) > 1): ?>
                    <div class="variant-section">
                        <h3><?php echo __('size'); ?>:</h3>
                        <div class="variant-options">
                            <?php foreach ($storlekar as $size): 
                                $newArticle = buildArticleNumber($allVarianter, $currentPrefix, $size);
                                if ($newArticle):
                                    $isActive = ($size === $currentSize);
                            ?>
                            <a href="produkt.php?artno=<?php echo urlencode($newArticle); ?>" 
                               class="variant-option <?php echo $isActive ? 'active' : ''; ?>">
                                <?php echo $size; ?> mm
                            </a>
                            <?php endif; endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Materialval -->
                    <?php if (count($material_typer) > 1): ?>
                    <div class="variant-section">
                        <h3><?php echo __('material'); ?>:</h3>
                        <div class="variant-options">
                            <?php foreach ($material_typer as $prefix => $mat_names): 
                                $newArticle = buildArticleNumber($allVarianter, $prefix, $currentSize);
                                if ($newArticle):
                                    $isActive = ($prefix === $currentPrefix);
                                    $mat_name = CURRENT_LANG === 'en' ? $mat_names['en'] : $mat_names['sv'];
                            ?>
                            <a href="produkt.php?artno=<?php echo urlencode($newArticle); ?>" 
                               class="variant-option <?php echo $isActive ? 'active' : ''; ?>">
                                <?php echo $mat_name; ?>
                            </a>
                            <?php endif; endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                </div>
                <?php endif; ?>

                <!-- Produktval: Endast Antal (ingen storlek/material val då det är i artikelnumret) -->
                <form class="product-form" id="productForm">
                    <input type="hidden" name="article_number" value="<?php echo $produkt['article_number']; ?>">

                    <!-- Prisstaffling -->
                    <?php
                    // Parse staffling configuration
                    $stafflingConfig = json_decode($produkt['staffling_config'], true);
                    $pricingUnitDB = $produkt['pricing_unit'] ?? 'st';
                    
                    // Översätt enhet baserat på språk
                    $unitTranslations = [
                        'st' => ['sv' => 'st', 'en' => 'pcs'],
                        'ark' => ['sv' => 'ark', 'en' => 'sheets'],
                        'm' => ['sv' => 'm', 'en' => 'm'],
                        'kg' => ['sv' => 'kg', 'en' => 'kg'],
                        'm²' => ['sv' => 'm²', 'en' => 'm²']
                    ];
                    
                    $pricingUnit = $unitTranslations[$pricingUnitDB][CURRENT_LANG] ?? $pricingUnitDB;
                    
                    if (!$stafflingConfig || !is_array($stafflingConfig)) {
                        // Fallback till default om JSON är invalid
                        $stafflingConfig = [
                            ['min' => 1, 'max' => 9],
                            ['min' => 10, 'max' => 24],
                            ['min' => 25, 'max' => 49],
                            ['min' => 50, 'max' => 99],
                            ['min' => 100, 'max' => 9999]
                        ];
                    }
                    
                    // Hämta priser baserat på valuta
                    $prices = (CURRENT_CURRENCY === 'EUR') 
                        ? [$produkt['price_eur_1'], $produkt['price_eur_2'], $produkt['price_eur_3'], $produkt['price_eur_4'], $produkt['price_eur_5']]
                        : [$produkt['price_sek_1'], $produkt['price_sek_2'], $produkt['price_sek_3'], $produkt['price_sek_4'], $produkt['price_sek_5']];
                    ?>
                    
                    <div class="price-staffling">
                        <div class="order-row">
                            <div class="order-quantity">
                                <label for="antal"><?php echo __('quantity'); ?>:</label>
                                <div class="quantity-input">
                                    <button type="button" class="qty-btn minus">-</button>
                                    <input type="number" name="antal" id="antal" value="1" min="1" max="999">
                                    <button type="button" class="qty-btn plus">+</button>
                                </div>
                            </div>
                            
                            <div class="order-total">
                                <span class="total-label">Totalt:</span>
                                <span class="total-value" id="totalPrice"><?php echo formatPrice($prices[0]); ?></span>
                                <span class="total-vat"><?php echo __('excl_vat'); ?></span>
                            </div>
                            
                            <button type="submit" class="btn btn-primary add-to-cart-btn">
                                <?php echo __('add_to_cart'); ?>
                            </button>
                        </div>
                        
                        <h3>Prisstaffling</h3>
                        <div class="staffling-grid">
                            <?php foreach ($stafflingConfig as $index => $tier): 
                                $min = $tier['min'];
                                $max = $tier['max'];
                                $label = ($min == $max) 
                                    ? "{$min} {$pricingUnit}" 
                                    : (($max >= 9999) ? "{$min}+ {$pricingUnit}" : "{$min}-{$max} {$pricingUnit}");
                                $price = $prices[$index] ?? $prices[0];
                            ?>
                            <div class="staffling-item <?php echo $index === 0 ? 'active' : ''; ?>" 
                                 data-tier="<?php echo $index; ?>" 
                                 data-min="<?php echo $min; ?>" 
                                 data-max="<?php echo $max; ?>"
                                 data-price="<?php echo $price; ?>">
                                <div class="staffling-qty"><?php echo $label; ?></div>
                                <div class="staffling-price"><?php echo formatPrice($price); ?>/<?php echo $pricingUnit; ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <p class="staffling-note">Ditt pris är upplyst</p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
    // Produktdata för JavaScript
    window.currentProduct = {
        article_number: '<?php echo $produkt['article_number']; ?>',
        product_name: '<?php echo addslashes($produkt['product_name']); ?>',
        symbol_name: '<?php echo addslashes($symbolnamn); ?>',
        prices_sek: [<?php echo $produkt['price_sek_1']; ?>, <?php echo $produkt['price_sek_2']; ?>, <?php echo $produkt['price_sek_3']; ?>, <?php echo $produkt['price_sek_4']; ?>, <?php echo $produkt['price_sek_5']; ?>],
        prices_eur: [<?php echo $produkt['price_eur_1']; ?>, <?php echo $produkt['price_eur_2']; ?>, <?php echo $produkt['price_eur_3']; ?>, <?php echo $produkt['price_eur_4']; ?>, <?php echo $produkt['price_eur_5']; ?>],
        sheets_per_unit: <?php echo $produkt['sheets_per_unit'] ?? 'null'; ?>
    };

    // Uppdatera prisstaffling baserat på antal
    function updatePriceStaffling() {
        const antalInput = document.getElementById('antal');
        const antal = parseInt(antalInput.value) || 1;
        const stafflingItems = document.querySelectorAll('.staffling-item');
        const totalPriceEl = document.getElementById('totalPrice');
        const currentUnitPriceEl = document.getElementById('currentUnitPrice');
        
        let activeTier = 0;
        let unitPrice = 0;
        
        // Hitta rätt staffling baserat på antal
        stafflingItems.forEach((item, index) => {
            const min = parseInt(item.dataset.min);
            const max = parseInt(item.dataset.max);
            const price = parseFloat(item.dataset.price);
            
            if (antal >= min && antal <= max) {
                item.classList.add('active');
                activeTier = index;
                unitPrice = price;
            } else {
                item.classList.remove('active');
            }
        });
        
        // Beräkna totalpris
        const totalPrice = unitPrice * antal;
        const currency = '<?php echo CURRENT_CURRENCY; ?>';
        const currencySymbol = currency === 'EUR' ? '€' : 'kr';
        const formattedTotalPrice = totalPrice.toLocaleString('sv-SE', { minimumFractionDigits: 2 });
        const formattedUnitPrice = unitPrice.toLocaleString('sv-SE', { minimumFractionDigits: 2 });
        
        totalPriceEl.textContent = currency === 'EUR' 
            ? currencySymbol + formattedTotalPrice 
            : formattedTotalPrice + ' ' + currencySymbol;
            
        currentUnitPriceEl.textContent = currency === 'EUR' 
            ? currencySymbol + formattedUnitPrice 
            : formattedUnitPrice + ' ' + currencySymbol;
    }
    
    // Lyssna på ändringar i antal
    document.addEventListener('DOMContentLoaded', function() {
        const antalInput = document.getElementById('antal');
        
        // Initial uppdatering
        updatePriceStaffling();
        
        // Vid ändring av antal (plus/minus knappar hanteras i app.js)
        antalInput.addEventListener('input', updatePriceStaffling);
        antalInput.addEventListener('change', updatePriceStaffling);
    });
</script>

<?php require_once 'includes/footer.php'; ?>
