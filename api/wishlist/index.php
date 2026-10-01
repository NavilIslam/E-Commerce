<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if (!Auth::check()) {
    jsonError('Please log in to view your wishlist.', ['require_login' => true], 401);
}

$wishlist = new Wishlist();
$items = $wishlist->getItems();

jsonSuccess('Wishlist retrieved', [
    'items' => $items,
    'count' => count($items),
]);
