<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT'])) {
    jsonError('Method Not Allowed', [], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$productId = (int)($input['product_id'] ?? 0);
$quantity = (int)($input['quantity'] ?? 0);

if ($productId <= 0) {
    jsonError('Invalid product ID.');
}

$cart = new Cart();
$result = $cart->updateItem($productId, $quantity);

if (!$result['success']) {
    jsonError($result['message']);
}

$details = $cart->getDetails();
jsonSuccess($result['message'], $details);
