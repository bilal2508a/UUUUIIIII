<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

$user = requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('error', 'Invalid request.');
    redirect('/bookings.php');
}

$bookingId = (int)($_POST['booking_id'] ?? 0);
if (!$bookingId) {
    flash('error', 'Invalid booking.');
    redirect('/bookings.php');
}

$stmt = db()->prepare('SELECT * FROM bookings WHERE id = ? AND tenant_id = ?');
$stmt->execute([$bookingId, $user['id']]);
$booking = $stmt->fetch();

if (!$booking) {
    flash('error', 'Booking not found.');
    redirect('/bookings.php');
}

if ($booking['status'] !== 'confirmed' || $booking['payment_status'] !== 'paid') {
    flash('error', 'Only confirmed, paid bookings can be cancelled.');
    redirect('/bookings.php');
}

$today = date('Y-m-d');
$checkIn = $booking['start_date'];
$totalPaid = (float)$booking['total_amount'];

if ($today < $checkIn) {
    $refundAmount = $totalPaid;
    $paymentStatus = 'refunded';
    $message = 'Booking cancelled. Full refund of ' . format_price($refundAmount) . ' will be processed.';
} elseif ($today === $checkIn) {
    $refundAmount = round($totalPaid * 0.50, 2);
    $paymentStatus = 'partial_refund';
    $message = 'Booking cancelled on check-in day. 50% refund of ' . format_price($refundAmount) . ' will be processed.';
} else {
    $refundAmount = 0;
    $paymentStatus = 'paid';
    $message = 'Booking cancelled. No refund is available after check-in day.';
}

$stmt = db()->prepare("UPDATE bookings SET status = 'cancelled', payment_status = ?, refund_amount = ?, cancelled_at = NOW() WHERE id = ?");
$stmt->execute([$paymentStatus, $refundAmount, $bookingId]);

flash($refundAmount > 0 ? 'success' : 'info', $message);
redirect('/bookings.php');
