<?php
$pageTitle = "NovaTrend | Premium Tech, Lifestyle & Everyday Essentials";
$metaDescription = "Shop genuine electronics, lifestyle accessories, and home essentials with official warranty, fast delivery across Bangladesh, and secure checkout.";
require_once __DIR__ . '/includes/header.php';

$bannerModel = new Banner();
$heroBanners  = $bannerModel->getActiveBanners('hero');
$promoBanners = $bannerModel->getActiveBanners('promotional');

$offerModel = new Offer();
$flashSale  = $offerModel->getActiveFlashSale();

$productModel = new Product();

/**
 * Product deduplication across sections.
 */
$shown = [];
$claim = function (array $products, int $limit) use (&$shown): array {
    $out = [];
    foreach ($products as $p) {
        if (isset($shown[$p['id']])) continue;
        $shown[$p['id']] = true;
        $out[] = $p;
        if (count($out) >= $limit) break;
    }
    return $out;
};

// 1. Flash Sale products
$flashProducts = [];
if ($flashSale && !empty($flashSale['products'])) {
    $flashProducts = $claim($flashSale['products'], 4);
}

// 2. New Arrivals, Best Sellers, Trending
$newArrivals = $claim($productModel->getAll(['is_active' => 1, 'sort' => 'newest'], 1, 8)['products'], 8);
$bestSellers = $claim($productModel->getAll(['is_active' => 1, 'sort' => 'popular'], 1, 4)['products'], 4);
$trending    = $claim($productModel->getAll(['is_active' => 1, 'featured' => 1], 1, 8)['products'], 8);

// Fallback if not enough trending items
if (count($trending) < 4) {
    $trending = $productModel->getAll(['is_active' => 1], 1, 6)['products'];
}

$activeHero = $heroBanners[0] ?? null;
$freeShipFrom = formatPrice(getSetting('free_shipping_threshold', '2000'));
?>

