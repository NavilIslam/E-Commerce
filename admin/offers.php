<?php
$pageTitle = "Promotional Offers & Flash Sales";
require_once __DIR__ . '/includes/admin-header.php';

requirePermission('manage_offers');

$offerModel = new Offer();

// Handle Delete
if (isset($_GET['delete'])) {
    CSRF::requireValid();
    $oid = (int)$_GET['delete'];
    $offerModel->delete($oid);
    setFlash('success', 'Offer removed.');
    redirect(ADMIN_URL . '/offers.php');
}

$offers = $offerModel->getAll(false);
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div>
        <h1 style="font-size:1.6rem;font-weight:700;">Promotional Offers & Flash Sales</h1>
        <p style="color:var(--text-muted);font-size:0.875rem;">Create dynamic discounts, percentage savings, and flash deals</p>
    </div>
    <a href="<?= ADMIN_URL ?>/add-offer.php" class="btn btn-primary">
        + Create New Offer
    </a>
</div>

<div class="admin-card" style="padding:0;overflow:hidden;">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Offer Name</th>
                <th>Type</th>
                <th>Discount</th>
                <th>Scope</th>
                <th>Active Window</th>
                <th>Status</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($offers)): ?>
                <tr><td colspan="7" style="text-align:center;padding:3rem;">No offers configured.</td></tr>
            <?php else: ?>
                <?php foreach ($offers as $o): ?>
                    <?php
                    $isExpired = strtotime($o['end_date']) < time();
                    $isFlash = !empty($o['is_flash_sale']);
                    ?>
                    <tr>
                        <td>
                            <div style="font-weight:700;color:var(--text-dark);">
                                <?= $isFlash ? icon('percent', 'icon-xs') . ' ' : '' ?><?= e($o['name']) ?>
                            </div>
                            <?php if (!empty($o['description'])): ?>
                                <div style="font-size:0.75rem;color:var(--text-muted);"><?= e($o['description']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= $isFlash ? 'badge-muted' : 'badge-info' ?>">
                                <?= $isFlash ? 'FLASH DEAL' : strtoupper($o['discount_type']) ?>
                            </span>
                        </td>
                        <td>
                            <strong style="color:var(--primary);font-size:1.05rem;">
                                <?= $o['discount_type'] === 'percentage' ? $o['discount_value'] . '%' : formatPrice($o['discount_value']) ?> OFF
                            </strong>
                        </td>
                        <td>
                            <span style="font-size:0.85rem;">
                                <?= $o['product_count'] ?> Products, <?= $o['category_count'] ?> Categories
                            </span>
                        </td>
                        <td style="font-size:0.8rem;color:var(--text-muted);">
                            <?= date('M d', strtotime($o['start_date'])) ?> &ndash; <?= date('M d, Y', strtotime($o['end_date'])) ?>
                        </td>
                        <td>
                            <?php if ($isExpired): ?>
                                <span class="badge badge-muted">Expired</span>
                            <?php elseif ($o['is_active']): ?>
                                <span class="badge badge-success">Active</span>
                            <?php else: ?>
                                <span class="badge badge-muted">Disabled</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right;">
                            <a href="<?= ADMIN_URL ?>/edit-offer.php?id=<?= $o['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                            <a href="?delete=<?= $o['id'] ?>&csrf_token=<?= CSRF::getToken() ?>" onclick="return confirm('Delete offer <?= e(addslashes($o['name'])) ?>?')" class="btn btn-danger btn-sm" style="padding:0.35rem 0.5rem;"><?= icon('trash', '', 'Delete offer') ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
