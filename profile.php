<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$user = requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';

    if (!$name) {
        flash('error', 'Name cannot be empty.');
    } else {
        $stmt = db()->prepare('UPDATE users SET name = ?, phone = ? WHERE id = ?');
        $stmt->execute([$name, $phone, $user['id']]);

        if ($newPassword) {
            $pwStmt = db()->prepare('SELECT password FROM users WHERE id = ?');
            $pwStmt->execute([$user['id']]);
            $pwRow = $pwStmt->fetch();
            if (!password_verify($currentPassword, $pwRow['password'])) {
                flash('error', 'Current password is incorrect.');
            } elseif (strlen($newPassword) < 6) {
                flash('error', 'New password must be at least 6 characters.');
            } else {
                $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                $pwStmt = db()->prepare('UPDATE users SET password = ? WHERE id = ?');
                $pwStmt->execute([$hashed, $user['id']]);
                flash('success', 'Profile and password updated successfully!');
            }
        } else {
            flash('success', 'Profile updated successfully!');
        }
    }
    redirect('/profile.php');
}

// Refresh user data
$user = currentUser();

// Owner stats
$ownerStats = null;
if ($user['role'] === 'owner') {
    $myProperties = get_user_properties($user['id']);
    $ownerBookings = get_owner_bookings($user['id']);
    $earnings = 0;
    foreach ($ownerBookings as $b) {
        if ($b['status'] === 'confirmed' && $b['payment_status'] === 'paid') {
            $earnings += (float)$b['total_amount'];
        }
    }
    $ownerStats = [
        'properties' => count($myProperties),
        'bookings' => count($ownerBookings),
        'earnings' => $earnings,
    ];
}

// Tenant stats
$tenantStats = null;
if ($user['role'] === 'tenant') {
    try {
        $myBookings = db()->prepare("SELECT COUNT(*) as c FROM bookings WHERE tenant_id = ?");
        $myBookings->execute([$user['id']]);
        $bookingCount = (int)$myBookings->fetch()['c'];
        $wishStmt = db()->prepare("SELECT COUNT(*) as c FROM wishlists WHERE user_id = ?");
        $wishStmt->execute([$user['id']]);
        $wishCount = (int)$wishStmt->fetch()['c'];
        $tenantStats = [
            'bookings' => $bookingCount,
            'wishlist' => $wishCount,
        ];
    } catch (Exception $e) {
        $tenantStats = ['bookings' => 0, 'wishlist' => 0];
    }
}

// Admin stats
$adminStats = null;
if ($user['role'] === 'admin') {
    try {
        $adminStats = [
            'users' => (int)db()->query("SELECT COUNT(*) as c FROM users")->fetch()['c'],
            'properties' => (int)db()->query("SELECT COUNT(*) as c FROM properties")->fetch()['c'],
            'bookings' => (int)db()->query("SELECT COUNT(*) as c FROM bookings")->fetch()['c'],
        ];
    } catch (Exception $e) {
        $adminStats = ['users' => 0, 'properties' => 0, 'bookings' => 0];
    }
}

$roleLabel = ucfirst(e($user['role']));
$roleIcon = 'bi-person';
if ($user['role'] === 'owner') $roleIcon = 'bi-building';
elseif ($user['role'] === 'admin') $roleIcon = 'bi-shield-check';

include __DIR__ . '/includes/header.php';
?>

<div style="background:linear-gradient(135deg,#0ea5e9,#14b8a6);padding:2.5rem 0;color:#fff;">
    <div class="container-app">
        <h1 style="font-size:2rem;font-weight:800;margin:0;letter-spacing:-0.02em;">My Profile</h1>
        <p style="margin:0.5rem 0 0;opacity:0.95;">Manage your account information</p>
    </div>
</div>

