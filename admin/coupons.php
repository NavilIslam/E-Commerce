<?php
$pageTitle = "Coupons Management";
require_once __DIR__ . '/includes/admin-header.php';

requirePermission('manage_coupons');

$couponModel = new Coupon();

// Handle Delete
if (isset($_GET['delete'])) {
    CSRF::requireValid();
    $cid = (int)$_GET['delete'];
    $couponModel->delete($cid);
    setFlash('success', 'Coupon deleted.');
    redirect(ADMIN_URL . '/coupons.php');
}

$coupons = $couponModel->getAll();
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div>
        <h1 style="font-size:1.6rem;font-weight:700;">Coupon Codes Management</h1>
        <p style="color:var(--text-muted);font-size:0.875rem;">Manage promotional discount coupon codes and redemption limits</p>
    </div>
    <a href="<?= ADMIN_URL ?>/add-coupon.php" class="btn btn-primary">
        + Create New Coupon
    </a>
</div>

<div class="admin-card" style="padding:0;overflow:hidden;">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Coupon Code</th>
                <th>Discount Value</th>
                <th>Min. Purchase</th>
                <th>Redemptions</th>
                <th>Valid Until</th>
                <th>Status</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($coupons)): ?>
                <tr><td colspan="7" style="text-align:center;padding:3rem;">No coupons found.</td></tr>
            <?php else: ?>
                <?php foreach ($coupons as $c): ?>
                    <?php $isExpired = strtotime($c['end_date']) < time(); ?>
                    <tr>
                        <td>
                            <strong style="font-family:monospace;font-size:1.05rem;color:var(--primary);background:var(--primary-light);padding:0.2rem 0.6rem;">
                                <?= e($c['code']) ?>
                            </strong>
                            <?php if (!empty($c['description'])): ?>
                                <div style="font-size:0.75rem;color:var(--text-muted);margin-top:0.25rem;"><?= e($c['description']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong style="color:var(--text-dark);font-family:var(--font-mono);font-size:0.95rem;">
                                <?= $c['discount_type'] === 'percentage' ? $c['discount_value'] . '%' : formatPrice($c['discount_value']) ?> OFF
                            </strong>
                        </td>
                        <td><?= !empty($c['min_order_amount']) ? formatPrice($c['min_order_amount']) : 'No Minimum' ?></td>
                        <td>
                            <strong><?= $c['times_used'] ?></strong>
                            <span style="color:var(--text-muted);font-size:0.8rem;">
                                <?= $c['usage_limit'] !== null ? '/ ' . $c['usage_limit'] : ' (Unlimited)' ?>
                            </span>
                        </td>
                        <td style="font-size:0.8rem;color:var(--text-muted);">
                            <?= date('M d, Y', strtotime($c['end_date'])) ?>
                        </td>
                        <td>
                            <?php if ($isExpired): ?>
                                <span class="badge badge-muted">Expired</span>
                            <?php elseif ($c['is_active']): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-muted">Disabled</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right;">
                            <a href="<?= ADMIN_URL ?>/edit-coupon.php?id=<?= $c['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                            <a href="?delete=<?= $c['id'] ?>&csrf_token=<?= CSRF::getToken() ?>" onclick="return confirm('Delete coupon <?= e($c['code']) ?>?')" class="btn btn-danger btn-sm" style="padding:0.35rem 0.5rem;"><?= icon('trash', '', 'Delete coupon') ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
