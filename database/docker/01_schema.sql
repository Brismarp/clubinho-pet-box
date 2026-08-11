-- ============================================================================
-- CLUBINHO PET BOX - BANCO DE DADOS COMPLETO
-- ============================================================================
-- Data: 04/02/2026
-- Versão: 3.0
-- Descrição: Schema completo com todas as tabelas necessárias
-- ============================================================================

-- ============================================================================
-- 1. TABELA: users
-- ============================================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'editor', 'viewer') DEFAULT 'viewer',
    `is_active` BOOLEAN DEFAULT TRUE,
    `last_login` DATETIME,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. TABELA: settings
-- ============================================================================
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `key` VARCHAR(255) UNIQUE NOT NULL,
    `value` LONGTEXT,
    `type` ENUM('string', 'integer', 'boolean', 'json', 'array') DEFAULT 'string',
    `description` TEXT,
    `is_public` BOOLEAN DEFAULT FALSE,
    `version` INT DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_key` (`key`),
    INDEX `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. TABELA: settings_history
-- ============================================================================
CREATE TABLE IF NOT EXISTS `settings_history` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `setting_id` INT NOT NULL,
    `old_value` LONGTEXT,
    `new_value` LONGTEXT,
    `changed_by` INT,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`setting_id`) REFERENCES `settings`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`changed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_setting_id` (`setting_id`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. TABELA: plans
-- ============================================================================
CREATE TABLE IF NOT EXISTS `plans` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) UNIQUE NOT NULL,
    `description` TEXT,
    `price` DECIMAL(10, 2) NOT NULL,
    `currency` VARCHAR(3) DEFAULT 'BRL',
    `billing_cycle` ENUM('monthly', 'quarterly', 'annual') DEFAULT 'monthly',
    `checkout_url` VARCHAR(500),
    `features` JSON,
    `is_active` BOOLEAN DEFAULT TRUE,
    `is_popular` BOOLEAN DEFAULT FALSE,
    `display_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. TABELA: subscriptions
-- ============================================================================
CREATE TABLE IF NOT EXISTS `subscriptions` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT,
    `plan_id` INT NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `pet_name` VARCHAR(255),
    `pet_type` VARCHAR(100),
    `pet_age` INT,
    `status` ENUM('active', 'paused', 'cancelled', 'expired') DEFAULT 'active',
    `start_date` DATE,
    `next_billing_date` DATE,
    `cancellation_date` DATE,
    `external_id` VARCHAR(255),
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE RESTRICT,
    INDEX `idx_email` (`email`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. TABELA: pets
-- ============================================================================
CREATE TABLE IF NOT EXISTS `pets` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `customer_id` INT NULL,
    `subscription_id` INT NULL,
    `name` VARCHAR(255) NOT NULL,
    `type` VARCHAR(100),
    `species` VARCHAR(50),
    `breed` VARCHAR(255),
    `age` INT,
    `age_months` INT,
    `sex` VARCHAR(20),
    `size` VARCHAR(30),
    `weight` DECIMAL(5, 2),
    `dietary_restrictions` TEXT,
    `health_notes` TEXT,
    `photo_url` VARCHAR(500),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions`(`id`) ON DELETE SET NULL,
    INDEX `idx_subscription_id` (`subscription_id`),
    INDEX `idx_pets_customer` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 7. TABELA: boxes
-- ============================================================================
CREATE TABLE IF NOT EXISTS `boxes` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `subscription_id` INT NOT NULL,
    `box_number` INT,
    `send_date` DATE,
    `estimated_delivery_date` DATE,
    `actual_delivery_date` DATE,
    `tracking_number` VARCHAR(255),
    `status` ENUM('preparing', 'shipped', 'delivered', 'returned') DEFAULT 'preparing',
    `contents` JSON,
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions`(`id`) ON DELETE CASCADE,
    INDEX `idx_subscription_id` (`subscription_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_send_date` (`send_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 8. TABELA: products
