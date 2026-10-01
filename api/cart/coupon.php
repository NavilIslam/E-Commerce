<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$cart = new Cart();

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $code = trim($input['code'] ?? '');

    if (empty($code)) {
        jsonError('Please enter a coupon code.');
    }

    $result = $cart->applyCoupon($code);
    if (!$result['success']) {
        jsonError($result['message']);
    }

    $details = $cart->getDetails();
    jsonSuccess($result['message'], $details);
} elseif ($method === 'DELETE' || ($method === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove')) {
    $cart->removeCoupon();
    $details = $cart->getDetails();
    jsonSuccess('Coupon removed.', $details);
} else {
    jsonError('Method Not Allowed', [], 405);
}
