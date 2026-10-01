-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: ecommerce_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `ecommerce_db`
--



--
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addresses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `label` varchar(50) DEFAULT 'Home',
  `full_name` varchar(200) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `area` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) NOT NULL DEFAULT 'Bangladesh',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addresses`
--

LOCK TABLES `addresses` WRITE;
/*!40000 ALTER TABLE `addresses` DISABLE KEYS */;
INSERT INTO `addresses` VALUES (1,4,'Home','Rahim Ahmed','+8801811000004','House 42, Road 11, Block D','Banani','Dhaka','Banani','1213','Bangladesh',1,'2026-08-25 01:10:00','2026-08-25 01:10:00'),(2,4,'Office','Rahim Ahmed','+8801811000004','Level 7, Concord Tower, Gulshan 1','Gulshan Avenue','Dhaka','Gulshan','1212','Bangladesh',0,'2026-08-25 01:10:00','2026-08-25 01:10:00'),(3,5,'Home','Karim Uddin','+8801911000005','Plot 15, Sector 7','Uttara','Dhaka','Uttara','1230','Bangladesh',1,'2026-08-25 01:10:00','2026-08-25 01:10:00'),(4,4,'Home','Rahim Ahmed','+8801811000004','House 42, Road 11, Block D','Banani','Dhaka','Banani','1213','Bangladesh',0,'2026-08-25 02:38:01','2026-08-25 02:38:01');
/*!40000 ALTER TABLE `addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_logs`
--

DROP TABLE IF EXISTS `admin_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(10) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_entity` (`entity_type`,`entity_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_logs`
--

LOCK TABLES `admin_logs` WRITE;
/*!40000 ALTER TABLE `admin_logs` DISABLE KEYS */;
INSERT INTO `admin_logs` VALUES (1,1,'DATABASE_INITIALIZATION','system',NULL,'Database schema seeded with default products, categories, coupons, and settings','127.0.0.1',NULL,'2026-08-25 01:10:00');
/*!40000 ALTER TABLE `admin_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `banners`
--

DROP TABLE IF EXISTS `banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banners` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) DEFAULT NULL,
  `subtitle` varchar(300) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `button_text` varchar(100) DEFAULT NULL,
  `button_url` varchar(500) DEFAULT NULL,
  `position` enum('hero','promotional','sidebar') NOT NULL DEFAULT 'hero',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_position` (`position`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_dates` (`start_date`,`end_date`),
  CONSTRAINT `banners_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `banners`
--

LOCK TABLES `banners` WRITE;
/*!40000 ALTER TABLE `banners` DISABLE KEYS */;
INSERT INTO `banners` VALUES (1,'Sony WH-1000XM5','Wireless noise cancelling headphones with 30-hour battery life.','hero-electronics.jpg','Shop headphones','product.php?id=1','hero',1,'2026-01-01 00:00:00','2026-12-31 23:59:59',1,1,'2026-08-25 01:10:00','2026-08-25 23:52:00'),(2,'MacBook Air M2','13-inch laptop with the M2 chip and a 256GB SSD.','hero-electronics.jpg','Shop laptops','product.php?id=2','hero',2,'2026-01-01 00:00:00','2026-12-31 23:59:59',1,1,'2026-08-25 01:10:00','2026-08-25 23:52:00'),(3,'Workspace essentials','Office chairs, desk lighting and accessories.','hero-fashion.jpg','Shop home and living','products.php?category=home-living','hero',3,'2026-01-01 00:00:00','2026-12-31 23:59:59',0,1,'2026-08-25 01:10:00','2026-08-25 23:52:00'),(4,'Seasonal clearance','Up to 20% off selected electronics, fashion and home.','promo-audio.jpg','Shop the sale','products.php?sale=1','promotional',1,'2026-01-01 00:00:00','2026-12-31 23:59:59',1,1,'2026-08-25 01:10:00','2026-08-25 23:52:00');
/*!40000 ALTER TABLE `banners` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cart` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `coupon_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart`
--

