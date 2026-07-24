<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$user = currentUser();

$id = (int)($_GET['id'] ?? 0);
$property = get_property_by_id($id);

if (!$property) {
    flash('error', 'Property not found.');
    redirect('/properties.php');
}

$images = get_property_images($id);
$reviews = get_reviews($id);
$ratingData = get_avg_rating($id);
$avgRating = $ratingData['avg_rating'] ? round($ratingData['avg_rating'], 1) : 0;
$reviewCount = $ratingData['count'] ?? 0;

// Fetch confirmed bookings for this property to show unavailable dates
$bookedDatesStmt = db()->prepare("SELECT start_date, end_date FROM bookings WHERE property_id = ? AND status = 'confirmed' AND payment_status = 'paid' ORDER BY start_date ASC");
$bookedDatesStmt->execute([$id]);
$bookedRanges = $bookedDatesStmt->fetchAll();

$bookingState = 'book';
$bookingMessage = '';
if ($user) {
    if ($property['owner_id'] == $user['id']) {
        $bookingState = 'own';
        $bookingMessage = 'This is your property';
    } elseif (has_user_booked_property($user['id'], $property['id'])) {
        $bookingState = 'booked';
        $bookingMessage = 'You already booked this property';
    }
} else {
    $bookingState = 'login';
}

$inWishlist = $user ? is_in_wishlist($user['id'], $property['id']) : false;

