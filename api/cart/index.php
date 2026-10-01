<?php
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$cart = new Cart();
$details = $cart->getDetails();

jsonSuccess('Cart retrieved', $details);
