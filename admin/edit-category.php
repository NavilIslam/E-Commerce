<?php
$pageTitle = "Edit Category";
require_once __DIR__ . '/includes/admin-header.php';

$categoryId = (int)($_GET['id'] ?? 0);
$categoryModel = new Category();
$category = $categoryModel->findById($categoryId);

if (!$category) {
    setFlash('error', 'Category not found.');
    redirect(ADMIN_URL . '/categories.php');
}

$categories = $categoryModel->getAll(false);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::requireValid();

    $name = trim($_POST['name'] ?? '');
    if (empty($name)) {
        setFlash('error', 'Category name is required.');
    } else {
        $categoryModel->update($categoryId, [
            'name'        => $name,
            'slug'        => $_POST['slug'] ?? null,
            'description' => $_POST['description'] ?? null,
            'parent_id'   => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
            'sort_order'  => (int)($_POST['sort_order'] ?? 0),
            'is_active'   => isset($_POST['is_active']) ? 1 : 0,
        ]);

        setFlash('success', 'Category updated successfully.');
        redirect(ADMIN_URL . '/categories.php');
    }
}
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div>
        <a href="<?= ADMIN_URL ?>/categories.php" style="color:var(--primary);font-size:0.875rem;font-weight:600;"><?= icon('arrow-l', 'icon-sm') ?> Back to Categories</a>
        <h1 style="font-size:1.6rem;font-weight:700;margin-top:0.25rem;">Edit Category: <?= e($category['name']) ?></h1>
    </div>
</div>

<form method="POST" class="admin-card" style="max-width:650px;">
    <?= csrfField() ?>

    <div class="form-group">
        <label class="form-label">Category Name *</label>
        <input type="text" name="name" class="form-control" value="<?= e($category['name']) ?>" required>
    </div>

    <div class="form-group">
        <label class="form-label">URL Slug</label>
        <input type="text" name="slug" class="form-control" value="<?= e($category['slug']) ?>">
    </div>

    <div class="form-group">
        <label class="form-label">Parent Category</label>
        <select name="parent_id" class="form-control">
            <option value="">-- None (Root Level) --</option>
            <?php foreach ($categories as $cat): if ($cat['id'] != $categoryId): ?>
                <option value="<?= $cat['id'] ?>" <?= ($category['parent_id'] == $cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
            <?php endif; endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" rows="3" class="form-control"><?= e($category['description'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
        <label class="form-label">Display Sort Order</label>
        <input type="number" name="sort_order" class="form-control" value="<?= $category['sort_order'] ?>">
    </div>

    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-weight:600;margin:1.5rem 0;">
        <input type="checkbox" name="is_active" value="1" <?= $category['is_active'] ? 'checked' : '' ?>>
        <span>Active (Show in catalog and store menus)</span>
    </label>

    <button type="submit" class="btn btn-primary btn-lg">Update Category</button>
</form>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
