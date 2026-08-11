-- Blog posts (Clubinho Pet Box)
CREATE TABLE IF NOT EXISTS `blog_posts` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `slug` VARCHAR(191) NOT NULL UNIQUE,
    `title` VARCHAR(255) NOT NULL,
    `excerpt` TEXT,
    `body` LONGTEXT NOT NULL,
    `cover_image` VARCHAR(500) NULL,
    `status` ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    `published_at` DATETIME NULL,
    `seo_title` VARCHAR(255) NULL,
    `seo_description` VARCHAR(500) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_blog_status_published` (`status`, `published_at`),
    INDEX `idx_blog_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
