<?php
// Mehmaan Hub - Footer
?>
</main>

<footer class="footer-mh">
    <div class="container-app">
        <div class="row g-4 py-5">
            <div class="col-lg-4">
                <a href="<?php echo url('/index.php'); ?>" class="d-flex align-items-center gap-2 text-decoration-none mb-3">
                    <div class="d-flex align-items-center justify-content-center" style="width:42px;height:42px;border-radius:13px;background:linear-gradient(135deg,var(--primary-600),var(--accent-500));color:#fff;font-size:1.3rem;box-shadow:0 6px 16px -4px rgba(26,82,245,0.4);">
                        <i class="bi bi-buildings"></i>
                    </div>
                    <span style="font-size:1.3rem;font-weight:800;color:#fff;letter-spacing:-0.02em;">Mehmaan<span style="background:linear-gradient(135deg,var(--primary-400),var(--accent-400));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Hub</span></span>
                </a>
                <p style="color:var(--slate-400);font-size:0.9rem;line-height:1.7;max-width:340px;">
                    Pakistan's premier property booking platform. Find your perfect stay with verified listings from trusted owners.
                </p>
                <div class="d-flex gap-2 mt-3">
                    <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="social-icon"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <h5 class="footer-title">Company</h5>
                <a href="<?php echo url('/about.php'); ?>" class="footer-link">About</a>
                <a href="<?php echo url('/contact.php'); ?>" class="footer-link">Contact</a>
                <a href="<?php echo url('/faq.php'); ?>" class="footer-link">FAQ</a>
            </div>
            <div class="col-6 col-lg-3">
                <h5 class="footer-title">Properties</h5>
                <a href="<?php echo url('/properties.php'); ?>" class="footer-link">All Properties</a>
                <a href="<?php echo url('/properties.php?type=apartment'); ?>" class="footer-link">Apartments</a>
                <a href="<?php echo url('/properties.php?type=house'); ?>" class="footer-link">Houses</a>
                <a href="<?php echo url('/properties.php?type=villa'); ?>" class="footer-link">Villas</a>
            </div>
            <div class="col-6 col-lg-3">
                <h5 class="footer-title">Account</h5>
                <a href="<?php echo url('/login.php'); ?>" class="footer-link">Sign In</a>
                <a href="<?php echo url('/register.php'); ?>" class="footer-link">Register</a>
                <a href="<?php echo url('/become-host.php'); ?>" class="footer-link">Become a Host</a>
            </div>
        </div>
        <hr style="border-color:rgba(255,255,255,0.08);margin:0;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center py-3 gap-2">
            <p style="color:var(--slate-500);font-size:0.85rem;margin:0;">&copy; <?php echo date('Y'); ?> Mehmaan Hub. All rights reserved.</p>
            <div class="d-flex gap-3">
                <span style="color:var(--slate-400);font-size:0.85rem;"><i class="bi bi-envelope"></i> info@mehmaanhub.com</span>
                <span style="color:var(--slate-400);font-size:0.85rem;"><i class="bi bi-phone"></i> +92 300 1234567</span>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo url('/assets/js/main.js'); ?>"></script>
<script>
if (document.getElementById('flashAlert')) {
    setTimeout(function() {
        var el = document.getElementById('flashAlert');
        el.style.transition = 'opacity 0.3s, transform 0.3s';
        el.style.opacity = '0';
        el.style.transform = 'translateX(-50%) translateY(-10px)';
        setTimeout(function() { el.remove(); }, 300);
    }, 4000);
}
</script>
</body>
</html>
