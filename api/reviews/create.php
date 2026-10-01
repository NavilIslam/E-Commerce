<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method Not Allowed', [], 405);
}

if (!Auth::check()) {
    jsonError('Please log in to submit a review.', ['require_login' => true], 401);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$productId = (int)($input['product_id'] ?? 0);
$rating = (int)($input['rating'] ?? 5);
$comment = trim($input['comment'] ?? '');

if ($productId <= 0 || empty($comment)) {
    jsonError('Please provide a rating and review comment.');
}

$reviewModel = new Review();
$result = $reviewModel->create(Auth::id(), $productId, $rating, $comment);

if (!$result['success']) {
    jsonError($result['message']);
}

jsonSuccess($result['message']);