<section style="padding:2.5rem 0;">
    <div class="container-app">
        <div class="row g-4">
            <div class="col-lg-4">
                <!-- Profile Card -->
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:2rem;text-align:center;">
                    <div style="width:96px;height:96px;border-radius:50%;background:linear-gradient(135deg,#0ea5e9,#14b8a6);color:#fff;display:flex;align-items:center;justify-content:center;font-size:2.5rem;font-weight:800;margin:0 auto 1rem;">
                        <?php echo e(strtoupper(substr($user['name'], 0, 1))); ?>
                    </div>
                    <h4 style="margin:0;color:#0f172a;font-weight:700;"><?php echo e($user['name']); ?></h4>
                    <p style="color:#64748b;margin:0.25rem 0 0.5rem;"><?php echo e($user['email']); ?></p>
                    <span class="badge badge-info" style="font-size:0.8rem;"><i class="bi <?php echo $roleIcon; ?>"></i> <?php echo $roleLabel; ?> Account</span>
                    <hr style="border-color:#e2e8f0;margin:1.5rem 0;">
                    <div style="text-align:left;">
                        <div style="display:flex;justify-content:space-between;padding:0.5rem 0;">
                            <span style="color:#64748b;"><i class="bi bi-telephone"></i> Phone</span>
                            <strong style="color:#0f172a;"><?php echo e($user['phone'] ?: 'Not provided'); ?></strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:0.5rem 0;">
                            <span style="color:#64748b;"><i class="bi bi-calendar"></i> Joined</span>
                            <strong style="color:#0f172a;"><?php echo formatDate($user['created_at']); ?></strong>
                        </div>
                        <?php if (!empty($user['username'])): ?>
                        <div style="display:flex;justify-content:space-between;padding:0.5rem 0;">
                            <span style="color:#64748b;"><i class="bi bi-at"></i> Username</span>
                            <strong style="color:#0f172a;"><?php echo e($user['username']); ?></strong>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Owner Stats -->
                <?php if ($ownerStats): ?>
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:1.5rem;margin-top:1.5rem;">
                    <h5 style="color:#0f172a;font-weight:700;margin:0 0 1rem;"><i class="bi bi-graph-up" style="color:#14b8a6;"></i> Owner Stats</h5>
                    <div style="display:flex;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid #e2e8f0;">
                        <span style="color:#64748b;">Properties</span>
                        <strong style="color:#0f172a;"><?php echo $ownerStats['properties']; ?></strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid #e2e8f0;">
                        <span style="color:#64748b;">Bookings</span>
                        <strong style="color:#0f172a;"><?php echo $ownerStats['bookings']; ?></strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:0.5rem 0;">
                        <span style="color:#64748b;">Earnings</span>
                        <strong style="color:#0f172a;"><?php echo format_price($ownerStats['earnings']); ?></strong>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Tenant Stats -->
                <?php if ($tenantStats): ?>
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:1.5rem;margin-top:1.5rem;">
                    <h5 style="color:#0f172a;font-weight:700;margin:0 0 1rem;"><i class="bi bi-graph-up" style="color:#0ea5e9;"></i> Tenant Stats</h5>
                    <div style="display:flex;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid #e2e8f0;">
                        <span style="color:#64748b;">My Bookings</span>
                        <strong style="color:#0f172a;"><?php echo $tenantStats['bookings']; ?></strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:0.5rem 0;">
                        <span style="color:#64748b;">Wishlist Items</span>
                        <strong style="color:#0f172a;"><?php echo $tenantStats['wishlist']; ?></strong>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Admin Stats -->
                <?php if ($adminStats): ?>
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:1.5rem;margin-top:1.5rem;">
                    <h5 style="color:#0f172a;font-weight:700;margin:0 0 1rem;"><i class="bi bi-graph-up" style="color:#f59e0b;"></i> Admin Stats</h5>
                    <div style="display:flex;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid #e2e8f0;">
                        <span style="color:#64748b;">Total Users</span>
                        <strong style="color:#0f172a;"><?php echo $adminStats['users']; ?></strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid #e2e8f0;">
                        <span style="color:#64748b;">Properties</span>
                        <strong style="color:#0f172a;"><?php echo $adminStats['properties']; ?></strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:0.5rem 0;">
                        <span style="color:#64748b;">Bookings</span>
                        <strong style="color:#0f172a;"><?php echo $adminStats['bookings']; ?></strong>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-8">
                <!-- Account Info View (default) -->
                <div class="card" id="infoView" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:2rem;margin-bottom:1.5rem;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:0.5rem;">
                        <h4 style="color:#0f172a;font-weight:700;margin:0;"><i class="bi bi-person-lines-fill" style="color:#0ea5e9;"></i> Account Information</h4>
                        <button type="button" class="btn btn-primary" onclick="toggleEdit()" style="border-radius:10px;"><i class="bi bi-pencil-square"></i> Edit</button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div style="background:#f8fafc;border-radius:12px;padding:1rem 1.25rem;border:1px solid #e2e8f0;">
                                <div style="font-size:0.75rem;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:0.35rem;"><i class="bi bi-person"></i> Full Name</div>
                                <div style="font-size:1.05rem;font-weight:600;color:#0f172a;"><?php echo e($user['name']); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div style="background:#f8fafc;border-radius:12px;padding:1rem 1.25rem;border:1px solid #e2e8f0;">
                                <div style="font-size:0.75rem;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:0.35rem;"><i class="bi bi-envelope"></i> Email</div>
                                <div style="font-size:1.05rem;font-weight:600;color:#0f172a;word-break:break-all;"><?php echo e($user['email']); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div style="background:#f8fafc;border-radius:12px;padding:1rem 1.25rem;border:1px solid #e2e8f0;">
                                <div style="font-size:0.75rem;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:0.35rem;"><i class="bi bi-telephone"></i> Phone Number</div>
                                <div style="font-size:1.05rem;font-weight:600;color:#0f172a;"><?php echo e($user['phone'] ?: 'Not provided'); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div style="background:#f8fafc;border-radius:12px;padding:1rem 1.25rem;border:1px solid #e2e8f0;">
                                <div style="font-size:0.75rem;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:0.35rem;"><i class="bi bi-person-badge"></i> Account Type</div>
                                <div style="font-size:1.05rem;font-weight:600;color:#0f172a;"><?php echo $roleLabel; ?></div>
                            </div>
                        </div>
                        <?php if (!empty($user['username'])): ?>
                        <div class="col-md-6">
                            <div style="background:#f8fafc;border-radius:12px;padding:1rem 1.25rem;border:1px solid #e2e8f0;">
                                <div style="font-size:0.75rem;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:0.35rem;"><i class="bi bi-at"></i> Username</div>
                                <div style="font-size:1.05rem;font-weight:600;color:#0f172a;"><?php echo e($user['username']); ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="col-md-6">
                            <div style="background:#f8fafc;border-radius:12px;padding:1rem 1.25rem;border:1px solid #e2e8f0;">
                                <div style="font-size:0.75rem;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:0.35rem;"><i class="bi bi-calendar"></i> Member Since</div>
                                <div style="font-size:1.05rem;font-weight:600;color:#0f172a;"><?php echo formatDate($user['created_at']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Profile Form (hidden by default) -->
                <div class="card" id="editForm" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:2rem;margin-bottom:1.5rem;display:none;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:0.5rem;">
                        <h4 style="color:#0f172a;font-weight:700;margin:0;"><i class="bi bi-person-gear" style="color:#0ea5e9;"></i> Edit Profile</h4>
                        <button type="button" class="btn btn-ghost" onclick="toggleEdit()" style="border-radius:10px;"><i class="bi bi-x-lg"></i> Cancel</button>
                    </div>
                    <form method="POST" action="<?php echo url('/profile.php'); ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label style="font-size:0.85rem;font-weight:600;color:#64748b;margin-bottom:0.25rem;display:block;">Full Name</label>
                                <input type="text" name="name" value="<?php echo e($user['name']); ?>" class="form-control" style="border-radius:10px;" required>
                            </div>
                            <div class="col-md-6">
                                <label style="font-size:0.85rem;font-weight:600;color:#64748b;margin-bottom:0.25rem;display:block;">Email (cannot change)</label>
                                <input type="email" value="<?php echo e($user['email']); ?>" class="form-control" style="border-radius:10px;" disabled>
                            </div>
                            <div class="col-md-6">
                                <label style="font-size:0.85rem;font-weight:600;color:#64748b;margin-bottom:0.25rem;display:block;">Phone Number</label>
                                <input type="tel" name="phone" value="<?php echo e($user['phone']); ?>" maxlength="11" pattern="[0-9]{11}" class="form-control" style="border-radius:10px;" placeholder="03XXXXXXXXX">
                            </div>
                            <div class="col-md-6">
                                <label style="font-size:0.85rem;font-weight:600;color:#64748b;margin-bottom:0.25rem;display:block;">Account Type</label>
                                <input type="text" value="<?php echo $roleLabel; ?>" class="form-control" style="border-radius:10px;" disabled>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-top:1.5rem;border-radius:10px;"><i class="bi bi-save"></i> Save Changes</button>
                    </form>
                </div>

                <!-- Change Password -->
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:2rem;">
                    <h4 style="color:#0f172a;font-weight:700;margin:0 0 1.5rem;"><i class="bi bi-shield-lock" style="color:#14b8a6;"></i> Change Password</h4>
                    <form method="POST" action="<?php echo url('/profile.php'); ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label style="font-size:0.85rem;font-weight:600;color:#64748b;margin-bottom:0.25rem;display:block;">Current Password</label>
                                <input type="password" name="current_password" class="form-control" style="border-radius:10px;" placeholder="Required to change password">
                            </div>
                            <div class="col-md-6">
                                <label style="font-size:0.85rem;font-weight:600;color:#64748b;margin-bottom:0.25rem;display:block;">New Password</label>
                                <input type="password" name="new_password" class="form-control" style="border-radius:10px;" placeholder="Min 6 characters">
                            </div>
                        </div>
                        <small style="color:#64748b;display:block;margin-top:0.75rem;">Leave password fields blank to keep your current password.</small>
                        <button type="submit" class="btn btn-ghost" style="margin-top:1.5rem;border-radius:10px;"><i class="bi bi-key"></i> Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function toggleEdit() {
    var infoView = document.getElementById('infoView');
    var editForm = document.getElementById('editForm');
    if (infoView.style.display === 'none') {
        infoView.style.display = 'block';
        editForm.style.display = 'none';
    } else {
        infoView.style.display = 'none';
        editForm.style.display = 'block';
        editForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
