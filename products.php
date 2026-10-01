<?php
require_once __DIR__ . '/config/config.php';

$categoryModel = new Category();
$categories    = $categoryModel->getAll(true);

$productModel = new Product();
$brands       = $productModel->getBrands();

$filters = [
    'is_active'  => 1,
    'category'   => $_GET['category'] ?? null,
    'brand'      => $_GET['brand'] ?? null,
    'min_price'  => $_GET['min_price'] ?? null,
    'max_price'  => $_GET['max_price'] ?? null,
    'on_sale'    => isset($_GET['sale']) ? 1 : null,
    'in_stock'   => isset($_GET['in_stock']) ? 1 : null,
    'featured'   => isset($_GET['featured']) ? 1 : null,
    'min_rating' => $_GET['rating'] ?? null,
    'search'     => $_GET['search'] ?? null,
    'sort'       => $_GET['sort'] ?? 'newest',
];

// Swap the range if the customer entered it backwards rather than silently
// returning nothing.
if ($filters['min_price'] !== null && $filters['max_price'] !== null
    && $filters['min_price'] !== '' && $filters['max_price'] !== ''
    && (float)$filters['min_price'] > (float)$filters['max_price']) {
    [$filters['min_price'], $filters['max_price']] = [$filters['max_price'], $filters['min_price']];
}

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;

$result     = $productModel->getAll($filters, $page, $perPage);
$products   = $result['products'];
$totalPages = $result['total_pages'];
$totalCount = $result['total'];

// Resolve the real category record so headings use the stored name, never a
// de-slugged approximation.
$activeCategory = null;
if (!empty($filters['category'])) {
    foreach ($categories as $c) {
        if ($c['slug'] === $filters['category']) { $activeCategory = $c; break; }
    }
}

if (isset($_GET['sale']))          { $pageTitle = 'Deals'; }
elseif (isset($_GET['featured']))  { $pageTitle = 'Featured products'; }
elseif ($activeCategory)           { $pageTitle = $activeCategory['name']; }
else                               { $pageTitle = 'All products'; }

$heading = $pageTitle;

function filterUrl(array $overrides = []): string {
    $params = array_merge($_GET, $overrides);
    $params = array_filter($params, fn($v) => $v !== null && $v !== '');
    return 'products.php' . (empty($params) ? '' : '?' . http_build_query($params));
}

// Chips describing exactly what is currently narrowing the list.
$chips = [];
if ($activeCategory)              $chips[] = ['label' => $activeCategory['name'], 'url' => filterUrl(['category' => null, 'page' => null])];
if (!empty($filters['brand']))    $chips[] = ['label' => $filters['brand'], 'url' => filterUrl(['brand' => null, 'page' => null])];
if (!empty($filters['on_sale']))  $chips[] = ['label' => 'On sale', 'url' => filterUrl(['sale' => null, 'page' => null])];
if (!empty($filters['in_stock'])) $chips[] = ['label' => 'In stock', 'url' => filterUrl(['in_stock' => null, 'page' => null])];
if ($filters['min_price'] !== null && $filters['min_price'] !== '')
    $chips[] = ['label' => 'From ' . formatPrice($filters['min_price']), 'url' => filterUrl(['min_price' => null, 'page' => null])];
if ($filters['max_price'] !== null && $filters['max_price'] !== '')
    $chips[] = ['label' => 'Up to ' . formatPrice($filters['max_price']), 'url' => filterUrl(['max_price' => null, 'page' => null])];

require_once __DIR__ . '/includes/header.php';
?>

