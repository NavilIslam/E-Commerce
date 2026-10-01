<?php
$pageTitle = "Add Banner";
require_once __DIR__ . '/includes/admin-header.php';

requirePermission('manage_banners');

$bannerModel = new Banner();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::requireValid();

    $uploader = new FileUpload('banners');
    $fileName = $uploader->upload($_FILES['image']);

    if (!$fileName) {
        setFlash('error', $uploader->getFirstError() ?: 'Banner image is required.');
    } else {
        $bannerModel->create([
            'title'       => $_POST['title'] ?? null,
            'subtitle'    => $_POST['subtitle'] ?? null,
            'image'       => $fileName,
            'button_text' => $_POST['button_text'] ?? null,
            'button_url'  => $_POST['button_url'] ?? null,
            'position'    => $_POST['position'] ?? 'hero',
            'sort_order'  => (int)($_POST['sort_order'] ?? 0),
            'start_date'  => !empty($_POST['start_date']) ? $_POST['start_date'] . ' 00:00:00' : null,
            'end_date'    => !empty($_POST['end_date']) ? $_POST['end_date'] . ' 23:59:59' : null,
            'is_active'   => isset($_POST['is_active']) ? 1 : 0,
        ]);

        setFlash('success', 'Banner created successfully.');
        redirect(ADMIN_URL . '/banners.php');
    }
}
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div>
        <a href="<?= ADMIN_URL ?>/banners.php" style="color:var(--primary);font-size:0.875rem;font-weight:600;"><?= icon('arrow-l', 'icon-sm') ?> Back to Banners</a>
        <h1 style="font-size:1.6rem;font-weight:700;margin-top:0.25rem;">Create New Banner</h1>
    </div>
</div>

<form method="POST" enctype="multipart/form-data" class="admin-card" style="max-width:750px;">
    <?= csrfField() ?>

    <div class="form-group">
        <label class="form-label">Banner Image *</label>
        <input type="file" name="image" accept="image/*" class="form-control" data-preview="banner-prev" required>
        <div class="image-preview-box" style="width:200px;height:90px;">
            <img id="banner-prev" src="#" alt="Preview" style="display:none;">
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
        <div class="form-group">
            <label class="form-label">Main Heading / Title</label>
            <input type="text" name="title" class="form-control" placeholder="e.g. Next-Gen Audio Collection">
        </div>
        <div class="form-group">
            <label class="form-label">Subheading Text</label>
            <input type="text" name="subtitle" class="form-control" placeholder="e.g. Exclusive 15% discount for members">
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
        <div class="form-group">
            <label class="form-label">Button Label</label>
            <input type="text" name="button_text" class="form-control" placeholder="e.g. Shop Now">
        </div>
        <div class="form-group">
            <label class="form-label">Destination URL</label>
            <input type="text" name="button_url" class="form-control" placeholder="e.g. /products.php?category=electronics">
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
        <div class="form-group">
            <label class="form-label">Banner Placement</label>
            <select name="position" class="form-control">
                <option value="hero">Hero Top Carousel</option>
                <option value="promotional">Promotional Middle Section</option>
                <option value="sidebar">Sidebar Slot</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Display Order</label>
            <input type="number" name="sort_order" class="form-control" value="0">
        </div>
    </div>

    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-weight:600;margin:1.5rem 0;">
        <input type="checkbox" name="is_active" value="1" checked>
        <span>Active (Display on site)</span>
    </label>

    <button type="submit" class="btn btn-primary btn-lg">Publish banner</button>
</form>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
