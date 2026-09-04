-- ============================================================
--  MIGRATION: CCTV System Calculator + Package Builder
--  Run this in phpMyAdmin → SQL tab on ultranet_security DB
-- ============================================================

USE `ultranet_security`;

-- Step 1: Add calculator-related spec columns to products
-- (nullable — does not affect existing products/pages)
ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `product_type`
  ENUM('camera','nvr','dvr','hdd','poe_switch','cable','other') NOT NULL DEFAULT 'other'
  COMMENT 'Used by the CCTV Calculator to pick the right product'
  AFTER `category_id`;

ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `channels`
  SMALLINT UNSIGNED DEFAULT NULL COMMENT 'NVR/DVR: number of channels';

ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `storage_gb`
  INT UNSIGNED DEFAULT NULL COMMENT 'HDD: capacity in GB (1TB=1000)';

ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `poe_ports`
  SMALLINT UNSIGNED DEFAULT NULL COMMENT 'PoE Switch: number of PoE ports';

ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `poe_budget_watts`
  SMALLINT UNSIGNED DEFAULT NULL COMMENT 'PoE Switch: total wattage budget';

ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `camera_watts`
  DECIMAL(5,2) DEFAULT NULL COMMENT 'Camera: power draw in watts (PoE budgeting)';

ALTER TABLE `products` ADD COLUMN IF NOT EXISTS `price_per_meter`
  DECIMAL(10,2) DEFAULT NULL COMMENT 'Cable: price per metre (product_type=cable)';

-- Step 2: Settings table (key/value) for calculator configuration
CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key`   VARCHAR(80) PRIMARY KEY,
  `setting_value` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `settings` (`setting_key`,`setting_value`) VALUES
  ('package_discount_percent', '5'),
  ('poe_headroom_percent', '20'),
  ('default_camera_watts', '9');

-- Step 3: Calculator lead / requests table
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

-- Step 4: Seed a few calculator-ready sample products (safe to skip/edit)
-- Tag existing sample products with product_type so the calculator works out of the box
UPDATE `products` SET product_type='camera', camera_watts=7.5  WHERE slug='hikvision-2mp-dome-camera';
UPDATE `products` SET product_type='camera', camera_watts=9.0  WHERE slug='dahua-4mp-bullet-camera';
UPDATE `products` SET product_type='camera', camera_watts=30.0 WHERE slug='hikvision-4mp-ptz-camera';
UPDATE `products` SET product_type='nvr', channels=8  WHERE slug='hikvision-8-channel-nvr';
UPDATE `products` SET product_type='nvr', channels=16 WHERE slug='dahua-16-channel-nvr';
UPDATE `products` SET product_type='dvr', channels=4  WHERE slug='hikvision-4-channel-dvr';
UPDATE `products` SET product_type='dvr', channels=8  WHERE slug='dahua-8-channel-dvr';
UPDATE `products` SET product_type='hdd', storage_gb=1000 WHERE slug='seagate-skyhawk-1tb-surveillance-hdd';

-- Add a PoE switch + cable sample product if not present (optional — safe to edit later in Admin)
INSERT IGNORE INTO `products`
  (`category_id`,`product_type`,`name`,`slug`,`brand`,`sku`,`short_desc`,`description`,`price`,`image`,`stock_status`,`featured`,`status`,`poe_ports`,`poe_budget_watts`)
SELECT id, 'poe_switch', '8-Port PoE Switch 120W', '8-port-poe-switch-120w', 'Generic', 'POE-SW-8P120W',
       '8 port PoE switch, 120W total budget, supports up to 250m PoE extend mode',
       '8-port unmanaged PoE switch designed for CCTV installations. Total PoE power budget of 120W, supports standard and long-range PoE extend mode up to 250m per port at reduced speed.',
       12500.00, 'no-image.jpg', 'in_stock', 0, 'active', 8, 120
FROM categories WHERE slug='accessories' LIMIT 1;

INSERT IGNORE INTO `products`
  (`category_id`,`product_type`,`name`,`slug`,`brand`,`sku`,`short_desc`,`description`,`price`,`image`,`stock_status`,`featured`,`status`,`poe_ports`,`poe_budget_watts`)
SELECT id, 'poe_switch', '16-Port PoE Switch 250W', '16-port-poe-switch-250w', 'Generic', 'POE-SW-16P250W',
       '16 port PoE switch, 250W total budget',
       '16-port unmanaged PoE switch for medium-sized CCTV installations. Total PoE power budget of 250W.',
       24500.00, 'no-image.jpg', 'in_stock', 0, 'active', 16, 250
FROM categories WHERE slug='accessories' LIMIT 1;

INSERT IGNORE INTO `products`
  (`category_id`,`product_type`,`name`,`slug`,`brand`,`sku`,`short_desc`,`description`,`price`,`image`,`stock_status`,`featured`,`status`,`price_per_meter`)
SELECT id, 'cable', 'CAT6 Outdoor CCTV Cable (per meter)', 'cat6-outdoor-cctv-cable-per-meter', 'Generic', 'CAB-CAT6-OUT',
       'Outdoor rated CAT6 cable for PoE camera runs, priced per metre',
       'UV and weather resistant CAT6 cable suitable for outdoor CCTV camera runs. Supports PoE power delivery up to 100m per standard run.',
       35.00, 'no-image.jpg', 'in_stock', 0, 'active', 35.00
FROM categories WHERE slug='accessories' LIMIT 1;
