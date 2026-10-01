<?php
$pageTitle = "Edit Coupon";
require_once __DIR__ . '/includes/admin-header.php';

requirePermission('manage_coupons');

$couponId = (int)($_GET['id'] ?? 0);
$couponModel = new Coupon();
$coupon = $couponModel->findById($couponId);

if (!$coupon) {
    setFlash('error', 'Coupon not found.');
    redirect(ADMIN_URL . '/coupons.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::requireValid();

    $code = strtoupper(trim($_POST['code'] ?? ''));
    $discountValue = (float)($_POST['discount_value'] ?? 0);
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';

    $validator = new Validator($_POST);
    $isValid = $validator->validate([
        'code'           => 'required|unique:coupons,code,' . $couponId . ',id|max:50',
        'discount_value' => 'required|numeric',
    ]);

    if (!$isValid) {
        setFlash('error', $validator->getFirstError());
    } else {
        $couponModel->update($couponId, [
            'code'             => $code,
            'description'      => $_POST['description'] ?? null,
            'discount_type'    => $_POST['discount_type'] ?? 'percentage',
            'discount_value'   => $discountValue,
            'min_order_amount' => !empty($_POST['min_order_amount']) ? (float)$_POST['min_order_amount'] : null,
            'max_discount'     => !empty($_POST['max_discount']) ? (float)$_POST['max_discount'] : null,
            'start_date'       => $startDate . ' 00:00:00',
            'end_date'         => $endDate . ' 23:59:59',
            'usage_limit'      => !empty($_POST['usage_limit']) ? (int)$_POST['usage_limit'] : null,
            'per_user_limit'   => !empty($_POST['per_user_limit']) ? (int)$_POST['per_user_limit'] : 1,
            'is_active'        => isset($_POST['is_active']) ? 1 : 0,
        ]);

        setFlash('success', 'Coupon updated successfully.');
        redirect(ADMIN_URL . '/coupons.php');
    }
}
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div>
        <a href="<?= ADMIN_URL ?>/coupons.php" style="color:var(--primary);font-size:0.875rem;font-weight:600;"><?= icon('arrow-l', 'icon-sm') ?> Back to Coupons</a>
        <h1 style="font-size:1.6rem;font-weight:700;margin-top:0.25rem;">Edit Coupon: <?= e($coupon['code']) ?></h1>
    </div>
</div>

<form method="POST" class="admin-card" style="max-width:750px;">
    <?= csrfField() ?>

    <div class="form-group">
        <label class="form-label">Coupon Code *</label>
        <input type="text" name="code" class="form-control" value="<?= e($coupon['code']) ?>" style="text-transform:uppercase;font-weight:700;" required>
    </div>

    <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" rows="2" class="form-control"><?= e($coupon['description'] ?? '') ?></textarea>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.25rem;">
        <div class="form-group">
            <label class="form-label">Discount Type</label>
            <select name="discount_type" class="form-control">
                <option value="percentage" <?= $coupon['discount_type'] === 'percentage' ? 'selected' : '' ?>>Percentage (%)</option>
                <option value="fixed" <?= $coupon['discount_type'] === 'fixed' ? 'selected' : '' ?>>Fixed (৳)</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Discount Value *</label>
            <input type="number" step="0.01" name="discount_value" class="form-control" value="<?= $coupon['discount_value'] ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Max Discount (৳)</label>
            <input type="number" step="0.01" name="max_discount" class="form-control" value="<?= $coupon['max_discount'] ?? '' ?>">
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
        <div class="form-group">
            <label class="form-label">Minimum Order (৳)</label>
            <input type="number" step="0.01" name="min_order_amount" class="form-control" value="<?= $coupon['min_order_amount'] ?? '' ?>">
        </div>

        <div class="form-group">
            <label class="form-label">Per Customer Usage Limit</label>
            <input type="number" name="per_user_limit" class="form-control" value="<?= $coupon['per_user_limit'] ?>">
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.25rem;">
        <div class="form-group">
            <label class="form-label">Start Date *</label>
            <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d', strtotime($coupon['start_date'])) ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">End Date *</label>
            <input type="date" name="end_date" class="form-control" value="<?= date('Y-m-d', strtotime($coupon['end_date'])) ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Total Redemptions Limit</label>
            <input type="number" name="usage_limit" class="form-control" value="<?= $coupon['usage_limit'] ?? '' ?>">
        </div>
    </div>

    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-weight:600;margin:1.5rem 0;">
        <input type="checkbox" name="is_active" value="1" <?= $coupon['is_active'] ? 'checked' : '' ?>>
        <span>Active Coupon</span>
    </label>

    <button type="submit" class="btn btn-primary btn-lg">Update Coupon</button>
</form>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
