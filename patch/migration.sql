-- ============================================================
--  MIGRATION: Multiple Images + Video URL
--  ONLY run this if you already had the site installed before
--  this update. Fresh installs already have this in schema.sql
-- ============================================================

USE `ultranet_security`;

-- Add video_url column (skip if it already exists)
ALTER TABLE `products`
  ADD COLUMN `video_url` VARCHAR(500) DEFAULT NULL
  COMMENT 'YouTube or other video URL'
  AFTER `image`;

-- Create product_images table (skip if it already exists)
CREATE TABLE IF NOT EXISTS `product_images` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `image`      VARCHAR(255) NOT NULL,
  `sort_order` SMALLINT UNSIGNED DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  INDEX (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
