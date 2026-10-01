<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'DELETE'])) {
    jsonError('Method Not Allowed', [], 405);
}

if (!Auth::check()) {
    jsonError('Please sign in to use your wishlist.', ['require_login' => true], 401);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$productId = (int)($input['product_id'] ?? $_GET['product_id'] ?? 0);

if ($productId <= 0) {
    jsonError('Invalid product ID.');
}

$wishlist = new Wishlist();
$result = $wishlist->remove($productId);

jsonSuccess($result['message']);
