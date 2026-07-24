<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

$user = requireRole('owner');

$id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

if (!in_array($action, ['approve', 'reject'])) {
    flash('error', 'Invalid action.');
    redirect('/owner-dashboard.php');
}

// Fetch booking with property owner verification
$stmt = db()->prepare('SELECT b.*, p.owner_id, p.title as property_title FROM bookings b JOIN properties p ON b.property_id = p.id WHERE b.id = ?');
$stmt->execute([$id]);
$booking = $stmt->fetch();

if (!$booking) {
    flash('error', 'Booking not found.');
    redirect('/owner-dashboard.php');
}

// Verify booking belongs to owner's property
if ($booking['owner_id'] != $user['id'] && $user['role'] !== 'admin') {
    flash('error', 'Access denied. This booking is not for your property.');
    redirect('/owner-dashboard.php');
}

// Only pending bookings can be approved/rejected
if ($booking['status'] !== 'pending') {
    flash('error', 'This booking is no longer pending.');
    redirect('/owner-dashboard.php');
}

$statusMap = [
    'approve' => 'confirmed',
    'reject' => 'cancelled',
];

$newStatus = $statusMap[$action];

// Update booking status
$stmt = db()->prepare('UPDATE bookings SET status = ? WHERE id = ?');
$stmt->execute([$newStatus, $id]);

// Update property status
if ($action === 'approve') {
    $stmt = db()->prepare("UPDATE properties SET status = 'rented' WHERE id = ?");
    $stmt->execute([$booking['property_id']]);
    flash('success', 'Booking approved successfully. The property is now marked as rented.');
} else {
    // On reject, make property available again if it was rented
    $stmt = db()->prepare("UPDATE properties SET status = 'available' WHERE id = ?");
    $stmt->execute([$booking['property_id']]);
    flash('success', 'Booking rejected. The property is available again.');
}

redirect('/owner-dashboard.php');
