<?php
// Toggle wishlist (redirect endpoint - for non-AJAX calls)
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$propertyId = (int)($_GET['property_id'] ?? 0);
$userId = $_SESSION['user_id'];

if ($propertyId > 0) {
    if (is_in_wishlist($userId, $propertyId)) {
        $stmt = db()->prepare('DELETE FROM wishlist WHERE user_id = ? AND property_id = ?');
        $stmt->execute([$userId, $propertyId]);
    } else {
        $stmt = db()->prepare('INSERT INTO wishlist (user_id, property_id) VALUES (?, ?)');
        $stmt->execute([$userId, $propertyId]);
    }
}

$referer = $_SERVER['HTTP_REFERER'] ?? url('/wishlist.php');
redirect($referer);
