<?php
$pageTitle   = "Order";
$accountPage = 'orders';
require_once __DIR__ . '/includes/auth.php';

$orderId     = (int)($_GET['id'] ?? 0);
$orderNumber = $_GET['order_number'] ?? '';

$orderModel = new Order();
$userId     = Auth::id();

$order = null;
if ($orderId > 0) {
    $order = $orderModel->findById($orderId, $userId);
} elseif (!empty($orderNumber)) {
    $order = $orderModel->findByOrderNumber($orderNumber, $userId);
}

if (!$order) {
    setFlash('error', 'We could not find that order on your account.');
    redirect(BASE_URL . '/orders.php');
}

$pageTitle = 'Order ' . $order['order_number'];

// Was this order just placed? Then greet rather than simply report.
$justPlaced = isset($_GET['placed']) || (strtotime($order['created_at']) > time() - 120);

$statusPill = [
    'pending'    => 'pill-warning',
    'processing' => 'pill-info',
    'shipped'    => 'pill-info',
    'delivered'  => 'pill-success',
    'cancelled'  => 'pill-danger',
    'refunded'   => 'pill-neutral',
];
$payLabel = [
    'cod'        => 'Cash on delivery',
    'bkash'      => 'bKash or Nagad',
    'nagad'      => 'bKash or Nagad',
    'sslcommerz' => 'Card or bank transfer',
    'card'       => 'Card or bank transfer',
    'online'     => 'Online payment',
];

$timeline    = ['pending', 'processing', 'shipped', 'delivered'];
$currentStep = array_search($order['order_status'], $timeline, true);
$isCancelled = in_array($order['order_status'], ['cancelled', 'refunded'], true);

// The stored subtotal is net of offer discounts, same as the cart, so add them
// back for a summary the customer can actually follow.
$displaySubtotal = (float)$order['subtotal'] + (float)$order['discount_amount'];

require_once __DIR__ . '/includes/header.php';
?>

