/**
 * Varsel Webbutik - JavaScript
 */

// ================================
// BILDGALLERI & LIGHTBOX
// ================================
let galleryImages = [];
let currentImageIndex = 0;

// Byt huvudbild när man klickar på thumbnail
function changeMainImage(btn) {
    const imageSrc = btn.dataset.image;
    const mainImage = document.getElementById('mainImage');
    
    if (mainImage && imageSrc) {
        mainImage.src = imageSrc;
        
        // Uppdatera aktiv thumbnail
        document.querySelectorAll('.thumbnail-btn').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        
        // Uppdatera current index för lightbox
        const thumbnails = document.querySelectorAll('.thumbnail-btn');
        thumbnails.forEach((t, index) => {
            if (t === btn) currentImageIndex = index;
        });
    }
}

// Öppna lightbox
function openLightbox(imageSrc) {
    const lightbox = document.getElementById('imageLightbox');
    const lightboxImg = document.getElementById('lightboxImage');
    
    if (lightbox && lightboxImg) {
        lightboxImg.src = imageSrc;
        lightbox.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}

// Stäng lightbox
function closeLightbox() {
    const lightbox = document.getElementById('imageLightbox');
    if (lightbox) {
        lightbox.classList.remove('open');
        document.body.style.overflow = '';
    }
}

// Navigera i lightbox
function navigateLightbox(direction) {
    if (galleryImages.length === 0) return;
    
    currentImageIndex += direction;
    
    // Wrap around
    if (currentImageIndex < 0) currentImageIndex = galleryImages.length - 1;
    if (currentImageIndex >= galleryImages.length) currentImageIndex = 0;
    
    const lightboxImg = document.getElementById('lightboxImage');
    if (lightboxImg) {
        lightboxImg.src = galleryImages[currentImageIndex];
    }
    
    // Uppdatera även huvudbild och aktiv thumbnail
    const mainImage = document.getElementById('mainImage');
    const thumbnails = document.querySelectorAll('.thumbnail-btn');
    
    if (mainImage) mainImage.src = galleryImages[currentImageIndex];
    thumbnails.forEach((t, i) => {
        t.classList.toggle('active', i === currentImageIndex);
    });
}

// Initiera galleri
function initGallery() {
    const thumbnails = document.querySelectorAll('.thumbnail-btn');
    const mainImageContainer = document.querySelector('.product-main-image');
    
    // Samla alla bilder
    galleryImages = [];
    thumbnails.forEach(t => {
        galleryImages.push(t.dataset.image);
    });
    
    // Klicka på huvudbild öppnar lightbox
    if (mainImageContainer) {
        mainImageContainer.addEventListener('click', () => {
            const mainImage = document.getElementById('mainImage');
            if (mainImage) openLightbox(mainImage.src);
        });
    }
    
    // Stäng lightbox med Escape eller klick utanför
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') navigateLightbox(-1);
        if (e.key === 'ArrowRight') navigateLightbox(1);
    });
    
    const lightbox = document.getElementById('imageLightbox');
    if (lightbox) {
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) closeLightbox();
        });
    }
}

