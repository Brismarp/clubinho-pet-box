-- 012: role financeiro, seed roles demo, suporte a lifecycle de assinaturas (já no schema)
SET NAMES utf8mb4;

ALTER TABLE `users`
    MODIFY COLUMN `role` ENUM('admin','operacao','montagem','atendimento','estoque','financeiro') NOT NULL DEFAULT 'atendimento';

-- Demo roles (senha: Carpe1936! — mesmo hash do admin seed)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `is_active`)
SELECT 'Operação', 'operacao@clubinhopetbox.com.br', '$2y$12$4C7C.J3XAlENNEkznrqnj.GGXhOYqLVs1YUfgi4uCJqrJIU2sLGmW', 'operacao', TRUE
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `email` = 'operacao@clubinhopetbox.com.br');

INSERT INTO `users` (`name`, `email`, `password`, `role`, `is_active`)
SELECT 'Montagem', 'montagem@clubinhopetbox.com.br', '$2y$12$4C7C.J3XAlENNEkznrqnj.GGXhOYqLVs1YUfgi4uCJqrJIU2sLGmW', 'montagem', TRUE
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `email` = 'montagem@clubinhopetbox.com.br');

INSERT INTO `users` (`name`, `email`, `password`, `role`, `is_active`)
SELECT 'Atendimento', 'atendimento@clubinhopetbox.com.br', '$2y$12$4C7C.J3XAlENNEkznrqnj.GGXhOYqLVs1YUfgi4uCJqrJIU2sLGmW', 'atendimento', TRUE
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `email` = 'atendimento@clubinhopetbox.com.br');

INSERT INTO `users` (`name`, `email`, `password`, `role`, `is_active`)
SELECT 'Estoque', 'estoque@clubinhopetbox.com.br', '$2y$12$4C7C.J3XAlENNEkznrqnj.GGXhOYqLVs1YUfgi4uCJqrJIU2sLGmW', 'estoque', TRUE
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `email` = 'estoque@clubinhopetbox.com.br');

INSERT INTO `users` (`name`, `email`, `password`, `role`, `is_active`)
SELECT 'Financeiro', 'financeiro@clubinhopetbox.com.br', '$2y$12$4C7C.J3XAlENNEkznrqnj.GGXhOYqLVs1YUfgi4uCJqrJIU2sLGmW', 'financeiro', TRUE
WHERE NOT EXISTS (SELECT 1 FROM `users` WHERE `email` = 'financeiro@clubinhopetbox.com.br');
