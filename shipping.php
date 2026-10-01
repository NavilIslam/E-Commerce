<?php
$pageTitle   = "Delivery";
$policyIntro = "How and when your order reaches you.";
require_once __DIR__ . '/config/config.php';

$free = formatPrice(getSetting('free_shipping_threshold', '5000'));
$fee  = formatPrice(getSetting('shipping_inside_city', '60'));

$policyBody = <<<HTML
<h2>Delivery charges</h2>
<p>Delivery is <strong>free on orders over {$free}</strong>. Below that, a flat charge of
<strong>{$fee}</strong> applies. The exact charge is always shown in your cart and again on the
checkout page before you place the order.</p>

<h2>Delivery times</h2>
<p>Orders are dispatched from our Dhaka warehouse. Orders placed before 4pm on a working day are
normally dispatched the same day.</p>
<ul>
    <li><strong>Inside Dhaka:</strong> typically 1 to 2 working days</li>
    <li><strong>Outside Dhaka:</strong> typically 2 to 4 working days</li>
</ul>
<p>These are estimates, not guarantees. Delivery can take longer during public holidays and peak
sale periods.</p>

<h2>Tracking your order</h2>
<p>You can see the current status of every order in
<a href="orders.php">your account</a>. The status moves from pending to processing, shipped and
finally delivered.</p>

<h2>Receiving your order</h2>
<p>Please check the parcel in front of the courier where possible. If anything is damaged or
missing, contact us within 48 hours so we can put it right.</p>

<h2>Failed deliveries</h2>
<p>Our courier will attempt delivery and will call the phone number on your order. If we cannot
reach you after two attempts, the parcel is returned to us and we will contact you to arrange
redelivery.</p>
HTML;

require_once __DIR__ . '/includes/policy-page.php';
