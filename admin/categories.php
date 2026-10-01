<?php
$pageTitle = "Category Management";
require_once __DIR__ . '/includes/admin-header.php';

$categoryModel = new Category();

// Handle Delete
if (isset($_GET['delete'])) {
    CSRF::requireValid();
    $cid = (int)$_GET['delete'];
    $categoryModel->delete($cid);
    setFlash('success', 'Category deleted.');
    redirect(ADMIN_URL . '/categories.php');
}

// Handle Status Toggle
if (isset($_GET['toggle_status'])) {
    CSRF::requireValid();
    $cid = (int)$_GET['toggle_status'];
    $categoryModel->toggleStatus($cid);
    setFlash('success', 'Category status toggled.');
    redirect(ADMIN_URL . '/categories.php');
}

$categories = $categoryModel->getAll(false);
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div>
        <h1 style="font-size:1.6rem;font-weight:700;">Categories Management</h1>
        <p style="color:var(--text-muted);font-size:0.875rem;">Organize products into hierarchical departments</p>
    </div>
    <a href="<?= ADMIN_URL ?>/add-category.php" class="btn btn-primary">
        + Add New Category
    </a>
</div>

<div class="admin-card" style="padding:0;overflow:hidden;">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Slug</th>
                <th>Parent Department</th>
                <th>Products</th>
                <th>Sort Order</th>
                <th>Status</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($categories)): ?>
                <tr><td colspan="7" style="text-align:center;padding:3rem;">No categories created yet.</td></tr>
            <?php else: ?>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:0.75rem;">
                                <div style="color:var(--primary);display:flex;"><?= icon(categoryIcon($cat['slug']), 'icon-lg') ?></div>
                                <div>
                                    <div style="font-weight:700;color:var(--text-dark);"><?= e($cat['name']) ?></div>
                                    <?php if (!empty($cat['description'])): ?>
                                        <div style="font-size:0.75rem;color:var(--text-muted);"><?= e(truncateText($cat['description'], 60)) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td style="font-family:monospace;font-size:0.85rem;"><?= e($cat['slug']) ?></td>
                        <td><?= e($cat['parent_name'] ?? 'Root Category') ?></td>
                        <td>
                            <span class="badge badge-info"><?= $cat['product_count'] ?> items</span>
                        </td>
                        <td><?= $cat['sort_order'] ?></td>
                        <td>
                            <a href="?toggle_status=<?= $cat['id'] ?>&csrf_token=<?= CSRF::getToken() ?>">
                                <span class="badge badge-<?= $cat['is_active'] ? 'success' : 'muted' ?>">
                                    <?= $cat['is_active'] ? 'Active' : 'Disabled' ?>
                                </span>
                            </a>
                        </td>
                        <td style="text-align:right;">
                            <a href="<?= ADMIN_URL ?>/edit-category.php?id=<?= $cat['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                            <a href="?delete=<?= $cat['id'] ?>&csrf_token=<?= CSRF::getToken() ?>" onclick="return confirm('Delete category <?= e(addslashes($cat['name'])) ?>?')" class="btn btn-danger btn-sm" style="padding:0.35rem 0.5rem;"><?= icon('trash', '', 'Delete category') ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
