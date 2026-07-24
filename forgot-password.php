<?php
// Mehmaan Hub - Forgot Password
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$errors = [];
$demoOtp = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');

    if ($identifier === '') {
        $errors[] = 'Please enter your email or phone number.';
    } else {
        $foundUser = find_user_by_email_or_phone($identifier);
        if (!$foundUser) {
            $errors[] = 'No account found with that email or phone number.';
        } else {
            // Generate OTP and create password reset entry
            $otp = create_password_reset($foundUser['id']);

            // Store user_id in session for reset-password.php
            $_SESSION['reset_user_id'] = $foundUser['id'];
            $_SESSION['reset_user_name'] = $foundUser['name'];

            // Demo: show OTP on screen (no mail server configured)
            $demoOtp = $otp;
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
                        <h1 style="font-size:1.5rem;font-weight:800;color:#0f172a;margin-bottom:0.5rem;">Forgot Password</h1>
                        <p style="color:#64748b;margin-bottom:1.5rem;">Enter your email or phone number and we'll send you a verification code.</p>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul style="margin:0;padding-left:1.25rem;">
                                    <?php foreach ($errors as $err): ?>
                                        <li><?php echo e($err); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <?php if ($demoOtp !== null): ?>
                            <div class="alert alert-success">
                                <p style="margin:0 0 0.5rem;font-weight:700;"><i class="bi bi-shield-check"></i> Verification Code Generated</p>
                                <p style="margin:0 0 0.5rem;color:#64748b;font-size:0.9rem;">Hi <?php echo e($_SESSION['reset_user_name'] ?? ''); ?>, your one-time password (OTP) is:</p>
                                <div style="font-size:2rem;font-weight:800;letter-spacing:0.5rem;text-align:center;padding:1rem;background:#f0f9ff;border-radius:12px;color:#0ea5e9;margin-bottom:0.5rem;">
                                    <?php echo e($demoOtp); ?>
                                </div>
                                <p style="margin:0;color:#64748b;font-size:0.85rem;"><i class="bi bi-clock"></i> This code expires in 10 minutes.</p>
                            </div>
                            <a href="<?php echo url('/reset-password.php'); ?>" class="btn btn-primary w-100">
                                <i class="bi bi-arrow-right-circle"></i> Continue to Reset Password
                            </a>
                        <?php else: ?>
                            <form method="POST" action="<?php echo url('/forgot-password.php'); ?>">
                                <div class="mb-3">
                                    <label class="form-label-mh">Email or Phone Number</label>
                                    <input type="text" name="identifier" class="form-control-mh" value="<?php echo e($_POST['identifier'] ?? ''); ?>" placeholder="e.g. user@example.com or 03001234567" required autofocus>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-send"></i> Send Verification Code
                                </button>
                            </form>
                        <?php endif; ?>

                        <div class="text-center mt-4">
                            <a href="<?php echo url('/login.php'); ?>" style="color:#0ea5e9;font-weight:600;text-decoration:none;font-size:0.9rem;">
                                <i class="bi bi-arrow-left"></i> Back to Sign In
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer-minimal.php'; ?>
