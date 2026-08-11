-- 009 operational core: CRM fields, box_items, stock ledger, payments, RBAC roles
SET NAMES utf8mb4;

-- Customers CRM fields
ALTER TABLE `customers`
    ADD COLUMN `notes` TEXT NULL AFTER `consent_lgpd_at`,
    ADD COLUMN `city` VARCHAR(120) NULL AFTER `notes`,
    ADD COLUMN `state` VARCHAR(2) NULL AFTER `city`,
    ADD COLUMN `address_line` VARCHAR(255) NULL AFTER `state`;

ALTER TABLE `customers` ADD INDEX `idx_customers_name` (`name`);
ALTER TABLE `customers` ADD INDEX `idx_customers_phone` (`phone`);

-- Products stock min
ALTER TABLE `products` ADD COLUMN `stock_min` INT NOT NULL DEFAULT 0 AFTER `stock_qty`;

-- Boxes link to recommendation
ALTER TABLE `boxes` ADD COLUMN `recommendation_id` INT NULL AFTER `subscription_id`;
ALTER TABLE `boxes` ADD INDEX `idx_boxes_recommendation` (`recommendation_id`);

-- Subscriptions: allow pending stub for fulfill without prior paid sub
ALTER TABLE `subscriptions`
    MODIFY COLUMN `status` ENUM('pending','active','paused','cancelled','expired') DEFAULT 'active';

-- Users roles: widen then normalize
ALTER TABLE `users` MODIFY COLUMN `role` VARCHAR(40) NOT NULL DEFAULT 'atendimento';
UPDATE `users` SET `role` = 'operacao' WHERE `role` = 'editor';
UPDATE `users` SET `role` = 'atendimento' WHERE `role` = 'viewer';
ALTER TABLE `users`
    MODIFY COLUMN `role` ENUM('admin','operacao','montagem','atendimento','estoque') NOT NULL DEFAULT 'atendimento';

CREATE TABLE IF NOT EXISTS `box_items` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `box_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `qty` INT NOT NULL DEFAULT 1,
    `justification` TEXT NULL,
    `source` ENUM('recommendation','manual') NOT NULL DEFAULT 'recommendation',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`box_id`) REFERENCES `boxes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT,
    INDEX `idx_box_items_box` (`box_id`),
    INDEX `idx_box_items_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `stock_movements` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `product_id` INT NOT NULL,
    `delta` INT NOT NULL,
    `balance_after` INT NOT NULL,
    `reason` ENUM('receive','adjust','reserve','ship','release','sale') NOT NULL,
    `ref_type` VARCHAR(40) NULL,
    `ref_id` INT NULL,
    `user_id` INT NULL,
    `note` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_stock_movements_product` (`product_id`, `created_at`),
    INDEX `idx_stock_movements_ref` (`ref_type`, `ref_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `subscription_id` INT NULL,
    `customer_email` VARCHAR(255) NULL,
    `provider` VARCHAR(40) NOT NULL DEFAULT 'kirvano',
    `external_id` VARCHAR(120) NULL,
    `amount_cents` INT NOT NULL DEFAULT 0,
    `currency` CHAR(3) NOT NULL DEFAULT 'BRL',
    `status` ENUM('pending','paid','failed','refunded','cancelled') NOT NULL DEFAULT 'pending',
    `paid_at` DATETIME NULL,
    `payload_json` JSON NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions`(`id`) ON DELETE SET NULL,
    INDEX `idx_payments_status` (`status`, `created_at`),
    INDEX `idx_payments_external` (`provider`, `external_id`),
    INDEX `idx_payments_email` (`customer_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional FK (box_recommendations may exist after 007)
ALTER TABLE `boxes`
    ADD CONSTRAINT `fk_boxes_recommendation`
    FOREIGN KEY (`recommendation_id`) REFERENCES `box_recommendations`(`id`) ON DELETE SET NULL;
