<?php
// Mehmaan Hub - Edit Property
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$user = requireLogin();
$propertyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$propertyId) {
    flash('error', 'Invalid property.');
    redirect('owner-dashboard.php');
}

$property = get_property_by_id($propertyId);
if (!$property) {
    flash('error', 'Property not found.');
    redirect('owner-dashboard.php');
}

// Verify ownership (or admin)
if ($property['owner_id'] != $user['id'] && $user['role'] !== 'admin') {
    flash('error', 'You do not have permission to edit this property.');
    redirect('owner-dashboard.php');
}

$cities = getCities();
$images = get_property_images($propertyId);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $property_type = $_POST['property_type'] ?? 'apartment';
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $area = trim($_POST['area'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $price_period = $_POST['price_period'] ?? 'per_month';
    $price_per_day = trim($_POST['price_per_day'] ?? '');
    $bedrooms = (int)($_POST['bedrooms'] ?? 1);
    $bathrooms = (int)($_POST['bathrooms'] ?? 1);
    $area_sqft = trim($_POST['area_sqft'] ?? '');
    $is_furnished = isset($_POST['is_furnished']) ? 1 : 0;
    $has_parking = isset($_POST['has_parking']) ? 1 : 0;
    $has_wifi = isset($_POST['has_wifi']) ? 1 : 0;
    $has_ac = isset($_POST['has_ac']) ? 1 : 0;
    $has_generator = isset($_POST['has_generator']) ? 1 : 0;
    $has_kitchen = isset($_POST['has_kitchen']) ? 1 : 0;
    $has_swimming_pool = isset($_POST['has_swimming_pool']) ? 1 : 0;
    $has_gym = isset($_POST['has_gym']) ? 1 : 0;
    $has_security = isset($_POST['has_security']) ? 1 : 0;
    $has_elevator = isset($_POST['has_elevator']) ? 1 : 0;
    $has_garden = isset($_POST['has_garden']) ? 1 : 0;
    $has_heating = isset($_POST['has_heating']) ? 1 : 0;
    $has_cctv = isset($_POST['has_cctv']) ? 1 : 0;
    $status = $_POST['status'] ?? 'available';

    // Validation
    if ($title === '') $errors[] = 'Title is required.';
    if ($description === '') $errors[] = 'Description is required.';
    if ($address === '') $errors[] = 'Address is required.';
    if ($city === '') $errors[] = 'City is required.';
    if ($price === '' || !is_numeric($price)) $errors[] = 'Valid price is required.';
    if ($price_period === 'both' && ($price_per_day === '' || !is_numeric($price_per_day))) {
        $errors[] = 'Daily price is required when pricing is set to both.';
    }

    $validTypes = ['apartment', 'house', 'room', 'studio', 'villa'];
    if (!in_array($property_type, $validTypes)) {
        $errors[] = 'Invalid property type.';
    }

    $validStatuses = ['available', 'rented', 'inactive'];
    if (!in_array($status, $validStatuses)) {
        $errors[] = 'Invalid status.';
    }

    if (empty($errors)) {
        try {
            $stmt = db()->prepare('UPDATE properties SET title = ?, description = ?, property_type = ?, address = ?, city = ?, area = ?, price = ?, price_period = ?, price_per_day = ?, bedrooms = ?, bathrooms = ?, area_sqft = ?, is_furnished = ?, has_parking = ?, has_wifi = ?, has_ac = ?, has_generator = ?, has_kitchen = ?, has_swimming_pool = ?, has_gym = ?, has_security = ?, has_elevator = ?, has_garden = ?, has_heating = ?, has_cctv = ?, status = ? WHERE id = ?');
            $stmt->execute([
                $title, $description, $property_type, $address, $city, $area,
                $price, $price_period,
                ($price_period === 'both' ? $price_per_day : null),
                $bedrooms, $bathrooms,
                $area_sqft !== '' ? $area_sqft : null,
                $is_furnished, $has_parking, $has_wifi, $has_ac, $has_generator,
                $has_kitchen, $has_swimming_pool, $has_gym, $has_security,
                $has_elevator, $has_garden, $has_heating, $has_cctv,
                $status, $propertyId,
            ]);

            // Handle new image uploads
            if (!empty($_FILES['images']) && isset($_FILES['images']['name'])) {
                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0775, true);
                }

                $files = $_FILES['images'];
                $fileCount = count($files['name']);
                // Determine next sort_order
                $maxOrder = 0;
                foreach ($images as $img) {
                    if ($img['sort_order'] > $maxOrder) $maxOrder = $img['sort_order'];
                }

                $hasPrimary = false;
                foreach ($images as $img) {
                    if ($img['is_primary']) { $hasPrimary = true; break; }
                }

                for ($i = 0; $i < $fileCount; $i++) {
                    if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
                    if (!is_uploaded_file($files['tmp_name'][$i])) continue;

                    $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (!in_array($ext, $allowed)) continue;

                    $filename = 'prop_' . $propertyId . '_' . uniqid() . '.' . $ext;
                    $targetPath = UPLOAD_DIR . $filename;

                    if (move_uploaded_file($files['tmp_name'][$i], $targetPath)) {
                        $maxOrder++;
                        $isPrimary = (!$hasPrimary && $i === 0) ? 1 : 0;
                        if ($isPrimary) $hasPrimary = true;
                        $imgStmt = db()->prepare('INSERT INTO property_images (property_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)');
                        $imgStmt->execute([$propertyId, $filename, $isPrimary, $maxOrder]);
                    }
                }
            }

            flash('success', 'Property updated successfully!');
            redirect('edit-property.php?id=' . $propertyId);
        } catch (PDOException $ex) {
            $errors[] = 'Error updating property: ' . $ex->getMessage();
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
                    <h1 style="font-size:1.75rem;font-weight:800;color:#0f172a;margin:0;">Edit Property</h1>
                    <p style="color:#64748b;margin:0;"><?php echo e($property['title']); ?></p>
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

            <!-- Current Images -->
            <div class="card mb-4">
                <div class="card-header-mh">
                    <h5 class="mb-0"><i class="bi bi-images" style="color:#0ea5e9;"></i> Current Images</h5>
                </div>
                <div class="card-body p-4">
                    <?php if (empty($images)): ?>
                        <p style="color:#64748b;">No images uploaded yet.</p>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($images as $img): ?>
                                <div class="col-6 col-md-3">
                                    <div class="position-relative">
                                        <img src="<?php echo e(image_url($img['image_path'])); ?>" alt="Property image" style="width:100%;height:140px;object-fit:cover;border-radius:12px;">
                                        <?php if ($img['is_primary']): ?>
                                            <span class="badge badge-featured" style="position:absolute;top:8px;left:8px;">Primary</span>
                                        <?php endif; ?>
                                        <a href="<?php echo url('/api/delete-image.php?id=' . (int)$img['id'] . '&property_id=' . $propertyId); ?>"
                                           class="btn btn-error btn-sm"
                                           style="position:absolute;top:8px;right:8px;padding:4px 8px;"
                                           onclick="return confirm('Delete this image?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <form method="POST" action="<?php echo url('/edit-property.php?id=' . $propertyId); ?>" enctype="multipart/form-data">
                <!-- Basic Information -->
                <div class="card mb-4">
                    <div class="card-header-mh">
                        <h5 class="mb-0"><i class="bi bi-info-circle" style="color:#0ea5e9;"></i> Basic Information</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label-mh">Title <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="title" class="form-control-mh" value="<?php echo e($property['title']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-mh">Description <span style="color:#ef4444;">*</span></label>
                            <textarea name="description" class="form-control-mh" rows="4" required><?php echo e($property['description']); ?></textarea>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-mh">Property Type <span style="color:#ef4444;">*</span></label>
                                <select name="property_type" class="form-control-mh">
                                    <option value="apartment" <?php echo $property['property_type'] === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                                    <option value="house" <?php echo $property['property_type'] === 'house' ? 'selected' : ''; ?>>House</option>
                                    <option value="room" <?php echo $property['property_type'] === 'room' ? 'selected' : ''; ?>>Room</option>
                                    <option value="studio" <?php echo $property['property_type'] === 'studio' ? 'selected' : ''; ?>>Studio</option>
                                    <option value="villa" <?php echo $property['property_type'] === 'villa' ? 'selected' : ''; ?>>Villa</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-mh">Area / Neighborhood</label>
                                <input type="text" name="area" class="form-control-mh" value="<?php echo e($property['area']); ?>">
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
                            <input type="text" name="address" class="form-control-mh" value="<?php echo e($property['address']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-mh">City <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="city" class="form-control-mh" value="<?php echo e($property['city']); ?>" list="cityList" required>
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
                            <select name="price_period" class="form-control-mh" id="pricePeriod">
                                <option value="per_month" <?php echo $property['price_period'] === 'per_month' ? 'selected' : ''; ?>>Per Month</option>
                                <option value="per_day" <?php echo $property['price_period'] === 'per_day' ? 'selected' : ''; ?>>Per Day</option>
                                <option value="both" <?php echo $property['price_period'] === 'both' ? 'selected' : ''; ?>>Both (Monthly & Daily)</option>
                            </select>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6" id="mainPriceField">
                                <label class="form-label-mh">
                                    <span id="mainPriceLabel">Monthly Price (Rs)</span> <span style="color:#ef4444;">*</span>
                                </label>
                                <input type="number" name="price" class="form-control-mh" id="priceInput" value="<?php echo e($property['price']); ?>" min="0" step="100">
                            </div>
                            <div class="col-md-6" id="dailyPriceField" style="display:none;">
                                <label class="form-label-mh">Daily Price (Rs) <span style="color:#ef4444;">*</span></label>
                                <input type="number" name="price_per_day" class="form-control-mh" id="dailyPriceInput" value="<?php echo e($property['price_per_day']); ?>" min="0" step="100">
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
                                <input type="number" name="bedrooms" class="form-control-mh" value="<?php echo (int)$property['bedrooms']; ?>" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-mh">Bathrooms</label>
                                <input type="number" name="bathrooms" class="form-control-mh" value="<?php echo (int)$property['bathrooms']; ?>" min="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-mh">Area (sqft)</label>
                                <input type="number" name="area_sqft" class="form-control-mh" value="<?php echo e($property['area_sqft']); ?>" min="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-mh">Status</label>
                            <select name="status" class="form-control-mh">
                                <option value="available" <?php echo $property['status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                                <option value="rented" <?php echo $property['status'] === 'rented' ? 'selected' : ''; ?>>Rented</option>
                                <option value="inactive" <?php echo $property['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <label class="form-label-mh">Amenities</label>
                        <div class="d-flex flex-wrap gap-2">
                            <label class="amenity-chip <?php echo $property['is_furnished'] ? 'active' : ''; ?>">
                                <input type="checkbox" name="is_furnished" value="1" <?php echo $property['is_furnished'] ? 'checked' : ''; ?> style="display:none;">
                                <i class="bi bi-house-check"></i> Furnished
                            </label>
                            <label class="amenity-chip <?php echo $property['has_parking'] ? 'active' : ''; ?>">
                                <input type="checkbox" name="has_parking" value="1" <?php echo $property['has_parking'] ? 'checked' : ''; ?> style="display:none;">
                                <i class="bi bi-car-front"></i> Parking
                            </label>
                            <label class="amenity-chip <?php echo $property['has_wifi'] ? 'active' : ''; ?>">
                                <input type="checkbox" name="has_wifi" value="1" <?php echo $property['has_wifi'] ? 'checked' : ''; ?> style="display:none;">
                                <i class="bi bi-wifi"></i> WiFi
                            </label>
                            <label class="amenity-chip <?php echo $property['has_ac'] ? 'active' : ''; ?>">
                                <input type="checkbox" name="has_ac" value="1" <?php echo $property['has_ac'] ? 'checked' : ''; ?> style="display:none;">
                                <i class="bi bi-snow"></i> AC
                            </label>
                            <label class="amenity-chip <?php echo $property['has_generator'] ? 'active' : ''; ?>">
                                <input type="checkbox" name="has_generator" value="1" <?php echo $property['has_generator'] ? 'checked' : ''; ?> style="display:none;">
                                <i class="bi bi-lightning-charge"></i> Generator
                            </label>
                            <label class="amenity-chip <?php echo $property['has_kitchen'] ? 'active' : ''; ?>">
                                <input type="checkbox" name="has_kitchen" value="1" <?php echo $property['has_kitchen'] ? 'checked' : ''; ?> style="display:none;">
                                <i class="bi bi-cup-hot"></i> Kitchen
                            </label>
                            <label class="amenity-chip <?php echo $property['has_swimming_pool'] ? 'active' : ''; ?>">
                                <input type="checkbox" name="has_swimming_pool" value="1" <?php echo $property['has_swimming_pool'] ? 'checked' : ''; ?> style="display:none;">
                                <i class="bi bi-water"></i> Swimming Pool
                            </label>
                            <label class="amenity-chip <?php echo $property['has_gym'] ? 'active' : ''; ?>">
                                <input type="checkbox" name="has_gym" value="1" <?php echo $property['has_gym'] ? 'checked' : ''; ?> style="display:none;">
                                <i class="bi bi-bicycle"></i> Gym
                            </label>
                            <label class="amenity-chip <?php echo $property['has_security'] ? 'active' : ''; ?>">
                                <input type="checkbox" name="has_security" value="1" <?php echo $property['has_security'] ? 'checked' : ''; ?> style="display:none;">
                                <i class="bi bi-shield-check"></i> Security
                            </label>
                            <label class="amenity-chip <?php echo $property['has_elevator'] ? 'active' : ''; ?>">
                                <input type="checkbox" name="has_elevator" value="1" <?php echo $property['has_elevator'] ? 'checked' : ''; ?> style="display:none;">
                                <i class="bi bi-elevator"></i> Elevator
                            </label>
                            <label class="amenity-chip <?php echo $property['has_garden'] ? 'active' : ''; ?>">
                                <input type="checkbox" name="has_garden" value="1" <?php echo $property['has_garden'] ? 'checked' : ''; ?> style="display:none;">
                                <i class="bi bi-tree"></i> Garden
                            </label>
                            <label class="amenity-chip <?php echo $property['has_heating'] ? 'active' : ''; ?>">
                                <input type="checkbox" name="has_heating" value="1" <?php echo $property['has_heating'] ? 'checked' : ''; ?> style="display:none;">
                                <i class="bi bi-thermometer-sun"></i> Heating
                            </label>
                            <label class="amenity-chip <?php echo $property['has_cctv'] ? 'active' : ''; ?>">
                                <input type="checkbox" name="has_cctv" value="1" <?php echo $property['has_cctv'] ? 'checked' : ''; ?> style="display:none;">
                                <i class="bi bi-camera-video"></i> CCTV
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Add More Images -->
                <div class="card mb-4">
                    <div class="card-header-mh">
                        <h5 class="mb-0"><i class="bi bi-cloud-upload" style="color:#0ea5e9;"></i> Add More Images</h5>
                    </div>
                    <div class="card-body p-4">
                        <label class="form-label-mh">Upload New Images</label>
                        <input type="file" name="images[]" class="form-control-mh" accept="image/*" multiple>
                        <small style="color:#64748b;">Select multiple images to add to this property.</small>
                    </div>
                </div>

                <!-- Submit -->
                <div class="d-flex gap-2 justify-content-end">
                    <a href="<?php echo url('/owner-dashboard.php'); ?>" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var pricePeriod = document.getElementById('pricePeriod');
    function toggleDailyPrice() {
        var period = pricePeriod.value;
        var mainField = document.getElementById('mainPriceField');
        var dailyField = document.getElementById('dailyPriceField');
        var mainLabel = document.getElementById('mainPriceLabel');
        if (period === 'per_month') {
            mainField.style.display = 'block';
            dailyField.style.display = 'none';
            mainLabel.textContent = 'Monthly Price (Rs)';
        } else if (period === 'per_day') {
            mainField.style.display = 'block';
            dailyField.style.display = 'none';
            mainLabel.textContent = 'Daily Price (Rs)';
        } else if (period === 'both') {
            mainField.style.display = 'block';
            dailyField.style.display = 'block';
            mainLabel.textContent = 'Monthly Price (Rs)';
        }
    }
    pricePeriod.addEventListener('change', toggleDailyPrice);
    toggleDailyPrice();

    document.querySelectorAll('.amenity-chip input[type="checkbox"]').forEach(function(cb) {
        cb.addEventListener('change', function() {
            if (this.checked) {
                this.parentElement.classList.add('active');
            } else {
                this.parentElement.classList.remove('active');
            }
        });
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
