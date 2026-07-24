<?php
// Mehmaan Hub - Add Property
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$user = requireRole('owner');

$cities = getCities();
$errors = [];
$form = [
    'title' => '', 'description' => '', 'property_type' => 'apartment',
    'address' => '', 'city' => '', 'area' => '', 'price' => '',
    'price_period' => 'per_month', 'price_per_day' => '',
    'bedrooms' => '1', 'bathrooms' => '1', 'area_sqft' => '',
    'is_furnished' => false, 'has_parking' => false, 'has_wifi' => false,
    'has_ac' => false, 'has_generator' => false,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['title'] = trim($_POST['title'] ?? '');
    $form['description'] = trim($_POST['description'] ?? '');
    $form['property_type'] = $_POST['property_type'] ?? 'apartment';
    $form['address'] = trim($_POST['address'] ?? '');
    $form['city'] = trim($_POST['city'] ?? '');
    $form['area'] = trim($_POST['area'] ?? '');
    $form['price'] = trim($_POST['price'] ?? '');
    $form['price_period'] = $_POST['price_period'] ?? 'per_month';
    $form['price_per_day'] = trim($_POST['price_per_day'] ?? '');
    $form['bedrooms'] = (int)($_POST['bedrooms'] ?? 1);
    $form['bathrooms'] = (int)($_POST['bathrooms'] ?? 1);
    $form['area_sqft'] = trim($_POST['area_sqft'] ?? '');
    $form['is_furnished'] = isset($_POST['is_furnished']);
    $form['has_parking'] = isset($_POST['has_parking']);
    $form['has_wifi'] = isset($_POST['has_wifi']);
    $form['has_ac'] = isset($_POST['has_ac']);
    $form['has_generator'] = isset($_POST['has_generator']);

    // Validation
    if ($form['title'] === '') $errors[] = 'Title is required.';
    if ($form['description'] === '') $errors[] = 'Description is required.';
    if ($form['address'] === '') $errors[] = 'Address is required.';
    if ($form['city'] === '') $errors[] = 'City is required.';
    if ($form['price'] === '' || !is_numeric($form['price'])) $errors[] = 'Valid price is required.';
    if ($form['price_period'] === 'both' && ($form['price_per_day'] === '' || !is_numeric($form['price_per_day']))) {
        $errors[] = 'Daily price is required when pricing is set to both.';
    }

    $validTypes = ['apartment', 'house', 'room', 'studio', 'villa'];
    if (!in_array($form['property_type'], $validTypes)) {
        $errors[] = 'Invalid property type.';
    }

    if (empty($errors)) {
        try {
            $stmt = db()->prepare('INSERT INTO properties (owner_id, title, description, property_type, address, city, area, price, price_period, price_per_day, bedrooms, bathrooms, area_sqft, is_furnished, has_parking, has_wifi, has_ac, has_generator, status, featured, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
            $stmt->execute([
                $user['id'],
                $form['title'],
                $form['description'],
                $form['property_type'],
                $form['address'],
                $form['city'],
                $form['area'],
                $form['price'],
                $form['price_period'],
                ($form['price_period'] === 'both' ? $form['price_per_day'] : null),
                $form['bedrooms'],
                $form['bathrooms'],
                $form['area_sqft'] !== '' ? $form['area_sqft'] : null,
                $form['is_furnished'] ? 1 : 0,
                $form['has_parking'] ? 1 : 0,
                $form['has_wifi'] ? 1 : 0,
                $form['has_ac'] ? 1 : 0,
                $form['has_generator'] ? 1 : 0,
                'available',
                0,
            ]);
            $propertyId = db()->lastInsertId();

            // Handle image uploads
            if (!empty($_FILES['images']) && isset($_FILES['images']['name'])) {
                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0775, true);
                }

                $files = $_FILES['images'];
                $fileCount = count($files['name']);
                $isFirst = true;
                for ($i = 0; $i < $fileCount; $i++) {
                    if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
                    if (!is_uploaded_file($files['tmp_name'][$i])) continue;

                    $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (!in_array($ext, $allowed)) continue;

                    $filename = 'prop_' . $propertyId . '_' . uniqid() . '.' . $ext;
                    $targetPath = UPLOAD_DIR . $filename;

                    if (move_uploaded_file($files['tmp_name'][$i], $targetPath)) {
                        $imgStmt = db()->prepare('INSERT INTO property_images (property_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)');
                        $imgStmt->execute([$propertyId, $filename, $isFirst ? 1 : 0, $i]);
                        $isFirst = false;
                    }
                }
            }

            flash('success', 'Property added successfully!');
            redirect('owner-dashboard.php');
        } catch (PDOException $ex) {
            $errors[] = 'Error saving property: ' . $ex->getMessage();
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="container-app py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex align-items-center gap-3 mb-4">
                <a href="<?php echo url('/owner-dashboard.php'); ?>" class="btn btn-ghost btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
                <div>
                    <h1 style="font-size:1.75rem;font-weight:800;color:#0f172a;margin:0;">Add New Property</h1>
                    <p style="color:#64748b;margin:0;">Fill in the details below to list your property.</p>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul style="margin:0;padding-left:1.25rem;">
                        <?php foreach ($errors as $err): ?>
                            <li><?php echo e($err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo url('/add-property.php'); ?>" enctype="multipart/form-data">
                <!-- Basic Information -->
                <div class="card mb-4">
                    <div class="card-header-mh">
                        <h5 class="mb-0"><i class="bi bi-info-circle" style="color:#0ea5e9;"></i> Basic Information</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label-mh">Title <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="title" class="form-control-mh" value="<?php echo e($form['title']); ?>" placeholder="e.g. Modern Apartment in Clifton" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-mh">Description <span style="color:#ef4444;">*</span></label>
                            <textarea name="description" class="form-control-mh" rows="4" placeholder="Describe your property..." required><?php echo e($form['description']); ?></textarea>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-mh">Property Type <span style="color:#ef4444;">*</span></label>
                                <select name="property_type" class="form-control-mh">
                                    <option value="apartment" <?php echo $form['property_type'] === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                                    <option value="house" <?php echo $form['property_type'] === 'house' ? 'selected' : ''; ?>>House</option>
                                    <option value="room" <?php echo $form['property_type'] === 'room' ? 'selected' : ''; ?>>Room</option>
                                    <option value="studio" <?php echo $form['property_type'] === 'studio' ? 'selected' : ''; ?>>Studio</option>
                                    <option value="villa" <?php echo $form['property_type'] === 'villa' ? 'selected' : ''; ?>>Villa</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-mh">Area / Neighborhood</label>
                                <input type="text" name="area" class="form-control-mh" value="<?php echo e($form['area']); ?>" placeholder="e.g. Clifton, DHA">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div class="card mb-4">
                    <div class="card-header-mh">
                        <h5 class="mb-0"><i class="bi bi-geo-alt" style="color:#0ea5e9;"></i> Location</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label-mh">Address <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="address" class="form-control-mh" value="<?php echo e($form['address']); ?>" placeholder="Full street address" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-mh">City <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="city" class="form-control-mh" value="<?php echo e($form['city']); ?>" list="cityList" placeholder="Select or type city" required>
                            <datalist id="cityList">
                                <?php foreach ($cities as $city): ?>
                                    <option value="<?php echo e($city); ?>"><?php echo e($city); ?></option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                    </div>
                </div>

                <!-- Pricing -->
                <div class="card mb-4">
                    <div class="card-header-mh">
                        <h5 class="mb-0"><i class="bi bi-cash" style="color:#0ea5e9;"></i> Pricing</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label-mh">Pricing Period <span style="color:#ef4444;">*</span></label>
                            <select name="price_period" class="form-control-mh" id="pricePeriod" onchange="toggleDailyPrice()">
                                <option value="per_month" <?php echo $form['price_period'] === 'per_month' ? 'selected' : ''; ?>>Per Month</option>
                                <option value="per_day" <?php echo $form['price_period'] === 'per_day' ? 'selected' : ''; ?>>Per Day</option>
                                <option value="both" <?php echo $form['price_period'] === 'both' ? 'selected' : ''; ?>>Both (Monthly & Daily)</option>
                            </select>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-mh">
                                    Price (Rs) <span style="color:#ef4444;">*</span>
                                    <small id="priceLabelSuffix" style="color:#64748b;font-weight:400;">/month</small>
                                </label>
                                <input type="number" name="price" class="form-control-mh" value="<?php echo e($form['price']); ?>" min="0" step="100" placeholder="e.g. 50000" required>
                            </div>
                            <div class="col-md-6" id="dailyPriceField" style="display:none;">
                                <label class="form-label-mh">Price Per Day (Rs) <span style="color:#ef4444;">*</span></label>
                                <input type="number" name="price_per_day" class="form-control-mh" value="<?php echo e($form['price_per_day']); ?>" min="0" step="100" placeholder="e.g. 2000">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Details & Amenities -->
                <div class="card mb-4">
                    <div class="card-header-mh">
                        <h5 class="mb-0"><i class="bi bi-house-door" style="color:#0ea5e9;"></i> Details & Amenities</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label-mh">Bedrooms</label>
                                <input type="number" name="bedrooms" class="form-control-mh" value="<?php echo (int)$form['bedrooms']; ?>" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-mh">Bathrooms</label>
                                <input type="number" name="bathrooms" class="form-control-mh" value="<?php echo (int)$form['bathrooms']; ?>" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-mh">Area (sqft)</label>
                                <input type="number" name="area_sqft" class="form-control-mh" value="<?php echo e($form['area_sqft']); ?>" min="0" placeholder="e.g. 1200">
                            </div>
                        </div>
                        <label class="form-label-mh">Amenities</label>
                        <div class="d-flex flex-wrap gap-2">
                            <label class="amenity-chip <?php echo $form['is_furnished'] ? 'active' : ''; ?>">
                                <input type="checkbox" name="is_furnished" value="1" <?php echo $form['is_furnished'] ? 'checked' : ''; ?> style="display:none;">
                                <i class="bi bi-house-check"></i> Furnished
                            </label>
                            <label class="amenity-chip <?php echo $form['has_parking'] ? 'active' : ''; ?>">
                                <input type="checkbox" name="has_parking" value="1" <?php echo $form['has_parking'] ? 'checked' : ''; ?> style="display:none;">
                                <i class="bi bi-car-front"></i> Parking
                            </label>
                            <label class="amenity-chip <?php echo $form['has_wifi'] ? 'active' : ''; ?>">
                                <input type="checkbox" name="has_wifi" value="1" <?php echo $form['has_wifi'] ? 'checked' : ''; ?> style="display:none;">
                                <i class="bi bi-wifi"></i> WiFi
                            </label>
                            <label class="amenity-chip <?php echo $form['has_ac'] ? 'active' : ''; ?>">
                                <input type="checkbox" name="has_ac" value="1" <?php echo $form['has_ac'] ? 'checked' : ''; ?> style="display:none;">
                                <i class="bi bi-snow"></i> AC
                            </label>
                            <label class="amenity-chip <?php echo $form['has_generator'] ? 'active' : ''; ?>">
                                <input type="checkbox" name="has_generator" value="1" <?php echo $form['has_generator'] ? 'checked' : ''; ?> style="display:none;">
                                <i class="bi bi-lightning-charge"></i> Generator
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Images -->
                <div class="card mb-4">
                    <div class="card-header-mh">
                        <h5 class="mb-0"><i class="bi bi-images" style="color:#0ea5e9;"></i> Property Images</h5>
                    </div>
                    <div class="card-body p-4">
                        <label class="form-label-mh">Upload Images</label>
                        <input type="file" name="images[]" class="form-control-mh" accept="image/*" multiple>
                        <small style="color:#64748b;">You can select multiple images. The first image will be set as the primary image.</small>
                    </div>
                </div>

                <!-- Submit -->
                <div class="d-flex gap-2 justify-content-end">
                    <a href="<?php echo url('/owner-dashboard.php'); ?>" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add Property</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleDailyPrice() {
    var period = document.getElementById('pricePeriod').value;
    var dailyField = document.getElementById('dailyPriceField');
    var suffix = document.getElementById('priceLabelSuffix');
    if (period === 'both') {
        dailyField.style.display = '';
        suffix.textContent = '/month';
    } else if (period === 'per_day') {
        dailyField.style.display = 'none';
        suffix.textContent = '/day';
    } else {
        dailyField.style.display = 'none';
        suffix.textContent = '/month';
    }
}
toggleDailyPrice();

// Amenity chip toggle
document.querySelectorAll('.amenity-chip input[type="checkbox"]').forEach(function(cb) {
    cb.addEventListener('change', function() {
        if (this.checked) {
            this.parentElement.classList.add('active');
        } else {
            this.parentElement.classList.remove('active');
        }
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
