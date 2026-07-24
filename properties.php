<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$user = currentUser();
$cities = getCities();

// Filters from URL
$search = trim($_GET['search'] ?? '');
$type = $_GET['type'] ?? '';
$city = $_GET['city'] ?? '';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

$minPriceNum = ($minPrice !== '') ? (float)$minPrice : null;
$maxPriceNum = ($maxPrice !== '') ? (float)$maxPrice : null;

// For initial page load, fetch first 6 properties
$initialLimit = 6;
$properties = get_all_properties($initialLimit, $search, $type, $city, $minPriceNum, $maxPriceNum);

// Sort options
if ($sort === 'price_low') {
    usort($properties, function($a, $b) { return $a['price'] <=> $b['price']; });
} elseif ($sort === 'price_high') {
    usort($properties, function($a, $b) { return $b['price'] <=> $a['price']; });
} elseif ($sort === 'rating') {
    usort($properties, function($a, $b) {
        $ra = get_avg_rating($a['id']);
        $rb = get_avg_rating($b['id']);
        return ($rb['avg_rating'] ?? 0) <=> ($ra['avg_rating'] ?? 0);
    });
}

// Get total count for "Show More" logic
$totalProperties = get_all_properties(null, $search, $type, $city, $minPriceNum, $maxPriceNum);
$totalCount = count($totalProperties);

include __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<div style="background:linear-gradient(135deg,var(--primary-700),var(--accent-600));padding:2.5rem 0;color:#fff;position:relative;overflow:hidden;">
    <div style="position:absolute;top:-80px;right:-80px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,0.12),transparent 70%);"></div>
    <div class="container-app" style="position:relative;z-index:2;">
        <h1 style="font-size:2.25rem;font-weight:800;margin:0;letter-spacing:-0.02em;">Browse Properties</h1>
        <p style="margin:0.5rem 0 0;opacity:0.92;font-size:1.05rem;">Find your perfect rental from our verified listings</p>
    </div>
</div>

<section style="padding:2.5rem 0;">
    <div class="container-app">
        <!-- Filters -->
        <div class="card-premium" style="padding:1.5rem;margin-bottom:2rem;">
            <form id="filterForm" method="GET" action="<?php echo url('/properties.php'); ?>" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:180px;">
                    <label class="form-label-mh">Search</label>
                    <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Title, city, address..." class="form-control-mh">
                </div>
                <div style="min-width:140px;">
                    <label class="form-label-mh">Type</label>
                    <select name="type" class="form-control-mh">
                        <option value="">All Types</option>
                        <option value="apartment" <?php echo $type === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                        <option value="house" <?php echo $type === 'house' ? 'selected' : ''; ?>>House</option>
                        <option value="room" <?php echo $type === 'room' ? 'selected' : ''; ?>>Room</option>
                        <option value="studio" <?php echo $type === 'studio' ? 'selected' : ''; ?>>Studio</option>
                        <option value="villa" <?php echo $type === 'villa' ? 'selected' : ''; ?>>Villa</option>
                    </select>
                </div>
                <div style="min-width:140px;">
                    <label class="form-label-mh">City</label>
                    <select name="city" class="form-control-mh">
                        <option value="">All Cities</option>
                        <?php foreach ($cities as $c): ?>
                            <option value="<?php echo e($c); ?>" <?php echo $city === $c ? 'selected' : ''; ?>><?php echo e($c); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="min-width:120px;">
                    <label class="form-label-mh">Min Price</label>
                    <input type="number" name="min_price" value="<?php echo e($minPrice); ?>" placeholder="0" class="form-control-mh">
                </div>
                <div style="min-width:120px;">
                    <label class="form-label-mh">Max Price</label>
                    <input type="number" name="max_price" value="<?php echo e($maxPrice); ?>" placeholder="∞" class="form-control-mh">
                </div>
                <div style="min-width:140px;">
                    <label class="form-label-mh">Sort By</label>
                    <select name="sort" class="form-control-mh">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Top Rated</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Filter</button>
                <a href="<?php echo url('/properties.php'); ?>" class="btn btn-ghost"><i class="bi bi-arrow-clockwise"></i> Reset</a>
            </form>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
            <p style="margin:0;color:var(--slate-500);font-weight:500;">
                <strong style="color:var(--slate-900);" id="resultCount"><?php echo $totalCount; ?></strong> propert<?php echo $totalCount === 1 ? 'y' : 'ies'; ?> found
            </p>
            <span id="filterLoading" style="display:none;color:var(--primary-600);font-size:0.85rem;font-weight:500;"><i class="bi bi-arrow-repeat spin"></i> Filtering...</span>
        </div>

        <div id="resultsContainer">
            <?php if (empty($properties)): ?>
                <div class="card-premium" style="text-align:center;padding:4rem 2rem;">
                    <div style="width:80px;height:80px;border-radius:var(--radius-md);background:var(--slate-100);display:flex;align-items:center;justify-content:center;color:var(--slate-300);font-size:2.5rem;margin:0 auto 1.25rem;">
                        <i class="bi bi-search"></i>
                    </div>
                    <h3 style="margin-top:1rem;color:var(--slate-900);font-weight:700;">No properties found</h3>
                    <p style="color:var(--slate-500);">Try adjusting your filters or search terms.</p>
                    <a href="<?php echo url('/properties.php'); ?>" class="btn btn-primary" style="margin-top:1rem;"><i class="bi bi-arrow-clockwise"></i> Clear Filters</a>
                </div>
            <?php else: ?>
                <div class="row g-4" id="propertyGrid">
                    <?php foreach ($properties as $p): ?>
                        <div class="col-md-6 col-lg-4 property-col">
                            <?php include __DIR__ . '/includes/property_card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($totalCount > $initialLimit): ?>
                    <div style="text-align:center;margin-top:2.5rem;" id="showMoreWrap">
                        <button id="showMoreBtn" class="btn btn-primary btn-lg" style="padding:0.75rem 2rem;font-weight:600;">
                            <i class="bi bi-arrow-down-circle"></i> Show More Properties
                        </button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
