-- 010: shipment timeline + expanded testimonials seed
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `shipment_events` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `box_id` INT NULL,
    `tracking_number` VARCHAR(255) NOT NULL,
    `status` VARCHAR(40) NOT NULL,
    `event_code` VARCHAR(60) NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `occurred_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`box_id`) REFERENCES `boxes`(`id`) ON DELETE SET NULL,
    INDEX `idx_shipment_tracking` (`tracking_number`, `occurred_at`),
    INDEX `idx_shipment_box` (`box_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Extra testimonials (idempotent by name+pet)
INSERT INTO `testimonials` (`name`, `pet_name`, `photo_path`, `quote`, `status`, `display_order`)
SELECT s.name, s.pet_name, s.photo_path, s.quote, s.status, s.display_order
FROM (
    SELECT 'Marina S.' AS name, 'Thor' AS pet_name, '/assets/images/mood/q04.jpg' AS photo_path,
           'O Thor espera a box todo mês. A curadoria parece feita para ele de verdade.' AS quote, 'published' AS status, 1 AS display_order
    UNION ALL SELECT 'Rafael M.', 'Luna', '/assets/images/mood/q02.jpg', 'Mais fácil que escolher produto por produto. Qualidade consistente e entrega no prazo.', 'published', 2
    UNION ALL SELECT 'Camila A.', 'Bob', '/assets/images/mood/result-love.jpg', 'Fiz o quiz e o plano Love encaixou perfeito. O Bob destruiu menos sofá desde os mordedores certos.', 'published', 3
    UNION ALL SELECT 'Pedro H.', 'Mel', NULL, 'Atendimento rápido quando precisei ajustar o ciclo. Recomendo.', 'published', 4
    UNION ALL SELECT 'Juliana R.', 'Amora', '/assets/images/mood/q01.jpg', 'A Amora adora os petiscos. Montagem bem pensada sem apego a “pacote padrão”.', 'published', 5
    UNION ALL SELECT 'Bruno T.', 'Zeus', '/assets/images/mood/q05.jpg', 'Eu viajava e a box chegava no dia. Zeus ficou feliz e eu sem culpa.', 'published', 6
    UNION ALL SELECT 'Fernanda L.', 'Mia', '/assets/images/mood/q07.jpg', 'Gata seletiva e mesmo assim a Mia se interessou por quase tudo. Surpresa boa.', 'published', 7
    UNION ALL SELECT 'Lucas P.', 'Toby', '/assets/images/mood/q10.jpg', 'Quiz rápido, indicação certeira. Toby ganhou mordedor resistente — enfim algo que dura.', 'published', 8
    UNION ALL SELECT 'Beatriz N.', 'Pipoca', '/assets/images/mood/result-deluxe.jpg', 'Deluxe vale cada centavo. Embalagem linda e produtos que eu não acharia sozinha.', 'published', 9
    UNION ALL SELECT 'André S.', 'Nala', '/assets/images/mood/q09.jpg', 'Troquei de pet shop por esse clube. Menos impulsividade, mais curadoria.', 'published', 10
    UNION ALL SELECT 'Carolina V.', 'Fred', '/assets/images/mood/q03.jpg', 'Fred tem alergia a frango e o sistema respeitou. Isso me deu tranquilidade.', 'published', 11
    UNION ALL SELECT 'Diego M.', 'Belinha', '/assets/images/mood/q06.jpg', 'Assinatura anual pagou na praticidade. A Belinha já reconhece a caixinha.', 'published', 12
    UNION ALL SELECT 'Helena C.', 'Max', '/assets/images/mood/q08.jpg', 'Deixei de gastar à toa no shopping pet. Agora chega o que ele realmente usa.', 'published', 13
    UNION ALL SELECT 'Igor A.', 'Lola', '/assets/images/mood/result-happy.jpg', 'Happy é o suficiente para a Lola — e o preço fecha com o que eu queria pagar.', 'published', 14
    UNION ALL SELECT 'Sofia K.', 'Chico', NULL, 'Equipe revisou a montagem quando pedi troca. Experiência humana, não robô.', 'published', 15
    UNION ALL SELECT 'Tiago B.', 'Jade', '/assets/images/mood/q04.jpg', 'Já indiquei para três amigos. Todo mundo pergunta da Jade cheia de energia.', 'published', 16
) AS s
WHERE NOT EXISTS (
    SELECT 1 FROM testimonials t WHERE t.name = s.name AND t.pet_name = s.pet_name
);
