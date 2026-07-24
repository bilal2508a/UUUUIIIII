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
                    <span class="badge badge-info" style="font-size:0.8rem;"><?php echo ucfirst(e($user['role'])); ?> Account</span>
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
            </div>

            <div class="col-lg-8">
                <!-- Edit Profile Form -->
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:2rem;margin-bottom:1.5rem;">
                    <h4 style="color:#0f172a;font-weight:700;margin:0 0 1.5rem;"><i class="bi bi-person-gear" style="color:#0ea5e9;"></i> Edit Profile</h4>
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
                                <input type="tel" name="phone" value="<?php echo e($user['phone']); ?>" class="form-control" style="border-radius:10px;" placeholder="03XX-XXXXXXX">
                            </div>
                            <div class="col-md-6">
                                <label style="font-size:0.85rem;font-weight:600;color:#64748b;margin-bottom:0.25rem;display:block;">Account Type</label>
                                <input type="text" value="<?php echo ucfirst(e($user['role'])); ?>" class="form-control" style="border-radius:10px;" disabled>
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

<?php include __DIR__ . '/includes/footer.php'; ?>
