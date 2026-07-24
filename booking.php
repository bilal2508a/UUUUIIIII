<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$user = requireLogin();

$propertyId = (int)($_GET['property_id'] ?? 0);
$property = get_property_by_id($propertyId);

if (!$property) {
    flash('error', 'Property not found.');
    redirect('/properties.php');
}

if ($property['owner_id'] == $user['id']) {
    flash('error', 'You cannot book your own property.');
    redirect('/property-details.php?id=' . $propertyId);
}

if (has_user_booked_property($user['id'], $propertyId)) {
    flash('error', 'You have already booked this property.');
    redirect('/property-details.php?id=' . $propertyId);
}

$period = $property['price_period'];
$price = (float)$property['price'];
$pricePerDay = $property['price_per_day'] !== null ? (float)$property['price_per_day'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    $bookingMode = $_POST['booking_mode'] ?? 'month';
    $numMonths = (int)($_POST['num_months'] ?? 1);
    $notes = trim($_POST['notes'] ?? '');

    $effectiveMode = $bookingMode;
    if ($period === 'per_day') $effectiveMode = 'day';
    if ($period === 'per_month') $effectiveMode = 'month';

    if (!$startDate) {
        flash('error', 'Please select a start date.');
    } elseif ($effectiveMode === 'month' && $numMonths < 1) {
        flash('error', 'Please enter at least 1 month.');
    } elseif ($effectiveMode === 'day' && !$endDate) {
        flash('error', 'Please select an end date.');
    } elseif ($effectiveMode === 'day' && $endDate && strtotime($endDate) < strtotime($startDate)) {
        flash('error', 'End date must be after start date.');
    } else {
        if ($effectiveMode === 'month') {
            $numMonths = max(1, $numMonths);
            $totalAmount = $price * $numMonths;
            $endDate = date('Y-m-d', strtotime("+$numMonths months", strtotime($startDate)));
        } else {
            $days = max(1, (int)((strtotime($endDate) - strtotime($startDate)) / 86400));
            $dailyRate = $pricePerDay !== null ? $pricePerDay : $price;
            $totalAmount = $dailyRate * $days;
        }

        // Check for date overlap with existing confirmed bookings
        $overlapStmt = db()->prepare("SELECT COUNT(*) FROM bookings WHERE property_id = ? AND status = 'confirmed' AND payment_status = 'paid' AND start_date < ? AND end_date > ?");
        $overlapStmt->execute([$propertyId, $endDate, $startDate]);
        $overlapCount = (int)$overlapStmt->fetchColumn();
        if ($overlapCount > 0) {
            flash('error', 'This property is already booked for those dates. Please choose different dates.');
            redirect('/property-details.php?id=' . $propertyId);
        }

        $stmt = db()->prepare('INSERT INTO bookings (property_id, tenant_id, start_date, end_date, total_amount, notes) VALUES (?, ?, ?, ?, ?, ?)');
        $result = $stmt->execute([$propertyId, $user['id'], $startDate, $endDate, $totalAmount, $notes]);

        if ($result) {
            $bookingId = (int)db()->lastInsertId();
            redirect('/payment.php?id=' . $bookingId);
        } else {
            flash('error', 'Failed to create booking. Please try again.');
        }
    }
}

if ($period === 'per_day') {
    $priceLabel = format_price($property['price']) . '/day';
} elseif ($period === 'both') {
    $priceLabel = format_price($property['price']) . '/month';
    if ($pricePerDay !== null) {
        $priceLabel .= ' &middot; ' . format_price($pricePerDay) . '/day';
    }
} else {
    $priceLabel = format_price($property['price']) . '/month';
}

$primaryImg = get_primary_image($propertyId);

