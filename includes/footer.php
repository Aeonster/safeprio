    </main>

    <footer class="main-footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Om företaget -->
                <div class="footer-section">
                    <h4><?php echo __('about_company'); ?> <?php echo COMPANY_NAME; ?></h4>
                    <p><?php echo __('footer_about'); ?></p>
                </div>

                <!-- Kategorier -->
                <div class="footer-section">
                    <h4><?php echo __('categories'); ?></h4>
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>/produkter.php?kategori=varning"><?php echo __('warning_signs'); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>/produkter.php?kategori=paabud"><?php echo __('mandatory_signs'); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>/produkter.php?kategori=forbud"><?php echo __('prohibition_signs'); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>/produkter.php?kategori=brand"><?php echo __('fire_symbols'); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>/produkter.php?kategori=fara"><?php echo __('hazard_symbols'); ?></a></li>
                    </ul>
                </div>

                <!-- Kundservice -->
                <div class="footer-section">
                    <h4><?php echo __('customer_service'); ?></h4>
                    <ul>
                        <li><a href="<?php echo SITE_URL; ?>/om-oss.php"><?php echo __('about'); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>/kontakt.php"><?php echo __('contact'); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>/leverans.php"><?php echo __('delivery_information'); ?></a></li>
                        <li><a href="<?php echo SITE_URL; ?>/villkor.php"><?php echo __('terms_conditions'); ?></a></li>
                    </ul>
                </div>

                <!-- Kontakt -->
                <div class="footer-section">
                    <h4><?php echo __('contact_us'); ?></h4>
                    <ul class="contact-list">
                        <li>📍 <?php echo COMPANY_ADDRESS; ?></li>
                        <li>📞 <?php echo COMPANY_PHONE; ?></li>
                        <li>✉️ <?php echo COMPANY_EMAIL; ?></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo COMPANY_NAME; ?>. <?php echo __('all_rights_reserved'); ?></p>
            </div>
        </div>
    </footer>

    <!-- Lightbox för produktbilder -->
    <div class="image-lightbox" id="imageLightbox">
        <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
        <button class="lightbox-nav lightbox-prev" onclick="navigateLightbox(-1)">&#10094;</button>
        <img src="" alt="" id="lightboxImage">
        <button class="lightbox-nav lightbox-next" onclick="navigateLightbox(1)">&#10095;</button>
    </div>

    <script src="<?php echo SITE_URL; ?>/js/app.js"></script>
</body>
</html>