(function() {
    var form = document.getElementById('filterForm');
    var resultsContainer = document.getElementById('resultsContainer');
    var resultCount = document.getElementById('resultCount');
    var loading = document.getElementById('filterLoading');
    var debounceTimer = null;
    var currentOffset = 6;
    var batchSize = 6;

    function fetchResults(isLoadMore) {
        var params = new URLSearchParams(new FormData(form));
        var offset = isLoadMore ? currentOffset : 0;
        var url = '<?php echo url("/api/load-properties.php"); ?>?limit=' + batchSize + '&offset=' + offset + '&' + params.toString();
        loading.style.display = '';

        fetch(url)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (isLoadMore) {
                    var grid = document.getElementById('propertyGrid');
                    if (grid) {
                        grid.insertAdjacentHTML('beforeend', data.html);
                    }
                    currentOffset += batchSize;
                } else {
                    var html = '<div class="row g-4" id="propertyGrid">' + (data.html || '') + '</div>';
                    if (!data.html) {
                        html = '<div class="card-premium" style="text-align:center;padding:4rem 2rem;"><div style="width:80px;height:80px;border-radius:var(--radius-md);background:var(--slate-100);display:flex;align-items:center;justify-content:center;color:var(--slate-300);font-size:2.5rem;margin:0 auto 1.25rem;"><i class="bi bi-search"></i></div><h3 style="margin-top:1rem;color:var(--slate-900);font-weight:700;">No properties found</h3><p style="color:var(--slate-500);">Try adjusting your filters or search terms.</p></div>';
                    }
                    resultsContainer.innerHTML = html;
                    currentOffset = batchSize;
                }
                resultCount.textContent = data.total || 0;

                var showMoreWrap = document.getElementById('showMoreWrap');
                if (data.has_more) {
                    if (!showMoreWrap) {
                        var btnHtml = '<div style="text-align:center;margin-top:2.5rem;" id="showMoreWrap"><button id="showMoreBtn" class="btn btn-primary btn-lg" style="padding:0.75rem 2rem;font-weight:600;"><i class="bi bi-arrow-down-circle"></i> Show More Properties</button></div>';
                        resultsContainer.insertAdjacentHTML('beforeend', btnHtml);
                    }
                } else if (showMoreWrap) {
                    showMoreWrap.remove();
                }

                loading.style.display = 'none';
            })
            .catch(function() { loading.style.display = 'none'; });
    }

    form.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() { fetchResults(false); }, 350);
    });
    form.addEventListener('change', function() {
        clearTimeout(debounceTimer);
        fetchResults(false);
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('#showMoreBtn')) {
            fetchResults(true);
        }
    });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
