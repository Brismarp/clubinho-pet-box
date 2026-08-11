-- Integrations mock + outbox + webhook ingest
CREATE TABLE IF NOT EXISTS `webhook_events` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `provider` VARCHAR(40) NOT NULL DEFAULT 'kirvano',
    `event_type` VARCHAR(120) NULL,
    `payload_json` JSON NOT NULL,
    `headers_json` JSON NULL,
    `status` ENUM('received', 'processed', 'ignored', 'error') NOT NULL DEFAULT 'received',
    `processed_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_webhook_provider_status` (`provider`, `status`),
    INDEX `idx_webhook_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notification_outbox` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `channel` ENUM('email', 'whatsapp', 'log') NOT NULL DEFAULT 'log',
    `recipient` VARCHAR(255) NOT NULL,
    `template_key` VARCHAR(120) NOT NULL,
    `payload_json` JSON NULL,
    `status` ENUM('queued', 'sent_mock', 'failed') NOT NULL DEFAULT 'queued',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `sent_at` DATETIME NULL,
    INDEX `idx_outbox_status` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
