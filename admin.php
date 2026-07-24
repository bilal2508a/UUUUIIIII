<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$user = requireRole('admin');

// Stats
$stats = [
    'users' => 0,
    'properties' => 0,
    'bookings' => 0,
    'revenue' => 0,
    'owners' => 0,
    'tenants' => 0,
    'pending_bookings' => 0,
    'available_properties' => 0,
];
try {
    $stats['users'] = (int)db()->query("SELECT COUNT(*) as c FROM users")->fetch()['c'];
    $stats['owners'] = (int)db()->query("SELECT COUNT(*) as c FROM users WHERE role = 'owner'")->fetch()['c'];
    $stats['tenants'] = (int)db()->query("SELECT COUNT(*) as c FROM users WHERE role = 'tenant'")->fetch()['c'];
    $stats['properties'] = (int)db()->query("SELECT COUNT(*) as c FROM properties")->fetch()['c'];
    $stats['available_properties'] = (int)db()->query("SELECT COUNT(*) as c FROM properties WHERE status = 'available'")->fetch()['c'];
    $stats['bookings'] = (int)db()->query("SELECT COUNT(*) as c FROM bookings")->fetch()['c'];
    $stats['pending_bookings'] = (int)db()->query("SELECT COUNT(*) as c FROM bookings WHERE status = 'pending'")->fetch()['c'];
    $revRow = db()->query("SELECT COALESCE(SUM(total_amount), 0) as r FROM bookings WHERE payment_status = 'paid'")->fetch();
    $stats['revenue'] = (float)$revRow['r'];
} catch (Exception $e) {}

// Fetch data for tabs
$users = [];
$properties = [];
$bookings = [];
try {
    $users = db()->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 50")->fetchAll();
} catch (Exception $e) {}
try {
    $properties = db()->query("SELECT p.*, u.name as owner_name FROM properties p JOIN users u ON p.owner_id = u.id ORDER BY p.created_at DESC LIMIT 50")->fetchAll();
} catch (Exception $e) {}
try {
    $bookings = db()->query("SELECT b.*, p.title as property_title, u.name as tenant_name FROM bookings b JOIN properties p ON b.property_id = p.id JOIN users u ON b.tenant_id = u.id ORDER BY b.created_at DESC LIMIT 50")->fetchAll();
} catch (Exception $e) {}

$activeTab = $_GET['tab'] ?? 'overview';

include __DIR__ . '/includes/header.php';
?>

<div style="background:linear-gradient(135deg,#0ea5e9,#14b8a6);padding:2.5rem 0;color:#fff;">
    <div class="container-app">
        <h1 style="font-size:2rem;font-weight:800;margin:0;letter-spacing:-0.02em;">Admin Dashboard</h1>
        <p style="margin:0.5rem 0 0;opacity:0.95;">Welcome back, <?php echo e($user['name']); ?>! System overview and management</p>
    </div>
</div>

