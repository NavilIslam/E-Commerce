<?php
$pageTitle   = "Terms of service";
$policyIntro = "The terms you agree to when you order from us.";
require_once __DIR__ . '/config/config.php';

$name = e(getSetting('site_name', APP_NAME));

$policyBody = <<<HTML
<h2>Using this site</h2>
<p>By browsing or ordering from {$name} you agree to these terms. If you do not agree with them,
please do not use the site.</p>

<h2>Your account</h2>
<p>You are responsible for keeping your password confidential and for activity that happens under
your account. Tell us promptly if you believe someone else has accessed it.</p>

<h2>Orders</h2>
<p>Placing an order is an offer to buy. We accept your order when we confirm it. We may decline or
cancel an order if:</p>
<ul>
    <li>the item is out of stock</li>
    <li>there was a pricing or product description error</li>
    <li>we cannot verify the delivery details or suspect fraud</li>
</ul>
<p>If we cancel an order you have already paid for, we refund it in full.</p>

<h2>Pricing</h2>
<p>Prices are shown in Bangladeshi Taka and include applicable taxes unless stated otherwise.
Delivery charges are shown separately in your cart and at checkout before you place the order.
We correct pricing errors when we find them, even after an order is placed; if a correction
affects your order we contact you before proceeding.</p>

<h2>Payment</h2>
<p>Cash on delivery is available. For wallet, card and bank transfer we contact you with
instructions after you place the order. Online card capture is not enabled on this store at present.</p>

<h2>Product information</h2>
<p>We describe products as accurately as we can. Photographs are illustrative and colours can vary
between screens. Where a manufacturer changes packaging or specification, the item you receive may
differ slightly from the photograph.</p>

<h2>Returns</h2>
<p>Returns are covered by our <a href="returns.php">returns policy</a>, which forms part of these
terms.</p>

<h2>Reviews</h2>
<p>Reviews you submit must be your own honest opinion. We remove reviews that are abusive,
unlawful, or not about the product.</p>

<h2>Liability</h2>
<p>Nothing in these terms limits liability that cannot be limited by law. Otherwise our liability
in relation to any order is limited to the value of that order.</p>

<h2>Contact</h2>
<p>Questions about these terms can go to our <a href="contact.php">support team</a>.</p>
HTML;

require_once __DIR__ . '/includes/policy-page.php';
