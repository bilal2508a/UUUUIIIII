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
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$identifier || !$password) {
        flash('error', 'Please fill in all fields.');
    } elseif (!signIn($identifier, $password)) {
        flash('error', 'Invalid username/email or password.');
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
    <!-- Image Panel -->
    <div class="auth-panel-image">
        <img src="https://images.pexels.com/photos/323780/pexels-photo-323780.jpeg?auto=compress&cs=tinysrgb&w=1200" alt="Luxury property">
        <div class="auth-panel-image-content">
            <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:1.5rem;">
                <div style="width:48px;height:48px;border-radius:14px;background:rgba(255,255,255,0.2);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.3);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.5rem;">
                    <i class="bi bi-buildings"></i>
                </div>
                <span style="font-size:1.5rem;font-weight:800;color:#fff;letter-spacing:-0.02em;">Mehmaan<span style="color:var(--gold-300);">Hub</span></span>
            </div>
            <h2 style="font-size:2.25rem;font-weight:800;color:#fff;line-height:1.2;margin:0 0 0.75rem;letter-spacing:-0.02em;">Welcome back to your<br>perfect stay.</h2>
            <p style="font-size:1.05rem;color:rgba(255,255,255,0.85);margin:0;max-width:420px;line-height:1.6;">Sign in to manage your bookings, explore new properties, and connect with trusted owners across Pakistan.</p>
            <div style="display:flex;gap:2rem;margin-top:2rem;">
                <div>
                    <div style="font-size:1.75rem;font-weight:800;color:#fff;">500+</div>
                    <div style="font-size:0.85rem;color:rgba(255,255,255,0.7);">Properties</div>
                </div>
                <div>
                    <div style="font-size:1.75rem;font-weight:800;color:#fff;">120+</div>
                    <div style="font-size:0.85rem;color:rgba(255,255,255,0.7);">Owners</div>
                </div>
                <div>
                    <div style="font-size:1.75rem;font-weight:800;color:#fff;">15+</div>
                    <div style="font-size:0.85rem;color:rgba(255,255,255,0.7);">Cities</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Panel -->
    <div class="auth-panel-form">
        <div style="width:100%;max-width:440px;animation:fadeInUp 0.5s ease;">
            <!-- Mobile Logo -->
            <div style="text-align:center;margin-bottom:2rem;" class="d-lg-none">
                <div style="display:inline-flex;align-items:center;gap:0.5rem;">
                    <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,var(--primary-600),var(--accent-500));color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.5rem;box-shadow:0 8px 20px -4px rgba(26,82,245,0.4);">
                        <i class="bi bi-buildings"></i>
                    </div>
                    <span style="font-size:1.5rem;font-weight:800;color:var(--slate-900);">Mehmaan<span style="background:linear-gradient(135deg,var(--primary-600),var(--accent-500));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Hub</span></span>
                </div>
            </div>

            <h1 style="font-size:1.75rem;font-weight:800;color:var(--slate-900);margin:0 0 0.5rem;letter-spacing:-0.02em;">Sign In</h1>
            <p style="color:var(--slate-500);margin:0 0 2rem;">Enter your credentials to access your account</p>

            <form method="POST" action="<?php echo url('/login.php'); ?>">
                <div style="margin-bottom:1.25rem;">
                    <label class="form-label-mh">Username or Email</label>
                    <div style="position:relative;">
                        <i class="bi bi-person" style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--slate-400);z-index:1;"></i>
                        <input type="text" name="identifier" placeholder="Enter username or email" required class="form-control-mh" style="padding-left:2.75rem;">
                    </div>
                </div>
                <div style="margin-bottom:1.25rem;">
                    <label class="form-label-mh">Password</label>
                    <div style="position:relative;">
                        <i class="bi bi-lock" style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--slate-400);z-index:1;"></i>
                        <input type="password" name="password" id="loginPassword" placeholder="Enter your password" required class="form-control-mh" style="padding-left:2.75rem;padding-right:2.75rem;">
                        <button type="button" onclick="togglePassword('loginPassword', this)" style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--slate-400);cursor:pointer;padding:0.25rem;z-index:2;">
                            <i class="bi bi-eye"></i>
                        </button>
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

            <!-- Demo Accounts -->
            <div style="border-radius:var(--radius-md);padding:1.25rem;margin-top:1.5rem;background:var(--slate-50);border:1px solid var(--slate-200);">
                <h6 style="color:var(--slate-900);font-weight:700;margin:0 0 0.75rem;text-align:center;"><i class="bi bi-info-circle" style="color:var(--primary-500);"></i> Demo Accounts</h6>
                <div style="display:flex;flex-direction:column;gap:0.5rem;font-size:0.82rem;color:var(--slate-600);">
                    <div style="display:flex;justify-content:space-between;padding:0.4rem 0.6rem;background:#fff;border-radius:var(--radius-xs);">
                        <span><strong style="color:var(--slate-900);">Admin:</strong> admin@mehmaanhub.com</span>
                        <span style="font-weight:600;">admin123</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:0.4rem 0.6rem;background:#fff;border-radius:var(--radius-xs);">
                        <span><strong style="color:var(--slate-900);">Owner:</strong> owner@mehmaanhub.com</span>
                        <span style="font-weight:600;">owner123</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:0.4rem 0.6rem;background:#fff;border-radius:var(--radius-xs);">
                        <span><strong style="color:var(--slate-900);">Tenant:</strong> tenant@mehmaanhub.com</span>
                        <span style="font-weight:600;">tenant123</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId, btn) {
    var input = document.getElementById(inputId);
    var icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}
</script>

<?php include __DIR__ . '/includes/footer-minimal.php'; ?>
