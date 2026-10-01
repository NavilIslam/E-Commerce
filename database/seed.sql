-- ==============================================================================
-- FULL-STACK E-COMMERCE SEED DATA (MySQL 8+)
-- Database: ecommerce_db
-- ==============================================================================

USE ecommerce_db;

-- 1. SEED USERS
-- Passwords:
-- owner@example.com    => password: "Password123!"
-- admin@example.com    => password: "Password123!"
-- staff@example.com    => password: "Password123!"
-- customer@example.com => password: "Password123!"
-- Hash below generated with password_hash('Password123!', PASSWORD_BCRYPT)
-- $2y$10$eW4E56qXJzT9/eM6hL9Gse5Z9v68H.t6s7p9Y9.JvC4D7Y8G2N8w6

INSERT INTO users (id, first_name, last_name, email, phone, password_hash, role, avatar, is_active, email_verified) VALUES
(1, 'Super', 'Owner', 'owner@example.com', '+8801711000001', '$2y$10$/p2l5qCRzVfT1S5DxX0NOe8hBnZ.ysWgvlrTMC4F9Y45ZfTNgB4Na', 'owner', 'avatar-owner.jpg', 1, 1),
(2, 'Site', 'Admin', 'admin@example.com', '+8801711000002', '$2y$10$/p2l5qCRzVfT1S5DxX0NOe8hBnZ.ysWgvlrTMC4F9Y45ZfTNgB4Na', 'admin', 'avatar-admin.jpg', 1, 1),
(3, 'Support', 'Staff', 'staff@example.com', '+8801711000003', '$2y$10$/p2l5qCRzVfT1S5DxX0NOe8hBnZ.ysWgvlrTMC4F9Y45ZfTNgB4Na', 'staff', NULL, 1, 1),
(4, 'Rahim', 'Ahmed', 'customer@example.com', '+8801811000004', '$2y$10$/p2l5qCRzVfT1S5DxX0NOe8hBnZ.ysWgvlrTMC4F9Y45ZfTNgB4Na', 'customer', NULL, 1, 1),
(5, 'Karim', 'Uddin', 'karim@example.com', '+8801911000005', '$2y$10$/p2l5qCRzVfT1S5DxX0NOe8hBnZ.ysWgvlrTMC4F9Y45ZfTNgB4Na', 'customer', NULL, 1, 1)
ON DUPLICATE KEY UPDATE first_name=VALUES(first_name);

-- 2. SEED ADDRESSES
INSERT INTO addresses (id, user_id, label, full_name, phone, address_line1, address_line2, city, area, postal_code, country, is_default) VALUES
(1, 4, 'Home', 'Rahim Ahmed', '+8801811000004', 'House 42, Road 11, Block D', 'Banani', 'Dhaka', 'Banani', '1213', 'Bangladesh', 1),
(2, 4, 'Office', 'Rahim Ahmed', '+8801811000004', 'Level 7, Concord Tower, Gulshan 1', 'Gulshan Avenue', 'Dhaka', 'Gulshan', '1212', 'Bangladesh', 0),
(3, 5, 'Home', 'Karim Uddin', '+8801911000005', 'Plot 15, Sector 7', 'Uttara', 'Dhaka', 'Uttara', '1230', 'Bangladesh', 1)
ON DUPLICATE KEY UPDATE full_name=VALUES(full_name);

