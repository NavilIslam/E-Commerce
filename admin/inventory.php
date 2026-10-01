<?php
$pageTitle = "Inventory Management";
require_once __DIR__ . '/includes/admin-header.php';

$productModel = new Product();
$db = Database::getInstance();

// Handle Quick Stock Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'quick_stock') {
    CSRF::requireValid();
    $pid = (int)$_POST['product_id'];
    $newStock = (int)$_POST['stock_quantity'];
    $productModel->updateStock($pid, $newStock);
    setFlash('success', 'Stock updated successfully.');
    redirect(ADMIN_URL . '/inventory.php');
}

$filters = [
    'search'       => $_GET['q'] ?? null,
    'stock_status' => $_GET['stock'] ?? null,
];

$page = max(1, (int)($_GET['page'] ?? 1));
$result = $productModel->getAll($filters, $page, 20);
$products = $result['products'];
$totalPages = $result['total_pages'];
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div>
        <h1 style="font-size:1.5rem;font-weight:700;">Inventory</h1>
        <p style="color:var(--text-muted);font-size:0.825rem;">Monitor and update stock levels across all catalog products.</p>
    </div>
</div>

<!-- Filter Box -->
<div class="admin-card" style="padding:1rem;">
    <form method="GET" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:center;">
        <input type="text" name="q" placeholder="Search by name, SKU..." value="<?= e($filters['search'] ?? '') ?>" class="form-control" style="max-width:280px;">

        <select name="stock" class="form-control" style="max-width:200px;">
            <!-- Plain text: an <option> cannot render SVG. -->
            <option value="">All inventory levels</option>
            <option value="low_stock" <?= ($filters['stock_status'] === 'low_stock') ? 'selected' : '' ?>>Low stock only</option>
            <option value="out_of_stock" <?= ($filters['stock_status'] === 'out_of_stock') ? 'selected' : '' ?>>Out of stock</option>
            <option value="in_stock" <?= ($filters['stock_status'] === 'in_stock') ? 'selected' : '' ?>>Healthy stock</option>
        </select>

        <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
        <a href="<?= ADMIN_URL ?>/inventory.php" class="btn btn-outline btn-sm">Reset</a>
    </form>
</div>

<div class="admin-card" style="padding:0;overflow:hidden;">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Product & SKU</th>
                <th>Category</th>
                <th>Units Sold</th>
                <th>Current Stock</th>
                <th>Threshold</th>
                <th>Status</th>
                <th style="text-align:right;">Quick Update Stock</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr><td colspan="7" style="text-align:center;padding:3rem;">No products match inventory filter.</td></tr>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td>
                            <div style="font-weight:700;"><?= e($p['name']) ?></div>
                            <div style="font-size:0.75rem;color:var(--text-muted);font-family:monospace;">SKU: <?= e($p['sku'] ?? 'N/A') ?></div>
                        </td>
                        <td><?= e($p['category_name'] ?? 'Uncategorized') ?></td>
                        <td><strong><?= (int)$p['total_sold'] ?></strong> sold</td>
                        <td>
                            <span style="font-size:1.1rem;font-weight:700;color:<?= $p['stock_quantity'] <= 0 ? 'var(--danger)' : ($p['stock_quantity'] <= $p['low_stock_threshold'] ? 'var(--warning)' : 'var(--success)') ?>;">
                                <?= $p['stock_quantity'] ?>
                            </span>
                        </td>
                        <td><?= $p['low_stock_threshold'] ?></td>
                        <td>
                            <?php if ($p['stock_quantity'] <= 0): ?>
                                <span class="badge badge-danger">Out of Stock</span>
                            <?php elseif ($p['stock_quantity'] <= $p['low_stock_threshold']): ?>
                                <span class="badge badge-warning">Low Stock Warning</span>
                            <?php else: ?>
                                <span class="badge badge-success">In Stock</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right;">
                            <form method="POST" style="display:inline-flex;gap:0.5rem;align-items:center;">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="quick_stock">
                                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                <input type="number" name="stock_quantity" value="<?= $p['stock_quantity'] ?>" min="0" class="form-control" style="width:80px;padding:0.35rem 0.5rem;font-weight:700;text-align:center;" required>
                                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