$checklistItems = [
    ['icon' => 'bi-passport', 'title' => 'Valid ID / Passport', 'desc' => 'Carry identification'],
    ['icon' => 'bi-cash-coin', 'title' => 'Payment Method', 'desc' => 'Cash or card ready'],
    ['icon' => 'bi-geo-alt', 'title' => 'Property Address', 'desc' => 'Saved & directions ready'],
    ['icon' => 'bi-telephone', 'title' => 'Owner Contact', 'desc' => 'Phone number saved'],
    ['icon' => 'bi-bag', 'title' => 'Pack Essentials', 'desc' => 'Clothes & toiletries'],
    ['icon' => 'bi-capsule', 'title' => 'Medications', 'desc' => 'Any prescription meds'],
    ['icon' => 'bi-plug', 'title' => 'Phone Charger', 'desc' => 'Don\'t forget your charger'],
    ['icon' => 'bi-key', 'title' => 'Booking Details', 'desc' => 'Screenshot confirmation'],
    ['icon' => 'bi-map', 'title' => 'Local Map', 'desc' => 'Offline maps downloaded'],
    ['icon' => 'bi-shield-check', 'title' => 'Emergency Contacts', 'desc' => 'Local emergency numbers'],
    ['icon' => 'bi-thermometer-half', 'title' => 'Weather Check', 'desc' => 'Pack for the weather'],
    ['icon' => 'bi-camera', 'title' => 'Arrival Photos', 'desc' => 'Document property condition'],
];

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-app py-4">
    <div class="row">
        <div class="col-lg-12">
            <!-- Header -->
            <div class="text-center mb-4">
                <div style="width:64px;height:64px;border-radius:var(--radius-md);background:linear-gradient(135deg,var(--primary-600),var(--accent-500));display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.75rem;margin:0 auto 1.25rem;box-shadow:0 12px 28px -6px rgba(26,82,245,0.35);">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <h1 style="font-size:1.75rem;font-weight:800;color:var(--slate-900);margin-bottom:0.25rem;letter-spacing:-0.02em;">Book Property</h1>
                <p style="color:var(--slate-500);font-size:0.95rem;margin:0;">Complete your booking request below</p>
            </div>

            <!-- Property Info Card -->
            <div class="card-premium mb-4" style="padding:1.25rem;">
                <div class="d-flex gap-3">
                    <?php if ($primaryImg): ?>
                    <div style="width:120px;height:90px;border-radius:var(--radius-sm);overflow:hidden;flex-shrink:0;">
                        <img src="<?php echo e(image_url($primaryImg)); ?>" alt="<?php echo e($property['title']); ?>" style="width:100%;height:100%;object-fit:cover;">
                    </div>
                    <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center" style="width:120px;height:90px;border-radius:var(--radius-sm);background:var(--slate-100);flex-shrink:0;color:var(--slate-400);font-size:1.5rem;">
                        <i class="bi bi-image"></i>
                    </div>
                    <?php endif; ?>
                    <div class="flex-grow-1">
                        <h3 style="font-size:1.1rem;font-weight:700;color:var(--slate-900);margin-bottom:0.25rem;letter-spacing:-0.01em;"><?php echo e($property['title']); ?></h3>
                        <p style="color:var(--slate-500);font-size:0.85rem;margin-bottom:0.5rem;">
                            <i class="bi bi-geo-alt"></i> <?php echo e($property['address'] . ', ' . $property['city']); ?>
                        </p>
                        <div class="d-flex gap-3" style="font-size:0.8rem;color:var(--slate-600);">
                            <span><i class="bi bi-door-open"></i> <?php echo (int)$property['bedrooms']; ?> Beds</span>
                            <span><i class="bi bi-droplet"></i> <?php echo (int)$property['bathrooms']; ?> Baths</span>
                            <span><i class="bi bi-building"></i> <?php echo e(get_property_type_label($property['property_type'])); ?></span>
                        </div>
                    </div>
                    <div class="text-end" style="flex-shrink:0;">
                        <span class="badge badge-price" style="position:static;font-size:0.95rem;"><?php echo $priceLabel; ?></span>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Full-width Travel Checklist (6 + 6 grid) -->
                <div class="col-lg-12">
                    <div class="card-premium" style="padding:1.5rem;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 style="font-size:1rem;font-weight:700;color:var(--slate-900);margin:0;letter-spacing:-0.01em;">
                                <i class="bi bi-list-check" style="color:var(--accent-600);"></i> Travel Checklist
                            </h3>
                            <span class="badge badge-primary" id="checklistProgress">0/12</span>
                        </div>

                        <div class="progress mb-3" style="max-width:300px;">
                            <div id="checklistBar" style="height:100%;width:0%;"></div>
                        </div>

                        <div class="row g-2">
                            <?php foreach ($checklistItems as $idx => $item): ?>
                            <div class="col-md-6 col-lg-4">
                                <label class="checklist-item d-flex align-items-start gap-2 p-2" style="border-radius:var(--radius-sm);border:1px solid var(--slate-200);transition:var(--transition);cursor:pointer;height:100%;">
                                    <input type="checkbox" class="checklist-checkbox" onchange="updateChecklist()" style="width:18px;height:18px;accent-color:var(--primary-600);flex-shrink:0;margin-top:2px;">
                                    <i class="bi <?php echo e($item['icon']); ?>" style="color:var(--primary-500);font-size:1.1rem;flex-shrink:0;margin-top:1px;"></i>
                                    <div style="flex-grow:1;min-width:0;">
                                        <div style="font-weight:600;color:var(--slate-900);font-size:0.85rem;" class="checklist-title"><?php echo e($item['title']); ?></div>
                                        <div style="color:var(--slate-400);font-size:0.75rem;" class="checklist-desc"><?php echo e($item['desc']); ?></div>
                                    </div>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Full-width Booking Form + Summary + Confirm -->
                <div class="col-lg-12">
                    <div class="card-premium" style="padding:2rem;">
                        <h3 style="font-size:1.15rem;font-weight:700;color:var(--slate-900);margin-bottom:1.25rem;letter-spacing:-0.01em;">
                            <i class="bi bi-calendar2-plus" style="color:var(--primary-600);"></i> Booking Details
                        </h3>
                        <form method="POST" action="<?php echo url('/booking.php?property_id=' . $propertyId); ?>">
                            <?php if ($period === 'both'): ?>
                            <div class="mb-4">
                                <label class="form-label-mh">Booking Type <span style="color:var(--error-500);">*</span></label>
                                <div class="d-flex gap-2">
                                    <button type="button" class="booking-mode-btn active" data-mode="month" onclick="selectBookingMode('month')">
                                        <i class="bi bi-calendar-month"></i> Monthly
                                    </button>
                                    <button type="button" class="booking-mode-btn" data-mode="day" onclick="selectBookingMode('day')">
                                        <i class="bi bi-calendar-day"></i> Daily
                                    </button>
                                </div>
                                <input type="hidden" name="booking_mode" id="booking_mode" value="month">
                            </div>
                            <?php elseif ($period === 'per_day'): ?>
                            <input type="hidden" name="booking_mode" value="day">
                            <?php else: ?>
                            <input type="hidden" name="booking_mode" value="month">
                            <?php endif; ?>

                            <div id="monthlyFields">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label-mh">Start Date <span style="color:var(--error-500);">*</span></label>
                                        <input type="date" id="start_date" name="start_date" required onchange="updateTotal()" class="form-control-mh">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-mh">Number of Months <span style="color:var(--error-500);">*</span></label>
                                        <input type="number" id="num_months" name="num_months" min="1" value="1" required onchange="updateTotal()" class="form-control-mh">
                                    </div>
                                </div>
                            </div>

                            <div id="dailyFields" style="display:none;">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label-mh">Check-In Date <span style="color:var(--error-500);">*</span></label>
                                        <input type="date" id="start_date_day" name="start_date_day" onchange="syncStartDate()" class="form-control-mh">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label-mh">Check-Out Date <span style="color:var(--error-500);">*</span></label>
                                        <input type="date" id="end_date" name="end_date" onchange="updateTotal()" class="form-control-mh">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="form-label-mh">Notes <span style="color:var(--slate-400);font-weight:400;">(optional)</span></label>
                                <textarea id="notes" name="notes" rows="3" placeholder="Any special requests..." class="form-control-mh"></textarea>
                            </div>

                            <!-- Booking Summary -->
                            <div class="mt-4 p-4" style="background:linear-gradient(135deg,var(--primary-50),var(--accent-50));border-radius:var(--radius);border:1px solid var(--primary-200);">
                                <h3 style="font-size:1rem;font-weight:700;color:var(--slate-900);margin-bottom:1rem;letter-spacing:-0.01em;">
                                    <i class="bi bi-receipt" style="color:var(--primary-600);"></i> Booking Summary
                                </h3>
                                <div class="d-flex justify-content-between align-items-center mb-2" id="rateRow">
                                    <span style="color:var(--slate-500);font-size:0.875rem;" id="rateLabel">Monthly Rent</span>
                                    <span style="font-weight:700;color:var(--slate-900);font-size:0.95rem;" id="rateValue"><?php echo format_price($price); ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2" id="durationRow" style="display:none;">
                                    <span style="color:var(--slate-500);font-size:0.875rem;">Duration</span>
                                    <span style="font-weight:600;color:var(--slate-900);font-size:0.9rem;" id="durationText">1 month</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2" id="totalRow" style="display:none;border-top:1px solid var(--primary-200);">
                                    <span style="font-weight:700;color:var(--slate-900);font-size:1rem;">Total Amount</span>
                                    <span style="font-weight:800;color:var(--primary-600);font-size:1.25rem;" id="totalText"><?php echo format_price(0); ?></span>
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-4">
                                <a href="<?php echo url('/property-details.php?id=' . $propertyId); ?>" class="btn btn-ghost flex-grow-1">
                                    <i class="bi bi-arrow-left"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary flex-grow-1">
                                    <i class="bi bi-check-lg"></i> Proceed to Payment
                                </button>
                            </div>

                            <div style="margin-top:1.25rem;padding:1rem;background:var(--primary-50);border:1px solid var(--primary-200);border-radius:12px;">
                                <div style="font-weight:700;color:var(--primary-700);font-size:0.9rem;margin-bottom:0.5rem;">
                                    <i class="bi bi-shield-check"></i> Cancellation Policy
                                </div>
                                <ul style="margin:0;padding-left:1.25rem;color:var(--slate-600);font-size:0.85rem;line-height:1.6;">
                                    <li><strong>Full refund</strong> if you cancel up to 1 day before check-in.</li>
                                    <li><strong>50% refund</strong> if you cancel on check-in day or later.</li>
                                    <li>Payment is collected at booking time.</li>
                                </ul>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var price = <?php echo $price; ?>;
