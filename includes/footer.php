<?php
$siteName = getSetting('site_name', APP_NAME);
$isCheckout = !empty($checkoutMode);
?>
</main>

<?php if ($isCheckout): ?>

    <footer class="site-footer" style="background:#111;color:#9ca3af;padding-block:var(--space-8);">
        <div class="container">
            <div class="footer-bottom" style="border-top:none;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
                <p>&copy; <?= date('Y') ?> NovaTrend. All rights reserved.</p>
                <div class="footer-legal" style="display:flex;gap:20px;">
                    <a href="<?= BASE_URL ?>/terms.php" style="color:#9ca3af;">Terms of Service</a>
                    <a href="<?= BASE_URL ?>/privacy.php" style="color:#9ca3af;">Privacy Policy</a>
                    <a href="<?= BASE_URL ?>/returns.php" style="color:#9ca3af;">Returns &amp; Refunds</a>
                </div>
            </div>
        </div>
    </footer>

<?php else: ?>

    <footer class="site-footer" style="background:#111111;color:#9CA3AF;padding-top:var(--space-16);padding-bottom:var(--space-8);border-top:1px solid #222;">
        <div class="container">
            <div class="grid-12" style="margin-bottom:var(--space-12);">
                <!-- Column 1: Logo, Description & Social Icons (4 cols) -->
                <div class="col-4" style="padding-right:var(--space-6);">
                    <div style="margin-bottom:var(--space-4);">
                        <a href="<?= BASE_URL ?>/index.php"><?= brandLogo('NovaTrend', 'white') ?></a>
                    </div>
                    <p style="font-size:var(--text-sm);line-height:1.6;color:#9CA3AF;margin-bottom:var(--space-6);">
                        NovaTrend is an online retailer offering genuine electronics, computer accessories, home goods, and lifestyle products with official warranties and fast delivery across Bangladesh.
                    </p>
                    <div style="display:flex;gap:12px;" aria-label="Social media channels">
                        <a href="https://facebook.com" target="_blank" rel="noopener" aria-label="Facebook" style="width:36px;height:36px;border-radius:50%;background:#222;display:flex;align-items:center;justify-content:center;color:#fff;transition:all var(--transition);" onmouseover="this.style.background='var(--accent)'" onmouseout="this.style.background='#222'"><?= icon('facebook', 'icon-sm') ?></a>
                        <a href="https://instagram.com" target="_blank" rel="noopener" aria-label="Instagram" style="width:36px;height:36px;border-radius:50%;background:#222;display:flex;align-items:center;justify-content:center;color:#fff;transition:all var(--transition);" onmouseover="this.style.background='var(--accent)'" onmouseout="this.style.background='#222'"><?= icon('instagram', 'icon-sm') ?></a>
                        <a href="https://tiktok.com" target="_blank" rel="noopener" aria-label="TikTok" style="width:36px;height:36px;border-radius:50%;background:#222;display:flex;align-items:center;justify-content:center;color:#fff;transition:all var(--transition);" onmouseover="this.style.background='var(--accent)'" onmouseout="this.style.background='#222'"><?= icon('tiktok', 'icon-sm') ?></a>
                        <a href="https://pinterest.com" target="_blank" rel="noopener" aria-label="Pinterest" style="width:36px;height:36px;border-radius:50%;background:#222;display:flex;align-items:center;justify-content:center;color:#fff;transition:all var(--transition);" onmouseover="this.style.background='var(--accent)'" onmouseout="this.style.background='#222'"><?= icon('pinterest', 'icon-sm') ?></a>
                        <a href="https://youtube.com" target="_blank" rel="noopener" aria-label="YouTube" style="width:36px;height:36px;border-radius:50%;background:#222;display:flex;align-items:center;justify-content:center;color:#fff;transition:all var(--transition);" onmouseover="this.style.background='var(--accent)'" onmouseout="this.style.background='#222'"><?= icon('youtube', 'icon-sm') ?></a>
                    </div>
                </div>

                <!-- Column 2: Shop (2 cols) -->
                <div class="col-2">
                    <h3 style="color:#ffffff;font-size:var(--text-base);font-weight:700;margin-bottom:var(--space-4);letter-spacing:-0.01em;">Shop</h3>
                    <ul style="list-style:none;display:flex;flex-direction:column;gap:10px;font-size:var(--text-sm);">
                        <li><a href="<?= BASE_URL ?>/products.php?category=electronics" style="color:#9CA3AF;hover:color:#fff;">Electronics</a></li>
                        <li><a href="<?= BASE_URL ?>/products.php?category=fashion-apparel" style="color:#9CA3AF;hover:color:#fff;">Fashion</a></li>
                        <li><a href="<?= BASE_URL ?>/products.php?category=home-living" style="color:#9CA3AF;hover:color:#fff;">Home &amp; Living</a></li>
                        <li><a href="<?= BASE_URL ?>/products.php?sort=newest" style="color:#9CA3AF;hover:color:#fff;">New Arrivals</a></li>
                        <li><a href="<?= BASE_URL ?>/products.php?sort=popular" style="color:#9CA3AF;hover:color:#fff;">Best Sellers</a></li>
                        <li><a href="<?= BASE_URL ?>/products.php?sale=1" style="color:var(--accent);font-weight:600;">Sale &amp; Offers</a></li>
                    </ul>
                </div>

                <!-- Column 3: Support (2 cols) -->
                <div class="col-2">
                    <h3 style="color:#ffffff;font-size:var(--text-base);font-weight:700;margin-bottom:var(--space-4);letter-spacing:-0.01em;">Support</h3>
                    <ul style="list-style:none;display:flex;flex-direction:column;gap:10px;font-size:var(--text-sm);">
                        <li><a href="<?= BASE_URL ?>/contact.php" style="color:#9CA3AF;hover:color:#fff;">Contact Us</a></li>
                        <li><a href="<?= BASE_URL ?>/faq.php" style="color:#9CA3AF;hover:color:#fff;">FAQs</a></li>
                        <li><a href="<?= BASE_URL ?>/shipping.php" style="color:#9CA3AF;hover:color:#fff;">Shipping Info</a></li>
                        <li><a href="<?= BASE_URL ?>/returns.php" style="color:#9CA3AF;hover:color:#fff;">Returns &amp; Refunds</a></li>
                        <li><a href="<?= BASE_URL ?>/orders.php" style="color:#9CA3AF;hover:color:#fff;">Track Order</a></li>
                    </ul>
                </div>

                <!-- Column 4: Company (2 cols) -->
                <div class="col-2">
                    <h3 style="color:#ffffff;font-size:var(--text-base);font-weight:700;margin-bottom:var(--space-4);letter-spacing:-0.01em;">Company</h3>
                    <ul style="list-style:none;display:flex;flex-direction:column;gap:10px;font-size:var(--text-sm);">
                        <li><a href="<?= BASE_URL ?>/about.php" style="color:#9CA3AF;hover:color:#fff;">About NovaTrend</a></li>
                        <li><a href="<?= BASE_URL ?>/about.php#careers" style="color:#9CA3AF;hover:color:#fff;">Careers</a></li>
                        <li><a href="<?= BASE_URL ?>/about.php#terms" style="color:#9CA3AF;hover:color:#fff;">Terms of Service</a></li>
                        <li><a href="<?= BASE_URL ?>/privacy.php" style="color:#9CA3AF;hover:color:#fff;">Privacy Policy</a></li>
                    </ul>
                </div>

                <!-- Column 5: Newsletter & Perks (2 cols) -->
                <div class="col-2">
                    <h3 style="color:#ffffff;font-size:var(--text-base);font-weight:700;margin-bottom:var(--space-4);letter-spacing:-0.01em;">Newsletter</h3>
                    <p style="font-size:var(--text-xs);line-height:1.5;color:#9CA3AF;margin-bottom:var(--space-3);">
                        Subscribe for product updates, price drops, and promotional discounts.
                    </p>
                    <form onsubmit="event.preventDefault(); showToast('Thank you for subscribing.', 'success'); this.reset();" style="display:flex;flex-direction:column;gap:8px;">
                        <input type="email" required placeholder="Enter your email" style="width:100%;padding:10px 14px;background:#1e1e1e;border:1px solid #333;border-radius:var(--radius-sm);color:#fff;font-size:var(--text-xs);outline:none;">
                        <button type="submit" class="btn btn-primary btn-sm" style="width:100%;justify-content:center;background:var(--accent);border-color:var(--accent);">Subscribe</button>
                    </form>
                </div>
            </div>

            <!-- Footer Bottom: Legal & Payment Badges -->
            <div style="border-top:1px solid #222;padding-top:var(--space-8);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;font-size:var(--text-xs);">
                <p>&copy; <?= date('Y') ?> NovaTrend. All rights reserved.</p>

                <div class="footer-legal" style="display:flex;gap:16px;">
                    <a href="<?= BASE_URL ?>/privacy.php" style="color:#9CA3AF;">Privacy Policy</a>
                    <a href="<?= BASE_URL ?>/terms.php" style="color:#9CA3AF;">Terms &amp; Conditions</a>
                    <a href="<?= BASE_URL ?>/returns.php" style="color:#9CA3AF;">Return Policy</a>
                </div>

                <!-- Accepted Payment Methods -->
                <div style="display:flex;align-items:center;gap:10px;">
                    <span style="font-size:11px;color:#737373;text-transform:uppercase;letter-spacing:0.05em;">Accepted Payments:</span>
                    <div style="display:flex;gap:6px;align-items:center;">
                        <span style="background:#1e1e1e;border:1px solid #333;border-radius:4px;padding:3px 8px;font-weight:700;color:#fff;font-size:11px;">VISA</span>
                        <span style="background:#1e1e1e;border:1px solid #333;border-radius:4px;padding:3px 8px;font-weight:700;color:#fff;font-size:11px;">MasterCard</span>
                        <span style="background:#1e1e1e;border:1px solid #333;border-radius:4px;padding:3px 8px;font-weight:700;color:#fff;font-size:11px;">bKash</span>
                        <span style="background:#1e1e1e;border:1px solid #333;border-radius:4px;padding:3px 8px;font-weight:700;color:#fff;font-size:11px;">Nagad</span>
                        <span style="background:#1e1e1e;border:1px solid #333;border-radius:4px;padding:3px 8px;font-weight:700;color:#fff;font-size:11px;">COD</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

<?php endif; ?>

<script src="<?= BASE_URL ?>/assets/js/app.js?v=<?= @filemtime(ROOT_PATH . '/assets/js/app.js') ?: '1' ?>"></script>
</body>
</html>
