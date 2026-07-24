// Mehmaan Hub - Main JavaScript

// Navbar scroll effect
window.addEventListener('scroll', function() {
    var navbar = document.getElementById('navbar');
    if (navbar) {
        if (window.scrollY > 20) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }
});

// Toggle mobile menu
function toggleMobileMenu() {
    var menu = document.getElementById('mobileMenu');
    if (menu) menu.classList.toggle('show');
}

// Toggle user menu
function toggleUserMenu() {
    var menu = document.getElementById('userMenu');
    if (menu) menu.classList.toggle('show');
}

// Close user menu on outside click
document.addEventListener('click', function(e) {
    var userMenu = document.getElementById('userMenu');
    if (userMenu && userMenu.classList.contains('show')) {
        var btn = e.target.closest('button[onclick="toggleUserMenu()"]');
        if (!btn && !userMenu.contains(e.target)) {
            userMenu.classList.remove('show');
        }
    }
});

// FAQ accordion
document.addEventListener('click', function(e) {
    var toggle = e.target.closest('.faq-toggle');
    if (toggle) {
        e.preventDefault();
        var answer = toggle.nextElementSibling;
        var icon = toggle.querySelector('.faq-icon');
        if (answer) answer.classList.toggle('open');
        if (icon) {
            icon.classList.toggle('bi-chevron-down');
            icon.classList.toggle('bi-chevron-up');
        }
    }
});

// Image gallery - set active image
function setActiveImage(src) {
    var mainImage = document.getElementById('mainImage');
    if (mainImage) mainImage.src = src;
    var thumbs = document.querySelectorAll('.gallery-thumb');
    thumbs.forEach(function(t) {
        t.classList.remove('active');
        if (t.getAttribute('src') === src) t.classList.add('active');
    });
}

// Toggle checklist item
function toggleChecklistItem(el) {
    el.classList.toggle('checked');
    var total = document.querySelectorAll('.checklist-item').length;
    var checked = document.querySelectorAll('.checklist-item.checked').length;
    var progressBar = document.getElementById('checklistProgress');
    var progressText = document.getElementById('checklistProgressText');
    if (progressBar) {
        var pct = total > 0 ? (checked / total) * 100 : 0;
        progressBar.style.width = pct + '%';
    }
    if (progressText) {
        progressText.textContent = checked + ' / ' + total;
    }
}

// Toggle wishlist via AJAX
function toggleWishlist(e, propertyId) {
    e.preventDefault();
    e.stopPropagation();
    fetch(SITE_URL + '/api/wishlist.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'property_id=' + propertyId
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            var btn = e.target.closest('button');
            if (btn) {
                var icon = btn.querySelector('i');
                var text = btn.querySelector('span');
                if (data.action === 'added') {
                    if (icon) icon.className = 'bi bi-heart-fill';
                    if (text) text.textContent = ' Remove from Wishlist';
                    btn.classList.remove('btn-ghost');
                    btn.classList.add('btn-error');
                } else {
                    if (icon) icon.className = 'bi bi-heart';
                    if (text) text.textContent = ' Add to Wishlist';
                    btn.classList.remove('btn-error');
                    btn.classList.add('btn-ghost');
                }
            }
        }
    });
}

// Coupon codes
var coupons = {'EARLY20':0.20,'STAY7':0.15,'FAMILY4':0.10,'WELCOME10':0.10};
var appliedDiscount = 0;

function applyCoupon() {
    var input = document.getElementById('couponCode');
    var msg = document.getElementById('couponMessage');
    if (!input) return;
    var code = input.value.trim().toUpperCase();
    if (coupons.hasOwnProperty(code)) {
        appliedDiscount = coupons[code];
        if (msg) { msg.textContent = 'Coupon "' + code + '" applied! ' + (appliedDiscount * 100) + '% off'; msg.className = 'text-success'; }
    } else {
        appliedDiscount = 0;
        if (msg) { msg.textContent = 'Invalid coupon code'; msg.className = 'text-error'; }
    }
    recalculateTotal();
}

