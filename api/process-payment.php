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

// Process payment - update booking to paid and confirmed
$stmt = db()->prepare("UPDATE bookings SET payment_status = 'paid', status = 'confirmed' WHERE id = ?");
$stmt->execute([$bookingId]);

// Update property status to rented
$stmt = db()->prepare("UPDATE properties SET status = 'rented' WHERE id = ?");
$stmt->execute([$booking['property_id']]);

flash('success', 'Payment successful! Your booking is confirmed.');
redirect('/bookings.php');
