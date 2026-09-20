-- Run once on an existing UltraNet Security database.
ALTER TABLE `categories`
  ADD COLUMN `featured` TINYINT(1) NOT NULL DEFAULT 0 AFTER `icon`;

-- Keep the original main categories visible when the feature is first enabled.
UPDATE `categories`
SET `featured` = 1
WHERE `slug` IN ('accessories', 'cctv-cameras', 'dvr', 'nvr');
