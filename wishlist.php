<?php
$pageTitle   = "Your wishlist";
$accountPage = 'wishlist';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/header.php';

$wishlist = new Wishlist();
$items    = $wishlist->getItems();

// On this page the heart means "remove" — see includes/product-card.php.
$cardWishlistMode = 'remove';
?>

<div class="container page">
    <div class="page-head">
        <h1 class="page-title">Your wishlist</h1>
        <?php if (!empty($items)): ?>
            <p class="page-sub"><?= count($items) ?> saved item<?= count($items) === 1 ? '' : 's' ?></p>
        <?php endif; ?>
    </div>

    <div class="account-layout">
        <?php require __DIR__ . '/includes/account-nav.php'; ?>

        <div>
            <?php if (empty($items)): ?>
                <div class="empty">
                    <div class="empty-icon"><?= icon('heart') ?></div>
                    <h2 class="empty-title">Nothing saved yet</h2>
                    <p class="empty-text">Tap the heart on any product to keep it here for later.</p>
                    <a href="<?= BASE_URL ?>/products.php" class="btn btn-primary">Browse products</a>
                </div>
            <?php else: ?>
                <?php
                    $inStockIds = array_values(array_filter(array_map(
                        fn($p) => (int)$p['stock_quantity'] > 0 ? (int)$p['id'] : null,
                        $items
                    )));
                ?>
                <?php if (!empty($inStockIds)): ?>
                    <div class="row-between" style="margin-bottom:var(--space-5)">
                        <p class="t-sm t-subtle"><?= count($inStockIds) ?> available to buy</p>
                        <button type="button" class="btn btn-secondary btn-sm" id="add-all"
                                data-ids="<?= e(implode(',', $inStockIds)) ?>">
                            <?= icon('cart', 'icon-sm') ?> Add all to cart
                        </button>
                    </div>
                <?php endif; ?>

                <div class="product-grid">
                    <?php foreach ($items as $prod): ?>
                        <?php include __DIR__ . '/includes/product-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const addAll = document.getElementById('add-all');
if (addAll) {
    addAll.addEventListener('click', async () => {
        const ids = addAll.dataset.ids.split(',').filter(Boolean);
        setBusy(addAll, true);
        let added = 0;
        for (const id of ids) {
            try {
                const res = await apiFetch(`${window.BASE_URL}/api/cart/add.php`, {
                    method: 'POST',
                    body: { product_id: parseInt(id, 10), quantity: 1 },
                });
                added++;
                if (res.data) updateCartCount(res.data.item_count);
            } catch (e) { /* per-item errors already surfaced */ }
        }
        setBusy(addAll, false);
        if (added) showToast(`${added} item${added === 1 ? '' : 's'} added to your cart`, 'success');
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