<section style="padding:2.5rem 0;">
    <div class="container-app">
        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:1.5rem;">
                    <div style="display:flex;align-items:center;gap:1rem;">
                        <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#0ea5e9,#0ea5e9);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.25rem;">
                            <i class="bi bi-people"></i>
                        </div>
                        <div>
                            <div style="font-size:1.75rem;font-weight:800;color:#0f172a;line-height:1;"><?php echo $stats['users']; ?></div>
                            <small style="color:#64748b;">Total Users</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:1.5rem;">
                    <div style="display:flex;align-items:center;gap:1rem;">
                        <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#14b8a6,#14b8a6);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.25rem;">
                            <i class="bi bi-building"></i>
                        </div>
                        <div>
                            <div style="font-size:1.75rem;font-weight:800;color:#0f172a;line-height:1;"><?php echo $stats['properties']; ?></div>
                            <small style="color:#64748b;">Properties</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:1.5rem;">
                    <div style="display:flex;align-items:center;gap:1rem;">
                        <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#f59e0b,#f59e0b);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.25rem;">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div>
                            <div style="font-size:1.75rem;font-weight:800;color:#0f172a;line-height:1;"><?php echo $stats['bookings']; ?></div>
                            <small style="color:#64748b;">Bookings</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:1.5rem;">
                    <div style="display:flex;align-items:center;gap:1rem;">
                        <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#10b981,#10b981);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.25rem;">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                        <div>
                            <div style="font-size:1.5rem;font-weight:800;color:#0f172a;line-height:1;"><?php echo format_price($stats['revenue']); ?></div>
                            <small style="color:#64748b;">Revenue</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div style="display:flex;gap:0.5rem;margin-bottom:1.5rem;border-bottom:2px solid #e2e8f0;flex-wrap:wrap;">
            <button class="tab-btn <?php echo $activeTab === 'overview' ? 'active' : ''; ?>" onclick="switchTab('overview')"><i class="bi bi-graph-up"></i> Overview</button>
            <button class="tab-btn <?php echo $activeTab === 'users' ? 'active' : ''; ?>" onclick="switchTab('users')"><i class="bi bi-people"></i> Users</button>
            <button class="tab-btn <?php echo $activeTab === 'properties' ? 'active' : ''; ?>" onclick="switchTab('properties')"><i class="bi bi-building"></i> Properties</button>
            <button class="tab-btn <?php echo $activeTab === 'bookings' ? 'active' : ''; ?>" onclick="switchTab('bookings')"><i class="bi bi-calendar-check"></i> Bookings</button>
        </div>

        <!-- Overview Tab -->
        <div id="tab-overview" class="tab-pane" style="<?php echo $activeTab !== 'overview' ? 'display:none;' : ''; ?>">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:2rem;">
                        <h4 style="color:#0f172a;font-weight:700;margin:0 0 1rem;"><i class="bi bi-people" style="color:#0ea5e9;"></i> User Breakdown</h4>
                        <div style="display:flex;justify-content:space-between;padding:0.75rem 0;border-bottom:1px solid #e2e8f0;">
                            <span style="color:#64748b;">Owners</span>
                            <strong style="color:#0f172a;"><?php echo $stats['owners']; ?></strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:0.75rem 0;border-bottom:1px solid #e2e8f0;">
                            <span style="color:#64748b;">Tenants</span>
                            <strong style="color:#0f172a;"><?php echo $stats['tenants']; ?></strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:0.75rem 0;">
                            <span style="color:#64748b;">Total Users</span>
                            <strong style="color:#0f172a;"><?php echo $stats['users']; ?></strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:2rem;">
                        <h4 style="color:#0f172a;font-weight:700;margin:0 0 1rem;"><i class="bi bi-building" style="color:#14b8a6;"></i> Property Status</h4>
                        <div style="display:flex;justify-content:space-between;padding:0.75rem 0;border-bottom:1px solid #e2e8f0;">
                            <span style="color:#64748b;">Available</span>
                            <strong style="color:#0f172a;"><?php echo $stats['available_properties']; ?></strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:0.75rem 0;border-bottom:1px solid #e2e8f0;">
                            <span style="color:#64748b;">Total Properties</span>
                            <strong style="color:#0f172a;"><?php echo $stats['properties']; ?></strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:0.75rem 0;">
                            <span style="color:#64748b;">Pending Bookings</span>
                            <strong style="color:#0f172a;"><?php echo $stats['pending_bookings']; ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Tab -->
        <div id="tab-users" class="tab-pane" style="<?php echo $activeTab !== 'users' ? 'display:none;' : ''; ?>">
            <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);overflow:hidden;">
                <div style="overflow-x:auto;">
                    <table class="table table-hover mb-0" style="margin:0;">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                            <tr><td colspan="5" style="text-align:center;color:#64748b;padding:2rem;">No users found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><strong style="color:#0f172a;"><?php echo e($u['name']); ?></strong></td>
                                    <td style="color:#64748b;"><?php echo e($u['email']); ?></td>
                                    <td style="color:#64748b;"><?php echo e($u['phone'] ?: '—'); ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = 'badge-secondary';
                                        if ($u['role'] === 'admin') $badgeClass = 'badge-error';
                                        elseif ($u['role'] === 'owner') $badgeClass = 'badge-info';
                                        elseif ($u['role'] === 'tenant') $badgeClass = 'badge-success';
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst(e($u['role'])); ?></span>
                                    </td>
                                    <td style="color:#64748b;font-size:0.85rem;"><?php echo formatDate($u['created_at']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Properties Tab -->
        <div id="tab-properties" class="tab-pane" style="<?php echo $activeTab !== 'properties' ? 'display:none;' : ''; ?>">
            <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);overflow:hidden;">
                <div style="overflow-x:auto;">
                    <table class="table table-hover mb-0" style="margin:0;">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th>Property</th>
                                <th>Owner</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($properties)): ?>
                            <tr><td colspan="6" style="text-align:center;color:#64748b;padding:2rem;">No properties found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($properties as $p): ?>
                                <tr>
                                    <td>
                                        <strong style="color:#0f172a;font-size:0.9rem;"><?php echo e($p['title']); ?></strong><br>
                                        <small style="color:#64748b;"><?php echo e($p['city']); ?></small>
                                    </td>
                                    <td style="color:#64748b;font-size:0.9rem;"><?php echo e($p['owner_name']); ?></td>
                                    <td style="font-size:0.9rem;"><?php echo e(get_property_type_label($p['property_type'])); ?></td>
                                    <td style="font-weight:600;color:#0f172a;"><?php echo format_price($p['price']); ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = 'badge-secondary';
                                        if ($p['status'] === 'available') $badgeClass = 'badge-success';
                                        elseif ($p['status'] === 'rented') $badgeClass = 'badge-warning';
                                        elseif ($p['status'] === 'inactive') $badgeClass = 'badge-secondary';
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst(e($p['status'])); ?></span>
                                    </td>
                                    <td>
                                        <a href="<?php echo url('/property-details.php?id=' . (int)$p['id']); ?>" class="btn btn-ghost btn-sm" title="View"><i class="bi bi-eye"></i></a>
                                        <a href="<?php echo url('/api/delete-property.php?id=' . (int)$p['id']); ?>" class="btn btn-error btn-sm" title="Delete" onclick="return confirm('Delete this property?')"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Bookings Tab -->
        <div id="tab-bookings" class="tab-pane" style="<?php echo $activeTab !== 'bookings' ? 'display:none;' : ''; ?>">
            <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);overflow:hidden;">
                <div style="overflow-x:auto;">
                    <table class="table table-hover mb-0" style="margin:0;">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th>Property</th>
                                <th>Tenant</th>
                                <th>Dates</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bookings)): ?>
                            <tr><td colspan="6" style="text-align:center;color:#64748b;padding:2rem;">No bookings found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($bookings as $b): ?>
                                <tr>
                                    <td><strong style="color:#0f172a;font-size:0.9rem;"><?php echo e($b['property_title']); ?></strong></td>
                                    <td style="color:#64748b;font-size:0.9rem;"><?php echo e($b['tenant_name']); ?></td>
                                    <td style="font-size:0.85rem;"><?php echo formatDate($b['start_date']); ?> - <?php echo formatDate($b['end_date']); ?></td>
                                    <td style="font-weight:600;color:#0f172a;"><?php echo format_price($b['total_amount']); ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = 'badge-secondary';
                                        if ($b['status'] === 'pending') $badgeClass = 'badge-warning';
                                        elseif ($b['status'] === 'confirmed') $badgeClass = 'badge-success';
                                        elseif ($b['status'] === 'cancelled') $badgeClass = 'badge-error';
                                        elseif ($b['status'] === 'completed') $badgeClass = 'badge-info';
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst(e($b['status'])); ?></span>
                                    </td>
                                    <td>
                                        <?php $payBadge = $b['payment_status'] === 'paid' ? 'badge-success' : 'badge-error'; ?>
                                        <span class="badge <?php echo $payBadge; ?>"><?php echo ucfirst(e($b['payment_status'])); ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
    event.target.closest('.tab-btn').classList.add('active');
    document.getElementById('tab-' + tab).style.display = 'block';
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
