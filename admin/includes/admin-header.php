<?php
require_once __DIR__ . '/../../includes/admin-auth.php';

$currentAdmin = Auth::user();
$siteName = getSetting('site_name', APP_NAME);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | Admin' : 'Admin | ' . e($siteName) ?></title>
    <?= csrfMeta() ?>
    <script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
    <script>window.ADMIN_URL = <?= json_encode(ADMIN_URL) ?>;</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= ADMIN_URL ?>/assets/css/admin.css">
    <!-- Chart.js for analytics visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="admin-wrapper">
    <!-- Sidebar Include -->
    <?php include __DIR__ . '/admin-sidebar.php'; ?>

    <div class="admin-main">
        <!-- Topbar -->
        <header class="admin-topbar">
            <!-- Left Search Bar -->
            <div style="position:relative;width:320px;display:flex;align-items:center;">
                <span style="position:absolute;left:10px;color:var(--text-muted);pointer-events:none;"><?= icon('search', 'icon-sm') ?></span>
                <input type="text" placeholder="Search orders, products, customers..."
                       style="width:100%;padding:6px 10px 6px 32px;background:#ffffff;border:1px solid var(--admin-border);font-size:13px;outline:none;">
            </div>

            <!-- Right Controls -->
            <div style="display:flex;align-items:center;gap:12px;">
                <a href="<?= BASE_URL ?>/index.php" target="_blank" class="btn btn-outline btn-sm" style="font-size:12px;font-weight:600;">
                    <?= icon('store', 'icon-xs') ?> View Storefront
                </a>

                <div style="width:1px;height:20px;background:var(--admin-border);"></div>

                <!-- Admin Profile -->
                <div style="display:flex;align-items:center;gap:10px;">
                    <div class="avatar-circle">
                        <?= strtoupper(substr($currentAdmin['first_name'] ?? 'A', 0, 1)) ?>
                    </div>
                    <div style="display:flex;flex-direction:column;line-height:1.2;">
                        <span style="font-size:12px;font-weight:700;color:var(--text-dark);"><?= e($currentAdmin['first_name'] . ' ' . $currentAdmin['last_name']) ?></span>
                        <span style="font-size:11px;color:var(--text-muted);text-transform:capitalize;"><?= e($currentAdmin['role']) ?></span>
                    </div>
                    <a href="<?= ADMIN_URL ?>/logout.php" title="Sign Out" class="btn btn-outline btn-sm" style="padding:4px 8px;margin-left:6px;color:var(--danger);border-color:var(--admin-border);">
                        <?= icon('logout', 'icon-xs') ?>
                    </a>
                </div>
            </div>
        </header>

        <!-- Flash messages -->
        <?php if (hasFlash('success') || hasFlash('error') || hasFlash('warning')): ?>
            <div style="padding:1.25rem 1.75rem 0;">
                <?php if ($msg = getFlash('success')): ?>
                    <div style="background:#f0fdf4;color:#166534;padding:0.75rem 1rem;border:1px solid #bbf7d0;font-size:0.85rem;display:flex;align-items:center;gap:8px;">
                        <?= icon('check', 'icon-sm') ?> <?= e($msg) ?>
                    </div>
                <?php endif; ?>
                <?php if ($msg = getFlash('error')): ?>
                    <div style="background:#fef2f2;color:#991b1b;padding:0.75rem 1rem;border:1px solid #fecaca;font-size:0.85rem;display:flex;align-items:center;gap:8px;">
                        <?= icon('close', 'icon-sm') ?> <?= e($msg) ?>
                    </div>
                <?php endif; ?>
                <?php if ($msg = getFlash('warning')): ?>
                    <div style="background:#fffbeb;color:#92400e;padding:0.75rem 1rem;border:1px solid #fde68a;font-size:0.85rem;display:flex;align-items:center;gap:8px;">
                        <?= icon('alert', 'icon-sm') ?> <?= e($msg) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <main class="admin-content">
