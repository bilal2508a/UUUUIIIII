<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$user = currentUser();

// Featured properties (featured=1)
$featuredProperties = get_all_properties(6);
// Top-rated properties (by avg rating)
$topRated = [];
try {
    $stmt = db()->prepare("SELECT p.*, u.name as owner_name,
        (SELECT AVG(rating) FROM reviews WHERE property_id = p.id) as avg_rating,
        (SELECT COUNT(*) FROM reviews WHERE property_id = p.id) as review_count,
        (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as primary_image
        FROM properties p JOIN users u ON p.owner_id = u.id
        WHERE p.status = 'available'
        ORDER BY avg_rating DESC, p.created_at DESC LIMIT 6");
    $stmt->execute();
    $topRated = $stmt->fetchAll();
} catch (Exception $e) {
    $topRated = [];
}

// Stats
$totalProperties = count(get_all_properties());
$ownerCount = 0;
try {
    $ownerCount = (int)db()->query("SELECT COUNT(*) as c FROM users WHERE role = 'owner'")->fetch()['c'];
} catch (Exception $e) {}
$bookingCount = 0;
try {
    $bookingCount = (int)db()->query("SELECT COUNT(*) as c FROM bookings")->fetch()['c'];
} catch (Exception $e) {}
$cities = getCities();

include __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section style="position:relative;padding:8rem 0 6rem;overflow:hidden;background-image:url('https://images.pexels.com/photos/323780/pexels-photo-323780.jpeg?auto=compress&cs=tinysrgb&w=1600');background-size:cover;background-position:center;">
    <div style="position:absolute;inset:0;background:linear-gradient(180deg,rgba(13,26,77,0.55) 0%,rgba(13,26,77,0.35) 50%,rgba(13,26,77,0.65) 100%);"></div>
    <div class="container-app" style="position:relative;z-index:2;text-align:center;color:#fff;">
        <span class="badge" style="background:rgba(255,255,255,0.15);backdrop-filter:blur(12px);color:#fff;border:1px solid rgba(255,255,255,0.25);padding:0.5rem 1.125rem;border-radius:999px;font-weight:600;font-size:0.85rem;display:inline-flex;align-items:center;gap:0.4rem;animation:fadeInUp 0.5s ease;">
            <i class="bi bi-stars" style="color:var(--gold-300);"></i> Pakistan's #1 Rental Platform
        </span>
        <h1 style="font-size:clamp(2.5rem,5vw,4.25rem);font-weight:800;line-height:1.1;margin:1.5rem 0 1rem;letter-spacing:-0.03em;animation:fadeInUp 0.6s ease 0.1s both;">
            Find Your Perfect <span style="background:linear-gradient(135deg,var(--gold-300),var(--gold-500));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Rental Home</span>
        </h1>
        <p style="font-size:1.15rem;max-width:640px;margin:0 auto 2.5rem;opacity:0.92;line-height:1.6;animation:fadeInUp 0.6s ease 0.2s both;">
            Discover verified rental properties from trusted owners across Pakistan. Apartments, houses, rooms, and more.
        </p>
        <form method="GET" action="<?php echo url('/properties.php'); ?>" class="glass" style="max-width:680px;margin:0 auto;display:flex;gap:0.5rem;padding:0.625rem;border-radius:var(--radius-lg);align-items:center;flex-wrap:wrap;box-shadow:0 20px 60px -12px rgba(0,0,0,0.3);animation:fadeInUp 0.6s ease 0.3s both;">
            <div style="flex:1;min-width:200px;display:flex;align-items:center;gap:0.625rem;background:#fff;border-radius:var(--radius);padding:0.625rem 0.875rem;">
                <i class="bi bi-search" style="color:var(--slate-400);font-size:1.1rem;"></i>
                <input type="text" name="search" placeholder="Search by title, city, or address..." style="border:none;outline:none;flex:1;font-size:0.95rem;background:transparent;font-family:var(--font-sans);color:var(--slate-900);">
            </div>
            <div style="min-width:160px;display:flex;align-items:center;gap:0.625rem;background:#fff;border-radius:var(--radius);padding:0.625rem 0.875rem;">
                <i class="bi bi-geo-alt" style="color:var(--slate-400);font-size:1.1rem;"></i>
                <select name="city" style="border:none;outline:none;flex:1;font-size:0.95rem;background:transparent;font-family:var(--font-sans);color:var(--slate-900);cursor:pointer;">
                    <option value="">All Cities</option>
                    <?php foreach ($cities as $c): ?>
                        <option value="<?php echo e($c); ?>"><?php echo e($c); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="border-radius:var(--radius);padding:0.625rem 1.5rem;font-weight:700;"><i class="bi bi-search"></i> Search</button>
        </form>

        <div style="display:flex;justify-content:center;gap:3.5rem;margin-top:3.5rem;flex-wrap:wrap;animation:fadeInUp 0.6s ease 0.4s both;">
            <div>
                <div style="font-size:2.5rem;font-weight:800;color:#fff;line-height:1;"><?php echo $totalProperties; ?>+</div>
                <div style="font-size:0.95rem;opacity:0.85;margin-top:0.25rem;">Properties</div>
            </div>
            <div>
                <div style="font-size:2.5rem;font-weight:800;color:#fff;line-height:1;"><?php echo $ownerCount; ?>+</div>
                <div style="font-size:0.95rem;opacity:0.85;margin-top:0.25rem;">Verified Owners</div>
            </div>
            <div>
                <div style="font-size:2.5rem;font-weight:800;color:#fff;line-height:1;"><?php echo count($cities); ?>+</div>
                <div style="font-size:0.95rem;opacity:0.85;margin-top:0.25rem;">Cities Covered</div>
            </div>
            <div>
                <div style="font-size:2.5rem;font-weight:800;color:#fff;line-height:1;"><?php echo $bookingCount; ?>+</div>
                <div style="font-size:0.95rem;opacity:0.85;margin-top:0.25rem;">Bookings Made</div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Properties -->
<section style="padding:5rem 0;">
    <div class="container-app">
        <div style="text-align:center;margin-bottom:3rem;">
            <span class="badge badge-primary" style="margin-bottom:0.75rem;">Featured</span>
            <h2 style="font-size:2.25rem;font-weight:800;color:var(--slate-900);letter-spacing:-0.02em;">Featured Properties</h2>
            <p style="color:var(--slate-500);margin-top:0.5rem;font-size:1.05rem;">Handpicked premium rental listings just for you</p>
        </div>
        <?php if (empty($featuredProperties)): ?>
            <div class="card-premium" style="text-align:center;padding:4rem 2rem;">
                <div style="width:80px;height:80px;border-radius:var(--radius-md);background:var(--slate-100);display:flex;align-items:center;justify-content:center;color:var(--slate-300);font-size:2.5rem;margin:0 auto 1.25rem;">
                    <i class="bi bi-house-door"></i>
                </div>
                <h3 style="color:var(--slate-900);font-weight:700;">No properties yet</h3>
                <p style="color:var(--slate-500);margin-top:0.5rem;">Property owners haven't listed any properties yet. Check back soon!</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($featuredProperties as $p): ?>
                    <div class="col-md-6 col-lg-4">
                        <?php include __DIR__ . '/includes/property_card.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="text-align:center;margin-top:3rem;">
                <a href="<?php echo url('/properties.php'); ?>" class="btn btn-ghost btn-lg">View All Properties <i class="bi bi-arrow-right"></i></a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Popular Cities -->
<section style="padding:5rem 0;background:linear-gradient(180deg,var(--slate-50),#fff);">
    <div class="container-app">
        <div style="text-align:center;margin-bottom:3rem;">
            <span class="badge badge-info" style="margin-bottom:0.75rem;">Destinations</span>
            <h2 style="font-size:2.25rem;font-weight:800;color:var(--slate-900);letter-spacing:-0.02em;">Popular Cities</h2>
            <p style="color:var(--slate-500);margin-top:0.5rem;font-size:1.05rem;">Explore rentals in top destinations across Pakistan</p>
        </div>
        <?php if (empty($cities)): ?>
            <div class="card-premium" style="text-align:center;padding:3rem 2rem;">
                <p style="color:var(--slate-500);margin:0;">No cities available yet.</p>
            </div>
        <?php else: ?>
            <?php
            $cityImages = [
                'Karachi' => 'https://images.pexels.com/photos/2695480/pexels-photo-2695480.jpeg?auto=compress&cs=tinysrgb&w=600',
                'Lahore' => 'https://images.pexels.com/photos/2901209/pexels-photo-2901209.jpeg?auto=compress&cs=tinysrgb&w=600',
                'Islamabad' => 'https://images.pexels.com/photos/323780/pexels-photo-323780.jpeg?auto=compress&cs=tinysrgb&w=600',
                'Rawalpindi' => 'https://images.pexels.com/photos/4666748/pexels-photo-4666748.jpeg?auto=compress&cs=tinysrgb&w=600',
                'Faisalabad' => 'https://images.pexels.com/photos/1396122/pexels-photo-1396122.jpeg?auto=compress&cs=tinysrgb&w=600',
                'Multan' => 'https://images.pexels.com/photos/417074/pexels-photo-417074.jpeg?auto=compress&cs=tinysrgb&w=600',
                'Peshawar' => 'https://images.pexels.com/photos/417074/pexels-photo-417074.jpeg?auto=compress&cs=tinysrgb&w=600',
                'Quetta' => 'https://images.pexels.com/photos/1366919/pexels-photo-1366919.jpeg?auto=compress&cs=tinysrgb&w=600',
                'Hyderabad' => 'https://images.pexels.com/photos/1366913/pexels-photo-1366913.jpeg?auto=compress&cs=tinysrgb&w=600',
                'Sialkot' => 'https://images.pexels.com/photos/1396122/pexels-photo-1396122.jpeg?auto=compress&cs=tinysrgb&w=600',
                'Gujranwala' => 'https://images.pexels.com/photos/1396122/pexels-photo-1396122.jpeg?auto=compress&cs=tinysrgb&w=600',
                'Bahawalpur' => 'https://images.pexels.com/photos/417074/pexels-photo-417074.jpeg?auto=compress&cs=tinysrgb&w=600',
            ];
            $defaultCityImage = 'https://images.pexels.com/photos/323780/pexels-photo-323780.jpeg?auto=compress&cs=tinysrgb&w=600';
            ?>
            <div class="row g-4">
                <?php foreach ($cities as $city): ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="<?php echo url('/properties.php?city=' . urlencode($city)); ?>" class="card-premium" style="text-decoration:none;display:block;padding:0;overflow:hidden;height:100%;position:relative;">
                            <div style="width:100%;height:160px;overflow:hidden;position:relative;">
                                <img src="<?php echo e($cityImages[$city] ?? $defaultCityImage); ?>" alt="<?php echo e($city); ?>" style="width:100%;height:100%;object-fit:cover;transition:transform 0.3s ease;" onerror="this.src='<?php echo $defaultCityImage; ?>'">
                                <div style="position:absolute;inset:0;background:linear-gradient(180deg,transparent 40%,rgba(0,0,0,0.65));"></div>
                                <div style="position:absolute;bottom:0;left:0;right:0;padding:1rem;color:#fff;">
                                    <h5 style="margin:0;font-weight:700;font-size:1.1rem;letter-spacing:-0.01em;text-shadow:0 2px 4px rgba(0,0,0,0.3);"><?php echo e($city); ?></h5>
                                    <small style="opacity:0.9;font-size:0.8rem;">View properties <i class="bi bi-arrow-right" style="font-size:0.7rem;"></i></small>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Top Rated -->
<section style="padding:5rem 0;">
    <div class="container-app">
        <div style="text-align:center;margin-bottom:3rem;">
            <span class="badge badge-warning" style="margin-bottom:0.75rem;"><i class="bi bi-star-fill"></i> Top Rated</span>
            <h2 style="font-size:2.25rem;font-weight:800;color:var(--slate-900);letter-spacing:-0.02em;">Top Rated Properties</h2>
            <p style="color:var(--slate-500);margin-top:0.5rem;font-size:1.05rem;">Highest-rated stays by our community</p>
        </div>
        <?php if (empty($topRated)): ?>
            <div class="card-premium" style="text-align:center;padding:3rem 2rem;">
                <p style="color:var(--slate-500);margin:0;">No rated properties yet.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($topRated as $p): ?>
                    <div class="col-md-6 col-lg-4">
                        <?php include __DIR__ . '/includes/property_card.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- How It Works -->
<section style="padding:5rem 0;background:linear-gradient(180deg,var(--slate-50),#fff);">
    <div class="container-app">
        <div style="text-align:center;margin-bottom:3rem;">
            <span class="badge badge-success" style="margin-bottom:0.75rem;">Simple Process</span>
            <h2 style="font-size:2.25rem;font-weight:800;color:var(--slate-900);letter-spacing:-0.02em;">How It Works</h2>
            <p style="color:var(--slate-500);margin-top:0.5rem;font-size:1.05rem;">Find your rental in three simple steps</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card-premium" style="padding:2.5rem 2rem;height:100%;text-align:center;">
                    <div style="width:72px;height:72px;border-radius:var(--radius-md);background:linear-gradient(135deg,var(--primary-600),var(--primary-500));display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.75rem;margin:0 auto 1.5rem;box-shadow:0 12px 28px -6px rgba(26,82,245,0.35);">
                        <i class="bi bi-search"></i>
                    </div>
                    <h4 style="color:var(--slate-900);font-weight:700;">Search</h4>
                    <p style="color:var(--slate-500);margin-top:0.5rem;">Browse our extensive collection of verified rental properties across Pakistan.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-premium" style="padding:2.5rem 2rem;height:100%;text-align:center;">
                    <div style="width:72px;height:72px;border-radius:var(--radius-md);background:linear-gradient(135deg,var(--accent-600),var(--accent-500));display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.75rem;margin:0 auto 1.5rem;box-shadow:0 12px 28px -6px rgba(16,185,129,0.35);">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h4 style="color:var(--slate-900);font-weight:700;">Book</h4>
                    <p style="color:var(--slate-500);margin-top:0.5rem;">Contact the owner directly and book your preferred property in minutes.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-premium" style="padding:2.5rem 2rem;height:100%;text-align:center;">
                    <div style="width:72px;height:72px;border-radius:var(--radius-md);background:linear-gradient(135deg,var(--gold-400),var(--gold-600));display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.75rem;margin:0 auto 1.5rem;box-shadow:var(--shadow-gold);">
                        <i class="bi bi-key"></i>
                    </div>
                    <h4 style="color:var(--slate-900);font-weight:700;">Move In</h4>
                    <p style="color:var(--slate-500);margin-top:0.5rem;">Complete the booking and get your keys. Welcome to your new home!</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<?php if (!$user || $user['role'] === 'tenant'): ?>
<section style="padding:5rem 0;">
    <div class="container-app">
        <div class="card-premium" style="border:none;border-radius:var(--radius-2xl);overflow:hidden;background:linear-gradient(135deg,var(--primary-700),var(--accent-600));color:#fff;position:relative;">
            <div style="position:absolute;top:-50px;right:-50px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,0.15),transparent 70%);"></div>
            <div style="padding:4rem;text-align:center;position:relative;z-index:2;">
                <span class="badge" style="background:rgba(255,255,255,0.2);color:#fff;padding:0.4rem 1rem;border-radius:999px;font-weight:600;margin-bottom:1rem;"><i class="bi bi-stars"></i> Become a Host</span>
                <h2 style="font-size:2.25rem;font-weight:800;margin-bottom:0.75rem;letter-spacing:-0.02em;">Have a property to rent out?</h2>
                <p style="font-size:1.1rem;opacity:0.92;margin-bottom:2rem;max-width:540px;margin-left:auto;margin-right:auto;">List your property on Mehmaan Hub and reach thousands of potential tenants across Pakistan.</p>
                <a href="<?php echo url(($user && $user['role'] === 'tenant') ? '/become-host.php' : '/register.php?role=owner'); ?>" class="btn btn-gold btn-lg" style="padding:0.875rem 2.25rem;">
                    <i class="bi bi-stars"></i> Become a Host
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
