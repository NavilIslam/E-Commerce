<?php
$pageTitle = "About us";
$metaDescription = "NovaMart is an online retailer based in Dhaka, delivering electronics, fashion, home, beauty, sports and books across Bangladesh.";
require_once __DIR__ . '/includes/header.php';

$categoryModel = new Category();
$categories    = $categoryModel->getAll(true);
$productCount  = array_sum(array_column($categories, 'product_count'));
?>

<div class="container page">
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="<?= BASE_URL ?>/index.php">Home</a>
        <?= icon('chevron-r') ?>
        <span aria-current="page">About us</span>
    </nav>

    <div class="page-narrow">
        <div class="page-head">
            <h1 class="page-title">About <?= e(getSetting('site_name', APP_NAME)) ?></h1>
            <p class="page-sub">An online retailer based in Dhaka, delivering across Bangladesh.</p>
        </div>

        <div class="card card-lg">
            <div class="prose">
                <p>
                    We sell <?= $productCount ?> products across <?= count($categories) ?> departments (electronics, fashion, home and living, beauty, sports, and books) and ship them nationwide from our warehouse in Dhaka.
                </p>

                <h2>How we work</h2>
                <p>
                    We buy from authorised distributors and importers rather than the grey market, so items
                    that carry a manufacturer warranty are sold with it. Stock levels shown on the site are
                    the real quantities in our warehouse; when something is out of stock we say so rather
                    than taking the order and sorting it out later.
                </p>
                <p>
                    Orders placed before 4pm on a working day are normally dispatched the same day.
                    Delivery is free over <?= formatPrice(getSetting('free_shipping_threshold', '2000')) ?>
                    and a flat <?= formatPrice(getSetting('shipping_inside_city', '60')) ?> below that.
                </p>

                <h2>Pricing</h2>
                <p>
                    The price on the product page is the price you pay, plus delivery where it applies.
                    Discounts are shown against the original price, and your cart itemises every reduction
                    before you commit to the order.
                </p>

                <h2>If something goes wrong</h2>
                <p>
                    You have 7 days from delivery to return most items. The full details, including
                    what we cannot accept back, are on our <a href="<?= BASE_URL ?>/returns.php">returns page</a>.
                    If an order arrives damaged or incorrect, we cover the return cost.
                </p>

                <h2>Contact</h2>
                <p>
                    Our support team is available Saturday to Thursday, 10am to 7pm, on
                    <?= e(getSetting('site_phone', '+880 9612-000000')) ?> or through the
                    <a href="<?= BASE_URL ?>/contact.php">contact page</a>.
                </p>
            </div>
        </div>

        <div class="trust-strip" style="margin-top:var(--space-8)">
            <div class="trust-item">
                <?= icon('package') ?>
                <div>
                    <p class="trust-title"><?= $productCount ?> products</p>
                    <p class="trust-desc">Across <?= count($categories) ?> departments</p>
                </div>
            </div>
            <div class="trust-item">
                <?= icon('truck') ?>
                <div>
                    <p class="trust-title">Nationwide delivery</p>
                    <p class="trust-desc">Dispatched from Dhaka</p>
                </div>
            </div>
            <div class="trust-item">
                <?= icon('refresh') ?>
                <div>
                    <p class="trust-title">7-day returns</p>
                    <p class="trust-desc">On unused items</p>
                </div>
            </div>
            <div class="trust-item">
                <?= icon('headset') ?>
                <div>
                    <p class="trust-title">Support 10am&ndash;7pm</p>
                    <p class="trust-desc">Saturday to Thursday</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
