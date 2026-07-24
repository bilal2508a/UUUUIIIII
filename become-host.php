<?php
// Mehmaan Hub - Become a Host
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$user = requireLogin();

// If already an owner or admin, redirect to owner dashboard
if ($user['role'] === 'owner' || $user['role'] === 'admin') {
    redirect('owner-dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = db()->prepare("UPDATE users SET role = 'owner' WHERE id = ?");
        $stmt->execute([$user['id']]);

        // Update the cached user
        $_SESSION['user_id'] = $user['id'];

        flash('success', 'You are now a host! Start listing your properties.');
        redirect('owner-dashboard.php');
    } catch (PDOException $ex) {
        flash('error', 'Something went wrong. Please try again.');
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="container-app py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <!-- Hero Section -->
            <div class="card mb-4 gradient-hero" style="border:none;">
                <div class="card-body p-5 text-center text-white">
                    <div style="font-size:3rem;margin-bottom:1rem;">
                        <i class="bi bi-key-fill"></i>
                    </div>
                    <h1 style="font-size:2.25rem;font-weight:800;margin-bottom:0.5rem;">Become a Host</h1>
                    <p style="font-size:1.1rem;opacity:0.95;margin-bottom:0;max-width:500px;margin-left:auto;margin-right:auto;">
                        Turn your property into income. Join thousands of hosts across Pakistan earning with Mehmaan Hub.
                    </p>
                </div>
            </div>

            <!-- Benefits Grid -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div class="d-flex align-items-center justify-content-center" style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#0ea5e9,#14b8a6);color:#fff;font-size:1.5rem;flex-shrink:0;">
                                    <i class="bi bi-cash-stack"></i>
                                </div>
                                <h5 style="font-weight:700;margin:0;color:#0f172a;">Earn Extra Income</h5>
                            </div>
                            <p style="color:#64748b;margin:0;">List your property for daily or monthly rent and start earning. Set your own prices and availability.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div class="d-flex align-items-center justify-content-center" style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#0ea5e9,#14b8a6);color:#fff;font-size:1.5rem;flex-shrink:0;">
                                    <i class="bi bi-people"></i>
                                </div>
                                <h5 style="font-weight:700;margin:0;color:#0f172a;">Reach Thousands</h5>
                            </div>
                            <p style="color:#64748b;margin:0;">Connect with verified tenants across Pakistan looking for their perfect stay.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div class="d-flex align-items-center justify-content-center" style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#0ea5e9,#14b8a6);color:#fff;font-size:1.5rem;flex-shrink:0;">
                                    <i class="bi bi-shield-check"></i>
                                </div>
                                <h5 style="font-weight:700;margin:0;color:#0f172a;">Secure & Trusted</h5>
                            </div>
                            <p style="color:#64748b;margin:0;">Our platform verifies users and manages bookings securely. You're always in control.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div class="d-flex align-items-center justify-content-center" style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#0ea5e9,#14b8a6);color:#fff;font-size:1.5rem;flex-shrink:0;">
                                    <i class="bi bi-graph-up"></i>
                                </div>
                                <h5 style="font-weight:700;margin:0;color:#0f172a;">Easy Dashboard</h5>
                            </div>
                            <p style="color:#64748b;margin:0;">Manage all your properties, bookings, and earnings from one simple dashboard.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- How It Works -->
            <div class="card mb-4">
                <div class="card-header-mh">
                    <h5 class="mb-0"><i class="bi bi-list-ol" style="color:#0ea5e9;"></i> How It Works</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;border-radius:50%;background:#f0f9ff;color:#0ea5e9;font-size:1.5rem;font-weight:800;">1</div>
                                <h6 style="font-weight:700;color:#0f172a;">Upgrade Your Account</h6>
                                <p style="color:#64748b;font-size:0.9rem;margin:0;">Click the button below to become a host instantly.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;border-radius:50%;background:#f0f9ff;color:#0ea5e9;font-size:1.5rem;font-weight:800;">2</div>
                                <h6 style="font-weight:700;color:#0f172a;">List Your Property</h6>
                                <p style="color:#64748b;font-size:0.9rem;margin:0;">Add photos, details, pricing, and amenities.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;border-radius:50%;background:#f0f9ff;color:#0ea5e9;font-size:1.5rem;font-weight:800;">3</div>
                                <h6 style="font-weight:700;color:#0f172a;">Start Earning</h6>
                                <p style="color:#64748b;font-size:0.9rem;margin:0;">Receive booking requests and manage your rentals.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upgrade CTA -->
            <div class="card">
                <div class="card-body p-5 text-center">
                    <h3 style="font-weight:800;color:#0f172a;margin-bottom:0.5rem;">Ready to Get Started?</h3>
                    <p style="color:#64748b;margin-bottom:2rem;">Upgrade your account now and start listing your properties today. It's free!</p>
                    <form method="POST" action="<?php echo url('/become-host.php'); ?>">
                        <button type="submit" class="btn btn-primary btn-lg" style="padding:0.75rem 2.5rem;">
                            <i class="bi bi-key-fill"></i> Become a Host Now
                        </button>
                    </form>
                    <p style="color:#94a3b8;font-size:0.85rem;margin-top:1rem;margin-bottom:0;">
                        <i class="bi bi-info-circle"></i> You can always go back to being a tenant by contacting support.
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
