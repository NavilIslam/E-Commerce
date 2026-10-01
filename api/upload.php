<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if (!Auth::isAdminOrStaff()) {
    jsonError('Unauthorized access.', [], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method Not Allowed', [], 405);
}

if (empty($_FILES['image'])) {
    jsonError('No image file was provided.');
}

$type = $_POST['type'] ?? 'products';
if (!in_array($type, ['products', 'categories', 'banners', 'avatars'])) {
    $type = 'products';
}

$uploader = new FileUpload($type);
$fileName = $uploader->upload($_FILES['image']);

if (!$fileName) {
    jsonError($uploader->getFirstError() ?: 'Upload failed.');
}

jsonSuccess('Image uploaded successfully', [
    'file_name' => $fileName,
    'url'       => UPLOAD_URL . '/' . $type . '/' . $fileName,
]);
