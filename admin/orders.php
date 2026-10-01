<?php
$pageTitle = "Orders Management";
require_once __DIR__ . '/includes/admin-header.php';

$orderModel = new Order();

$filters = [
    'order_status'   => $_GET['status'] ?? null,
    'payment_status' => $_GET['payment'] ?? null,
    'search'         => $_GET['q'] ?? null,
];

$page = max(1, (int)($_GET['page'] ?? 1));
$result = $orderModel->getAll($filters, $page, 20);
$orders = $result['orders'];
$totalPages = $result['total_pages'];
$total = $result['total'];
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div>
        <h1 style="font-size:1.5rem;font-weight:700;">Orders</h1>
        <p style="color:var(--text-muted);font-size:0.825rem;">Total orders placed: <?= $total ?></p>
    </div>
</div>

<!-- Filters -->
<div class="admin-card" style="padding:1rem;">
    <form method="GET" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:center;">
        <input type="text" name="q" placeholder="Search order #, customer name, phone..." value="<?= e($filters['search'] ?? '') ?>" class="form-control" style="max-width:300px;">

        <select name="status" class="form-control" style="max-width:180px;">
            <option value="">All Order Statuses</option>
            <option value="pending" <?= ($filters['order_status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
            <option value="processing" <?= ($filters['order_status'] === 'processing') ? 'selected' : '' ?>>Processing</option>
            <option value="shipped" <?= ($filters['order_status'] === 'shipped') ? 'selected' : '' ?>>Shipped</option>
            <option value="delivered" <?= ($filters['order_status'] === 'delivered') ? 'selected' : '' ?>>Delivered</option>
            <option value="cancelled" <?= ($filters['order_status'] === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
            <option value="refunded" <?= ($filters['order_status'] === 'refunded') ? 'selected' : '' ?>>Refunded</option>
        </select>

        <select name="payment" class="form-control" style="max-width:180px;">
            <option value="">All Payment Statuses</option>
            <option value="pending" <?= ($filters['payment_status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
            <option value="paid" <?= ($filters['payment_status'] === 'paid') ? 'selected' : '' ?>>Paid</option>
            <option value="failed" <?= ($filters['payment_status'] === 'failed') ? 'selected' : '' ?>>Failed</option>
            <option value="refunded" <?= ($filters['payment_status'] === 'refunded') ? 'selected' : '' ?>>Refunded</option>
        </select>

        <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
        <a href="<?= ADMIN_URL ?>/orders.php" class="btn btn-outline btn-sm">Reset</a>
    </form>
</div>

<div class="admin-card" style="padding:0;overflow:hidden;">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Order Number</th>
                <th>Customer</th>
                <th>Recipient Phone</th>
                <th>Amount</th>
                <th>Order Status</th>
                <th>Payment</th>
                <th>Placed Date</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="8" style="text-align:center;padding:3rem;">No orders match the filters.</td></tr>
            <?php else: ?>
                <?php foreach ($orders as $ord): ?>
                    <tr>
                        <td>
                            <a href="<?= ADMIN_URL ?>/order-detail.php?id=<?= $ord['id'] ?>" style="font-family:var(--font-mono, monospace);font-weight:600;color:var(--primary);">
                                #<?= e($ord['order_number']) ?>
                            </a>
                            <div style="font-size:0.75rem;color:var(--text-muted);"><?= $ord['item_count'] ?> item(s)</div>
                        </td>
                        <td>
                            <div style="font-weight:600;color:var(--text-dark);"><?= e($ord['shipping_name']) ?></div>
                            <div style="font-size:0.75rem;color:var(--text-muted);"><?= e($ord['customer_email']) ?></div>
                        </td>
                        <td style="font-family:var(--font-mono, monospace);font-size:0.85rem;"><?= e($ord['shipping_phone']) ?></td>
                        <td style="font-family:var(--font-mono, monospace);font-weight:700;color:var(--text-dark);"><?= formatPrice($ord['total_amount']) ?></td>
                        <td>
                            <?php
                            $orderPillClass = match($ord['order_status']) {
                                'delivered'             => 'pill-success',
                                'cancelled', 'refunded' => 'pill-danger',
                                'shipped', 'processing' => 'pill-neutral',
                                default                 => 'pill-warning',
                            };
                            ?>
                            <span class="status-pill <?= $orderPillClass ?>">
                                <span class="status-dot"></span>
                                <?= ucfirst($ord['order_status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            $paymentPillClass = match($ord['payment_status']) {
                                'paid'               => 'pill-success',
                                'failed', 'refunded' => 'pill-danger',
                                default              => 'pill-warning',
                            };
                            ?>
                            <span class="status-pill <?= $paymentPillClass ?>">
                                <span class="status-dot"></span>
                                <?= strtoupper($ord['payment_status']) ?> <span style="opacity:0.75;font-weight:500;">(<?= strtoupper($ord['payment_method']) ?>)</span>
                            </span>
                        </td>
                        <td style="font-size:0.8rem;color:var(--text-muted);"><?= date('M d, Y h:i A', strtotime($ord['created_at'])) ?></td>
                        <td style="text-align:right;">
                            <a href="<?= ADMIN_URL ?>/order-detail.php?id=<?= $ord['id'] ?>" class="btn btn-outline btn-sm">
                                View / Manage <?= icon('chevron-r', 'icon-sm') ?>
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
