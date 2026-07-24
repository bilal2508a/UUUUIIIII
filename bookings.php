<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$user = requireLogin();

$myBookings = get_user_bookings($user['id']);

$upcoming = [];
$completed = [];
$cancelled = [];
foreach ($myBookings as $b) {
    if (in_array($b['status'], ['pending', 'confirmed'])) $upcoming[] = $b;
    elseif ($b['status'] === 'completed') $completed[] = $b;
    elseif ($b['status'] === 'cancelled') $cancelled[] = $b;
}

$activeTab = $_GET['tab'] ?? 'upcoming';

include __DIR__ . '/includes/header.php';
?>

<div style="background:linear-gradient(135deg,#0ea5e9,#14b8a6);padding:2.5rem 0;color:#fff;">
    <div class="container-app">
        <h1 style="font-size:2rem;font-weight:800;margin:0;letter-spacing:-0.02em;">My Bookings</h1>
        <p style="margin:0.5rem 0 0;opacity:0.95;">Manage your trips and reservations</p>
    </div>
</div>

<section style="padding:2.5rem 0;">
    <div class="container-app">
        <!-- Tabs -->
        <div style="display:flex;gap:0.5rem;margin-bottom:1.5rem;border-bottom:2px solid #e2e8f0;flex-wrap:wrap;">
            <button class="tab-btn <?php echo $activeTab === 'upcoming' ? 'active' : ''; ?>" onclick="switchTab('upcoming')"><i class="bi bi-clock"></i> Upcoming <span class="badge badge-secondary" style="margin-left:0.25rem;"><?php echo count($upcoming); ?></span></button>
            <button class="tab-btn <?php echo $activeTab === 'completed' ? 'active' : ''; ?>" onclick="switchTab('completed')"><i class="bi bi-check-circle"></i> Completed <span class="badge badge-secondary" style="margin-left:0.25rem;"><?php echo count($completed); ?></span></button>
            <button class="tab-btn <?php echo $activeTab === 'cancelled' ? 'active' : ''; ?>" onclick="switchTab('cancelled')"><i class="bi bi-x-circle"></i> Cancelled <span class="badge badge-secondary" style="margin-left:0.25rem;"><?php echo count($cancelled); ?></span></button>
        </div>

        <!-- Upcoming -->
        <div id="tab-upcoming" class="tab-pane" style="<?php echo $activeTab !== 'upcoming' ? 'display:none;' : ''; ?>">
            <?php if (empty($upcoming)): ?>
                <div class="card" style="text-align:center;padding:3rem 2rem;border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);">
                    <i class="bi bi-calendar" style="font-size:3rem;color:#cbd5e1;"></i>
                    <h3 style="margin-top:1rem;color:#0f172a;">No upcoming bookings</h3>
                    <p style="color:#64748b;">Browse properties and book your next stay.</p>
                    <a href="<?php echo url('/properties.php'); ?>" class="btn btn-primary" style="margin-top:1rem;"><i class="bi bi-search"></i> Browse Properties</a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($upcoming as $b): ?>
                        <div class="col-md-6">
                            <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);overflow:hidden;">
                                <div style="display:flex;">
                                    <?php if (!empty($b['primary_image'])): ?>
                                        <img src="<?php echo e(image_url($b['primary_image'])); ?>" alt="" style="width:140px;height:140px;object-fit:cover;">
                                    <?php else: ?>
                                        <div style="width:140px;height:140px;background:#e2e8f0;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:2rem;"><i class="bi bi-house"></i></div>
                                    <?php endif; ?>
                                    <div style="flex:1;padding:1.25rem;">
                                        <a href="<?php echo url('/property-details.php?id=' . (int)$b['property_id']); ?>" style="color:#0f172a;font-weight:700;text-decoration:none;font-size:1rem;"><?php echo e($b['property_title']); ?></a>
                                        <div style="color:#64748b;font-size:0.85rem;margin-top:0.25rem;"><i class="bi bi-geo-alt"></i> <?php echo e($b['property_city'] ?? ''); ?></div>
                                        <div style="color:#64748b;font-size:0.85rem;margin-top:0.5rem;"><i class="bi bi-calendar"></i> <?php echo formatDate($b['start_date']); ?> - <?php echo formatDate($b['end_date']); ?></div>
                                        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:0.75rem;">
                                            <strong style="color:#0f172a;"><?php echo format_price($b['total_amount']); ?></strong>
                                            <?php
                                            $badgeClass = $b['status'] === 'confirmed' ? 'badge-success' : 'badge-warning';
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst(e($b['status'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div style="padding:0.75rem 1.25rem;background:#f8fafc;display:flex;justify-content:space-between;align-items:center;">
                                    <?php if ($b['payment_status'] === 'unpaid'): ?>
                                        <span class="badge badge-error">Unpaid</span>
                                        <a href="<?php echo url('/payment.php?id=' . (int)$b['id']); ?>" class="btn btn-primary btn-sm"><i class="bi bi-credit-card"></i> Pay Now</a>
                                    <?php elseif ($b['status'] === 'confirmed' && $b['payment_status'] === 'paid'): ?>
                                        <span class="badge badge-success">Paid & Confirmed</span>
                                        <form method="POST" action="<?php echo url('/api/cancel-booking.php'); ?>" onsubmit="return confirm('Cancel this booking? Full refund if before check-in day, 50% refund on check-in day.');" style="display:inline;">
                                            <input type="hidden" name="booking_id" value="<?php echo (int)$b['id']; ?>">
                                            <button type="submit" class="btn btn-error btn-sm"><i class="bi bi-x-circle"></i> Cancel Booking</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="badge badge-success">Paid</span>
                                        <a href="<?php echo url('/property-details.php?id=' . (int)$b['property_id']); ?>" class="btn btn-ghost btn-sm">View Property</a>
                                    <?php endif; ?>
                                </div>
                                <?php if ($b['status'] === 'confirmed' && $b['payment_status'] === 'paid'): ?>
                                <div style="padding:0.5rem 1.25rem;background:var(--primary-50);border-top:1px solid var(--primary-100);font-size:0.78rem;color:var(--slate-600);">
                                    <i class="bi bi-shield-check" style="color:var(--primary-600);"></i>
                                    Free cancellation until <?php echo formatDate(date('Y-m-d', strtotime($b['start_date'] . ' -1 day'))); ?>. 50% refund on check-in day.
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Completed -->
        <div id="tab-completed" class="tab-pane" style="<?php echo $activeTab !== 'completed' ? 'display:none;' : ''; ?>">
            <?php if (empty($completed)): ?>
                <div class="card" style="text-align:center;padding:3rem 2rem;border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);">
                    <i class="bi bi-check-circle" style="font-size:3rem;color:#cbd5e1;"></i>
                    <h3 style="margin-top:1rem;color:#0f172a;">No completed trips</h3>
                    <p style="color:#64748b;">Your completed bookings will appear here.</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($completed as $b): ?>
                        <div class="col-md-6">
                            <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);overflow:hidden;">
                                <div style="display:flex;">
                                    <?php if (!empty($b['primary_image'])): ?>
                                        <img src="<?php echo e(image_url($b['primary_image'])); ?>" alt="" style="width:140px;height:140px;object-fit:cover;">
                                    <?php else: ?>
                                        <div style="width:140px;height:140px;background:#e2e8f0;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:2rem;"><i class="bi bi-house"></i></div>
                                    <?php endif; ?>
                                    <div style="flex:1;padding:1.25rem;">
                                        <a href="<?php echo url('/property-details.php?id=' . (int)$b['property_id']); ?>" style="color:#0f172a;font-weight:700;text-decoration:none;font-size:1rem;"><?php echo e($b['property_title']); ?></a>
                                        <div style="color:#64748b;font-size:0.85rem;margin-top:0.25rem;"><i class="bi bi-geo-alt"></i> <?php echo e($b['property_city'] ?? ''); ?></div>
                                        <div style="color:#64748b;font-size:0.85rem;margin-top:0.5rem;"><i class="bi bi-calendar"></i> <?php echo formatDate($b['start_date']); ?> - <?php echo formatDate($b['end_date']); ?></div>
                                        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:0.75rem;">
                                            <strong style="color:#0f172a;"><?php echo format_price($b['total_amount']); ?></strong>
                                            <span class="badge badge-info">Completed</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Cancelled -->
        <div id="tab-cancelled" class="tab-pane" style="<?php echo $activeTab !== 'cancelled' ? 'display:none;' : ''; ?>">
            <?php if (empty($cancelled)): ?>
                <div class="card" style="text-align:center;padding:3rem 2rem;border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);">
                    <i class="bi bi-x-circle" style="font-size:3rem;color:#cbd5e1;"></i>
                    <h3 style="margin-top:1rem;color:#0f172a;">No cancelled bookings</h3>
                    <p style="color:#64748b;">Cancelled bookings will appear here.</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($cancelled as $b): ?>
                        <div class="col-md-6">
                            <div class="card" style="border:none;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,0.06);overflow:hidden;opacity:0.75;">
                                <div style="display:flex;">
                                    <?php if (!empty($b['primary_image'])): ?>
                                        <img src="<?php echo e(image_url($b['primary_image'])); ?>" alt="" style="width:140px;height:140px;object-fit:cover;">
                                    <?php else: ?>
                                        <div style="width:140px;height:140px;background:#e2e8f0;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:2rem;"><i class="bi bi-house"></i></div>
                                    <?php endif; ?>
                                    <div style="flex:1;padding:1.25rem;">
                                        <a href="<?php echo url('/property-details.php?id=' . (int)$b['property_id']); ?>" style="color:#0f172a;font-weight:700;text-decoration:none;font-size:1rem;"><?php echo e($b['property_title']); ?></a>
                                        <div style="color:#64748b;font-size:0.85rem;margin-top:0.25rem;"><i class="bi bi-geo-alt"></i> <?php echo e($b['property_city'] ?? ''); ?></div>
                                        <div style="color:#64748b;font-size:0.85rem;margin-top:0.5rem;"><i class="bi bi-calendar"></i> <?php echo formatDate($b['start_date']); ?> - <?php echo formatDate($b['end_date']); ?></div>
                                        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:0.75rem;">
                                            <strong style="color:#0f172a;"><?php echo format_price($b['total_amount']); ?></strong>
                                            <span class="badge badge-error">Cancelled</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
