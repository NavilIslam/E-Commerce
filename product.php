<?php
require_once __DIR__ . '/config/config.php';

$slug = $_GET['slug'] ?? '';
$id   = (int)($_GET['id'] ?? 0);

$productModel = new Product();
$product = null;

if (!empty($slug)) {
    $product = $productModel->findBySlug($slug);
} elseif ($id > 0) {
    $product = $productModel->findById($id, true);
    if ($product) $productModel->incrementViews($product['id']);
}

if (!$product || empty($product['is_active'])) {
    http_response_code(404);
    $pageTitle = "Product not found";
    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="container page">
        <div class="empty">
            <div class="empty-icon"><?= icon('package') ?></div>
            <h1 class="empty-title">We couldn't find that product</h1>
            <p class="empty-text">It may have been removed or the link may be out of date.</p>
            <a href="<?= BASE_URL ?>/products.php" class="btn btn-primary">Browse products</a>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle       = $product['name'];
$metaDescription = truncateText(strip_tags($product['short_description'] ?? $product['description'] ?? ''), 155);

$offerModel     = new Offer();
$basePrice      = !empty($product['sale_price']) && $product['sale_price'] > 0 ? (float)$product['sale_price'] : (float)$product['price'];
$offerDiscount  = $offerModel->calculateItemDiscount($product, $basePrice);
$effectivePrice = max(0, $basePrice - $offerDiscount);
$listPrice      = (float)$product['price'];
$hasDiscount    = $effectivePrice < $listPrice;
$discountPct    = $hasDiscount ? (int)round((($listPrice - $effectivePrice) / $listPrice) * 100) : 0;

$images    = $product['images'] ?? [];
$mainImage = !empty($images) ? $images[0]['image_path'] : null;

$reviewModel = new Review();
$reviews     = $reviewModel->getProductReviews($product['id'], true);
$canReview   = Auth::check()
    ? $reviewModel->canUserReview(Auth::id(), $product['id'])
    : ['allowed' => false, 'message' => 'Sign in to write a review'];

// Rating distribution from the reviews we already loaded.
$dist = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
foreach ($reviews as $r) {
    $k = (int)$r['rating'];
    if (isset($dist[$k])) $dist[$k]++;
}
$reviewTotal = max(1, count($reviews));

$relatedProducts = $productModel->getRelatedProducts($product['id'], $product['category_id'], 4);

$stock     = (int)$product['stock_quantity'];
$lowStock  = (int)$product['low_stock_threshold'];
$inWish    = Auth::check() ? (new Wishlist())->isInWishlist((int)$product['id']) : false;

// Specifications built strictly from stored columns — nothing invented.
$specs = array_filter([
    'Brand'    => $product['brand'] ?? null,
    'Category' => $product['category_name'] ?? null,
    'SKU'      => $product['sku'] ?? null,
    'Weight'   => !empty($product['weight']) ? rtrim(rtrim(number_format((float)$product['weight'], 2), '0'), '.') . ' kg' : null,
]);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="<?= BASE_URL ?>/index.php">Home</a>
        <?= icon('chevron-r') ?>
        <?php if (!empty($product['category_name'])): ?>
            <a href="<?= BASE_URL ?>/products.php?category=<?= urlencode($product['category_slug'] ?? '') ?>"><?= e($product['category_name']) ?></a>
            <?= icon('chevron-r') ?>
        <?php endif; ?>
        <span aria-current="page"><?= e($product['name']) ?></span>
    </nav>

    <div class="pdp">
        <!-- Gallery -->
        <div>
            <div class="gallery-main">
                <img id="gallery-image" src="<?= getImageUrl($mainImage) ?>" alt="<?= e($product['name']) ?>" width="600" height="600">
            </div>
            <?php if (count($images) > 1): ?>
                <div class="gallery-thumbs" role="tablist" aria-label="Product images">
                    <?php foreach ($images as $idx => $img): ?>
                        <button type="button" role="tab"
                                class="gallery-thumb<?= $idx === 0 ? ' is-active' : '' ?>"
                                aria-selected="<?= $idx === 0 ? 'true' : 'false' ?>"
                                aria-label="View image <?= $idx + 1 ?>"
                                onclick="switchProductImage('<?= getImageUrl($img['image_path']) ?>', this)">
                            <img src="<?= getImageUrl($img['image_path']) ?>" alt="" loading="lazy" width="72" height="72">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Buy box -->
        <div>
            <?php if (!empty($product['brand'])): ?>
                <p class="pdp-brand"><strong><?= e($product['brand']) ?></strong></p>
            <?php endif; ?>

            <h1 class="pdp-title"><?= e($product['name']) ?></h1>

            <div class="pdp-meta">
                <?php if ((int)$product['review_count'] > 0): ?>
                    <a href="#reviews" class="row" style="gap:var(--space-2)">
                        <?= ratingStars((float)$product['avg_rating'], (int)$product['review_count'], 'lg') ?>
                    </a>
                <?php else: ?>
                    <span class="t-subtle">No reviews yet</span>
                <?php endif; ?>
                <?php if ((int)$product['total_sold'] > 0): ?>
                    <span><?= (int)$product['total_sold'] ?> sold</span>
                <?php endif; ?>
            </div>

            <div class="pdp-price-box">
                <div class="price price-xl">
                    <span class="price-now"><?= formatPrice($effectivePrice) ?></span>
                    <?php if ($hasDiscount): ?>
                        <span class="price-was"><?= formatPrice($listPrice) ?></span>
                        <span class="price-off">Save <?= formatPrice($listPrice - $effectivePrice) ?> (<?= $discountPct ?>%)</span>
                    <?php endif; ?>
                </div>

                <div style="margin-top:var(--space-3)">
                    <?php if ($stock > $lowStock): ?>
                        <span class="stock stock-in"><?= icon('check') ?> In stock</span>
                    <?php elseif ($stock > 0): ?>
                        <span class="stock stock-low"><?= icon('alert') ?> Only <?= $stock ?> left</span>
                    <?php else: ?>
                        <span class="stock stock-out"><?= icon('close') ?> Out of stock</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($product['short_description'])): ?>
                <p class="t-muted" style="line-height:1.7"><?= nl2br(e($product['short_description'])) ?></p>
            <?php endif; ?>

            <?php if ($stock > 0): ?>
                <div class="pdp-actions">
                    <div class="qty qty-lg">
                        <button type="button" class="qty-btn" data-pdp-qty="-1" aria-label="Decrease quantity"><?= icon('minus') ?></button>
                        <label class="sr-only" for="product-qty">Quantity</label>
                        <input type="number" id="product-qty" class="qty-input" value="1" min="1" max="<?= $stock ?>">
                        <button type="button" class="qty-btn" data-pdp-qty="1" aria-label="Increase quantity"><?= icon('plus') ?></button>
                    </div>

                    <button type="button" class="btn btn-primary btn-lg" data-add-to-cart="<?= (int)$product['id'] ?>">
                        <?= icon('cart') ?> Add to cart
                    </button>

                    <button type="button" class="btn btn-secondary btn-lg btn-icon wish-inline<?= $inWish ? ' is-active' : '' ?>"
                            data-wishlist-toggle="<?= (int)$product['id'] ?>"
                            aria-pressed="<?= $inWish ? 'true' : 'false' ?>"
                            aria-label="<?= $inWish ? 'Remove from wishlist' : 'Save to wishlist' ?>">
                        <?= icon('heart') ?>
                    </button>
                </div>

                <button type="button" class="btn btn-dark btn-lg btn-block" id="buy-now" data-product="<?= (int)$product['id'] ?>">
                    Buy now
                </button>
            <?php else: ?>
                <div class="alert alert-warning" style="margin:var(--space-6) 0">
                    <?= icon('alert') ?>
                    <span>This product is currently out of stock.</span>
                </div>
            <?php endif; ?>

            <div class="delivery-facts" style="margin-top:var(--space-6)">
                <div class="delivery-fact">
                    <?= icon('truck') ?>
                    <span>
                        <strong>Delivery:</strong> Free over <?= formatPrice(getSetting('free_shipping_threshold', '2000')) ?>, standard delivery <?= formatPrice(getSetting('shipping_inside_city', '60')) ?>
                    </span>
                </div>
                <div class="delivery-fact">
                    <?= icon('refresh') ?>
                    <span><strong>Returns:</strong> 7 days replacement for damaged or defective items</span>
                </div>
                <div class="delivery-fact">
                    <?= icon('wallet') ?>
                    <span><strong>Payment:</strong> Cash on delivery and bKash / cards accepted</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Description & specifications -->
    <div class="section">
        <div class="card card-lg">
            <div class="stack-6">
                <?php if (!empty($product['description'])): ?>
                    <div>
                        <h2 class="card-title" style="margin-bottom:var(--space-3)">Description</h2>
                        <div class="prose"><?= nl2br(e($product['description'])) ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($specs)): ?>
                    <div>
                        <h2 class="card-title" style="margin-bottom:var(--space-3)">Specifications</h2>
                        <table class="spec-table">
                            <tbody>
                            <?php foreach ($specs as $label => $value): ?>
                                <tr>
                                    <th scope="row"><?= e($label) ?></th>
                                    <td><?= e($value) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Reviews -->
    <div class="section" id="reviews">
        <div class="card card-lg">
            <h2 class="card-title" style="margin-bottom:var(--space-5)">
                Reviews <?= count($reviews) > 0 ? '(' . count($reviews) . ')' : '' ?>
            </h2>

            <?php if (count($reviews) > 0): ?>
                <div class="review-summary" style="margin-bottom:var(--space-8)">
                    <div class="review-score">
                        <p class="review-score-num"><?= number_format((float)$product['avg_rating'], 1) ?></p>
                        <?= ratingStars((float)$product['avg_rating'], null, 'lg') ?>
                        <p class="t-xs t-subtle" style="margin-top:var(--space-2)"><?= count($reviews) ?> review<?= count($reviews) === 1 ? '' : 's' ?></p>
                    </div>
                    <div class="review-bars">
                        <?php foreach ([5,4,3,2,1] as $starVal): ?>
                            <div class="review-bar-row">
                                <span><?= $starVal ?> star<?= $starVal === 1 ? '' : 's' ?></span>
                                <span class="review-bar">
                                    <span class="review-bar-fill" style="width:<?= round(($dist[$starVal] / $reviewTotal) * 100) ?>%"></span>
                                </span>
                                <span class="num"><?= $dist[$starVal] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($canReview['allowed']): ?>
                <form id="review-form" class="card" style="background:var(--bg-subtle);border:none;margin-bottom:var(--space-6)">
                    <h3 class="t-semi" style="margin-bottom:var(--space-4)">Write a review</h3>

                    <div class="form-group">
                        <span class="form-label" id="rating-label">Your rating</span>
                        <div class="star-input" role="radiogroup" aria-labelledby="rating-label">
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                <button type="button" role="radio" aria-checked="<?= $s === 5 ? 'true' : 'false' ?>"
                                        data-star="<?= $s ?>" class="<?= $s <= 5 ? 'is-on' : '' ?>"
                                        aria-label="<?= $s ?> star<?= $s === 1 ? '' : 's' ?>">
                                    <?= icon('star') ?>
                                </button>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" id="review-rating" value="5">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="review-comment">Your review</label>
                        <textarea id="review-comment" rows="3" class="form-control" required
                                  placeholder="What did you think of this product?"></textarea>
                        <p class="form-error" id="err-review" hidden></p>
                    </div>

                    <button type="submit" class="btn btn-primary">Submit review</button>
                </form>
            <?php elseif (!Auth::check()): ?>
                <div class="alert alert-info" style="margin-bottom:var(--space-6)">
                    <?= icon('info') ?>
                    <span><a href="<?= BASE_URL ?>/login.php?redirect=<?= urlencode(BASE_URL . '/product.php?slug=' . $product['slug']) ?>" style="font-weight:600">Sign in</a> to write a review.</span>
                </div>
            <?php endif; ?>

            <?php if (empty($reviews)): ?>
                <p class="t-sm t-subtle">No reviews yet. Be the first to review this product.</p>
            <?php else: ?>
                <div>
                    <?php foreach ($reviews as $rev): ?>
                        <article class="review-entry">
                            <div class="row-between">
                                <p class="review-author">
                                    <?= e($rev['first_name'] . ' ' . mb_substr($rev['last_name'], 0, 1) . '.') ?>
                                    <?php if (!empty($rev['is_verified_buyer'])): ?>
                                        <span class="pill pill-success"><?= icon('check', 'icon-sm') ?> Verified buyer</span>
                                    <?php endif; ?>
                                </p>
                                <span class="t-xs t-subtle"><?= timeAgo($rev['created_at']) ?></span>
                            </div>
                            <div style="margin-top:var(--space-2)">
                                <?= ratingStars((float)$rev['rating']) ?>
                            </div>
                            <p class="review-body"><?= nl2br(e($rev['comment'])) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Related -->
    <?php if (!empty($relatedProducts)): ?>
        <section class="section">
            <div class="section-head">
                <h2 class="section-title">More in <?= e($product['category_name'] ?? 'this category') ?></h2>
                <a href="<?= BASE_URL ?>/products.php?category=<?= urlencode($product['category_slug'] ?? '') ?>" class="btn btn-tertiary btn-sm">
                    See all <?= icon('chevron-r', 'icon-sm') ?>
                </a>
            </div>
            <div class="product-grid product-grid-4">
                <?php foreach ($relatedProducts as $prod): ?>
                    <?php include __DIR__ . '/includes/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<script>
