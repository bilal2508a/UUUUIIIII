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

$prefillRole = ($_GET['role'] ?? '') === 'owner' ? 'owner' : 'tenant';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'tenant';

    if (!$name || !$email || !$password) {
        flash('error', 'Please fill in all required fields.');
    } elseif ($password !== $confirm) {
        flash('error', 'Passwords do not match.');
    } elseif (strlen($password) < 6) {
        flash('error', 'Password must be at least 6 characters.');
    } elseif (!in_array($role, ['tenant', 'owner'])) {
        flash('error', 'Invalid account type.');
    } elseif (!signUp($name, $email, $password, $role, $phone)) {
        flash('error', 'Email already registered. Please login.');
    } else {
        flash('success', 'Account created successfully! Welcome to Mehmaan Hub.');
        redirect(dashboardUrlForRole($role));
    }
    redirect('/register.php');
}

include __DIR__ . '/includes/header-minimal.php';
?>

<div class="auth-wrapper">
    <div style="width:100%;max-width:460px;animation:fadeInUp 0.5s ease;">
        <div class="auth-card">
            <!-- Logo -->
            <div style="text-align:center;margin-bottom:2rem;">
                <a href="<?php echo url('/index.php'); ?>" style="text-decoration:none;display:inline-flex;align-items:center;gap:0.5rem;margin-bottom:1.5rem;">
                    <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,var(--primary-600),var(--accent-500));color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.5rem;box-shadow:0 8px 20px -4px rgba(26,82,245,0.4);">
                        <i class="bi bi-buildings"></i>
                    </div>
                    <span style="font-size:1.5rem;font-weight:800;color:var(--slate-900);">Mehmaan<span style="background:linear-gradient(135deg,var(--primary-600),var(--accent-500));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Hub</span></span>
                </a>
                <h1 style="font-size:1.75rem;font-weight:800;color:var(--slate-900);margin:0 0 0.5rem;letter-spacing:-0.02em;">Create Account</h1>
                <p style="color:var(--slate-500);margin:0;">Join Mehmaan Hub to find or list properties</p>
            </div>

            <form method="POST" action="<?php echo url('/register.php'); ?>">
                <div style="margin-bottom:1rem;">
                    <label class="form-label-mh">Full Name</label>
                    <div style="position:relative;">
                        <i class="bi bi-person" style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--slate-400);z-index:1;"></i>
                        <input type="text" name="name" placeholder="Your full name" required class="form-control-mh" style="padding-left:2.75rem;">
                    </div>
                </div>
                <div style="margin-bottom:1rem;">
                    <label class="form-label-mh">Email Address</label>
                    <div style="position:relative;">
                        <i class="bi bi-envelope" style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--slate-400);z-index:1;"></i>
                        <input type="email" name="email" placeholder="you@example.com" required class="form-control-mh" style="padding-left:2.75rem;">
                    </div>
                </div>
                <div style="margin-bottom:1rem;">
                    <label class="form-label-mh">Phone Number <span style="color:var(--slate-400);font-weight:400;">(optional)</span></label>
                    <div style="position:relative;">
                        <i class="bi bi-telephone" style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--slate-400);z-index:1;"></i>
                        <input type="tel" name="phone" placeholder="03XX-XXXXXXX" class="form-control-mh" style="padding-left:2.75rem;">
                    </div>
                </div>

                <!-- Role Selector -->
                <div style="margin-bottom:1rem;">
                    <label class="form-label-mh">Account Type</label>
                    <div style="display:flex;gap:0.75rem;">
                        <label style="flex:1;cursor:pointer;">
                            <input type="radio" name="role" value="tenant" <?php echo $prefillRole === 'tenant' ? 'checked' : ''; ?> style="display:none;" onchange="selectRole(this)">
                            <div class="role-card-mh" id="role-tenant" style="padding:1rem;border:2px solid <?php echo $prefillRole === 'tenant' ? 'var(--primary-500)' : 'var(--slate-200)'; ?>;border-radius:var(--radius);text-align:center;background:<?php echo $prefillRole === 'tenant' ? 'var(--primary-50)' : '#fff'; ?>;transition:var(--transition);">
                                <i class="bi bi-person" style="font-size:1.5rem;color:var(--primary-600);"></i>
                                <div style="font-weight:700;color:var(--slate-900);margin-top:0.25rem;">Tenant</div>
                                <small style="color:var(--slate-500);">I want to rent</small>
                            </div>
                        </label>
                        <label style="flex:1;cursor:pointer;">
                            <input type="radio" name="role" value="owner" <?php echo $prefillRole === 'owner' ? 'checked' : ''; ?> style="display:none;" onchange="selectRole(this)">
                            <div class="role-card-mh" id="role-owner" style="padding:1rem;border:2px solid <?php echo $prefillRole === 'owner' ? 'var(--accent-500)' : 'var(--slate-200)'; ?>;border-radius:var(--radius);text-align:center;background:<?php echo $prefillRole === 'owner' ? 'var(--accent-50)' : '#fff'; ?>;transition:var(--transition);">
                                <i class="bi bi-building" style="font-size:1.5rem;color:var(--accent-600);"></i>
                                <div style="font-weight:700;color:var(--slate-900);margin-top:0.25rem;">Owner</div>
                                <small style="color:var(--slate-500);">I want to list</small>
                            </div>
                        </label>
                    </div>
                </div>

                <div style="margin-bottom:1rem;">
                    <label class="form-label-mh">Password</label>
                    <div style="position:relative;">
                        <i class="bi bi-lock" style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--slate-400);z-index:1;"></i>
                        <input type="password" name="password" placeholder="Min 6 characters" required class="form-control-mh" style="padding-left:2.75rem;">
                    </div>
                </div>
                <div style="margin-bottom:1.5rem;">
                    <label class="form-label-mh">Confirm Password</label>
                    <div style="position:relative;">
                        <i class="bi bi-lock" style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--slate-400);z-index:1;"></i>
                        <input type="password" name="confirm_password" placeholder="Re-enter password" required class="form-control-mh" style="padding-left:2.75rem;">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg" style="padding:0.9rem;">
                    Create Account
                </button>
            </form>

            <p style="text-align:center;color:var(--slate-500);margin:1.5rem 0 0;font-size:0.9rem;">
                Already have an account? <a href="<?php echo url('/login.php'); ?>" style="color:var(--primary-600);font-weight:600;">Login here</a>
            </p>
        </div>
    </div>
</div>

<script>
function selectRole(radio) {
    document.querySelectorAll('.role-card-mh').forEach(function(card) {
        card.style.borderColor = 'var(--slate-200)';
        card.style.background = '#fff';
    });
    var card = document.getElementById('role-' + radio.value);
    if (radio.value === 'tenant') {
        card.style.borderColor = 'var(--primary-500)';
        card.style.background = 'var(--primary-50)';
    } else {
        card.style.borderColor = 'var(--accent-500)';
        card.style.background = 'var(--accent-50)';
    }
}
</script>

<?php include __DIR__ . '/includes/footer-minimal.php'; ?>
