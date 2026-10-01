<?php
$pageTitle = "Dashboard";
require_once __DIR__ . '/includes/admin-header.php';

$db = Database::getInstance();

// 1. KPI Statistics
$totalSales = (float) $db->fetchColumn("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE order_status != 'cancelled'");
$todaySales = (float) $db->fetchColumn("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE order_status != 'cancelled' AND DATE(created_at) = CURDATE()");
$totalOrders = (int) $db->fetchColumn("SELECT COUNT(*) FROM orders");
$pendingOrders = (int) $db->fetchColumn("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'");
$totalCustomers = (int) $db->fetchColumn("SELECT COUNT(*) FROM users WHERE role = 'customer'");
$totalProducts = (int) $db->fetchColumn("SELECT COUNT(*) FROM products WHERE is_active = 1");
$lowStock = (int) $db->fetchColumn("SELECT COUNT(*) FROM products WHERE stock_quantity <= low_stock_threshold AND is_active = 1");
$activeOffers = (int) $db->fetchColumn("SELECT COUNT(*) FROM offers WHERE is_active = 1 AND start_date <= NOW() AND end_date >= NOW()");
$aov = $totalOrders > 0 ? ($totalSales / $totalOrders) : 0;

// 2. Recent Transactions
$recentOrders = $db->fetchAll(
    "SELECT o.*, u.first_name, u.last_name, u.email as customer_email,
            (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count
     FROM orders o
     JOIN users u ON o.user_id = u.id
     ORDER BY o.created_at DESC
     LIMIT 6"
);

// 3. Low Stock Items
$criticalStock = $db->fetchAll(
    "SELECT p.id, p.name, p.sku, p.price, p.stock_quantity, p.low_stock_threshold,
            (SELECT image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC LIMIT 1) as primary_image
     FROM products p
     WHERE p.is_active = 1
     ORDER BY (p.stock_quantity <= p.low_stock_threshold) DESC, p.stock_quantity ASC
     LIMIT 5"
);

// 4. Category Mix
$categoryMix = $db->fetchAll(
    "SELECT c.name, COUNT(p.id) as product_count, COALESCE(SUM(p.total_sold * p.price), 0) as total_volume
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id, c.name
     ORDER BY total_volume DESC, product_count DESC
     LIMIT 5"
);
$totalMixVolume = array_sum(array_column($categoryMix, 'total_volume')) ?: 1;
?>

<!-- Page Header & Action Bar -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
    <div>
        <div class="admin-breadcrumbs">
            <span>Admin</span>
            <?= icon('chevron-r', 'icon-xs') ?>
            <span class="active">Dashboard</span>
        </div>
        <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-dark);letter-spacing:-0.01em;">Dashboard</h1>
        <p style="color:var(--text-muted);font-size:0.825rem;margin-top:0.15rem;">Real-time overview of sales, orders, catalog status, and inventory.</p>
    </div>

    <div style="display:flex;align-items:center;gap:0.5rem;">
        <a href="<?= ADMIN_URL ?>/reports.php" class="btn btn-outline btn-sm">
            <?= icon('chart', 'icon-xs') ?> Sales Reports
        </a>
        <a href="<?= ADMIN_URL ?>/add-product.php" class="btn btn-primary btn-sm">
            <?= icon('package', 'icon-xs') ?> Add Product
        </a>
    </div>
</div>