<div class="container page">

    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="<?= BASE_URL ?>/index.php">Home</a>
        <?= icon('chevron-r') ?>
        <?php if ($activeCategory): ?>
            <a href="<?= BASE_URL ?>/products.php">Products</a>
            <?= icon('chevron-r') ?>
            <span aria-current="page"><?= e($activeCategory['name']) ?></span>
        <?php else: ?>
            <span aria-current="page"><?= e($heading) ?></span>
        <?php endif; ?>
    </nav>

    <div class="page-head">
        <h1 class="page-title"><?= e($heading) ?></h1>
        <?php if ($activeCategory && !empty($activeCategory['description'])): ?>
            <p class="page-sub"><?= e($activeCategory['description']) ?></p>
        <?php endif; ?>
    </div>

    <div class="listing">

        <!-- Filters: sidebar on desktop, slide-in sheet on mobile -->
        <aside class="filters" id="filters" aria-label="Filters">
            <div class="filter-panel">
                <div class="row-between" style="margin-bottom:var(--space-4)">
                    <h2 class="card-title">Filters</h2>
                    <button type="button" class="btn btn-tertiary btn-sm filter-close" id="filter-close"
                            style="display:none" aria-label="Close filters"><?= icon('close') ?></button>
                </div>

                <form method="GET" action="<?= BASE_URL ?>/products.php">
                    <?php foreach (['sort' => $filters['sort'], 'sale' => $_GET['sale'] ?? null, 'featured' => $_GET['featured'] ?? null] as $k => $v): ?>
                        <?php if ($v !== null && $v !== ''): ?>
                            <input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>

                    <div class="filter-group">
                        <p class="filter-legend">Category</p>
                        <div class="filter-options">
                            <a href="<?= filterUrl(['category' => null, 'page' => null]) ?>"
                               class="filter-option<?= empty($filters['category']) ? ' is-active' : '' ?>">
                                <span>All categories</span>
                            </a>
                            <?php foreach ($categories as $cat): ?>
                                <a href="<?= filterUrl(['category' => $cat['slug'], 'page' => null]) ?>"
                                   class="filter-option<?= ($filters['category'] === $cat['slug']) ? ' is-active' : '' ?>">
                                    <span><?= e($cat['name']) ?></span>
                                    <span class="count"><?= (int)$cat['product_count'] ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="filter-group">
                        <p class="filter-legend">Price (<?= CURRENCY_SYMBOL ?>)</p>
                        <div class="price-range">
                            <label class="sr-only" for="min_price">Minimum price</label>
                            <input type="number" min="0" id="min_price" name="min_price" placeholder="Min"
                                   value="<?= e($filters['min_price'] ?? '') ?>" class="form-control">
                            <span aria-hidden="true">&ndash;</span>
                            <label class="sr-only" for="max_price">Maximum price</label>
                            <input type="number" min="0" id="max_price" name="max_price" placeholder="Max"
                                   value="<?= e($filters['max_price'] ?? '') ?>" class="form-control">
                        </div>
                    </div>

                    <?php if (!empty($brands)): ?>
                        <div class="filter-group">
                            <label class="filter-legend" for="brand">Brand</label>
                            <select name="brand" id="brand" class="form-control">
                                <option value="">All brands</option>
                                <?php foreach ($brands as $b): ?>
                                    <option value="<?= e($b['brand']) ?>" <?= ($filters['brand'] === $b['brand']) ? 'selected' : '' ?>>
                                        <?= e($b['brand']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="filter-group">
                        <p class="filter-legend">Availability</p>
                        <div class="filter-checks">
                            <label class="check">
                                <input type="checkbox" name="sale" value="1" <?= !empty($filters['on_sale']) ? 'checked' : '' ?>>
                                <span>On sale</span>
                            </label>
                            <label class="check">
                                <input type="checkbox" name="in_stock" value="1" <?= !empty($filters['in_stock']) ? 'checked' : '' ?>>
                                <span>In stock only</span>
                            </label>
                        </div>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary btn-block">Apply filters</button>
                        <?php if (!empty($chips)): ?>
                            <a href="<?= BASE_URL ?>/products.php" class="btn btn-tertiary btn-block">Clear all filters</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </aside>

        <div>
            <div class="listing-bar">
                <div class="row" style="gap:var(--space-3)">
                    <button type="button" class="btn btn-secondary btn-sm filter-open" id="filter-open"
                            aria-expanded="false" aria-controls="filters">
                        <?= icon('sliders', 'icon-sm') ?> Filters
                        <?php if (!empty($chips)): ?><span class="badge badge-featured"><?= count($chips) ?></span><?php endif; ?>
                    </button>
                    <p class="t-sm t-subtle num"><?= resultRange($page, $perPage, $totalCount) ?></p>
                </div>

                <form method="GET" class="row" style="gap:var(--space-2)">
                    <?php foreach ($_GET as $k => $v): if ($k !== 'sort' && $k !== 'page' && $v !== ''): ?>
                        <input type="hidden" name="<?= e($k) ?>" value="<?= e(is_array($v) ? '' : $v) ?>">
                    <?php endif; endforeach; ?>
                    <label for="sort" class="t-sm t-subtle">Sort</label>
                    <select id="sort" name="sort" class="form-control btn-sm" style="width:auto;height:var(--control-sm)"
                            onchange="this.form.submit()">
                        <option value="newest"     <?= $filters['sort'] === 'newest' ? 'selected' : '' ?>>Newest</option>
                        <option value="popular"    <?= $filters['sort'] === 'popular' ? 'selected' : '' ?>>Most popular</option>
                        <option value="rating"     <?= $filters['sort'] === 'rating' ? 'selected' : '' ?>>Best rated</option>
                        <option value="price_low"  <?= $filters['sort'] === 'price_low' ? 'selected' : '' ?>>Price: low to high</option>
                        <option value="price_high" <?= $filters['sort'] === 'price_high' ? 'selected' : '' ?>>Price: high to low</option>
                    </select>
                    <noscript><button type="submit" class="btn btn-secondary btn-sm">Go</button></noscript>
                </form>
            </div>

            <?php if (!empty($chips)): ?>
                <div class="chips">
                    <?php foreach ($chips as $chip): ?>
                        <span class="chip">
                            <?= e($chip['label']) ?>
                            <a href="<?= e($chip['url']) ?>" class="chip-remove" aria-label="Remove filter <?= e($chip['label']) ?>">
                                <?= icon('close', 'icon-sm') ?>
                            </a>
                        </span>
                    <?php endforeach; ?>
                    <a href="<?= BASE_URL ?>/products.php" class="chip-clear">Clear all</a>
                </div>
            <?php endif; ?>

            <?php if (empty($products)): ?>
                <div class="empty">
                    <div class="empty-icon"><?= icon('search') ?></div>
                    <h2 class="empty-title">No products match these filters</h2>
                    <p class="empty-text">Try widening your price range or removing a filter.</p>
                    <a href="<?= BASE_URL ?>/products.php" class="btn btn-primary">Clear filters</a>
                </div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($products as $prod): ?>
                        <?php include __DIR__ . '/includes/product-card.php'; ?>
                    <?php endforeach; ?>
                </div>

                <?= renderPagination($page, $totalPages, fn($p) => filterUrl(['page' => $p])) ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