LOCK TABLES `cart` WRITE;
/*!40000 ALTER TABLE `cart` DISABLE KEYS */;
INSERT INTO `cart` VALUES (1,4,NULL,'2026-08-25 01:54:00','2026-08-25 01:54:00');
/*!40000 ALTER TABLE `cart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cart_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cart_product` (`cart_id`,`product_id`),
  KEY `product_id` (`product_id`),
  KEY `idx_cart_id` (`cart_id`),
  CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart_items`
--

LOCK TABLES `cart_items` WRITE;
/*!40000 ALTER TABLE `cart_items` DISABLE KEYS */;
INSERT INTO `cart_items` VALUES (4,1,1,2,'2026-08-25 02:46:23','2026-08-25 02:46:23'),(5,1,6,2,'2026-08-25 02:46:23','2026-08-25 02:46:23'),(6,1,9,2,'2026-08-25 02:46:23','2026-08-25 02:46:23');
/*!40000 ALTER TABLE `cart_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_slug` (`slug`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Electronics','electronics','Smartphones, laptops, accessories and gadgets','category-electronics.jpg',NULL,1,1,'2026-08-25 01:10:00','2026-08-25 01:10:00'),(2,'Fashion & Apparel','fashion-apparel','Men, women and kids clothing and accessories','category-fashion.jpg',NULL,2,1,'2026-08-25 01:10:00','2026-08-25 01:10:00'),(3,'Home & Living','home-living','Furniture, decor, kitchenware and home essentials','category-home.jpg',NULL,3,1,'2026-08-25 01:10:00','2026-08-25 01:10:00'),(4,'Beauty & Personal Care','beauty-personal-care','Skincare, haircare, makeup and grooming','category-beauty.jpg',NULL,4,1,'2026-08-25 01:10:00','2026-08-25 01:10:00'),(5,'Sports & Fitness','sports-fitness','Workout equipment, sportswear and outdoor gear','category-sports.jpg',NULL,5,1,'2026-08-25 01:10:00','2026-08-25 01:10:00'),(6,'Books & Stationery','books-stationery','Best-selling novels, academic books and stationery items','category-books.jpg',NULL,6,1,'2026-08-25 01:10:00','2026-08-25 01:10:00');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(300) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_is_read` (`is_read`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_messages`
--

LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupon_usage`
--

DROP TABLE IF EXISTS `coupon_usage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupon_usage` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `coupon_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `order_id` int(10) unsigned NOT NULL,
  `discount_amount` decimal(12,2) NOT NULL,
  `used_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`),
  KEY `idx_coupon_user` (`coupon_id`,`user_id`),
  CONSTRAINT `coupon_usage_ibfk_1` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_usage_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_usage_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupon_usage`
--

LOCK TABLES `coupon_usage` WRITE;
/*!40000 ALTER TABLE `coupon_usage` DISABLE KEYS */;
INSERT INTO `coupon_usage` VALUES (1,1,4,1,1500.00,'2026-08-01 14:30:00'),(2,2,5,3,500.00,'2026-08-15 16:45:00');
/*!40000 ALTER TABLE `coupon_usage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(12,2) NOT NULL,
  `min_order_amount` decimal(12,2) DEFAULT NULL,
  `max_discount` decimal(12,2) DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `usage_limit` int(10) unsigned DEFAULT NULL,
  `per_user_limit` int(10) unsigned NOT NULL DEFAULT 1,
  `times_used` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `created_by` (`created_by`),
  KEY `idx_code` (`code`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_dates` (`start_date`,`end_date`),
  CONSTRAINT `coupons_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupons`
--

LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
INSERT INTO `coupons` VALUES (1,'WELCOME10','10% OFF on your first purchase, minimum Óº│1,000','percentage',10.00,1000.00,1500.00,'2026-01-01 00:00:00','2026-12-31 23:59:59',1000,1,14,1,1,'2026-08-25 01:10:00','2026-08-25 01:10:00'),(2,'SAVE500','Flat Óº│500 OFF on orders above Óº│5,000','fixed',500.00,5000.00,500.00,'2026-01-01 00:00:00','2026-12-31 23:59:59',500,2,22,1,1,'2026-08-25 01:10:00','2026-08-25 01:10:00'),(3,'FLASH20','Special 20% promotional discount up to Óº│2,000','percentage',20.00,3000.00,2000.00,'2026-01-01 00:00:00','2026-12-31 23:59:59',100,1,6,1,1,'2026-08-25 01:10:00','2026-08-25 01:10:00');
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `homepage_section_items`
--

DROP TABLE IF EXISTS `homepage_section_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `homepage_section_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `section_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_section_product` (`section_id`,`product_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `homepage_section_items_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `homepage_sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `homepage_section_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `homepage_section_items`
--

LOCK TABLES `homepage_section_items` WRITE;
/*!40000 ALTER TABLE `homepage_section_items` DISABLE KEYS */;
INSERT INTO `homepage_section_items` VALUES (1,1,1,1),(2,1,2,2),(3,1,5,3),(4,1,8,4),(5,1,10,5),(6,1,12,6),(7,1,14,7),(8,1,3,8),(9,2,1,1),(10,2,3,2),(11,2,5,3),(12,2,12,4);
/*!40000 ALTER TABLE `homepage_section_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `homepage_sections`
--

DROP TABLE IF EXISTS `homepage_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `homepage_sections` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `section_key` varchar(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `subtitle` varchar(300) DEFAULT NULL,
  `section_type` enum('featured','bestseller','new_arrival','flash_sale','offer','custom') NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `max_items` int(11) NOT NULL DEFAULT 8,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `section_key` (`section_key`),
  KEY `idx_section_key` (`section_key`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `homepage_sections`
--

LOCK TABLES `homepage_sections` WRITE;
/*!40000 ALTER TABLE `homepage_sections` DISABLE KEYS */;
INSERT INTO `homepage_sections` VALUES (1,'featured_products','Featured Products','Hand-picked premium selections curated for you','featured',1,8,1,'2026-08-25 01:10:00','2026-08-25 01:10:00'),(2,'flash_sale','Flash Deals & Limited Offers','Hurry! Limited stock at unbeatable discounts','flash_sale',2,4,1,'2026-08-25 01:10:00','2026-08-25 01:10:00'),(3,'best_sellers','Best Selling Products','Top rated and loved by thousands of happy shoppers','bestseller',3,8,1,'2026-08-25 01:10:00','2026-08-25 01:10:00'),(4,'new_arrivals','New Arrivals','Fresh additions just landed in our catalog','new_arrival',4,8,1,'2026-08-25 01:10:00','2026-08-25 01:10:00');
/*!40000 ALTER TABLE `homepage_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offer_categories`
--

DROP TABLE IF EXISTS `offer_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offer_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `offer_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_offer_category` (`offer_id`,`category_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `offer_categories_ibfk_1` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `offer_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offer_categories`
--

LOCK TABLES `offer_categories` WRITE;
/*!40000 ALTER TABLE `offer_categories` DISABLE KEYS */;
INSERT INTO `offer_categories` VALUES (1,1,1),(2,1,2),(3,1,3),(4,2,1),(5,2,5);
/*!40000 ALTER TABLE `offer_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offer_products`
--

DROP TABLE IF EXISTS `offer_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offer_products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `offer_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_offer_product` (`offer_id`,`product_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `offer_products_ibfk_1` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `offer_products_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offer_products`
--

LOCK TABLES `offer_products` WRITE;
/*!40000 ALTER TABLE `offer_products` DISABLE KEYS */;
INSERT INTO `offer_products` VALUES (1,3,1),(2,3,3);
/*!40000 ALTER TABLE `offer_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `offers`
--

DROP TABLE IF EXISTS `offers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(12,2) NOT NULL,
  `min_order_amount` decimal(12,2) DEFAULT NULL,
  `max_discount_amount` decimal(12,2) DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `usage_limit` int(10) unsigned DEFAULT NULL,
  `per_user_limit` int(10) unsigned DEFAULT NULL,
  `times_used` int(10) unsigned NOT NULL DEFAULT 0,
  `is_flash_sale` tinyint(1) NOT NULL DEFAULT 0,
  `flash_stock` int(10) unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_dates` (`start_date`,`end_date`),
  KEY `idx_flash` (`is_flash_sale`),
  CONSTRAINT `offers_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `offers`
--

LOCK TABLES `offers` WRITE;
/*!40000 ALTER TABLE `offers` DISABLE KEYS */;
INSERT INTO `offers` VALUES (1,'Grand Summer Mega Sale','Get 15% OFF across all categories on orders over Óº│2,000','percentage',15.00,2000.00,3000.00,'2026-01-01 00:00:00','2026-12-31 23:59:59',1000,3,12,0,NULL,1,1,'2026-08-25 01:10:00','2026-08-25 01:10:00'),(2,'Midnight Flash Sale','Limited-time instant Óº│500 flat discount on electronics and fitness','fixed',500.00,3500.00,500.00,'2026-01-01 00:00:00','2026-12-31 23:59:59',200,1,8,1,50,1,1,'2026-08-25 01:10:00','2026-08-25 01:10:00'),(3,'Gadget Fest Discount','Special 20% discount on selected audio and computer accessories','percentage',20.00,1500.00,2500.00,'2026-01-01 00:00:00','2026-12-31 23:59:59',500,2,5,0,NULL,1,1,'2026-08-25 01:10:00','2026-08-25 01:10:00');
/*!40000 ALTER TABLE `offers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `product_name` varchar(300) NOT NULL,
  `product_sku` varchar(100) DEFAULT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(12,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,1,'Sony WH-1000XM5 Wireless Noise Canceling Headphones','SONY-WH1000XM5-BLK',1,34990.00,0.00,34990.00,'2026-08-25 01:10:00'),(2,2,5,'Men\'s Premium Slim-Fit Cotton Oxford Shirt','OXF-SHIRT-NVY-L',1,1950.00,0.00,1950.00,'2026-08-25 01:10:00'),(3,2,10,'Hydrating Hyaluronic Acid Serum with Vitamin B5 30ml','SKN-HYA-SERUM-30',1,1490.00,0.00,1490.00,'2026-08-25 01:10:00'),(4,2,15,'Luxury Matte Black Rollerball Pen with Refill Gift Box','STN-PEN-MATTE-GLD',1,1400.00,0.00,1400.00,'2026-08-25 01:10:00'),(5,3,8,'Ergonomic High-Back Mesh Executive Office Chair','CHR-ERGO-MESH-BLK',1,15990.00,0.00,15990.00,'2026-08-25 01:10:00'),(6,4,14,'Atomic Habits by James Clear (Hardcover Edition)','BOK-ATOMIC-HABITS',2,1150.00,0.00,2300.00,'2026-08-25 01:10:00'),(7,5,1,'Sony WH-1000XM5 Wireless Noise Canceling Headphones','SONY-WH1000XM5-BLK',3,32490.00,7500.00,97470.00,'2026-08-25 02:38:01'),(8,5,4,'Samsung 27-inch Odyssey G5 WQHD Curved Gaming Monitor','SAM-ODYSSEY-G5-27',1,32000.00,500.00,32000.00,'2026-08-25 02:38:01'),(9,5,6,'Women\'s Elegant Floral Print Summer Maxi Dress','FLR-MAXI-DRS-M',2,2720.00,960.00,5440.00,'2026-08-25 02:38:01');
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `order_number` varchar(30) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `coupon_discount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shipping_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL,
  `coupon_id` int(10) unsigned DEFAULT NULL,
  `coupon_code` varchar(50) DEFAULT NULL,
  `order_status` enum('pending','processing','shipped','delivered','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `payment_method` enum('cod','online','bkash','nagad','sslcommerz','card') NOT NULL DEFAULT 'cod',
  `shipping_name` varchar(200) NOT NULL,
  `shipping_phone` varchar(20) NOT NULL,
  `shipping_address` varchar(500) NOT NULL,
  `shipping_city` varchar(100) NOT NULL,
  `shipping_area` varchar(100) DEFAULT NULL,
  `shipping_postal` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_order_number` (`order_number`),
  KEY `idx_order_status` (`order_status`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,4,'ORD-20260801-001',34990.00,0.00,1500.00,60.00,33550.00,1,'WELCOME10','delivered','paid','online','Rahim Ahmed','+8801811000004','House 42, Road 11, Block D, Banani','Dhaka','Banani','1213','Please call before delivery','2026-08-01 14:30:00','2026-08-25 01:10:00'),(2,4,'ORD-20260810-002',4690.00,0.00,0.00,60.00,4750.00,NULL,NULL,'shipped','paid','bkash','Rahim Ahmed','+8801811000004','House 42, Road 11, Block D, Banani','Dhaka','Banani','1213',NULL,'2026-08-10 11:15:00','2026-08-25 01:10:00'),(3,5,'ORD-20260815-003',15990.00,0.00,500.00,120.00,15610.00,2,'SAVE500','processing','paid','sslcommerz','Karim Uddin','+8801911000005','Plot 15, Sector 7, Uttara','Dhaka','Uttara','1230','Deliver after 5 PM','2026-08-15 16:45:00','2026-08-25 01:10:00'),(4,5,'ORD-20260820-004',2800.00,0.00,0.00,60.00,2860.00,NULL,NULL,'pending','pending','cod','Karim Uddin','+8801911000005','Plot 15, Sector 7, Uttara','Dhaka','Uttara','1230',NULL,'2026-08-20 09:20:00','2026-08-25 01:10:00'),(5,4,'ORD-20260824-2B145A',134910.00,8960.00,0.00,0.00,134910.00,NULL,NULL,'pending','pending','cod','Rahim Ahmed','+8801811000004','House 42, Road 11, Block D, Banani','Dhaka','Banani','1213',NULL,'2026-08-25 02:38:01','2026-08-25 02:38:01');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_token` (`token_hash`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'BDT',
  `status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `gateway_response` text DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_transaction_id` (`transaction_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,1,'online','TXN-ONLINE-987654321',33550.00,'BDT','completed','{\"status\":\"SUCCESS\",\"card_type\":\"VISA-CREDIT\"}','2026-08-01 14:32:10','2026-08-25 01:10:00','2026-08-25 01:10:00'),(2,2,'bkash','BKASH-8A9F439D',4750.00,'BDT','completed','{\"status\":\"SUCCESS\",\"wallet\":\"01811000004\"}','2026-08-10 11:16:30','2026-08-25 01:10:00','2026-08-25 01:10:00'),(3,3,'sslcommerz','SSLCZ-20260815-7788',15610.00,'BDT','completed','{\"status\":\"VALIDATED\",\"bank_tran_id\":\"BTR8899\"}','2026-08-15 16:47:05','2026-08-25 01:10:00','2026-08-25 01:10:00'),(4,4,'cod',NULL,2860.00,'BDT','pending',NULL,NULL,'2026-08-25 01:10:00','2026-08-25 01:10:00'),(5,5,'cod',NULL,134910.00,'BDT','pending',NULL,NULL,'2026-08-25 02:38:01','2026-08-25 02:38:01');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_product_id` (`product_id`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_images`
--

LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;
INSERT INTO `product_images` VALUES (1,1,'sony-xm5-1.jpg','Sony WH-1000XM5 Front View',1,1,'2026-08-25 01:10:00'),(2,1,'sony-xm5-2.jpg','Sony WH-1000XM5 Folded in Case',2,0,'2026-08-25 01:10:00'),(3,2,'macbook-air-m2-1.jpg','MacBook Air M2 Midnight',1,1,'2026-08-25 01:10:00'),(4,2,'macbook-air-m2-2.jpg','MacBook Air M2 Side Profile',2,0,'2026-08-25 01:10:00'),(5,3,'logitech-mx3s-1.jpg','Logitech MX Master 3S Grey',1,1,'2026-08-25 01:10:00'),(6,4,'samsung-g5-1.jpg','Samsung Odyssey G5 Curved Monitor',1,1,'2026-08-25 01:10:00'),(7,5,'oxford-shirt-1.jpg','Men Navy Oxford Shirt Front',1,1,'2026-08-25 01:10:00'),(8,6,'floral-maxi-1.jpg','Women Floral Maxi Dress',1,1,'2026-08-25 01:10:00'),(9,7,'leather-wallet-1.jpg','Men Leather Bifold Wallet',1,1,'2026-08-25 01:10:00'),(10,8,'ergo-chair-1.jpg','Ergonomic Mesh Chair Black',1,1,'2026-08-25 01:10:00'),(11,9,'desk-lamp-1.jpg','Smart LED Desk Lamp with Charger',1,1,'2026-08-25 01:10:00'),(12,10,'serum-hyaluronic-1.jpg','Hyaluronic Acid Serum 30ml',1,1,'2026-08-25 01:10:00'),(13,11,'argan-oil-1.jpg','Organic Moroccan Argan Oil',1,1,'2026-08-25 01:10:00'),(14,12,'dumbbell-set-1.jpg','20KG Cast Iron Dumbbell Set',1,1,'2026-08-25 01:10:00'),(15,13,'yoga-mat-1.jpg','Pro Non-Slip TPE Yoga Mat',1,1,'2026-08-25 01:10:00'),(16,14,'atomic-habits-1.jpg','Atomic Habits Hardcover Book',1,1,'2026-08-25 01:10:00'),(17,15,'luxury-pen-1.jpg','Matte Black Rollerball Pen Gift Box',1,1,'2026-08-25 01:10:00');
/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(300) NOT NULL,
  `slug` varchar(300) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `brand` varchar(150) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `sale_price` decimal(12,2) DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `low_stock_threshold` int(11) NOT NULL DEFAULT 5,
  `weight` decimal(8,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `view_count` int(10) unsigned NOT NULL DEFAULT 0,
  `total_sold` int(10) unsigned NOT NULL DEFAULT 0,
  `avg_rating` decimal(3,2) NOT NULL DEFAULT 0.00,
  `review_count` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `sku` (`sku`),
  KEY `idx_category` (`category_id`),
  KEY `idx_slug` (`slug`),
  KEY `idx_sku` (`sku`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_is_featured` (`is_featured`),
  KEY `idx_price` (`price`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_total_sold` (`total_sold`),
  KEY `idx_avg_rating` (`avg_rating`),
  FULLTEXT KEY `idx_search` (`name`,`description`,`brand`,`sku`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,'Sony WH-1000XM5 Wireless Noise Canceling Headphones','sony-wh-1000xm5-wireless-noise-canceling-headphones','Industry-leading noise cancellation with two processors and 8 microphones. Magnificent audio quality engineered with the Integrated Processor V1. Crystal clear hands-free calling with 4 beamforming microphones.','Premium noise-canceling wireless over-ear headphones with 30-hour battery life.','SONY-WH1000XM5-BLK','Sony',38500.00,34990.00,22,5,0.25,1,1,347,17,4.80,5,'2026-08-25 01:10:00','2026-08-27 23:03:49'),(2,1,'Apple MacBook Air 13-inch M2 Chip 256GB SSD','apple-macbook-air-13-inch-m2-chip-256gb-ssd','Strikingly thin design with all-day battery life. Supercharged by the next-generation M2 chip, delivering incredible speed and power efficiency. 13.6-inch Liquid Retina display with True Tone.','13.6-inch Liquid Retina display, 8GB Unified Memory, 256GB SSD storage, Midnight color.','APL-MBA-M2-256','Apple',125000.00,118500.00,12,3,1.24,1,1,542,8,4.90,4,'2026-08-25 01:10:00','2026-08-25 22:34:47'),(3,1,'Logitech MX Master 3S Wireless Performance Mouse','logitech-mx-master-3s-wireless-performance-mouse','Quiet Clicks feel satisfying and make 90% less noise. 8K DPI any-surface tracking, including glass. MagSpeed electromagnetic scrolling is 90% faster and 87% more precise.','Ergonomic wireless mouse with 8K DPI sensor and ultra-fast electromagnetic scrolling.','LOGI-MXM3S-GRY','Logitech',11500.00,9990.00,45,10,0.14,1,1,210,22,4.75,3,'2026-08-25 01:10:00','2026-08-25 01:10:00'),(4,1,'Samsung 27-inch Odyssey G5 WQHD Curved Gaming Monitor','samsung-27-inch-odyssey-g5-wqhd-curved-gaming-monitor','1000R curve matches the human eye for maximum immersion. WQHD resolution packs in 1.7 times the pixel density of Full HD. 144Hz refresh rate and 1ms response time.','27\" WQHD 144Hz 1ms 1000R Curved Gaming Monitor with HDR10 and AMD FreeSync Premium.','SAM-ODYSSEY-G5-27','Samsung',36000.00,32500.00,17,4,4.50,1,0,180,7,4.60,2,'2026-08-25 01:10:00','2026-08-25 02:38:01'),(5,2,'Men\'s Premium Slim-Fit Cotton Oxford Shirt','mens-premium-slim-fit-cotton-oxford-shirt','Crafted from 100% long-staple combed cotton for superior comfort and breathability. Features a button-down collar, chest pocket, and durable pearl buttons. Tailored slim fit suitable for formal and smart-casual occasions.','100% combed cotton classic button-down Oxford shirt in Navy Blue.','OXF-SHIRT-NVY-L','Heritage Club',2450.00,1950.00,60,10,0.30,1,1,410,35,4.50,6,'2026-08-25 01:10:00','2026-08-25 01:10:00'),(6,2,'Women\'s Elegant Floral Print Summer Maxi Dress','womens-elegant-floral-print-summer-maxi-dress','Flowing silhouette with an adjustable waist tie and breathable chiffon fabric. Features vibrant floral prints and a tiered skirt design. Perfect for festive celebrations, dinners, and vacations.','Lightweight chiffon floral maxi dress with adjustable waist and ruffled hem.','FLR-MAXI-DRS-M','Luxe Aura',3800.00,3200.00,28,5,0.40,1,1,290,20,4.70,3,'2026-08-25 01:10:00','2026-08-25 02:38:01'),(7,2,'Men\'s Genuine Leather Bifold Wallet with RFID Blocking','mens-genuine-leather-bifold-wallet-rfid','Handcrafted from full-grain vegetable-tanned leather. Includes 8 card slots, 2 currency compartments, and an ID window. Embedded with RFID-blocking technology to protect your digital identity.','Full-grain leather bifold wallet with 8 card slots and advanced RFID protection.','WAL-LEA-RFID-BRN','UrbanHide',1650.00,1350.00,80,15,0.12,1,0,151,28,4.65,4,'2026-08-25 01:10:00','2026-08-25 02:45:34'),(8,3,'Ergonomic High-Back Mesh Executive Office Chair','ergonomic-high-back-mesh-executive-office-chair','Engineered for all-day comfort with dynamic lumbar support, 3D adjustable armrests, breathable mesh back, and 135-degree recline mechanism. Heavy-duty aluminum base supports up to 150kg.','Breathable high-back ergonomic chair with 3D armrests and adjustable lumbar cushion.','CHR-ERGO-MESH-BLK','ErgoPro',18500.00,15990.00,14,3,14.50,1,1,380,12,4.85,4,'2026-08-25 01:10:00','2026-08-25 01:10:00'),(9,3,'Smart LED Ambient Desk Lamp with Wireless Charging','smart-led-ambient-desk-lamp-wireless-charging','Features customizable color temperatures (2700K - 6500K), touch dimmer controls, memory function, and an integrated 15W Qi fast wireless charging pad at the base.','Touch control LED desk lamp with 5 color modes, timer, and 15W wireless charger.','LMP-LED-QI-WHT','Lumex',4200.00,3450.00,40,8,0.85,1,0,120,15,4.40,2,'2026-08-25 01:10:00','2026-08-25 01:10:00'),(10,4,'Hydrating Hyaluronic Acid Serum with Vitamin B5 30ml','hydrating-hyaluronic-acid-serum-vitamin-b5-30ml','Formulated with multi-molecular weight hyaluronic acid to penetrate multiple skin layers. Delivers deep, long-lasting hydration, smooths fine lines, and restores skin suppleness.','Intensive moisturizing serum with pure hyaluronic acid and Provitamin B5.','SKN-HYA-SERUM-30','DermaPure',1850.00,1490.00,95,15,0.08,1,1,510,62,4.90,8,'2026-08-25 01:10:00','2026-08-25 01:10:00'),(11,4,'Organic Cold-Pressed Moroccan Argan Oil 100ml','organic-cold-pressed-moroccan-argan-oil-100ml','100% pure, unrefined USDA certified organic argan oil. Rich in essential fatty acids and Vitamin E to deeply nourish hair, skin, and nails without feeling greasy.','100% pure organic argan oil for radiant hair shine and intense skin hydration.','OIL-ARGAN-100ML','NatureGlow',2200.00,1850.00,50,10,0.15,1,0,177,20,4.60,3,'2026-08-25 01:10:00','2026-08-25 01:12:20'),(12,5,'Adjustable Cast Iron Dumbbell Set 20KG with Connector','adjustable-cast-iron-dumbbell-set-20kg','Complete home gym set including 12 cast iron weight plates, 2 textured chrome handles, star-lock collars, and an extension bar that converts dumbbells into a barbell.','20KG total weight dumbbell & barbell combo set with anti-slip grip and carrying case.','FIT-DUMBBELL-20KG','PowerFit',6500.00,5490.00,20,4,20.00,1,1,260,16,4.70,5,'2026-08-25 01:10:00','2026-08-25 01:10:00'),(13,5,'Pro Non-Slip Eco-Friendly TPE Yoga Mat 6mm','pro-non-slip-eco-friendly-tpe-yoga-mat-6mm','High-density dual-layer textured design provides unmatched grip on wood, tile, or cement floors. Includes alignment lines and a convenient carrying strap.','6mm dual-layer eco-friendly TPE workout mat with laser alignment guidelines.','FIT-YOGA-MAT-6MM','ZenActive',2100.00,1650.00,35,8,0.90,1,0,140,19,4.55,3,'2026-08-25 01:10:00','2026-08-25 01:10:00'),(14,6,'Atomic Habits by James Clear (Hardcover Edition)','atomic-habits-james-clear-hardcover','An easy and proven way to build good habits and break bad ones. Over 10 million copies sold worldwide. Learn practical strategies to form good habits, break bad ones, and master tiny behaviors.','The definitive guide to habit formation and personal growth by James Clear.','BOK-ATOMIC-HABITS','Penguin Random House',1450.00,1150.00,120,20,0.45,1,1,620,85,4.95,12,'2026-08-25 01:10:00','2026-08-25 01:10:00'),(15,6,'Luxury Matte Black Rollerball Pen with Refill Gift Box','luxury-matte-black-rollerball-pen-gift-box','Precision brass body with a sleek matte black finish and gold trim accents. Smooth 0.5mm Swiss ink cartridge delivers an effortless writing experience.','Executive matte black metal pen with gold trims in a velvet-lined presentation gift box.','STN-PEN-MATTE-GLD','Monarch',1800.00,1400.00,65,12,0.20,1,0,110,14,4.60,2,'2026-08-25 01:10:00','2026-08-25 01:10:00');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reviews` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `order_id` int(10) unsigned DEFAULT NULL,
  `rating` tinyint(3) unsigned NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `status` enum('pending','approved','hidden','deleted') NOT NULL DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_product_order` (`user_id`,`product_id`,`order_id`),
  KEY `order_id` (`order_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_status` (`status`),
  KEY `idx_rating` (`rating`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (1,4,1,1,5,'Absolutely blown away by the active noise cancellation! Battery life easily lasts multiple days of heavy listening during office hours. Best investment in headphones so far.','approved',NULL,'2026-08-05 18:20:00','2026-08-25 01:10:00'),(2,4,5,2,5,'Exceptional build quality and pure cotton feel. Fits perfectly on shoulders and collar remains crisp after washing.','approved',NULL,'2026-08-14 10:15:00','2026-08-25 01:10:00'),(3,5,8,3,5,'Transformed my work from home posture! The lumbar support adjusts exactly where needed and the mesh keeps cool during long sessions.','approved',NULL,'2026-08-18 19:40:00','2026-08-25 01:10:00'),(4,4,14,NULL,5,'One of the most practical life-changing books on habit building and behavior change. Delivered promptly and in pristine hardcover condition.','approved',NULL,'2026-08-21 12:00:00','2026-08-25 01:10:00');
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` char(128) NOT NULL,
  `payload` mediumtext NOT NULL,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) NOT NULL DEFAULT 'general',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_key` (`setting_key`),
  KEY `idx_group` (`setting_group`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'site_name','NovaMart','general','2026-08-25 23:50:19'),(2,'site_tagline','Online shopping in Bangladesh','general','2026-08-25 23:50:19'),(3,'site_email','support@novamart.com','general','2026-08-25 01:10:00'),(4,'site_phone','+880 9612-000000','general','2026-08-25 01:10:00'),(5,'site_address','Gulshan 2, Dhaka 1212, Bangladesh','general','2026-08-25 23:50:19'),(6,'currency_symbol','Óº│','localization','2026-08-25 01:10:00'),(7,'currency_code','BDT','localization','2026-08-25 01:10:00'),(8,'shipping_inside_city','60.00','shipping','2026-08-25 01:10:00'),(9,'shipping_outside_city','120.00','shipping','2026-08-25 01:10:00'),(10,'free_shipping_threshold','5000.00','shipping','2026-08-25 01:10:00'),(11,'tax_percentage','0.00','finance','2026-08-25 01:10:00'),(12,'maintenance_mode','0','system','2026-08-25 01:10:00');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('customer','staff','admin','owner') NOT NULL DEFAULT 'customer',
  `avatar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Super','Owner','owner@example.com','+8801711000001','$2y$10$qpnBsruJAfYh74wnuPh5FOcsoTmDoN1rXcZsTZ47S4VuosGTZOgvq','owner','avatar-owner.jpg',1,1,NULL,'2026-08-25 01:10:00','2026-08-25 01:53:52'),(2,'Site','Admin','admin@example.com','+8801711000002','$2y$10$qpnBsruJAfYh74wnuPh5FOcsoTmDoN1rXcZsTZ47S4VuosGTZOgvq','admin','avatar-admin.jpg',1,1,'2026-08-24 23:16:35','2026-08-25 01:10:00','2026-08-25 03:16:35'),(3,'Support','Staff','staff@example.com','+8801711000003','$2y$10$qpnBsruJAfYh74wnuPh5FOcsoTmDoN1rXcZsTZ47S4VuosGTZOgvq','staff',NULL,1,1,NULL,'2026-08-25 01:10:00','2026-08-25 01:53:52'),(4,'Rahim','Ahmed','customer@example.com','+8801811000004','$2y$10$qpnBsruJAfYh74wnuPh5FOcsoTmDoN1rXcZsTZ47S4VuosGTZOgvq','customer',NULL,1,1,'2026-08-25 19:53:15','2026-08-25 01:10:00','2026-08-25 23:53:15'),(5,'Karim','Uddin','karim@example.com','+8801911000005','$2y$10$qpnBsruJAfYh74wnuPh5FOcsoTmDoN1rXcZsTZ47S4VuosGTZOgvq','customer',NULL,1,1,NULL,'2026-08-25 01:10:00','2026-08-25 01:53:52');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wishlist`
--

DROP TABLE IF EXISTS `wishlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wishlist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wishlist`
--

LOCK TABLES `wishlist` WRITE;
/*!40000 ALTER TABLE `wishlist` DISABLE KEYS */;
INSERT INTO `wishlist` VALUES (1,4,'2026-08-25 02:18:42');
/*!40000 ALTER TABLE `wishlist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wishlist_items`
--

DROP TABLE IF EXISTS `wishlist_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wishlist_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `wishlist_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_wishlist_product` (`wishlist_id`,`product_id`),
  KEY `product_id` (`product_id`),
  KEY `idx_wishlist_id` (`wishlist_id`),
  CONSTRAINT `wishlist_items_ibfk_1` FOREIGN KEY (`wishlist_id`) REFERENCES `wishlist` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlist_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wishlist_items`
--

LOCK TABLES `wishlist_items` WRITE;
/*!40000 ALTER TABLE `wishlist_items` DISABLE KEYS */;
INSERT INTO `wishlist_items` VALUES (1,1,1,'2026-08-25 02:41:25');
/*!40000 ALTER TABLE `wishlist_items` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-27 23:09:48
