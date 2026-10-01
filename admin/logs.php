<?php
$pageTitle = "Activity Audit Logs";
require_once __DIR__ . '/includes/admin-header.php';

requirePermission('view_logs');

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 30;
$offset = ($page - 1) * $perPage;

$db = Database::getInstance();
$total = (int) $db->fetchColumn("SELECT COUNT(*) FROM admin_logs");
$totalPages = ceil($total / $perPage);

$logs = AdminLog::getRecent($perPage, $offset);
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div>
        <h1 style="font-size:1.6rem;font-weight:700;">Administrator Activity Trail</h1>
        <p style="color:var(--text-muted);font-size:0.875rem;">Immutable audit log of modifications and events</p>
    </div>
</div>

<div class="admin-card" style="padding:0;overflow:hidden;">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>Administrator</th>
                <th>Action Type</th>
                <th>Entity Affected</th>
                <th>Description / Details</th>
                <th>IP Address</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr><td colspan="6" style="text-align:center;padding:3rem;">No activity logged yet.</td></tr>
            <?php else: ?>
                <?php foreach ($logs as $l): ?>
                    <tr>
                        <td style="font-size:0.8rem;color:var(--text-muted);white-space:nowrap;">
                            <?= date('M d, Y h:i:s A', strtotime($l['created_at'])) ?>
                        </td>
                        <td>
                            <div style="font-weight:700;"><?= e($l['first_name'] . ' ' . $l['last_name']) ?></div>
                            <span class="badge badge-info" style="font-size:0.65rem;"><?= strtoupper($l['role']) ?></span>
                        </td>
                        <td>
                            <span class="badge badge-warning" style="font-family:monospace;font-size:0.75rem;">
                                <?= e($l['action']) ?>
                            </span>
                        </td>
                        <td>
                            <span style="text-transform:capitalize;font-weight:600;"><?= e($l['entity_type']) ?></span>
                            <?php if (!empty($l['entity_id'])): ?>
                                <span style="font-family:monospace;color:var(--text-muted);">#<?= $l['entity_id'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:0.875rem;"><?= e($l['description'] ?? 'No description provided') ?></td>
                        <td style="font-family:monospace;font-size:0.8rem;color:var(--text-muted);"><?= e($l['ip_address'] ?? '127.0.0.1') ?></td>
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
