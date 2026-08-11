-- Home CMS: testimonials, happy_media, faq_items
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

-- Seed home CMS (idempotent-ish: only when empty)
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
