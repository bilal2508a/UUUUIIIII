<?php
// Mehmaan Hub - Configuration File

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mehmaan_hub');
define('SITE_NAME', 'Mehmaan Hub');

// Auto-detect base URL for subdirectory hosting
$_scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$_base = rtrim(preg_replace('#/[^/]+\.php$#', '', $_scriptDir), '/');
$_base = preg_replace('#/(api|includes)$#', '', $_base);
define('SITE_URL', $_base);
unset($_scriptDir, $_base);

// Upload paths
define('UPLOAD_DIR', __DIR__ . '/../uploads/properties/');
define('UPLOAD_URL', SITE_URL . '/uploads/properties/');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Singleton PDO connection
function db() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    return $pdo;
}

// Send no-cache headers
function send_no_cache_headers() {
    if (!headers_sent()) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    }
}

// Format price as Rs
function format_price($price) {
    return 'Rs ' . number_format((float)$price, 0);
}

// Alias for backward compat
function formatPKR($amount) {
    return format_price($amount);
}

// Format date string to 'M j, Y'
function formatDate($dateStr) {
    $date = new DateTime($dateStr);
    return $date->format('M j, Y');
}

// Escape output for HTML safety
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Redirect to a path (prepends SITE_URL if path starts with /)
function redirect($path) {
    if (strpos($path, 'http') === 0) {
        header('Location: ' . $path);
        exit;
    }
    header('Location: ' . SITE_URL . '/' . ltrim($path, '/'));
    exit;
}

// Build a URL relative to SITE_URL
function url($path = '/') {
    if (strpos($path, 'http') === 0 || strpos($path, '//') === 0) {
        return $path;
    }
    return SITE_URL . '/' . ltrim($path, '/');
}