<div class="container">

    <!-- =========================================================================
         SECTION 3: CINEMATIC HERO SECTION
         ========================================================================= -->
    <section class="hero-cinematic" aria-labelledby="hero-title">
        <div class="hero-ambient-glow"></div>
        <div style="position:relative;z-index:2;">
            <div class="hero-badge">
                <?= icon('shield', 'icon-sm') ?>
                <span>Official Store &amp; Warranty</span>
            </div>
            <h1 class="hero-h1" id="hero-title">
                Original Products. <br><span style="color:var(--accent);">Delivered Fast.</span>
            </h1>
            <p class="hero-sub">
                Shop genuine electronics, accessories, and home gear with verified brand warranties and fast nationwide delivery.
            </p>
            <div class="hero-ctas">
                <a href="<?= BASE_URL ?>/products.php" class="btn btn-primary btn-lg" style="background:var(--primary);border-color:var(--primary);box-shadow:var(--shadow-md);">
                    Shop Products
                </a>
                <a href="<?= BASE_URL ?>/categories.php" class="btn btn-secondary btn-lg">
                    Browse Categories
                </a>
            </div>

            <!-- Micro Trust Metrics in Hero -->
            <div style="display:flex;align-items:center;gap:24px;margin-top:var(--space-8);padding-top:var(--space-6);border-top:1px solid rgba(0,0,0,0.06);">
                <div>
                    <div style="font-size:var(--text-xl);font-weight:800;color:var(--primary);line-height:1;">100%</div>
                    <div style="font-size:12px;color:var(--text-muted);font-weight:500;">Genuine Products</div>
                </div>
                <div style="width:1px;height:32px;background:var(--border);"></div>
                <div>
                    <div style="font-size:var(--text-xl);font-weight:800;color:var(--primary);line-height:1;">7 Days</div>
                    <div style="font-size:12px;color:var(--text-muted);font-weight:500;">Easy Replacement</div>
                </div>
                <div style="width:1px;height:32px;background:var(--border);"></div>
                <div>
                    <div style="font-size:var(--text-xl);font-weight:800;color:var(--primary);line-height:1;">24-48h</div>
                    <div style="font-size:12px;color:var(--text-muted);font-weight:500;">Dhaka Delivery</div>
                </div>
            </div>
        </div>

        <div class="hero-visual-box">
            <?php
                $heroImg = !empty($activeHero['image']) ? getImageUrl($activeHero['image'], 'banners') : BASE_URL . '/uploads/banners/hero-electronics.jpg';
            ?>
            <img src="<?= $heroImg ?>" alt="NovaTrend Hero Showcase" class="hero-main-img">

            <!-- Boxy Featured Product Badge -->
            <div class="hero-floating-card">
                <div class="hero-card-icon">
                    <?= icon('zap', 'icon-sm') ?>
                </div>
                <div>
                    <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--accent);">Featured Product</div>
                    <div style="font-size:13.5px;font-weight:700;color:var(--text-main);line-height:1.2;margin:2px 0;">Sony WH-1000XM5</div>
                    <div style="font-size:11.5px;color:var(--text-muted);">Official 1-Year Warranty</div>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         SECTION 4: TRUST BADGES SECTION (4 Feature Cards)
         ========================================================================= -->
    <section class="trust-grid-4" aria-label="Why Shop With Us">
        <div class="trust-card-modern">
            <div class="trust-icon-box"><?= icon('truck') ?></div>
            <div>
                <h3 style="font-size:var(--text-base);font-weight:700;margin-bottom:2px;">Free Delivery</h3>
                <p style="font-size:var(--text-xs);color:var(--text-muted);">On orders over ৳2,000</p>
            </div>
        </div>
        <div class="trust-card-modern">
            <div class="trust-icon-box"><?= icon('shield') ?></div>
            <div>
                <h3 style="font-size:var(--text-base);font-weight:700;margin-bottom:2px;">Secure Payment</h3>
                <p style="font-size:var(--text-xs);color:var(--text-muted);">bKash, cards, and COD</p>
            </div>
        </div>
        <div class="trust-card-modern">
            <div class="trust-icon-box"><?= icon('refresh') ?></div>
            <div>
                <h3 style="font-size:var(--text-base);font-weight:700;margin-bottom:2px;">7-Day Replacement</h3>
                <p style="font-size:var(--text-xs);color:var(--text-muted);">For defective or damaged items</p>
            </div>
        </div>
        <div class="trust-card-modern">
            <div class="trust-icon-box"><?= icon('headset') ?></div>
            <div>
                <h3 style="font-size:var(--text-base);font-weight:700;margin-bottom:2px;">Customer Support</h3>
                <p style="font-size:var(--text-xs);color:var(--text-muted);">Saturday to Thursday, 10am-7pm</p>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         SECTION 5: FEATURED CATEGORIES (Star Tech Clean Grid)
         ========================================================================= -->
    <?php if (!empty($navCategories)): ?>
        <section class="section-featured-cat" aria-labelledby="cat-section-title">
            <div class="section-heading-clean">
                <h2 class="section-title-clean" id="cat-section-title">Featured Categories</h2>
                <p class="section-subtitle-clean">Browse our top product categories</p>
            </div>

            <div class="cat-grid-startech">
                <?php 
                $iconMap = [
                    'electronics'           => 'cat-tech',
                    'fashion-apparel'       => 'cat-fashion',
                    'home-living'           => 'cat-home',
                    'beauty-personal-care'  => 'cat-beauty',
                    'sports-fitness'        => 'cat-sports',
                    'books-stationery'      => 'cat-books',
                ];
                foreach (array_slice($navCategories, 0, 6) as $cat): 
                    $catIcon = $iconMap[$cat['slug']] ?? 'grid';
                ?>
                    <a href="<?= BASE_URL ?>/products.php?category=<?= urlencode($cat['slug']) ?>" class="cat-card-startech">
                        <div class="cat-icon-circle">
                            <?= icon($catIcon) ?>
                        </div>
                        <span class="cat-name-startech"><?= e($cat['name']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- =========================================================================
         SECTION 6: NEW ARRIVALS SECTION (Product Grid)
         ========================================================================= -->
    <?php if (!empty($newArrivals)): ?>
        <section style="margin-bottom:var(--space-16);" aria-labelledby="new-arrivals-title">
            <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:var(--space-8);">
                <div>
                    <span style="font-size:var(--text-xs);font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--accent);">Just In</span>
                    <h2 style="font-size:var(--text-3xl);font-weight:800;letter-spacing:-0.02em;margin-top:4px;" id="new-arrivals-title">New Arrivals</h2>
                </div>
                <a href="<?= BASE_URL ?>/products.php?sort=newest" class="btn btn-secondary btn-sm">
                    View All New Products
                </a>
            </div>

            <div class="product-grid product-grid-4">
                <?php foreach ($newArrivals as $prod): ?>
                    <?php include __DIR__ . '/includes/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- =========================================================================
         SECTION 7: BEST SELLERS SECTION (Premium Showcase)
         ========================================================================= -->
    <?php if (!empty($bestSellers)): ?>
        <section style="margin-bottom:var(--space-16);" aria-labelledby="best-sellers-title">
            <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:var(--space-8);">
                <div>
                    <span style="font-size:var(--text-xs);font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--accent);">Customer Favorites</span>
                    <h2 style="font-size:var(--text-3xl);font-weight:800;letter-spacing:-0.02em;margin-top:4px;" id="best-sellers-title">Best Sellers</h2>
                </div>
                <a href="<?= BASE_URL ?>/products.php?sort=popular" class="btn btn-secondary btn-sm">
                    View All Best Sellers
                </a>
            </div>

            <div class="product-grid product-grid-4">
                <?php foreach ($bestSellers as $prod): ?>
                    <?php include __DIR__ . '/includes/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- =========================================================================
         SECTION 8: TRENDING PRODUCTS CAROUSEL
         ========================================================================= -->
    <?php if (!empty($trending)): ?>
        <section style="margin-bottom:var(--space-16);" aria-labelledby="trending-title">
            <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:var(--space-6);">
                <div>
                    <span style="font-size:var(--text-xs);font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--accent);">Popular This Week</span>
                    <h2 style="font-size:var(--text-3xl);font-weight:800;letter-spacing:-0.02em;margin-top:4px;" id="trending-title">Trending Products</h2>
                </div>
            </div>

            <div class="carousel-container">
                <button type="button" class="carousel-nav-btn carousel-prev" aria-label="Previous trending products">
                    <?= icon('chevron-l') ?>
                </button>

                <div class="carousel-track">
                    <?php foreach ($trending as $prod): ?>
                        <div class="carousel-card">
                            <?php include __DIR__ . '/includes/product-card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" class="carousel-nav-btn carousel-next" aria-label="Next trending products">
                    <?= icon('chevron-r') ?>
                </button>
            </div>
        </section>
    <?php endif; ?>

    <!-- =========================================================================
         SECTION 9: FLASH SALE SECTION (Countdown Timer)
         ========================================================================= -->
    <section class="flash-sale-box" aria-label="Flash Sale Promotion">
        <div>
            <div class="flash-badge">
                <?= icon('zap', 'icon-xs') ?>
                <span>Special Deals</span>
            </div>
            <h2 style="font-size:var(--text-3xl);font-weight:800;letter-spacing:-0.02em;margin-bottom:var(--space-3);">
                Flash Sale: <span style="color:var(--accent);">Up to 40% Off</span>
            </h2>
            <p style="color:#D1D5DB;font-size:var(--text-base);max-width:460px;margin-bottom:var(--space-6);">
                Save on selected electronics, audio, and accessories while promotional stocks last.
            </p>

            <!-- Countdown Timer Blocks -->
            <div class="countdown-row">
                <div class="countdown-box">
                    <div class="countdown-num" id="cd-hours">08</div>
                    <div class="countdown-lbl">Hours</div>
                </div>
                <div class="countdown-colon">:</div>
                <div class="countdown-box">
                    <div class="countdown-num" id="cd-mins">45</div>
                    <div class="countdown-lbl">Minutes</div>
                </div>
                <div class="countdown-colon">:</div>
                <div class="countdown-box">
                    <div class="countdown-num" id="cd-secs">30</div>
                    <div class="countdown-lbl">Seconds</div>
                </div>
            </div>

            <a href="<?= BASE_URL ?>/products.php?sale=1" class="btn btn-primary btn-lg" style="background:var(--accent);border-color:var(--accent);margin-top:var(--space-4);">
                Shop All Deals
            </a>
        </div>

        <div style="display:flex;justify-content:center;align-items:center;">
            <img src="<?= BASE_URL ?>/uploads/banners/promo-audio.jpg" alt="Flash Sale Deals" style="max-height:360px;border-radius:var(--radius-lg);box-shadow:var(--shadow-xl);object-fit:cover;">
        </div>
    </section>


    <!-- =========================================================================
         SECTION 11: FEATURED COLLECTION SECTION
         ========================================================================= -->
    <section id="featured-collections" style="margin-bottom:var(--space-16);" aria-labelledby="collections-title">
        <div style="text-align:center;margin-bottom:var(--space-8);">
            <span style="font-size:var(--text-xs);font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--accent);">Popular Departments</span>
            <h2 style="font-size:var(--text-3xl);font-weight:800;letter-spacing:-0.02em;margin-top:4px;" id="collections-title">Featured Collections</h2>
        </div>

        <div class="collection-grid">
            <div class="collection-card">
                <img src="<?= BASE_URL ?>/uploads/banners/hero-electronics.jpg" alt="Tech Collection">
                <div class="collection-content">
                    <h3 style="font-size:var(--text-xl);font-weight:700;margin-bottom:var(--space-2);">Tech Essentials</h3>
                    <p style="font-size:var(--text-sm);color:#d1d5db;margin-bottom:var(--space-4);">Headphones, speakers, keyboards, and desk accessories.</p>
                    <a href="<?= BASE_URL ?>/products.php?category=electronics" class="btn btn-primary btn-sm" style="background:var(--accent);border-color:var(--accent);">Browse Electronics</a>
                </div>
            </div>
            <div class="collection-card">
                <img src="<?= BASE_URL ?>/uploads/banners/promo-audio.jpg" alt="Summer Luxury">
                <div class="collection-content">
                    <h3 style="font-size:var(--text-xl);font-weight:700;margin-bottom:var(--space-2);">Everyday Fashion</h3>
                    <p style="font-size:var(--text-sm);color:#d1d5db;margin-bottom:var(--space-4);">Casual wear, bags, watches, and seasonal apparel.</p>
                    <a href="<?= BASE_URL ?>/products.php?category=fashion-apparel" class="btn btn-primary btn-sm" style="background:var(--accent);border-color:var(--accent);">Browse Fashion</a>
                </div>
            </div>
            <div class="collection-card">
                <img src="<?= BASE_URL ?>/uploads/banners/hero-electronics.jpg" alt="Home Minimalist">
                <div class="collection-content">
                    <h3 style="font-size:var(--text-xl);font-weight:700;margin-bottom:var(--space-2);">Home &amp; Living</h3>
                    <p style="font-size:var(--text-sm);color:#d1d5db;margin-bottom:var(--space-4);">Desk lighting, organizers, and home comfort essentials.</p>
                    <a href="<?= BASE_URL ?>/products.php?category=home-living" class="btn btn-primary btn-sm" style="background:var(--accent);border-color:var(--accent);">Browse Home</a>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         SECTION 12: VIDEO SHOWCASE SECTION
         ========================================================================= -->
    <section class="video-showcase" aria-label="NovaTrend Brand Story">
        <img src="<?= BASE_URL ?>/uploads/banners/hero-electronics.jpg" alt="Brand Video Showcase">
        <div class="video-content">
            <button type="button" class="play-btn-large" aria-label="Watch brand video" onclick="showToast('Product video coming soon.', 'info')">
                <?= icon('play') ?>
            </button>
            <h2 style="font-size:var(--text-3xl);font-weight:800;letter-spacing:-0.02em;margin-bottom:var(--space-3);">
                Quality You Can Rely On
            </h2>
            <p style="font-size:var(--text-base);color:#e5e7eb;max-width:540px;margin:0 auto;">
                We partner directly with established brands to ensure authentic products, proper warranty handling, and dependable service.
            </p>
        </div>
    </section>

    <!-- =========================================================================
         SECTION 13: CUSTOMER TESTIMONIALS
         ========================================================================= -->
    <section style="margin-bottom:var(--space-16);" aria-labelledby="testimonials-title">
        <div style="text-align:center;margin-bottom:var(--space-8);">
            <span style="font-size:var(--text-xs);font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--accent);">Verified Reviews</span>
            <h2 style="font-size:var(--text-3xl);font-weight:800;letter-spacing:-0.02em;margin-top:4px;" id="testimonials-title">What Customers Say About Us</h2>
        </div>

        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div>
                    <div class="testimonial-rating" aria-label="5 out of 5 stars"><?= str_repeat(icon('star', 'icon-xs'), 5) ?></div>
                    <p class="testimonial-quote">
                        &ldquo;Ordered the Sony XM5 in the morning and received it the next afternoon. Brand new, official warranty card included, and properly sealed packaging.&rdquo;
                    </p>
                </div>
                <div class="testimonial-author">
                    <img src="<?= BASE_URL ?>/uploads/avatars/marcus.jpg" alt="Tanvir Ahmed" class="author-avatar" onerror="this.src='<?= BASE_URL ?>/assets/images/placeholder.svg'">
                    <div>
                        <div class="author-name">Tanvir Ahmed</div>
                        <span class="author-verified"><?= icon('check', 'icon-xs') ?> Verified Buyer &middot; Dhaka</span>
                    </div>
                </div>
            </div>

            <div class="testimonial-card">
                <div>
                    <div class="testimonial-rating" aria-label="5 out of 5 stars"><?= str_repeat(icon('star', 'icon-xs'), 5) ?></div>
                    <p class="testimonial-quote">
                        &ldquo;Customer service helped me choose the right keyboard switches on chat before ordering. Delivery to Chattogram took 2 days. Very satisfied.&rdquo;
                    </p>
                </div>
                <div class="testimonial-author">
                    <img src="<?= BASE_URL ?>/uploads/avatars/elena.jpg" alt="Nusrat Jahan" class="author-avatar" onerror="this.src='<?= BASE_URL ?>/assets/images/placeholder.svg'">
                    <div>
                        <div class="author-name">Nusrat Jahan</div>
                        <span class="author-verified"><?= icon('check', 'icon-xs') ?> Verified Buyer &middot; Chattogram</span>
                    </div>
                </div>
            </div>

            <div class="testimonial-card">
                <div>
                    <div class="testimonial-rating" aria-label="5 out of 5 stars"><?= str_repeat(icon('star', 'icon-xs'), 5) ?></div>
                    <p class="testimonial-quote">
                        &ldquo;Cash on delivery option gave me peace of mind. Tested the product in front of the delivery agent before paying. Will order again.&rdquo;
                    </p>
                </div>
                <div class="testimonial-author">
                    <img src="<?= BASE_URL ?>/uploads/avatars/marcus.jpg" alt="Sazzad Hossain" class="author-avatar" onerror="this.src='<?= BASE_URL ?>/assets/images/placeholder.svg'">
                    <div>
                        <div class="author-name">Sazzad Hossain</div>
                        <span class="author-verified"><?= icon('check', 'icon-xs') ?> Verified Buyer &middot; Sylhet</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         SECTION 14: SOCIAL PROOF SECTION
         ========================================================================= -->
    <section class="stats-proof-row" aria-label="Store Performance Statistics">
        <div class="stat-box">
            <div class="stat-value">15,000+</div>
            <div class="stat-label">Orders Delivered Safely</div>
        </div>
        <div class="stat-box">
            <div class="stat-value" style="display:inline-flex;align-items:center;justify-content:center;gap:6px;">4.8 <?= icon('star', 'icon-lg star-gold') ?></div>
            <div class="stat-label">Average Customer Rating</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">100%</div>
            <div class="stat-label">Genuine Brand Warranty</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">64</div>
            <div class="stat-label">Districts Covered Across Bangladesh</div>
        </div>
    </section>

    <!-- =========================================================================
         SECTION 15: INSTAGRAM SHOP SECTION
         ========================================================================= -->
    <section style="margin-bottom:var(--space-16);" aria-labelledby="insta-title">
        <div style="text-align:center;margin-bottom:var(--space-8);">
            <span style="font-size:var(--text-xs);font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--accent);">Connect With Us</span>
            <h2 style="font-size:var(--text-3xl);font-weight:800;letter-spacing:-0.02em;margin-top:4px;" id="insta-title">Follow Us on Instagram</h2>
            <p style="color:var(--text-muted);font-size:var(--text-sm);margin-top:4px;">Follow @novatrend.bd for product updates, unboxings, and new arrivals.</p>
        </div>

        <div class="insta-grid">
            <div class="insta-card">
                <img src="<?= BASE_URL ?>/uploads/banners/hero-electronics.jpg" alt="Instagram update 1">
                <div class="insta-hover-overlay">
                    <?= icon('instagram') ?>
                    <span style="font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:4px;"><?= icon('heart', 'icon-xs', 'Likes') ?> 1,420</span>
                </div>
            </div>
            <div class="insta-card">
                <img src="<?= BASE_URL ?>/uploads/banners/promo-audio.jpg" alt="Instagram update 2">
                <div class="insta-hover-overlay">
                    <?= icon('instagram') ?>
                    <span style="font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:4px;"><?= icon('heart', 'icon-xs', 'Likes') ?> 2,840</span>
                </div>
            </div>
            <div class="insta-card">
                <img src="<?= BASE_URL ?>/uploads/banners/promo-audio.jpg" alt="Instagram update 3">
                <div class="insta-hover-overlay">
                    <?= icon('instagram') ?>
                    <span style="font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:4px;"><?= icon('heart', 'icon-xs', 'Likes') ?> 980</span>
                </div>
            </div>
            <div class="insta-card">
                <img src="<?= BASE_URL ?>/uploads/banners/hero-electronics.jpg" alt="Instagram update 4">
                <div class="insta-hover-overlay">
                    <?= icon('instagram') ?>
                    <span style="font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:4px;"><?= icon('heart', 'icon-xs', 'Likes') ?> 3,110</span>
                </div>
            </div>
            <div class="insta-card">
                <img src="<?= BASE_URL ?>/uploads/banners/promo-audio.jpg" alt="Instagram update 5">
                <div class="insta-hover-overlay">
                    <?= icon('instagram') ?>
                    <span style="font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:4px;"><?= icon('heart', 'icon-xs', 'Likes') ?> 1,750</span>
                </div>
            </div>
            <div class="insta-card">
                <img src="<?= BASE_URL ?>/uploads/banners/hero-electronics.jpg" alt="Instagram update 6">
                <div class="insta-hover-overlay">
                    <?= icon('instagram') ?>
                    <span style="font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:4px;"><?= icon('heart', 'icon-xs', 'Likes') ?> 4,290</span>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         SECTION 16: LUXURY NEWSLETTER SECTION
         ========================================================================= -->
    <section class="newsletter-luxury" aria-labelledby="newsletter-title">
        <span style="display:inline-block;padding:4px 12px;background:rgba(255,107,53,0.2);border:1px solid var(--accent);color:var(--accent);border-radius:var(--radius-full);font-size:11px;font-weight:700;text-transform:uppercase;margin-bottom:var(--space-4);">
            Email Newsletter
        </span>
        <h2 id="newsletter-title">Stay Updated on Offers &amp; New Stock</h2>
        <p>Sign up to receive price drop alerts, promotional discount codes, and weekly product highlights.</p>

        <form class="newsletter-form-modern" onsubmit="event.preventDefault(); showToast('Thank you for subscribing. Check your inbox for updates.', 'success'); this.reset();">
            <input type="email" required placeholder="Enter your email address...">
            <button type="submit" class="btn btn-primary" style="background:var(--accent);border-color:var(--accent);border-radius:var(--radius-full);padding-inline:var(--space-6);">
                Subscribe
            </button>
        </form>

        <div class="newsletter-perks">
            <span><?= icon('check', 'icon-xs') ?> ৳200 voucher on first order</span>
            <span><?= icon('check', 'icon-xs') ?> New product announcements</span>
            <span><?= icon('check', 'icon-xs') ?> No spam, unsubscribe anytime</span>
        </div>
    </section>

    <!-- =========================================================================
         SECTION 17: FAQ SECTION (Accordion Style)
         ========================================================================= -->
    <section id="faq" class="faq-wrap" aria-labelledby="faq-title">
        <div style="text-align:center;margin-bottom:var(--space-8);">
            <span style="font-size:var(--text-xs);font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--accent);">Help &amp; Support</span>
            <h2 style="font-size:var(--text-3xl);font-weight:800;letter-spacing:-0.02em;margin-top:4px;" id="faq-title">Frequently Asked Questions</h2>
        </div>

        <div class="faq-item is-open">
            <button type="button" class="faq-question">
                <span>What are your delivery times and shipping charges?</span>
                <span class="faq-icon"><?= icon('chevron-d') ?></span>
            </button>
            <div class="faq-answer">
                We deliver across all 64 districts in Bangladesh. Delivery inside Dhaka takes 24 to 48 hours (৳60 fee). Delivery outside Dhaka takes 2 to 4 business days (৳120 fee). Orders over ৳2,000 qualify for free standard delivery.
            </div>
        </div>

        <div class="faq-item">
            <button type="button" class="faq-question">
                <span>What is your return and replacement policy?</span>
                <span class="faq-icon"><?= icon('chevron-d') ?></span>
            </button>
            <div class="faq-answer">
                If your product arrives damaged, defective, or incorrect, you can claim a replacement or refund within 7 days of delivery. The item must be in its original condition with all packaging, manuals, and accessories intact.
            </div>
        </div>

        <div class="faq-item">
            <button type="button" class="faq-question">
                <span>What payment methods do you accept?</span>
                <span class="faq-icon"><?= icon('chevron-d') ?></span>
            </button>
            <div class="faq-answer">
                We accept bKash, Nagad, Visa, MasterCard, and Cash on Delivery (COD) throughout Bangladesh. Online card payments are securely processed through SSLCommerz with multi-factor authentication.
            </div>
        </div>

        <div class="faq-item">
            <button type="button" class="faq-question">
                <span>How do I track my order once shipped?</span>
                <span class="faq-icon"><?= icon('chevron-d') ?></span>
            </button>
            <div class="faq-answer">
                Once your order is handed to the courier, we send an SMS and email with your tracking number. You can also check status anytime on our <a href="<?= BASE_URL ?>/orders.php" style="color:var(--accent);text-decoration:underline;">Order Tracking page</a>.
            </div>
        </div>
    </section>

    <!-- =========================================================================
         SECTION 18: MOBILE APP SECTION
         ========================================================================= -->
    <section class="app-promo-box" aria-label="NovaTrend Mobile App">
        <div>
            <span style="font-size:var(--text-xs);font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--accent);">Mobile App</span>
            <h2 style="font-size:var(--text-3xl);font-weight:800;letter-spacing:-0.02em;margin-top:4px;margin-bottom:var(--space-3);">
                Shop on the NovaTrend App
            </h2>
            <p style="color:var(--text-muted);font-size:var(--text-base);line-height:1.6;max-width:480px;">
                Get order status alerts, faster checkout, and early access to promotional discounts on your phone.
            </p>

            <div class="app-badges-row">
                <a href="#" class="app-badge-btn" onclick="event.preventDefault(); showToast('iOS app is coming soon.', 'info');">
                    <?= icon('apple', 'icon-lg') ?>
                    <div>
                        <div style="font-size:9px;opacity:0.75;text-transform:uppercase;">Download on the</div>
                        <div style="font-size:13px;font-weight:700;">App Store</div>
                    </div>
                </a>
                <a href="#" class="app-badge-btn" onclick="event.preventDefault(); showToast('Android app is coming soon.', 'info');">
                    <?= icon('google-play', 'icon-lg') ?>
                    <div>
                        <div style="font-size:9px;opacity:0.75;text-transform:uppercase;">Get it on</div>
                        <div style="font-size:13px;font-weight:700;">Google Play</div>
                    </div>
                </a>
            </div>
        </div>

        <div style="display:flex;justify-content:center;align-items:center;">
            <div class="glass-card" style="padding:var(--space-8);text-align:center;max-width:320px;">
                <div style="display:flex;justify-content:center;margin-bottom:var(--space-3);color:var(--accent);"><?= icon('smartphone', 'icon-2xl') ?></div>
                <h3 style="font-size:var(--text-lg);font-weight:700;margin-bottom:var(--space-1);">NovaTrend Mobile</h3>
                <p style="font-size:var(--text-xs);color:var(--text-muted);margin-bottom:var(--space-4);">Available soon for Android and iOS devices.</p>
                <span class="badge-hot">Coming Soon</span>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         SECTION 19: FINAL CALL TO ACTION SECTION
         ========================================================================= -->
    <section class="final-cta" aria-labelledby="final-cta-title">
        <span style="font-size:var(--text-xs);font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--accent);">Ready to Order?</span>
        <h2 id="final-cta-title" style="margin-top:4px;">Find What You Need Today</h2>
        <p>Browse thousands of genuine products with official warranty and reliable delivery across Bangladesh.</p>
        <div style="display:flex;justify-content:center;gap:var(--space-4);flex-wrap:wrap;">
            <a href="<?= BASE_URL ?>/products.php" class="btn btn-primary btn-lg" style="background:var(--accent);border-color:var(--accent);">
                Browse All Products
            </a>
            <a href="<?= BASE_URL ?>/categories.php" class="btn btn-secondary btn-lg">
                View Categories
            </a>
        </div>
    </section>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
