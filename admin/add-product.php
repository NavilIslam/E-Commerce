<?php
$pageTitle = "Add New Product";
require_once __DIR__ . '/includes/admin-header.php';

$categoryModel = new Category();
$categories = $categoryModel->getAll(false);

$productModel = new Product();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::requireValid();

    $validator = new Validator($_POST);
    $isValid = $validator->validate([
        'name'           => 'required|max:300',
        'price'          => 'required|numeric',
        'stock_quantity' => 'required|integer',
        'sku'            => 'unique:products,sku',
    ]);

    if (!$isValid) {
        $errors = $validator->getErrors();
        setFlash('error', 'Please resolve the highlighted validation errors.');
    } else {
        $productId = $productModel->create([
            'category_id'         => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
            'name'                => $_POST['name'],
            'slug'                => $_POST['slug'] ?? null,
            'description'         => $_POST['description'] ?? null,
            'short_description'   => $_POST['short_description'] ?? null,
            'sku'                 => $_POST['sku'] ?? null,
            'brand'               => $_POST['brand'] ?? null,
            'price'               => $_POST['price'],
            'sale_price'          => $_POST['sale_price'] ?? null,
            'stock_quantity'      => $_POST['stock_quantity'] ?? 0,
            'low_stock_threshold' => $_POST['low_stock_threshold'] ?? 5,
            'weight'              => $_POST['weight'] ?? null,
            'is_active'           => isset($_POST['is_active']) ? 1 : 0,
            'is_featured'         => isset($_POST['is_featured']) ? 1 : 0,
        ]);

        // Upload primary image if provided
        if (!empty($_FILES['primary_image']['name'])) {
            $uploader = new FileUpload('products');
            $fileName = $uploader->upload($_FILES['primary_image']);
            if ($fileName) {
                $productModel->addImage($productId, $fileName, $_POST['name'], true);
            }
        }

        setFlash('success', 'Product created successfully!');
        redirect(ADMIN_URL . '/products.php');
    }
}
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div>
        <a href="<?= ADMIN_URL ?>/products.php" style="color:var(--primary);font-size:0.875rem;font-weight:600;"><?= icon('arrow-l', 'icon-sm') ?> Back to Products</a>
        <h1 style="font-size:1.6rem;font-weight:700;margin-top:0.25rem;">Create New Product</h1>
    </div>
</div>

<form method="POST" enctype="multipart/form-data" class="admin-card" style="max-width:900px;">
    <?= csrfField() ?>

    <div class="form-group">
        <label class="form-label">Product Name *</label>
        <input type="text" name="name" class="form-control" value="<?= e($_POST['name'] ?? '') ?>" placeholder="e.g. Sony WH-1000XM5 Wireless Headphones" required autofocus>
        <?php if (isset($errors['name'])): ?><div style="color:var(--danger);font-size:0.8rem;"><?= e($errors['name']) ?></div><?php endif; ?>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
        <div class="form-group">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-control">
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Brand Name</label>
            <input type="text" name="brand" class="form-control" value="<?= e($_POST['brand'] ?? '') ?>" placeholder="e.g. Sony, Apple, Nike">
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.25rem;">
        <div class="form-group">
            <label class="form-label">Regular Price (৳) *</label>
            <input type="number" step="0.01" name="price" class="form-control" value="<?= e($_POST['price'] ?? '') ?>" placeholder="0.00" required>
            <?php if (isset($errors['price'])): ?><div style="color:var(--danger);font-size:0.8rem;"><?= e($errors['price']) ?></div><?php endif; ?>
        </div>

        <div class="form-group">
            <label class="form-label">Sale / Discount Price (৳)</label>
            <input type="number" step="0.01" name="sale_price" class="form-control" value="<?= e($_POST['sale_price'] ?? '') ?>" placeholder="Optional discounted price">
        </div>

        <div class="form-group">
            <label class="form-label">Stock Quantity *</label>
            <input type="number" name="stock_quantity" class="form-control" value="<?= e($_POST['stock_quantity'] ?? '10') ?>" required>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.25rem;">
        <div class="form-group">
            <label class="form-label">SKU (Stock Keeping Unit)</label>
            <input type="text" name="sku" class="form-control" value="<?= e($_POST['sku'] ?? '') ?>" placeholder="Leave blank to auto-generate">
            <?php if (isset($errors['sku'])): ?><div style="color:var(--danger);font-size:0.8rem;"><?= e($errors['sku']) ?></div><?php endif; ?>
        </div>

        <div class="form-group">
            <label class="form-label">Low Stock Threshold</label>
            <input type="number" name="low_stock_threshold" class="form-control" value="<?= e($_POST['low_stock_threshold'] ?? '5') ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Weight (kg)</label>
            <input type="number" step="0.01" name="weight" class="form-control" value="<?= e($_POST['weight'] ?? '') ?>" placeholder="e.g. 0.5">
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Primary Product Image</label>
        <input type="file" name="primary_image" accept="image/*" class="form-control" data-preview="img-preview">
        <div class="image-preview-box">
            <img id="img-preview" src="#" alt="Preview" style="display:none;">
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Short Summary / Highlights</label>
        <textarea name="short_description" rows="2" class="form-control" placeholder="1-2 sentences summarizing key selling points..."><?= e($_POST['short_description'] ?? '') ?></textarea>
    </div>

    <div class="form-group">
        <label class="form-label">Full Product Description & Specifications</label>
        <textarea name="description" rows="6" class="form-control" placeholder="Detailed technical specifications, materials, warranty..."><?= e($_POST['description'] ?? '') ?></textarea>
    </div>

    <div style="display:flex;gap:2rem;margin:1.5rem 0;">
        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-weight:600;">
            <input type="checkbox" name="is_active" value="1" checked>
            <span>Active (Visible on storefront)</span>
        </label>
        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-weight:600;">
            <input type="checkbox" name="is_featured" value="1">
            <span><?= icon('star') ?> Feature on Homepage</span>
        </label>
    </div>

    <button type="submit" class="btn btn-primary btn-lg">Publish product</button>
</form>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
