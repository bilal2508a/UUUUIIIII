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
    'has_ac' => false, 'has_generator' => false, 'has_kitchen' => false,
    'has_swimming_pool' => false, 'has_gym' => false, 'has_security' => false,
    'has_elevator' => false, 'has_garden' => false, 'has_heating' => false,
    'has_cctv' => false,
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
    $form['has_kitchen'] = isset($_POST['has_kitchen']);
    $form['has_swimming_pool'] = isset($_POST['has_swimming_pool']);
    $form['has_gym'] = isset($_POST['has_gym']);
    $form['has_security'] = isset($_POST['has_security']);
    $form['has_elevator'] = isset($_POST['has_elevator']);
    $form['has_garden'] = isset($_POST['has_garden']);
    $form['has_heating'] = isset($_POST['has_heating']);
    $form['has_cctv'] = isset($_POST['has_cctv']);

    // Validation
    if ($form['title'] === '') $errors[] = 'Title is required.';
    if ($form['description'] === '') $errors[] = 'Description is required.';
    if ($form['address'] === '') $errors[] = 'Address is required.';
    if ($form['city'] === '') $errors[] = 'City is required.';
    if ($form['price_period'] === 'per_day') {
        if ($form['price'] === '' || !is_numeric($form['price'])) $errors[] = 'Valid daily price is required.';
    } elseif ($form['price_period'] === 'per_month') {
        if ($form['price'] === '' || !is_numeric($form['price'])) $errors[] = 'Valid monthly price is required.';
    } elseif ($form['price_period'] === 'both') {
        if ($form['price'] === '' || !is_numeric($form['price'])) $errors[] = 'Valid monthly price is required.';
        if ($form['price_per_day'] === '' || !is_numeric($form['price_per_day'])) $errors[] = 'Valid daily price is required when pricing is set to both.';
    }

    $validTypes = ['apartment', 'house', 'room', 'studio', 'villa'];
    if (!in_array($form['property_type'], $validTypes)) {
        $errors[] = 'Invalid property type.';
    }

    if (empty($errors)) {
        try {
            $stmt = db()->prepare('INSERT INTO properties (owner_id, title, description, property_type, address, city, area, price, price_period, price_per_day, bedrooms, bathrooms, area_sqft, is_furnished, has_parking, has_wifi, has_ac, has_generator, has_kitchen, has_swimming_pool, has_gym, has_security, has_elevator, has_garden, has_heating, has_cctv, status, featured, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
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
                $form['has_kitchen'] ? 1 : 0,
                $form['has_swimming_pool'] ? 1 : 0,
                $form['has_gym'] ? 1 : 0,
                $form['has_security'] ? 1 : 0,
                $form['has_elevator'] ? 1 : 0,
                $form['has_garden'] ? 1 : 0,
                $form['has_heating'] ? 1 : 0,
                $form['has_cctv'] ? 1 : 0,
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

<!-- Page Hero -->
<div style="background:linear-gradient(135deg,var(--primary-700),var(--accent-600));padding:2.5rem 0;color:#fff;position:relative;overflow:hidden;">
    <div style="position:absolute;top:-80px;right:-80px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,0.12),transparent 70%);"></div>
    <div class="container-app" style="position:relative;z-index:2;">
        <div class="d-flex align-items-center gap-3">
            <a href="<?php echo url('/owner-dashboard.php'); ?>" class="btn btn-light" style="background:rgba(255,255,255,0.15);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.25);color:#fff;border-radius:var(--radius);padding:0.5rem 0.875rem;font-weight:600;">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 style="font-size:2rem;font-weight:800;margin:0;letter-spacing:-0.02em;">Add New Property</h1>
                <p style="margin:0.25rem 0 0;opacity:0.92;">Fill in the details below to list your property.</p>
            </div>
        </div>
    </div>
</div>

<div class="container-app py-5">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" style="border-radius:var(--radius);box-shadow:var(--shadow-md);">
            <ul style="margin:0;padding-left:1.25rem;">
                <?php foreach ($errors as $err): ?>
                    <li><?php echo e($err); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo url('/add-property.php'); ?>" enctype="multipart/form-data">
        <!-- Basic Information -->
        <div class="form-section">
            <div class="form-section-header">
                <div class="form-section-icon" style="background:linear-gradient(135deg,var(--primary-600),var(--primary-500));">
                    <i class="bi bi-info-circle"></i>
                </div>
                <div>
                    <h5 class="form-section-title">Basic Information</h5>
                    <p class="form-section-subtitle">Title, description, and property type</p>
                </div>
            </div>
            <div class="form-section-body">
                <div class="mb-3">
                    <label class="form-label-mh">Title <span style="color:var(--error-500);">*</span></label>
                    <input type="text" name="title" class="form-control-mh" value="<?php echo e($form['title']); ?>" placeholder="e.g. Modern Apartment in Clifton" required>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label-mh mb-0">Description <span style="color:var(--error-500);">*</span></label>
                        <button type="button" class="btn btn-primary btn-sm" id="aiGenBtn" onclick="generateAIDescription()">
                            <i class="bi bi-stars"></i> AI Generate
                        </button>
                    </div>
                    <textarea name="description" id="descriptionField" class="form-control-mh" rows="4" placeholder="Describe your property..." required><?php echo e($form['description']); ?></textarea>
                    <div id="aiVariations" style="margin-top:0.75rem;display:none;">
                        <p style="font-size:0.8rem;color:var(--slate-500);margin-bottom:0.5rem;">Generated descriptions (click to use):</p>
                        <div id="aiVariationsList" style="display:flex;flex-direction:column;gap:0.5rem;"></div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-mh">Property Type <span style="color:var(--error-500);">*</span></label>
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
        <div class="form-section">
            <div class="form-section-header">
                <div class="form-section-icon" style="background:linear-gradient(135deg,var(--accent-600),var(--accent-500));">
                    <i class="bi bi-geo-alt"></i>
                </div>
                <div>
                    <h5 class="form-section-title">Location</h5>
                    <p class="form-section-subtitle">Where is your property located?</p>
                </div>
            </div>
            <div class="form-section-body">
                <div class="mb-3">
                    <label class="form-label-mh">Address <span style="color:var(--error-500);">*</span></label>
                    <input type="text" name="address" class="form-control-mh" value="<?php echo e($form['address']); ?>" placeholder="Full street address" required>
                </div>
                <div class="mb-3">
                    <label class="form-label-mh">City <span style="color:var(--error-500);">*</span></label>
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
        <div class="form-section">
            <div class="form-section-header">
                <div class="form-section-icon" style="background:linear-gradient(135deg,var(--gold-400),var(--gold-600));box-shadow:var(--shadow-gold);">
                    <i class="bi bi-cash"></i>
                </div>
                <div>
                    <h5 class="form-section-title">Pricing</h5>
                    <p class="form-section-subtitle">Set your rental rates</p>
                </div>
            </div>
            <div class="form-section-body">
                <div class="mb-3">
                    <label class="form-label-mh">Pricing Period <span style="color:var(--error-500);">*</span></label>
                    <select name="price_period" class="form-control-mh" id="pricePeriod">
                        <option value="per_month" <?php echo $form['price_period'] === 'per_month' ? 'selected' : ''; ?>>Per Month</option>
                        <option value="per_day" <?php echo $form['price_period'] === 'per_day' ? 'selected' : ''; ?>>Per Day</option>
                        <option value="both" <?php echo $form['price_period'] === 'both' ? 'selected' : ''; ?>>Both (Monthly & Daily)</option>
                    </select>
                </div>
                <div class="row g-3">
                    <div class="col-md-6" id="mainPriceField">
                        <label class="form-label-mh">
                            <span id="mainPriceLabel">Monthly Price (Rs)</span> <span style="color:var(--error-500);">*</span>
                        </label>
                        <input type="number" name="price" class="form-control-mh" id="priceInput" value="<?php echo e($form['price']); ?>" min="0" step="100" placeholder="e.g. 50000">
                    </div>
                    <div class="col-md-6" id="dailyPriceField" style="display:none;">
                        <label class="form-label-mh">Daily Price (Rs) <span style="color:var(--error-500);">*</span></label>
                        <input type="number" name="price_per_day" class="form-control-mh" id="dailyPriceInput" value="<?php echo e($form['price_per_day']); ?>" min="0" step="100" placeholder="e.g. 2000">
                    </div>
                </div>
            </div>
        </div>

        <!-- Details & Amenities -->
        <div class="form-section">
            <div class="form-section-header">
                <div class="form-section-icon" style="background:linear-gradient(135deg,var(--success-500),var(--success-600));">
                    <i class="bi bi-house-door"></i>
                </div>
                <div>
                    <h5 class="form-section-title">Details & Amenities</h5>
                    <p class="form-section-subtitle">Rooms, area, and features</p>
                </div>
            </div>
            <div class="form-section-body">
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
                    <label class="amenity-chip <?php echo $form['has_kitchen'] ? 'active' : ''; ?>">
                        <input type="checkbox" name="has_kitchen" value="1" <?php echo $form['has_kitchen'] ? 'checked' : ''; ?> style="display:none;">
                        <i class="bi bi-cup-hot"></i> Kitchen
                    </label>
                    <label class="amenity-chip <?php echo $form['has_swimming_pool'] ? 'active' : ''; ?>">
                        <input type="checkbox" name="has_swimming_pool" value="1" <?php echo $form['has_swimming_pool'] ? 'checked' : ''; ?> style="display:none;">
                        <i class="bi bi-water"></i> Swimming Pool
                    </label>
                    <label class="amenity-chip <?php echo $form['has_gym'] ? 'active' : ''; ?>">
                        <input type="checkbox" name="has_gym" value="1" <?php echo $form['has_gym'] ? 'checked' : ''; ?> style="display:none;">
                        <i class="bi bi-bicycle"></i> Gym
                    </label>
                    <label class="amenity-chip <?php echo $form['has_security'] ? 'active' : ''; ?>">
                        <input type="checkbox" name="has_security" value="1" <?php echo $form['has_security'] ? 'checked' : ''; ?> style="display:none;">
                        <i class="bi bi-shield-check"></i> Security
                    </label>
                    <label class="amenity-chip <?php echo $form['has_elevator'] ? 'active' : ''; ?>">
                        <input type="checkbox" name="has_elevator" value="1" <?php echo $form['has_elevator'] ? 'checked' : ''; ?> style="display:none;">
                        <i class="bi bi-elevator"></i> Elevator
                    </label>
                    <label class="amenity-chip <?php echo $form['has_garden'] ? 'active' : ''; ?>">
                        <input type="checkbox" name="has_garden" value="1" <?php echo $form['has_garden'] ? 'checked' : ''; ?> style="display:none;">
                        <i class="bi bi-tree"></i> Garden
                    </label>
                    <label class="amenity-chip <?php echo $form['has_heating'] ? 'active' : ''; ?>">
                        <input type="checkbox" name="has_heating" value="1" <?php echo $form['has_heating'] ? 'checked' : ''; ?> style="display:none;">
                        <i class="bi bi-thermometer-sun"></i> Heating
                    </label>
                    <label class="amenity-chip <?php echo $form['has_cctv'] ? 'active' : ''; ?>">
                        <input type="checkbox" name="has_cctv" value="1" <?php echo $form['has_cctv'] ? 'checked' : ''; ?> style="display:none;">
                        <i class="bi bi-camera-video"></i> CCTV
                    </label>
                </div>
            </div>
        </div>

        <!-- Images -->
        <div class="form-section">
            <div class="form-section-header">
                <div class="form-section-icon" style="background:linear-gradient(135deg,var(--info-500),var(--info-600));">
                    <i class="bi bi-images"></i>
                </div>
                <div>
                    <h5 class="form-section-title">Property Images</h5>
                    <p class="form-section-subtitle">Upload photos of your property</p>
                </div>
            </div>
            <div class="form-section-body">
                <div class="image-upload-area" onclick="document.getElementById('imageInput').click()">
                    <i class="bi bi-cloud-arrow-up" style="font-size:3rem;color:var(--primary-500);"></i>
                    <h5 style="color:var(--slate-900);font-weight:700;margin-top:0.75rem;">Click to upload images</h5>
                    <p style="color:var(--slate-500);font-size:0.85rem;margin:0.25rem 0 0;">You can select multiple images. The first image will be set as the primary image.</p>
                    <input type="file" name="images[]" id="imageInput" accept="image/*" multiple style="display:none;" onchange="previewImages(this)">
                </div>
                <div id="imagePreview" style="display:flex;flex-wrap:wrap;gap:0.75rem;margin-top:1rem;"></div>
            </div>
        </div>

        <!-- Submit -->
        <div class="d-flex gap-2 justify-content-end" style="position:sticky;bottom:1rem;z-index:10;">
            <a href="<?php echo url('/owner-dashboard.php'); ?>" class="btn btn-ghost btn-lg" style="background:#fff;box-shadow:var(--shadow-md);">Cancel</a>
            <button type="submit" class="btn btn-primary btn-lg" style="box-shadow:var(--shadow-primary);"><i class="bi bi-plus-circle"></i> Add Property</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var pricePeriod = document.getElementById('pricePeriod');
    function toggleDailyPrice() {
        var period = pricePeriod.value;
        var mainField = document.getElementById('mainPriceField');
        var dailyField = document.getElementById('dailyPriceField');
        var mainLabel = document.getElementById('mainPriceLabel');
        var priceInput = document.getElementById('priceInput');
        if (period === 'per_month') {
            mainField.style.display = 'block';
            dailyField.style.display = 'none';
            mainLabel.textContent = 'Monthly Price (Rs)';
            priceInput.placeholder = 'e.g. 50000';
        } else if (period === 'per_day') {
            mainField.style.display = 'block';
            dailyField.style.display = 'none';
            mainLabel.textContent = 'Daily Price (Rs)';
            priceInput.placeholder = 'e.g. 2000';
        } else if (period === 'both') {
            mainField.style.display = 'block';
            dailyField.style.display = 'block';
            mainLabel.textContent = 'Monthly Price (Rs)';
            priceInput.placeholder = 'e.g. 50000';
        }
    }
    pricePeriod.addEventListener('change', toggleDailyPrice);
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
}); // end DOMContentLoaded

// Image preview
function previewImages(input) {
    var preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    if (input.files) {
        Array.prototype.forEach.call(input.files, function(file, i) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var div = document.createElement('div');
                div.style.cssText = 'position:relative;width:100px;height:100px;border-radius:10px;overflow:hidden;box-shadow:0 4px 12px -2px rgba(0,0,0,0.15);';
                div.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">' +
                    (i === 0 ? '<span style="position:absolute;top:4px;left:4px;background:var(--primary-600);color:#fff;font-size:0.65rem;font-weight:700;padding:2px 6px;border-radius:4px;">PRIMARY</span>' : '');
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
}

// AI Description Generator
var aiGenCount = 0;
function generateAIDescription() {
    var title = document.querySelector('input[name="title"]').value || '';
    var type = document.querySelector('select[name="property_type"]').value || '';
    var city = document.querySelector('input[name="city"]').value || '';
    var area = document.querySelector('input[name="area"]').value || '';
    var bedrooms = document.querySelector('input[name="bedrooms"]').value || '';
    var bathrooms = document.querySelector('input[name="bathrooms"]').value || '';

    var amenities = [];
    document.querySelectorAll('.amenity-chip input:checked').forEach(function(cb) {
        amenities.push(cb.parentElement.textContent.trim());
    });

    var btn = document.getElementById('aiGenBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-arrow-repeat spin"></i> Generating...';

    fetch('<?php echo url("/api/ai-description.php"); ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            title: title, type: type, city: city, area: area,
            bedrooms: bedrooms, bathrooms: bathrooms, amenities: amenities
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-stars"></i> AI Generate';
        if (data.descriptions && data.descriptions.length > 0) {
            var container = document.getElementById('aiVariations');
            var list = document.getElementById('aiVariationsList');
            list.innerHTML = '';
            data.descriptions.forEach(function(desc, i) {
                var div = document.createElement('div');
                div.style.cssText = 'padding:0.75rem;border:1px solid var(--slate-200);border-radius:var(--radius-sm);cursor:pointer;transition:all 0.2s;background:var(--slate-50);';
                div.innerHTML = '<div style="font-size:0.75rem;color:var(--primary-600);font-weight:600;margin-bottom:0.25rem;">Option ' + (i + 1) + '</div><div style="font-size:0.85rem;color:var(--slate-700);">' + desc + '</div>';
                div.addEventListener('click', function() {
                    document.getElementById('descriptionField').value = desc;
                    container.style.display = 'none';
                });
                div.addEventListener('mouseenter', function() { div.style.borderColor = 'var(--primary-400)'; div.style.background = 'var(--primary-50)'; });
                div.addEventListener('mouseleave', function() { div.style.borderColor = 'var(--slate-200)'; div.style.background = 'var(--slate-50)'; });
                list.appendChild(div);
            });
            container.style.display = '';
        }
    })
    .catch(function(err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-stars"></i> AI Generate';
        alert('Error generating description. Please try again.');
    });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
