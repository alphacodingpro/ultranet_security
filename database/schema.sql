-- ============================================================
--  UltraNet Security – Database Schema
--  Import this file in phpMyAdmin / MySQL to create the database
-- ============================================================

CREATE DATABASE IF NOT EXISTS `ultranet_security`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `ultranet_security`;

-- ------------------------------------------------------------
-- Table: categories
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`        VARCHAR(120)  NOT NULL,
  `slug`        VARCHAR(140)  NOT NULL UNIQUE,
  `description` VARCHAR(500)  DEFAULT NULL,
  `icon`        VARCHAR(60)   DEFAULT 'fa-solid fa-camera',
  `created_at`  DATETIME      DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: products
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id`   INT UNSIGNED NOT NULL,
  `product_type`  ENUM('camera','nvr','dvr','hdd','poe_switch','cable','other') NOT NULL DEFAULT 'other'
                  COMMENT 'Used by the CCTV Calculator to pick the right product',
  `channels`         SMALLINT UNSIGNED DEFAULT NULL COMMENT 'NVR/DVR: number of channels',
  `storage_gb`       INT UNSIGNED DEFAULT NULL COMMENT 'HDD: capacity in GB (1TB=1000)',
  `poe_ports`        SMALLINT UNSIGNED DEFAULT NULL COMMENT 'PoE Switch: number of PoE ports',
  `poe_budget_watts` SMALLINT UNSIGNED DEFAULT NULL COMMENT 'PoE Switch: total wattage budget',
  `camera_watts`     DECIMAL(5,2) DEFAULT NULL COMMENT 'Camera: power draw in watts',
  `price_per_meter`  DECIMAL(10,2) DEFAULT NULL COMMENT 'Cable: price per metre',
  `name`          VARCHAR(180)  NOT NULL,
  `slug`          VARCHAR(200)  NOT NULL UNIQUE,
  `brand`         VARCHAR(80)   DEFAULT NULL,
  `sku`           VARCHAR(60)   DEFAULT NULL,
  `short_desc`    VARCHAR(300)  DEFAULT NULL,
  `description`   TEXT          DEFAULT NULL,
  `price`         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `old_price`     DECIMAL(10,2) DEFAULT NULL,
  `image`         VARCHAR(255)  DEFAULT NULL,
  `video_url`     VARCHAR(500)  DEFAULT NULL COMMENT 'YouTube or other video URL',
  `stock_status`  ENUM('in_stock','out_of_stock','on_order') NOT NULL DEFAULT 'in_stock',
  `featured`      TINYINT(1)    NOT NULL DEFAULT 0,
  `status`        ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `meta_title`       VARCHAR(180) DEFAULT NULL,
  `meta_description` VARCHAR(300) DEFAULT NULL,
  `created_at`    DATETIME      DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME      DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE,
  INDEX (`brand`),
  INDEX (`slug`),
  FULLTEXT KEY `ft_search` (`name`, `brand`, `short_desc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: product_images  (extra gallery images per product)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_images` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `image`      VARCHAR(255) NOT NULL,
  `sort_order` SMALLINT UNSIGNED DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  INDEX (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: settings  (key/value config used by the Calculator)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key`   VARCHAR(80) PRIMARY KEY,
  `setting_value` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`setting_key`,`setting_value`) VALUES
  ('package_discount_percent', '5'),
  ('poe_headroom_percent', '20'),
  ('default_camera_watts', '9');

-- ------------------------------------------------------------
-- Table: calculator_requests  (client leads from the calculator)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `calculator_requests` (
  `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ref_code`        VARCHAR(20)  NOT NULL UNIQUE,
  `client_name`     VARCHAR(120) NOT NULL,
  `client_phone`    VARCHAR(30)  NOT NULL,
  `client_email`    VARCHAR(150) DEFAULT NULL,
  `system_type`     ENUM('ip','analog') NOT NULL,
  `camera_count`    SMALLINT UNSIGNED NOT NULL,
  `resolution_label`VARCHAR(40)  DEFAULT NULL,
  `lens_note`       VARCHAR(120) DEFAULT NULL,
  `recording_days`  SMALLINT UNSIGNED DEFAULT NULL,
  `recording_mode`  ENUM('continuous','motion') DEFAULT 'continuous',
  `cable_length_m`  INT UNSIGNED DEFAULT NULL,
  `required_hdd_gb` INT UNSIGNED DEFAULT NULL,
  `required_poe_watts` INT UNSIGNED DEFAULT NULL,
  `items_json`      TEXT NOT NULL COMMENT 'JSON of selected products, qty, price',
  `subtotal`        DECIMAL(12,2) NOT NULL DEFAULT 0,
  `discount_percent`DECIMAL(5,2)  NOT NULL DEFAULT 0,
  `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `grand_total`     DECIMAL(12,2) NOT NULL DEFAULT 0,
  `status`          ENUM('new','contacted','closed') NOT NULL DEFAULT 'new',
  `created_at`      DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: contact_messages  (homepage contact form leads)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(120) NOT NULL,
  `phone`      VARCHAR(30)  NOT NULL,
  `email`      VARCHAR(150) DEFAULT NULL,
  `service`    VARCHAR(120) DEFAULT NULL,
  `area`       VARCHAR(120) DEFAULT NULL,
  `cameras`    VARCHAR(60)  DEFAULT NULL,
  `message`    TEXT         DEFAULT NULL,
  `source`     VARCHAR(40)  DEFAULT 'homepage_contact_form',
  `status`     ENUM('new','read','replied') NOT NULL DEFAULT 'new',
  `email_sent` TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Table: admins  (admin panel login accounts)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username`   VARCHAR(60)  NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL,   -- bcrypt hash
  `full_name`  VARCHAR(120) DEFAULT NULL,
  `last_login` DATETIME     DEFAULT NULL,
  `created_at` DATETIME     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Seed: Categories
-- ------------------------------------------------------------
INSERT INTO `categories` (`name`, `slug`, `description`, `icon`) VALUES
('CCTV Cameras',     'cctv-cameras',     'Dome, bullet, PTZ and turret CCTV cameras',      'fa-solid fa-camera'),
('NVR',              'nvr',              'Network Video Recorders for IP camera systems',  'fa-solid fa-server'),
('DVR',              'dvr',              'Digital Video Recorders for analog CCTV systems','fa-solid fa-hard-drive'),
('Accessories',      'accessories',      'Cables, power supplies, hard disks and mounts',  'fa-solid fa-toolbox');

-- ------------------------------------------------------------
-- Seed: Products (sample data so the site is not empty)
-- ------------------------------------------------------------
INSERT INTO `products`
  (`category_id`,`name`,`slug`,`brand`,`sku`,`short_desc`,`description`,`price`,`old_price`,`image`,`stock_status`,`featured`,`status`)
VALUES
(1,'Hikvision 2MP Dome Camera','hikvision-2mp-dome-camera','Hikvision','HIK-DOME-2MP',
 '2MP Full HD dome camera with 20m night vision',
 'The Hikvision 2MP Dome Camera delivers crystal clear Full HD video with excellent low-light performance. Ideal for indoor and covered outdoor areas such as reception halls, corridors and shop entrances. Features smart IR up to 20 metres, weatherproof housing and easy DVR/NVR integration.',
 4500.00, 5200.00, 'prod-cam1.jpg', 'in_stock', 1, 'active'),

(1,'Dahua 4MP Bullet Camera','dahua-4mp-bullet-camera','Dahua','DAH-BULLET-4MP',
 '4MP outdoor bullet camera, 30m IR night vision',
 'Dahua 4MP Bullet Camera is built for outdoor durability with an IP67 weatherproof rating. Its long-range infrared reaches up to 30 metres in total darkness, making it perfect for gates, parking areas, and building perimeters.',
 6200.00, NULL, 'prod-cam2.jpg', 'in_stock', 1, 'active'),

(1,'Hikvision 4MP PTZ Camera','hikvision-4mp-ptz-camera','Hikvision','HIK-PTZ-4MP',
 '25x optical zoom PTZ camera with auto-tracking',
 'This PTZ camera provides 25x optical zoom and 360-degree pan with intelligent auto-tracking, perfect for large open areas like warehouses, plazas and parking lots that need active monitoring.',
 28500.00, 32000.00, 'prod-cam3.jpg', 'in_stock', 1, 'active'),

(2,'Hikvision 8-Channel NVR','hikvision-8-channel-nvr','Hikvision','HIK-NVR-8CH',
 '8 channel NVR supports up to 8MP IP cameras',
 'A reliable 8-channel Network Video Recorder supporting resolutions up to 8MP per channel. Includes 1 SATA bay for HDD, mobile app remote viewing, and motion-triggered alerts.',
 15500.00, NULL, 'prod-nvr1.jpg', 'in_stock', 0, 'active'),

(2,'Dahua 16-Channel NVR','dahua-16-channel-nvr','Dahua','DAH-NVR-16CH',
 '16 channel NVR with 2 SATA bays, supports 4K',
 'Designed for medium to large installations, this 16-channel NVR supports 4K playback, 2 hard-disk bays for extended storage, and centralized remote monitoring via the DMSS app.',
 32000.00, 36500.00, 'prod-nvr2.jpg', 'in_stock', 0, 'active'),

(3,'Hikvision 4-Channel DVR','hikvision-4-channel-dvr','Hikvision','HIK-DVR-4CH',
 'Turbo HD 4 channel DVR for analog cameras',
 'Entry-level 4-channel Turbo HD DVR ideal for small homes and shops. Supports up to 5MP analog cameras, has mobile app viewing via Hik-Connect, and 1 SATA bay for local recording.',
 8500.00, NULL, 'prod-dvr1.jpg', 'in_stock', 1, 'active'),

(3,'Dahua 8-Channel DVR','dahua-8-channel-dvr','Dahua','DAH-DVR-8CH',
 '8 channel Penta-brid DVR, supports up to 4 audio inputs',
 'Penta-brid technology lets this DVR work with HDCVI, AHD, TVI, CVBS and IP cameras all on the same unit — a flexible option for mixed camera environments.',
 13800.00, 15200.00, 'prod-dvr2.jpg', 'in_stock', 0, 'active'),

(4,'Seagate SkyHawk 1TB Surveillance HDD','seagate-skyhawk-1tb-surveillance-hdd','Seagate','SG-SKYHAWK-1TB',
 '1TB hard disk built specifically for CCTV recording',
 'Purpose-built for 24/7 surveillance recording workloads, the SkyHawk 1TB drive offers reliable continuous read/write performance and is rated for use in NVR/DVR systems.',
 9500.00, NULL, 'prod-acc1.jpg', 'in_stock', 0, 'active'),

(4,'12V 5A CCTV Power Supply (8 Way)','12v-5a-cctv-power-supply-8-way','Generic','PWR-12V5A-8W',
 '8-way distributed power supply box for CCTV cameras',
 'A dependable 8-channel power distribution box supplying 12V DC to multiple cameras from a single source. Includes fuse protection per channel.',
 2200.00, 2600.00, 'prod-acc2.jpg', 'in_stock', 0, 'active');

-- ------------------------------------------------------------
-- Seed: Tag sample products for Calculator + add PoE switch/cable
-- ------------------------------------------------------------
UPDATE `products` SET product_type='camera', camera_watts=7.5  WHERE slug='hikvision-2mp-dome-camera';
UPDATE `products` SET product_type='camera', camera_watts=9.0  WHERE slug='dahua-4mp-bullet-camera';
UPDATE `products` SET product_type='camera', camera_watts=30.0 WHERE slug='hikvision-4mp-ptz-camera';
UPDATE `products` SET product_type='nvr', channels=8  WHERE slug='hikvision-8-channel-nvr';
UPDATE `products` SET product_type='nvr', channels=16 WHERE slug='dahua-16-channel-nvr';
UPDATE `products` SET product_type='dvr', channels=4  WHERE slug='hikvision-4-channel-dvr';
UPDATE `products` SET product_type='dvr', channels=8  WHERE slug='dahua-8-channel-dvr';
UPDATE `products` SET product_type='hdd', storage_gb=1000 WHERE slug='seagate-skyhawk-1tb-surveillance-hdd';

INSERT INTO `products`
  (`category_id`,`product_type`,`name`,`slug`,`brand`,`sku`,`short_desc`,`description`,`price`,`image`,`stock_status`,`featured`,`status`,`poe_ports`,`poe_budget_watts`)
SELECT id, 'poe_switch', '8-Port PoE Switch 120W', '8-port-poe-switch-120w', 'Generic', 'POE-SW-8P120W',
       '8 port PoE switch, 120W total budget',
       '8-port unmanaged PoE switch designed for CCTV installations. Total PoE power budget of 120W.',
       12500.00, 'no-image.jpg', 'in_stock', 0, 'active', 8, 120
FROM categories WHERE slug='accessories' LIMIT 1;

INSERT INTO `products`
  (`category_id`,`product_type`,`name`,`slug`,`brand`,`sku`,`short_desc`,`description`,`price`,`image`,`stock_status`,`featured`,`status`,`poe_ports`,`poe_budget_watts`)
SELECT id, 'poe_switch', '16-Port PoE Switch 250W', '16-port-poe-switch-250w', 'Generic', 'POE-SW-16P250W',
       '16 port PoE switch, 250W total budget',
       '16-port unmanaged PoE switch for medium-sized CCTV installations. Total PoE power budget of 250W.',
       24500.00, 'no-image.jpg', 'in_stock', 0, 'active', 16, 250
FROM categories WHERE slug='accessories' LIMIT 1;

INSERT INTO `products`
  (`category_id`,`product_type`,`name`,`slug`,`brand`,`sku`,`short_desc`,`description`,`price`,`image`,`stock_status`,`featured`,`status`,`price_per_meter`)
SELECT id, 'cable', 'CAT6 Outdoor CCTV Cable (per meter)', 'cat6-outdoor-cctv-cable-per-meter', 'Generic', 'CAB-CAT6-OUT',
       'Outdoor rated CAT6 cable for PoE camera runs, priced per metre',
       'UV and weather resistant CAT6 cable suitable for outdoor CCTV camera runs. Supports PoE power delivery up to 100m per standard run.',
       35.00, 'no-image.jpg', 'in_stock', 0, 'active', 35.00
FROM categories WHERE slug='accessories' LIMIT 1;

-- ------------------------------------------------------------
-- Seed: Default Admin Account
-- ------------------------------------------------------------
-- NOTE: This is a placeholder row so the `admins` table is never
-- empty. The password hash below corresponds to the password
-- "Admin@123" — CHANGE THIS after first login, or simply rely on
-- the .env-based login (see README) which does not need this row.
INSERT INTO `admins` (`username`,`password`,`full_name`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Site Administrator');
