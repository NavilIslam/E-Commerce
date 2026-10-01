<?php
$pageTitle = "Edit Offer";
require_once __DIR__ . '/includes/admin-header.php';

requirePermission('manage_offers');

$offerId = (int)($_GET['id'] ?? 0);
$offerModel = new Offer();
$offer = $offerModel->findById($offerId);

if (!$offer) {
    setFlash('error', 'Offer not found.');
    redirect(ADMIN_URL . '/offers.php');
}

$categoryModel = new Category();
$categories = $categoryModel->getAll(false);

$productModel = new Product();
$products = $productModel->getAll(['is_active' => 1], 1, 100)['products'];

$selectedProductIds = array_column($offer['products'], 'id');
$selectedCategoryIds = array_column($offer['categories'], 'id');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::requireValid();

    $name = trim($_POST['name'] ?? '');
    $discountValue = (float)($_POST['discount_value'] ?? 0);
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';

    if (empty($name) || $discountValue <= 0 || empty($startDate) || empty($endDate)) {
        setFlash('error', 'Please fill in offer name, discount value, and date range.');
    } else {
        $offerModel->update($offerId, [
            'name'                => $name,
            'description'         => $_POST['description'] ?? null,
            'discount_type'       => $_POST['discount_type'] ?? 'percentage',
            'discount_value'      => $discountValue,
            'min_order_amount'    => !empty($_POST['min_order_amount']) ? (float)$_POST['min_order_amount'] : null,
            'max_discount_amount' => !empty($_POST['max_discount_amount']) ? (float)$_POST['max_discount_amount'] : null,
            'start_date'          => $startDate . ' 00:00:00',
            'end_date'            => $endDate . ' 23:59:59',
            'usage_limit'         => !empty($_POST['usage_limit']) ? (int)$_POST['usage_limit'] : null,
            'per_user_limit'      => !empty($_POST['per_user_limit']) ? (int)$_POST['per_user_limit'] : null,
            'is_flash_sale'       => !empty($_POST['is_flash_sale']) ? 1 : 0,
            'is_active'           => isset($_POST['is_active']) ? 1 : 0,
            'product_ids'         => $_POST['product_ids'] ?? [],
            'category_ids'        => $_POST['category_ids'] ?? [],
        ]);

        setFlash('success', 'Offer updated successfully.');
        redirect(ADMIN_URL . '/offers.php');
    }
}
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div>
        <a href="<?= ADMIN_URL ?>/offers.php" style="color:var(--primary);font-size:0.875rem;font-weight:600;"><?= icon('arrow-l', 'icon-sm') ?> Back to Offers</a>
        <h1 style="font-size:1.6rem;font-weight:700;margin-top:0.25rem;">Edit Offer: <?= e($offer['name']) ?></h1>
    </div>
</div>

<form method="POST" class="admin-card" style="max-width:850px;">
    <?= csrfField() ?>

    <div class="form-group">
        <label class="form-label">Offer Campaign Name *</label>
        <input type="text" name="name" class="form-control" value="<?= e($offer['name']) ?>" required>
    </div>

    <div class="form-group">
        <label class="form-label">Campaign Description</label>
        <textarea name="description" rows="2" class="form-control"><?= e($offer['description'] ?? '') ?></textarea>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.25rem;">
        <div class="form-group">
            <label class="form-label">Discount Type *</label>
            <select name="discount_type" class="form-control">
                <option value="percentage" <?= $offer['discount_type'] === 'percentage' ? 'selected' : '' ?>>Percentage (%)</option>
                <option value="fixed" <?= $offer['discount_type'] === 'fixed' ? 'selected' : '' ?>>Fixed (৳)</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Discount Value *</label>
            <input type="number" step="0.01" name="discount_value" class="form-control" value="<?= $offer['discount_value'] ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Max Discount Cap (৳)</label>
            <input type="number" step="0.01" name="max_discount_amount" class="form-control" value="<?= $offer['max_discount_amount'] ?? '' ?>">
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
        <div class="form-group">
            <label class="form-label">Start Date *</label>
            <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d', strtotime($offer['start_date'])) ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">End Date *</label>
            <input type="date" name="end_date" class="form-control" value="<?= date('Y-m-d', strtotime($offer['end_date'])) ?>" required>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
        <div class="form-group">
            <label class="form-label">Apply to Categories</label>
            <select name="category_ids[]" multiple class="form-control" style="height:120px;">
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= in_array($cat['id'], $selectedCategoryIds) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Apply to Specific Products</label>
            <select name="product_ids[]" multiple class="form-control" style="height:120px;">
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= in_array($p['id'], $selectedProductIds) ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div style="display:flex;gap:2rem;margin:1.5rem 0;">
        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-weight:600;">
            <input type="checkbox" name="is_flash_sale" value="1" <?= $offer['is_flash_sale'] ? 'checked' : '' ?>>
            <span><?= icon('percent') ?> Flash Sale Campaign</span>
        </label>

        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-weight:600;">
            <input type="checkbox" name="is_active" value="1" <?= $offer['is_active'] ? 'checked' : '' ?>>
            <span>Active</span>
        </label>
    </div>

    <button type="submit" class="btn btn-primary btn-lg">Update Offer Changes</button>
</form>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
