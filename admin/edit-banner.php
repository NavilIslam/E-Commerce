<?php
$pageTitle = "Edit Banner";
require_once __DIR__ . '/includes/admin-header.php';

requirePermission('manage_banners');

$bannerId = (int)($_GET['id'] ?? 0);
$bannerModel = new Banner();
$banner = $bannerModel->findById($bannerId);

if (!$banner) {
    setFlash('error', 'Banner not found.');
    redirect(ADMIN_URL . '/banners.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::requireValid();

    $fileName = null;
    if (!empty($_FILES['image']['name'])) {
        $uploader = new FileUpload('banners');
        $fileName = $uploader->upload($_FILES['image']);
    }

    $bannerModel->update($bannerId, [
        'title'       => $_POST['title'] ?? null,
        'subtitle'    => $_POST['subtitle'] ?? null,
        'image'       => $fileName,
        'button_text' => $_POST['button_text'] ?? null,
        'button_url'  => $_POST['button_url'] ?? null,
        'position'    => $_POST['position'] ?? 'hero',
        'sort_order'  => (int)($_POST['sort_order'] ?? 0),
        'is_active'   => isset($_POST['is_active']) ? 1 : 0,
    ]);

    setFlash('success', 'Banner updated.');
    redirect(ADMIN_URL . '/banners.php');
}
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div>
        <a href="<?= ADMIN_URL ?>/banners.php" style="color:var(--primary);font-size:0.875rem;font-weight:600;"><?= icon('arrow-l', 'icon-sm') ?> Back to Banners</a>
        <h1 style="font-size:1.6rem;font-weight:700;margin-top:0.25rem;">Edit Banner</h1>
    </div>
</div>

<form method="POST" enctype="multipart/form-data" class="admin-card" style="max-width:750px;">
    <?= csrfField() ?>

    <div class="form-group">
        <label class="form-label">Current Graphic</label>
        <img src="<?= getImageUrl($banner['image'], 'banners') ?>" style="width:240px;height:100px;object-fit:cover;border-radius:4px;display:block;margin-bottom:0.75rem;" alt="">
        <label class="form-label">Replace Banner Image (Optional)</label>
        <input type="file" name="image" accept="image/*" class="form-control">
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
        <div class="form-group">
            <label class="form-label">Main Heading / Title</label>
            <input type="text" name="title" class="form-control" value="<?= e($banner['title'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Subheading Text</label>
            <input type="text" name="subtitle" class="form-control" value="<?= e($banner['subtitle'] ?? '') ?>">
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
        <div class="form-group">
            <label class="form-label">Button Label</label>
            <input type="text" name="button_text" class="form-control" value="<?= e($banner['button_text'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Destination URL</label>
            <input type="text" name="button_url" class="form-control" value="<?= e($banner['button_url'] ?? '') ?>">
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
        <div class="form-group">
            <label class="form-label">Banner Placement</label>
            <select name="position" class="form-control">
                <option value="hero" <?= $banner['position'] === 'hero' ? 'selected' : '' ?>>Hero Top Carousel</option>
                <option value="promotional" <?= $banner['position'] === 'promotional' ? 'selected' : '' ?>>Promotional Middle Section</option>
                <option value="sidebar" <?= $banner['position'] === 'sidebar' ? 'selected' : '' ?>>Sidebar Slot</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Display Order</label>
            <input type="number" name="sort_order" class="form-control" value="<?= $banner['sort_order'] ?>">
        </div>
    </div>

    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-weight:600;margin:1.5rem 0;">
        <input type="checkbox" name="is_active" value="1" <?= $banner['is_active'] ? 'checked' : '' ?>>
        <span>Active</span>
    </label>

    <button type="submit" class="btn btn-primary btn-lg">Update Banner</button>
</form>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
