<?php
$currentScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
<aside class="admin-sidebar">
    <div class="admin-sidebar-header">
        <?= brandLogo(getSetting('site_name', APP_NAME), 'mark', 'admin-brand-mark') ?>
        <div>
            <div style="font-weight:700;font-size:0.95rem;color:var(--text-dark);letter-spacing:-0.01em;"><?= e(getSetting('site_name', APP_NAME)) ?></div>
            <div style="font-size:0.7rem;color:var(--text-muted);font-weight:600;text-transform:uppercase;letter-spacing:0.04em;">Store Administration</div>
        </div>
    </div>

    <div style="flex:1;overflow-y:auto;display:flex;flex-direction:column;">
        <ul class="admin-sidebar-menu" style="flex:1;">
            <li class="menu-heading">Main</li>
            <li>
                <a href="<?= ADMIN_URL ?>/index.php" class="<?= $currentScript === 'index.php' ? 'active' : '' ?>">
                    <?= icon('grid') ?> Overview
                </a>
            </li>
            <li>
                <a href="<?= ADMIN_URL ?>/orders.php" class="<?= in_array($currentScript, ['orders.php', 'order-detail.php']) ? 'active' : '' ?>">
                    <?= icon('cart') ?> Orders
                </a>
            </li>
            <li>
                <a href="<?= ADMIN_URL ?>/products.php" class="<?= in_array($currentScript, ['products.php', 'add-product.php', 'edit-product.php']) ? 'active' : '' ?>">
                    <?= icon('package') ?> Products
                </a>
            </li>
            <li>
                <a href="<?= ADMIN_URL ?>/categories.php" class="<?= in_array($currentScript, ['categories.php', 'add-category.php', 'edit-category.php']) ? 'active' : '' ?>">
                    <?= icon('grid') ?> Categories
                </a>
            </li>
            <li>
                <a href="<?= ADMIN_URL ?>/inventory.php" class="<?= $currentScript === 'inventory.php' ? 'active' : '' ?>">
                    <?= icon('clipboard') ?> Inventory
                </a>
            </li>
            <li>
                <a href="<?= ADMIN_URL ?>/customers.php" class="<?= in_array($currentScript, ['customers.php', 'customer-detail.php']) ? 'active' : '' ?>">
                    <?= icon('users') ?> Customers
                </a>
            </li>
            <?php if (Auth::can('view_reports')): ?>
                <li>
                    <a href="<?= ADMIN_URL ?>/reports.php" class="<?= $currentScript === 'reports.php' ? 'active' : '' ?>">
                        <?= icon('chart') ?> Sales Reports
                    </a>
                </li>
            <?php endif; ?>

            <li class="menu-heading">Storefront</li>
            <?php if (Auth::can('manage_offers')): ?>
                <li>
                    <a href="<?= ADMIN_URL ?>/offers.php" class="<?= in_array($currentScript, ['offers.php', 'add-offer.php', 'edit-offer.php']) ? 'active' : '' ?>">
                        <?= icon('tag') ?> Discounts &amp; Offers
                    </a>
                </li>
                <li>
                    <a href="<?= ADMIN_URL ?>/coupons.php" class="<?= in_array($currentScript, ['coupons.php', 'add-coupon.php', 'edit-coupon.php']) ? 'active' : '' ?>">
                        <?= icon('gift') ?> Coupons
                    </a>
                </li>
                <li>
                    <a href="<?= ADMIN_URL ?>/banners.php" class="<?= in_array($currentScript, ['banners.php', 'add-banner.php', 'edit-banner.php']) ? 'active' : '' ?>">
                        <?= icon('image') ?> Banners &amp; Sliders
                    </a>
                </li>
            <?php endif; ?>
            <li>
                <a href="<?= ADMIN_URL ?>/reviews.php" class="<?= $currentScript === 'reviews.php' ? 'active' : '' ?>">
                    <?= icon('star') ?> Customer Reviews
                </a>
            </li>

            <li class="menu-heading">System</li>
            <?php if (Auth::isOwner()): ?>
                <li>
                    <a href="<?= ADMIN_URL ?>/settings.php" class="<?= $currentScript === 'settings.php' ? 'active' : '' ?>">
                        <?= icon('settings') ?> Settings
                    </a>
                </li>
            <?php endif; ?>
            <?php if (Auth::can('view_reports')): ?>
                <li>
                    <a href="<?= ADMIN_URL ?>/logs.php" class="<?= $currentScript === 'logs.php' ? 'active' : '' ?>">
                        <?= icon('list') ?> Activity Logs
                    </a>
                </li>
            <?php endif; ?>
        </ul>

        <div style="padding:0.85rem 1rem;border-top:1px solid var(--admin-border);margin-top:auto;background:#ffffff;">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:0.4rem 0.65rem;background:#f8fafc;border:1px solid var(--admin-border);">
                <div style="display:flex;align-items:center;gap:0.5rem;">
                    <span style="width:7px;height:7px;background:#16a34a;display:inline-block;"></span>
                    <span style="font-size:0.75rem;font-weight:600;color:var(--text-dark);">Store Online</span>
                </div>
                <span style="font-size:0.7rem;color:var(--text-muted);font-family:var(--font-mono, monospace);">v2.0</span>
            </div>
        </div>
    </div>
</aside>
