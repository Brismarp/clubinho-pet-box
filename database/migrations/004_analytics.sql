-- Analytics first-party + leads (Clubinho Pet Box)
CREATE TABLE IF NOT EXISTS `analytics_events` (
    `id` BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `visitor_id` VARCHAR(64) NOT NULL,
    `session_id` VARCHAR(64) NOT NULL,
    `event_name` VARCHAR(64) NOT NULL,
    `path` VARCHAR(500) NULL,
    `plan_slug` VARCHAR(100) NULL,
    `email` VARCHAR(255) NULL,
    `properties` JSON NULL,
    `referrer` VARCHAR(1000) NULL,
    `utm_source` VARCHAR(255) NULL,
    `utm_medium` VARCHAR(255) NULL,
    `utm_campaign` VARCHAR(255) NULL,
    `ip_hash` VARCHAR(64) NULL,
    `user_agent` VARCHAR(500) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ae_created` (`created_at`),
    INDEX `idx_ae_event_created` (`event_name`, `created_at`),
    INDEX `idx_ae_visitor` (`visitor_id`),
    INDEX `idx_ae_session` (`session_id`),
    INDEX `idx_ae_path` (`path`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `leads` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(50) NOT NULL,
    `pet_type` VARCHAR(50) NOT NULL,
    `city` VARCHAR(255) NULL,
    `visitor_id` VARCHAR(64) NULL,
    `accept_newsletter` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_leads_created` (`created_at`),
    INDEX `idx_leads_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
