<?php
// Delete a property (owner or admin only)
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$propertyId = (int)($_GET['id'] ?? 0);
$user = currentUser();

if ($propertyId <= 0) {
    redirect('/owner-dashboard.php');
}

// Verify ownership (or admin)
$stmt = db()->prepare('SELECT owner_id FROM properties WHERE id = ?');
$stmt->execute([$propertyId]);
$property = $stmt->fetch();

if (!$property || ($property['owner_id'] != $user['id'] && $user['role'] !== 'admin')) {
    flash('error', 'Property not found or access denied.');
    redirect('/owner-dashboard.php');
}

// Delete property images from disk
$images = get_property_images($propertyId);
foreach ($images as $img) {
    if (strpos($img['image_path'], 'http') !== 0) {
        $filePath = UPLOAD_DIR . $img['image_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}

// Delete the property (cascade will handle property_images, bookings, wishlist, reviews)
$stmt = db()->prepare('DELETE FROM properties WHERE id = ?');
$stmt->execute([$propertyId]);

flash('success', 'Property deleted successfully.');
if ($user['role'] === 'admin') {
    redirect('/admin.php');
} else {
    redirect('/owner-dashboard.php');
}
