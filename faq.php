<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

// 12 FAQ items across categories
$faqs = [
    ['category' => 'General', 'question' => 'What is Mehmaan Hub?', 'answer' => 'Mehmaan Hub is Pakistan\'s premier rental property platform that connects property owners with potential tenants. We offer verified listings, direct owner contact, and a seamless booking process across major cities.'],
    ['category' => 'General', 'question' => 'How do I get started?', 'answer' => 'Simply create an account as a tenant or owner. Tenants can browse and book properties, while owners can list their properties for rent. Registration is free and takes less than a minute.'],
    ['category' => 'Booking', 'question' => 'How do I book a property?', 'answer' => 'Browse available properties, select one you like, choose your dates, and submit a booking request. The owner will review and approve your request. Once approved, you can complete your payment to confirm the booking.'],
    ['category' => 'Booking', 'question' => 'Can I cancel my booking?', 'answer' => 'Yes, you can cancel a pending booking from your dashboard. If the booking is already confirmed, please contact the owner directly to discuss cancellation and any potential refunds.'],
    ['category' => 'Booking', 'question' => 'How long does owner approval take?', 'answer' => 'Owners are notified immediately of new booking requests. Most owners respond within a few hours, though it may take up to 24 hours depending on availability.'],
    ['category' => 'Payment', 'question' => 'What payment methods are accepted?', 'answer' => 'We accept credit/debit cards, mobile wallets (JazzCash, EasyPaisa, SadaPay), and bank transfers. All payments are processed securely through our encrypted payment gateway.'],
    ['category' => 'Payment', 'question' => 'Are there any hidden fees?', 'answer' => 'No. We believe in transparent pricing. The price you see on the listing is the price you pay. There are no booking fees, service charges, or hidden costs added at checkout.'],
    ['category' => 'Payment', 'question' => 'Do you offer any discounts?', 'answer' => 'Yes! We offer coupon codes like EARLY20 (20% off), STAY7 (15% off), FAMILY4 (10% off), and WELCOME10 (10% off for new users). Apply these at checkout to receive your discount.'],
    ['category' => 'Properties', 'question' => 'How do I list my property?', 'answer' => 'Create an owner account, then click "Add Property" on your dashboard. Fill in the property details, upload photos, set your price, and publish. Your listing will be visible to thousands of potential tenants immediately.'],
    ['category' => 'Properties', 'question' => 'Are property listings verified?', 'answer' => 'Yes, all listings go through a verification process to ensure authenticity. We verify property ownership and check that listings meet our quality standards before they appear publicly.'],
    ['category' => 'Account', 'question' => 'I forgot my password. What do I do?', 'answer' => 'Click on "Forgot password?" on the login page. Enter your email or phone number, and we\'ll send you a 6-digit OTP code valid for 10 minutes. Use it to reset your password securely.'],
    ['category' => 'Account', 'question' => 'How do I change my account type?', 'answer' => 'Currently, account types (tenant/owner) cannot be changed after registration. If you need to switch, please create a new account with a different email, or contact our support team for assistance.'],
];

$categories = array_unique(array_column($faqs, 'category'));

include __DIR__ . '/includes/header.php';
?>

<div style="background:linear-gradient(135deg,#0ea5e9,#14b8a6);padding:3rem 0;color:#fff;">
    <div class="container-app">
        <h1 style="font-size:2.25rem;font-weight:800;margin:0;letter-spacing:-0.02em;">Frequently Asked Questions</h1>
        <p style="margin:0.5rem 0 0;opacity:0.95;font-size:1.05rem;">Find answers to common questions</p>
    </div>
</div>

