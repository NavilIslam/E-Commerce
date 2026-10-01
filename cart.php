<?php
$pageTitle = "Your cart";
require_once __DIR__ . '/includes/header.php';

$cart    = new Cart();
$details = $cart->getDetails();

$freeMin   = (float)($details['free_shipping_min'] ?? 0);
$remaining = max(0, $freeMin - (float)$details['subtotal']);
$progress  = $freeMin > 0 ? min(100, ((float)$details['subtotal'] / $freeMin) * 100) : 100;
?>

<div class="container page">
    <div class="page-head">
        <h1 class="page-title">Your cart</h1>
        <?php if (!empty($details['items'])): ?>
            <p class="page-sub"><span data-sum-count><?= (int)$details['item_count'] ?></span> item<?= $details['item_count'] === 1 ? '' : 's' ?></p>
        <?php endif; ?>
    </div>

    <?php if (empty($details['items'])): ?>
        <div class="empty">
            <div class="empty-icon"><?= icon('cart') ?></div>
            <h2 class="empty-title">Your cart is empty</h2>
            <p class="empty-text">Once you add something, it will show up here.</p>
            <a href="<?= BASE_URL ?>/products.php" class="btn btn-primary">Browse products</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">

            <div class="card">
                <?php foreach ($details['items'] as $item): ?>
                    <div class="cart-item" data-cart-row="<?= (int)$item['id'] ?>">
                        <a href="<?= BASE_URL ?>/product.php?slug=<?= urlencode($item['slug']) ?>" tabindex="-1" aria-hidden="true">
                            <img src="<?= getImageUrl($item['image']) ?>" class="cart-thumb" alt="<?= e($item['name']) ?>" width="88" height="88">
                        </a>

                        <div class="cart-info">
                            <a href="<?= BASE_URL ?>/product.php?slug=<?= urlencode($item['slug']) ?>" class="cart-name"><?= e($item['name']) ?></a>
                            <p class="cart-meta"><?= e($item['category_name']) ?> &middot; <?= e($item['sku'] ?? '') ?></p>

                            <?php if (!$item['is_in_stock']): ?>
                                <p class="cart-meta" style="color:var(--danger-text)">
                                    Only <?= (int)$item['stock_quantity'] ?> left in stock
                                </p>
                            <?php endif; ?>

                            <?php if ($item['offer_discount_unit'] > 0): ?>
                                <p class="cart-meta" style="color:var(--success)">
                                    Offer applied: &minus;<?= formatPrice($item['offer_discount_unit']) ?> each
                                </p>
                            <?php endif; ?>

                            <div class="cart-controls">
                                <div class="qty">
                                    <button type="button" class="qty-btn" data-qty-dec
                                            <?= $item['quantity'] <= 1 ? 'disabled' : '' ?>
                                            aria-label="Decrease quantity"><?= icon('minus') ?></button>
                                    <label class="sr-only" for="qty-<?= (int)$item['id'] ?>">Quantity for <?= e($item['name']) ?></label>
                                    <input type="number" class="qty-input" id="qty-<?= (int)$item['id'] ?>"
                                           value="<?= (int)$item['quantity'] ?>" min="1" max="<?= (int)$item['stock_quantity'] ?>">
                                    <button type="button" class="qty-btn" data-qty-inc
                                            <?= $item['quantity'] >= $item['stock_quantity'] ? 'disabled' : '' ?>
                                            aria-label="Increase quantity"><?= icon('plus') ?></button>
                                </div>

                                <button type="button" class="link-danger" data-cart-remove="<?= (int)$item['id'] ?>">
                                    <?= icon('trash') ?> Remove
                                </button>
                            </div>
                        </div>

                        <div class="cart-line">
                            <p class="cart-line-total" data-line-total><?= formatPrice($item['line_total']) ?></p>
                            <p class="cart-unit"><?= formatPrice($item['effective_unit_price']) ?> each</p>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div style="margin-top:var(--space-6)">
                    <a href="<?= BASE_URL ?>/products.php" class="btn btn-tertiary btn-sm">
                        <?= icon('chevron-l', 'icon-sm') ?> Continue shopping
                    </a>
                </div>
            </div>

            <div class="summary">
                <div class="card">
                    <h2 class="card-title" style="margin-bottom:var(--space-4)">Order summary</h2>

                    <!-- Free delivery progress -->
                    <div class="ship-progress<?= $details['is_free_shipping'] ? ' is-met' : '' ?>" data-ship-progress>
                        <p class="ship-progress-text">
                            <?= icon('truck') ?>
                            <span data-ship-text>
                                <?= $details['is_free_shipping']
                                    ? "You've qualified for free delivery"
                                    : 'Add ' . formatPrice($remaining) . ' more for free delivery' ?>
                            </span>
                        </p>
                        <div class="ship-bar">
                            <div class="ship-bar-fill" data-ship-fill style="width:<?= round($progress) ?>%"></div>
                        </div>
                    </div>

                    <div class="sum-row">
                        <span>Subtotal</span>
                        <span data-sum-items><?= formatPrice($details['items_subtotal']) ?></span>
                    </div>

                    <div class="sum-row is-credit" data-row-offer <?= $details['offer_discount'] > 0 ? '' : 'hidden' ?>>
                        <span>Offers</span>
                        <span data-sum-offer>&minus;<?= formatPrice($details['offer_discount']) ?></span>
                    </div>

                    <div class="sum-row is-credit" data-row-coupon <?= $details['coupon_discount'] > 0 ? '' : 'hidden' ?>>
                        <span>Coupon<?= !empty($details['coupon']) ? ' (' . e($details['coupon']['code']) . ')' : '' ?></span>
                        <span data-sum-coupon>&minus;<?= formatPrice($details['coupon_discount']) ?></span>
                    </div>

                    <div class="sum-row">
                        <span>Delivery</span>
                        <span data-sum-shipping>
                            <?php if ($details['is_free_shipping']): ?>
                                <strong style="color:var(--success)">Free</strong>
                            <?php else: ?>
                                <?= formatPrice($details['shipping_fee']) ?>
                            <?php endif; ?>
                        </span>
                    </div>

                    <div class="sum-total">
                        <span>Total</span>
                        <span data-sum-total><?= formatPrice($details['total_amount']) ?></span>
                    </div>

                    <a href="<?= BASE_URL ?>/checkout.php" class="btn btn-primary btn-lg btn-block" style="margin-top:var(--space-5)">
                        Checkout
                    </a>

                    <p class="t-xs t-subtle t-center" style="margin-top:var(--space-3);display:flex;align-items:center;justify-content:center;gap:var(--space-2)">
                        <?= icon('lock', 'icon-sm') ?> Your details are encrypted in transit
                    </p>
                </div>

                <!-- Coupon -->
                <div class="card" style="margin-top:var(--space-4)">
                    <h2 class="card-title" style="font-size:var(--text-base);margin-bottom:var(--space-3)">Promo code</h2>
                    <?php if (!empty($details['coupon'])): ?>
                        <div class="alert alert-success">
                            <?= icon('check') ?>
                            <span class="grow">
                                <strong><?= e($details['coupon']['code']) ?></strong> applied
                                (saved <?= formatPrice($details['coupon_discount']) ?>)
                            </span>
                            <button type="button" class="link-danger" data-coupon-remove aria-label="Remove coupon">
                                <?= icon('close', 'icon-sm') ?>
                            </button>
                        </div>
                    <?php else: ?>
                        <form id="coupon-form" class="row" style="gap:var(--space-2)">
                            <label class="sr-only" for="coupon-code">Promo code</label>
                            <input type="text" id="coupon-code" class="form-control grow" placeholder="Enter code" required
                                   style="text-transform:uppercase">
                            <button type="submit" class="btn btn-secondary">Apply</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
