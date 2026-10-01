<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method Not Allowed', [], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

if (empty($email) || empty($password)) {
    jsonError('Please enter both email and password.');
}

$result = Auth::login($email, $password);

if (!$result['success']) {
    jsonError($result['message'], [], 401);
}

$user = $result['user'];
$redirectUrl = BASE_URL . '/index.php';

if (in_array($user['role'], ['staff', 'admin', 'owner'])) {
    $redirectUrl = ADMIN_URL . '/index.php';
}

jsonSuccess('Login successful', [
    'user'     => [
        'id'    => $user['id'],
        'name'  => $user['first_name'] . ' ' . $user['last_name'],
        'email' => $user['email'],
        'role'  => $user['role'],
    ],
    'redirect' => $redirectUrl,
]);
