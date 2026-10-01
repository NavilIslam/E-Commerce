<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method Not Allowed', [], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$result = Auth::register($input);

if (!$result['success']) {
    jsonError('Registration failed', $result['errors'] ?? [], 422);
}

jsonSuccess('Registration successful! Welcome to ' . APP_NAME . '.', [
    'redirect' => BASE_URL . '/index.php',
]);
