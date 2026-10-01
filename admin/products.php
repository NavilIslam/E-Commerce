<?php
$pageTitle = "Product Management";
require_once __DIR__ . '/includes/admin-header.php';

$productModel = new Product();
$categoryModel = new Category();
$categories = $categoryModel->getAll(false);

// Handle Delete
if (isset($_GET['delete'])) {
    CSRF::requireValid();
    $pid = (int)$_GET['delete'];
    $productModel->delete($pid);
    setFlash('success', 'Product deleted or deactivated.');
    redirect(ADMIN_URL . '/products.php');
}

// Handle Status Toggle
if (isset($_GET['toggle_status'])) {
    CSRF::requireValid();
    $pid = (int)$_GET['toggle_status'];
    $p = $productModel->findById($pid);
    if ($p) {
        $newStatus = $p['is_active'] ? 0 : 1;
        (new Database)->getInstance()->update('products', ['is_active' => $newStatus], 'id = :id', [':id' => $pid]);
        AdminLog::log('PRODUCT_STATUS_TOGGLE', 'product', $pid, "Changed status to " . ($newStatus ? 'active' : 'inactive'));
        setFlash('success', 'Product status updated.');
    }
    redirect(ADMIN_URL . '/products.php');
}

$filters = [
    'search'       => $_GET['q'] ?? null,
    'category_id'  => $_GET['category_id'] ?? null,
    'stock_status' => $_GET['stock'] ?? null,
    'is_active'    => isset($_GET['status']) && $_GET['status'] !== '' ? (int)$_GET['status'] : null,
];

$page = max(1, (int)($_GET['page'] ?? 1));
$result = $productModel->getAll($filters, $page, 15);
$products = $result['products'];
$totalPages = $result['total_pages'];
$total = $result['total'];
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
    <div>
        <h1 style="font-size:1.5rem;font-weight:700;">Products</h1>
        <p style="color:var(--text-muted);font-size:0.825rem;">Total catalog count: <?= $total ?> products</p>
    </div>
    <a href="<?= ADMIN_URL ?>/add-product.php" class="btn btn-primary btn-sm">
        + Add Product
    </a>
</div>

<!-- Search & Filter Bar -->
<div class="admin-card" style="padding:1rem;">
    <form method="GET" style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center;">
        <input type="text" name="q" placeholder="Search by name, SKU, brand..." value="<?= e($filters['search'] ?? '') ?>" class="form-control" style="max-width:260px;">

        <select name="category_id" class="form-control" style="max-width:180px;">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="stock" class="form-control" style="max-width:160px;">
            <option value="">All Stock Levels</option>
            <option value="in_stock" <?= ($filters['stock_status'] === 'in_stock') ? 'selected' : '' ?>>In Stock</option>
            <option value="low_stock" <?= ($filters['stock_status'] === 'low_stock') ? 'selected' : '' ?>>Low Stock</option>
            <option value="out_of_stock" <?= ($filters['stock_status'] === 'out_of_stock') ? 'selected' : '' ?>>Out of Stock</option>
        </select>

        <select name="status" class="form-control" style="max-width:140px;">
            <option value="">All Statuses</option>
            <option value="1" <?= ($filters['is_active'] === 1) ? 'selected' : '' ?>>Active</option>
            <option value="0" <?= ($filters['is_active'] === 0) ? 'selected' : '' ?>>Disabled</option>
        </select>

        <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
        <a href="<?= ADMIN_URL ?>/products.php" class="btn btn-outline btn-sm">Reset</a>
    </form>
</div>

<!-- Products Table -->
<div class="admin-card" style="padding:0;overflow:hidden;">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Sold</th>
                <th>Status</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr><td colspan="7" style="text-align:center;padding:3rem;">No products match the selected criteria.</td></tr>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:0.75rem;">
                                <img src="<?= getImageUrl($p['primary_image']) ?>" style="width:40px;height:40px;object-fit:cover;border:1px solid var(--admin-border);" alt="">
                                <div>
                                    <a href="<?= ADMIN_URL ?>/edit-product.php?id=<?= $p['id'] ?>" style="font-weight:700;color:var(--text-dark);">
                                        <?= e($p['name']) ?>
                                    </a>
                                    <div style="font-size:0.75rem;color:var(--text-muted);">SKU: <?= e($p['sku'] ?? 'N/A') ?> | <?= e($p['brand'] ?? 'No Brand') ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?= e($p['category_name'] ?? 'Uncategorized') ?></td>
                        <td>
                            <div style="font-weight:700;"><?= formatPrice(!empty($p['sale_price']) ? $p['sale_price'] : $p['price']) ?></div>
                            <?php if (!empty($p['sale_price']) && (float)$p['sale_price'] < (float)$p['price']): ?>
                                <span style="font-size:0.75rem;text-decoration:line-through;color:var(--text-muted);"><?= formatPrice($p['price']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($p['stock_quantity'] <= 0): ?>
                                <span class="status-pill pill-danger"><span class="status-dot"></span>Out of Stock</span>
                            <?php elseif ($p['stock_quantity'] <= $p['low_stock_threshold']): ?>
                                <span class="status-pill pill-warning"><span class="status-dot"></span>Low: <?= $p['stock_quantity'] ?></span>
                            <?php else: ?>
                                <span class="status-pill pill-success"><span class="status-dot"></span>In Stock: <?= $p['stock_quantity'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="font-family:var(--font-mono, monospace);font-weight:600;"><?= (int)$p['total_sold'] ?></td>
                        <td>
                            <a href="?toggle_status=<?= $p['id'] ?>&csrf_token=<?= CSRF::getToken() ?>" title="Click to toggle status">
                                <span class="status-pill <?= $p['is_active'] ? 'pill-success' : 'pill-neutral' ?>">
                                    <span class="status-dot"></span>
                                    <?= $p['is_active'] ? 'Active' : 'Disabled' ?>
                                </span>
                            </a>
                        </td>
                        <td style="text-align:right;">
                            <a href="<?= ADMIN_URL ?>/edit-product.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                            <a href="?delete=<?= $p['id'] ?>&csrf_token=<?= CSRF::getToken() ?>" onclick="return confirm('Delete product <?= e(addslashes($p['name'])) ?>?')" class="btn btn-danger btn-sm" style="padding:0.35rem 0.5rem;"><?= icon('trash', '', 'Delete product') ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPages > 1): ?>
    <div style="display:flex;justify-content:center;gap:0.5rem;margin-top:1.5rem;">
        <?php for ($pg = 1; $pg <= $totalPages; $pg++): ?>
            <a href="?page=<?= $pg ?>" class="btn <?= $pg === $page ? 'btn-primary' : 'btn-outline' ?> btn-sm">
                <?= $pg ?>
            </a>
        <?php endfor; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
