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
    `subscription_id` INT NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `type` VARCHAR(100),
    `breed` VARCHAR(255),
    `age` INT,
    `weight` DECIMAL(5, 2),
    `dietary_restrictions` TEXT,
    `health_notes` TEXT,
    `photo_url` VARCHAR(500),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions`(`id`) ON DELETE CASCADE,
    INDEX `idx_subscription_id` (`subscription_id`)
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
    `description` TEXT,
    `category` VARCHAR(100),
    `price` DECIMAL(10, 2),
    `supplier` VARCHAR(255),
    `is_available` BOOLEAN DEFAULT TRUE,
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
-- DADOS INICIAIS
-- ============================================================================

-- Admin User
INSERT INTO `users` (`name`, `email`, `password`, `role`, `is_active`) VALUES
('Administrador', 'admin@clubinhopetbox.com.br', '$2y$10$YourHashedPasswordHere', 'admin', TRUE);

-- Plans
INSERT INTO `plans` (`name`, `slug`, `description`, `price`, `currency`, `billing_cycle`, `checkout_url`, `features`, `is_active`, `is_popular`, `display_order`) VALUES
('Clubinho Pet Happy', 'happy', 'Box essencial com produtos selecionados', 101.47, 'BRL', 'monthly', 'https://checkout.transacaoprotegida.com/b26d7285-8336-49dc-baf0-f2501441c0bf', '["4 a 6 produtos essenciais", "Brinquedos divertidos", "Petiscos saborosos", "Produtos de higiene"]', TRUE, FALSE, 1),
('Clubinho Pet Love', 'love', 'Box premium com produtos exclusivos', 164.97, 'BRL', 'monthly', 'https://checkout.transacaoprotegida.com/7bfbb99e-7a2e-4500-90d6-85b90d5c1026', '["6 a 8 produtos exclusivos", "Brinquedos interativos", "Petiscos premium", "Marcas renomadas"]', TRUE, TRUE, 2),
('Clubinho Pet Deluxe', 'deluxe', 'Box super premium com o que há de melhor', 253.87, 'BRL', 'monthly', 'https://checkout.transacaoprotegida.com/41c09d38-99c5-4fd4-b385-02c8877bf608', '["8 a 10 produtos de luxo", "Brinquedos importados", "Petiscos gourmet", "Produtos naturais"]', TRUE, FALSE, 3);

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
(1, 'https://checkout.transacaoprotegida.com/b26d7285-8336-49dc-baf0-f2501441c0bf', TRUE),
(2, 'https://checkout.transacaoprotegida.com/7bfbb99e-7a2e-4500-90d6-85b90d5c1026', TRUE),
(3, 'https://checkout.transacaoprotegida.com/41c09d38-99c5-4fd4-b385-02c8877bf608', TRUE);

-- ============================================================================
-- FIM DO SCRIPT
-- ============================================================================