-- 3. SEED CATEGORIES
INSERT INTO categories (id, name, slug, description, image, parent_id, sort_order, is_active) VALUES
(1, 'Electronics', 'electronics', 'Smartphones, laptops, accessories and gadgets', 'category-electronics.jpg', NULL, 1, 1),
(2, 'Fashion & Apparel', 'fashion-apparel', 'Men, women and kids clothing and accessories', 'category-fashion.jpg', NULL, 2, 1),
(3, 'Home & Living', 'home-living', 'Furniture, decor, kitchenware and home essentials', 'category-home.jpg', NULL, 3, 1),
(4, 'Beauty & Personal Care', 'beauty-personal-care', 'Skincare, haircare, makeup and grooming', 'category-beauty.jpg', NULL, 4, 1),
(5, 'Sports & Fitness', 'sports-fitness', 'Workout equipment, sportswear and outdoor gear', 'category-sports.jpg', NULL, 5, 1),
(6, 'Books & Stationery', 'books-stationery', 'Best-selling novels, academic books and stationery items', 'category-books.jpg', NULL, 6, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- 4. SEED PRODUCTS
INSERT INTO products (id, category_id, name, slug, description, short_description, sku, brand, price, sale_price, stock_quantity, low_stock_threshold, weight, is_active, is_featured, view_count, total_sold, avg_rating, review_count) VALUES
(1, 1, 'Sony WH-1000XM5 Wireless Noise Canceling Headphones', 'sony-wh-1000xm5-wireless-noise-canceling-headphones', 'Industry-leading noise cancellation with two processors and 8 microphones. Magnificent audio quality engineered with the Integrated Processor V1. Crystal clear hands-free calling with 4 beamforming microphones.', 'Premium noise-canceling wireless over-ear headphones with 30-hour battery life.', 'SONY-WH1000XM5-BLK', 'Sony', 38500.00, 34990.00, 25, 5, 0.25, 1, 1, 320, 14, 4.80, 5),
(2, 1, 'Apple MacBook Air 13-inch M2 Chip 256GB SSD', 'apple-macbook-air-13-inch-m2-chip-256gb-ssd', 'Strikingly thin design with all-day battery life. Supercharged by the next-generation M2 chip, delivering incredible speed and power efficiency. 13.6-inch Liquid Retina display with True Tone.', '13.6-inch Liquid Retina display, 8GB Unified Memory, 256GB SSD storage, Midnight color.', 'APL-MBA-M2-256', 'Apple', 125000.00, 118500.00, 12, 3, 1.24, 1, 1, 540, 8, 4.90, 4),
(3, 1, 'Logitech MX Master 3S Wireless Performance Mouse', 'logitech-mx-master-3s-wireless-performance-mouse', 'Quiet Clicks feel satisfying and make 90% less noise. 8K DPI any-surface tracking, including glass. MagSpeed electromagnetic scrolling is 90% faster and 87% more precise.', 'Ergonomic wireless mouse with 8K DPI sensor and ultra-fast electromagnetic scrolling.', 'LOGI-MXM3S-GRY', 'Logitech', 11500.00, 9990.00, 45, 10, 0.14, 1, 1, 210, 22, 4.75, 3),
(4, 1, 'Samsung 27-inch Odyssey G5 WQHD Curved Gaming Monitor', 'samsung-27-inch-odyssey-g5-wqhd-curved-gaming-monitor', '1000R curve matches the human eye for maximum immersion. WQHD resolution packs in 1.7 times the pixel density of Full HD. 144Hz refresh rate and 1ms response time.', '27" WQHD 144Hz 1ms 1000R Curved Gaming Monitor with HDR10 and AMD FreeSync Premium.', 'SAM-ODYSSEY-G5-27', 'Samsung', 36000.00, 32500.00, 18, 4, 4.50, 1, 0, 180, 6, 4.60, 2),
(5, 2, 'Men''s Premium Slim-Fit Cotton Oxford Shirt', 'mens-premium-slim-fit-cotton-oxford-shirt', 'Crafted from 100% long-staple combed cotton for superior comfort and breathability. Features a button-down collar, chest pocket, and durable pearl buttons. Tailored slim fit suitable for formal and smart-casual occasions.', '100% combed cotton classic button-down Oxford shirt in Navy Blue.', 'OXF-SHIRT-NVY-L', 'Heritage Club', 2450.00, 1950.00, 60, 10, 0.30, 1, 1, 410, 35, 4.50, 6),
(6, 2, 'Women''s Elegant Floral Print Summer Maxi Dress', 'womens-elegant-floral-print-summer-maxi-dress', 'Flowing silhouette with an adjustable waist tie and breathable chiffon fabric. Features vibrant floral prints and a tiered skirt design. Perfect for festive celebrations, dinners, and vacations.', 'Lightweight chiffon floral maxi dress with adjustable waist and ruffled hem.', 'FLR-MAXI-DRS-M', 'Luxe Aura', 3800.00, 3200.00, 30, 5, 0.40, 1, 1, 290, 18, 4.70, 3),
(7, 2, 'Men''s Genuine Leather Bifold Wallet with RFID Blocking', 'mens-genuine-leather-bifold-wallet-rfid', 'Handcrafted from full-grain vegetable-tanned leather. Includes 8 card slots, 2 currency compartments, and an ID window. Embedded with RFID-blocking technology to protect your digital identity.', 'Full-grain leather bifold wallet with 8 card slots and advanced RFID protection.', 'WAL-LEA-RFID-BRN', 'UrbanHide', 1650.00, 1350.00, 80, 15, 0.12, 1, 0, 150, 28, 4.65, 4),
(8, 3, 'Ergonomic High-Back Mesh Executive Office Chair', 'ergonomic-high-back-mesh-executive-office-chair', 'Engineered for all-day comfort with dynamic lumbar support, 3D adjustable armrests, breathable mesh back, and 135-degree recline mechanism. Heavy-duty aluminum base supports up to 150kg.', 'Breathable high-back ergonomic chair with 3D armrests and adjustable lumbar cushion.', 'CHR-ERGO-MESH-BLK', 'ErgoPro', 18500.00, 15990.00, 14, 3, 14.50, 1, 1, 380, 12, 4.85, 4),
(9, 3, 'Smart LED Ambient Desk Lamp with Wireless Charging', 'smart-led-ambient-desk-lamp-wireless-charging', 'Features customizable color temperatures (2700K - 6500K), touch dimmer controls, memory function, and an integrated 15W Qi fast wireless charging pad at the base.', 'Touch control LED desk lamp with 5 color modes, timer, and 15W wireless charger.', 'LMP-LED-QI-WHT', 'Lumex', 4200.00, 3450.00, 40, 8, 0.85, 1, 0, 120, 15, 4.40, 2),
(10, 4, 'Hydrating Hyaluronic Acid Serum with Vitamin B5 30ml', 'hydrating-hyaluronic-acid-serum-vitamin-b5-30ml', 'Formulated with multi-molecular weight hyaluronic acid to penetrate multiple skin layers. Delivers deep, long-lasting hydration, smooths fine lines, and restores skin suppleness.', 'Intensive moisturizing serum with pure hyaluronic acid and Provitamin B5.', 'SKN-HYA-SERUM-30', 'DermaPure', 1850.00, 1490.00, 95, 15, 0.08, 1, 1, 510, 62, 4.90, 8),
(11, 4, 'Organic Cold-Pressed Moroccan Argan Oil 100ml', 'organic-cold-pressed-moroccan-argan-oil-100ml', '100% pure, unrefined USDA certified organic argan oil. Rich in essential fatty acids and Vitamin E to deeply nourish hair, skin, and nails without feeling greasy.', '100% pure organic argan oil for radiant hair shine and intense skin hydration.', 'OIL-ARGAN-100ML', 'NatureGlow', 2200.00, 1850.00, 50, 10, 0.15, 1, 0, 175, 20, 4.60, 3),
(12, 5, 'Adjustable Cast Iron Dumbbell Set 20KG with Connector', 'adjustable-cast-iron-dumbbell-set-20kg', 'Complete home gym set including 12 cast iron weight plates, 2 textured chrome handles, star-lock collars, and an extension bar that converts dumbbells into a barbell.', '20KG total weight dumbbell & barbell combo set with anti-slip grip and carrying case.', 'FIT-DUMBBELL-20KG', 'PowerFit', 6500.00, 5490.00, 20, 4, 20.00, 1, 1, 260, 16, 4.70, 5),
(13, 5, 'Pro Non-Slip Eco-Friendly TPE Yoga Mat 6mm', 'pro-non-slip-eco-friendly-tpe-yoga-mat-6mm', 'High-density dual-layer textured design provides unmatched grip on wood, tile, or cement floors. Includes alignment lines and a convenient carrying strap.', '6mm dual-layer eco-friendly TPE workout mat with laser alignment guidelines.', 'FIT-YOGA-MAT-6MM', 'ZenActive', 2100.00, 1650.00, 35, 8, 0.90, 1, 0, 140, 19, 4.55, 3),
(14, 6, 'Atomic Habits by James Clear (Hardcover Edition)', 'atomic-habits-james-clear-hardcover', 'An easy and proven way to build good habits and break bad ones. Over 10 million copies sold worldwide. Learn practical strategies to form good habits, break bad ones, and master tiny behaviors.', 'The definitive guide to habit formation and personal growth by James Clear.', 'BOK-ATOMIC-HABITS', 'Penguin Random House', 1450.00, 1150.00, 120, 20, 0.45, 1, 1, 620, 85, 4.95, 12),
(15, 6, 'Luxury Matte Black Rollerball Pen with Refill Gift Box', 'luxury-matte-black-rollerball-pen-gift-box', 'Precision brass body with a sleek matte black finish and gold trim accents. Smooth 0.5mm Swiss ink cartridge delivers an effortless writing experience.', 'Executive matte black metal pen with gold trims in a velvet-lined presentation gift box.', 'STN-PEN-MATTE-GLD', 'Monarch', 1800.00, 1400.00, 65, 12, 0.20, 1, 0, 110, 14, 4.60, 2)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- 5. SEED PRODUCT IMAGES
INSERT INTO product_images (id, product_id, image_path, alt_text, sort_order, is_primary) VALUES
(1, 1, 'sony-xm5-1.jpg', 'Sony WH-1000XM5 Front View', 1, 1),
(2, 1, 'sony-xm5-2.jpg', 'Sony WH-1000XM5 Folded in Case', 2, 0),
(3, 2, 'macbook-air-m2-1.jpg', 'MacBook Air M2 Midnight', 1, 1),
(4, 2, 'macbook-air-m2-2.jpg', 'MacBook Air M2 Side Profile', 2, 0),
(5, 3, 'logitech-mx3s-1.jpg', 'Logitech MX Master 3S Grey', 1, 1),
(6, 4, 'samsung-g5-1.jpg', 'Samsung Odyssey G5 Curved Monitor', 1, 1),
(7, 5, 'oxford-shirt-1.jpg', 'Men Navy Oxford Shirt Front', 1, 1),
(8, 6, 'floral-maxi-1.jpg', 'Women Floral Maxi Dress', 1, 1),
(9, 7, 'leather-wallet-1.jpg', 'Men Leather Bifold Wallet', 1, 1),
(10, 8, 'ergo-chair-1.jpg', 'Ergonomic Mesh Chair Black', 1, 1),
(11, 9, 'desk-lamp-1.jpg', 'Smart LED Desk Lamp with Charger', 1, 1),
(12, 10, 'serum-hyaluronic-1.jpg', 'Hyaluronic Acid Serum 30ml', 1, 1),
(13, 11, 'argan-oil-1.jpg', 'Organic Moroccan Argan Oil', 1, 1),
(14, 12, 'dumbbell-set-1.jpg', '20KG Cast Iron Dumbbell Set', 1, 1),
(15, 13, 'yoga-mat-1.jpg', 'Pro Non-Slip TPE Yoga Mat', 1, 1),
(16, 14, 'atomic-habits-1.jpg', 'Atomic Habits Hardcover Book', 1, 1),
(17, 15, 'luxury-pen-1.jpg', 'Matte Black Rollerball Pen Gift Box', 1, 1)
ON DUPLICATE KEY UPDATE image_path=VALUES(image_path);

-- 6. SEED OFFERS
INSERT INTO offers (id, name, description, discount_type, discount_value, min_order_amount, max_discount_amount, start_date, end_date, usage_limit, per_user_limit, times_used, is_flash_sale, flash_stock, is_active, created_by) VALUES
(1, 'Grand Summer Mega Sale', 'Get 15% OFF across all categories on orders over ৳2,000', 'percentage', 15.00, 2000.00, 3000.00, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 1000, 3, 12, 0, NULL, 1, 1),
(2, 'Midnight Flash Sale', 'Limited-time instant ৳500 flat discount on electronics and fitness', 'fixed', 500.00, 3500.00, 500.00, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 200, 1, 8, 1, 50, 1, 1),
(3, 'Gadget Fest Discount', 'Special 20% discount on selected audio and computer accessories', 'percentage', 20.00, 1500.00, 2500.00, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 500, 2, 5, 0, NULL, 1, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- 7. SEED OFFER CATEGORIES / PRODUCTS
INSERT INTO offer_categories (id, offer_id, category_id) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3),
(4, 2, 1),
(5, 2, 5)
ON DUPLICATE KEY UPDATE offer_id=VALUES(offer_id);

INSERT INTO offer_products (id, offer_id, product_id) VALUES
(1, 3, 1),
(2, 3, 3)
ON DUPLICATE KEY UPDATE offer_id=VALUES(offer_id);

-- 8. SEED COUPONS
INSERT INTO coupons (id, code, description, discount_type, discount_value, min_order_amount, max_discount, start_date, end_date, usage_limit, per_user_limit, times_used, is_active, created_by) VALUES
(1, 'WELCOME10', '10% OFF on your first purchase, minimum ৳1,000', 'percentage', 10.00, 1000.00, 1500.00, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 1000, 1, 14, 1, 1),
(2, 'SAVE500', 'Flat ৳500 OFF on orders above ৳5,000', 'fixed', 500.00, 5000.00, 500.00, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 500, 2, 22, 1, 1),
(3, 'FLASH20', 'Special 20% promotional discount up to ৳2,000', 'percentage', 20.00, 3000.00, 2000.00, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 100, 1, 6, 1, 1)
ON DUPLICATE KEY UPDATE code=VALUES(code);

-- 9. SEED BANNERS
INSERT INTO banners (id, title, subtitle, image, button_text, button_url, position, sort_order, start_date, end_date, is_active, created_by) VALUES
(1, 'Sony WH-1000XM5', 'Wireless noise cancelling headphones with 30-hour battery life.', 'hero-electronics.jpg', 'Shop headphones', 'product.php?id=1', 'hero', 1, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 1, 1),
(2, 'MacBook Air M2', '13-inch laptop with the M2 chip and a 256GB SSD.', 'hero-electronics.jpg', 'Shop laptops', 'product.php?id=2', 'hero', 2, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 1, 1),
(3, 'Workspace essentials', 'Office chairs, desk lighting and accessories.', 'hero-fashion.jpg', 'Shop home and living', 'products.php?category=home-living', 'hero', 3, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 1, 1),
(4, 'Seasonal clearance', 'Up to 20% off selected electronics, fashion and home.', 'promo-audio.jpg', 'Shop the sale', 'products.php?sale=1', 'promotional', 1, '2026-01-01 00:00:00', '2026-12-31 23:59:59', 1, 1)
ON DUPLICATE KEY UPDATE title=VALUES(title);

-- 10. SEED HOMEPAGE SECTIONS
INSERT INTO homepage_sections (id, section_key, title, subtitle, section_type, sort_order, max_items, is_active) VALUES
(1, 'featured_products', 'Featured Products', 'Hand-picked premium selections curated for you', 'featured', 1, 8, 1),
(2, 'flash_sale', 'Flash Deals & Limited Offers', 'Hurry! Limited stock at unbeatable discounts', 'flash_sale', 2, 4, 1),
(3, 'best_sellers', 'Best Selling Products', 'Top rated and loved by thousands of happy shoppers', 'bestseller', 3, 8, 1),
(4, 'new_arrivals', 'New Arrivals', 'Fresh additions just landed in our catalog', 'new_arrival', 4, 8, 1)
ON DUPLICATE KEY UPDATE section_key=VALUES(section_key);

-- 11. SEED HOMEPAGE SECTION ITEMS
INSERT INTO homepage_section_items (section_id, product_id, sort_order) VALUES
(1, 1, 1), (1, 2, 2), (1, 5, 3), (1, 8, 4), (1, 10, 5), (1, 12, 6), (1, 14, 7), (1, 3, 8),
(2, 1, 1), (2, 3, 2), (2, 5, 3), (2, 12, 4)
ON DUPLICATE KEY UPDATE sort_order=VALUES(sort_order);

-- 12. SEED ORDERS & ITEMS (Sample Completed Orders)
INSERT INTO orders (id, user_id, order_number, subtotal, discount_amount, coupon_discount, shipping_fee, total_amount, coupon_id, coupon_code, order_status, payment_status, payment_method, shipping_name, shipping_phone, shipping_address, shipping_city, shipping_area, shipping_postal, notes, created_at) VALUES
(1, 4, 'ORD-20260801-001', 34990.00, 0.00, 1500.00, 60.00, 33550.00, 1, 'WELCOME10', 'delivered', 'paid', 'online', 'Rahim Ahmed', '+8801811000004', 'House 42, Road 11, Block D, Banani', 'Dhaka', 'Banani', '1213', 'Please call before delivery', '2026-08-01 14:30:00'),
(2, 4, 'ORD-20260810-002', 4690.00, 0.00, 0.00, 60.00, 4750.00, NULL, NULL, 'shipped', 'paid', 'bkash', 'Rahim Ahmed', '+8801811000004', 'House 42, Road 11, Block D, Banani', 'Dhaka', 'Banani', '1213', NULL, '2026-08-10 11:15:00'),
(3, 5, 'ORD-20260815-003', 15990.00, 0.00, 500.00, 120.00, 15610.00, 2, 'SAVE500', 'processing', 'paid', 'sslcommerz', 'Karim Uddin', '+8801911000005', 'Plot 15, Sector 7, Uttara', 'Dhaka', 'Uttara', '1230', 'Deliver after 5 PM', '2026-08-15 16:45:00'),
(4, 5, 'ORD-20260820-004', 2800.00, 0.00, 0.00, 60.00, 2860.00, NULL, NULL, 'pending', 'pending', 'cod', 'Karim Uddin', '+8801911000005', 'Plot 15, Sector 7, Uttara', 'Dhaka', 'Uttara', '1230', NULL, '2026-08-20 09:20:00')
ON DUPLICATE KEY UPDATE order_number=VALUES(order_number);

INSERT INTO order_items (id, order_id, product_id, product_name, product_sku, quantity, unit_price, discount_amount, total_price) VALUES
(1, 1, 1, 'Sony WH-1000XM5 Wireless Noise Canceling Headphones', 'SONY-WH1000XM5-BLK', 1, 34990.00, 0.00, 34990.00),
(2, 2, 5, 'Men''s Premium Slim-Fit Cotton Oxford Shirt', 'OXF-SHIRT-NVY-L', 1, 1950.00, 0.00, 1950.00),
(3, 2, 10, 'Hydrating Hyaluronic Acid Serum with Vitamin B5 30ml', 'SKN-HYA-SERUM-30', 1, 1490.00, 0.00, 1490.00),
(4, 2, 15, 'Luxury Matte Black Rollerball Pen with Refill Gift Box', 'STN-PEN-MATTE-GLD', 1, 1400.00, 0.00, 1400.00),
(5, 3, 8, 'Ergonomic High-Back Mesh Executive Office Chair', 'CHR-ERGO-MESH-BLK', 1, 15990.00, 0.00, 15990.00),
(6, 4, 14, 'Atomic Habits by James Clear (Hardcover Edition)', 'BOK-ATOMIC-HABITS', 2, 1150.00, 0.00, 2300.00)
ON DUPLICATE KEY UPDATE order_id=VALUES(order_id);

INSERT INTO payments (id, order_id, payment_method, transaction_id, amount, currency, status, gateway_response, paid_at) VALUES
(1, 1, 'online', 'TXN-ONLINE-987654321', 33550.00, 'BDT', 'completed', '{"status":"SUCCESS","card_type":"VISA-CREDIT"}', '2026-08-01 14:32:10'),
(2, 2, 'bkash', 'BKASH-8A9F439D', 4750.00, 'BDT', 'completed', '{"status":"SUCCESS","wallet":"01811000004"}', '2026-08-10 11:16:30'),
(3, 3, 'sslcommerz', 'SSLCZ-20260815-7788', 15610.00, 'BDT', 'completed', '{"status":"VALIDATED","bank_tran_id":"BTR8899"}', '2026-08-15 16:47:05'),
(4, 4, 'cod', NULL, 2860.00, 'BDT', 'pending', NULL, NULL)
ON DUPLICATE KEY UPDATE order_id=VALUES(order_id);

INSERT INTO coupon_usage (id, coupon_id, user_id, order_id, discount_amount, used_at) VALUES
(1, 1, 4, 1, 1500.00, '2026-08-01 14:30:00'),
(2, 2, 5, 3, 500.00, '2026-08-15 16:45:00')
ON DUPLICATE KEY UPDATE coupon_id=VALUES(coupon_id);

-- 13. SEED REVIEWS
INSERT INTO reviews (id, user_id, product_id, order_id, rating, comment, status, created_at) VALUES
(1, 4, 1, 1, 5, 'Absolutely blown away by the active noise cancellation! Battery life easily lasts multiple days of heavy listening during office hours. Best investment in headphones so far.', 'approved', '2026-08-05 18:20:00'),
(2, 4, 5, 2, 5, 'Exceptional build quality and pure cotton feel. Fits perfectly on shoulders and collar remains crisp after washing.', 'approved', '2026-08-14 10:15:00'),
(3, 5, 8, 3, 5, 'Transformed my work from home posture! The lumbar support adjusts exactly where needed and the mesh keeps cool during long sessions.', 'approved', '2026-08-18 19:40:00'),
(4, 4, 14, NULL, 5, 'One of the most practical life-changing books on habit building and behavior change. Delivered promptly and in pristine hardcover condition.', 'approved', '2026-08-21 12:00:00')
ON DUPLICATE KEY UPDATE rating=VALUES(rating);

-- 14. SEED SETTINGS
INSERT INTO settings (setting_key, setting_value, setting_group) VALUES
('site_name', 'NovaMart', 'general'),
('site_tagline', 'Online shopping in Bangladesh', 'general'),
('site_email', 'support@novamart.com', 'general'),
('site_phone', '+880 9612-000000', 'general'),
('site_address', 'Gulshan 2, Dhaka 1212, Bangladesh', 'general'),
('currency_symbol', '৳', 'localization'),
('currency_code', 'BDT', 'localization'),
('shipping_inside_city', '60.00', 'shipping'),
('shipping_outside_city', '120.00', 'shipping'),
('free_shipping_threshold', '5000.00', 'shipping'),
('tax_percentage', '0.00', 'finance'),
('maintenance_mode', '0', 'system')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

-- 15. SEED ADMIN LOGS
INSERT INTO admin_logs (user_id, action, entity_type, entity_id, description, ip_address) VALUES
(1, 'DATABASE_INITIALIZATION', 'system', NULL, 'Database schema seeded with default products, categories, coupons, and settings', '127.0.0.1');
