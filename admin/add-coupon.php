<?php
$pageTitle = "Add Coupon";
require_once __DIR__ . '/includes/admin-header.php';

requirePermission('manage_coupons');

$couponModel = new Coupon();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::requireValid();

    $code = strtoupper(trim($_POST['code'] ?? ''));
    $discountValue = (float)($_POST['discount_value'] ?? 0);
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';

    $validator = new Validator($_POST);
    $isValid = $validator->validate([
        'code'           => 'required|unique:coupons,code|max:50',
        'discount_value' => 'required|numeric',
    ]);

    if (!$isValid) {
        $errors = $validator->getErrors();
        setFlash('error', $validator->getFirstError());
    } else {
        $couponModel->create([
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

        setFlash('success', 'Coupon created successfully.');
        redirect(ADMIN_URL . '/coupons.php');
    }
}
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div>
        <a href="<?= ADMIN_URL ?>/coupons.php" style="color:var(--primary);font-size:0.875rem;font-weight:600;"><?= icon('arrow-l', 'icon-sm') ?> Back to Coupons</a>
        <h1 style="font-size:1.6rem;font-weight:700;margin-top:0.25rem;">Create Discount Coupon</h1>
    </div>
</div>

<form method="POST" class="admin-card" style="max-width:750px;">
    <?= csrfField() ?>

    <div class="form-group">
        <label class="form-label">Coupon Code (Uppercase) *</label>
        <input type="text" name="code" class="form-control" placeholder="e.g. FLASH25" style="text-transform:uppercase;font-weight:700;" required autofocus>
        <?php if (isset($errors['code'])): ?><div style="color:var(--danger);font-size:0.8rem;"><?= e($errors['code']) ?></div><?php endif; ?>
    </div>

    <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" rows="2" class="form-control" placeholder="Short description of coupon benefits..."></textarea>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.25rem;">
        <div class="form-group">
            <label class="form-label">Discount Type</label>
            <select name="discount_type" class="form-control">
                <option value="percentage">Percentage (%)</option>
                <option value="fixed">Fixed (৳)</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Discount Value *</label>
            <input type="number" step="0.01" name="discount_value" class="form-control" placeholder="e.g. 10 or 500" required>
        </div>

        <div class="form-group">
            <label class="form-label">Max Discount (৳)</label>
            <input type="number" step="0.01" name="max_discount" class="form-control" placeholder="Optional cap">
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
        <div class="form-group">
            <label class="form-label">Minimum Order Requirement (৳)</label>
            <input type="number" step="0.01" name="min_order_amount" class="form-control" placeholder="e.g. 1000">
        </div>

        <div class="form-group">
            <label class="form-label">Per Customer Usage Limit</label>
            <input type="number" name="per_user_limit" class="form-control" value="1">
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.25rem;">
        <div class="form-group">
            <label class="form-label">Start Date *</label>
            <input type="date" name="start_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">End Date *</label>
            <input type="date" name="end_date" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Total Redemptions Limit</label>
            <input type="number" name="usage_limit" class="form-control" placeholder="Unlimited">
        </div>
    </div>

    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-weight:600;margin:1.5rem 0;">
        <input type="checkbox" name="is_active" value="1" checked>
        <span>Active Coupon</span>
    </label>

    <button type="submit" class="btn btn-primary btn-lg">Create coupon</button>
</form>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
