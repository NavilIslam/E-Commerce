<?php
require_once __DIR__ . '/../config/config.php';

$currentUser = Auth::user();
$cart = new Cart();
$cartDetails = $cart->getDetails();
$cartCount = $cartDetails['item_count'] ?? 0;

$categoryModel = new Category();
$navCategories = $categoryModel->getAll(true);
$siteName = getSetting('site_name', APP_NAME);

// Checkout runs a stripped header so the customer is not invited to wander off.
$isCheckout = !empty($checkoutMode);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' . e($siteName) : e($siteName) . ' | ' . e(getSetting('site_tagline', 'Online shopping in Bangladesh')) ?></title>
    <?php if (!empty($metaDescription)): ?>
        <meta name="description" content="<?= e($metaDescription) ?>">
    <?php endif; ?>
    <?= csrfMeta() ?>
    <script>
        window.BASE_URL = <?= json_encode(BASE_URL) ?>;
        window.CURRENCY_SYMBOL = <?= json_encode(CURRENCY_SYMBOL) ?>;
    </script>
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/images/favicon-32.png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/images/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= @filemtime(ROOT_PATH . '/assets/css/style.css') ?: '1' ?>">
</head>
<body>

<a class="skip-link" href="#main">Skip to content</a>

<?php if ($isCheckout): ?>

    <header class="checkout-header">
        <div class="container">
            <a href="<?= BASE_URL ?>/index.php" aria-label="NovaTrend home">
                <?= brandLogo('NovaTrend', 'color') ?>
            </a>
            <span class="checkout-secure">
                <?= icon('lock') ?> Secure 256-Bit SSL Checkout
            </span>
        </div>
    </header>

