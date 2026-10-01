-- ==============================================================================
-- FULL-STACK E-COMMERCE DATABASE SCHEMA (MySQL 8+)
-- Database: ecommerce_db
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS ecommerce_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecommerce_db;

-- 1. USERS TABLE
CREATE TABLE IF NOT EXISTS users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name      VARCHAR(100) NOT NULL,
    last_name       VARCHAR(100) NOT NULL,
    email           VARCHAR(255) NOT NULL UNIQUE,
    phone           VARCHAR(20) DEFAULT NULL,
    password_hash   VARCHAR(255) NOT NULL,
    role            ENUM('customer','staff','admin','owner') NOT NULL DEFAULT 'customer',
    avatar          VARCHAR(255) DEFAULT NULL,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    email_verified  TINYINT(1) NOT NULL DEFAULT 0,
    last_login_at   DATETIME DEFAULT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. ADDRESSES TABLE
CREATE TABLE IF NOT EXISTS addresses (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    label           VARCHAR(50) DEFAULT 'Home',
    full_name       VARCHAR(200) NOT NULL,
    phone           VARCHAR(20) NOT NULL,
    address_line1   VARCHAR(255) NOT NULL,
    address_line2   VARCHAR(255) DEFAULT NULL,
    city            VARCHAR(100) NOT NULL,
    area            VARCHAR(100) DEFAULT NULL,
    postal_code     VARCHAR(20) DEFAULT NULL,
    country         VARCHAR(100) NOT NULL DEFAULT 'Bangladesh',
    is_default      TINYINT(1) NOT NULL DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. CATEGORIES TABLE
CREATE TABLE IF NOT EXISTS categories (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(200) NOT NULL,
    slug            VARCHAR(200) NOT NULL UNIQUE,
    description     TEXT DEFAULT NULL,
    image           VARCHAR(255) DEFAULT NULL,
    parent_id       INT UNSIGNED DEFAULT NULL,
    sort_order      INT NOT NULL DEFAULT 0,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_parent_id (parent_id),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. PRODUCTS TABLE
CREATE TABLE IF NOT EXISTS products (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id         INT UNSIGNED DEFAULT NULL,
    name                VARCHAR(300) NOT NULL,
    slug                VARCHAR(300) NOT NULL UNIQUE,
    description         TEXT DEFAULT NULL,
    short_description   VARCHAR(500) DEFAULT NULL,
    sku                 VARCHAR(100) DEFAULT NULL UNIQUE,
    brand               VARCHAR(150) DEFAULT NULL,
    price               DECIMAL(12,2) NOT NULL,
    sale_price          DECIMAL(12,2) DEFAULT NULL,
    stock_quantity      INT NOT NULL DEFAULT 0,
    low_stock_threshold INT NOT NULL DEFAULT 5,
    weight              DECIMAL(8,2) DEFAULT NULL,
    is_active           TINYINT(1) NOT NULL DEFAULT 1,
    is_featured         TINYINT(1) NOT NULL DEFAULT 0,
    view_count          INT UNSIGNED NOT NULL DEFAULT 0,
    total_sold          INT UNSIGNED NOT NULL DEFAULT 0,
    avg_rating          DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    review_count        INT UNSIGNED NOT NULL DEFAULT 0,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_slug (slug),
    INDEX idx_sku (sku),
    INDEX idx_is_active (is_active),
    INDEX idx_is_featured (is_featured),
    INDEX idx_price (price),
    INDEX idx_created_at (created_at),
    INDEX idx_total_sold (total_sold),
    INDEX idx_avg_rating (avg_rating),
    FULLTEXT idx_search (name, description, brand, sku)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. PRODUCT IMAGES TABLE
CREATE TABLE IF NOT EXISTS product_images (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id      INT UNSIGNED NOT NULL,
    image_path      VARCHAR(255) NOT NULL,
    alt_text        VARCHAR(255) DEFAULT NULL,
    sort_order      INT NOT NULL DEFAULT 0,
    is_primary      TINYINT(1) NOT NULL DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. CART TABLE
CREATE TABLE IF NOT EXISTS cart (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL UNIQUE,
    coupon_id       INT UNSIGNED DEFAULT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. CART ITEMS TABLE
CREATE TABLE IF NOT EXISTS cart_items (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id         INT UNSIGNED NOT NULL,
    product_id      INT UNSIGNED NOT NULL,
    quantity        INT UNSIGNED NOT NULL DEFAULT 1,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cart_id) REFERENCES cart(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY uk_cart_product (cart_id, product_id),
    INDEX idx_cart_id (cart_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. WISHLIST TABLE
CREATE TABLE IF NOT EXISTS wishlist (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL UNIQUE,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. WISHLIST ITEMS TABLE
CREATE TABLE IF NOT EXISTS wishlist_items (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    wishlist_id     INT UNSIGNED NOT NULL,
    product_id      INT UNSIGNED NOT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (wishlist_id) REFERENCES wishlist(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY uk_wishlist_product (wishlist_id, product_id),
    INDEX idx_wishlist_id (wishlist_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. OFFERS TABLE
CREATE TABLE IF NOT EXISTS offers (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name                VARCHAR(200) NOT NULL,
    description         TEXT DEFAULT NULL,
    discount_type       ENUM('percentage','fixed') NOT NULL,
    discount_value      DECIMAL(12,2) NOT NULL,
    min_order_amount    DECIMAL(12,2) DEFAULT NULL,
    max_discount_amount DECIMAL(12,2) DEFAULT NULL,
    start_date          DATETIME NOT NULL,
    end_date            DATETIME NOT NULL,
    usage_limit         INT UNSIGNED DEFAULT NULL,
    per_user_limit      INT UNSIGNED DEFAULT NULL,
    times_used          INT UNSIGNED NOT NULL DEFAULT 0,
    is_flash_sale       TINYINT(1) NOT NULL DEFAULT 0,
    flash_stock         INT UNSIGNED DEFAULT NULL,
    is_active           TINYINT(1) NOT NULL DEFAULT 1,
    created_by          INT UNSIGNED DEFAULT NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_is_active (is_active),
    INDEX idx_dates (start_date, end_date),
    INDEX idx_flash (is_flash_sale)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. OFFER PRODUCTS TABLE
CREATE TABLE IF NOT EXISTS offer_products (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    offer_id        INT UNSIGNED NOT NULL,
    product_id      INT UNSIGNED NOT NULL,
    FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY uk_offer_product (offer_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. OFFER CATEGORIES TABLE
CREATE TABLE IF NOT EXISTS offer_categories (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    offer_id        INT UNSIGNED NOT NULL,
    category_id     INT UNSIGNED NOT NULL,
    FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY uk_offer_category (offer_id, category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. COUPONS TABLE
CREATE TABLE IF NOT EXISTS coupons (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code                VARCHAR(50) NOT NULL UNIQUE,
    description         TEXT DEFAULT NULL,
    discount_type       ENUM('percentage','fixed') NOT NULL,
    discount_value      DECIMAL(12,2) NOT NULL,
    min_order_amount    DECIMAL(12,2) DEFAULT NULL,
    max_discount        DECIMAL(12,2) DEFAULT NULL,
    start_date          DATETIME NOT NULL,
    end_date            DATETIME NOT NULL,
    usage_limit         INT UNSIGNED DEFAULT NULL,
    per_user_limit      INT UNSIGNED NOT NULL DEFAULT 1,
    times_used          INT UNSIGNED NOT NULL DEFAULT 0,
    is_active           TINYINT(1) NOT NULL DEFAULT 1,
    created_by          INT UNSIGNED DEFAULT NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_code (code),
    INDEX idx_is_active (is_active),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. ORDERS TABLE
CREATE TABLE IF NOT EXISTS orders (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id             INT UNSIGNED NOT NULL,
    order_number        VARCHAR(30) NOT NULL UNIQUE,
    subtotal            DECIMAL(12,2) NOT NULL,
    discount_amount     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    coupon_discount     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    shipping_fee        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total_amount        DECIMAL(12,2) NOT NULL,
    coupon_id           INT UNSIGNED DEFAULT NULL,
    coupon_code         VARCHAR(50) DEFAULT NULL,
    order_status        ENUM('pending','processing','shipped','delivered','cancelled','refunded') NOT NULL DEFAULT 'pending',
    payment_status      ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
    payment_method      ENUM('cod','online','bkash','nagad','sslcommerz','card') NOT NULL DEFAULT 'cod',
    shipping_name       VARCHAR(200) NOT NULL,
    shipping_phone      VARCHAR(20) NOT NULL,
    shipping_address    VARCHAR(500) NOT NULL,
    shipping_city       VARCHAR(100) NOT NULL,
    shipping_area       VARCHAR(100) DEFAULT NULL,
    shipping_postal     VARCHAR(20) DEFAULT NULL,
    notes               TEXT DEFAULT NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_user_id (user_id),
    INDEX idx_order_number (order_number),
    INDEX idx_order_status (order_status),
    INDEX idx_payment_status (payment_status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. ORDER ITEMS TABLE
CREATE TABLE IF NOT EXISTS order_items (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id        INT UNSIGNED NOT NULL,
    product_id      INT UNSIGNED NOT NULL,
    product_name    VARCHAR(300) NOT NULL,
    product_sku     VARCHAR(100) DEFAULT NULL,
    quantity        INT UNSIGNED NOT NULL,
    unit_price      DECIMAL(12,2) NOT NULL,
    discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    total_price     DECIMAL(12,2) NOT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. PAYMENTS TABLE
CREATE TABLE IF NOT EXISTS payments (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id            INT UNSIGNED NOT NULL,
    payment_method      VARCHAR(50) NOT NULL,
    transaction_id      VARCHAR(255) DEFAULT NULL,
    amount              DECIMAL(12,2) NOT NULL,
    currency            VARCHAR(10) NOT NULL DEFAULT 'BDT',
    status              ENUM('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
    gateway_response    TEXT DEFAULT NULL,
    paid_at             DATETIME DEFAULT NULL,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_transaction_id (transaction_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. COUPON USAGE TABLE
CREATE TABLE IF NOT EXISTS coupon_usage (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    coupon_id       INT UNSIGNED NOT NULL,
    user_id         INT UNSIGNED NOT NULL,
    order_id        INT UNSIGNED NOT NULL,
    discount_amount DECIMAL(12,2) NOT NULL,
    used_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_coupon_user (coupon_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. REVIEWS TABLE
CREATE TABLE IF NOT EXISTS reviews (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    product_id      INT UNSIGNED NOT NULL,
    order_id        INT UNSIGNED DEFAULT NULL,
    rating          TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment         TEXT DEFAULT NULL,
    status          ENUM('pending','approved','hidden','deleted') NOT NULL DEFAULT 'pending',
    admin_note      TEXT DEFAULT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    UNIQUE KEY uk_user_product_order (user_id, product_id, order_id),
    INDEX idx_product_id (product_id),
    INDEX idx_status (status),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 19. BANNERS TABLE
CREATE TABLE IF NOT EXISTS banners (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(200) DEFAULT NULL,
    subtitle        VARCHAR(300) DEFAULT NULL,
    image           VARCHAR(255) NOT NULL,
    button_text     VARCHAR(100) DEFAULT NULL,
    button_url      VARCHAR(500) DEFAULT NULL,
    position        ENUM('hero','promotional','sidebar') NOT NULL DEFAULT 'hero',
    sort_order      INT NOT NULL DEFAULT 0,
    start_date      DATETIME DEFAULT NULL,
    end_date        DATETIME DEFAULT NULL,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_by      INT UNSIGNED DEFAULT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_position (position),
    INDEX idx_is_active (is_active),
    INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 20. HOMEPAGE SECTIONS TABLE
CREATE TABLE IF NOT EXISTS homepage_sections (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_key     VARCHAR(50) NOT NULL UNIQUE,
    title           VARCHAR(200) NOT NULL,
    subtitle        VARCHAR(300) DEFAULT NULL,
    section_type    ENUM('featured','bestseller','new_arrival','flash_sale','offer','custom') NOT NULL,
    sort_order      INT NOT NULL DEFAULT 0,
    max_items       INT NOT NULL DEFAULT 8,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_section_key (section_key),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 21. HOMEPAGE SECTION ITEMS TABLE
CREATE TABLE IF NOT EXISTS homepage_section_items (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_id      INT UNSIGNED NOT NULL,
    product_id      INT UNSIGNED NOT NULL,
    sort_order      INT NOT NULL DEFAULT 0,
    FOREIGN KEY (section_id) REFERENCES homepage_sections(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY uk_section_product (section_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 22. ADMIN LOGS TABLE
CREATE TABLE IF NOT EXISTS admin_logs (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    action          VARCHAR(100) NOT NULL,
    entity_type     VARCHAR(50) NOT NULL,
    entity_id       INT UNSIGNED DEFAULT NULL,
    description     TEXT DEFAULT NULL,
    ip_address      VARCHAR(45) DEFAULT NULL,
    user_agent      VARCHAR(500) DEFAULT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 23. SETTINGS TABLE
CREATE TABLE IF NOT EXISTS settings (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key     VARCHAR(100) NOT NULL UNIQUE,
    setting_value   TEXT DEFAULT NULL,
    setting_group   VARCHAR(50) NOT NULL DEFAULT 'general',
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key),
    INDEX idx_group (setting_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 24. CONTACT MESSAGES TABLE
CREATE TABLE IF NOT EXISTS contact_messages (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(200) NOT NULL,
    email           VARCHAR(255) NOT NULL,
    subject         VARCHAR(300) DEFAULT NULL,
    message         TEXT NOT NULL,
    is_read         TINYINT(1) NOT NULL DEFAULT 0,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password reset tokens. Only a SHA-256 hash of the token is stored, so a
-- database leak cannot be replayed against the reset endpoint.
CREATE TABLE IF NOT EXISTS password_resets (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    token_hash  CHAR(64) NOT NULL,
    expires_at  DATETIME NOT NULL,
    used_at     DATETIME DEFAULT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token_hash),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Shared session storage. Required on any host that runs more than one
-- instance (serverless included), where local session files are not shared.
CREATE TABLE IF NOT EXISTS sessions (
    id          CHAR(128) NOT NULL PRIMARY KEY,
    payload     MEDIUMTEXT NOT NULL,
    expires_at  DATETIME NOT NULL,
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
