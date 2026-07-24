<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

$user = requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('error', 'Invalid request method.');
    redirect('/bookings.php');
}

$bookingId = (int)($_POST['booking_id'] ?? 0);
$paymentMethod = $_POST['payment_method'] ?? '';
$totalPrice = (float)($_POST['total_price'] ?? 0);
$couponCode = $_POST['coupon_code'] ?? '';

if (!$bookingId || !$paymentMethod || !$totalPrice) {
    flash('error', 'Missing payment information.');
    redirect('/bookings.php');
}

if (!in_array($paymentMethod, ['card', 'wallet', 'bank'])) {
    flash('error', 'Invalid payment method.');
    redirect('/bookings.php');
}

// Fetch booking, verify ownership
$stmt = db()->prepare('SELECT * FROM bookings WHERE id = ? AND tenant_id = ?');
$stmt->execute([$bookingId, $user['id']]);
$booking = $stmt->fetch();

if (!$booking) {
    flash('error', 'Booking not found.');
    redirect('/bookings.php');
}

if ($booking['payment_status'] === 'paid') {
    flash('error', 'This booking is already paid.');
    redirect('/bookings.php');
}

// Validate coupon if provided
$coupons = [
    'EARLY20' => 20,
    'STAY7' => 15,
    'FAMILY4' => 10,
    'WELCOME10' => 10,
];
$expectedTotal = (float)$booking['total_amount'];
if ($couponCode && isset($coupons[$couponCode])) {
    $discount = $coupons[$couponCode];
    $expectedTotal = $expectedTotal - ($expectedTotal * $discount / 100);
}

// Verify total price matches expected (allow small float variance)
if (abs($totalPrice - $expectedTotal) > 1) {
    flash('error', 'Payment amount mismatch. Please try again.');
    redirect('/payment.php?id=' . $bookingId);
}

// Re-check for date overlap (in case another booking was confirmed meanwhile)
$overlapStmt = db()->prepare("SELECT COUNT(*) FROM bookings WHERE property_id = ? AND id != ? AND status = 'confirmed' AND payment_status = 'paid' AND start_date < ? AND end_date > ?");
$overlapStmt->execute([$booking['property_id'], $bookingId, $booking['end_date'], $booking['start_date']]);
if ((int)$overlapStmt->fetchColumn() > 0) {
    flash('error', 'Sorry, this property was just booked for those dates. Please try different dates.');
    redirect('/property-details.php?id=' . (int)$booking['property_id']);
}

// Calculate commission (10%) and owner payout
$commissionRate = 10.00;
$commissionAmount = round($totalPrice * $commissionRate / 100, 2);
$ownerPayout = round($totalPrice - $commissionAmount, 2);

// Process payment - auto-confirm booking, set commission/payout
$stmt = db()->prepare("UPDATE bookings SET payment_status = 'paid', status = 'confirmed', commission_rate = ?, commission_amount = ?, owner_payout = ? WHERE id = ?");
$stmt->execute([$commissionRate, $commissionAmount, $ownerPayout, $bookingId]);

flash('success', 'Payment successful! Your booking is confirmed. You can cancel for a full refund up to 1 day before check-in.');
redirect('/bookings.php');