<?php else: ?>

    <!-- Top Announcement Bar -->
    <div class="announce" style="background:var(--primary);color:#fff;border-bottom:1px solid #222;">
        <div class="container" style="display:flex;justify-content:space-between;align-items:center;font-size:12px;font-weight:600;padding-block:6px;">
            <div class="announce-msg" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <span>Free delivery on orders over ৳2,000</span>
                <span style="opacity:0.4;">|</span>
                <span>Official brand warranty on all products</span>
                <span style="opacity:0.4;">|</span>
                <span style="color:var(--accent);">Same-day dispatch inside Dhaka</span>
            </div>
            <div class="announce-links" style="display:flex;gap:16px;">
                <a href="<?= BASE_URL ?>/orders.php" style="color:#d1d5db;hover:color:#fff;">Track order</a>
                <a href="<?= BASE_URL ?>/contact.php" style="color:#d1d5db;hover:color:#fff;">Helpline: +880 1700-000000</a>
                <?php if (Auth::isAdminOrStaff()): ?>
                    <a href="<?= ADMIN_URL ?>/index.php" style="color:var(--accent);font-weight:700;">Admin Back Office</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sticky Premium Navigation Bar -->
    <header class="site-header glass-nav" style="position:sticky;top:0;z-index:900;background:rgba(255,255,255,0.92);backdrop-filter:blur(16px);border-bottom:1px solid var(--border);">
        <div class="container">
            <div class="header-bar" style="height:70px;display:flex;align-items:center;justify-content:space-between;">
                <!-- Left: Brand Logo -->
                <a href="<?= BASE_URL ?>/index.php" aria-label="NovaTrend home" style="display:flex;align-items:center;">
                    <?= brandLogo('NovaTrend', 'color') ?>
                </a>

                <!-- Center Navigation -->
                <nav class="nav-desktop" style="display:flex;align-items:center;gap:24px;">
                    <a href="<?= BASE_URL ?>/index.php" class="nav-link<?= navActive('index.php') ?>" style="font-weight:600;font-size:14px;letter-spacing:-0.01em;">Home</a>
                    <a href="<?= BASE_URL ?>/products.php" class="nav-link<?= (basename($_SERVER['SCRIPT_NAME']) === 'products.php' && empty($_GET['sort']) && empty($_GET['sale'])) ? ' is-active' : '' ?>" style="font-weight:600;font-size:14px;letter-spacing:-0.01em;">Shop</a>
                    <a href="<?= BASE_URL ?>/products.php?sort=newest" class="nav-link<?= (isset($_GET['sort']) && $_GET['sort'] === 'newest') ? ' is-active' : '' ?>" style="font-weight:600;font-size:14px;letter-spacing:-0.01em;">New Arrivals</a>
                    <a href="<?= BASE_URL ?>/products.php?sort=popular" class="nav-link<?= (isset($_GET['sort']) && $_GET['sort'] === 'popular') ? ' is-active' : '' ?>" style="font-weight:600;font-size:14px;letter-spacing:-0.01em;">Best Sellers</a>
                    <a href="<?= BASE_URL ?>/categories.php" class="nav-link<?= navActive('categories.php') ?>" style="font-weight:600;font-size:14px;letter-spacing:-0.01em;">Categories</a>
                    <a href="<?= BASE_URL ?>/about.php" class="nav-link<?= navActive('about.php') ?>" style="font-weight:600;font-size:14px;letter-spacing:-0.01em;">About</a>
                    <a href="<?= BASE_URL ?>/index.php#faq" class="nav-link" style="font-weight:600;font-size:14px;letter-spacing:-0.01em;">FAQ</a>
                    <a href="<?= BASE_URL ?>/contact.php" class="nav-link<?= navActive('contact.php') ?>" style="font-weight:600;font-size:14px;letter-spacing:-0.01em;">Contact</a>
                </nav>

                <!-- Right Action Icons -->
                <div class="header-actions" style="display:flex;align-items:center;gap:16px;">
                    <!-- Search Icon (Triggers Search Modal) -->
                    <button type="button" class="header-btn" data-open-search-modal aria-label="Search products" style="cursor:pointer;position:relative;">
                        <?= icon('search') ?>
                        <span style="font-size:11px;font-weight:700;background:var(--bg-subtle);border:1px solid var(--border);border-radius:4px;padding:2px 6px;margin-left:4px;color:var(--text-muted);">⌘K</span>
                    </button>

                    <!-- Wishlist Icon with count badge -->
                    <a href="<?= BASE_URL ?>/wishlist.php" class="header-btn" aria-label="Wishlist">
                        <?= icon('heart') ?>
                        <span class="header-btn-label" style="display:none;">Wishlist</span>
                    </a>

                    <!-- Account Menu -->
                    <?php if (Auth::check()): ?>
                        <div class="account" style="position:relative;">
                            <button type="button" class="header-btn" id="account-trigger" aria-expanded="false" aria-haspopup="true" aria-controls="account-menu">
                                <?= icon('user') ?>
                                <span class="header-btn-label"><?= e(explode(' ', Auth::name())[0]) ?></span>
                            </button>
                            <div class="account-menu" id="account-menu">
                                <div class="account-head">
                                    <div class="account-name"><?= e(Auth::name()) ?></div>
                                    <div class="account-mail"><?= e($currentUser['email'] ?? '') ?></div>
                                </div>
                                <a href="<?= BASE_URL ?>/profile.php" class="account-link"><?= icon('user') ?> Profile</a>
                                <a href="<?= BASE_URL ?>/orders.php" class="account-link"><?= icon('package') ?> Orders</a>
                                <a href="<?= BASE_URL ?>/addresses.php" class="account-link"><?= icon('pin') ?> Addresses</a>
                                <a href="<?= BASE_URL ?>/wishlist.php" class="account-link"><?= icon('heart') ?> Wishlist</a>
                                <a href="<?= BASE_URL ?>/logout.php" class="account-link is-danger"><?= icon('logout') ?> Sign out</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/login.php" class="header-btn" aria-label="Account">
                            <?= icon('user') ?>
                        </a>
                    <?php endif; ?>

                    <!-- Shopping Cart Icon with Badge & Slide-out Cart Drawer -->
                    <button type="button" class="header-btn" data-open-cart-drawer aria-label="Open Shopping Cart" style="cursor:pointer;position:relative;">
                        <?= icon('cart') ?>
                        <span class="header-count" data-cart-count <?= $cartCount > 0 ? '' : 'hidden' ?> style="background:var(--accent);color:#fff;"><?= $cartCount ?></span>
                    </button>

                    <!-- Mobile Menu Toggle -->
                    <button type="button" class="nav-toggle" id="nav-toggle" aria-expanded="false" aria-controls="primary-nav" aria-label="Menu" style="display:none;">
                        <?= icon('menu') ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Drawer -->
        <nav class="nav" id="primary-nav" aria-label="Primary" style="display:none;background:var(--bg-surface);border-top:1px solid var(--border);">
            <div class="container" style="padding-block:var(--space-4);">
                <ul class="nav-list" style="list-style:none;display:flex;flex-direction:column;gap:8px;">
                    <li><a href="<?= BASE_URL ?>/index.php" class="nav-link">Home</a></li>
                    <li><a href="<?= BASE_URL ?>/products.php" class="nav-link">Shop All</a></li>
                    <li><a href="<?= BASE_URL ?>/products.php?sort=newest" class="nav-link">New Arrivals</a></li>
                    <li><a href="<?= BASE_URL ?>/products.php?sort=popular" class="nav-link">Best Sellers</a></li>
                    <li><a href="<?= BASE_URL ?>/categories.php" class="nav-link">Categories</a></li>
                    <li><a href="<?= BASE_URL ?>/about.php" class="nav-link">About NovaTrend</a></li>
                    <li><a href="<?= BASE_URL ?>/contact.php" class="nav-link">Contact &amp; Support</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- Slide-out Cart Drawer -->
    <div class="cart-drawer-overlay" id="cart-drawer-overlay"></div>
    <div class="cart-drawer" id="cart-drawer" aria-label="Shopping Cart Drawer">
        <div class="cart-drawer-head">
            <div class="cart-drawer-title">
                <?= icon('cart') ?>
                <span>Cart (<span data-cart-count><?= $cartCount ?></span>)</span>
            </div>
            <button type="button" class="cart-drawer-close" id="cart-drawer-close" aria-label="Close cart drawer">
                <?= icon('close') ?>
            </button>
        </div>
        <div class="cart-drawer-shipping-meter">
            <div id="drawer-shipping-msg">Add ৳2,000 or more for free delivery</div>
            <div class="shipping-progress-bar">
                <div class="shipping-progress-fill" id="drawer-shipping-fill" style="width:0%;"></div>
            </div>
        </div>
        <div class="cart-drawer-body">
            <div class="cart-drawer-items" id="cart-drawer-items-list">
                <!-- Populated dynamically via JS -->
            </div>
        </div>
        <div class="cart-drawer-footer">
            <div class="drawer-summary-row">
                <span style="color:var(--text-muted);">Subtotal:</span>
                <span style="font-weight:700;" id="drawer-subtotal"><?= formatPrice($cartDetails['subtotal'] ?? 0) ?></span>
            </div>
            <div class="drawer-total-row" style="display:flex;justify-content:space-between;align-items:center;">
                <span>Total:</span>
                <span style="color:var(--accent);font-size:22px;font-weight:800;" id="drawer-total"><?= formatPrice($cartDetails['total_amount'] ?? 0) ?></span>
            </div>
            <div style="display:flex;flex-direction:column;gap:10px;">
                <a href="<?= BASE_URL ?>/checkout.php" class="btn btn-primary btn-lg" style="width:100%;justify-content:center;background:var(--accent);border-color:var(--accent);">
                    Proceed to Checkout
                </a>
                <a href="<?= BASE_URL ?>/cart.php" class="btn btn-secondary" style="width:100%;justify-content:center;">
                    View Cart
                </a>
            </div>
        </div>
    </div>

    <!-- Quick View Modal -->
    <div class="modal-overlay" id="quickview-modal">
        <div class="quickview-modal-box" role="dialog" aria-modal="true" aria-label="Product Quick View">
            <button type="button" class="modal-close-btn" id="quickview-close" aria-label="Close modal">
                <?= icon('close') ?>
            </button>
            <div id="quickview-content">
                <!-- Populated dynamically by JS -->
            </div>
        </div>
    </div>

    <!-- ⌘K Search Modal -->
    <div class="modal-overlay" id="search-modal">
        <div class="search-modal-box" role="dialog" aria-modal="true" aria-label="Search Products">
            <div class="search-modal-input-wrap">
                <?= icon('search', 'icon-lg') ?>
                <input type="text" id="search-modal-input" class="search-modal-input" placeholder="Search products, brands, categories..." autocomplete="off">
                <button type="button" class="modal-close-btn" id="search-modal-close" aria-label="Close search" style="position:static;">
                    <?= icon('close') ?>
                </button>
            </div>
            <div id="search-modal-results" class="search-modal-results">
                <p style="padding:var(--space-6);color:var(--text-muted);font-size:var(--text-sm);text-align:center;">
                    Type at least 2 characters to search products...
                </p>
            </div>
        </div>
    </div>

<?php endif; ?>

<main id="main">

<?php if (hasFlash('success') || hasFlash('error') || hasFlash('warning')): ?>
    <div class="container" style="padding-top:var(--space-5)">
        <?php if ($msg = getFlash('success')): ?>
            <div class="alert alert-success" role="status"><?= icon('check') ?><span><?= e($msg) ?></span></div>
        <?php endif; ?>
        <?php if ($msg = getFlash('error')): ?>
            <div class="alert alert-error" role="alert"><?= icon('alert') ?><span><?= e($msg) ?></span></div>
        <?php endif; ?>
        <?php if ($msg = getFlash('warning')): ?>
            <div class="alert alert-warning" role="alert"><?= icon('alert') ?><span><?= e($msg) ?></span></div>
        <?php endif; ?>
    </div>
<?php endif; ?>

