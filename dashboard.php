<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$user = requireLogin();

// Redirect based on role
if ($user['role'] === 'owner') {
    redirect('/owner-dashboard.php');
} elseif ($user['role'] === 'admin') {
    redirect('/admin.php');
}

$myBookings = get_user_bookings($user['id']);
$wishlist = get_wishlist($user['id']);

$stats = [
    'trips' => count($myBookings),
    'pending' => 0,
    'confirmed' => 0,
    'completed' => 0,
    'cancelled' => 0,
    'wishlist' => count($wishlist),
];
foreach ($myBookings as $b) {
    if ($b['status'] === 'pending') $stats['pending']++;
    if ($b['status'] === 'confirmed') $stats['confirmed']++;
    if ($b['status'] === 'completed') $stats['completed']++;
    if ($b['status'] === 'cancelled') $stats['cancelled']++;
}

// Travel analytics: cities visited
$citiesVisited = [];
foreach ($myBookings as $b) {
    if (in_array($b['status'], ['confirmed', 'completed']) && !empty($b['property_city'])) {
        $citiesVisited[$b['property_city']] = ($citiesVisited[$b['property_city']] ?? 0) + 1;
    }
}
$totalSpent = 0;
foreach ($myBookings as $b) {
    if (in_array($b['status'], ['confirmed', 'completed']) && $b['payment_status'] === 'paid') {
        $totalSpent += (float)$b['total_amount'];
    }
}

$recentBookings = array_slice($myBookings, 0, 5);

include __DIR__ . '/includes/header.php';
?>

<!-- Dashboard Hero -->
<div style="background:linear-gradient(135deg,var(--primary-700),var(--accent-600));padding:2.5rem 0 5rem;color:#fff;position:relative;overflow:hidden;">
    <div style="position:absolute;top:-80px;right:-80px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,0.12),transparent 70%);"></div>
    <div class="container-app d-flex justify-content-between align-items-center flex-wrap gap-2" style="position:relative;z-index:2;">
        <div>
            <h1 style="font-size:2rem;font-weight:800;margin:0;letter-spacing:-0.02em;">My Dashboard</h1>
            <p style="margin:0.5rem 0 0;opacity:0.92;font-size:1.05rem;">Welcome back, <?php echo e($user['name']); ?>!</p>
        </div>
        <a href="<?php echo url('/properties.php'); ?>" class="btn btn-light" style="background:rgba(255,255,255,0.95);color:var(--primary-600);font-weight:700;border:none;border-radius:var(--radius);padding:0.625rem 1.25rem;backdrop-filter:blur(12px);">
            <i class="bi bi-search"></i> Browse Properties
        </a>
    </div>
</div>

