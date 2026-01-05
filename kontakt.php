<?php
require_once 'includes/config.php';

$pageTitle = __('contact');
$pageDescription = __('contact_page_desc');

require_once 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?php echo SITE_URL; ?>"><?php echo __('home'); ?></a>
            <span>/</span>
            <span><?php echo __('contact'); ?></span>
        </nav>
        <h1><?php echo __('contact_us'); ?></h1>
    </div>
</section>

<section class="contact-section">
    <div class="container">
        <div class="contact-grid">
            <!-- Kontaktformulär -->
            <div class="contact-form-container">
                <h2><?php echo __('send_message'); ?></h2>
                <form id="contactForm" class="contact-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="namn"><?php echo __('name'); ?> *</label>
                            <input type="text" id="namn" name="namn" required>
                        </div>
                        <div class="form-group">
                            <label for="foretag"><?php echo __('company_name'); ?></label>
                            <input type="text" id="foretag" name="foretag">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email"><?php echo __('email'); ?> *</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="telefon"><?php echo __('phone'); ?></label>
                            <input type="tel" id="telefon" name="telefon">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="amne"><?php echo __('subject'); ?> *</label>
                        <select id="amne" name="amne" required>
                            <option value=""><?php echo __('choose_subject'); ?></option>
                            <option value="produktfråga"><?php echo __('subject_product'); ?></option>
                            <option value="offert"><?php echo __('subject_quote'); ?></option>
                            <option value="order"><?php echo __('subject_order'); ?></option>
                            <option value="reklamation"><?php echo __('subject_complaint'); ?></option>
                            <option value="ovrigt"><?php echo __('subject_other'); ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="meddelande"><?php echo __('message'); ?> *</label>
                        <textarea id="meddelande" name="meddelande" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo __('send'); ?></button>
                </form>
                <div class="form-success" id="contactSuccess" style="display: none;">
                    <p>✅ <?php echo __('message_sent'); ?></p>
                </div>
            </div>

            <!-- Kontaktinfo -->
            <div class="contact-info">
                <div class="info-card">
                    <h3><?php echo __('contact_details'); ?></h3>
                    <ul class="contact-list">
                        <li>
                            <span class="icon">📍</span>
                            <div>
                                <strong><?php echo __('visit_address'); ?></strong><br>
                                <?php echo COMPANY_ADDRESS; ?>
                            </div>
                        </li>
                        <li>
                            <span class="icon">📞</span>
                            <div>
                                <strong><?php echo __('phone'); ?></strong><br>
                                <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', COMPANY_PHONE); ?>"><?php echo COMPANY_PHONE; ?></a>
                            </div>
                        </li>
                        <li>
                            <span class="icon">✉️</span>
                            <div>
                                <strong><?php echo __('email'); ?></strong><br>
                                <a href="mailto:<?php echo COMPANY_EMAIL; ?>"><?php echo COMPANY_EMAIL; ?></a>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="info-card">
                    <h3><?php echo __('opening_hours'); ?></h3>
                    <table class="hours-table">
                        <tr>
                            <td><?php echo __('monday_friday'); ?></td>
                            <td>08:00 - 17:00</td>
                        </tr>
                        <tr>
                            <td><?php echo __('saturday_sunday'); ?></td>
                            <td><?php echo __('closed'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
