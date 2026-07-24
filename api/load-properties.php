<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

// Parse GET params
$offset = (int)($_GET['offset'] ?? 0);
$limit = (int)($_GET['limit'] ?? 6);
if ($limit > 20) $limit = 20;
if ($limit < 1) $limit = 6;

$search = trim($_GET['search'] ?? '');
$type = trim($_GET['type'] ?? '');
$city = trim($_GET['city'] ?? '');
$minPrice = $_GET['min_price'] ?? null;
$maxPrice = $_GET['max_price'] ?? null;
if ($minPrice !== '' && $minPrice !== null) $minPrice = (float)$minPrice; else $minPrice = null;
if ($maxPrice !== '' && $maxPrice !== null) $maxPrice = (float)$maxPrice; else $maxPrice = null;

// Fetch properties with filters using helper (no limit here, we handle offset/limit manually)
// Build the query inline to support offset
$sql = "SELECT p.*, u.name as owner_name FROM properties p JOIN users u ON p.owner_id = u.id WHERE p.status = 'available'";
$params = [];
if ($search) {
    $sql .= " AND (p.title LIKE ? OR p.description LIKE ? OR p.city LIKE ? OR p.address LIKE ?)";
    $term = "%$search%";
    array_push($params, $term, $term, $term, $term);
}
if ($type) {
    $sql .= " AND p.property_type = ?";
    $params[] = $type;
}
if ($city) {
    $sql .= " AND p.city LIKE ?";
    $params[] = "%$city%";
}
if ($minPrice !== null) {
    $sql .= " AND p.price >= ?";
    $params[] = $minPrice;
}
if ($maxPrice !== null) {
    $sql .= " AND p.price <= ?";
    $params[] = $maxPrice;
}
$sql .= " ORDER BY p.featured DESC, p.created_at DESC LIMIT $limit OFFSET $offset";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$properties = $stmt->fetchAll();

// Get booked property IDs for current user (if tenant)
$bookedIds = [];
$apiUser = currentUser();
if ($apiUser && $apiUser['role'] === 'tenant') {
    $bookedIds = get_user_booked_property_ids($apiUser['id']);
}
$bookedSet = array_flip($bookedIds);

// Render property cards as HTML
$html = '';
foreach ($properties as $p) {
    $img = get_primary_image($p['id']);
    $imgSrc = $img ? image_url($img) : 'https://images.pexels.com/photos/1571460/pexels-photo-1571460.jpeg?auto=compress&cs=tinysrgb&w=800';
    $typeLabel = get_property_type_label($p['property_type']);
    $ratingData = get_avg_rating($p['id']);
    $avgRating = $ratingData['avg_rating'] ? round($ratingData['avg_rating'], 1) : 0;
    $reviewCount = $ratingData['count'] ?? 0;
    $isBooked = isset($bookedSet[$p['id']]);

    // Price display based on price_period
    $priceDisplay = format_price($p['price']);
    if ($p['price_period'] === 'per_day') {
        $priceDisplay .= '<span style="font-size:0.7rem;font-weight:400;">/day</span>';
    } elseif ($p['price_period'] === 'both') {
        $priceDisplay .= '<span style="font-size:0.7rem;font-weight:400;">/month</span>';
        if ($p['price_per_day'] !== null) {
            $priceDisplay .= ' &middot; ' . format_price($p['price_per_day']) . '<span style="font-size:0.7rem;font-weight:400;">/day</span>';
        }
    } else {
        $priceDisplay .= '<span style="font-size:0.7rem;font-weight:400;">/month</span>';
    }

    ob_start();
    ?>
    <div class="col-md-6 col-lg-4 property-col">
        <a href="<?php echo url('/property-details.php?id=' . (int)$p['id']); ?>" class="property-card">
            <div class="property-card-image">
                <img src="<?php echo e($imgSrc); ?>" alt="<?php echo e($p['title']); ?>" loading="lazy">
                <?php if (!empty($p['featured'])): ?>
                    <span class="badge badge-featured"><i class="bi bi-star-fill"></i> Featured</span>
                <?php endif; ?>
                <?php if ($isBooked): ?>
                    <span class="badge badge-success" style="position:absolute;bottom:12px;left:12px;"><i class="bi bi-check-circle"></i> Booked by You</span>
                <?php endif; ?>
                <span class="badge badge-type"><?php echo e($typeLabel); ?></span>
                <span class="badge badge-price glass"><?php echo $priceDisplay; ?></span>
            </div>
            <div class="property-card-body">
                <h5 class="property-card-title line-clamp-1"><?php echo e($p['title']); ?></h5>
                <div class="d-flex align-items-center gap-1 mb-2">
                    <i class="bi bi-star-fill" style="color:#f59e0b;font-size:0.85rem;"></i>
                    <span style="font-weight:700;font-size:0.9rem;color:#0f172a;"><?php echo e($avgRating > 0 ? number_format($avgRating, 1) : 'New'); ?></span>
                    <?php if ($reviewCount > 0): ?>
                        <span style="color:#64748b;font-size:0.8rem;">(<?php echo (int)$reviewCount; ?> reviews)</span>
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
    </div>
    <?php
    $html .= ob_get_clean();
}

// Count total matching properties for pagination
$countSql = "SELECT COUNT(*) as c FROM properties p WHERE p.status = 'available'";
$countParams = [];
if ($search) {
    $countSql .= " AND (p.title LIKE ? OR p.description LIKE ? OR p.city LIKE ? OR p.address LIKE ?)";
    $term = "%$search%";
    array_push($countParams, $term, $term, $term, $term);
}
if ($type) {
    $countSql .= " AND p.property_type = ?";
    $countParams[] = $type;
}
if ($city) {
    $countSql .= " AND p.city LIKE ?";
    $countParams[] = "%$city%";
}
if ($minPrice !== null) {
    $countSql .= " AND p.price >= ?";
    $countParams[] = $minPrice;
}
if ($maxPrice !== null) {
    $countSql .= " AND p.price <= ?";
    $countParams[] = $maxPrice;
}

$stmt = db()->prepare($countSql);
$stmt->execute($countParams);
$countRow = $stmt->fetch();
$total = (int)($countRow['c'] ?? 0);

echo json_encode([
    'html' => $html,
    'total' => $total,
    'has_more' => ($offset + $limit) < $total
]);
