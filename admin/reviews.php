<?php
$pageTitle = "Review Moderation";
require_once __DIR__ . '/includes/admin-header.php';

requirePermission('manage_reviews');

$reviewModel = new Review();

// Handle Status Change
if (isset($_GET['status_set']) && isset($_GET['id'])) {
    CSRF::requireValid();
    $rid = (int)$_GET['id'];
    $status = $_GET['status_set'];
    $reviewModel->updateStatus($rid, $status);
    setFlash('success', "Review marked as $status.");
    redirect(ADMIN_URL . '/reviews.php');
}

// Handle Delete
if (isset($_GET['delete'])) {
    CSRF::requireValid();
    $rid = (int)$_GET['delete'];
    $reviewModel->delete($rid);
    setFlash('success', 'Review deleted.');
    redirect(ADMIN_URL . '/reviews.php');
}

$filters = [
    'status' => $_GET['status'] ?? null,
    'rating' => $_GET['rating'] ?? null,
    'search' => $_GET['q'] ?? null,
];

$page = max(1, (int)($_GET['page'] ?? 1));
$result = $reviewModel->getAll($filters, $page, 20);
$reviews = $result['reviews'];
$totalPages = $result['total_pages'];
$total = $result['total'];
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div>
        <h1 style="font-size:1.6rem;font-weight:700;">Product Reviews & Ratings</h1>
        <p style="color:var(--text-muted);font-size:0.875rem;">Moderate customer feedback and sentiment</p>
    </div>
</div>

<!-- Filters -->
<div class="admin-card" style="padding:1rem;">
    <form method="GET" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:center;">
        <input type="text" name="q" placeholder="Search by customer, product, or comment..." value="<?= e($filters['search'] ?? '') ?>" class="form-control" style="max-width:320px;">

        <select name="status" class="form-control" style="max-width:180px;">
            <option value="">All Statuses</option>
            <option value="approved" <?= ($filters['status'] === 'approved') ? 'selected' : '' ?>>Approved (Public)</option>
            <option value="pending" <?= ($filters['status'] === 'pending') ? 'selected' : '' ?>>Pending Review</option>
            <option value="hidden" <?= ($filters['status'] === 'hidden') ? 'selected' : '' ?>>Hidden</option>
        </select>

        <select name="rating" class="form-control" style="max-width:160px;">
            <!-- Plain text: an <option> cannot render SVG, and Unicode stars
                 would reintroduce a second icon language. -->
            <option value="">All ratings</option>
            <option value="5" <?= ($filters['rating'] === '5') ? 'selected' : '' ?>>5 stars</option>
            <option value="4" <?= ($filters['rating'] === '4') ? 'selected' : '' ?>>4 stars</option>
            <option value="3" <?= ($filters['rating'] === '3') ? 'selected' : '' ?>>3 stars</option>
            <option value="2" <?= ($filters['rating'] === '2') ? 'selected' : '' ?>>2 stars</option>
            <option value="1" <?= ($filters['rating'] === '1') ? 'selected' : '' ?>>1 star</option>
        </select>

        <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
        <a href="<?= ADMIN_URL ?>/reviews.php" class="btn btn-outline btn-sm">Reset</a>
    </form>
</div>

<div class="admin-card" style="padding:0;overflow:hidden;">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Customer</th>
                <th>Target Product</th>
                <th>Rating</th>
                <th>Comment</th>
                <th>Date</th>
                <th>Status</th>
                <th style="text-align:right;">Moderation Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($reviews)): ?>
                <tr><td colspan="7" style="text-align:center;padding:3rem;">No customer reviews match criteria.</td></tr>
            <?php else: ?>
                <?php foreach ($reviews as $r): ?>
                    <tr>
                        <td>
                            <div style="font-weight:700;"><?= e($r['first_name'] . ' ' . $r['last_name']) ?></div>
                            <div style="font-size:0.75rem;color:var(--text-muted);"><?= e($r['user_email']) ?></div>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/product.php?slug=<?= urlencode($r['product_slug']) ?>" target="_blank" style="color:var(--primary);font-weight:600;">
                                <?= e($r['product_name']) ?>
                            </a>
                        </td>
                        <td style="color:var(--warning);font-weight:700;white-space:nowrap;">
                            <?= str_repeat(icon('star', 'icon-xs'), (int)$r['rating']) ?>
                            <span style="margin-left:4px;"><?= (int)$r['rating'] ?>/5</span>
                        </td>
                        <td style="max-width:320px;font-size:0.875rem;line-height:1.4;">
                            <?= nl2br(e(truncateText($r['comment'], 140))) ?>
                        </td>
                        <td style="font-size:0.8rem;color:var(--text-muted);"><?= timeAgo($r['created_at']) ?></td>
                        <td>
                            <span class="badge badge-<?= $r['status'] === 'approved' ? 'success' : ($r['status'] === 'hidden' ? 'danger' : 'warning') ?>">
                                <?= e($r['status']) ?>
                            </span>
                        </td>
                        <td style="text-align:right;white-space:nowrap;">
                            <?php if ($r['status'] !== 'approved'): ?>
                                <a href="?status_set=approved&id=<?= $r['id'] ?>&csrf_token=<?= CSRF::getToken() ?>" class="btn btn-outline btn-sm" style="color:var(--success);border-color:var(--success);" title="Approve Review"><?= icon('check') ?> Approve</a>
                            <?php endif; ?>
                            <?php if ($r['status'] !== 'hidden'): ?>
                                <a href="?status_set=hidden&id=<?= $r['id'] ?>&csrf_token=<?= CSRF::getToken() ?>" class="btn btn-outline btn-sm" style="color:var(--warning);border-color:var(--warning);" title="Hide Review"><?= icon('close') ?> Hide</a>
                            <?php endif; ?>
                            <a href="?delete=<?= $r['id'] ?>&csrf_token=<?= CSRF::getToken() ?>" onclick="return confirm('Delete review permanently?')" class="btn btn-danger btn-sm" style="padding:0.35rem 0.5rem;"><?= icon('trash') ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($totalPages > 1): ?>
    <div style="display:flex;justify-content:center;gap:0.5rem;margin-top:1.5rem;">
        <?php for ($pg = 1; $pg <= $totalPages; $pg++): ?>
            <a href="?page=<?= $pg ?>" class="btn <?= $pg === $page ? 'btn-primary' : 'btn-outline' ?> btn-sm">
                <?= $pg ?>
            </a>
        <?php endfor; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
