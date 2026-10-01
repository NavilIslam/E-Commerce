<?php
/**
 * Professional Product Card (Star Tech style)
 * Clean, modern, high-conversion, non-AI aesthetic.
 */
$hasSale        = !empty($prod['sale_price']) && (float)$prod['sale_price'] < (float)$prod['price'];
$displayPrice   = $hasSale ? $prod['sale_price'] : $prod['price'];
$inStock        = (int)($prod['stock_quantity'] ?? 0) > 0;
$badge          = productBadge($prod, !empty($cardSuppressFeatured));
$productUrl     = BASE_URL . '/product.php?slug=' . urlencode($prod['slug']);
$wishlistMode   = $cardWishlistMode ?? 'toggle';

static $wishlistModel = null;
$isSaved = false;
if (Auth::check()) {
    if ($wishlistModel === null) $wishlistModel = new Wishlist();
    $isSaved = $wishlistMode === 'remove' ? true : $wishlistModel->isInWishlist((int)$prod['id']);
}
?>
<article class="product-card" <?= $wishlistMode === 'remove' ? 'data-wishlist-item' : '' ?>>
    <!-- Top Tags & Wishlist Button -->
    <div class="product-card-topbar">
        <?php if ($hasSale): ?>
            <?php 
            $diff = (float)$prod['price'] - (float)$prod['sale_price'];
            $disc = (int)round(($diff / (float)$prod['price']) * 100); 
            ?>
            <span class="badge-startech-save">Save: <?= formatPrice($diff) ?> (-<?= $disc ?>%)</span>
        <?php elseif ($badge): ?>
            <span class="badge-startech-tag"><?= e($badge['label']) ?></span>
        <?php else: ?>
            <span></span>
        <?php endif; ?>

        <?php if ($wishlistMode === 'remove'): ?>
            <button type="button" class="wish-btn-clean is-active"
                    data-wishlist-remove="<?= (int)$prod['id'] ?>"
                    aria-label="Remove <?= e($prod['name']) ?> from wishlist" title="Remove">
                <?= icon('trash', 'icon-sm') ?>
            </button>
        <?php else: ?>
            <button type="button" class="wish-btn-clean<?= $isSaved ? ' is-active' : '' ?>"
                    data-wishlist-toggle="<?= (int)$prod['id'] ?>"
                    aria-pressed="<?= $isSaved ? 'true' : 'false' ?>"
                    aria-label="<?= $isSaved ? 'Remove from wishlist' : 'Save to wishlist' ?>" title="Wishlist">
                <?= icon('heart', 'icon-sm') ?>
            </button>
        <?php endif; ?>
    </div>

    <!-- Product Image Container -->
    <div class="product-media">
        <a href="<?= $productUrl ?>" tabindex="-1" aria-hidden="true" class="product-img-link">
            <img src="<?= getImageUrl($prod['primary_image'] ?? null) ?>"
                 alt="<?= e($prod['name']) ?>" loading="lazy" width="300" height="300"
                 class="product-main-img">
        </a>
    </div>

    <!-- Product Details -->
    <div class="product-body">
        <?php if (!empty($prod['category_name'])): ?>
            <span class="product-cat-tag"><?= e($prod['category_name']) ?></span>
        <?php endif; ?>

        <a href="<?= $productUrl ?>" class="product-title-link">
            <h3 class="product-name"><?= e($prod['name']) ?></h3>
        </a>

        <!-- Rating & Review Count -->
        <div class="product-rating-row">
            <?php if ((int)($prod['review_count'] ?? 0) > 0): ?>
                <span class="star-glyph"><?= icon('star', 'icon-xs') ?></span>
                <span class="rating-num"><?= number_format((float)($prod['avg_rating'] ?? 5.0), 1) ?></span>
                <span class="rating-sep">&middot;</span>
                <span class="review-count"><?= (int)$prod['review_count'] ?> <?= (int)$prod['review_count'] === 1 ? 'review' : 'reviews' ?></span>
            <?php else: ?>
                <span class="review-count" style="color:var(--text-subtle);font-size:11px;">No reviews yet</span>
            <?php endif; ?>
        </div>

        <!-- Pricing (Crimson/Red Current Price like Star Tech) -->
        <div class="product-price">
            <span class="price-now"><?= formatPrice($displayPrice) ?></span>
            <?php if ($hasSale): ?>
                <span class="price-was"><?= formatPrice($prod['price']) ?></span>
            <?php endif; ?>
        </div>

        <!-- Action Bar: Clean Add to Cart & Quick View -->
        <div class="product-actions-bar">
            <?php if ($inStock): ?>
                <button type="button" class="btn-card-buy" data-add-to-cart="<?= (int)$prod['id'] ?>">
                    <?= icon('cart', 'icon-sm') ?> Add to cart
                </button>
            <?php else: ?>
                <button type="button" class="btn-card-buy is-out" disabled>Out of stock</button>
            <?php endif; ?>

            <button type="button" class="btn-card-quickview" data-quick-view="<?= (int)$prod['id'] ?>" title="Quick view" aria-label="Quick view <?= e($prod['name']) ?>">
                <?= icon('eye', 'icon-sm') ?>
            </button>
        </div>
    </div>
</article>
