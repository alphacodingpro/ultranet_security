-- Run once on an existing UltraNet Security database.
ALTER TABLE `categories`
  ADD COLUMN `parent_id` INT UNSIGNED DEFAULT NULL AFTER `id`,
  ADD COLUMN `brand` VARCHAR(80) DEFAULT NULL AFTER `icon`,
  ADD INDEX `idx_category_parent` (`parent_id`),
  ADD INDEX `idx_category_brand` (`brand`);

-- Put imported Hikvision camera series below CCTV Cameras.
SET @cctv_category_id = (
  SELECT `id` FROM `categories` WHERE `slug` = 'cctv-cameras' LIMIT 1
);

UPDATE `categories`
SET `parent_id` = @cctv_category_id,
    `brand` = 'Hikvision',
    `featured` = 0
WHERE `id` <> @cctv_category_id
  AND (`name` LIKE 'Hikvision %' OR `description` LIKE 'Hikvision network camera category%');
