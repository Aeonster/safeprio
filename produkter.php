<?php
header('Content-Type: text/html; charset=UTF-8');
require_once 'includes/config.php';

// Hämta kategori från URL
$kategori = isset($_GET['kategori']) ? sanitize($_GET['kategori']) : '';
$search = isset($_GET['sok']) ? sanitize($_GET['sok']) : '';

// Hämta kategorier som har BÅDE produktgrupper OCH symboler
$db = getDBConnection();
$stmt = $db->query("
    SELECT DISTINCT pg.symbol_category
    FROM product_groups pg
    INNER JOIN symbols s ON pg.symbol_category = s.category
    WHERE pg.active = 1 AND s.active = 1 AND pg.symbol_category IS NOT NULL
    ORDER BY pg.symbol_category
");
$kategorier = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Kategorinamn (mappning från symbol category)
$kategoriNamnMapping = [
    // VM-grupper (Varselmärkning)
    'M' => __('mandatory_signs'),
    'W' => __('warning_signs'),
    'P' => __('prohibition_signs'),
    'E' => __('emergency_signs'),
    'F' => __('fire_symbols'),
    'D' => __('hazard_symbols'),
    // RM-grupper (Rörmärkning)
    'B' => __('fire_protection'),
    'BG' => __('flammable_gases'),
    'BV' => __('flammable_liquids'),
    'FG' => __('corrosive_toxic'),
    'LV' => __('air_vacuum'),
    'V' => __('water'),
    'VA' => __('steam'),
    // Övriga
    'PM' => __('placement_signs'),
    'RC' => __('recycling_signs')
];

// Hierarkisk kategoristruktur
$huvudkategorier = [
    'varselmarkning' => [
        'namn' => 'Varselmärkning',
        'kategorier' => ['M', 'W', 'P', 'E', 'F', 'D'],
        'har_under' => true
    ],
    'placering' => [
        'namn' => 'Placering',
        'kategorier' => ['PM'],
        'har_under' => false
    ],
    'atervinning' => [
        'namn' => 'Återvinning',
        'kategorier' => ['RC'],
        'har_under' => false
    ],
    'rormarkning' => [
        'namn' => 'Rörmärkning',
        'kategorier' => ['B', 'BG', 'BV', 'FG', 'LV', 'V', 'VA'],
        'har_under' => true
    ]
];

$pageTitle = $kategori ? ($kategoriNamnMapping[$kategori] ?? __('products')) : __('all_products');
$pageDescription = __('products_page_desc');

require_once 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?php echo SITE_URL; ?>"><?php echo __('home'); ?></a>
            <span>/</span>
            <?php if ($kategori): ?>
                <a href="produkter.php"><?php echo __('products'); ?></a>
                <span>/</span>
                <span><?php echo $pageTitle; ?></span>
            <?php else: ?>
                <span><?php echo __('products'); ?></span>
            <?php endif; ?>
        </nav>
        <h1><?php echo $pageTitle; ?></h1>
    </div>
</section>

<section class="products-page">
    <div class="container">
        <div class="products-layout">
            <!-- Sidebar med filter -->
            <aside class="products-sidebar">
                <div class="filter-section">
                    <ul class="filter-list">
                        <li>
                            <a href="produkter.php" class="<?php echo !$kategori ? 'active' : ''; ?>">
                                <?php echo __('all_products'); ?>
                            </a>
                        </li>
                        <?php foreach ($huvudkategorier as $hkat): ?>
                            <?php 
                            // Kolla om någon av underkategorierna finns i databasen
                            $harKategorier = false;
                            $isActive = false;
                            foreach ($hkat['kategorier'] as $subkat) {
                                if (in_array($subkat, $kategorier)) {
                                    $harKategorier = true;
                                    if ($kategori === $subkat) $isActive = true;
                                }
                            }
                            if (!$harKategorier) continue;
                            
                            // Om kategorin inte har underkategorier, visa som direkt länk
                            if (!$hkat['har_under']): 
                                $direktKategori = $hkat['kategorier'][0];
                            ?>
                            <li>
                                <a href="produkter.php?kategori=<?php echo $direktKategori; ?>" 
                                   class="<?php echo $kategori === $direktKategori ? 'active' : ''; ?>">
                                    <?php echo $hkat['namn']; ?>
                                </a>
                            </li>
                            <?php else: ?>
                            <li class="category-group">
                                <div class="category-header <?php echo $isActive ? 'active' : ''; ?>">
                                    <?php echo $hkat['namn']; ?>
                                </div>
                                <ul class="sub-categories">
                                    <?php foreach ($hkat['kategorier'] as $subkat): ?>
                                        <?php if (in_array($subkat, $kategorier)): ?>
                                        <li>
                                            <a href="produkter.php?kategori=<?php echo $subkat; ?>" 
                                               class="<?php echo $kategori === $subkat ? 'active' : ''; ?>">
                                                <?php echo $kategoriNamnMapping[$subkat] ?? $subkat; ?>
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="filter-section">
                    <form action="produkter.php" method="GET" class="search-form">
                        <?php if ($kategori): ?>
                        <input type="hidden" name="kategori" value="<?php echo $kategori; ?>">
                        <?php endif; ?>
                        <input type="text" name="sok" placeholder="<?php echo __('search_placeholder'); ?>" value="<?php echo $search; ?>">
                        <button type="submit" class="btn btn-primary"><?php echo __('search'); ?></button>
                    </form>
                </div>
            </aside>

            <!-- Produkter -->
            <div class="products-main">
                <div class="products-toolbar">
                    <div class="products-count">
                        <span id="productsCount">0</span> <?php echo __('products_lowercase'); ?>
                    </div>
                    <div class="products-sort">
                        <label for="sortSelect"><?php echo __('sort_by'); ?>:</label>
                        <select id="sortSelect">
                            <option value="symbol_asc" selected><?php echo __('sort_symbol_asc'); ?></option>
                            <option value="symbol_desc"><?php echo __('sort_symbol_desc'); ?></option>
                            <option value="name_asc"><?php echo __('sort_name_asc'); ?></option>
                            <option value="name_desc"><?php echo __('sort_name_desc'); ?></option>
                        </select>
                    </div>
                </div>

                <div class="products-grid" id="productsGrid">
                    <p class="loading"><?php echo __('loading'); ?></p>
                </div>

                <!-- Pagination -->
                <div class="pagination" id="pagination">
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Skicka kategori och sök till JavaScript
    window.currentCategory = '<?php echo $kategori; ?>';
    window.currentSearch = '<?php echo $search; ?>';
</script>

<?php require_once 'includes/footer.php'; ?>
