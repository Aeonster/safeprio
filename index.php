<?php
header('Content-Type: text/html; charset=UTF-8');
require_once 'includes/config.php';

$pageTitle = __('welcome');
$pageDescription = __('hero_text');

require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1><?php echo __('hero_title'); ?> <span class="highlight"><?php echo __('hero_title_highlight'); ?></span></h1>
            <p><?php echo __('hero_text'); ?></p>
        </div>
    </div>
</section>

<!-- Kategorier -->
<section class="categories-section">
    <div class="container">
        <h2 class="section-title"><?php echo __('our_categories'); ?></h2>
        <div class="categories-grid">
            <a href="produkter.php?kategori=W" class="category-card category-varning">
                <div class="category-icon">⚠️</div>
                <h3><?php echo __('warning_signs'); ?></h3>
                <p><?php echo __('warning_desc'); ?></p>
            </a>
            <a href="produkter.php?kategori=M" class="category-card category-paabud">
                <div class="category-icon">🔵</div>
                <h3><?php echo __('mandatory_signs'); ?></h3>
                <p><?php echo __('mandatory_desc'); ?></p>
            </a>
            <a href="produkter.php?kategori=P" class="category-card category-forbud">
                <div class="category-icon">🚫</div>
                <h3><?php echo __('prohibition_signs'); ?></h3>
                <p><?php echo __('prohibition_desc'); ?></p>
            </a>
            <a href="produkter.php?kategori=F" class="category-card category-brand">
                <div class="category-icon">🧯</div>
                <h3><?php echo __('fire_symbols'); ?></h3>
                <p><?php echo __('fire_desc'); ?></p>
            </a>
            <a href="produkter.php?kategori=D" class="category-card category-fara">
                <div class="category-icon">☠️</div>
                <h3><?php echo __('hazard_symbols'); ?></h3>
                <p><?php echo __('hazard_desc'); ?></p>
            </a>
        </div>
    </div>
</section>

<!-- USP Section -->
<section class="usp-section">
    <div class="container">
        <div class="usp-grid">
            <div class="usp-item">
                <div class="usp-icon">🚚</div>
                <h3><?php echo __('fast_delivery'); ?></h3>
                <p><?php echo __('fast_delivery_desc'); ?></p>
            </div>
            <div class="usp-item">
                <div class="usp-icon">✅</div>
                <h3><?php echo __('high_quality'); ?></h3>
                <p><?php echo __('high_quality_desc'); ?></p>
            </div>
            <div class="usp-item">
                <div class="usp-icon">📋</div>
                <h3><?php echo __('standard_compliant'); ?></h3>
                <p><?php echo __('standard_compliant_desc'); ?></p>
            </div>
            <div class="usp-item">
                <div class="usp-icon">💬</div>
                <h3><?php echo __('personal_service'); ?></h3>
                <p><?php echo __('personal_service_desc'); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Utvalda produkter -->
<section class="featured-section">
    <div class="container">
        <h2 class="section-title"><?php echo __('popular_products'); ?></h2>
        <div class="products-grid" id="featuredProducts">
            <!-- Produkter laddas via JavaScript -->
            <p class="loading"><?php echo __('loading'); ?></p>
        </div>
        <div class="section-footer">
            <a href="produkter.php" class="btn btn-outline"><?php echo __('view_all_products'); ?></a>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2><?php echo __('need_help'); ?></h2>
            <p><?php echo __('help_text'); ?></p>
            <a href="kontakt.php" class="btn btn-primary btn-large"><?php echo __('contact_us'); ?></a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
