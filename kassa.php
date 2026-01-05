<?php
require_once 'includes/config.php';

$pageTitle = __('checkout');
$pageDescription = __('checkout');

require_once 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?php echo SITE_URL; ?>"><?php echo __('home'); ?></a>
            <span>/</span>
            <span><?php echo __('checkout'); ?></span>
        </nav>
        <h1><?php echo __('checkout'); ?></h1>
    </div>
</section>

<section class="checkout-section">
    <div class="container">
        <div class="checkout-grid">
            <!-- Beställningsformulär -->
            <div class="checkout-form-container">
                <form id="checkoutForm" class="checkout-form">
                    <!-- Företagsuppgifter -->
                    <div class="form-section">
                        <h2><?php echo __('company_info'); ?></h2>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="foretag"><?php echo __('company_name'); ?> *</label>
                                <input type="text" id="foretag" name="foretag" required>
                            </div>
                            <div class="form-group">
                                <label for="orgnr"><?php echo __('org_number'); ?></label>
                                <input type="text" id="orgnr" name="orgnr" placeholder="XXXXXX-XXXX">
                            </div>
                        </div>
                    </div>

                    <!-- Kontaktuppgifter -->
                    <div class="form-section">
                        <h2><?php echo __('contact_info'); ?></h2>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="fornamn"><?php echo __('first_name'); ?> *</label>
                                <input type="text" id="fornamn" name="fornamn" required>
                            </div>
                            <div class="form-group">
                                <label for="efternamn"><?php echo __('last_name'); ?> *</label>
                                <input type="text" id="efternamn" name="efternamn" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="email"><?php echo __('email'); ?> *</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="telefon"><?php echo __('phone'); ?> *</label>
                                <input type="tel" id="telefon" name="telefon" required>
                            </div>
                        </div>
                    </div>

                    <!-- Leveransadress -->
                    <div class="form-section">
                        <h2><?php echo __('delivery_address'); ?></h2>
                        <div class="form-group">
                            <label for="adress"><?php echo __('street_address'); ?> *</label>
                            <input type="text" id="adress" name="adress" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="postnr"><?php echo __('postal_code'); ?> *</label>
                                <input type="text" id="postnr" name="postnr" required>
                            </div>
                            <div class="form-group">
                                <label for="ort"><?php echo __('city'); ?> *</label>
                                <input type="text" id="ort" name="ort" required>
                            </div>
                        </div>
                    </div>

                    <!-- Övriga uppgifter -->
                    <div class="form-section">
                        <h2><?php echo __('other_info'); ?></h2>
                        <div class="form-group">
                            <label for="meddelande"><?php echo __('message_reference'); ?></label>
                            <textarea id="meddelande" name="meddelande" rows="3" placeholder="<?php echo __('message_placeholder'); ?>"></textarea>
                        </div>
                    </div>

                    <!-- Villkor -->
                    <div class="form-section">
                        <div class="form-group checkbox-group">
                            <label>
                                <input type="checkbox" name="villkor" id="villkor" required>
                                <?php echo __('accept_terms'); ?> <a href="villkor.php" target="_blank"><?php echo __('terms_conditions'); ?></a> *
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-large btn-block" id="submitOrder">
                        <?php echo __('send_order'); ?>
                    </button>
                </form>
            </div>

            <!-- Ordersammanfattning -->
            <div class="checkout-summary">
                <div class="summary-box">
                    <h2><?php echo __('your_order'); ?></h2>
                    <div class="summary-items" id="summaryItems">
                        <p class="cart-empty"><?php echo __('cart_empty'); ?></p>
                    </div>
                    <div class="summary-totals" id="summaryTotals" style="display: none;">
                        <div class="summary-row">
                            <span><?php echo __('subtotal'); ?>:</span>
                            <span id="summarySubtotal">0 kr</span>
                        </div>
                        <div class="summary-row">
                            <span><?php echo __('vat'); ?> (25%):</span>
                            <span id="summaryVat">0 kr</span>
                        </div>
                        <div class="summary-row total">
                            <span><?php echo __('total'); ?>:</span>
                            <span id="summaryTotal">0 kr</span>
                        </div>
                    </div>
                </div>

                <div class="summary-info">
                    <h3><?php echo __('information'); ?></h3>
                    <p><?php echo __('order_info'); ?></p>
                    <p><strong><?php echo __('shipping_info'); ?>:</strong> <?php echo __('shipping_terms'); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Bekräftelse-modal -->
<div class="modal" id="confirmationModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>✅ <?php echo __('thank_you'); ?></h2>
        </div>
        <div class="modal-body">
            <p><?php echo __('order_received'); ?></p>
            <p><strong><?php echo __('order_number'); ?>:</strong> <span id="orderNumber"></span></p>
        </div>
        <div class="modal-footer">
            <a href="<?php echo SITE_URL; ?>" class="btn btn-primary"><?php echo __('back_to_home'); ?></a>
        </div>
    </div>
</div>
<div class="modal-overlay" id="modalOverlay"></div>

<?php require_once 'includes/footer.php'; ?>
