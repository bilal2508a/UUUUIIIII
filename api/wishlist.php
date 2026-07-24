<?php
// Toggle wishlist (AJAX endpoint - returns JSON)
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$propertyId = (int)($_POST['property_id'] ?? 0);
$userId = $_SESSION['user_id'];

if (!$propertyId) {
    echo json_encode(['success' => false, 'message' => 'Invalid property']);
    exit;
}

if (is_in_wishlist($userId, $propertyId)) {
    $stmt = db()->prepare('DELETE FROM wishlist WHERE user_id = ? AND property_id = ?');
    $stmt->execute([$userId, $propertyId]);
    echo json_encode(['success' => true, 'action' => 'removed']);
} else {
    $stmt = db()->prepare('INSERT INTO wishlist (user_id, property_id) VALUES (?, ?)');
    $stmt->execute([$userId, $propertyId]);
    echo json_encode(['success' => true, 'action' => 'added']);
}