var pricePerDay = <?php echo $pricePerDay !== null ? $pricePerDay : 'null'; ?>;
var period = '<?php echo $period; ?>';

function selectBookingMode(mode) {
    document.getElementById('booking_mode').value = mode;
    document.querySelectorAll('.booking-mode-btn').forEach(function(btn) {
        if (btn.dataset.mode === mode) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    toggleFields(mode);
    updateTotal();
}

function toggleFields(mode) {
    var monthlyFields = document.getElementById('monthlyFields');
    var dailyFields = document.getElementById('dailyFields');
    if (mode === 'day') {
        monthlyFields.style.display = 'none';
        dailyFields.style.display = 'block';
    } else {
        monthlyFields.style.display = 'block';
        dailyFields.style.display = 'none';
    }
}

function syncStartDate() {
    var startDayInput = document.getElementById('start_date_day');
    var startInput = document.getElementById('start_date');
    if (startDayInput.value) startInput.value = startDayInput.value;
    updateTotal();
}

function updateTotal() {
    var modeEl = document.getElementById('booking_mode');
    var mode = modeEl ? modeEl.value : 'month';
    var start = document.getElementById('start_date').value;
    var durationRow = document.getElementById('durationRow');
    var totalRow = document.getElementById('totalRow');
    var durationText = document.getElementById('durationText');
    var totalText = document.getElementById('totalText');
    var rateLabel = document.getElementById('rateLabel');
    var rateValue = document.getElementById('rateValue');

    var effectiveMode = mode;
    if (period === 'per_day') effectiveMode = 'day';
    if (period === 'per_month') effectiveMode = 'month';

    if (effectiveMode === 'day') {
        var daily = pricePerDay !== null ? pricePerDay : price;
        rateLabel.textContent = 'Daily Rate';
        rateValue.textContent = 'Rs ' + daily.toLocaleString();
    } else {
        rateLabel.textContent = 'Monthly Rent';
        rateValue.textContent = 'Rs ' + price.toLocaleString();
    }

    if (effectiveMode === 'month' && start) {
        var numMonths = Math.max(1, parseInt(document.getElementById('num_months').value) || 1);
        var total = price * numMonths;
        durationRow.style.display = 'flex';
        totalRow.style.display = 'flex';
        durationText.textContent = numMonths + ' month' + (numMonths > 1 ? 's' : '');
        totalText.textContent = 'Rs ' + total.toLocaleString();
    } else if (effectiveMode === 'day' && start) {
        var end = document.getElementById('end_date').value;
        if (end) {
            var days = Math.max(1, Math.round((new Date(end) - new Date(start)) / 86400000));
            var daily = pricePerDay !== null ? pricePerDay : price;
            var total = daily * days;
            durationRow.style.display = 'flex';
            totalRow.style.display = 'flex';
            durationText.textContent = days + ' day' + (days > 1 ? 's' : '');
            totalText.textContent = 'Rs ' + total.toLocaleString();
        } else {
            durationRow.style.display = 'none';
            totalRow.style.display = 'none';
        }
    } else {
        durationRow.style.display = 'none';
        totalRow.style.display = 'none';
    }
}

(function() {
    if (period === 'per_day') {
        toggleFields('day');
        var modeInput = document.getElementById('booking_mode');
        if (modeInput) modeInput.value = 'day';
    } else if (period === 'per_month') {
        toggleFields('month');
        var modeInput = document.getElementById('booking_mode');
        if (modeInput) modeInput.value = 'month';
    }
})();

function updateChecklist() {
    var checkboxes = document.querySelectorAll('.checklist-checkbox');
    var checked = 0;
    checkboxes.forEach(function(cb) {
        if (cb.checked) checked++;
        var item = cb.closest('.checklist-item');
        if (cb.checked) {
            item.classList.add('checked');
        } else {
            item.classList.remove('checked');
        }
    });
    var total = checkboxes.length;
    var pct = (checked / total) * 100;
    document.getElementById('checklistProgress').textContent = checked + '/' + total;
    document.getElementById('checklistBar').style.width = pct + '%';
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