function recalculateTotal() {
    var priceEl = document.getElementById('pricePerNight');
    var checkInEl = document.getElementById('checkIn');
    var checkOutEl = document.getElementById('checkOut');
    if (!priceEl || !checkInEl || !checkOutEl) return;
    var price = parseFloat(priceEl.value) || 0;
    var nights = 0;
    if (checkInEl.value && checkOutEl.value) {
        nights = Math.round((new Date(checkOutEl.value) - new Date(checkInEl.value)) / 86400000);
        if (nights < 0) nights = 0;
    }
    var subtotal = price * nights;
    var serviceFee = subtotal * 0.05;
    var discount = subtotal * appliedDiscount;
    var total = subtotal + serviceFee - discount;
    var nightsEl = document.getElementById('nightsCount');
    if (nightsEl) nightsEl.textContent = nights;
    var subtotalEl = document.getElementById('subtotalAmount');
    if (subtotalEl) subtotalEl.textContent = 'Rs ' + subtotal.toLocaleString();
    var feeEl = document.getElementById('serviceFeeAmount');
    if (feeEl) feeEl.textContent = 'Rs ' + serviceFee.toLocaleString();
    var discountEl = document.getElementById('discountAmount');
    if (discountEl) discountEl.textContent = 'Rs ' + discount.toLocaleString();
    var totalEl = document.getElementById('totalAmount');
    if (totalEl) totalEl.textContent = 'Rs ' + total.toLocaleString();
    var hiddenTotal = document.getElementById('hiddenTotal');
    if (hiddenTotal) hiddenTotal.value = total;
}

// FAQ search
function searchFAQ() {
    var query = document.getElementById('faqSearch') ? document.getElementById('faqSearch').value.toLowerCase() : '';
    var items = document.querySelectorAll('.faq-item');
    items.forEach(function(item) {
        var text = item.textContent.toLowerCase();
        item.style.display = text.includes(query) ? '' : 'none';
    });
}

// FAQ category filter
function filterFAQ(category) {
    var items = document.querySelectorAll('.faq-item');
    var btns = document.querySelectorAll('.faq-category-btn');
    btns.forEach(function(b) { b.classList.remove('active'); });
    if (event && event.target) event.target.classList.add('active');
    items.forEach(function(item) {
        if (category === 'all' || item.getAttribute('data-category') === category) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

// Toggle amenity selection (for add-property)
function toggleAmenity(el) {
    el.classList.toggle('selected');
}

// Toggle daily price field visibility
function toggleDailyPrice() {
    var period = document.getElementById('price_period');
    var dailyGroup = document.getElementById('pricePerDayGroup');
    var priceHint = document.getElementById('priceHint');
    var priceInput = document.getElementById('price');
    if (!period) return;
    var val = period.value;
    if (val === 'both') {
        if (dailyGroup) dailyGroup.style.display = 'block';
        if (priceHint) priceHint.textContent = 'Monthly rent amount';
        if (priceInput) priceInput.placeholder = 'e.g. 50000 (monthly)';
    } else if (val === 'per_day') {
        if (dailyGroup) dailyGroup.style.display = 'none';
        if (priceHint) priceHint.textContent = 'Daily rent amount';
        if (priceInput) priceInput.placeholder = 'e.g. 2000 (daily)';
    } else {
        if (dailyGroup) dailyGroup.style.display = 'none';
        if (priceHint) priceHint.textContent = 'Monthly rent amount';
        if (priceInput) priceInput.placeholder = 'e.g. 50000 (monthly)';
    }
}

// Image upload preview
function previewImages(input, previewId) {
    var preview = document.getElementById(previewId);
    if (!preview || !input.files) return;
    preview.innerHTML = '';
    Array.from(input.files).forEach(function(file) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var img = document.createElement('img');
            img.src = e.target.result;
            img.style.cssText = 'width:80px;height:80px;object-fit:cover;border-radius:10px;';
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}
