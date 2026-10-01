<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$productModel = new Product();

if (!empty($_GET['id'])) {
    $product = $productModel->findById((int)$_GET['id']);
    if ($product && $product['is_active']) {
        jsonSuccess('Product retrieved', $product);
    } else {
        jsonError('Product not found', [], 404);
    }
}

$filters = [
    'is_active'   => 1,
    'category'    => $_GET['category'] ?? null,
    'category_id' => $_GET['category_id'] ?? null,
    'brand'       => $_GET['brand'] ?? null,
    'min_price'   => $_GET['min_price'] ?? null,
    'max_price'   => $_GET['max_price'] ?? null,
    'on_sale'     => $_GET['sale'] ?? null,
    'in_stock'    => $_GET['in_stock'] ?? null,
    'min_rating'  => $_GET['rating'] ?? null,
    'search'      => $_GET['search'] ?? null,
    'sort'        => $_GET['sort'] ?? 'newest',
];

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = min(48, max(1, (int)($_GET['per_page'] ?? 12)));

$productModel = new Product();
$result = $productModel->getAll($filters, $page, $perPage);

jsonSuccess('Products retrieved', $result['products'], [
    'pagination' => [
        'page'        => $result['page'],
        'per_page'    => $result['per_page'],
        'total'       => $result['total'],
        'total_pages' => $result['total_pages'],
    ]
]);
