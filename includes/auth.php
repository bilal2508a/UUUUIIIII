<?php
// Mehmaan Hub - Authentication Functions
// Requires config.php to be included first

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fetch current user from database (cached)
function currentUser() {
    if (!isLoggedIn()) return null;
    static $cachedUser = [];
    $uid = $_SESSION['user_id'];
    if (array_key_exists($uid, $cachedUser)) return $cachedUser[$uid];
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$uid]);
    $cachedUser[$uid] = $stmt->fetch();
    return $cachedUser[$uid];
}

// Require login - redirect if not logged in
function requireLogin() {
    send_no_cache_headers();
    if (!isset($_SESSION['user_id'])) {
        redirect('/login.php');
    }
    $user = currentUser();
    if (!$user) {
        // Stale session - user no longer exists in DB
        session_destroy();
        redirect('/login.php');
    }
    return $user;
}

// Require specific role (admin always passes)
function requireRole($role) {
    $user = requireLogin();
    if ($user['role'] !== $role && $user['role'] !== 'admin') {
        redirect('/index.php');
    }
    return $user;
}

// Sign in user with email/username and password
function signIn($identifier, $password) {
    $identifier = trim($identifier);
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? OR username = ? LIMIT 1');
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        return true;
    }
    return false;
}

// Sign up new user
function signUp($name, $email, $password, $role = 'tenant', $phone = '', $username = '') {
    $stmt = db()->prepare('SELECT id FROM users WHERE email = ? OR (username = ? AND username != "")');
    $stmt->execute([$email, $username]);
    if ($stmt->fetch()) return false;

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = db()->prepare('INSERT INTO users (name, username, email, password, role, phone) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$name, $username ?: null, $email, $hashed, $role, $phone]);
    $_SESSION['user_id'] = db()->lastInsertId();
    return true;
}

// Sign out user
function signOut() {
    session_destroy();
}
