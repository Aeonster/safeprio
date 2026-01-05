<!DOCTYPE html>
<html lang="<?php echo CURRENT_LANG; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . COMPANY_NAME : COMPANY_NAME; ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? $pageDescription : __('hero_text'); ?>">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Spartan:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <!-- Logo -->
                <a href="<?php echo SITE_URL; ?>" class="logo">
                    <img src="<?php echo SITE_URL; ?>/images/logo.svg" alt="Varsel Logo" class="logo-image">
                </a>

                <!-- Navigation -->
                <nav class="main-nav">
                    <ul class="nav-list">
                        <li><a href="<?php echo SITE_URL; ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>"><?php echo __('home'); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>/produkter.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'produkter.php' ? 'active' : ''; ?>"><?php echo __('products'); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>/om-oss.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'om-oss.php' ? 'active' : ''; ?>"><?php echo __('about'); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>/kontakt.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'kontakt.php' ? 'active' : ''; ?>"><?php echo __('contact'); ?></a></li>
                    </ul>
                </nav>

                <!-- Språkväljare + Kundvagn -->
                <div class="header-actions">
                    <div class="settings-menu">
                        <a href="#" class="settings-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
                                <path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
                            </svg>
                        </a>
                        <div class="settings-dropdown">
                            <div class="settings-group">
                                <h4>Språk</h4>
                                <?php
                                // Behåll aktuella URL-parametrar vid språkbyte
                                $params_sv = $_GET;
                                $params_sv['lang'] = 'sv';
                                $params_en = $_GET;
                                $params_en['lang'] = 'en';
                                ?>
                                <a href="?<?php echo http_build_query($params_sv); ?>" class="settings-option <?php echo CURRENT_LANG === 'sv' ? 'active' : ''; ?>">Svenska</a>
                                <a href="?<?php echo http_build_query($params_en); ?>" class="settings-option <?php echo CURRENT_LANG === 'en' ? 'active' : ''; ?>">Engelska</a>
                            </div>
                            <div class="settings-group">
                                <h4>Valuta</h4>
                                <?php
                                // Behåll aktuella URL-parametrar vid valutabyte
                                $params_sek = $_GET;
                                $params_sek['currency'] = 'SEK';
                                $params_eur = $_GET;
                                $params_eur['currency'] = 'EUR';
                                ?>
                                <a href="?<?php echo http_build_query($params_sek); ?>" class="settings-option <?php echo CURRENT_CURRENCY === 'SEK' ? 'active' : ''; ?>">SEK</a>
                                <a href="?<?php echo http_build_query($params_eur); ?>" class="settings-option <?php echo CURRENT_CURRENCY === 'EUR' ? 'active' : ''; ?>">EUR</a>
                            </div>
                        </div>
                    </div>
                    <button class="cart-button" id="cartButton">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6.331 8h11.339a2 2 0 0 1 1.977 2.304l-1.255 8.152a3 3 0 0 1 -2.966 2.544h-6.852a3 3 0 0 1 -2.965 -2.544l-1.255 -8.152a2 2 0 0 1 1.977 -2.304z" />
                            <path d="M9 11v-5a3 3 0 0 1 6 0v5" />
                        </svg>
                        <span class="cart-count" id="cartCount">0</span>
                    </button>
                </div>

                <!-- Mobil meny-knapp -->
                <button class="mobile-menu-toggle" id="mobileMenuToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Kundvagns-sidebar -->
    <div class="cart-sidebar" id="cartSidebar">
        <div class="cart-sidebar-header">
            <h3><?php echo __('your_cart'); ?></h3>
            <button class="cart-close" id="cartClose">✕</button>
        </div>
        <div class="cart-sidebar-content" id="cartContent">
            <p class="cart-empty"><?php echo __('cart_empty'); ?></p>
        </div>
        <div class="cart-sidebar-footer" id="cartFooter" style="display: none;">
            <div class="cart-total">
                <span><?php echo __('total'); ?>:</span>
                <span id="cartTotal">0 kr</span>
            </div>
            <a href="<?php echo SITE_URL; ?>/kassa.php" class="btn btn-primary btn-block"><?php echo __('go_to_checkout'); ?></a>
        </div>
    </div>
    <div class="cart-overlay" id="cartOverlay"></div>

    <!-- Språkvariabler för JavaScript -->
    <script>
        window.SITE_URL = '<?php echo SITE_URL; ?>';
        window.LANG = {
            cart_empty: '<?php echo __('cart_empty'); ?>',
            total: '<?php echo __('total'); ?>',
            added_to_cart: '<?php echo __('added_to_cart'); ?>',
            loading: '<?php echo __('loading'); ?>',
            no_products_found: '<?php echo __('no_products_found'); ?>',
            excl_vat: '<?php echo __('excl_vat'); ?>'
        };
        window.CURRENT_LANG = '<?php echo CURRENT_LANG; ?>';
        window.CURRENT_CURRENCY = '<?php echo CURRENT_CURRENCY; ?>';
    </script>

    <main class="main-content">
