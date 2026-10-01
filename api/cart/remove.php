<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'DELETE'])) {
    jsonError('Method Not Allowed', [], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$productId = (int)($input['product_id'] ?? $_GET['product_id'] ?? 0);

if ($productId <= 0) {
    jsonError('Invalid product ID.');
}

$cart = new Cart();
$result = $cart->removeItem($productId);

$details = $cart->getDetails();
jsonSuccess('Item removed from cart', $details);