// Flash messages
function flash($key, $value = null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
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

// Image URL helper (handles both URLs and uploaded files)
function image_url($path) {
    if (strpos($path, 'http') === 0) {
        return $path;
    }
    return UPLOAD_URL . $path;
}

// Get property images from property_images table
function get_property_images($propertyId) {
    $stmt = db()->prepare('SELECT * FROM property_images WHERE property_id = ? ORDER BY is_primary DESC, sort_order ASC');
    $stmt->execute([$propertyId]);
    return $stmt->fetchAll();
}

// Get primary image for a property
function get_primary_image($propertyId) {
    $stmt = db()->prepare('SELECT image_path FROM property_images WHERE property_id = ? ORDER BY is_primary DESC, sort_order ASC LIMIT 1');
    $stmt->execute([$propertyId]);
    $row = $stmt->fetch();
    return $row ? $row['image_path'] : null;
}

// Get all properties with filters
function get_all_properties($limit = null, $search = '', $type = '', $city = '', $minPrice = null, $maxPrice = null) {
    $sql = "SELECT p.*, u.name as owner_name FROM properties p JOIN users u ON p.owner_id = u.id WHERE p.status = 'available'";
    $params = [];
    if ($search) {
        $sql .= " AND (p.title LIKE ? OR p.description LIKE ? OR p.city LIKE ? OR p.address LIKE ?)";
        $term = "%$search%";
        $params[] = $term; $params[] = $term; $params[] = $term; $params[] = $term;
    }
    if ($type) {
        $sql .= " AND p.property_type = ?";
        $params[] = $type;
    }
    if ($city) {
        $sql .= " AND p.city LIKE ?";
        $params[] = "%$city%";
    }
    if ($minPrice !== null) {
        $sql .= " AND p.price >= ?";
        $params[] = $minPrice;
    }
    if ($maxPrice !== null) {
        $sql .= " AND p.price <= ?";
        $params[] = $maxPrice;
    }
    $sql .= " ORDER BY p.featured DESC, p.created_at DESC";
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Get single property by ID with owner info
function get_property_by_id($id) {
    $stmt = db()->prepare('SELECT p.*, u.name as owner_name, u.phone as owner_phone, u.email as owner_email FROM properties p JOIN users u ON p.owner_id = u.id WHERE p.id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Get properties by owner
function get_user_properties($ownerId) {
    $stmt = db()->prepare('SELECT p.*, (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as primary_image FROM properties p WHERE p.owner_id = ? ORDER BY p.created_at DESC');
    $stmt->execute([$ownerId]);
    return $stmt->fetchAll();
}

// Get user's bookings
function get_user_bookings($userId) {
    $stmt = db()->prepare('SELECT b.*, p.title as property_title, p.city as property_city, p.area as property_area, p.price_period, (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as primary_image FROM bookings b JOIN properties p ON b.property_id = p.id WHERE b.tenant_id = ? ORDER BY b.created_at DESC');
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

// Get owner's bookings (all bookings on their properties)
function get_owner_bookings($ownerId) {
    $stmt = db()->prepare('SELECT b.*, p.title as property_title, u.name as tenant_name, u.phone as tenant_phone, u.email as tenant_email, (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as primary_image FROM bookings b JOIN properties p ON b.property_id = p.id JOIN users u ON b.tenant_id = u.id WHERE p.owner_id = ? ORDER BY b.created_at DESC');
    $stmt->execute([$ownerId]);
    return $stmt->fetchAll();
}

// Get user's wishlist
function get_wishlist($userId) {
    $stmt = db()->prepare('SELECT w.*, p.*, (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as primary_image FROM wishlist w JOIN properties p ON w.property_id = p.id WHERE w.user_id = ? ORDER BY w.created_at DESC');
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

// Check if property is in user's wishlist
function is_in_wishlist($userId, $propertyId) {
    $stmt = db()->prepare('SELECT id FROM wishlist WHERE user_id = ? AND property_id = ?');
    $stmt->execute([$userId, $propertyId]);
    return $stmt->fetch() ? true : false;
}

// Check if user has already booked a property (pending or confirmed)
function has_user_booked_property($userId, $propertyId) {
    $stmt = db()->prepare("SELECT id FROM bookings WHERE tenant_id = ? AND property_id = ? AND status IN ('pending','confirmed')");
    $stmt->execute([$userId, $propertyId]);
    return $stmt->fetch() ? true : false;
}

// Get list of property IDs the user has booked
function get_user_booked_property_ids($userId) {
    $stmt = db()->prepare("SELECT DISTINCT property_id FROM bookings WHERE tenant_id = ? AND status IN ('pending','confirmed')");
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Get reviews for a property
function get_reviews($propertyId) {
    $stmt = db()->prepare('SELECT r.*, u.name as user_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.property_id = ? ORDER BY r.created_at DESC');
    $stmt->execute([$propertyId]);
    return $stmt->fetchAll();
}

// Get average rating and count for a property
function get_avg_rating($propertyId) {
    $stmt = db()->prepare('SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM reviews WHERE property_id = ?');
    $stmt->execute([$propertyId]);
    return $stmt->fetch();
}

// Get property type label
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

// Find user by email or phone (for password reset)
function find_user_by_email_or_phone($identifier) {
    $identifier = trim($identifier);
    if (!$identifier) return null;
    $stmt = db()->prepare('SELECT id, name, email, phone FROM users WHERE email = ? OR phone = ? LIMIT 1');
    $stmt->execute([$identifier, $identifier]);
    return $stmt->fetch();
}

// Generate 6-digit OTP
function generate_otp() {
    return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Create password reset entry with OTP
function create_password_reset($userId) {
    $otp = generate_otp();
    $otpHash = password_hash($otp, PASSWORD_DEFAULT);
    $expiresAt = date('Y-m-d H:i:s', time() + 600);

    $stmt = db()->prepare('DELETE FROM password_resets WHERE user_id = ?');
    $stmt->execute([$userId]);

    $stmt = db()->prepare('INSERT INTO password_resets (user_id, otp_hash, expires_at) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $otpHash, $expiresAt]);

    return $otp;
}

// Verify OTP for password reset
function verify_password_reset_otp($userId, $otp) {
    $stmt = db()->prepare('SELECT id, otp_hash, expires_at, used FROM password_resets WHERE user_id = ? ORDER BY created_at DESC LIMIT 1');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();

    if (!$row || $row['used']) return false;
    if (strtotime($row['expires_at']) < time()) return false;
    if (!password_verify($otp, $row['otp_hash'])) return false;

    $stmt = db()->prepare('UPDATE password_resets SET used = 1 WHERE id = ?');
    $stmt->execute([$row['id']]);

    return true;
}

// Get current path (relative to SITE_URL)
function currentPath() {
    $full = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (SITE_URL !== '' && strpos($full, SITE_URL) === 0) {
        $full = substr($full, strlen(SITE_URL));
    }
    if ($full === '') $full = '/';
    return $full;
}

// Check if a nav link is active
function isActive($path) {
    $current = currentPath();
    if ($path === '/' && $current === '/') return true;
    if ($path !== '/' && strpos($current, $path) === 0) return true;
    return false;
}

// Get list of cities from database
function getCities() {
    $cities = [];
    $stmt = db()->query("SELECT DISTINCT city FROM properties WHERE city != '' AND status = 'available' ORDER BY city");
    while ($row = $stmt->fetch()) {
        $cities[] = $row['city'];
    }
    return $cities;
}

// Get dashboard URL based on role
function dashboardUrlForRole($role) {
    switch ($role) {
        case 'admin': return '/admin.php';
        case 'owner': return '/owner-dashboard.php';
        default: return '/dashboard.php';
    }
}
