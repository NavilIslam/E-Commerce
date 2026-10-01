<?php
$pageTitle   = "Frequently asked questions";
$policyIntro = "Quick answers to the things customers ask us most.";
require_once __DIR__ . '/config/config.php';

$free = formatPrice(getSetting('free_shipping_threshold', '5000'));
$fee  = formatPrice(getSetting('shipping_inside_city', '60'));

$policyBody = <<<HTML
<h2>Ordering</h2>

<h3>Do I need an account to order?</h3>
<p>Yes. An account lets you track your order, save delivery addresses and see your order history.
Creating one takes about a minute.</p>

<h3>Can I change or cancel my order?</h3>
<p>Contact us as soon as possible with your order number. If the order has not yet been dispatched
we can usually change or cancel it.</p>

<h3>How do I use a promo code?</h3>
<p>Enter it in the promo code box on the cart page and select Apply. The discount appears in your
order summary straight away.</p>

<h2>Delivery</h2>

<h3>What does delivery cost?</h3>
<p>Free on orders over {$free}. Below that it is a flat {$fee}. Full details are on our
<a href="shipping.php">delivery page</a>.</p>

<h3>How long will my order take?</h3>
<p>Usually 1 to 2 working days inside Dhaka and 2 to 4 working days elsewhere in Bangladesh.</p>

<h3>How do I track my order?</h3>
<p>Sign in and open <a href="orders.php">your orders</a>. Each order shows its current status.</p>

<h2>Payment</h2>

<h3>How can I pay?</h3>
<p>Cash on delivery is available now. For bKash, Nagad, card or bank transfer, choose that option
at checkout and we will send you the payment details.</p>

<h3>Can I pay by card on the website?</h3>
<p>Not yet. Online card capture is not enabled on this store, so we arrange card and bank payments
with you directly after you place the order.</p>

<h2>Returns</h2>

<h3>What is your return window?</h3>
<p>7 days from delivery for unused items in original packaging. See the
<a href="returns.php">returns policy</a> for the full details and exclusions.</p>

<h3>How long do refunds take?</h3>
<p>5 to 7 working days after we receive and check the returned item.</p>

<h2>Products</h2>

<h3>Are your products genuine?</h3>
<p>We source from authorised distributors and importers. Items covered by a manufacturer warranty
are sold with that warranty.</p>

<h3>An item I want is out of stock. What now?</h3>
<p>Save it to your <a href="wishlist.php">wishlist</a> and check back, or
<a href="contact.php">contact us</a> and we will tell you whether it is being restocked.</p>
HTML;

require_once __DIR__ . '/includes/policy-page.php';
