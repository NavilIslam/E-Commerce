<?php
$pageTitle   = "Privacy policy";
$policyIntro = "What we collect, why we collect it, and what we do with it.";
require_once __DIR__ . '/config/config.php';

$email = e(getSetting('site_email', 'support@novamart.com'));
$name  = e(getSetting('site_name', APP_NAME));

$policyBody = <<<HTML
<h2>What we collect</h2>
<p>When you create an account or place an order we collect the information you give us:</p>
<ul>
    <li>Your name, email address and phone number</li>
    <li>Delivery addresses you save or enter at checkout</li>
    <li>Your order history and any reviews you write</li>
</ul>
<p>We also record basic technical information such as your session so the cart works, and server
logs used for security and troubleshooting.</p>

<h2>What we do not collect</h2>
<p>We do not store card numbers. Online card payment is not currently enabled on this store; where
you choose card or bank transfer we contact you separately to arrange it.</p>

<h2>How we use it</h2>
<ul>
    <li>To process, deliver and support your orders</li>
    <li>To let you sign in and see your order history</li>
    <li>To respond when you contact us</li>
    <li>To detect and prevent fraud and abuse</li>
</ul>
<p>We do not sell your personal information.</p>

<h2>Who we share it with</h2>
<p>We share your name, address and phone number with the courier delivering your order, because
they need it to deliver. We may disclose information where the law requires it.</p>

<h2>How long we keep it</h2>
<p>Order records are kept for as long as we are required to retain them for accounting and
warranty purposes. You can ask us to close your account at any time.</p>

<h2>Cookies</h2>
<p>We use a session cookie to keep you signed in and to remember your cart. It is required for the
site to function and is not used for advertising.</p>

<h2>Your choices</h2>
<p>You can view and update your details in your account at any time. To request a copy of your
data or ask us to delete your account, email <a href="mailto:{$email}">{$email}</a>.</p>

<h2>Changes</h2>
<p>If we change this policy we will update this page. Continued use of {$name} after a change means
you accept the updated policy.</p>
HTML;

require_once __DIR__ . '/includes/policy-page.php';
