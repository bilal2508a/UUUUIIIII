<?php
// Mehmaan Hub - Property Card Component
// Expects $p variable with property data (from get_all_properties or get_wishlist)
$primaryImg = !empty($p['primary_image']) ? $p['primary_image'] : get_primary_image($p['id']);
if (!$primaryImg) {
    $primaryImg = 'https://images.pexels.com/photos/1571460/pexels-photo-1571460.jpeg?auto=compress&cs=tinysrgb&w=800';
}
$imgSrc = image_url($primaryImg);
$typeLabel = get_property_type_label($p['property_type']);
$ratingData = get_avg_rating($p['id']);
$avgRating = $ratingData['avg_rating'] ? round($ratingData['avg_rating'], 1) : 0;
$reviewCount = $ratingData['count'] ?? 0;

$priceDisplay = format_price($p['price']);
if ($p['price_period'] === 'per_day') {
    $priceDisplay .= '<span style="font-size:0.7rem;font-weight:400;color:var(--slate-500);">/day</span>';
} elseif ($p['price_period'] === 'both') {
    $priceDisplay .= '<span style="font-size:0.7rem;font-weight:400;color:var(--slate-500);">/month</span>';
    if ($p['price_per_day'] !== null) {
        $priceDisplay .= ' &middot; ' . format_price($p['price_per_day']) . '<span style="font-size:0.7rem;font-weight:400;color:var(--slate-500);">/day</span>';
    }
} else {
    $priceDisplay .= '<span style="font-size:0.7rem;font-weight:400;color:var(--slate-500);">/month</span>';
}
?>
<a href="<?php echo url('/property-details.php?id=' . (int)$p['id']); ?>" class="property-card">
    <div class="property-card-image">
        <img src="<?php echo e($imgSrc); ?>" alt="<?php echo e($p['title']); ?>" loading="lazy">
        <?php if (!empty($p['featured'])): ?>
            <span class="badge badge-featured"><i class="bi bi-star-fill"></i> Featured</span>
        <?php endif; ?>
        <span class="badge badge-type"><?php echo e($typeLabel); ?></span>
        <span class="badge badge-price"><?php echo $priceDisplay; ?></span>
    </div>
    <div class="property-card-body">
        <h5 class="property-card-title line-clamp-1"><?php echo e($p['title']); ?></h5>
        <div class="d-flex align-items-center gap-1 mb-2">
            <i class="bi bi-star-fill" style="color:var(--warning-500);font-size:0.85rem;"></i>
            <span style="font-weight:700;font-size:0.9rem;color:var(--slate-900);"><?php echo e($avgRating > 0 ? number_format($avgRating, 1) : 'New'); ?></span>
            <?php if ($reviewCount > 0): ?>
                <span style="color:var(--slate-500);font-size:0.8rem;">(<?php echo (int)$reviewCount; ?> reviews)</span>
            <?php endif; ?>
        </div>
        <p class="property-card-location line-clamp-1">
            <i class="bi bi-geo-alt"></i> <?php echo e($p['city'] . ', ' . $p['area']); ?>
        </p>
        <div class="property-card-info">
            <span><i class="bi bi-house"></i> <?php echo (int)$p['bedrooms']; ?> Beds</span>
            <span><i class="bi bi-droplet"></i> <?php echo (int)$p['bathrooms']; ?> Baths</span>
            <?php if (!empty($p['area_sqft'])): ?>
                <span><i class="bi bi-rulers"></i> <?php echo (int)$p['area_sqft']; ?> sqft</span>
            <?php endif; ?>
        </div>
    </div>
</a>
