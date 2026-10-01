<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method Not Allowed', [], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$code = trim($input['code'] ?? '');
$subtotal = (float)($input['subtotal'] ?? 0.0);

if (empty($code)) {
    jsonError('Please enter a coupon code.');
}

$couponModel = new Coupon();
$result = $couponModel->validate($code, $subtotal, Auth::id());

if (!$result['valid']) {
    jsonError($result['message']);
}

jsonSuccess($result['message'], [
    'code'            => $result['coupon']['code'],
    'discount_amount' => $result['discount_amount'],
    'formatted_discount' => formatPrice($result['discount_amount']),
]);