<section style="padding:0 0 2.5rem;margin-top:-3.5rem;position:relative;z-index:3;">
    <div class="container-app">
        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card-premium" style="padding:1.5rem;height:100%;">
                    <div style="display:flex;align-items:center;gap:1rem;">
                        <div style="width:52px;height:52px;border-radius:var(--radius);background:linear-gradient(135deg,var(--primary-600),var(--primary-500));display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.35rem;box-shadow:0 8px 20px -4px rgba(26,82,245,0.35);">
                            <i class="bi bi-suitcase-lg"></i>
                        </div>
                        <div>
                            <div style="font-size:1.75rem;font-weight:800;color:var(--slate-900);line-height:1;"><?php echo $stats['trips']; ?></div>
                            <small style="color:var(--slate-500);font-weight:500;">Total Trips</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card-premium" style="padding:1.5rem;height:100%;">
                    <div style="display:flex;align-items:center;gap:1rem;">
                        <div style="width:52px;height:52px;border-radius:var(--radius);background:linear-gradient(135deg,var(--success-500),var(--success-600));display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.35rem;box-shadow:0 8px 20px -4px rgba(22,163,74,0.35);">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div>
                            <div style="font-size:1.75rem;font-weight:800;color:var(--slate-900);line-height:1;"><?php echo $stats['completed']; ?></div>
                            <small style="color:var(--slate-500);font-weight:500;">Completed</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card-premium" style="padding:1.5rem;height:100%;">
                    <div style="display:flex;align-items:center;gap:1rem;">
                        <div style="width:52px;height:52px;border-radius:var(--radius);background:linear-gradient(135deg,var(--warning-400),var(--warning-500));display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.35rem;box-shadow:0 8px 20px -4px rgba(245,158,11,0.35);">
                            <i class="bi bi-clock"></i>
                        </div>
                        <div>
                            <div style="font-size:1.75rem;font-weight:800;color:var(--slate-900);line-height:1;"><?php echo $stats['pending'] + $stats['confirmed']; ?></div>
                            <small style="color:var(--slate-500);font-weight:500;">Upcoming</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card-premium" style="padding:1.5rem;height:100%;">
                    <div style="display:flex;align-items:center;gap:1rem;">
                        <div style="width:52px;height:52px;border-radius:var(--radius);background:linear-gradient(135deg,var(--error-400),var(--error-500));display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.35rem;box-shadow:0 8px 20px -4px rgba(239,68,68,0.35);">
                            <i class="bi bi-heart"></i>
                        </div>
                        <div>
                            <div style="font-size:1.75rem;font-weight:800;color:var(--slate-900);line-height:1;"><?php echo $stats['wishlist']; ?></div>
                            <small style="color:var(--slate-500);font-weight:500;">Wishlist</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Recent Bookings -->
            <div class="col-lg-8">
                <div class="card-premium" style="padding:1.75rem;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
                        <h4 style="margin:0;color:var(--slate-900);font-weight:700;font-size:1.15rem;">
                            <i class="bi bi-calendar-check" style="color:var(--primary-600);"></i> Recent Bookings
                        </h4>
                        <a href="<?php echo url('/bookings.php'); ?>" class="btn btn-ghost btn-sm">View All <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <?php if (empty($recentBookings)): ?>
                        <div style="text-align:center;padding:2.5rem 1rem;">
                            <div style="width:72px;height:72px;border-radius:var(--radius-md);background:var(--slate-100);display:flex;align-items:center;justify-content:center;color:var(--slate-300);font-size:2rem;margin:0 auto 1.25rem;">
                                <i class="bi bi-calendar"></i>
                            </div>
                            <h5 style="margin:0 0 0.25rem;color:var(--slate-900);">No bookings yet</h5>
                            <p style="color:var(--slate-500);margin-bottom:1rem;">Browse properties and book your favorite ones.</p>
                            <a href="<?php echo url('/properties.php'); ?>" class="btn btn-primary"><i class="bi bi-search"></i> Browse Properties</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentBookings as $b): ?>
                            <div style="display:flex;align-items:center;gap:1rem;padding:1rem 0;border-bottom:1px solid var(--slate-100);">
                                <?php if (!empty($b['primary_image'])): ?>
                                    <img src="<?php echo e(image_url($b['primary_image'])); ?>" alt="" style="width:64px;height:64px;border-radius:var(--radius-sm);object-fit:cover;flex-shrink:0;">
                                <?php else: ?>
                                    <div style="width:64px;height:64px;border-radius:var(--radius-sm);background:var(--slate-100);display:flex;align-items:center;justify-content:center;color:var(--slate-400);font-size:1.5rem;flex-shrink:0;"><i class="bi bi-house"></i></div>
                                <?php endif; ?>
                                <div style="flex:1;min-width:0;">
                                    <a href="<?php echo url('/property-details.php?id=' . (int)$b['property_id']); ?>" style="color:var(--slate-900);font-weight:700;text-decoration:none;font-size:0.95rem;" class="line-clamp-1"><?php echo e($b['property_title']); ?></a>
                                    <div style="color:var(--slate-500);font-size:0.85rem;margin-top:0.25rem;">
                                        <i class="bi bi-calendar"></i> <?php echo formatDate($b['start_date']); ?> - <?php echo formatDate($b['end_date']); ?>
                                    </div>
                                </div>
                                <div style="text-align:right;flex-shrink:0;">
                                    <div style="font-weight:700;color:var(--slate-900);"><?php echo format_price($b['total_amount']); ?></div>
                                    <?php
                                    $badgeClass = 'badge-secondary';
                                    if ($b['status'] === 'pending') $badgeClass = 'badge-warning';
                                    elseif ($b['status'] === 'confirmed') $badgeClass = 'badge-success';
                                    elseif ($b['status'] === 'cancelled') $badgeClass = 'badge-error';
                                    elseif ($b['status'] === 'completed') $badgeClass = 'badge-info';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>" style="margin-top:0.25rem;"><?php echo ucfirst(e($b['status'])); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Travel Analytics -->
            <div class="col-lg-4">
                <div class="card-premium" style="padding:1.75rem;margin-bottom:1.5rem;">
                    <h4 style="margin:0 0 1rem;color:var(--slate-900);font-weight:700;font-size:1.15rem;">
                        <i class="bi bi-graph-up" style="color:var(--accent-600);"></i> Travel Analytics
                    </h4>
                    <div style="display:flex;justify-content:space-between;padding:0.75rem 0;border-bottom:1px solid var(--slate-100);">
                        <span style="color:var(--slate-500);">Total Spent</span>
                        <strong style="color:var(--slate-900);"><?php echo format_price($totalSpent); ?></strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:0.75rem 0;border-bottom:1px solid var(--slate-100);">
                        <span style="color:var(--slate-500);">Cities Visited</span>
                        <strong style="color:var(--slate-900);"><?php echo count($citiesVisited); ?></strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:0.75rem 0;">
                        <span style="color:var(--slate-500);">Trips Taken</span>
                        <strong style="color:var(--slate-900);"><?php echo $stats['completed']; ?></strong>
                    </div>
                </div>
                <div class="card-premium" style="padding:1.75rem;">
                    <h4 style="margin:0 0 1rem;color:var(--slate-900);font-weight:700;font-size:1.15rem;">
                        <i class="bi bi-geo-alt" style="color:var(--primary-600);"></i> Top Destinations
                    </h4>
                    <?php if (empty($citiesVisited)): ?>
                        <p style="color:var(--slate-500);text-align:center;padding:1rem 0;margin:0;">No trips yet. Start exploring!</p>
                    <?php else: ?>
                        <?php foreach ($citiesVisited as $city => $count): ?>
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:0.5rem 0;">
                            <span style="color:var(--slate-900);font-size:0.9rem;"><i class="bi bi-pin-map" style="color:var(--primary-500);"></i> <?php echo e($city); ?></span>
                            <span class="badge badge-info"><?php echo $count; ?> trip<?php echo $count > 1 ? 's' : ''; ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