// Buy now: add to cart, then go straight to checkout.
const buyNow = document.getElementById('buy-now');
if (buyNow) {
    buyNow.addEventListener('click', async () => {
        setBusy(buyNow, true);
        const qty = parseInt(document.getElementById('product-qty').value, 10) || 1;
        try {
            await apiFetch(`${window.BASE_URL}/api/cart/add.php`, {
                method: 'POST',
                body: { product_id: <?= (int)$product['id'] ?>, quantity: qty },
            });
            window.location.href = `${window.BASE_URL}/checkout.php`;
        } catch (e) {
            setBusy(buyNow, false);
        }
    });
}

// Star picker
document.querySelectorAll('.star-input [data-star]').forEach((btn) => {
    btn.addEventListener('click', () => {
        const value = parseInt(btn.dataset.star, 10);
        document.getElementById('review-rating').value = value;
        document.querySelectorAll('.star-input [data-star]').forEach((b) => {
            const on = parseInt(b.dataset.star, 10) <= value;
            b.classList.toggle('is-on', on);
            b.setAttribute('aria-checked', parseInt(b.dataset.star, 10) === value ? 'true' : 'false');
        });
    });
});

const reviewForm = document.getElementById('review-form');
if (reviewForm) {
    reviewForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const comment = document.getElementById('review-comment');
        const err = document.getElementById('err-review');

        if (!comment.value.trim()) {
            comment.classList.add('has-error');
            err.textContent = 'Write a few words about the product first';
            err.hidden = false;
            comment.focus();
            return;
        }
        comment.classList.remove('has-error');
        err.hidden = true;

        const btn = reviewForm.querySelector('button[type="submit"]');
        setBusy(btn, true);
        try {
            const res = await apiFetch(`${window.BASE_URL}/api/reviews/create.php`, {
                method: 'POST',
                body: {
                    product_id: <?= (int)$product['id'] ?>,
                    rating: parseInt(document.getElementById('review-rating').value, 10),
                    comment: comment.value.trim(),
                },
            });
            showToast(res.message, 'success');
            setTimeout(() => location.reload(), 900);
        } catch (err2) {
            setBusy(btn, false);
        }
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
