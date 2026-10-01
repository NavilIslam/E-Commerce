<?php
$pageTitle = "Categories";
$metaDescription = "Browse NovaMart departments: electronics, fashion, home, beauty, sports and books.";
require_once __DIR__ . '/includes/header.php';

$categoryModel = new Category();
$categories    = $categoryModel->getAll(true);
$showCategoryDesc = true;
?>

<div class="container page">
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="<?= BASE_URL ?>/index.php">Home</a>
        <?= icon('chevron-r') ?>
        <span aria-current="page">Categories</span>
    </nav>

    <div class="page-head">
        <h1 class="page-title">Categories</h1>
        <p class="page-sub"><?= count($categories) ?> departments, <?= array_sum(array_column($categories, 'product_count')) ?> products</p>
    </div>

    <?php if (empty($categories)): ?>
        <div class="empty">
            <div class="empty-icon"><?= icon('grid') ?></div>
            <h2 class="empty-title">No categories yet</h2>
            <p class="empty-text">Check back soon.</p>
            <a href="<?= BASE_URL ?>/products.php" class="btn btn-primary">Browse products</a>
        </div>
    <?php else: ?>
        <div class="category-grid" style="grid-template-columns:repeat(auto-fill,minmax(230px,1fr))">
            <?php foreach ($categories as $cat): ?>
                <?php include __DIR__ . '/includes/category-card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
