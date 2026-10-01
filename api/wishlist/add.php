<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method Not Allowed', [], 405);
}

if (!Auth::check()) {
    jsonError('Please log in to add items to your wishlist.', ['require_login' => true], 401);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$productId = (int)($input['product_id'] ?? 0);

if ($productId <= 0) {
    jsonError('Invalid product ID.');
}

$wishlist = new Wishlist();
$result = $wishlist->add($productId);

if (!$result['success']) {
    jsonError($result['message']);
}

jsonSuccess($result['message']);
