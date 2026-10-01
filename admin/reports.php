<?php
$pageTitle = "Reports & Analytics";
require_once __DIR__ . '/includes/admin-header.php';

requirePermission('view_reports');

$db = Database::getInstance();

// Date range filters
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// 1. Sales Report
$salesStats = $db->fetchOne(
    "SELECT COUNT(id) as order_count,
            COALESCE(SUM(total_amount), 0) as total_revenue,
            COALESCE(AVG(total_amount), 0) as avg_order_value,
            COALESCE(SUM(discount_amount + coupon_discount), 0) as total_discounts
     FROM orders 
     WHERE order_status != 'cancelled' 
       AND created_at >= :start_date AND created_at <= :end_date",
    [':start_date' => $startDate . ' 00:00:00', ':end_date' => $endDate . ' 23:59:59']
);

// 2. Best-Selling Products in Range
$topProducts = $db->fetchAll(
    "SELECT p.id, p.name, p.sku, c.name as category_name,
            SUM(oi.quantity) as units_sold,
            SUM(oi.total_price) as product_revenue
     FROM order_items oi
     JOIN orders o ON oi.order_id = o.id
     JOIN products p ON oi.product_id = p.id
     LEFT JOIN categories c ON p.category_id = c.id
     WHERE o.order_status != 'cancelled'
       AND o.created_at >= :start_date AND o.created_at <= :end_date
     GROUP BY p.id
     ORDER BY product_revenue DESC
     LIMIT 10",
    [':start_date' => $startDate . ' 00:00:00', ':end_date' => $endDate . ' 23:59:59']
);

// 3. Top Spending Customers
$topCustomers = $db->fetchAll(
    "SELECT u.id, u.first_name, u.last_name, u.email,
            COUNT(o.id) as orders_count,
            SUM(o.total_amount) as total_spent
     FROM users u
     JOIN orders o ON u.id = o.user_id
     WHERE o.order_status != 'cancelled'
       AND o.created_at >= :start_date AND o.created_at <= :end_date
     GROUP BY u.id
     ORDER BY total_spent DESC
     LIMIT 8",
    [':start_date' => $startDate . ' 00:00:00', ':end_date' => $endDate . ' 23:59:59']
);

// 4. Coupon Usage Report
$couponReport = $db->fetchAll(
    "SELECT c.code, COUNT(cu.id) as redemptions, SUM(cu.discount_amount) as total_savings
     FROM coupons c
     JOIN coupon_usage cu ON c.id = cu.coupon_id
     WHERE cu.used_at >= :start_date AND cu.used_at <= :end_date
     GROUP BY c.id
     ORDER BY redemptions DESC",
    [':start_date' => $startDate . ' 00:00:00', ':end_date' => $endDate . ' 23:59:59']
);
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
    <div>
        <h1 style="font-size:1.5rem;font-weight:700;">Sales Reports</h1>
        <p style="color:var(--text-muted);font-size:0.825rem;">Sales volume, top-selling products, and customer purchase summaries.</p>
    </div>

    <!-- Date Range Picker -->
    <form method="GET" style="display:flex;gap:0.5rem;align-items:center;">
        <input type="date" name="start_date" class="form-control" value="<?= e($startDate) ?>" style="width:auto;">
        <span>to</span>
        <input type="date" name="end_date" class="form-control" value="<?= e($endDate) ?>" style="width:auto;">
        <button type="submit" class="btn btn-primary btn-sm">Filter Range</button>
    </form>
</div>

<!-- Performance KPIs -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div>
            <div class="kpi-title">Selected Revenue</div>
            <div class="kpi-value"><?= formatPrice($salesStats['total_revenue']) ?></div>
        </div>
        <div class="kpi-icon"><?= icon('cash') ?></div>
    </div>

    <div class="kpi-card">
        <div>
            <div class="kpi-title">Orders Executed</div>
            <div class="kpi-value"><?= (int)$salesStats['order_count'] ?></div>
        </div>
        <div class="kpi-icon"><?= icon('package') ?></div>
    </div>

    <div class="kpi-card">
        <div>
            <div class="kpi-title">Average Order Value</div>
            <div class="kpi-value"><?= formatPrice($salesStats['avg_order_value']) ?></div>
        </div>
        <div class="kpi-icon"><?= icon('percent') ?></div>
    </div>

    <div class="kpi-card">
        <div>
            <div class="kpi-title">Discounts Absorbed</div>
            <div class="kpi-value"><?= formatPrice($salesStats['total_discounts']) ?></div>
        </div>
        <div class="kpi-icon"><?= icon('gift') ?></div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1.8fr 1.2fr;gap:1.5rem;margin-bottom:2rem;">
    <!-- Best-Selling Products -->
    <div class="admin-card" style="padding:0;overflow:hidden;">
        <div style="padding:1.25rem;border-bottom:1px solid var(--admin-border);">
            <h3 class="admin-card-title">Top Revenue Generating Products</h3>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Units</th>
                    <th style="text-align:right;">Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($topProducts)): ?>
                    <tr><td colspan="4" style="text-align:center;padding:2rem;">No product sales in this timeframe.</td></tr>
                <?php else: ?>
                    <?php foreach ($topProducts as $p): ?>
                        <tr>
                            <td>
                                <strong><?= e($p['name']) ?></strong>
                                <div style="font-size:0.75rem;color:var(--text-muted);font-family:monospace;"><?= e($p['sku'] ?? 'N/A') ?></div>
                            </td>
                            <td><?= e($p['category_name'] ?? 'N/A') ?></td>
                            <td style="font-weight:700;"><?= $p['units_sold'] ?></td>
                            <td style="text-align:right;font-weight:700;color:var(--primary);"><?= formatPrice($p['product_revenue']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Top Spending Customers -->
    <div class="admin-card" style="padding:0;overflow:hidden;">
        <div style="padding:1.25rem;border-bottom:1px solid var(--admin-border);">
            <h3 class="admin-card-title">Top Valued Customers</h3>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Orders</th>
                    <th style="text-align:right;">Total Spent</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($topCustomers)): ?>
                    <tr><td colspan="3" style="text-align:center;padding:2rem;">No customer activity recorded.</td></tr>
                <?php else: ?>
                    <?php foreach ($topCustomers as $c): ?>
                        <tr>
                            <td>
                                <strong><?= e($c['first_name'] . ' ' . $c['last_name']) ?></strong>
                                <div style="font-size:0.75rem;color:var(--text-muted);"><?= e($c['email']) ?></div>
                            </td>
                            <td><?= $c['orders_count'] ?></td>
                            <td style="text-align:right;font-weight:700;color:var(--primary);"><?= formatPrice($c['total_spent']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Coupon Usage Analytics -->
<div class="admin-card" style="padding:0;overflow:hidden;">
    <div style="padding:1.25rem;border-bottom:1px solid var(--admin-border);">
        <h3 class="admin-card-title">Promo Coupon Redemption Performance</h3>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Coupon Code</th>
                <th>Total Redemptions</th>
                <th style="text-align:right;">Customer Savings Delivered</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($couponReport)): ?>
                <tr><td colspan="3" style="text-align:center;padding:2rem;">No coupons redeemed in this period.</td></tr>
            <?php else: ?>
                <?php foreach ($couponReport as $cr): ?>
                    <tr>
                        <td><strong style="font-family:monospace;"><?= e($cr['code']) ?></strong></td>
                        <td><strong><?= $cr['redemptions'] ?></strong> times used</td>
                        <td style="text-align:right;font-weight:700;color:var(--text-dark);font-family:var(--font-mono);"><?= formatPrice($cr['total_savings']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
