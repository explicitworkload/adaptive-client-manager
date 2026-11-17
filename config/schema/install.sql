-- Adaptive Client Manager: database schema for a NEW installation.
--
-- How to use (DirectAdmin):
--   1. Create an empty database and user (DirectAdmin > MySQL Management).
--   2. Open phpMyAdmin, select the new database, open the Import (or SQL) tab
--      and run this file.
--   3. Upload the app, fill in config/app_local.php, and open the site: you will
--      be asked to create the first admin account.
--
-- Creates every table plus default data: the settings row (id 1), the invoice
-- and quote email templates (ids 1 and 2) and a 0% tax rate. No user accounts.
-- Tested on MariaDB 10.11; uses standard MySQL syntax (MySQL 5.7+/8, MariaDB 10.4+).
--
-- Run it on an EMPTY database only: it fails rather than overwrite existing
-- tables. Equivalent to `bin/cake migrations migrate` (config/Migrations);
-- the cake_migrations row records that, so a later migrate is a no-op.

SET NAMES utf8mb4;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

CREATE TABLE `cake_migrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `version` bigint(20) NOT NULL,
  `migration_name` varchar(100) DEFAULT NULL,
  `plugin` varchar(100) DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `version_plugin_unique` (`version`,`plugin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `cake_migrations` VALUES
(1,20261117000001,'InitialSchema',NULL,NULL,NULL,0);
CREATE TABLE `clients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_name` varchar(64) NOT NULL,
  `client_contact` varchar(64) DEFAULT NULL,
  `client_phone` varchar(32) DEFAULT NULL,
  `client_email` varchar(64) DEFAULT NULL,
  `client_address_line_1` varchar(64) DEFAULT NULL,
  `client_address_line_2` varchar(64) DEFAULT NULL,
  `client_town` varchar(64) DEFAULT NULL,
  `client_city` varchar(64) DEFAULT NULL,
  `client_state` varchar(64) DEFAULT NULL,
  `client_zip` varchar(32) DEFAULT NULL,
  `custom_1` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `client_name` (`client_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `email_templates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `content` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `email_templates` VALUES
(1,'Client Invoice','<p><span style="font-size: small;">Hi [CLIENT_CONTACT],</span></p><p><span style="font-size: small;">Please find&nbsp;Invoice Number: [INVOICE_NUMBER] (Total: [INVOICE_TOTAL]) attached to this email.</span></p><p><span style="font-size: small;">If you have any questions then please contact us at [COMPANY_EMAIL].</span></p><p><span style="font-size: small;">&nbsp;</span></p><p><span style="font-size: small;">Regards,</span></p><p>&nbsp;</p><p><span style="font-size: small;">[USER]</span></p><p><span style="font-size: small;">[COMPANY_NAME]</span></p>'),
(2,'Client Quote','<p><span style="font-size: small;">Hi [CLIENT_CONTACT],</span></p><p><span style="font-size: small;">Please find Quote Number: [QUOTE_NUMBER] attached to this email.</span></p><p><span style="font-size: small;">If you have any questions then please contact us at [COMPANY_EMAIL].</span></p><p><span style="font-size: small;">&nbsp;</span></p><p><span style="font-size: small;">Regards,</span></p><p>&nbsp;</p><p><span style="font-size: small;">[USER]</span></p><p><span style="font-size: small;">[COMPANY_NAME]</span></p>');
CREATE TABLE `invoices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(10) unsigned NOT NULL,
  `client_name` varchar(64) DEFAULT NULL,
  `invoice_number` varchar(20) NOT NULL,
  `invoice_date` date NOT NULL,
  `invoice_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'Unpaid',
  `public_notes` text DEFAULT NULL,
  `private_notes` text DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `created_by` varchar(130) DEFAULT NULL,
  `last_modified` datetime DEFAULT NULL,
  `last_mod_by` varchar(130) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `invoice_number` (`invoice_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` int(10) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` int(10) unsigned NOT NULL,
  `client_id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `method` varchar(20) NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `quote_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `quote_id` int(10) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `quote_id` (`quote_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `quotes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(10) unsigned NOT NULL,
  `quote_number` varchar(20) NOT NULL,
  `quote_date` date NOT NULL,
  `expires` date DEFAULT NULL,
  `quote_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'Pending',
  `public_notes` text DEFAULT NULL,
  `private_notes` text DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `created_by` varchar(130) DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `last_mod_by` varchar(130) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `quote_number` (`quote_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_name` varchar(64) NOT NULL DEFAULT 'My Company',
  `company_phone` varchar(32) DEFAULT NULL,
  `company_email` varchar(64) DEFAULT NULL,
  `company_address_line_1` varchar(64) NOT NULL DEFAULT '',
  `company_address_line_2` varchar(64) DEFAULT NULL,
  `company_town` varchar(64) DEFAULT NULL,
  `company_city` varchar(64) DEFAULT NULL,
  `company_state` varchar(64) DEFAULT NULL,
  `company_zip` varchar(32) DEFAULT NULL,
  `custom_1` varchar(255) DEFAULT NULL,
  `payment_info` text DEFAULT NULL,
  `currency` varchar(8) NOT NULL DEFAULT '$',
  `primary_color` varchar(7) NOT NULL DEFAULT '#339966',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` VALUES
(1,'My Company',NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'$','#339966');
CREATE TABLE `taxrates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `taxrates` VALUES
(1,'No Tax',0.00);
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(64) NOT NULL,
  `last_name` varchar(64) NOT NULL,
  `email` varchar(64) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'manager',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
