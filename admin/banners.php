<?php
$pageTitle = "Banner & CMS Management";
require_once __DIR__ . '/includes/admin-header.php';

requirePermission('manage_banners');

$bannerModel = new Banner();

// Handle Delete
if (isset($_GET['delete'])) {
    CSRF::requireValid();
    $bid = (int)$_GET['delete'];
    $bannerModel->delete($bid);
    setFlash('success', 'Banner removed.');
    redirect(ADMIN_URL . '/banners.php');
}

$banners = $bannerModel->getAll();
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div>
        <h1 style="font-size:1.6rem;font-weight:700;">Hero Banners & Storefront CMS</h1>
        <p style="color:var(--text-muted);font-size:0.875rem;">Control homepage sliders, promotional graphics, and call-to-action buttons</p>
    </div>
    <a href="<?= ADMIN_URL ?>/add-banner.php" class="btn btn-primary">
        + Create New Banner
    </a>
</div>

<div class="admin-card" style="padding:0;overflow:hidden;">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Banner Graphic</th>
                <th>Title & Subtitle</th>
                <th>Position</th>
                <th>Target URL</th>
                <th>Sort Order</th>
                <th>Status</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($banners)): ?>
                <tr><td colspan="7" style="text-align:center;padding:3rem;">No banners created yet.</td></tr>
            <?php else: ?>
                <?php foreach ($banners as $b): ?>
                    <tr>
                        <td>
                            <img src="<?= getImageUrl($b['image'], 'banners') ?>" style="width:90px;height:45px;object-fit:cover;border-radius:4px;" alt="">
                        </td>
                        <td>
                            <div style="font-weight:700;"><?= e($b['title'] ?? 'Untitled Banner') ?></div>
                            <div style="font-size:0.75rem;color:var(--text-muted);"><?= e($b['subtitle'] ?? '') ?></div>
                        </td>
                        <td>
                            <span class="badge badge-<?= $b['position'] === 'hero' ? 'info' : 'warning' ?>">
                                <?= strtoupper($b['position']) ?>
                            </span>
                        </td>
                        <td style="font-size:0.85rem;color:var(--text-muted);"><?= e($b['button_url'] ?? 'No link') ?></td>
                        <td><?= $b['sort_order'] ?></td>
                        <td>
                            <span class="badge badge-<?= $b['is_active'] ? 'success' : 'muted' ?>">
                                <?= $b['is_active'] ? 'Active' : 'Hidden' ?>
                            </span>
                        </td>
                        <td style="text-align:right;">
                            <a href="<?= ADMIN_URL ?>/edit-banner.php?id=<?= $b['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                            <a href="?delete=<?= $b['id'] ?>&csrf_token=<?= CSRF::getToken() ?>" onclick="return confirm('Delete banner?')" class="btn btn-danger btn-sm" style="padding:0.35rem 0.5rem;"><?= icon('trash', '', 'Delete banner') ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
