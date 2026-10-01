<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method Not Allowed', [], 405);
}

if (!Auth::check()) {
    jsonError('Please log in to place your order.', ['require_login' => true], 401);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$validator = new Validator($input);
$isValid = $validator->validate([
    'full_name'     => 'required|max:200',
    'phone'         => 'required|phone|max:20',
    'address_line1' => 'required|max:255',
    'city'          => 'required|max:100',
    'payment_method'=> 'required|in:cod,online,bkash,nagad,sslcommerz,card',
]);

if (!$isValid) {
    jsonError($validator->getFirstError() ?: 'Validation failed', $validator->getErrors(), 422);
}

$userId = Auth::id();
$shippingData = [
    'full_name'     => $input['full_name'],
    'phone'         => $input['phone'],
    'address_line1' => $input['address_line1'],
    'address_line2' => $input['address_line2'] ?? null,
    'city'          => $input['city'],
    'area'          => $input['area'] ?? null,
    'postal_code'   => $input['postal_code'] ?? null,
];

// Save address if requested
if (!empty($input['save_address'])) {
    $userModel = new User();
    $userModel->saveAddress($userId, $shippingData);
}

$orderModel = new Order();
$result = $orderModel->createFromCart(
    $userId,
    $shippingData,
    $input['payment_method'],
    $input['notes'] ?? null
);

if (!$result['success']) {
    jsonError($result['message']);
}

jsonSuccess($result['message'], [
    'order_id'     => $result['order_id'],
    'order_number' => $result['order_number'],
    'redirect'     => BASE_URL . '/order.php?id=' . $result['order_id'],
]);
