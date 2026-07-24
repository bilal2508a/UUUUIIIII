<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Require login
$user = currentUser();
if (!$user) {
    flash('error', 'Please log in to add a review.');
    redirect('/login.php');
}

// POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/index.php');
}

// Only tenants can add reviews
if ($user['role'] !== 'tenant') {
    flash('error', 'Only tenants can add reviews.');
    redirect('/index.php');
}

$propertyId = (int)($_POST['property_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 5);
$comment = trim($_POST['comment'] ?? '');

// Validate
if (!$propertyId || !$comment) {
    flash('error', 'Please provide a rating and comment.');
    redirect('/property-details.php?id=' . $propertyId);
}

if ($rating < 1 || $rating > 5) {
    $rating = 5;
}

// Verify property exists
$property = get_property_by_id($propertyId);
if (!$property) {
    flash('error', 'Property not found.');
    redirect('/properties.php');
}

// Insert review using PDO
$stmt = db()->prepare('INSERT INTO reviews (property_id, user_id, rating, comment) VALUES (?, ?, ?, ?)');
$result = $stmt->execute([$propertyId, $user['id'], $rating, $comment]);

if ($result) {
    flash('success', 'Review added successfully!');
} else {
    flash('error', 'Failed to add review. Please try again.');
}

redirect('/property-details.php?id=' . $propertyId);
