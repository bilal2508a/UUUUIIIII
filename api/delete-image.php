<?php
// Mehmaan Hub - Delete Property Image
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

$user = requireLogin();

$imageId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$propertyId = isset($_GET['property_id']) ? (int)$_GET['property_id'] : 0;

if (!$imageId || !$propertyId) {
    flash('error', 'Invalid request.');
    redirect('owner-dashboard.php');
}

// Verify the image exists and belongs to a property owned by current user (or admin)
$stmt = db()->prepare('SELECT pi.*, p.owner_id FROM property_images pi JOIN properties p ON pi.property_id = p.id WHERE pi.id = ?');
$stmt->execute([$imageId]);
$image = $stmt->fetch();

if (!$image) {
    flash('error', 'Image not found.');
    redirect('edit-property.php?id=' . $propertyId);
}

if ($image['owner_id'] != $user['id'] && $user['role'] !== 'admin') {
    flash('error', 'You do not have permission to delete this image.');
    redirect('edit-property.php?id=' . $propertyId);
}

// Delete from database
$delStmt = db()->prepare('DELETE FROM property_images WHERE id = ?');
$delStmt->execute([$imageId]);

// Delete the physical file if it exists
if (!empty($image['image_path']) && strpos($image['image_path'], 'http') !== 0) {
    $filePath = UPLOAD_DIR . $image['image_path'];
    if (file_exists($filePath)) {
        @unlink($filePath);
    }
}

// If the deleted image was primary, promote the next image to primary
if ($image['is_primary']) {
    $nextStmt = db()->prepare('SELECT id FROM property_images WHERE property_id = ? ORDER BY sort_order ASC LIMIT 1');
    $nextStmt->execute([$propertyId]);
    $next = $nextStmt->fetch();
    if ($next) {
        $updStmt = db()->prepare('UPDATE property_images SET is_primary = 1 WHERE id = ?');
        $updStmt->execute([$next['id']]);
    }
}

flash('success', 'Image deleted.');
redirect('edit-property.php?id=' . $propertyId);
