<?php
/**
 * Category card. Expects $cat.
 * Uses the category's own image when one is uploaded, otherwise falls back to a
 * real photo of a product in that category (see Category::getAll), and finally
 * to a tinted initial tile. Never a generic folder glyph.
 */
$catImage = null;
if (!empty($cat['image']) && hasImage($cat['image'], 'categories')) {
    $catImage = getImageUrl($cat['image'], 'categories');
} elseif (!empty($cat['preview_image']) && hasImage($cat['preview_image'], 'products')) {
    $catImage = getImageUrl($cat['preview_image'], 'products');
}
$showDesc = !empty($showCategoryDesc) && !empty($cat['description']);
?>
<a href="<?= BASE_URL ?>/products.php?category=<?= urlencode($cat['slug']) ?>" class="category-card">
    <?php if ($catImage): ?>
        <div class="category-media">
            <img src="<?= $catImage ?>" alt="<?= e($cat['name']) ?>" loading="lazy" width="300" height="225">
        </div>
    <?php else: ?>
        <div class="category-media is-empty" aria-hidden="true"><?= e(mb_substr($cat['name'], 0, 1)) ?></div>
    <?php endif; ?>
    <div class="category-body">
        <p class="category-name"><?= e($cat['name']) ?></p>
        <p class="category-count"><?= (int)$cat['product_count'] ?> <?= (int)$cat['product_count'] === 1 ? 'product' : 'products' ?></p>
        <?php if ($showDesc): ?>
            <p class="category-desc"><?= e(truncateText($cat['description'], 80)) ?></p>
        <?php endif; ?>
    </div>
</a>
