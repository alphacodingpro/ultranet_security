-- ============================================================
--  MIGRATION: Contact Form Leads + Admin Inbox
--  Run this in phpMyAdmin → SQL tab on ultranet_security DB
-- ============================================================

USE `ultranet_security`;

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
