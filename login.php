<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    $u = currentUser();
    if ($u) {
        redirect(dashboardUrlForRole($u['role']));
    } else {
        session_destroy();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        flash('error', 'Please fill in all fields.');
    } elseif (!signIn($email, $password)) {
        flash('error', 'Invalid email or password.');
    } else {
        $user = currentUser();
        flash('success', 'Welcome back, ' . $user['name'] . '!');
        redirect(dashboardUrlForRole($user['role']));
    }
    redirect('/login.php');
}

include __DIR__ . '/includes/header-minimal.php';
?>

<div class="auth-wrapper">
    <div style="width:100%;max-width:440px;animation:fadeInUp 0.5s ease;">
        <div class="auth-card">
            <!-- Logo -->
            <div style="text-align:center;margin-bottom:2rem;">
                <a href="<?php echo url('/index.php'); ?>" style="text-decoration:none;display:inline-flex;align-items:center;gap:0.5rem;margin-bottom:1.5rem;">
                    <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,var(--primary-600),var(--accent-500));color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.5rem;box-shadow:0 8px 20px -4px rgba(26,82,245,0.4);">
                        <i class="bi bi-buildings"></i>
                    </div>
                    <span style="font-size:1.5rem;font-weight:800;color:var(--slate-900);">Mehmaan<span style="background:linear-gradient(135deg,var(--primary-600),var(--accent-500));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Hub</span></span>
                </a>
                <h1 style="font-size:1.75rem;font-weight:800;color:var(--slate-900);margin:0 0 0.5rem;letter-spacing:-0.02em;">Welcome Back</h1>
                <p style="color:var(--slate-500);margin:0;">Sign in to your account to continue</p>
            </div>

            <form method="POST" action="<?php echo url('/login.php'); ?>">
                <div style="margin-bottom:1.25rem;">
                    <label class="form-label-mh">Email Address</label>
                    <div style="position:relative;">
                        <i class="bi bi-envelope" style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--slate-400);z-index:1;"></i>
                        <input type="email" name="email" placeholder="you@example.com" required class="form-control-mh" style="padding-left:2.75rem;">
                    </div>
                </div>
                <div style="margin-bottom:1.25rem;">
                    <label class="form-label-mh">Password</label>
                    <div style="position:relative;">
                        <i class="bi bi-lock" style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--slate-400);z-index:1;"></i>
                        <input type="password" name="password" placeholder="Enter your password" required class="form-control-mh" style="padding-left:2.75rem;">
                    </div>
                </div>
                <div style="display:flex;justify-content:flex-end;margin-bottom:1.5rem;">
                    <a href="<?php echo url('/forgot-password.php'); ?>" style="color:var(--primary-600);font-size:0.85rem;font-weight:600;">Forgot password?</a>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg" style="padding:0.9rem;">
                    Sign In
                </button>
            </form>

            <p style="text-align:center;color:var(--slate-500);margin:1.5rem 0 0;font-size:0.9rem;">
                Don't have an account? <a href="<?php echo url('/register.php'); ?>" style="color:var(--primary-600);font-weight:600;">Register here</a>
            </p>
        </div>

        <!-- Demo Accounts -->
        <div class="glass-card" style="border-radius:var(--radius-md);padding:1.25rem;margin-top:1rem;">
            <h6 style="color:var(--slate-900);font-weight:700;margin:0 0 0.75rem;text-align:center;"><i class="bi bi-info-circle" style="color:var(--primary-500);"></i> Demo Accounts</h6>
            <div style="display:flex;flex-direction:column;gap:0.5rem;font-size:0.82rem;color:var(--slate-600);">
                <div style="display:flex;justify-content:space-between;padding:0.4rem 0.6rem;background:var(--slate-50);border-radius:var(--radius-xs);">
                    <span><strong style="color:var(--slate-900);">Admin:</strong> admin@mehmaanhub.com</span>
                    <span style="font-weight:600;">admin123</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:0.4rem 0.6rem;background:var(--slate-50);border-radius:var(--radius-xs);">
                    <span><strong style="color:var(--slate-900);">Owner:</strong> owner@mehmaanhub.com</span>
                    <span style="font-weight:600;">owner123</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:0.4rem 0.6rem;background:var(--slate-50);border-radius:var(--radius-xs);">
                    <span><strong style="color:var(--slate-900);">Tenant:</strong> tenant@mehmaanhub.com</span>
                    <span style="font-weight:600;">tenant123</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer-minimal.php'; ?>