$amenities = [];
if (!empty($property['has_wifi'])) $amenities[] = ['bi-wifi', 'WiFi'];
if (!empty($property['has_ac'])) $amenities[] = ['bi-snow', 'Air Conditioning'];
if (!empty($property['has_parking'])) $amenities[] = ['bi-car-front', 'Parking'];
if (!empty($property['has_generator'])) $amenities[] = ['bi-lightning-charge', 'Backup Generator'];
if (!empty($property['is_furnished'])) $amenities[] = ['bi-house-heart', 'Furnished'];
if (!empty($property['has_kitchen'])) $amenities[] = ['bi-cup-hot', 'Kitchen'];
if (!empty($property['has_swimming_pool'])) $amenities[] = ['bi-water', 'Swimming Pool'];
if (!empty($property['has_gym'])) $amenities[] = ['bi-bicycle', 'Gym'];
if (!empty($property['has_security'])) $amenities[] = ['bi-shield-check', 'Security'];
if (!empty($property['has_elevator'])) $amenities[] = ['bi-elevator', 'Elevator'];
if (!empty($property['has_garden'])) $amenities[] = ['bi-tree', 'Garden'];
if (!empty($property['has_heating'])) $amenities[] = ['bi-thermometer-sun', 'Heating'];
if (!empty($property['has_cctv'])) $amenities[] = ['bi-camera-video', 'CCTV'];

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-app py-4">
    <!-- Breadcrumb -->
    <nav class="mb-4" style="font-size:0.875rem;">
        <a href="<?php echo url('/index.php'); ?>" style="color:var(--slate-500);text-decoration:none;">Home</a>
        <span style="color:var(--slate-300);"> / </span>
        <a href="<?php echo url('/properties.php'); ?>" style="color:var(--slate-500);text-decoration:none;">Properties</a>
        <span style="color:var(--slate-300);"> / </span>
        <span style="color:var(--slate-900);font-weight:600;"><?php echo e($property['title']); ?></span>
    </nav>

    <!-- Image Gallery -->
    <?php if (!empty($images)): ?>
    <div class="card-premium mb-4" style="overflow:hidden;border:none;box-shadow:var(--shadow-lg);">
        <div class="row g-0">
            <div class="col-lg-7">
                <div style="position:relative;height:480px;background:var(--slate-100);">
                    <img src="<?php echo e(image_url($images[0]['image_path'])); ?>" alt="<?php echo e($property['title']); ?>" id="mainGalleryImg" style="width:100%;height:100%;object-fit:cover;">
                    <?php if (!empty($property['featured'])): ?>
                        <span class="badge badge-featured"><i class="bi bi-star-fill"></i> Featured</span>
                    <?php endif; ?>
                    <span class="badge badge-type"><?php echo e(get_property_type_label($property['property_type'])); ?></span>
                </div>
            </div>
            <?php if (count($images) > 1): ?>
            <div class="col-lg-5">
                <div class="d-flex flex-column gap-2 p-3" style="height:480px;overflow-y:auto;">
                    <?php foreach ($images as $idx => $img): ?>
                        <div class="gallery-thumb <?php echo $idx === 0 ? 'active' : ''; ?>" onclick="changeMainImage(this, '<?php echo e(image_url($img['image_path'])); ?>')" style="flex-shrink:0;height:145px;border-radius:var(--radius-sm);overflow:hidden;cursor:pointer;width:100%;">
                            <img src="<?php echo e(image_url($img['image_path'])); ?>" alt="Thumbnail <?php echo $idx + 1; ?>" style="width:100%;height:100%;object-fit:cover;">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="card-premium mb-4 d-flex align-items-center justify-content-center" style="height:300px;background:var(--slate-100);">
        <div class="text-center" style="color:var(--slate-400);">
            <i class="bi bi-image" style="font-size:3rem;"></i>
            <p class="mt-2">No images available</p>
        </div>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Header -->
            <div class="card-premium mb-4" style="padding:1.75rem;">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                    <div>
                        <h1 style="font-size:1.75rem;font-weight:800;color:var(--slate-900);margin-bottom:0.5rem;letter-spacing:-0.02em;"><?php echo e($property['title']); ?></h1>
                        <p class="mb-0" style="color:var(--slate-500);font-size:0.95rem;">
                            <i class="bi bi-geo-alt" style="color:var(--primary-500);"></i>
                            <?php echo e($property['address'] . ', ' . $property['city']); ?>
                        </p>
                    </div>
                    <div class="text-end">
                        <?php if ($property['price_period'] === 'both'): ?>
                            <div style="font-size:1.5rem;font-weight:800;color:var(--slate-900);">
                                <?php echo format_price($property['price']); ?><span style="font-size:0.85rem;font-weight:500;color:var(--slate-500);">/month</span>
                            </div>
                            <?php if ($property['price_per_day'] !== null): ?>
                                <div style="font-size:1.1rem;font-weight:700;color:var(--accent-600);">
                                    <?php echo format_price($property['price_per_day']); ?><span style="font-size:0.8rem;font-weight:500;color:var(--slate-500);">/day</span>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div style="font-size:1.5rem;font-weight:800;color:var(--slate-900);">
                                <?php echo format_price($property['price']); ?>
                                <span style="font-size:0.85rem;font-weight:500;color:var(--slate-500);">/<?php echo $property['price_period'] === 'per_day' ? 'day' : 'month'; ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($avgRating > 0): ?>
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="d-flex align-items-center gap-1">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star-fill" style="color:<?php echo $i <= round($avgRating) ? 'var(--warning-500)' : 'var(--slate-200)'; ?>;font-size:1rem;"></i>
                        <?php endfor; ?>
                    </div>
                    <span style="font-weight:700;color:var(--slate-900);"><?php echo e(number_format($avgRating, 1)); ?></span>
                    <span style="color:var(--slate-500);font-size:0.875rem;">(<?php echo (int)$reviewCount; ?> review<?php echo $reviewCount === 1 ? '' : 's'; ?>)</span>
                </div>
                <?php endif; ?>

                <div class="d-flex flex-wrap gap-4 pt-3" style="border-top:1px solid var(--slate-100);">
                    <div class="d-flex align-items-center gap-2">
                        <div class="d-flex align-items-center justify-content-center" style="width:40px;height:40px;border-radius:var(--radius-sm);background:var(--primary-50);color:var(--primary-600);font-size:1.1rem;">
                            <i class="bi bi-house-door"></i>
                        </div>
                        <div>
                            <div style="font-weight:700;color:var(--slate-900);font-size:0.95rem;"><?php echo e(get_property_type_label($property['property_type'])); ?></div>
                            <div style="font-size:0.75rem;color:var(--slate-500);">Type</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="d-flex align-items-center justify-content-center" style="width:40px;height:40px;border-radius:var(--radius-sm);background:var(--accent-50);color:var(--accent-600);font-size:1.1rem;">
                            <i class="bi bi-door-open"></i>
                        </div>
                        <div>
                            <div style="font-weight:700;color:var(--slate-900);font-size:0.95rem;"><?php echo (int)$property['bedrooms']; ?></div>
                            <div style="font-size:0.75rem;color:var(--slate-500);">Bedrooms</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="d-flex align-items-center justify-content-center" style="width:40px;height:40px;border-radius:var(--radius-sm);background:var(--primary-50);color:var(--primary-600);font-size:1.1rem;">
                            <i class="bi bi-droplet"></i>
                        </div>
                        <div>
                            <div style="font-weight:700;color:var(--slate-900);font-size:0.95rem;"><?php echo (int)$property['bathrooms']; ?></div>
                            <div style="font-size:0.75rem;color:var(--slate-500);">Bathrooms</div>
                        </div>
                    </div>
                    <?php if (!empty($property['area_sqft'])): ?>
                    <div class="d-flex align-items-center gap-2">
                        <div class="d-flex align-items-center justify-content-center" style="width:40px;height:40px;border-radius:var(--radius-sm);background:var(--warning-50);color:var(--warning-600);font-size:1.1rem;">
                            <i class="bi bi-rulers"></i>
                        </div>
                        <div>
                            <div style="font-weight:700;color:var(--slate-900);font-size:0.95rem;"><?php echo (int)$property['area_sqft']; ?></div>
                            <div style="font-size:0.75rem;color:var(--slate-500);">Sq Ft</div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Amenities -->
            <?php if (!empty($amenities)): ?>
            <div class="card-premium mb-4" style="padding:1.75rem;">
                <h3 style="font-size:1.25rem;font-weight:700;color:var(--slate-900);margin-bottom:1rem;letter-spacing:-0.01em;">
                    <i class="bi bi-check2-circle" style="color:var(--accent-600);"></i> Amenities
                </h3>
                <div class="row g-3">
                    <?php foreach ($amenities as $a): ?>
                    <div class="col-6 col-md-4">
                        <div style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem 1rem;border-radius:var(--radius-sm);background:var(--slate-50);border:1px solid var(--slate-200);">
                            <i class="bi <?php echo $a[0]; ?>" style="font-size:1.25rem;color:var(--primary-600);"></i>
                            <span style="font-weight:600;color:var(--slate-900);font-size:0.9rem;"><?php echo e($a[1]); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Description -->
            <div class="card-premium mb-4" style="padding:1.75rem;">
                <h3 style="font-size:1.25rem;font-weight:700;color:var(--slate-900);margin-bottom:1rem;letter-spacing:-0.01em;">
                    <i class="bi bi-text-left" style="color:var(--primary-600);"></i> Description
                </h3>
                <p style="color:var(--slate-600);line-height:1.7;font-size:0.95rem;"><?php echo nl2br(e($property['description'])); ?></p>
            </div>

            <!-- Reviews -->
            <div class="card-premium mb-4" style="padding:1.75rem;">
                <h3 style="font-size:1.25rem;font-weight:700;color:var(--slate-900);margin-bottom:1rem;letter-spacing:-0.01em;">
                    <i class="bi bi-chat-square-text" style="color:var(--warning-500);"></i> Reviews (<?php echo (int)$reviewCount; ?>)
                </h3>

                <?php if ($avgRating > 0): ?>
                <div class="d-flex align-items-center gap-3 mb-4 p-3" style="background:var(--slate-50);border-radius:var(--radius);">
                    <span style="font-size:2.5rem;font-weight:800;color:var(--slate-900);"><?php echo e(number_format($avgRating, 1)); ?></span>
                    <div>
                        <div class="d-flex align-items-center gap-1">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi bi-star-fill" style="color:<?php echo $i <= round($avgRating) ? 'var(--warning-500)' : 'var(--slate-200)'; ?>;"></i>
                            <?php endfor; ?>
                        </div>
                        <div style="color:var(--slate-500);font-size:0.85rem;margin-top:0.25rem;">
                            Based on <?php echo (int)$reviewCount; ?> review<?php echo $reviewCount === 1 ? '' : 's'; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($user && $user['role'] === 'tenant'): ?>
                <form action="<?php echo url('/api/add-review.php'); ?>" method="POST" class="mb-4 p-3" style="background:var(--primary-50);border-radius:var(--radius);border:1px solid var(--primary-200);">
                    <input type="hidden" name="property_id" value="<?php echo (int)$property['id']; ?>">
                    <h4 style="font-size:1rem;font-weight:700;color:var(--slate-900);margin-bottom:0.75rem;">Write a Review</h4>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span style="font-size:0.875rem;color:var(--slate-500);margin-right:0.25rem;">Rating:</span>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <input type="radio" name="rating" id="star<?php echo $i; ?>" value="<?php echo $i; ?>" <?php echo $i === 5 ? 'checked' : ''; ?> style="display:none;" required>
                            <label for="star<?php echo $i; ?>" class="review-star-label" style="cursor:pointer;font-size:1.5rem;color:var(--slate-200);transition:color 0.15s;" onmouseover="hoverStars(<?php echo $i; ?>)" onmouseout="resetStars()">
                                <i class="bi bi-star-fill"></i>
                            </label>
                        <?php endfor; ?>
                    </div>
                    <textarea name="comment" placeholder="Share your experience..." rows="3" required class="form-control-mh" style="margin-bottom:0.75rem;"></textarea>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-send"></i> Submit Review
                    </button>
                </form>
                <?php elseif (!$user): ?>
                <div class="alert alert-info mb-4" style="background:var(--primary-50);border:1px solid var(--primary-200);border-radius:var(--radius);padding:1rem;color:var(--primary-700);font-size:0.9rem;">
                    <i class="bi bi-info-circle"></i>
                    <a href="<?php echo url('/login.php'); ?>" style="color:var(--primary-700);font-weight:600;">Login</a> to write a review.
                </div>
                <?php endif; ?>

                <?php if (empty($reviews)): ?>
                    <p style="color:var(--slate-500);font-style:italic;">No reviews yet. Be the first to review!</p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($reviews as $review): ?>
                        <div class="p-3" style="border:1px solid var(--slate-200);border-radius:var(--radius);background:#fff;">
                            <div class="d-flex align-items-start gap-3 mb-2">
                                <div class="d-flex align-items-center justify-content-center" style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--primary-600),var(--accent-500));color:#fff;font-weight:700;flex-shrink:0;">
                                    <?php echo e(strtoupper(substr($review['user_name'], 0, 1))); ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div style="font-weight:700;color:var(--slate-900);font-size:0.95rem;"><?php echo e($review['user_name']); ?></div>
                                    <div class="d-flex align-items-center gap-1">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star-fill" style="color:<?php echo $i <= $review['rating'] ? 'var(--warning-500)' : 'var(--slate-200)'; ?>;font-size:0.75rem;"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <span style="color:var(--slate-400);font-size:0.8rem;white-space:nowrap;"><?php echo e(formatDate($review['created_at'])); ?></span>
                            </div>
                            <p style="color:var(--slate-600);font-size:0.9rem;margin:0;padding-left:52px;"><?php echo e($review['comment']); ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card-premium" style="padding:1.75rem;position:sticky;top:90px;">
                <h3 style="font-size:1.1rem;font-weight:700;color:var(--slate-900);margin-bottom:1rem;letter-spacing:-0.01em;">
                    <i class="bi bi-person-circle" style="color:var(--primary-600);"></i> Contact Owner
                </h3>

                <div class="d-flex align-items-center gap-3 mb-3 p-3" style="background:var(--slate-50);border-radius:var(--radius);">
                    <div class="d-flex align-items-center justify-content-center" style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--primary-600),var(--accent-500));color:#fff;font-weight:700;font-size:1.25rem;flex-shrink:0;box-shadow:0 4px 12px -2px rgba(26,82,245,0.3);">
                        <?php echo e(strtoupper(substr($property['owner_name'], 0, 1))); ?>
                    </div>
                    <div>
                        <div style="font-weight:700;color:var(--slate-900);font-size:0.95rem;"><?php echo e($property['owner_name']); ?></div>
                        <div style="font-size:0.8rem;color:var(--slate-500);">Property Owner</div>
                    </div>
                </div>

                <div class="d-flex flex-column gap-2 mb-3">
                    <?php if (!empty($property['owner_phone'])): ?>
                    <div class="d-flex align-items-center gap-2" style="color:var(--slate-600);font-size:0.9rem;">
                        <i class="bi bi-phone" style="color:var(--accent-600);"></i>
                        <?php echo e($property['owner_phone']); ?>
                    </div>
                    <?php endif; ?>
                    <div class="d-flex align-items-center gap-2" style="color:var(--slate-600);font-size:0.9rem;">
                        <i class="bi bi-envelope" style="color:var(--accent-600);"></i>
                        <?php echo e($property['owner_email']); ?>
                    </div>
                </div>

                <?php if ($bookingState === 'own' || $bookingState === 'booked'): ?>
                    <div class="alert alert-success text-center mb-3" style="font-weight:600;padding:0.75rem;">
                        <i class="bi bi-check-circle"></i> <?php echo e($bookingMessage); ?>
                    </div>
                <?php elseif ($bookingState === 'login'): ?>
                    <a href="<?php echo url('/login.php'); ?>" class="btn btn-primary btn-block mb-2">
                        <i class="bi bi-box-arrow-in-right"></i> Login to Book
                    </a>
                <?php else: ?>
                    <?php if (!empty($bookedRanges)): ?>
                    <div style="margin-bottom:0.75rem;padding:0.75rem 1rem;background:var(--warning-50);border:1px solid var(--warning-200);border-radius:10px;">
                        <div style="font-weight:700;color:var(--warning-700);font-size:0.85rem;margin-bottom:0.4rem;">
                            <i class="bi bi-calendar-x"></i> Currently Booked Dates
                        </div>
                        <?php foreach ($bookedRanges as $range): ?>
                            <div style="font-size:0.8rem;color:var(--slate-600);padding:0.15rem 0;">
                                <?php echo formatDate($range['start_date']); ?> — <?php echo formatDate($range['end_date']); ?>
                            </div>
                        <?php endforeach; ?>
                        <div style="font-size:0.78rem;color:var(--slate-500);margin-top:0.4rem;font-style:italic;">You can book for dates outside these ranges.</div>
                    </div>
                    <?php endif; ?>
                    <a href="<?php echo url('/booking.php?property_id=' . (int)$property['id']); ?>" class="btn btn-primary btn-block mb-2">
                        <i class="bi bi-calendar-check"></i> Book Now
                    </a>
                <?php endif; ?>

                <?php if ($user && $property['owner_id'] != $user['id']): ?>
                <button onclick="toggleWishlist(event, <?php echo (int)$property['id']; ?>)" class="btn <?php echo $inWishlist ? 'btn-accent' : 'btn-ghost'; ?> btn-block" id="wishlistBtn">
                    <i class="bi <?php echo $inWishlist ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
                    <span id="wishlistText"><?php echo $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist'; ?></span>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function changeMainImage(thumb, src) {
    document.getElementById('mainGalleryImg').src = src;
    document.querySelectorAll('.gallery-thumb').forEach(function(t) { t.classList.remove('active'); });
    thumb.classList.add('active');
}