<!-- Primary 4 KPI Cards (Boxy, Star Tech styled) -->
<div class="kpi-grid">
    <!-- Card 1: Total Revenue -->
    <div class="kpi-card">
        <div class="kpi-modern-top">
            <div>
                <span class="kpi-title">Total Revenue</span>
                <div class="kpi-value"><?= formatPrice($totalSales) ?></div>
            </div>
            <div class="kpi-icon"><?= icon('cash') ?></div>
        </div>
        <div class="kpi-modern-bottom">
            <span style="font-size:0.75rem;color:var(--text-muted);">Cumulative store sales</span>
            <span style="font-size:0.75rem;font-weight:600;color:var(--text-dark);font-family:var(--font-mono);"><?= CURRENCY_CODE ?></span>
        </div>
    </div>

    <!-- Card 2: Total Orders -->
    <div class="kpi-card">
        <div class="kpi-modern-top">
            <div>
                <span class="kpi-title">Total Orders</span>
                <div class="kpi-value"><?= number_format($totalOrders) ?></div>
            </div>
            <div class="kpi-icon"><?= icon('cart') ?></div>
        </div>
        <div class="kpi-modern-bottom">
            <span style="font-size:0.75rem;color:var(--text-muted);">All-time orders placed</span>
            <a href="<?= ADMIN_URL ?>/orders.php" style="font-size:0.75rem;font-weight:600;color:var(--primary);">View orders &rarr;</a>
        </div>
    </div>

    <!-- Card 3: Today's Sales -->
    <div class="kpi-card">
        <div class="kpi-modern-top">
            <div>
                <span class="kpi-title">Today's Sales</span>
                <div class="kpi-value"><?= formatPrice($todaySales) ?></div>
            </div>
            <div class="kpi-icon"><?= icon('trending-up') ?></div>
        </div>
        <div class="kpi-modern-bottom">
            <span style="font-size:0.75rem;color:var(--text-muted);"><?= date('M j, Y') ?></span>
            <span style="font-size:0.75rem;font-weight:600;color:var(--success-text);">Active</span>
        </div>
    </div>

    <!-- Card 4: Pending Orders -->
    <div class="kpi-card">
        <div class="kpi-modern-top">
            <div>
                <span class="kpi-title">Pending Orders</span>
                <div class="kpi-value" style="color:<?= $pendingOrders > 0 ? 'var(--warning-text)' : 'var(--text-dark)' ?>;"><?= number_format($pendingOrders) ?></div>
            </div>
            <div class="kpi-icon" style="<?= $pendingOrders > 0 ? 'border-color:var(--warning-border);color:var(--warning-text);background:var(--warning-bg);' : '' ?>"><?= icon('alert') ?></div>
        </div>
        <div class="kpi-modern-bottom">
            <span style="font-size:0.75rem;color:var(--text-muted);">Awaiting processing</span>
            <?php if ($pendingOrders > 0): ?>
                <a href="<?= ADMIN_URL ?>/orders.php?status=pending" style="font-size:0.75rem;font-weight:700;color:var(--warning-text);">Process &rarr;</a>
            <?php else: ?>
                <span style="font-size:0.75rem;color:var(--success-text);font-weight:600;">Up to date</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Secondary Stats Row (Boxy Chips) -->
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:0.75rem;margin-bottom:1.5rem;">
    <div style="background:#ffffff;border:1px solid var(--admin-border);padding:0.75rem 1rem;display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:0.8rem;color:var(--text-muted);font-weight:600;">Registered Customers</span>
        <span style="font-size:1.1rem;font-weight:700;color:var(--text-dark);font-family:var(--font-mono);"><?= number_format($totalCustomers) ?></span>
    </div>
    <div style="background:#ffffff;border:1px solid var(--admin-border);padding:0.75rem 1rem;display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:0.8rem;color:var(--text-muted);font-weight:600;">Active Catalog Items</span>
        <span style="font-size:1.1rem;font-weight:700;color:var(--text-dark);font-family:var(--font-mono);"><?= number_format($totalProducts) ?></span>
    </div>
    <div style="background:#ffffff;border:1px solid var(--admin-border);padding:0.75rem 1rem;display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:0.8rem;color:var(--text-muted);font-weight:600;">Low Stock Alert</span>
        <span style="font-size:1.1rem;font-weight:700;color:<?= $lowStock > 0 ? 'var(--warning-text)' : 'var(--text-dark)' ?>;font-family:var(--font-mono);">
            <?= number_format($lowStock) ?>
        </span>
    </div>
    <div style="background:#ffffff;border:1px solid var(--admin-border);padding:0.75rem 1rem;display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:0.8rem;color:var(--text-muted);font-weight:600;">Average Order Value</span>
        <span style="font-size:1.1rem;font-weight:700;color:var(--text-dark);font-family:var(--font-mono);"><?= formatPrice($aov) ?></span>
    </div>
</div>