<section style="padding:3rem 0;">
    <div class="container-app">
        <div style="max-width:800px;margin:0 auto;">
            <!-- Search -->
            <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:1.5rem;margin-bottom:2rem;">
                <div style="position:relative;">
                    <i class="bi bi-search" style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:#94a3b8;"></i>
                    <input type="text" id="faqSearch" placeholder="Search questions..." onkeyup="filterFAQs()" style="width:100%;padding:0.85rem 1rem 0.85rem 2.75rem;border:1.5px solid #e2e8f0;border-radius:12px;font-size:1rem;outline:none;" onfocus="this.style.borderColor='#0ea5e9'" onblur="this.style.borderColor='#e2e8f0'">
                </div>
                <!-- Category Filter -->
                <div style="display:flex;gap:0.5rem;margin-top:1rem;flex-wrap:wrap;">
                    <button class="btn btn-primary btn-sm faq-cat-btn active" data-category="all" onclick="filterByCategory('all')">All</button>
                    <?php foreach ($categories as $cat): ?>
                        <button class="btn btn-ghost btn-sm faq-cat-btn" data-category="<?php echo e($cat); ?>" onclick="filterByCategory('<?php echo e($cat); ?>')"><?php echo e($cat); ?></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Accordion -->
            <div class="accordion" id="faqAccordion">
                <?php foreach ($faqs as $i => $faq): ?>
                <div class="card faq-item" data-category="<?php echo e($faq['category']); ?>" data-question="<?php echo e(strtolower($faq['question'])); ?>" data-answer="<?php echo e(strtolower($faq['answer'])); ?>" style="border:none;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.05);margin-bottom:0.75rem;overflow:hidden;">
                    <div style="padding:0;">
                        <button class="btn btn-ghost" style="width:100%;text-align:left;padding:1.25rem 1.5rem;display:flex;justify-content:space-between;align-items:center;border-radius:0;" type="button" onclick="toggleFAQ(<?php echo $i; ?>)">
                            <span style="font-weight:700;color:#0f172a;font-size:1rem;"><?php echo e($faq['question']); ?></span>
                            <i class="bi bi-chevron-down" id="faq-icon-<?php echo $i; ?>" style="color:#0ea5e9;transition:transform 0.2s;"></i>
                        </button>
                    </div>
                    <div id="faq-body-<?php echo $i; ?>" style="display:none;padding:0 1.5rem 1.25rem;color:#64748b;line-height:1.6;">
                        <?php echo e($faq['answer']); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- No results -->
            <div id="noResults" style="display:none;text-align:center;padding:3rem 2rem;color:#64748b;">
                <i class="bi bi-search" style="font-size:3rem;color:#cbd5e1;"></i>
                <h4 style="margin-top:1rem;color:#0f172a;">No matching questions</h4>
                <p>Try a different search term or category.</p>
            </div>

            <!-- Still have questions -->
            <div class="card" style="border:none;border-radius:16px;padding:2rem;margin-top:2rem;background:linear-gradient(135deg,#0ea5e9,#14b8a6);color:#fff;text-align:center;">
                <h4 style="font-weight:700;margin:0 0 0.5rem;">Still have questions?</h4>
                <p style="opacity:0.95;margin:0 0 1rem;">Our support team is here to help you.</p>
                <a href="<?php echo url('/contact.php'); ?>" class="btn btn-light" style="background:#fff;color:#0ea5e9;font-weight:700;border:none;border-radius:10px;"><i class="bi bi-envelope"></i> Contact Us</a>
            </div>
        </div>
    </div>
</section>

<script>
var currentCategory = 'all';

function toggleFAQ(i) {
    var body = document.getElementById('faq-body-' + i);
    var icon = document.getElementById('faq-icon-' + i);
    if (body.style.display === 'none') {
        body.style.display = 'block';
        icon.style.transform = 'rotate(180deg)';
    } else {
        body.style.display = 'none';
        icon.style.transform = 'rotate(0deg)';
    }
}

function filterByCategory(category) {
    currentCategory = category;
    document.querySelectorAll('.faq-cat-btn').forEach(function(b) {
        b.classList.remove('active');
        b.classList.remove('btn-primary');
        b.classList.add('btn-ghost');
    });
    event.target.classList.add('active');
    event.target.classList.remove('btn-ghost');
    event.target.classList.add('btn-primary');
    filterFAQs();
}

function filterFAQs() {
    var search = document.getElementById('faqSearch').value.toLowerCase();
    var items = document.querySelectorAll('.faq-item');
    var visibleCount = 0;
    items.forEach(function(item) {
        var category = item.getAttribute('data-category');
        var question = item.getAttribute('data-question');
        var answer = item.getAttribute('data-answer');
        var matchesCategory = currentCategory === 'all' || category === currentCategory;
        var matchesSearch = !search || question.indexOf(search) !== -1 || answer.indexOf(search) !== -1;
        if (matchesCategory && matchesSearch) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    document.getElementById('noResults').style.display = visibleCount === 0 ? 'block' : 'none';
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
