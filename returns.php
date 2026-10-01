<?php
$pageTitle   = "Returns and refunds";
$policyIntro = "Our 7-day return window and how to use it.";
require_once __DIR__ . '/config/config.php';

$policyBody = <<<HTML
<h2>The short version</h2>
<p>You have <strong>7 days from delivery</strong> to return most items for a refund or exchange,
provided they are unused and in their original packaging.</p>

<h2>What can be returned</h2>
<ul>
    <li>Items that are unused, unworn and in their original packaging with all accessories</li>
    <li>Items that arrived damaged, faulty, or different from what you ordered</li>
</ul>

<h2>What cannot be returned</h2>
<ul>
    <li>Cosmetics, skincare and other personal care items once the seal is broken</li>
    <li>Innerwear and swimwear</li>
    <li>Items damaged through normal use, accident or misuse</li>
    <li>Items returned after the 7-day window has closed</li>
</ul>

<h2>How to start a return</h2>
<ol>
    <li>Contact us with your order number and what you would like to return.</li>
    <li>We confirm whether the item is eligible and how to send it back.</li>
    <li>Pack the item securely with all original accessories and packaging.</li>
</ol>

<h2>Refunds</h2>
<p>Once we receive and inspect the returned item we process the refund within
<strong>5 to 7 working days</strong>. Refunds are issued by the same method you used to pay.
For cash on delivery orders we refund by bKash, Nagad or bank transfer to details you provide.</p>

<h2>Return delivery charges</h2>
<p>If the return is due to our error (a faulty, damaged, or incorrect item), we cover the
return delivery cost. For change-of-mind returns, the return delivery cost is yours.</p>

<h2>Warranty</h2>
<p>Products carrying a manufacturer warranty are covered by that warranty for its stated period.
Warranty claims are handled by the manufacturer's service centre; contact us and we will point you
to the right one.</p>
HTML;

require_once __DIR__ . '/includes/policy-page.php';