<!-- Main Analytics Grid: Sales Chart & Category Mix -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">
    <!-- Sales & Orders Chart -->
    <div class="admin-card" style="margin-bottom:0;display:flex;flex-direction:column;justify-content:space-between;">
        <div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem;padding-bottom:0.75rem;border-bottom:1px solid var(--admin-border);">
                <div>
                    <h2 class="admin-card-title" style="margin:0;">Sales &amp; Orders Trend</h2>
                    <p style="color:var(--text-muted);font-size:0.75rem;margin-top:0.15rem;">Daily revenue and order volume overview</p>
                </div>
                <div style="display:flex;align-items:center;gap:12px;font-size:0.75rem;">
                    <span style="display:inline-flex;align-items:center;gap:5px;">
                        <span style="width:10px;height:10px;background:#0f172a;display:inline-block;"></span> Revenue
                    </span>
                    <span style="display:inline-flex;align-items:center;gap:5px;">
                        <span style="width:10px;height:10px;background:#64748b;display:inline-block;"></span> Orders
                    </span>
                </div>
            </div>
        </div>

        <div style="height:260px;position:relative;margin-top:0.5rem;">
            <canvas id="salesTrendsChart"></canvas>
        </div>
    </div>

    <!-- Category Sales Breakdown -->
    <div class="admin-card" style="margin-bottom:0;display:flex;flex-direction:column;justify-content:space-between;">
        <div>
            <div style="margin-bottom:0.75rem;padding-bottom:0.75rem;border-bottom:1px solid var(--admin-border);">
                <h2 class="admin-card-title" style="margin:0;">Category Breakdown</h2>
                <p style="color:var(--text-muted);font-size:0.75rem;margin-top:0.15rem;">Sales distribution by product category</p>
            </div>

            <div style="height:180px;position:relative;display:flex;align-items:center;justify-content:center;margin:0.75rem 0;">
                <canvas id="categoryShareChart"></canvas>
            </div>
        </div>

        <!-- Legend breakdown -->
        <div style="display:flex;flex-direction:column;gap:0.4rem;padding-top:0.75rem;border-top:1px solid var(--admin-border);">
            <?php
            $colors = ['#0f172a', '#334155', '#475569', '#64748b', '#94a3b8'];
            foreach ($categoryMix as $idx => $mix):
                $pct = $totalMixVolume > 0 ? round(($mix['total_volume'] / $totalMixVolume) * 100) : 0;
                $color = $colors[$idx % count($colors)];
            ?>
                <div style="display:flex;align-items:center;justify-content:space-between;padding:0.35rem 0.5rem;background:#f8fafc;border:1px solid var(--admin-border);font-size:0.75rem;">
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="width:7px;height:7px;background:<?= $color ?>;display:inline-block;"></span>
                        <span style="font-weight:600;color:var(--text-dark);"><?= e($mix['name']) ?></span>
                    </div>
                    <div>
                        <span style="font-family:var(--font-mono);font-weight:700;color:var(--text-dark);"><?= formatPrice($mix['total_volume']) ?></span>
                        <span style="color:var(--text-muted);font-size:0.7rem;margin-left:3px;">(<?= $pct ?>%)</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Secondary Row: Recent Orders & Inventory Watch -->
