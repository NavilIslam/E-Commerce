<?php
$pageTitle = "Store Settings";
require_once __DIR__ . '/includes/admin-header.php';

requirePermission('manage_settings');

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::requireValid();

    $settingsToUpdate = [
        'site_name'                => trim($_POST['site_name'] ?? APP_NAME),
        'site_tagline'             => trim($_POST['site_tagline'] ?? ''),
        'site_email'               => trim($_POST['site_email'] ?? ''),
        'site_phone'               => trim($_POST['site_phone'] ?? ''),
        'site_address'             => trim($_POST['site_address'] ?? ''),
        'shipping_inside_city'     => trim($_POST['shipping_inside_city'] ?? '60.00'),
        'shipping_outside_city'    => trim($_POST['shipping_outside_city'] ?? '120.00'),
        'free_shipping_threshold'  => trim($_POST['free_shipping_threshold'] ?? '5000.00'),
    ];

    foreach ($settingsToUpdate as $key => $val) {
        $db->query(
            "INSERT INTO settings (setting_key, setting_value, setting_group) 
             VALUES (:k, :v, 'general') 
             ON DUPLICATE KEY UPDATE setting_value = :v2",
            [':k' => $key, ':v' => $val, ':v2' => $val]
        );
    }

    AdminLog::log('SETTINGS_UPDATED', 'settings', null, 'Store general settings and shipping rates updated.');
    setFlash('success', 'Store settings updated successfully.');
    redirect(ADMIN_URL . '/settings.php');
}
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div>
        <h1 style="font-size:1.5rem;font-weight:700;">Settings</h1>
        <p style="color:var(--text-muted);font-size:0.825rem;">Store details, contact information, and shipping rates.</p>
    </div>
</div>

<form method="POST" class="admin-card" style="max-width:800px;">
    <?= csrfField() ?>

    <h2 style="font-size:1.2rem;font-weight:700;margin-bottom:1.25rem;border-bottom:1px solid var(--admin-border);padding-bottom:0.5rem;">
        General Brand Information
    </h2>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
        <div class="form-group">
            <label class="form-label">Store Brand Name *</label>
            <input type="text" name="site_name" class="form-control" value="<?= e(getSetting('site_name', APP_NAME)) ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Tagline</label>
            <input type="text" name="site_tagline" class="form-control" value="<?= e(getSetting('site_tagline', 'Your Premium Online Shopping Destination')) ?>">
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
        <div class="form-group">
            <label class="form-label">Support Email *</label>
            <input type="email" name="site_email" class="form-control" value="<?= e(getSetting('site_email', 'support@novamart.com')) ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Helpline Phone *</label>
            <input type="text" name="site_phone" class="form-control" value="<?= e(getSetting('site_phone', '+880 9612-000000')) ?>" required>
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Physical Corporate Address</label>
        <textarea name="site_address" rows="2" class="form-control"><?= e(getSetting('site_address', 'Gulshan 2, Dhaka-1212, Bangladesh')) ?></textarea>
    </div>

    <h2 style="font-size:1.2rem;font-weight:700;margin:2rem 0 1.25rem;border-bottom:1px solid var(--admin-border);padding-bottom:0.5rem;">
        Shipping & Delivery Pricing (৳)
    </h2>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.25rem;">
        <div class="form-group">
            <label class="form-label">Inside City Standard (৳)</label>
            <input type="number" step="0.01" name="shipping_inside_city" class="form-control" value="<?= e(getSetting('shipping_inside_city', '60.00')) ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Outside City Standard (৳)</label>
            <input type="number" step="0.01" name="shipping_outside_city" class="form-control" value="<?= e(getSetting('shipping_outside_city', '120.00')) ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Free Shipping Threshold (৳)</label>
            <input type="number" step="0.01" name="free_shipping_threshold" class="form-control" value="<?= e(getSetting('free_shipping_threshold', '5000.00')) ?>" required>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-lg" style="margin-top:1rem;">Save settings</button>
</form>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
