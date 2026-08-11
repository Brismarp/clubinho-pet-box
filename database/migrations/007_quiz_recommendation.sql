-- Quiz v2 + recommendation mock (apply once on existing DBs)

CREATE TABLE IF NOT EXISTS `customers` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(50) NULL,
    `consent_marketing` TINYINT(1) NOT NULL DEFAULT 0,
    `consent_lgpd_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_customers_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `quiz_versions` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `code` VARCHAR(40) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_quiz_versions_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_tags` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `product_id` INT NOT NULL,
    `tag_key` VARCHAR(80) NOT NULL,
    `tag_value` VARCHAR(120) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    INDEX `idx_product_tags_product` (`product_id`),
    INDEX `idx_product_tags_kv` (`tag_key`, `tag_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `quiz_versions` (`code`, `title`, `is_active`)
SELECT 'v2', 'Quiz inteligente Clubinho v2', 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `quiz_versions` WHERE `code` = 'v2' LIMIT 1);
CREATE TABLE IF NOT EXISTS quiz_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    public_token VARCHAR(64) NOT NULL,
    customer_id INT NULL,
    pet_id INT NULL,
    version_id INT NOT NULL,
    status ENUM('draft', 'completed') NOT NULL DEFAULT 'draft',
    current_step INT NOT NULL DEFAULT 1,
    suggested_plan_slug VARCHAR(50) NULL,
    completed_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_quiz_sessions_token (public_token),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE SET NULL,
    FOREIGN KEY (version_id) REFERENCES quiz_versions(id) ON DELETE RESTRICT,
    INDEX idx_quiz_sessions_status (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quiz_answers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT NOT NULL,
    step_key VARCHAR(40) NOT NULL,
    question_key VARCHAR(80) NOT NULL,
    value_json JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES quiz_sessions(id) ON DELETE CASCADE,
    UNIQUE KEY uq_quiz_answers_session_q (session_id, step_key, question_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quiz_attributes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT NOT NULL,
    attr_key VARCHAR(80) NOT NULL,
    attr_value VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES quiz_sessions(id) ON DELETE CASCADE,
    UNIQUE KEY uq_quiz_attrs (session_id, attr_key),
    INDEX idx_quiz_attrs_kv (attr_key, attr_value)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS box_recommendations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT NOT NULL,
    pet_id INT NULL,
    status ENUM('pending_review', 'approved', 'rejected') NOT NULL DEFAULT 'pending_review',
    suggested_plan_slug VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES quiz_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE SET NULL,
    INDEX idx_box_rec_status (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS box_recommendation_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    recommendation_id INT NOT NULL,
    product_id INT NOT NULL,
    score INT NOT NULL DEFAULT 0,
    justification VARCHAR(500) NOT NULL,
    is_selected TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recommendation_id) REFERENCES box_recommendations(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_box_rec_items_rec (recommendation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- see also 007b_seed_products.sql
