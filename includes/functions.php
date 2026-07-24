<?php
require_once __DIR__ . '/db.php';

function start_session_safe() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function send_no_cache_headers() {
    if (!headers_sent()) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    }
}

function current_user() {
    start_session_safe();
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    static $cachedUser = null;
    if ($cachedUser !== null) {
        return $cachedUser;
    }
    $stmt = db()->prepare('SELECT id, name, email, phone, role, avatar FROM users WHERE id = ?');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        return null;
    }
    $cachedUser = $result->fetch_assoc();
    return $cachedUser;
}

function require_login() {
    send_no_cache_headers();
    $user = current_user();
    if (!$user) {
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
    return $user;
}

function require_role($role) {
    $user = require_login();
    if ($user['role'] !== $role && $user['role'] !== 'admin') {
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    }
    return $user;
}

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header('Location: ' . SITE_URL . '/' . ltrim($path, '/'));
    exit;
}

function flash($key, $value = null) {
    start_session_safe();
    if ($value !== null) {
        $_SESSION['flash'][$key] = $value;
        return;
    }
    if (isset($_SESSION['flash'][$key])) {
        $v = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $v;
    }
    return null;
}

function image_url($path) {
    if (strpos($path, 'http') === 0) {
        return $path;
    }
    return UPLOAD_URL . $path;
}

function get_property_images($propertyId) {
    $stmt = db()->prepare('SELECT * FROM property_images WHERE property_id = ? ORDER BY is_primary DESC, sort_order ASC');
    $stmt->bind_param('i', $propertyId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_primary_image($propertyId) {
    $stmt = db()->prepare('SELECT image_path FROM property_images WHERE property_id = ? ORDER BY is_primary DESC, sort_order ASC LIMIT 1');
    $stmt->bind_param('i', $propertyId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['image_path'];
    }
    return null;
}

function get_all_properties($limit = null, $search = '', $type = '', $city = '', $minPrice = null, $maxPrice = null) {
    $sql = "SELECT p.*, u.name as owner_name FROM properties p JOIN users u ON p.owner_id = u.id WHERE p.status = 'available'";
    $params = [];
    $types = '';
    if ($search) {
        $sql .= " AND (p.title LIKE ? OR p.description LIKE ? OR p.city LIKE ? OR p.address LIKE ?)";
        $term = "%$search%";
        $params[] = $term; $params[] = $term; $params[] = $term; $params[] = $term;
        $types .= 'ssss';
    }
    if ($type) {
        $sql .= " AND p.property_type = ?";
        $params[] = $type; $types .= 's';
    }
    if ($city) {
        $sql .= " AND p.city LIKE ?";
        $params[] = "%$city%"; $types .= 's';
    }
    if ($minPrice !== null) {
        $sql .= " AND p.price >= ?";
        $params[] = $minPrice; $types .= 'd';
    }
    if ($maxPrice !== null) {
        $sql .= " AND p.price <= ?";
        $params[] = $maxPrice; $types .= 'd';
    }
    $sql .= " ORDER BY p.created_at DESC";
    if ($limit) {
        $sql .= " LIMIT $limit";
    }
    $stmt = db()->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_property_by_id($id) {
    $stmt = db()->prepare('SELECT p.*, u.name as owner_name, u.phone as owner_phone, u.email as owner_email FROM properties p JOIN users u ON p.owner_id = u.id WHERE p.id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function get_user_properties($ownerId) {
    $stmt = db()->prepare('SELECT p.*, (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as primary_image FROM properties p WHERE p.owner_id = ? ORDER BY p.created_at DESC');
    $stmt->bind_param('i', $ownerId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_user_bookings($userId) {
    $stmt = db()->prepare('SELECT b.*, p.title as property_title, (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as primary_image FROM bookings b JOIN properties p ON b.property_id = p.id WHERE b.tenant_id = ? ORDER BY b.created_at DESC');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_owner_bookings($ownerId) {
    $stmt = db()->prepare('SELECT b.*, p.title as property_title, u.name as tenant_name, u.phone as tenant_phone, (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as primary_image FROM bookings b JOIN properties p ON b.property_id = p.id JOIN users u ON b.tenant_id = u.id WHERE p.owner_id = ? ORDER BY b.created_at DESC');
    $stmt->bind_param('i', $ownerId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_wishlist($userId) {
    $stmt = db()->prepare('SELECT w.*, p.*, (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as primary_image FROM wishlist w JOIN properties p ON w.property_id = p.id WHERE w.user_id = ? ORDER BY w.created_at DESC');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function is_in_wishlist($userId, $propertyId) {
    $stmt = db()->prepare('SELECT id FROM wishlist WHERE user_id = ? AND property_id = ?');
    $stmt->bind_param('ii', $userId, $propertyId);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function has_user_booked_property($userId, $propertyId) {
    $stmt = db()->prepare("SELECT id FROM bookings WHERE tenant_id = ? AND property_id = ? AND status IN ('pending','confirmed')");
    $stmt->bind_param('ii', $userId, $propertyId);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function get_user_booked_property_ids($userId) {
    $stmt = db()->prepare("SELECT DISTINCT property_id FROM bookings WHERE tenant_id = ? AND status IN ('pending','confirmed')");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    return array_column($rows, 'property_id');
}

function get_reviews($propertyId) {
    $stmt = db()->prepare('SELECT r.*, u.name as user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.property_id = ? ORDER BY r.created_at DESC');
    $stmt->bind_param('i', $propertyId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function get_avg_rating($propertyId) {
    $stmt = db()->prepare('SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM reviews WHERE property_id = ?');
    $stmt->bind_param('i', $propertyId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row;
}

function get_property_type_label($type) {
    $types = [
        'apartment' => 'Apartment',
        'house' => 'House',
        'room' => 'Room',
        'studio' => 'Studio',
        'villa' => 'Villa'
    ];
    return $types[$type] ?? ucfirst($type);
}

function format_price($price) {
    return 'Rs ' . number_format($price, 0);
}

function find_user_by_email_or_phone($identifier) {
    $identifier = trim($identifier);
    if (!$identifier) {
        return null;
    }
    $stmt = db()->prepare('SELECT id, name, email, phone FROM users WHERE email = ? OR phone = ? LIMIT 1');
    $stmt->bind_param('ss', $identifier, $identifier);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function generate_otp() {
    return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

function create_password_reset($userId) {
    $otp = generate_otp();
    $otpHash = password_hash($otp, PASSWORD_DEFAULT);
    $expiresAt = date('Y-m-d H:i:s', time() + 600);

    db()->query("DELETE FROM password_resets WHERE user_id = " . (int)$userId);

    $stmt = db()->prepare('INSERT INTO password_resets (user_id, otp_hash, expires_at) VALUES (?, ?, ?)');
    $stmt->bind_param('iss', $userId, $otpHash, $expiresAt);
    $stmt->execute();

    return $otp;
}

function verify_password_reset_otp($userId, $otp) {
    $stmt = db()->prepare('SELECT id, otp_hash, expires_at, used FROM password_resets WHERE user_id = ? ORDER BY created_at DESC LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row || $row['used']) {
        return false;
    }
    if (strtotime($row['expires_at']) < time()) {
        return false;
    }
    if (!password_verify($otp, $row['otp_hash'])) {
        return false;
    }

    $upd = db()->prepare('UPDATE password_resets SET used = 1 WHERE id = ?');
    $upd->bind_param('i', $row['id']);
    $upd->execute();

    return true;
}
