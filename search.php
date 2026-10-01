<?php
$query     = trim($_GET['q'] ?? '');
$pageTitle = $query !== '' ? 'Search: ' . $query : 'Search';
require_once __DIR__ . '/includes/header.php';

$productModel = new Product();
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$sort    = $_GET['sort'] ?? 'relevance';

// Search results support the same sorting as the listing page so the two
// behave consistently.
$sortMap = ['relevance' => 'newest', 'newest' => 'newest', 'popular' => 'popular',
            'rating' => 'rating', 'price_low' => 'price_low', 'price_high' => 'price_high'];

$result = $productModel->getAll(
    ['search' => $query, 'is_active' => 1, 'sort' => $sortMap[$sort] ?? 'newest'],
    $page, $perPage
);
$products   = $result['products'];
$totalPages = $result['total_pages'];
$totalCount = $result['total'];

$categoryModel = new Category();
$suggestions   = array_slice($categoryModel->getAll(true), 0, 4);

function searchUrl(array $overrides = []): string {
    $params = array_merge($_GET, $overrides);
    $params = array_filter($params, fn($v) => $v !== null && $v !== '');
    return 'search.php?' . http_build_query($params);
}
?>

<div class="container page">
    <?php if ($query === ''): ?>
        <div class="empty">
            <div class="empty-icon"><?= icon('search') ?></div>
            <h1 class="empty-title">What are you looking for?</h1>
            <p class="empty-text">Search by product name, brand or category.</p>
            <a href="<?= BASE_URL ?>/products.php" class="btn btn-primary">Browse all products</a>
        </div>

    <?php else: ?>
        <div class="page-head">
            <h1 class="page-title">Results for &ldquo;<?= e($query) ?>&rdquo;</h1>
            <p class="page-sub num"><?= resultRange($page, $perPage, $totalCount) ?></p>
        </div>

        <?php if (empty($products)): ?>
            <div class="empty">
                <div class="empty-icon"><?= icon('search') ?></div>
                <h2 class="empty-title">No matches for &ldquo;<?= e($query) ?>&rdquo;</h2>
                <p class="empty-text">Check the spelling, or try a brand name or a broader term.</p>
                <a href="<?= BASE_URL ?>/products.php" class="btn btn-primary">Browse all products</a>

                <?php if (!empty($suggestions)): ?>
                    <div style="margin-top:var(--space-10)">
                        <p class="t-sm t-subtle" style="margin-bottom:var(--space-3)">Or start from a category</p>
                        <div class="chips" style="justify-content:center;margin-bottom:0">
                            <?php foreach ($suggestions as $cat): ?>
                                <a href="<?= BASE_URL ?>/products.php?category=<?= urlencode($cat['slug']) ?>" class="chip">
                                    <?= e($cat['name']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="listing-bar">
                <p class="t-sm t-subtle">Showing products matching your search</p>
                <form method="GET" class="row" style="gap:var(--space-2)">
                    <input type="hidden" name="q" value="<?= e($query) ?>">
                    <label for="sort" class="t-sm t-subtle">Sort</label>
                    <select id="sort" name="sort" class="form-control" style="width:auto;height:var(--control-sm)"
                            onchange="this.form.submit()">
                        <option value="relevance" <?= $sort === 'relevance' ? 'selected' : '' ?>>Relevance</option>
                        <option value="popular"   <?= $sort === 'popular' ? 'selected' : '' ?>>Most popular</option>
                        <option value="rating"    <?= $sort === 'rating' ? 'selected' : '' ?>>Best rated</option>
                        <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: low to high</option>
                        <option value="price_high"<?= $sort === 'price_high' ? 'selected' : '' ?>>Price: high to low</option>
                    </select>
                    <noscript><button type="submit" class="btn btn-secondary btn-sm">Go</button></noscript>
                </form>
            </div>

            <div class="product-grid">
                <?php foreach ($products as $prod): ?>
                    <?php include __DIR__ . '/includes/product-card.php'; ?>
                <?php endforeach; ?>
            </div>

            <?= renderPagination($page, $totalPages, fn($p) => searchUrl(['page' => $p])) ?>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