-- ============================================================================
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `sku` VARCHAR(80) NULL,
    `description` TEXT,
    `category` VARCHAR(100),
    `price` DECIMAL(10, 2),
    `cost` DECIMAL(10, 2),
    `supplier` VARCHAR(255),
    `is_available` BOOLEAN DEFAULT TRUE,
    `stock_qty` INT NOT NULL DEFAULT 0,
    `image_url` VARCHAR(500),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_category` (`category`),
    INDEX `idx_is_available` (`is_available`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 9. TABELA: activity_logs
-- ============================================================================
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT,
    `action` VARCHAR(255) NOT NULL,
    `entity_type` VARCHAR(100),
    `entity_id` INT,
    `old_values` JSON,
    `new_values` JSON,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_entity_type` (`entity_type`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 10. TABELA: contact_messages
-- ============================================================================
CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `message` LONGTEXT NOT NULL,
    `status` ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
    `replied_at` DATETIME,
    `reply_message` LONGTEXT,
    `replied_by` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`replied_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_email` (`email`),
    INDEX `idx_status` (`status`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 11. TABELA: sessions
-- ============================================================================
CREATE TABLE IF NOT EXISTS `sessions` (
    `id` VARCHAR(255) PRIMARY KEY,
    `user_id` INT NOT NULL,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 12. TABELA: checkout_urls
-- ============================================================================
CREATE TABLE IF NOT EXISTS `checkout_urls` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `plan_id` INT NOT NULL,
    `url` VARCHAR(500) NOT NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE CASCADE,
    INDEX `idx_plan_id` (`plan_id`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 13. TABELA: analytics_events
-- ============================================================================
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

-- ============================================================================
-- 14. TABELA: leads
-- ============================================================================
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

-- ============================================================================
-- 15. TABELA: blog_posts
-- ============================================================================
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

-- ============================================================================
-- 16. TABELAS: home CMS
-- ============================================================================
CREATE TABLE IF NOT EXISTS `testimonials` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `pet_name` VARCHAR(255) NULL,
    `photo_path` VARCHAR(500) NULL,
    `quote` TEXT NOT NULL,
    `status` ENUM('pending', 'published', 'hidden') NOT NULL DEFAULT 'pending',
    `display_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_testimonials_status_order` (`status`, `display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `happy_media` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `type` ENUM('image', 'video') NOT NULL DEFAULT 'image',
    `media_path` VARCHAR(500) NULL,
    `media_url` VARCHAR(1000) NULL,
    `pet_name` VARCHAR(255) NOT NULL,
    `tutor_handle` VARCHAR(255) NULL,
    `orientation` ENUM('portrait', 'landscape') NOT NULL DEFAULT 'landscape',
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `display_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_happy_status_order` (`status`, `display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `faq_items` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `question` VARCHAR(500) NOT NULL,
    `answer` TEXT NOT NULL,
    `display_order` INT NOT NULL DEFAULT 0,
    `is_published` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_faq_published_order` (`is_published`, `display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Home CMS seed
INSERT INTO `testimonials` (`name`, `pet_name`, `photo_path`, `quote`, `status`, `display_order`)
SELECT * FROM (
    SELECT 'Marina S.' AS name, 'Thor' AS pet_name, '/assets/images/mood/q04.jpg' AS photo_path,
           'O Thor espera a box todo mês. A curadoria parece feita para ele de verdade.' AS quote,
           'published' AS status, 1 AS display_order
    UNION ALL SELECT 'Rafael M.', 'Luna', '/assets/images/mood/q02.jpg',
           'Mais fácil que escolher produto por produto. Qualidade consistente e entrega no prazo.',
           'published', 2
    UNION ALL SELECT 'Camila A.', 'Bob', '/assets/images/mood/result-love.jpg',
           'Fiz o quiz e o plano Love encaixou perfeito. O Bob destruiu menos sofá desde os mordedores certos.',
           'published', 3
    UNION ALL SELECT 'Pedro H.', 'Mel', NULL,
           'Atendimento rápido quando precisei ajustar o ciclo. Recomendo.',
           'published', 4
) AS seed WHERE NOT EXISTS (SELECT 1 FROM `testimonials` LIMIT 1);

INSERT INTO `happy_media` (`type`, `media_path`, `media_url`, `pet_name`, `tutor_handle`, `orientation`, `status`, `display_order`)
SELECT * FROM (
    SELECT 'image' AS type, '/assets/images/mood/q01.jpg' AS media_path, NULL AS media_url,
           'Thor' AS pet_name, '@marina.pets' AS tutor_handle, 'portrait' AS orientation,
           'approved' AS status, 1 AS display_order
    UNION ALL SELECT 'image', '/assets/images/mood/q05.jpg', NULL, 'Luna', '@rafa_m', 'portrait', 'approved', 2
    UNION ALL SELECT 'image', '/assets/images/mood/q09.jpg', NULL, 'Bob', NULL, 'landscape', 'approved', 3
    UNION ALL SELECT 'image', '/assets/images/mood/q07.jpg', NULL, 'Mel', '@camilas', 'portrait', 'approved', 4
    UNION ALL SELECT 'image', '/assets/images/mood/result-deluxe.jpg', NULL, 'Pipoca', NULL, 'landscape', 'approved', 5
    UNION ALL SELECT 'image', '/assets/images/mood/q10.jpg', NULL, 'Duke', '@pedro.h', 'portrait', 'approved', 6
) AS seed WHERE NOT EXISTS (SELECT 1 FROM `happy_media` LIMIT 1);

INSERT INTO `faq_items` (`question`, `answer`, `display_order`, `is_published`)
SELECT * FROM (
    SELECT 'Como funciona a assinatura?' AS question,
           'Você escolhe um plano (ou faz o quiz), conclui o checkout e recebe a box mensalmente com produtos selecionados para o perfil do seu pet.' AS answer,
           1 AS display_order, 1 AS is_published
    UNION ALL SELECT 'Posso pausar ou cancelar?',
           'Sim. Entre em contato pelo formulário ou e-mail de atendimento. Pausas e cancelamentos seguem as regras do ciclo de cobrança do provedor de pagamento.',
           2, 1
    UNION ALL SELECT 'A box é personalizada?',
           'Sim. O quiz e as informações do pet ajudam a equipe a montar uma seleção mais adequada — reduzindo produtos inadequados ao porte, rotina e preferências.',
           3, 1
    UNION ALL SELECT 'Quais formas de pagamento?',
           'O checkout é processado por parceiro externo (Kirvano), que oferece as opções disponíveis no momento da compra (cartão e outras conforme o provedor).',
           4, 1
    UNION ALL SELECT 'Como acompanho o envio?',
           'Após a expedição, o código de rastreio fica disponível no atendimento e, quando aplicável, no histórico da assinatura.',
           5, 1
    UNION ALL SELECT 'E se meu pet tiver restrições?',
           'Informe restrições no quiz ou no formulário de assinatura. Usamos esses dados apenas para seleção de produtos — não substituem orientação veterinária.',
           6, 1
) AS seed WHERE NOT EXISTS (SELECT 1 FROM `faq_items` LIMIT 1);

-- ============================================================================
-- DADOS INICIAIS
-- ============================================================================

-- Admin User
INSERT INTO `users` (`name`, `email`, `password`, `role`, `is_active`) VALUES
('Administrador', 'admin@clubinhopetbox.com.br', '$2y$12$4C7C.J3XAlENNEkznrqnj.GGXhOYqLVs1YUfgi4uCJqrJIU2sLGmW', 'admin', TRUE);

-- Plans
INSERT INTO `plans` (`name`, `slug`, `description`, `price`, `currency`, `billing_cycle`, `checkout_url`, `features`, `is_active`, `is_popular`, `display_order`) VALUES
('Clubinho Pet Happy', 'happy', 'Box essencial com produtos selecionados', 101.47, 'BRL', 'monthly', 'https://pay.kirvano.com/1e4f4ee0-4e70-41e8-b98f-b26946375d50', '["4 a 6 produtos essenciais", "Brinquedos divertidos", "Petiscos saborosos", "Produtos de higiene"]', TRUE, FALSE, 1),
('Clubinho Pet Love', 'love', 'Box premium com produtos exclusivos', 164.97, 'BRL', 'monthly', 'https://pay.kirvano.com/7c365f70-2296-4912-a19f-37d60b309e66', '["6 a 8 produtos exclusivos", "Brinquedos interativos", "Petiscos premium", "Marcas renomadas"]', TRUE, TRUE, 2),
('Clubinho Pet Deluxe', 'deluxe', 'Box super premium com o que há de melhor', 253.87, 'BRL', 'monthly', 'https://pay.kirvano.com/497c2b01-a5b9-40e7-a023-2e79c24eb322', '["8 a 10 produtos de luxo", "Brinquedos importados", "Petiscos gourmet", "Produtos naturais"]', TRUE, FALSE, 3);

-- Settings
INSERT INTO `settings` (`key`, `value`, `type`, `description`, `is_public`) VALUES
('site_name', 'Clubinho Pet Box', 'string', 'Nome do site', TRUE),
('site_description', 'Assinatura premium de produtos selecionados para o seu pet', 'string', 'Descrição do site', TRUE),
('site_logo_url', 'assets/images/logo/logo.jpg', 'string', 'URL do logo', TRUE),
('site_primary_color', '#000000', 'string', 'Cor primária', FALSE),
('site_secondary_color', '#ffffff', 'string', 'Cor secundária', FALSE),
('site_accent_color', '#d4af37', 'string', 'Cor de destaque', FALSE),
('contact_email', 'email-contato@clubinhopetbox.com.br', 'string', 'Email de contato', TRUE),
('contact_phone', '(11) 99999-9999', 'string', 'Telefone de contato', TRUE),
('contact_whatsapp', '(11) 99999-9999', 'string', 'WhatsApp', TRUE),
('home_hero_title', 'Momentos especiais merecem produtos especiais', 'string', 'Título da seção hero', TRUE),
('home_hero_description', 'Uma seleção exclusiva de produtos premium entregue mensalmente na sua casa.', 'string', 'Descrição da seção hero', TRUE),
('home_how_it_works_title', 'Como funciona', 'string', 'Título de como funciona', TRUE),
('about_title', 'Sobre Clubinho Pet Box', 'string', 'Título da página sobre', TRUE),
('about_description', 'Clubinho Pet Box nasceu da paixão por pets e da vontade de proporcionar momentos especiais aos nossos melhores amigos.', 'string', 'Descrição da página sobre', TRUE),
('footer_copyright', '© 2024 Clubinho Pet Box. Todos os direitos reservados.', 'string', 'Texto de copyright', TRUE),
('seo_keywords', 'pet box, assinatura, produtos para pets, caixa mensal', 'string', 'Palavras-chave SEO', FALSE),
('seo_author', 'Clubinho Pet Box', 'string', 'Autor para SEO', FALSE);

-- Checkout URLs
INSERT INTO `checkout_urls` (`plan_id`, `url`, `is_active`) VALUES
(1, 'https://pay.kirvano.com/1e4f4ee0-4e70-41e8-b98f-b26946375d50', TRUE),
(2, 'https://pay.kirvano.com/7c365f70-2296-4912-a19f-37d60b309e66', TRUE),
(3, 'https://pay.kirvano.com/497c2b01-a5b9-40e7-a023-2e79c24eb322', TRUE);

-- ============================================================================
-- 17. Quiz v2 + recomendação
-- ============================================================================
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

ALTER TABLE `pets`
    ADD CONSTRAINT `fk_pets_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL;

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

CREATE TABLE IF NOT EXISTS `quiz_versions` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `code` VARCHAR(40) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_quiz_versions_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `quiz_sessions` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `public_token` VARCHAR(64) NOT NULL,
    `customer_id` INT NULL,
    `pet_id` INT NULL,
    `version_id` INT NOT NULL,
    `status` ENUM('draft', 'completed') NOT NULL DEFAULT 'draft',
    `current_step` INT NOT NULL DEFAULT 1,
    `suggested_plan_slug` VARCHAR(50) NULL,
    `completed_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_quiz_sessions_token` (`public_token`),
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`pet_id`) REFERENCES `pets`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`version_id`) REFERENCES `quiz_versions`(`id`) ON DELETE RESTRICT,
    INDEX `idx_quiz_sessions_status` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `quiz_answers` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `session_id` INT NOT NULL,
    `step_key` VARCHAR(40) NOT NULL,
    `question_key` VARCHAR(80) NOT NULL,
    `value_json` JSON NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`session_id`) REFERENCES `quiz_sessions`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uq_quiz_answers_session_q` (`session_id`, `step_key`, `question_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `quiz_attributes` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `session_id` INT NOT NULL,
    `attr_key` VARCHAR(80) NOT NULL,
    `attr_value` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`session_id`) REFERENCES `quiz_sessions`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uq_quiz_attrs` (`session_id`, `attr_key`),
    INDEX `idx_quiz_attrs_kv` (`attr_key`, `attr_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `box_recommendations` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `session_id` INT NOT NULL,
    `pet_id` INT NULL,
    `status` ENUM('pending_review', 'approved', 'rejected') NOT NULL DEFAULT 'pending_review',
    `suggested_plan_slug` VARCHAR(50) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`session_id`) REFERENCES `quiz_sessions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`pet_id`) REFERENCES `pets`(`id`) ON DELETE SET NULL,
    INDEX `idx_box_rec_status` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `box_recommendation_items` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `recommendation_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `score` INT NOT NULL DEFAULT 0,
    `justification` VARCHAR(500) NOT NULL,
    `is_selected` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`recommendation_id`) REFERENCES `box_recommendations`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    INDEX `idx_box_rec_items_rec` (`recommendation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `quiz_versions` (`code`, `title`, `is_active`)
SELECT 'v2', 'Quiz inteligente Clubinho v2', 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `quiz_versions` WHERE `code` = 'v2' LIMIT 1);

-- Seed catalog for recommendation mock: apply migrations/007b_seed_products.sql + 007c_seed_tags.sql

-- ============================================================================
-- 18. Integrations mock / audit outbox
-- ============================================================================
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

-- ============================================================================
-- FIM DO SCRIPT
-- ============================================================================
