<?php
$pageTitle = "Customer Profile";
require_once __DIR__ . '/includes/admin-header.php';

requirePermission('manage_customers');

$userId = (int)($_GET['id'] ?? 0);
$userModel = new User();
$customer = $userModel->findById($userId);

if (!$customer) {
    setFlash('error', 'Customer not found.');
    redirect(ADMIN_URL . '/customers.php');
}

$addresses = $userModel->getAddresses($userId);
$orderModel = new Order();
$orderData = $orderModel->getCustomerOrders($userId, 1, 50);
$orders = $orderData['orders'];
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div>
        <a href="<?= ADMIN_URL ?>/customers.php" style="color:var(--primary);font-size:0.875rem;font-weight:600;"><?= icon('arrow-l', 'icon-sm') ?> Back to Customers</a>
        <h1 style="font-size:1.6rem;font-weight:700;margin-top:0.25rem;">Customer: <?= e($customer['first_name'] . ' ' . $customer['last_name']) ?></h1>
    </div>
</div>

<div style="display:grid;grid-template-columns:1.2fr 2fr;gap:2rem;">
    <!-- Customer Profile Card -->
    <div>
        <div class="admin-card" style="margin-bottom:1.5rem;">
            <h2 class="admin-card-title" style="margin-bottom:1rem;">Account Info</h2>
            <div style="display:flex;flex-direction:column;gap:0.75rem;font-size:0.9rem;">
                <div>
                    <span style="color:var(--text-muted);">Email:</span>
                    <strong><?= e($customer['email']) ?></strong>
                </div>
                <div>
                    <span style="color:var(--text-muted);">Phone:</span>
                    <strong><?= e($customer['phone'] ?? 'None registered') ?></strong>
                </div>
                <div>
                    <span style="color:var(--text-muted);">Account Role:</span>
                    <span class="badge badge-info"><?= strtoupper($customer['role']) ?></span>
                </div>
                <div>
                    <span style="color:var(--text-muted);">Status:</span>
                    <span class="badge badge-<?= $customer['is_active'] ? 'success' : 'danger' ?>"><?= $customer['is_active'] ? 'Active' : 'Suspended' ?></span>
                </div>
                <div>
                    <span style="color:var(--text-muted);">Member Since:</span>
                    <strong><?= date('M d, Y', strtotime($customer['created_at'])) ?></strong>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <h3 class="admin-card-title" style="margin-bottom:1rem;">Saved Addresses (<?= count($addresses) ?>)</h3>
            <?php if (empty($addresses)): ?>
                <p style="color:var(--text-muted);font-size:0.85rem;">No saved addresses on file.</p>
            <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:0.75rem;">
                    <?php foreach ($addresses as $a): ?>
                        <div style="border:1px solid var(--admin-border);padding:0.75rem;border-radius:4px;font-size:0.85rem;">
                            <strong><?= e($a['label']) ?></strong>: <?= e($a['full_name']) ?> (<?= e($a['phone']) ?>)<br>
                            <?= e($a['address_line1']) ?>, <?= e($a['city']) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Customer Orders -->
    <div class="admin-card" style="padding:0;overflow:hidden;">
        <div style="padding:1.25rem;border-bottom:1px solid var(--admin-border);">
            <h3 class="admin-card-title">Order History (<?= count($orders) ?>)</h3>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Payment</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="5" style="text-align:center;padding:2rem;">No orders placed yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $ord): ?>
                        <tr>
                            <td>
                                <a href="<?= ADMIN_URL ?>/order-detail.php?id=<?= $ord['id'] ?>" style="color:var(--primary);font-weight:700;">
                                    <?= e($ord['order_number']) ?>
                                </a>
                            </td>
                            <td><?= date('M d, Y', strtotime($ord['created_at'])) ?></td>
                            <td style="font-weight:700;"><?= formatPrice($ord['total_amount']) ?></td>
                            <td><span class="badge badge-info"><?= e($ord['order_status']) ?></span></td>
                            <td><?= strtoupper($ord['payment_status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
