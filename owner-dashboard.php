<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$user = requireRole('owner');

$myProperties = get_user_properties($user['id']);
$ownerBookings = get_owner_bookings($user['id']);

$stats = [
    'properties' => count($myProperties),
    'available' => 0,
    'rented' => 0,
    'bookings' => count($ownerBookings),
    'confirmed' => 0,
    'grossEarnings' => 0,
    'commission' => 0,
    'netEarnings' => 0,
];
foreach ($myProperties as $p) {
    if ($p['status'] === 'available') $stats['available']++;
    if ($p['status'] === 'rented') $stats['rented']++;
}
foreach ($ownerBookings as $b) {
    if ($b['status'] === 'confirmed') $stats['confirmed']++;
    if ($b['status'] === 'confirmed' && $b['payment_status'] === 'paid') {
        $stats['grossEarnings'] += (float)$b['total_amount'];
        $stats['commission'] += (float)($b['commission_amount'] ?? 0);
        $stats['netEarnings'] += (float)($b['owner_payout'] ?? 0);
    }
}

include __DIR__ . '/includes/header.php';
?>

<div style="background:linear-gradient(135deg,#0ea5e9,#14b8a6);padding:2.5rem 0;color:#fff;">
    <div class="container-app d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 style="font-size:2rem;font-weight:800;margin:0;letter-spacing:-0.02em;">Owner Dashboard</h1>
            <p style="margin:0.5rem 0 0;opacity:0.95;">Welcome back, <?php echo e($user['name']); ?>!</p>
        </div>
        <a href="<?php echo url('/add-property.php'); ?>" class="btn btn-light" style="background:#fff;color:#0ea5e9;font-weight:700;border:none;border-radius:10px;"><i class="bi bi-plus-circle"></i> Add Property</a>
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
                            <i class="bi bi-building"></i>
                        </div>
                        <div>
                            <div style="font-size:1.75rem;font-weight:800;color:#0f172a;line-height:1;"><?php echo $stats['properties']; ?></div>
                            <small style="color:#64748b;">Total Properties</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:1.5rem;">
                    <div style="display:flex;align-items:center;gap:1rem;">
                        <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#f59e0b,#f59e0b);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.25rem;">
                            <i class="bi bi-patch-minus"></i>
                        </div>
                        <div>
                            <div style="font-size:1.75rem;font-weight:800;color:#0f172a;line-height:1;"><?php echo format_price($stats['commission']); ?></div>
                            <small style="color:#64748b;">Platform Commission</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:1.5rem;">
                    <div style="display:flex;align-items:center;gap:1rem;">
                        <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#10b981,#10b981);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.25rem;">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div>
                            <div style="font-size:1.75rem;font-weight:800;color:#0f172a;line-height:1;"><?php echo $stats['confirmed']; ?></div>
                            <small style="color:#64748b;">Confirmed Bookings</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:1.5rem;">
                    <div style="display:flex;align-items:center;gap:1rem;">
                        <div style="width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#14b8a6,#14b8a6);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.25rem;">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                        <div>
                            <div style="font-size:1.5rem;font-weight:800;color:#0f172a;line-height:1;"><?php echo format_price($stats['netEarnings']); ?></div>
                            <small style="color:#64748b;">Your Net Earnings</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div style="display:flex;gap:0.5rem;margin-bottom:1.5rem;border-bottom:2px solid #e2e8f0;flex-wrap:wrap;">
            <button class="tab-btn active" data-tab="bookings" onclick="switchTab('bookings')"><i class="bi bi-calendar-check"></i> Confirmed Bookings</button>
            <button class="tab-btn" data-tab="properties" onclick="switchTab('properties')"><i class="bi bi-building"></i> My Properties</button>
            <button class="tab-btn" data-tab="earnings" onclick="switchTab('earnings')"><i class="bi bi-graph-up"></i> Earnings</button>
        </div>

        <!-- Booking Requests Tab -->
        <div id="tab-bookings" class="tab-pane active">
            <?php if (empty($ownerBookings)): ?>
                <div class="card" style="text-align:center;padding:3rem 2rem;border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);">
                    <i class="bi bi-calendar" style="font-size:3rem;color:#cbd5e1;"></i>
                    <h3 style="margin-top:1rem;color:#0f172a;">No bookings yet</h3>
                    <p style="color:#64748b;">When tenants book your properties, they will appear here.</p>
                </div>
            <?php else: ?>
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
                                    <th>Commission</th>
                                    <th>Your Payout</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ownerBookings as $b): ?>
                                <tr>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:0.75rem;">
                                            <?php if (!empty($b['primary_image'])): ?>
                                                <img src="<?php echo e(image_url($b['primary_image'])); ?>" alt="" style="width:48px;height:48px;border-radius:8px;object-fit:cover;">
                                            <?php else: ?>
                                                <div style="width:48px;height:48px;border-radius:8px;background:#e2e8f0;display:flex;align-items:center;justify-content:center;color:#94a3b8;"><i class="bi bi-house"></i></div>
                                            <?php endif; ?>
                                            <strong style="color:#0f172a;font-size:0.9rem;"><?php echo e($b['property_title']); ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <strong style="font-size:0.9rem;"><?php echo e($b['tenant_name']); ?></strong><br>
                                        <small style="color:#64748b;"><?php echo e($b['tenant_phone'] ?? ''); ?></small>
                                    </td>
                                    <td style="font-size:0.9rem;"><?php echo formatDate($b['start_date']); ?> - <?php echo formatDate($b['end_date']); ?></td>
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
                                        <?php if ($b['payment_status'] === 'paid'): ?>
                                            <br><small style="color:#10b981;">Paid</small>
                                        <?php elseif ($b['payment_status'] === 'refunded'): ?>
                                            <br><small style="color:#ef4444;">Refunded</small>
                                        <?php elseif ($b['payment_status'] === 'partial_refund'): ?>
                                            <br><small style="color:#f59e0b;">Partial Refund</small>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-size:0.85rem;color:#ef4444;">
                                        <?php if ($b['payment_status'] === 'paid' && $b['status'] === 'confirmed'): ?>
                                            -<?php echo format_price($b['commission_amount'] ?? 0); ?>
                                            <br><small style="color:#94a3b8;">(10% fee)</small>
                                        <?php else: ?>
                                            <span style="color:#94a3b8;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="font-weight:600;color:#10b981;">
                                        <?php if ($b['payment_status'] === 'paid' && $b['status'] === 'confirmed'): ?>
                                            <?php echo format_price($b['owner_payout'] ?? 0); ?>
                                        <?php else: ?>
                                            <span style="color:#94a3b8;">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- My Properties Tab -->
        <div id="tab-properties" class="tab-pane" style="display:none;">
            <?php if (empty($myProperties)): ?>
                <div class="card" style="text-align:center;padding:3rem 2rem;border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);">
                    <i class="bi bi-building" style="font-size:3rem;color:#cbd5e1;"></i>
                    <h3 style="margin-top:1rem;color:#0f172a;">No properties yet</h3>
                    <p style="color:#64748b;">Start listing your properties to receive bookings.</p>
                    <a href="<?php echo url('/add-property.php'); ?>" class="btn btn-primary" style="margin-top:1rem;"><i class="bi bi-plus-circle"></i> Add Your First Property</a>
                </div>
            <?php else: ?>
                <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);overflow:hidden;">
                    <div style="overflow-x:auto;">
                        <table class="table table-hover mb-0" style="margin:0;">
                            <thead style="background:#f8fafc;">
                                <tr>
                                    <th>Property</th>
                                    <th>Type</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($myProperties as $p): ?>
                                <tr>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:0.75rem;">
                                            <?php if (!empty($p['primary_image'])): ?>
                                                <img src="<?php echo e(image_url($p['primary_image'])); ?>" alt="" style="width:48px;height:48px;border-radius:8px;object-fit:cover;">
                                            <?php else: ?>
                                                <div style="width:48px;height:48px;border-radius:8px;background:#e2e8f0;display:flex;align-items:center;justify-content:center;color:#94a3b8;"><i class="bi bi-house"></i></div>
                                            <?php endif; ?>
                                            <div>
                                                <strong style="color:#0f172a;font-size:0.9rem;"><?php echo e($p['title']); ?></strong><br>
                                                <small style="color:#64748b;"><?php echo e($p['city']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="font-size:0.9rem;"><?php echo e(get_property_type_label($p['property_type'])); ?></td>
                                    <td style="font-weight:600;color:#0f172a;"><?php echo format_price($p['price']); ?><?php if ($p['price_period'] === 'per_day'): ?><small style="color:#64748b;"> /day</small><?php else: ?><small style="color:#64748b;"> /mo</small><?php endif; ?></td>
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
                                        <a href="<?php echo url('/edit-property.php?id=' . (int)$p['id']); ?>" class="btn btn-ghost btn-sm" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <a href="<?php echo url('/api/delete-property.php?id=' . (int)$p['id']); ?>" class="btn btn-error btn-sm" title="Delete" onclick="return confirm('Delete this property?')"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Earnings Tab -->
        <div id="tab-earnings" class="tab-pane" style="display:none;">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:2rem;">
                        <h4 style="color:#0f172a;font-weight:700;margin:0 0 1rem;"><i class="bi bi-cash-coin" style="color:#14b8a6;"></i> Earnings Summary</h4>
                        <div style="display:flex;justify-content:space-between;padding:0.75rem 0;border-bottom:1px solid #e2e8f0;">
                            <span style="color:#64748b;">Gross Earnings (Paid)</span>
                            <strong style="color:#0f172a;"><?php echo format_price($stats['grossEarnings']); ?></strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:0.75rem 0;border-bottom:1px solid #e2e8f0;">
                            <span style="color:#ef4444;">Platform Commission (10%)</span>
                            <strong style="color:#ef4444;">- <?php echo format_price($stats['commission']); ?></strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:0.75rem 0;border-bottom:1px solid #e2e8f0;">
                            <span style="color:#0f172a;font-weight:700;">Your Net Earnings</span>
                            <strong style="color:#10b981;font-size:1.1rem;"><?php echo format_price($stats['netEarnings']); ?></strong>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:0.75rem 0;">
                            <span style="color:#64748b;">Confirmed Bookings</span>
                            <strong style="color:#0f172a;"><?php echo $stats['confirmed']; ?></strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:2rem;">
                        <h4 style="color:#0f172a;font-weight:700;margin:0 0 1rem;"><i class="bi bi-receipt" style="color:#0ea5e9;"></i> Recent Paid Bookings</h4>
                        <?php
                        $paidBookings = array_filter($ownerBookings, function($b) { return $b['payment_status'] === 'paid'; });
                        $paidBookings = array_slice($paidBookings, 0, 5);
                        ?>
                        <?php if (empty($paidBookings)): ?>
                            <p style="color:#64748b;text-align:center;padding:1.5rem 0;">No paid bookings yet.</p>
                        <?php else: ?>
                            <?php foreach ($paidBookings as $b): ?>
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem 0;border-bottom:1px solid #e2e8f0;">
                                <div>
                                    <strong style="font-size:0.9rem;color:#0f172a;"><?php echo e($b['property_title']); ?></strong><br>
                                    <small style="color:#64748b;"><?php echo e($b['tenant_name']); ?></small>
                                </div>
                                <strong style="color:#10b981;"><?php echo format_price($b['total_amount']); ?></strong>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
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
