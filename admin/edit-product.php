<?php
$pageTitle = "Edit Product";
require_once __DIR__ . '/includes/admin-header.php';

$productId = (int)($_GET['id'] ?? 0);
$productModel = new Product();
$product = $productModel->findById($productId);

if (!$product) {
    setFlash('error', 'Product not found.');
    redirect(ADMIN_URL . '/products.php');
}

$categoryModel = new Category();
$categories = $categoryModel->getAll(false);

// Handle Delete Image
if (isset($_GET['delete_img'])) {
    CSRF::requireValid();
    $productModel->deleteImage((int)$_GET['delete_img']);
    setFlash('success', 'Image removed.');
    redirect(ADMIN_URL . '/edit-product.php?id=' . $productId);
}

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::requireValid();

    $validator = new Validator($_POST);
    $isValid = $validator->validate([
        'name'           => 'required|max:300',
        'price'          => 'required|numeric',
        'stock_quantity' => 'required|integer',
        'sku'            => 'unique:products,sku,' . $productId . ',id',
    ]);

    if (!$isValid) {
        setFlash('error', $validator->getFirstError());
    } else {
        $productModel->update($productId, [
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

        // Upload additional image if provided
        if (!empty($_FILES['new_image']['name'])) {
            $uploader = new FileUpload('products');
            $fileName = $uploader->upload($_FILES['new_image']);
            if ($fileName) {
                $isPrimary = empty($product['images']);
                $productModel->addImage($productId, $fileName, $_POST['name'], $isPrimary);
            }
        }

        setFlash('success', 'Product updated successfully!');
        redirect(ADMIN_URL . '/edit-product.php?id=' . $productId);
    }
}

// Refresh product after changes
$product = $productModel->findById($productId);
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div>
        <a href="<?= ADMIN_URL ?>/products.php" style="color:var(--primary);font-size:0.875rem;font-weight:600;"><?= icon('arrow-l', 'icon-sm') ?> Back to Products</a>
        <h1 style="font-size:1.6rem;font-weight:700;margin-top:0.25rem;">Edit Product: <?= e($product['name']) ?></h1>
    </div>
    <a href="<?= BASE_URL ?>/product.php?slug=<?= urlencode($product['slug']) ?>" target="_blank" class="btn btn-outline btn-sm">
        View on Storefront <?= icon('chevron-r', 'icon-sm') ?>
    </a>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:2rem;">
    <!-- Main Edit Form -->
    <form method="POST" enctype="multipart/form-data" class="admin-card">
        <?= csrfField() ?>

        <div class="form-group">
            <label class="form-label">Product Name *</label>
            <input type="text" name="name" class="form-control" value="<?= e($product['name']) ?>" required>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
            <div class="form-group">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-control">
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] == $cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Brand Name</label>
                <input type="text" name="brand" class="form-control" value="<?= e($product['brand'] ?? '') ?>">
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.25rem;">
            <div class="form-group">
                <label class="form-label">Regular Price (৳) *</label>
                <input type="number" step="0.01" name="price" class="form-control" value="<?= e($product['price']) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Sale / Discount Price (৳)</label>
                <input type="number" step="0.01" name="sale_price" class="form-control" value="<?= e($product['sale_price'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Stock Quantity *</label>
                <input type="number" name="stock_quantity" class="form-control" value="<?= e($product['stock_quantity']) ?>" required>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.25rem;">
            <div class="form-group">
                <label class="form-label">SKU</label>
                <input type="text" name="sku" class="form-control" value="<?= e($product['sku'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Low Stock Threshold</label>
                <input type="number" name="low_stock_threshold" class="form-control" value="<?= e($product['low_stock_threshold']) ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Weight (kg)</label>
                <input type="number" step="0.01" name="weight" class="form-control" value="<?= e($product['weight'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Upload Additional Image</label>
            <input type="file" name="new_image" accept="image/*" class="form-control" data-preview="new-img-prev">
            <div class="image-preview-box">
                <img id="new-img-prev" src="#" alt="Preview" style="display:none;">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Short Summary</label>
            <textarea name="short_description" rows="2" class="form-control"><?= e($product['short_description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Full Product Description</label>
            <textarea name="description" rows="6" class="form-control"><?= e($product['description'] ?? '') ?></textarea>
        </div>

        <div style="display:flex;gap:2rem;margin:1.5rem 0;">
            <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-weight:600;">
                <input type="checkbox" name="is_active" value="1" <?= $product['is_active'] ? 'checked' : '' ?>>
                <span>Active (Visible on storefront)</span>
            </label>
            <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-weight:600;">
                <input type="checkbox" name="is_featured" value="1" <?= $product['is_featured'] ? 'checked' : '' ?>>
                <span><?= icon('star') ?> Feature on Homepage</span>
            </label>
        </div>

        <button type="submit" class="btn btn-primary btn-lg">Update Product Changes</button>
    </form>

    <!-- Side Image Gallery Manager -->
    <div class="admin-card" style="height:fit-content;">
        <h3 class="admin-card-title" style="margin-bottom:1rem;">Product Image Gallery</h3>

        <?php if (empty($product['images'])): ?>
            <p style="color:var(--text-muted);font-size:0.875rem;">No images uploaded yet.</p>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:1rem;">
                <?php foreach ($product['images'] as $img): ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;border:1px solid var(--admin-border);padding:0.5rem;border-radius:var(--radius-sm);">
                        <div style="display:flex;align-items:center;gap:0.75rem;">
                            <img src="<?= getImageUrl($img['image_path']) ?>" style="width:50px;height:50px;object-fit:cover;border-radius:4px;" alt="">
                            <?php if ($img['is_primary']): ?>
                                <span class="badge badge-success" style="font-size:0.7rem;">Primary</span>
                            <?php endif; ?>
                        </div>
                        <a href="?id=<?= $productId ?>&delete_img=<?= $img['id'] ?>&csrf_token=<?= CSRF::getToken() ?>" onclick="return confirm('Remove this image?')" style="color:var(--danger);font-size:0.85rem;font-weight:600;">
                            Delete
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
