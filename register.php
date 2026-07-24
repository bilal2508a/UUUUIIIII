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
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'tenant';

    if (!$name || !$email || !$password || !$username) {
        flash('error', 'Please fill in all required fields.');
    } elseif ($password !== $confirm) {
        flash('error', 'Passwords do not match.');
    } elseif (strlen($password) < 6) {
        flash('error', 'Password must be at least 6 characters.');
    } elseif (!in_array($role, ['tenant', 'owner'])) {
        flash('error', 'Invalid account type.');
    } elseif (!signUp($name, $email, $password, $role, $phone, $username)) {
        flash('error', 'Email or username already registered. Please login.');
    } else {
        flash('success', 'Account created successfully! Welcome to Mehmaan Hub.');
        redirect(dashboardUrlForRole($role));
    }
    redirect('/register.php');
}

include __DIR__ . '/includes/header-minimal.php';
?>

<div class="auth-wrapper">
    <!-- Image Panel -->
    <div class="auth-panel-image">
        <img src="https://images.pexels.com/photos/1571460/pexels-photo-1571460.jpeg?auto=compress&cs=tinysrgb&w=1200" alt="Beautiful property">
        <div class="auth-panel-image-content">
            <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:1.25rem;">
                <div style="width:44px;height:44px;border-radius:12px;background:rgba(255,255,255,0.2);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,0.3);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.35rem;">
                    <i class="bi bi-buildings"></i>
                </div>
                <span style="font-size:1.35rem;font-weight:800;color:#fff;letter-spacing:-0.02em;">Mehmaan<span style="color:var(--gold-300);">Hub</span></span>
            </div>
            <h2 style="font-size:2rem;font-weight:800;color:#fff;line-height:1.2;margin:0 0 0.5rem;letter-spacing:-0.02em;">Find your next<br>home with us.</h2>
            <p style="font-size:1rem;color:rgba(255,255,255,0.85);margin:0;max-width:400px;line-height:1.5;">Join thousands of tenants and owners on Pakistan's premier rental platform.</p>
            <div style="display:flex;flex-direction:column;gap:0.625rem;margin-top:1.5rem;">
                <div style="display:flex;align-items:center;gap:0.625rem;color:rgba(255,255,255,0.9);font-size:0.9rem;">
                    <div style="width:24px;height:24px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:0.7rem;"><i class="bi bi-check" style="font-weight:bold;"></i></div>
                    Verified property listings
                </div>
                <div style="display:flex;align-items:center;gap:0.625rem;color:rgba(255,255,255,0.9);font-size:0.9rem;">
                    <div style="width:24px;height:24px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:0.7rem;"><i class="bi bi-check" style="font-weight:bold;"></i></div>
                    Direct contact with owners
                </div>
                <div style="display:flex;align-items:center;gap:0.625rem;color:rgba(255,255,255,0.9);font-size:0.9rem;">
                    <div style="width:24px;height:24px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:0.7rem;"><i class="bi bi-check" style="font-weight:bold;"></i></div>
                    Free to join, no hidden fees
                </div>
            </div>
        </div>
    </div>

    <!-- Form Panel -->
    <div class="auth-panel-form" style="padding:1.25rem 1rem;">
        <div style="width:100%;max-width:480px;animation:fadeInUp 0.5s ease;">
            <!-- Mobile Logo -->
            <div style="text-align:center;margin-bottom:1rem;" class="d-lg-none">
                <div style="display:inline-flex;align-items:center;gap:0.5rem;">
                    <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,var(--primary-600),var(--accent-500));color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.35rem;box-shadow:0 8px 20px -4px rgba(26,82,245,0.4);">
                        <i class="bi bi-buildings"></i>
                    </div>
                    <span style="font-size:1.35rem;font-weight:800;color:var(--slate-900);">Mehmaan<span style="background:linear-gradient(135deg,var(--primary-600),var(--accent-500));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Hub</span></span>
                </div>
            </div>

            <h1 style="font-size:1.4rem;font-weight:800;color:var(--slate-900);margin:0 0 0.25rem;letter-spacing:-0.02em;">Create Account</h1>
            <p style="color:var(--slate-500);margin:0 0 1rem;font-size:0.85rem;">Join Mehmaan Hub to find or list properties</p>

            <form method="POST" action="<?php echo url('/register.php'); ?>">
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label-mh" style="font-size:0.8rem;">Full Name <span style="color:var(--error-500);">*</span></label>
                        <div style="position:relative;">
                            <i class="bi bi-person" style="position:absolute;left:0.875rem;top:50%;transform:translateY(-50%);color:var(--slate-400);z-index:1;font-size:0.9rem;"></i>
                            <input type="text" name="name" placeholder="Full name" required class="form-control-mh" style="padding-left:2.5rem;font-size:0.9rem;height:42px;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-mh" style="font-size:0.8rem;">Username <span style="color:var(--error-500);">*</span></label>
                        <div style="position:relative;">
                            <i class="bi bi-person-badge" style="position:absolute;left:0.875rem;top:50%;transform:translateY(-50%);color:var(--slate-400);z-index:1;font-size:0.9rem;"></i>
                            <input type="text" name="username" placeholder="Choose a username" required class="form-control-mh" style="padding-left:2.5rem;font-size:0.9rem;height:42px;">
                        </div>
                    </div>
                </div>
                <div class="row g-2 mt-0">
                    <div class="col-md-6">
                        <label class="form-label-mh" style="font-size:0.8rem;">Email <span style="color:var(--error-500);">*</span></label>
                        <div style="position:relative;">
                            <i class="bi bi-envelope" style="position:absolute;left:0.875rem;top:50%;transform:translateY(-50%);color:var(--slate-400);z-index:1;font-size:0.9rem;"></i>
                            <input type="email" name="email" placeholder="you@example.com" required class="form-control-mh" style="padding-left:2.5rem;font-size:0.9rem;height:42px;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-mh" style="font-size:0.8rem;">Phone <span style="color:var(--slate-400);font-weight:400;">(optional)</span></label>
                        <div style="position:relative;">
                            <i class="bi bi-telephone" style="position:absolute;left:0.875rem;top:50%;transform:translateY(-50%);color:var(--slate-400);z-index:1;font-size:0.9rem;"></i>
                            <input type="tel" name="phone" placeholder="03XX-XXXXXXX" maxlength="11" pattern="[0-9]{11}" class="form-control-mh" style="padding-left:2.5rem;font-size:0.9rem;height:42px;">
                        </div>
                    </div>
                </div>

                <!-- Role Selector -->
                <div style="margin-top:0.75rem;">
                    <label class="form-label-mh" style="font-size:0.8rem;">Account Type</label>
                    <div style="display:flex;gap:0.625rem;">
                        <label style="flex:1;cursor:pointer;">
                            <input type="radio" name="role" value="tenant" <?php echo $prefillRole === 'tenant' ? 'checked' : ''; ?> style="display:none;" onchange="selectRole(this)">
                            <div class="role-card-mh" id="role-tenant" style="padding:0.625rem;border:2px solid <?php echo $prefillRole === 'tenant' ? 'var(--primary-500)' : 'var(--slate-200)'; ?>;border-radius:var(--radius);text-align:center;background:<?php echo $prefillRole === 'tenant' ? 'var(--primary-50)' : '#fff'; ?>;transition:var(--transition);">
                                <i class="bi bi-person" style="font-size:1.1rem;color:var(--primary-600);"></i>
                                <div style="font-weight:700;color:var(--slate-900);font-size:0.85rem;">Tenant</div>
                                <small style="color:var(--slate-500);font-size:0.72rem;">I want to rent</small>
                            </div>
                        </label>
                        <label style="flex:1;cursor:pointer;">
                            <input type="radio" name="role" value="owner" <?php echo $prefillRole === 'owner' ? 'checked' : ''; ?> style="display:none;" onchange="selectRole(this)">
                            <div class="role-card-mh" id="role-owner" style="padding:0.625rem;border:2px solid <?php echo $prefillRole === 'owner' ? 'var(--accent-500)' : 'var(--slate-200)'; ?>;border-radius:var(--radius);text-align:center;background:<?php echo $prefillRole === 'owner' ? 'var(--accent-50)' : '#fff'; ?>;transition:var(--transition);">
                                <i class="bi bi-building" style="font-size:1.1rem;color:var(--accent-600);"></i>
                                <div style="font-weight:700;color:var(--slate-900);font-size:0.85rem;">Owner</div>
                                <small style="color:var(--slate-500);font-size:0.72rem;">I want to list</small>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="row g-2 mt-0">
                    <div class="col-md-6">
                        <label class="form-label-mh" style="font-size:0.8rem;">Password <span style="color:var(--error-500);">*</span></label>
                        <div style="position:relative;">
                            <i class="bi bi-lock" style="position:absolute;left:0.875rem;top:50%;transform:translateY(-50%);color:var(--slate-400);z-index:1;font-size:0.9rem;"></i>
                            <input type="password" name="password" id="regPassword" placeholder="Min 6 chars" required class="form-control-mh" style="padding-left:2.5rem;padding-right:2.5rem;font-size:0.9rem;height:42px;">
                            <button type="button" onclick="togglePassword('regPassword', this)" style="position:absolute;right:0.5rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--slate-400);cursor:pointer;padding:0.25rem;z-index:2;">
                                <i class="bi bi-eye" style="font-size:0.85rem;"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-mh" style="font-size:0.8rem;">Confirm Password <span style="color:var(--error-500);">*</span></label>
                        <div style="position:relative;">
                            <i class="bi bi-lock" style="position:absolute;left:0.875rem;top:50%;transform:translateY(-50%);color:var(--slate-400);z-index:1;font-size:0.9rem;"></i>
                            <input type="password" name="confirm_password" id="regConfirmPassword" placeholder="Re-enter" required class="form-control-mh" style="padding-left:2.5rem;padding-right:2.5rem;font-size:0.9rem;height:42px;">
                            <button type="button" onclick="togglePassword('regConfirmPassword', this)" style="position:absolute;right:0.5rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--slate-400);cursor:pointer;padding:0.25rem;z-index:2;">
                                <i class="bi bi-eye" style="font-size:0.85rem;"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg" style="padding:0.7rem;margin-top:1rem;">
                    Create Account
                </button>
            </form>

            <p style="text-align:center;color:var(--slate-500);margin:0.75rem 0 0;font-size:0.85rem;">
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
