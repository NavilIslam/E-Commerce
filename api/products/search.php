<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');
if (mb_strlen($query) < 2) {
    jsonSuccess('Query too short', []);
}

$db = Database::getInstance();
$sql = "SELECT p.id, p.name, p.slug, p.price, p.sale_price, p.brand, c.name as category_name,
               (SELECT image_path FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC LIMIT 1) as primary_image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE (p.name LIKE :q1 OR p.brand LIKE :q2 OR p.sku LIKE :q3 OR c.name LIKE :q4)
          AND p.is_active = 1
        ORDER BY p.total_sold DESC, p.name ASC
        LIMIT 8";

// Emulated prepares are off, so each placeholder must be unique.
$term    = '%' . $query . '%';
$results = $db->fetchAll($sql, [':q1' => $term, ':q2' => $term, ':q3' => $term, ':q4' => $term]);

foreach ($results as &$item) {
    $item['formatted_price'] = formatPrice(!empty($item['sale_price']) ? $item['sale_price'] : $item['price']);
    $item['image_url'] = getImageUrl($item['primary_image']);
    $item['url'] = BASE_URL . '/product.php?slug=' . urlencode($item['slug']);
}

jsonSuccess('Search results', $results);