function hoverStars(n) {
    for (var i = 1; i <= 5; i++) {
        var label = document.querySelector('label[for="star' + i + '"]');
        if (label) label.style.color = i <= n ? 'var(--warning-500)' : 'var(--slate-200)';
    }
}

function resetStars() {
    var checked = document.querySelector('input[name="rating"]:checked');
    var checkedVal = checked ? parseInt(checked.value) : 5;
    for (var i = 1; i <= 5; i++) {
        var label = document.querySelector('label[for="star' + i + '"]');
        if (label) label.style.color = i <= checkedVal ? 'var(--warning-500)' : 'var(--slate-200)';
    }
}

document.querySelectorAll('input[name="rating"]').forEach(function(input) {
    input.addEventListener('change', function() { resetStars(); });
});

function toggleWishlist(event, propertyId) {
    event.preventDefault();
    fetch('<?php echo url("/api/wishlist.php"); ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'property_id=' + propertyId
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            var btn = document.getElementById('wishlistBtn');
            var text = document.getElementById('wishlistText');
            var icon = btn.querySelector('i');
            if (data.action === 'added') {
                btn.classList.remove('btn-ghost');
                btn.classList.add('btn-accent');
                icon.className = 'bi bi-heart-fill';
                text.textContent = 'Remove from Wishlist';
            } else {
                btn.classList.remove('btn-accent');
                btn.classList.add('btn-ghost');
                icon.className = 'bi bi-heart';
                text.textContent = 'Add to Wishlist';
            }
        } else if (data.message === 'Not logged in') {
            window.location.href = '<?php echo url("/login.php"); ?>';
        }
    })
    .catch(function() {});
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