// ================================
// KUNDVAGN (localStorage)
// ================================
const Cart = {
    key: 'varsel_cart',

    getItems() {
        const data = localStorage.getItem(this.key);
        return data ? JSON.parse(data) : [];
    },

    saveItems(items) {
        localStorage.setItem(this.key, JSON.stringify(items));
        this.updateUI();
    },

    addItem(product) {
        const items = this.getItems();
        // Använd article_number om det finns, annars id (bakåtkompatibilitet)
        const productKey = product.article_number || product.id;
        const existing = items.find(item => 
            (item.article_number || item.id) === productKey
        );

        if (existing) {
            existing.antal += product.antal;
        } else {
            items.push(product);
        }

        this.saveItems(items);
        const productName = product.product_name || product.namn;
        const msg = window.LANG ? window.LANG.added_to_cart : 'tillagd i kundvagnen';
        this.showNotification(`${productName} ${msg}`);
    },

    removeItem(index) {
        const items = this.getItems();
        items.splice(index, 1);
        this.saveItems(items);
    },

    updateQuantity(index, antal) {
        const items = this.getItems();
        if (antal > 0) {
            items[index].antal = antal;
        } else {
            items.splice(index, 1);
        }
        this.saveItems(items);
    },

    clear() {
        localStorage.removeItem(this.key);
        this.updateUI();
    },

    getTotal() {
        const items = this.getItems();
        const currency = window.CURRENT_CURRENCY || 'SEK';
        
        return items.reduce((sum, item) => {
            const price = currency === 'EUR' 
                ? (item.price_eur_1 || item.price_sek_1 || item.pris) 
                : (item.price_sek_1 || item.pris);
            return sum + (price * item.antal);
        }, 0);
    },

    getCount() {
        const items = this.getItems();
        return items.reduce((sum, item) => sum + item.antal, 0);
    },

    updateUI() {
        // Uppdatera räknare i header
        const countEl = document.getElementById('cartCount');
        if (countEl) {
            countEl.textContent = this.getCount();
        }

        // Uppdatera sidebar
        this.renderSidebar();

        // Uppdatera kassa-sida om den finns
        this.renderCheckout();
    },

    renderSidebar() {
        const contentEl = document.getElementById('cartContent');
        const footerEl = document.getElementById('cartFooter');
        const totalEl = document.getElementById('cartTotal');

        if (!contentEl) return;

        const items = this.getItems();
        const emptyText = window.LANG ? window.LANG.cart_empty : 'Din kundvagn är tom';

        if (items.length === 0) {
            contentEl.innerHTML = `<p class="cart-empty">${emptyText}</p>`;
            if (footerEl) footerEl.style.display = 'none';
            return;
        }

        let html = '';
        const currency = window.CURRENT_CURRENCY || 'SEK';
        
        items.forEach((item, index) => {
            // Stöd både nya och gamla strukturen
            const productName = item.product_name || item.namn;
            const productImage = item.symbol_image || item.bild;
            const productPrice = currency === 'EUR' 
                ? (item.price_eur_1 || item.price_sek_1 || item.pris)
                : (item.price_sek_1 || item.pris);
            const details = [item.storlek, item.material].filter(Boolean).join(' • ');
            
            html += `
                <div class="cart-item">
                    <div class="cart-item-image">
                        <img src="${SITE_URL}/uploads/${productImage || 'placeholder.svg'}" alt="${productName}">
                    </div>
                    <div class="cart-item-info">
                        <div class="cart-item-name">${productName}</div>
                        <div class="cart-item-details">${details}${details ? ' • ' : ''}${item.antal} st</div>
                        <div class="cart-item-price">${formatPrice(productPrice * item.antal)}</div>
                    </div>
                    <button class="cart-item-remove" onclick="Cart.removeItem(${index})">✕</button>
                </div>
            `;
        });

        contentEl.innerHTML = html;
        if (footerEl) footerEl.style.display = 'block';
        if (totalEl) totalEl.textContent = formatPrice(this.getTotal());
    },

    renderCheckout() {
        const summaryEl = document.getElementById('summaryItems');
        const totalsEl = document.getElementById('summaryTotals');

        if (!summaryEl) return;

        const items = this.getItems();
        const emptyText = window.LANG ? window.LANG.cart_empty : 'Din kundvagn är tom';

        if (items.length === 0) {
            summaryEl.innerHTML = `<p class="cart-empty">${emptyText}</p>`;
            if (totalsEl) totalsEl.style.display = 'none';
            return;
        }

        let html = '';
        const currency = window.CURRENT_CURRENCY || 'SEK';
        
        items.forEach(item => {
            const productName = item.product_name || item.namn;
            const productPrice = currency === 'EUR' 
                ? (item.price_eur_1 || item.price_sek_1 || item.pris)
                : (item.price_sek_1 || item.pris);
            const details = [item.storlek, item.material].filter(Boolean).join(', ');
            html += `
                <div class="summary-item">
                    <span class="summary-item-name">${productName} ${details ? '<small>(' + details + ')</small>' : ''}</span>
                    <span class="summary-item-qty">×${item.antal}</span>
                    <span class="summary-item-price">${formatPrice(productPrice * item.antal)}</span>
                </div>
            `;
        });

        summaryEl.innerHTML = html;

        const subtotal = this.getTotal();
        const vat = subtotal * 0.25;
        const total = subtotal + vat;

        if (totalsEl) {
            totalsEl.style.display = 'block';
            document.getElementById('summarySubtotal').textContent = formatPrice(subtotal);
            document.getElementById('summaryVat').textContent = formatPrice(vat);
            document.getElementById('summaryTotal').textContent = formatPrice(total);
        }
    },

    showNotification(message) {
        // Ta bort befintlig notification
        const existing = document.querySelector('.cart-notification');
        if (existing) existing.remove();

        // Skapa ny notification
        const div = document.createElement('div');
        div.className = 'cart-notification';
        div.innerHTML = `<span>✓</span> ${message}`;
        div.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #22c55e;
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            font-weight: 500;
            z-index: 9999;
            animation: slideIn 0.3s ease;
        `;

        document.body.appendChild(div);
        setTimeout(() => div.remove(), 3000);
    }
};

// ================================
// SITE URL & HELPERS
// ================================
const SITE_URL = window.SITE_URL || window.location.origin;

function formatPrice(price) {
    const currency = window.CURRENT_CURRENCY || 'SEK';
    const formatted = price.toLocaleString('sv-SE', { minimumFractionDigits: 2 });
    
    if (currency === 'EUR') {
        return '€' + formatted;
    } else {
        return formatted + ' kr';
    }
}

// ================================
// PRODUKTER
// ================================
function renderProducts(containerId, products) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const noProductsText = window.LANG ? window.LANG.no_products_found : 'Inga produkter hittades.';
    const exclVatText = window.LANG ? window.LANG.excl_vat : 'exkl. moms';

    if (products.length === 0) {
        container.innerHTML = `<p class="loading">${noProductsText}</p>`;
        return;
    }

    // Sortera produkter baserat på valt alternativ
    const sortSelect = document.getElementById('sortSelect');
    const sortValue = sortSelect ? sortSelect.value : 'symbol_asc';
    const lang = window.CURRENT_LANG || 'sv';
    
    products.sort((a, b) => {
        switch(sortValue) {
            case 'symbol_asc':
                return (a.symbol_code || '').localeCompare(b.symbol_code || '');
            case 'symbol_desc':
                return (b.symbol_code || '').localeCompare(a.symbol_code || '');
            case 'name_asc':
                const nameA_asc = lang === 'en' ? a.symbol_name_en : a.symbol_name_sv;
                const nameB_asc = lang === 'en' ? b.symbol_name_en : b.symbol_name_sv;
                return (nameA_asc || '').localeCompare(nameB_asc || '');
            case 'name_desc':
                const nameA_desc = lang === 'en' ? a.symbol_name_en : a.symbol_name_sv;
                const nameB_desc = lang === 'en' ? b.symbol_name_en : b.symbol_name_sv;
                return (nameB_desc || '').localeCompare(nameA_desc || '');
            default:
                return (a.symbol_code || '').localeCompare(b.symbol_code || '');
        }
    });

    let html = '';
    products.forEach(product => {
        // Länka till produktsida med artikelnummer (t.ex. VM_210-300_M-M003)
        const productUrl = `${SITE_URL}/produkt.php?artno=${product.article_number}`;
        // Visa symbolnamn som titel
        const lang = window.CURRENT_LANG || 'sv';
        const symbolName = lang === 'en' ? product.symbol_name_en : product.symbol_name_sv;
        
        // Använd symbol-bild från symbols mappen
        const symbolImage = product.symbol_images ? `symbols/${product.symbol_images}` : 'placeholder.svg';
        
        html += `
            <a href="${productUrl}" class="product-card">
                <div class="product-card-image">
                    <img src="${SITE_URL}/uploads/${symbolImage}" alt="${symbolName}">
                </div>
                <div class="product-card-pedestal">
                    <div class="pedestal-front">
                        <span class="product-card-category">${product.kategori_namn} - ${product.symbol_code}</span>
                        <h3 class="product-card-title">${symbolName}</h3>
                    </div>
                </div>
            </a>
        `;
    });

    container.innerHTML = html;

    // Uppdatera räknare
    const countEl = document.getElementById('productsCount');
    if (countEl) countEl.textContent = products.length;
}

// Hämta produkter från API (med fallback till demo-data)
async function loadProductsFromAPI(containerId, category = '', search = '', limit = 0) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    const loadingText = window.LANG ? window.LANG.loading : 'Laddar...';
    container.innerHTML = `<p class="loading">${loadingText}</p>`;
    
    const lang = window.CURRENT_LANG || 'sv';
    const currency = window.CURRENT_CURRENCY || 'SEK';
    let url = `${SITE_URL}/api/produkter.php?lang=${lang}&currency=${currency}`;
    if (category) url += `&kategori=${encodeURIComponent(category)}`;
    if (search) url += `&sok=${encodeURIComponent(search)}`;
    if (limit > 0) url += `&limit=${limit}`;
    
    try {
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success && data.products.length > 0) {
            renderProducts(containerId, data.products);
        } else {
            const noProductsText = window.LANG ? window.LANG.no_products_found : 'Inga produkter hittades.';
            container.innerHTML = `<p class="loading">${noProductsText}</p>`;
        }
    } catch (error) {
        console.error('API error:', error);
        const errorText = window.LANG ? window.LANG.error_loading : 'Kunde inte ladda produkter.';
        container.innerHTML = `<p class="loading">${errorText}</p>`;
    }
}

// ================================
// EVENT HANDLERS
// ================================
document.addEventListener('DOMContentLoaded', function() {
    // Initiera kundvagn
    Cart.updateUI();
    
    // Initiera bildgalleri (om på produktsida)
    initGallery();

    // Kundvagn sidebar toggle
    const cartButton = document.getElementById('cartButton');
    const cartSidebar = document.getElementById('cartSidebar');
    const cartClose = document.getElementById('cartClose');
    const cartOverlay = document.getElementById('cartOverlay');

    if (cartButton && cartSidebar) {
        cartButton.addEventListener('click', () => {
            cartSidebar.classList.add('open');
            cartOverlay.classList.add('open');
        });

        cartClose?.addEventListener('click', () => {
            cartSidebar.classList.remove('open');
            cartOverlay.classList.remove('open');
        });

        cartOverlay?.addEventListener('click', () => {
            cartSidebar.classList.remove('open');
            cartOverlay.classList.remove('open');
        });
    }

    // Mobil meny toggle
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mainNav = document.querySelector('.main-nav');

    if (mobileMenuToggle && mainNav) {
        mobileMenuToggle.addEventListener('click', () => {
            mainNav.classList.toggle('open');
        });
    }

    // Ladda produkter på startsidan
    const featuredProducts = document.getElementById('featuredProducts');
    if (featuredProducts) {
        loadProductsFromAPI('featuredProducts', '', '', 8);
    }

    // Ladda produkter på produktsidan
    const productsGrid = document.getElementById('productsGrid');
    if (productsGrid) {
        const category = window.currentCategory || '';
        const search = window.currentSearch || '';
        loadProductsFromAPI('productsGrid', category, search);
        
        // Sortering
        const sortSelect = document.getElementById('sortSelect');
        if (sortSelect) {
            sortSelect.addEventListener('change', () => {
                loadProductsFromAPI('productsGrid', category, search);
            });
        }
    }

    // Antal +/- knappar på produktsida
    const qtyMinus = document.querySelector('.qty-btn.minus');
    const qtyPlus = document.querySelector('.qty-btn.plus');
    const qtyInput = document.getElementById('antal');

    if (qtyMinus && qtyPlus && qtyInput) {
        qtyMinus.addEventListener('click', () => {
            const val = parseInt(qtyInput.value) || 1;
            if (val > 1) {
                qtyInput.value = val - 1;
                // Uppdatera prisstaffling om funktionen finns
                if (typeof updatePriceStaffling === 'function') {
                    updatePriceStaffling();
                }
            }
        });

        qtyPlus.addEventListener('click', () => {
            const val = parseInt(qtyInput.value) || 1;
            if (val < 999) {
                qtyInput.value = val + 1;
                // Uppdatera prisstaffling om funktionen finns
                if (typeof updatePriceStaffling === 'function') {
                    updatePriceStaffling();
                }
            }
        });
    }

    // Lägg till i kundvagn
    const productForm = document.getElementById('productForm');
    if (productForm && window.currentProduct) {
        productForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const storlek = document.getElementById('storlek')?.value || '';
            const material = document.getElementById('material')?.value || '';
            const antal = parseInt(document.getElementById('antal').value) || 1;

            // Stöd både nya och gamla strukturen
            Cart.addItem({
                article_number: window.currentProduct.article_number || null,
                id: window.currentProduct.id || null,
                product_name: window.currentProduct.product_name || window.currentProduct.namn,
                namn: window.currentProduct.product_name || window.currentProduct.namn,
                price_sek_1: window.currentProduct.prices_sek ? window.currentProduct.prices_sek[0] : window.currentProduct.pris,
                price_eur_1: window.currentProduct.prices_eur ? window.currentProduct.prices_eur[0] : null,
                pris: window.currentProduct.prices_sek ? window.currentProduct.prices_sek[0] : window.currentProduct.pris,
                symbol_image: window.currentProduct.symbol_image || window.currentProduct.bild,
                bild: window.currentProduct.symbol_image || window.currentProduct.bild,
                storlek: storlek,
                material: material,
                antal: antal
            });
        });
    }

    // Kassa-formulär
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const items = Cart.getItems();
            if (items.length === 0) {
                alert('Din kundvagn är tom!');
                return;
            }

            const formData = new FormData(checkoutForm);
            formData.append('produkter', JSON.stringify(items));
            formData.append('totalt', Cart.getTotal());

            try {
                const response = await fetch(`${SITE_URL}/api/order.php`, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    Cart.clear();
                    document.getElementById('orderNumber').textContent = result.ordernummer;
                    document.getElementById('confirmationModal').classList.add('open');
                    document.getElementById('modalOverlay').classList.add('open');
                } else {
                    alert('Något gick fel: ' + result.message);
                }
            } catch (error) {
                alert('Kunde inte skicka beställningen. Försök igen.');
                console.error(error);
            }
        });
    }

    // Kontaktformulär
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(contactForm);

            try {
                const response = await fetch(`${SITE_URL}/api/kontakt.php`, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    contactForm.style.display = 'none';
                    document.getElementById('contactSuccess').style.display = 'block';
                } else {
                    alert('Något gick fel: ' + result.message);
                }
            } catch (error) {
                alert('Kunde inte skicka meddelandet. Försök igen.');
                console.error(error);
            }
        });
    }
});

// CSS animation för notification
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
`;
document.head.appendChild(style);