<div class="container page">
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="<?= BASE_URL ?>/index.php">Home</a>
        <?= icon('chevron-r') ?>
        <a href="<?= BASE_URL ?>/orders.php">Orders</a>
        <?= icon('chevron-r') ?>
        <span aria-current="page"><?= e($order['order_number']) ?></span>
    </nav>

    <?php if ($justPlaced): ?>
        <div class="card card-lg" style="text-align:center;margin-bottom:var(--space-8)">
            <div class="empty-icon" style="color:var(--success);background:var(--success-bg)"><?= icon('check') ?></div>
            <h1 class="page-title">Thanks, your order is confirmed</h1>
            <p class="t-sm t-muted" style="margin-top:var(--space-3);max-width:52ch;margin-inline:auto">
                Order <strong><?= e($order['order_number']) ?></strong>.
                <?php if (in_array($order['payment_method'], ['bkash', 'nagad', 'sslcommerz', 'card'], true)): ?>
                    We'll contact you on <?= e($order['shipping_phone']) ?> with payment instructions before dispatch.
                <?php else: ?>
                    Pay the courier when it arrives.
                <?php endif; ?>
            </p>
            <div class="row wrap" style="justify-content:center;gap:var(--space-3);margin-top:var(--space-6)">
                <a href="<?= BASE_URL ?>/products.php" class="btn btn-primary">Continue shopping</a>
                <a href="<?= BASE_URL ?>/orders.php" class="btn btn-secondary">All orders</a>
            </div>
        </div>
    <?php else: ?>
        <div class="page-head row-between wrap">
            <div>
                <h1 class="page-title">Order <?= e($order['order_number']) ?></h1>
                <p class="page-sub">Placed <?= date('j F Y \a\t g:ia', strtotime($order['created_at'])) ?></p>
            </div>
            <button type="button" class="btn btn-secondary btn-sm" onclick="window.print()">Print</button>
        </div>
    <?php endif; ?>

    <!-- Status -->
    <div class="card" style="margin-bottom:var(--space-6)">
        <div class="row-between wrap" style="margin-bottom:<?= $isCancelled ? '0' : 'var(--space-6)' ?>">
            <div>
                <p class="t-xs t-subtle">Status</p>
                <span class="pill <?= $statusPill[$order['order_status']] ?? 'pill-neutral' ?>" style="margin-top:var(--space-1)">
                    <?= e($order['order_status']) ?>
                </span>
            </div>
            <div class="t-right">
                <p class="t-xs t-subtle">Payment</p>
                <p class="t-sm t-semi"><?= e($payLabel[$order['payment_method']] ?? ucfirst($order['payment_method'])) ?></p>
                <p class="t-xs t-subtle"><?= $order['payment_status'] === 'paid' ? 'Received' : 'Pending' ?></p>
            </div>
        </div>

        <?php if (!$isCancelled): ?>
            <ol class="steps" style="margin-bottom:0">
                <?php foreach ($timeline as $i => $stepName): ?>
                    <?php
                        $state = $currentStep !== false && $i < $currentStep ? 'is-done'
                               : ($i === $currentStep ? 'is-current' : '');
                    ?>
                    <li class="step <?= $state ?>" <?= $state === 'is-current' ? 'aria-current="step"' : '' ?>>
                        <span class="step-num"><?= $state === 'is-done' ? icon('check', 'icon-sm') : $i + 1 ?></span>
                        <span class="step-label"><?= ucfirst($stepName) ?></span>
                    </li>
                    <?php if ($i < count($timeline) - 1): ?><span class="step-sep" aria-hidden="true"></span><?php endif; ?>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </div>

    <div class="cart-layout">
        <!-- Items -->
        <div class="card">
            <div class="card-head">
                <h2 class="card-title">Items</h2>
                <span class="t-sm t-subtle num"><?= count($order['items']) ?></span>
            </div>

            <?php foreach ($order['items'] as $item): ?>
                <div class="cart-item">
                    <img src="<?= getImageUrl($item['product_image']) ?>" class="cart-thumb" alt="" width="88" height="88">
                    <div class="cart-info">
                        <?php if (!empty($item['product_slug'])): ?>
                            <a href="<?= BASE_URL ?>/product.php?slug=<?= urlencode($item['product_slug']) ?>" class="cart-name">
                                <?= e($item['product_name']) ?>
                            </a>
                        <?php else: ?>
                            <span class="cart-name"><?= e($item['product_name']) ?></span>
                        <?php endif; ?>
                        <p class="cart-meta"><?= e($item['product_sku'] ?? '') ?></p>
                        <p class="cart-meta num"><?= (int)$item['quantity'] ?> &times; <?= formatPrice($item['unit_price']) ?></p>
                    </div>
                    <div class="cart-line">
                        <p class="cart-line-total"><?= formatPrice($item['total_price']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>

            <div style="margin-top:var(--space-6);padding-top:var(--space-4);border-top:1px solid var(--border)">
                <div class="sum-row">
                    <span>Subtotal</span>
                    <span><?= formatPrice($displaySubtotal) ?></span>
                </div>
                <?php if ($order['discount_amount'] > 0): ?>
                    <div class="sum-row is-credit">
                        <span>Offers</span>
                        <span>&minus;<?= formatPrice($order['discount_amount']) ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($order['coupon_discount'] > 0): ?>
                    <div class="sum-row is-credit">
                        <span>Coupon (<?= e($order['coupon_code']) ?>)</span>
                        <span>&minus;<?= formatPrice($order['coupon_discount']) ?></span>
                    </div>
                <?php endif; ?>
                <div class="sum-row">
                    <span>Delivery</span>
                    <span>
                        <?php if ((float)$order['shipping_fee'] <= 0): ?>
                            <strong style="color:var(--success)">Free</strong>
                        <?php else: ?>
                            <?= formatPrice($order['shipping_fee']) ?>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="sum-total">
                    <span>Total</span>
                    <span><?= formatPrice($order['total_amount']) ?></span>
                </div>
            </div>
        </div>

        <!-- Delivery details -->
        <div class="summary">
            <div class="card">
                <div class="card-head">
                    <h2 class="card-title">Delivery address</h2>
                </div>
                <p class="t-semi"><?= e($order['shipping_name']) ?></p>
                <p class="t-sm t-muted" style="margin-top:var(--space-1)"><?= e($order['shipping_phone']) ?></p>
                <p class="t-sm t-muted" style="margin-top:var(--space-3);line-height:1.7">
                    <?= nl2br(e($order['shipping_address'])) ?><br>
                    <?= e($order['shipping_city']) ?><?= !empty($order['shipping_area']) ? ', ' . e($order['shipping_area']) : '' ?>
                    <?= e($order['shipping_postal'] ?? '') ?>
                </p>

                <?php if (!empty($order['notes'])): ?>
                    <div class="alert alert-info" style="margin-top:var(--space-4)">
                        <?= icon('info') ?>
                        <span><strong>Note:</strong> <?= e($order['notes']) ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card" style="margin-top:var(--space-4)">
                <h2 class="card-title" style="font-size:var(--text-base);margin-bottom:var(--space-3)">Need help?</h2>
                <p class="t-sm t-muted" style="margin-bottom:var(--space-4)">
                    Quote order <?= e($order['order_number']) ?> when you get in touch.
                </p>
                <a href="<?= BASE_URL ?>/contact.php" class="btn btn-secondary btn-sm btn-block">Contact support</a>
                <a href="<?= BASE_URL ?>/returns.php" class="btn btn-tertiary btn-sm btn-block" style="margin-top:var(--space-2)">
                    Returns policy
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