<div style="display:grid;grid-template-columns:1.5fr 1fr;gap:1.5rem;">
    <!-- Recent Orders Table Card -->
    <div class="admin-card" style="margin-bottom:0;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;padding-bottom:0.75rem;border-bottom:1px solid var(--admin-border);">
            <div>
                <h2 class="admin-card-title" style="margin:0;">Recent Orders</h2>
                <p style="color:var(--text-muted);font-size:0.75rem;margin-top:0.15rem;">Latest incoming customer purchases</p>
            </div>
            <a href="<?= ADMIN_URL ?>/orders.php" style="color:var(--primary);font-size:0.8rem;font-weight:600;display:inline-flex;align-items:center;gap:4px;">
                View All Orders &rarr;
            </a>
        </div>

        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th style="text-align:right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentOrders)): ?>
                        <tr><td colspan="4" style="text-align:center;padding:2rem;color:var(--text-muted);">No orders found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentOrders as $ord):
                            $statusPillClass = match($ord['order_status']) {
                                'delivered'  => 'pill-success',
                                'cancelled', 'refunded' => 'pill-danger',
                                'shipped', 'processing' => 'pill-neutral',
                                default      => 'pill-warning'
                            };
                        ?>
                            <tr>
                                <td>
                                    <a href="<?= ADMIN_URL ?>/order-detail.php?id=<?= $ord['id'] ?>" style="font-family:var(--font-mono);font-weight:700;color:var(--text-dark);">
                                        #<?= e($ord['order_number']) ?>
                                    </a>
                                    <div style="font-size:0.7rem;color:var(--text-muted);"><?= (int)($ord['item_count'] ?? 1) ?> item(s)</div>
                                </td>
                                <td>
                                    <div style="font-weight:600;color:var(--text-dark);"><?= e($ord['shipping_name']) ?></div>
                                    <div style="font-size:0.7rem;color:var(--text-muted);"><?= e($ord['customer_email']) ?></div>
                                </td>
                                <td>
                                    <span class="status-pill <?= $statusPillClass ?>">
                                        <span class="status-dot"></span>
                                        <?= ucfirst($ord['order_status']) ?>
                                    </span>
                                </td>
                                <td style="text-align:right;font-family:var(--font-mono);font-weight:700;color:var(--text-dark);">
                                    <?= formatPrice($ord['total_amount']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Inventory Watch Card -->
    <div class="admin-card" style="margin-bottom:0;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;padding-bottom:0.75rem;border-bottom:1px solid var(--admin-border);">
            <div>
                <div style="display:flex;align-items:center;gap:6px;">
                    <h2 class="admin-card-title" style="margin:0;">Inventory Alert</h2>
                    <?php if ($lowStock > 0): ?>
                        <span class="badge badge-warning"><?= $lowStock ?> Low</span>
                    <?php else: ?>
                        <span class="badge badge-success">Optimal</span>
                    <?php endif; ?>
                </div>
                <p style="color:var(--text-muted);font-size:0.75rem;margin-top:0.15rem;">Items at or below safety threshold</p>
            </div>
            <a href="<?= ADMIN_URL ?>/inventory.php" class="btn btn-outline btn-sm">Manage</a>
        </div>

        <div style="display:flex;flex-direction:column;gap:0.5rem;">
            <?php if (empty($criticalStock)): ?>
                <div style="text-align:center;padding:2rem;color:var(--text-muted);font-size:0.8rem;">All products have healthy stock.</div>
            <?php else: ?>
                <?php foreach ($criticalStock as $prod):
                    $isCritical = ($prod['stock_quantity'] <= $prod['low_stock_threshold']);
                    $isOutOfStock = ($prod['stock_quantity'] <= 0);
                ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:0.5rem 0.65rem;background:#f8fafc;border:1px solid var(--admin-border);">
                        <div style="display:flex;align-items:center;gap:0.65rem;min-width:0;">
                            <img src="<?= getImageUrl($prod['primary_image']) ?>" style="width:34px;height:34px;object-fit:cover;background:#e2e8f0;flex-shrink:0;border:1px solid var(--admin-border);" alt="">
                            <div style="min-width:0;">
                                <div style="font-weight:600;font-size:0.8rem;color:var(--text-dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:160px;">
                                    <?= e($prod['name']) ?>
                                </div>
                                <div style="font-size:0.72rem;font-family:var(--font-mono);color:<?= $isOutOfStock ? 'var(--danger-text)' : 'var(--warning-text)' ?>;font-weight:600;">
                                    <?= (int)$prod['stock_quantity'] ?> left <span style="color:var(--text-muted);font-weight:400;">(Min: <?= (int)$prod['low_stock_threshold'] ?>)</span>
                                </div>
                            </div>
                        </div>
                        <a href="<?= ADMIN_URL ?>/edit-product.php?id=<?= $prod['id'] ?>" class="btn btn-primary btn-sm" style="padding:0.25rem 0.6rem;font-size:0.72rem;flex-shrink:0;">
                            Update
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Fetch and render Chart.js charts
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const res = await fetch(`${window.ADMIN_URL}/../api/dashboard.php`);
        const json = await res.json();
        if (json.success && json.data && json.data.charts) {
            const c = json.data.charts;

            // 1. Sales Trends Line Chart
            const ctxTrends = document.getElementById('salesTrendsChart').getContext('2d');

            new Chart(ctxTrends, {
                type: 'line',
                data: {
                    labels: c.labels,
                    datasets: [
                        {
                            label: 'Revenue',
                            data: c.revenue,
                            borderColor: '#0f172a',
                            backgroundColor: 'rgba(15, 23, 42, 0.04)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.1,
                            pointRadius: 3,
                            pointBackgroundColor: '#0f172a',
                            yAxisID: 'y'
                        },
                        {
                            label: 'Orders',
                            data: c.orders,
                            borderColor: '#64748b',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            borderDash: [3, 3],
                            tension: 0.1,
                            pointRadius: 2,
                            pointBackgroundColor: '#64748b',
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: { family: 'Inter', size: 12 },
                            bodyFont: { family: 'JetBrains Mono', size: 12 },
                            padding: 8,
                            cornerRadius: 0
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: 'JetBrains Mono', size: 10 }, color: '#64748b' }
                        },
                        y: {
                            type: 'linear',
                            position: 'left',
                            beginAtZero: true,
                            grid: { color: '#e2e8f0' },
                            ticks: { font: { family: 'JetBrains Mono', size: 10 }, color: '#64748b' }
                        },
                        y1: {
                            type: 'linear',
                            position: 'right',
                            beginAtZero: true,
                            grid: { drawOnChartArea: false },
                            ticks: { font: { family: 'JetBrains Mono', size: 10 }, color: '#64748b' }
                        }
                    }
                }
            });

            // 2. Category Mix Donut Chart
            if (c.categories && c.categories.labels.length > 0) {
                const ctxCat = document.getElementById('categoryShareChart').getContext('2d');
                new Chart(ctxCat, {
                    type: 'doughnut',
                    data: {
                        labels: c.categories.labels,
                        datasets: [{
                            data: c.categories.values,
                            backgroundColor: ['#0f172a', '#334155', '#475569', '#64748b', '#94a3b8'],
                            borderWidth: 1,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                titleFont: { family: 'Inter', size: 12 },
                                bodyFont: { family: 'JetBrains Mono', size: 12 },
                                cornerRadius: 0
                            }
                        }
                    }
                });
            }
        }
    } catch (e) {
        console.error("Dashboard chart initialization error:", e);
    }
});
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
