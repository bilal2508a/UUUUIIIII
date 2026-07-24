<?php
// Mehmaan Hub - Header / Navbar
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';

$user = currentUser();
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php', '.php');

// Support both 'name' and 'full_name' keys, with null-safe fallback
$_userDisplay = $user ? ($user['name'] ?? $user['full_name'] ?? 'User') : 'User';
$_userInitial = $_userDisplay ? strtoupper(substr($_userDisplay, 0, 1)) : 'U';
$_userFirst = $_userDisplay ? explode(' ', $_userDisplay)[0] : 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mehmaan Hub - Find Your Perfect Stay in Pakistan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="<?php echo url('/assets/css/style.css'); ?>" rel="stylesheet">
    <script>var SITE_URL = '<?php echo SITE_URL; ?>';</script>
</head>
<body>
<nav id="navbar" class="navbar-mh">
    <div class="container-app d-flex align-items-center justify-content-between" style="height: 72px;">
        <a href="<?php echo url('/index.php'); ?>" class="d-flex align-items-center gap-2 text-decoration-none">
            <div class="d-flex align-items-center justify-content-center" style="width:42px;height:42px;border-radius:13px;background:linear-gradient(135deg,var(--primary-600),var(--accent-500));color:#fff;font-size:1.3rem;box-shadow:0 6px 16px -4px rgba(26,82,245,0.4);">
                <i class="bi bi-buildings"></i>
            </div>
            <span style="font-size:1.3rem;font-weight:800;color:var(--slate-900);letter-spacing:-0.02em;">Mehmaan<span style="background:linear-gradient(135deg,var(--primary-600),var(--accent-500));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Hub</span></span>
        </a>

        <div class="d-none d-lg-flex align-items-center gap-1">
            <a href="<?php echo url('/index.php'); ?>" class="nav-link-mh <?php echo $currentPage === 'index' ? 'active' : ''; ?>">Home</a>
            <a href="<?php echo url('/properties.php'); ?>" class="nav-link-mh <?php echo $currentPage === 'properties' ? 'active' : ''; ?>">Properties</a>
            <a href="<?php echo url('/about.php'); ?>" class="nav-link-mh <?php echo $currentPage === 'about' ? 'active' : ''; ?>">About</a>
            <a href="<?php echo url('/contact.php'); ?>" class="nav-link-mh <?php echo $currentPage === 'contact' ? 'active' : ''; ?>">Contact</a>
        </div>

        <div class="d-none d-lg-flex align-items-center gap-2">
            <?php if ($user): ?>
                <?php if ($user['role'] === 'owner'): ?>
                    <a href="<?php echo url('/add-property.php'); ?>" class="btn btn-ghost btn-sm"><i class="bi bi-plus-circle"></i> Add Property</a>
                <?php endif; ?>
                <?php if ($user['role'] === 'admin'): ?>
                    <a href="<?php echo url('/admin.php'); ?>" class="btn btn-ghost btn-sm"><i class="bi bi-shield-check"></i> Admin Panel</a>
                <?php endif; ?>
                <a href="<?php echo url('/wishlist.php'); ?>" class="btn btn-ghost btn-sm"><i class="bi bi-heart"></i></a>
                <div class="position-relative">
                    <button onclick="toggleUserMenu()" class="btn btn-ghost d-flex align-items-center gap-2">
                        <div class="d-flex align-items-center justify-content-center" style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--primary-600),var(--accent-500));color:#fff;font-weight:700;font-size:0.9rem;box-shadow:0 4px 12px -2px rgba(26,82,245,0.35);">
                            <?php echo e($_userInitial); ?>
                        </div>
                        <span style="font-weight:600;font-size:0.9rem;color:var(--slate-700);"><?php echo e($_userFirst); ?></span>
                        <i class="bi bi-chevron-down" style="font-size:0.75rem;color:var(--slate-400);"></i>
                    </button>
                    <div id="userMenu" class="user-menu">
                        <div style="padding:0.625rem 0.875rem 0.5rem;border-bottom:1px solid var(--slate-100);margin-bottom:0.375rem;">
                            <div style="font-weight:700;color:var(--slate-900);font-size:0.85rem;"><?php echo e($_userDisplay); ?></div>
                            <div style="font-size:0.75rem;color:var(--slate-400);text-transform:capitalize;"><?php echo e($user['role']); ?> account</div>
                        </div>
                        <a href="<?php echo url('/profile.php'); ?>" class="dropdown-item-mh">
                            <i class="bi bi-person"></i> Profile
                        </a>
                        <?php if ($user['role'] === 'owner'): ?>
                            <a href="<?php echo url('/owner-dashboard.php'); ?>" class="dropdown-item-mh">
                                <i class="bi bi-speedometer2"></i> Owner Dashboard
                            </a>
                        <?php endif; ?>
                        <?php if ($user['role'] === 'admin'): ?>
                            <a href="<?php echo url('/admin.php'); ?>" class="dropdown-item-mh">
                                <i class="bi bi-shield-check"></i> Admin Dashboard
                            </a>
                        <?php endif; ?>
                        <?php if ($user['role'] === 'tenant'): ?>
                            <a href="<?php echo url('/dashboard.php'); ?>" class="dropdown-item-mh">
                                <i class="bi bi-speedometer2"></i> My Dashboard
                            </a>
                            <a href="<?php echo url('/bookings.php'); ?>" class="dropdown-item-mh">
                                <i class="bi bi-calendar-check"></i> My Bookings
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo url('/wishlist.php'); ?>" class="dropdown-item-mh">
                            <i class="bi bi-heart"></i> Wishlist
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="<?php echo url('/logout.php'); ?>" class="dropdown-item-mh text-error">
                            <i class="bi bi-box-arrow-right"></i> Sign Out
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?php echo url('/login.php'); ?>" class="btn btn-ghost">Sign In</a>
                <a href="<?php echo url('/register.php'); ?>" class="btn btn-primary">Get Started</a>
            <?php endif; ?>
        </div>

        <button onclick="toggleMobileMenu()" class="btn btn-ghost d-lg-none" aria-label="Menu">
            <i class="bi bi-list" style="font-size:1.5rem;"></i>
        </button>
    </div>

    <div id="mobileMenu" class="mobile-menu d-lg-none">
        <a href="<?php echo url('/index.php'); ?>" class="mobile-nav-link">Home</a>
        <a href="<?php echo url('/properties.php'); ?>" class="mobile-nav-link">Properties</a>
        <a href="<?php echo url('/about.php'); ?>" class="mobile-nav-link">About</a>
        <a href="<?php echo url('/contact.php'); ?>" class="mobile-nav-link">Contact</a>
        <?php if ($user): ?>
            <?php if ($user['role'] === 'owner'): ?>
                <a href="<?php echo url('/add-property.php'); ?>" class="mobile-nav-link">Add Property</a>
            <?php endif; ?>
            <?php if ($user['role'] === 'admin'): ?>
                <a href="<?php echo url('/admin.php'); ?>" class="mobile-nav-link">Admin Dashboard</a>
            <?php endif; ?>
            <a href="<?php echo url('/wishlist.php'); ?>" class="mobile-nav-link">Wishlist</a>
            <a href="<?php echo url('/profile.php'); ?>" class="mobile-nav-link">Profile</a>
            <a href="<?php echo url('/logout.php'); ?>" class="mobile-nav-link text-error">Sign Out</a>
        <?php else: ?>
            <a href="<?php echo url('/login.php'); ?>" class="mobile-nav-link">Sign In</a>
            <a href="<?php echo url('/register.php'); ?>" class="mobile-nav-link">Get Started</a>
        <?php endif; ?>
    </div>
</nav>

<?php if ($flash = flash('success')): ?>
<div class="alert alert-success" style="position:fixed;top:88px;left:50%;transform:translateX(-50%);z-index:9999;min-width:300px;max-width:90%;box-shadow:var(--shadow-xl);border-radius:var(--radius);padding:1rem 1.25rem;font-weight:600;" id="flashAlert">
    <i class="bi bi-check-circle"></i> <?php echo e($flash); ?>
</div>
<?php endif; ?>
<?php if ($flash = flash('error')): ?>
<div class="alert alert-danger" style="position:fixed;top:88px;left:50%;transform:translateX(-50%);z-index:9999;min-width:300px;max-width:90%;box-shadow:var(--shadow-xl);border-radius:var(--radius);padding:1rem 1.25rem;font-weight:600;" id="flashAlert">
    <i class="bi bi-exclamation-circle"></i> <?php echo e($flash); ?>
</div>
<?php endif; ?>

<main>
