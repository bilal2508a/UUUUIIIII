<?php
// Mehmaan Hub - Reset Password with OTP
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

// Must have reset user_id in session (set by forgot-password.php)
$resetUserId = $_SESSION['reset_user_id'] ?? null;
$resetUserName = $_SESSION['reset_user_name'] ?? '';

if (!$resetUserId) {
    flash('error', 'Please request a password reset first.');
    redirect('forgot-password.php');
}

$errors = [];
$step = 1; // 1 = enter OTP, 2 = enter new password

// If OTP was already verified in this session, skip to step 2
if (isset($_SESSION['otp_verified']) && $_SESSION['otp_verified'] === true) {
    $step = 2;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'verify_otp' && $step === 1) {
        $otp = trim($_POST['otp'] ?? '');

        if ($otp === '' || strlen($otp) !== 6 || !ctype_digit($otp)) {
            $errors[] = 'Please enter a valid 6-digit code.';
        } else {
            if (verify_password_reset_otp($resetUserId, $otp)) {
                $_SESSION['otp_verified'] = true;
                $step = 2;
            } else {
                $errors[] = 'Invalid or expired verification code. Please try again.';
            }
        }
    } elseif ($action === 'reset_password' && $step === 2) {
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (strlen($newPassword) < 6) {
            $errors[] = 'Password must be at least 6 characters long.';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        } else {
            // Update the user's password
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = db()->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([$hashed, $resetUserId]);

            // Clean up session
            unset($_SESSION['reset_user_id']);
            unset($_SESSION['reset_user_name']);
            unset($_SESSION['otp_verified']);

            flash('success', 'Password reset successfully! Please sign in with your new password.');
            redirect('login.php');
        }
    }
}

include __DIR__ . '/includes/header-minimal.php';
?>

<div class="auth-page">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="text-center mb-4">
                    <a href="<?php echo url('/index.php'); ?>" class="d-inline-flex align-items-center gap-2 text-decoration-none">
                        <div class="d-flex align-items-center justify-content-center" style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,#0ea5e9,#14b8a6);color:#fff;font-size:1.5rem;">
                            <i class="bi bi-buildings"></i>
                        </div>
                        <span style="font-size:1.5rem;font-weight:800;color:#0f172a;">Mehmaan<span style="color:#0ea5e9;">Hub</span></span>
                    </a>
                </div>

                <div class="card">
                    <div class="card-body p-4 p-md-5">
                        <?php if ($step === 1): ?>
                            <h1 style="font-size:1.5rem;font-weight:800;color:#0f172a;margin-bottom:0.5rem;">Enter Verification Code</h1>
                            <p style="color:#64748b;margin-bottom:1.5rem;">
                                <?php if ($resetUserName): ?>
                                    Hi <?php echo e($resetUserName); ?>, we've sent a 6-digit code to your contact. Enter it below.
                                <?php else: ?>
                                    Enter the 6-digit verification code we sent you.
                                <?php endif; ?>
                            </p>

                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <ul style="margin:0;padding-left:1.25rem;">
                                        <?php foreach ($errors as $err): ?>
                                            <li><?php echo e($err); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="<?php echo url('/reset-password.php'); ?>">
                                <input type="hidden" name="action" value="verify_otp">
                                <div class="mb-3">
                                    <label class="form-label-mh">Verification Code (OTP)</label>
                                    <input type="text" name="otp" class="form-control-mh" maxlength="6" pattern="[0-9]{6}" placeholder="000000" style="text-align:center;font-size:1.5rem;letter-spacing:0.5rem;font-weight:700;" required autofocus>
                                    <small style="color:#64748b;">Enter the 6-digit code.</small>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-shield-check"></i> Verify Code
                                </button>
                            </form>

                            <div class="text-center mt-4">
                                <a href="<?php echo url('/forgot-password.php'); ?>" style="color:#0ea5e9;font-weight:600;text-decoration:none;font-size:0.9rem;">
                                    <i class="bi bi-arrow-left"></i> Request new code
                                </a>
                            </div>

                        <?php else: ?>
                            <h1 style="font-size:1.5rem;font-weight:800;color:#0f172a;margin-bottom:0.5rem;">Set New Password</h1>
                            <p style="color:#64748b;margin-bottom:1.5rem;">Your code has been verified. Choose a new password for your account.</p>

                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <ul style="margin:0;padding-left:1.25rem;">
                                        <?php foreach ($errors as $err): ?>
                                            <li><?php echo e($err); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="<?php echo url('/reset-password.php'); ?>">
                                <input type="hidden" name="action" value="reset_password">
                                <div class="mb-3">
                                    <label class="form-label-mh">New Password</label>
                                    <input type="password" name="new_password" class="form-control-mh" minlength="6" placeholder="At least 6 characters" required autofocus>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label-mh">Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="form-control-mh" minlength="6" placeholder="Re-enter new password" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-check-circle"></i> Reset Password
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer-minimal.php'; ?>
