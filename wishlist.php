<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$user = requireLogin();

$wishlist = get_wishlist($user['id']);

// Stats
$totalWishlist = count($wishlist);
$totalValue = 0;
foreach ($wishlist as $p) {
    $totalValue += (float)$p['price'];
}
$distinctCities = array_unique(array_column($wishlist, 'city'));

include __DIR__ . '/includes/header.php';
?>

<div style="background:linear-gradient(135deg,#0ea5e9,#14b8a6);padding:2.5rem 0;color:#fff;">
    <div class="container-app">
        <h1 style="font-size:2rem;font-weight:800;margin:0;letter-spacing:-0.02em;">My Wishlist</h1>
        <p style="margin:0.5rem 0 0;opacity:0.95;">Properties you've saved for later</p>
    </div>
</div>

<section style="padding:2.5rem 0;">
    <div class="container-app">
        <!-- Stats -->
        <?php if (!empty($wishlist)): ?>
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4">
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:1.5rem;">
                    <div style="display:flex;align-items:center;gap:1rem;">
                        <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#ec4899,#ec4899);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.25rem;">
                            <i class="bi bi-heart-fill"></i>
                        </div>
                        <div>
                            <div style="font-size:1.75rem;font-weight:800;color:#0f172a;line-height:1;"><?php echo $totalWishlist; ?></div>
                            <small style="color:#64748b;">Saved Properties</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:1.5rem;">
                    <div style="display:flex;align-items:center;gap:1rem;">
                        <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#0ea5e9,#0ea5e9);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.25rem;">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                        <div>
                            <div style="font-size:1.5rem;font-weight:800;color:#0f172a;line-height:1;"><?php echo format_price($totalValue); ?></div>
                            <small style="color:#64748b;">Total Value</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:1.5rem;">
                    <div style="display:flex;align-items:center;gap:1rem;">
                        <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#14b8a6,#14b8a6);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.25rem;">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <div>
                            <div style="font-size:1.75rem;font-weight:800;color:#0f172a;line-height:1;"><?php echo count($distinctCities); ?></div>
                            <small style="color:#64748b;">Cities</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (empty($wishlist)): ?>
            <div class="card" style="text-align:center;padding:4rem 2rem;border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);">
                <i class="bi bi-heart" style="font-size:3rem;color:#cbd5e1;"></i>
                <h3 style="margin-top:1rem;color:#0f172a;">Your wishlist is empty</h3>
                <p style="color:#64748b;">Save properties you like by clicking the heart icon.</p>
                <a href="<?php echo url('/properties.php'); ?>" class="btn btn-primary" style="margin-top:1rem;"><i class="bi bi-search"></i> Browse Properties</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($wishlist as $p): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card" style="border:none;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.06);height:100%;">
                            <a href="<?php echo url('/property-details.php?id=' . (int)$p['id']); ?>" style="display:block;position:relative;">
                                <?php
                                $primaryImg = !empty($p['primary_image']) ? $p['primary_image'] : get_primary_image($p['id']);
                                $imgSrc = $primaryImg ? image_url($primaryImg) : 'https://images.pexels.com/photos/1571460/pexels-photo-1571460.jpeg?auto=compress&cs=tinysrgb&w=800';
                                ?>
                                <img src="<?php echo e($imgSrc); ?>" alt="<?php echo e($p['title']); ?>" style="width:100%;height:200px;object-fit:cover;">
                                <span class="badge badge-type" style="position:absolute;top:0.75rem;left:0.75rem;"><?php echo e(get_property_type_label($p['property_type'])); ?></span>
                                <span class="badge badge-error" style="position:absolute;top:0.75rem;right:0.75rem;"><i class="bi bi-heart-fill"></i></span>
                            </a>
                            <div style="padding:1.25rem;">
                                <a href="<?php echo url('/property-details.php?id=' . (int)$p['id']); ?>" style="color:#0f172a;font-weight:700;text-decoration:none;font-size:1rem;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo e($p['title']); ?></a>
                                <p style="color:#64748b;font-size:0.85rem;margin:0.25rem 0;"><i class="bi bi-geo-alt"></i> <?php echo e($p['city'] . ', ' . $p['area']); ?></p>
                                <div style="display:flex;gap:1rem;color:#64748b;font-size:0.8rem;margin:0.5rem 0;">
                                    <span><i class="bi bi-house"></i> <?php echo (int)$p['bedrooms']; ?> Beds</span>
                                    <span><i class="bi bi-droplet"></i> <?php echo (int)$p['bathrooms']; ?> Baths</span>
                                </div>
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:0.75rem;">
                                    <strong style="color:#0f172a;font-size:1.1rem;"><?php echo format_price($p['price']); ?><?php if ($p['price_period'] === 'per_day'): ?><small style="color:#64748b;font-weight:400;"> /day</small><?php else: ?><small style="color:#64748b;font-weight:400;"> /mo</small><?php endif; ?></strong>
                                    <a href="<?php echo url('/api/toggle-wishlist.php?property_id=' . (int)$p['id']); ?>" class="btn btn-error btn-sm" style="border-radius:8px;"><i class="bi bi-trash"></i> Remove</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
