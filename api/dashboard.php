<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if (!Auth::isAdminOrStaff()) {
    jsonError('Unauthorized', [], 403);
}

$db = Database::getInstance();

// 1. Sales & Revenue Stats
$totalSales = (float) $db->fetchColumn("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE order_status != 'cancelled'");
$todaySales = (float) $db->fetchColumn("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE order_status != 'cancelled' AND DATE(created_at) = CURDATE()");
$thisMonthSales = (float) $db->fetchColumn("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE order_status != 'cancelled' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");

// 2. Counts
$totalOrders = (int) $db->fetchColumn("SELECT COUNT(*) FROM orders");
$pendingOrders = (int) $db->fetchColumn("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'");
$deliveredOrders = (int) $db->fetchColumn("SELECT COUNT(*) FROM orders WHERE order_status = 'delivered'");
$totalCustomers = (int) $db->fetchColumn("SELECT COUNT(*) FROM users WHERE role = 'customer'");
$totalProducts = (int) $db->fetchColumn("SELECT COUNT(*) FROM products WHERE is_active = 1");
$lowStockProducts = (int) $db->fetchColumn("SELECT COUNT(*) FROM products WHERE stock_quantity <= low_stock_threshold AND is_active = 1");
$activeOffers = (int) $db->fetchColumn("SELECT COUNT(*) FROM offers WHERE is_active = 1 AND start_date <= NOW() AND end_date >= NOW()");

// 3. Sales over last 7 days for Chart.js
$salesLast7Days = $db->fetchAll(
    "SELECT DATE(created_at) as date, COALESCE(SUM(total_amount), 0) as total, COUNT(id) as orders 
     FROM orders 
     WHERE order_status != 'cancelled' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
     GROUP BY DATE(created_at)
     ORDER BY DATE(created_at) ASC"
);

// Fill missing days with 0
$chartDates = [];
$chartRevenue = [];
$chartOrders = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $chartDates[] = date('M d', strtotime($d));
    $rev = 0;
    $ord = 0;
    foreach ($salesLast7Days as $row) {
        if ($row['date'] === $d) {
            $rev = (float)$row['total'];
            $ord = (int)$row['orders'];
            break;
        }
    }
    $chartRevenue[] = $rev;
    $chartOrders[] = $ord;
}

// 4. Best Selling Products
$topProducts = $db->fetchAll(
    "SELECT p.id, p.name, p.slug, p.price, p.total_sold,
            (SELECT image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC LIMIT 1) as primary_image
     FROM products p
     ORDER BY p.total_sold DESC
     LIMIT 5"
);

// 5. Category Share
$categorySales = $db->fetchAll(
    "SELECT c.name, COUNT(oi.id) as item_sales, COALESCE(SUM(oi.total_price), 0) as total_revenue
     FROM categories c
     JOIN products p ON c.id = p.category_id
     JOIN order_items oi ON p.id = oi.product_id
     JOIN orders o ON oi.order_id = o.id
     WHERE o.order_status != 'cancelled'
     GROUP BY c.id
     ORDER BY total_revenue DESC
     LIMIT 5"
);

// 6. Recent Orders
$recentOrders = $db->fetchAll(
    "SELECT o.*, u.first_name, u.last_name, u.email as customer_email 
     FROM orders o 
     JOIN users u ON o.user_id = u.id 
     ORDER BY o.created_at DESC 
     LIMIT 7"
);

jsonSuccess('Dashboard analytics', [
    'kpis' => [
        'total_sales'        => $totalSales,
        'formatted_sales'    => formatPrice($totalSales),
        'today_sales'        => $todaySales,
        'formatted_today'    => formatPrice($todaySales),
        'month_sales'        => $thisMonthSales,
        'formatted_month'    => formatPrice($thisMonthSales),
        'total_orders'       => $totalOrders,
        'pending_orders'     => $pendingOrders,
        'delivered_orders'   => $deliveredOrders,
        'total_customers'    => $totalCustomers,
        'total_products'     => $totalProducts,
        'low_stock_products' => $lowStockProducts,
        'active_offers'      => $activeOffers,
    ],
    'charts' => [
        'labels'   => $chartDates,
        'revenue'  => $chartRevenue,
        'orders'   => $chartOrders,
        'categories' => [
            'labels' => array_column($categorySales, 'name'),
            'values' => array_map('floatval', array_column($categorySales, 'total_revenue')),
        ],
    ],
    'top_products'  => $topProducts,
    'recent_orders' => $recentOrders,
]);
