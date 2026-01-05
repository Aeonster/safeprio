<?php
require_once 'includes/config.php';

$pageTitle = __('about');
$pageDescription = __('about_page_desc');

require_once 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?php echo SITE_URL; ?>"><?php echo __('home'); ?></a>
            <span>/</span>
            <span><?php echo __('about'); ?></span>
        </nav>
        <h1><?php echo __('about'); ?></h1>
    </div>
</section>

<section class="about-section">
    <div class="container">
        <div class="about-content">
            <div class="about-text">
                <h2><?php echo __('about_title'); ?></h2>
                <p><?php echo __('about_text_1'); ?></p>
                
                <p><?php echo __('about_text_2'); ?></p>

                <h3><?php echo __('our_values'); ?></h3>
                <ul class="values-list">
                    <li><strong><?php echo __('value_quality'); ?></strong> – <?php echo __('value_quality_desc'); ?></li>
                    <li><strong><?php echo __('value_service'); ?></strong> – <?php echo __('value_service_desc'); ?></li>
                    <li><strong><?php echo __('value_delivery'); ?></strong> – <?php echo __('value_delivery_desc'); ?></li>
                    <li><strong><?php echo __('value_knowledge'); ?></strong> – <?php echo __('value_knowledge_desc'); ?></li>
                </ul>
            </div>

            <div class="about-stats">
                <div class="stat-card">
                    <span class="stat-number">500+</span>
                    <span class="stat-label"><?php echo __('products_in_range'); ?></span>
                </div>
                <div class="stat-card">
                    <span class="stat-number">1000+</span>
                    <span class="stat-label"><?php echo __('satisfied_customers'); ?></span>
                </div>
                <div class="stat-card">
                    <span class="stat-number">2-5</span>
                    <span class="stat-label"><?php echo __('days_delivery'); ?></span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2><?php echo __('have_questions'); ?></h2>
            <p><?php echo __('help_find_right'); ?></p>
            <a href="kontakt.php" class="btn btn-primary btn-large"><?php echo __('contact_us'); ?></a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
