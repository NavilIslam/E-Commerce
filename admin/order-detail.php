<?php
$pageTitle = "Order Details";
require_once __DIR__ . '/includes/admin-header.php';

$orderId = (int)($_GET['id'] ?? 0);
$orderModel = new Order();
$order = $orderModel->findById($orderId);

if (!$order) {
    setFlash('error', 'Order not found.');
    redirect(ADMIN_URL . '/orders.php');
}

// Handle Status Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_order_status') {
    CSRF::requireValid();
    $newOrderStatus = $_POST['order_status'] ?? $order['order_status'];
    $newPaymentStatus = $_POST['payment_status'] ?? $order['payment_status'];

    $orderModel->updateStatus($orderId, $newOrderStatus, $newPaymentStatus);
    setFlash('success', 'Order and payment status updated successfully.');
    redirect(ADMIN_URL . '/order-detail.php?id=' . $orderId);
}

// Refresh order
$order = $orderModel->findById($orderId);
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
    <div>
        <a href="<?= ADMIN_URL ?>/orders.php" style="color:var(--primary);font-size:0.875rem;font-weight:600;"><?= icon('arrow-l', 'icon-sm') ?> Back to Orders List</a>
        <h1 style="font-size:1.6rem;font-weight:700;margin-top:0.25rem;">Manage Order: <?= e($order['order_number']) ?></h1>
        <p style="font-size:0.85rem;color:var(--text-muted);">Placed on <?= date('F d, Y \a\t h:i A', strtotime($order['created_at'])) ?></p>
    </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:2rem;">
    <!-- Items Breakdown -->
    <div class="admin-card">
        <h2 class="admin-card-title" style="margin-bottom:1rem;">Order Items</h2>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Unit Price</th>
                    <th style="text-align:center;">Qty</th>
                    <th style="text-align:right;">Line Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order['items'] as $item): ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:0.75rem;">
                                <img src="<?= getImageUrl($item['product_image']) ?>" style="width:40px;height:40px;object-fit:cover;border:1px solid var(--admin-border);" alt="">
                                <div>
                                    <div style="font-weight:600;"><?= e($item['product_name']) ?></div>
                                    <div style="font-size:0.75rem;color:var(--text-muted);font-family:monospace;">SKU: <?= e($item['product_sku'] ?? 'N/A') ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?= formatPrice($item['unit_price']) ?></td>
                        <td style="text-align:center;font-weight:700;"><?= $item['quantity'] ?></td>
                        <td style="text-align:right;font-weight:700;"><?= formatPrice($item['total_price']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top:1.5rem;border-top:1px solid var(--admin-border);padding-top:1rem;">
            <div style="display:flex;justify-content:space-between;padding:0.4rem 0;">
                <span>Subtotal:</span>
                <strong><?= formatPrice($order['subtotal']) ?></strong>
            </div>
            <?php if ($order['discount_amount'] > 0): ?>
                <div style="display:flex;justify-content:space-between;padding:0.4rem 0;color:var(--text-secondary);">
                    <span>Offer Discounts:</span>
                    <span>-<?= formatPrice($order['discount_amount']) ?></span>
                </div>
            <?php endif; ?>
            <?php if ($order['coupon_discount'] > 0): ?>
                <div style="display:flex;justify-content:space-between;padding:0.4rem 0;color:var(--text-secondary);">
                    <span>Coupon Discount (<?= e($order['coupon_code']) ?>):</span>
                    <span>-<?= formatPrice($order['coupon_discount']) ?></span>
                </div>
            <?php endif; ?>
            <div style="display:flex;justify-content:space-between;padding:0.4rem 0;">
                <span>Shipping Fee:</span>
                <span><?= formatPrice($order['shipping_fee']) ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:0.75rem 0;font-size:1.25rem;font-weight:700;border-top:2px dashed var(--admin-border);color:var(--primary);">
                <span>Total Amount:</span>
                <span><?= formatPrice($order['total_amount']) ?></span>
            </div>
        </div>
    </div>

    <!-- Status Updates & Customer Info -->
    <div>
        <!-- Update Status Card -->
        <div class="admin-card" style="margin-bottom:1.5rem;">
            <h3 class="admin-card-title" style="margin-bottom:1rem;">Update Order Status</h3>

            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="update_order_status">

                <div class="form-group">
                    <label class="form-label">Fulfillment Status</label>
                    <select name="order_status" class="form-control">
                        <option value="pending" <?= $order['order_status'] === 'pending' ? 'selected' : '' ?>>Pending Review</option>
                        <option value="processing" <?= $order['order_status'] === 'processing' ? 'selected' : '' ?>>Processing / Packing</option>
                        <option value="shipped" <?= $order['order_status'] === 'shipped' ? 'selected' : '' ?>>Shipped (In Transit)</option>
                        <option value="delivered" <?= $order['order_status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                        <option value="cancelled" <?= $order['order_status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled (Restores Stock)</option>
                        <option value="refunded" <?= $order['order_status'] === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Payment Status</label>
                    <select name="payment_status" class="form-control">
                        <option value="pending" <?= $order['payment_status'] === 'pending' ? 'selected' : '' ?>>Pending Payment</option>
                        <option value="paid" <?= $order['payment_status'] === 'paid' ? 'selected' : '' ?>>Paid (Confirmed)</option>
                        <option value="failed" <?= $order['payment_status'] === 'failed' ? 'selected' : '' ?>>Failed</option>
                        <option value="refunded" <?= $order['payment_status'] === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Update Status</button>
            </form>
        </div>

        <!-- Customer & Shipping Info Card -->
        <div class="admin-card">
            <h3 class="admin-card-title" style="margin-bottom:0.75rem;">Delivery Destination</h3>
            <p style="font-weight:700;font-size:0.95rem;"><?= e($order['shipping_name']) ?></p>
            <p style="font-size:0.875rem;color:var(--text-muted);margin-bottom:0.5rem;"><?= icon('phone') ?> <?= e($order['shipping_phone']) ?></p>
            <p style="font-size:0.875rem;line-height:1.6;">
                <?= nl2br(e($order['shipping_address'])) ?><br>
                <?= e($order['shipping_city']) ?><?= !empty($order['shipping_area']) ? ', ' . e($order['shipping_area']) : '' ?> <?= e($order['shipping_postal'] ?? '') ?>
            </p>
            <?php if (!empty($order['notes'])): ?>
                <div style="margin-top:1rem;background:#f8fafc;padding:0.75rem;border:1px solid var(--admin-border);font-size:0.825rem;">
                    <strong>Customer Instructions:</strong> <?= e($order['notes']) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
