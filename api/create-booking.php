<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/properties.php');
}

$propertyId = (int)($_POST['property_id'] ?? 0);
$checkIn = $_POST['check_in'] ?? '';
$checkOut = $_POST['check_out'] ?? '';
$guests = (int)($_POST['guests'] ?? 1);
$totalPrice = (float)($_POST['total_price'] ?? 0);
$guestName = $_POST['guest_name'] ?? '';
$guestEmail = $_POST['guest_email'] ?? '';
$guestPhone = $_POST['guest_phone'] ?? '';
$specialRequests = $_POST['special_requests'] ?? '';

if ($propertyId <= 0 || empty($checkIn) || empty($checkOut) || empty($guestName) || empty($guestEmail)) {
    redirect('/properties.php');
}

$stmt = db()->prepare('SELECT * FROM properties WHERE id = ?');
$stmt->execute([$propertyId]);
$property = $stmt->fetch();

if (!$property) {
    redirect('/properties.php');
}

if ($totalPrice <= 0) {
    $nights = nightsBetween($checkIn, $checkOut);
    $subtotal = $property['price_per_night'] * $nights;
    $totalPrice = $subtotal + ($subtotal * 0.05);
}

$stmt = db()->prepare('INSERT INTO bookings (property_id, user_id, check_in, check_out, guests, total_price, status, owner_status, payment_status, guest_name, guest_email, guest_phone, special_requests) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->execute([
    $propertyId,
    $_SESSION['user_id'],
    $checkIn,
    $checkOut,
    $guests,
    $totalPrice,
    'pending',
    'pending',
    'unpaid',
    $guestName,
    $guestEmail,
    $guestPhone,
    $specialRequests,
]);

$bookingId = (int)db()->lastInsertId();
redirect('/payment.php?id=' . $bookingId);
