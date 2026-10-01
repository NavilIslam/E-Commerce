<?php
$pageTitle   = "Your orders";
$accountPage = 'orders';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/header.php';

$userId     = Auth::id();
$orderModel = new Order();
$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = 10;
$result     = $orderModel->getCustomerOrders($userId, $page, $perPage);
$orders     = $result['orders'];
$totalPages = $result['total_pages'];

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
?>

<div class="container page">
    <div class="page-head">
        <h1 class="page-title">Your orders</h1>
    </div>

    <div class="account-layout">
        <?php require __DIR__ . '/includes/account-nav.php'; ?>

        <div>
            <?php if (empty($orders)): ?>
                <div class="empty">
                    <div class="empty-icon"><?= icon('package') ?></div>
                    <h2 class="empty-title">No orders yet</h2>
                    <p class="empty-text">When you place an order it will appear here with its delivery status.</p>
                    <a href="<?= BASE_URL ?>/products.php" class="btn btn-primary">Browse products</a>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $ord): ?>
                    <?php
                        $thumbs = array_filter(explode(',', (string)($ord['thumbs'] ?? '')));
                        $status = $ord['order_status'];
                    ?>
                    <article class="order-card">
                        <div class="order-card-head">
                            <div>
                                <p class="order-ref"><?= e($ord['order_number']) ?></p>
                                <p class="order-date"><?= date('j F Y', strtotime($ord['created_at'])) ?></p>
                            </div>
                            <span class="pill <?= $statusPill[$status] ?? 'pill-neutral' ?>"><?= e($status) ?></span>
                        </div>

                        <div class="order-card-body">
                            <div class="order-thumbs">
                                <?php foreach (array_slice($thumbs, 0, 4) as $t): ?>
                                    <img src="<?= getImageUrl(trim($t)) ?>" alt="" class="order-thumb" loading="lazy" width="52" height="52">
                                <?php endforeach; ?>
                                <?php if ((int)$ord['total_items'] > 4): ?>
                                    <span class="order-more">+<?= (int)$ord['total_items'] - 4 ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="row wrap" style="gap:var(--space-5);margin-top:var(--space-4)">
                                <div>
                                    <p class="t-xs t-subtle">Items</p>
                                    <p class="t-sm t-semi num"><?= (int)$ord['total_items'] ?></p>
                                </div>
                                <div>
                                    <p class="t-xs t-subtle">Total</p>
                                    <p class="t-sm t-semi num"><?= formatPrice($ord['total_amount']) ?></p>
                                </div>
                                <div>
                                    <p class="t-xs t-subtle">Payment</p>
                                    <p class="t-sm"><?= e($payLabel[$ord['payment_method']] ?? ucfirst($ord['payment_method'])) ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="order-card-foot">
                            <span class="t-xs t-subtle">
                                <?= $ord['payment_status'] === 'paid' ? 'Payment received' : 'Payment pending' ?>
                            </span>
                            <a href="<?= BASE_URL ?>/order.php?id=<?= (int)$ord['id'] ?>" class="btn btn-secondary btn-sm">
                                View order
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>

                <?= renderPagination($page, $totalPages, fn($p) => 'orders.php?page=' . $p) ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
