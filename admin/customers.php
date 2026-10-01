<?php
$pageTitle = "Customer Management";
require_once __DIR__ . '/includes/admin-header.php';

requirePermission('manage_customers');

$userModel = new User();

// Handle Status Toggle
if (isset($_GET['toggle_status'])) {
    CSRF::requireValid();
    $uid = (int)$_GET['toggle_status'];
    $userModel->toggleStatus($uid);
    setFlash('success', 'Customer account status updated.');
    redirect(ADMIN_URL . '/customers.php');
}

$filters = [
    'role'      => 'customer',
    'search'    => $_GET['q'] ?? null,
    'is_active' => isset($_GET['status']) && $_GET['status'] !== '' ? (int)$_GET['status'] : null,
];

$page = max(1, (int)($_GET['page'] ?? 1));
$result = $userModel->getAll($filters, $page, 20);
$customers = $result['users'];
$totalPages = $result['total_pages'];
$total = $result['total'];
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div>
        <h1 style="font-size:1.6rem;font-weight:700;">Customer Directory</h1>
        <p style="color:var(--text-muted);font-size:0.875rem;">Total registered customers: <?= $total ?></p>
    </div>
</div>

<!-- Filter Bar -->
<div class="admin-card" style="padding:1rem;">
    <form method="GET" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:center;">
        <input type="text" name="q" placeholder="Search customer name, email, phone..." value="<?= e($filters['search'] ?? '') ?>" class="form-control" style="max-width:320px;">

        <select name="status" class="form-control" style="max-width:180px;">
            <option value="">All Account Statuses</option>
            <option value="1" <?= ($filters['is_active'] === 1) ? 'selected' : '' ?>>Active</option>
            <option value="0" <?= ($filters['is_active'] === 0) ? 'selected' : '' ?>>Suspended</option>
        </select>

        <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
        <a href="<?= ADMIN_URL ?>/customers.php" class="btn btn-outline btn-sm">Reset</a>
    </form>
</div>

<div class="admin-card" style="padding:0;overflow:hidden;">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Phone</th>
                <th>Total Orders</th>
                <th>Lifetime Spend</th>
                <th>Joined Date</th>
                <th>Last Login</th>
                <th>Status</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($customers)): ?>
                <tr><td colspan="8" style="text-align:center;padding:3rem;">No customers found.</td></tr>
            <?php else: ?>
                <?php foreach ($customers as $c): ?>
                    <tr>
                        <td>
                            <div style="font-weight:700;color:var(--text-dark);"><?= e($c['first_name'] . ' ' . $c['last_name']) ?></div>
                            <div style="font-size:0.75rem;color:var(--text-muted);"><?= e($c['email']) ?></div>
                        </td>
                        <td><?= e($c['phone'] ?? 'N/A') ?></td>
                        <td><strong><?= $c['total_orders'] ?></strong> order(s)</td>
                        <td style="font-weight:700;color:var(--primary);"><?= formatPrice($c['total_spent']) ?></td>
                        <td style="font-size:0.8rem;color:var(--text-muted);"><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                        <td style="font-size:0.8rem;color:var(--text-muted);"><?= !empty($c['last_login_at']) ? timeAgo($c['last_login_at']) : 'Never' ?></td>
                        <td>
                            <a href="?toggle_status=<?= $c['id'] ?>&csrf_token=<?= CSRF::getToken() ?>" title="Click to toggle status">
                                <span class="badge badge-<?= $c['is_active'] ? 'success' : 'danger' ?>">
                                    <?= $c['is_active'] ? 'Active' : 'Suspended' ?>
                                </span>
                            </a>
                        </td>
                        <td style="text-align:right;">
                            <a href="<?= ADMIN_URL ?>/customer-detail.php?id=<?= $c['id'] ?>" class="btn btn-outline btn-sm">
                                Profile & Orders <?= icon('chevron-r', 'icon-sm') ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPages > 1): ?>
    <div style="display:flex;justify-content:center;gap:0.5rem;margin-top:1.5rem;">
        <?php for ($pg = 1; $pg <= $totalPages; $pg++): ?>
            <a href="?page=<?= $pg ?>" class="btn <?= $pg === $page ? 'btn-primary' : 'btn-outline' ?> btn-sm">
                <?= $pg ?>
            </a>
        <?php endfor; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
