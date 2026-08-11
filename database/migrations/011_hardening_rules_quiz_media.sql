-- Hardening: versioned JSON recommendation rules + quiz v3 attrs + media uploads prep
-- Apply: docker compose exec -T db mysql -uclubinho -pclubinho clubinhopet < database/migrations/011_hardening_rules_quiz_media.sql

CREATE TABLE IF NOT EXISTS `recommendation_rule_sets` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `code` VARCHAR(40) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `rules_json` JSON NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_recommendation_rule_sets_code` (`code`),
    INDEX `idx_recommendation_rule_sets_active` (`is_active`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add rule_set_id to box_recommendations (idempotent)
SET @col_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'box_recommendations'
      AND COLUMN_NAME = 'rule_set_id'
);
SET @sql := IF(
    @col_exists = 0,
    'ALTER TABLE `box_recommendations` ADD COLUMN `rule_set_id` INT NULL AFTER `suggested_plan_slug`, ADD INDEX `idx_box_rec_rule_set` (`rule_set_id`)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Quiz v3: deactivate v2, activate v3
UPDATE `quiz_versions` SET `is_active` = 0 WHERE `code` = 'v2';
INSERT INTO `quiz_versions` (`code`, `title`, `is_active`)
SELECT 'v3', 'Quiz inteligente Clubinho v3 (ambiente + materiais)', 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `quiz_versions` WHERE `code` = 'v3' LIMIT 1);
UPDATE `quiz_versions` SET `is_active` = 1, `title` = 'Quiz inteligente Clubinho v3 (ambiente + materiais)' WHERE `code` = 'v3';
UPDATE `quiz_versions` SET `is_active` = 0 WHERE `code` <> 'v3';

-- Best-effort enriched product tags (material / ambiente / toy_size / play_freq)
INSERT INTO `product_tags` (`product_id`, `tag_key`, `tag_value`)
SELECT p.id, t.k, t.v
FROM (
    SELECT 'CPB-TOY-01' sku, 'material' k, 'rubber' v UNION ALL SELECT 'CPB-TOY-01','toy_size','large' UNION ALL SELECT 'CPB-TOY-01','ambiente','house' UNION ALL SELECT 'CPB-TOY-01','ambiente','outdoor' UNION ALL SELECT 'CPB-TOY-01','play_freq','several'
    UNION ALL SELECT 'CPB-TOY-02','material','fabric' UNION ALL SELECT 'CPB-TOY-02','toy_size','small' UNION ALL SELECT 'CPB-TOY-02','ambiente','apartment' UNION ALL SELECT 'CPB-TOY-02','play_freq','rare'
    UNION ALL SELECT 'CPB-TOY-03','material','wood' UNION ALL SELECT 'CPB-TOY-03','toy_size','medium' UNION ALL SELECT 'CPB-TOY-03','ambiente','apartment' UNION ALL SELECT 'CPB-TOY-03','play_freq','daily'
    UNION ALL SELECT 'CPB-TOY-04','material','rubber' UNION ALL SELECT 'CPB-TOY-04','toy_size','medium' UNION ALL SELECT 'CPB-TOY-04','ambiente','outdoor' UNION ALL SELECT 'CPB-TOY-04','play_freq','several'
    UNION ALL SELECT 'CPB-TOY-05','material','rubber' UNION ALL SELECT 'CPB-TOY-05','toy_size','small' UNION ALL SELECT 'CPB-TOY-05','ambiente','apartment' UNION ALL SELECT 'CPB-TOY-05','play_freq','daily'
    UNION ALL SELECT 'CPB-ACC-01','ambiente','outdoor' UNION ALL SELECT 'CPB-ACC-01','ambiente','house'
    UNION ALL SELECT 'CPB-CAT-01','material','fabric' UNION ALL SELECT 'CPB-CAT-01','toy_size','small' UNION ALL SELECT 'CPB-CAT-01','ambiente','apartment'
) t
JOIN `products` p ON p.sku = t.sku
WHERE NOT EXISTS (
    SELECT 1 FROM `product_tags` pt
    WHERE pt.product_id = p.id AND pt.tag_key = t.k AND pt.tag_value = t.v
);

-- Seed rules-v1 (mock behavior + new attr weights)
UPDATE `recommendation_rule_sets` SET `is_active` = 0 WHERE `code` <> 'rules-v1';
INSERT INTO `recommendation_rule_sets` (`code`, `title`, `rules_json`, `is_active`)
SELECT
    'rules-v1',
    'Rules engine v1 (mock parity + quiz enrich)',
    CAST('{
  "version": "rules-v1",
  "builtins": {
    "require_available": true,
    "require_stock": true,
    "species_gate": true,
    "size_gate": true,
    "exclude_if_tag": true,
    "no_chicken_flavor": true,
    "dont_want_category": true,
    "min_score": 2
  },
  "excludes": [
    {
      "when_attr": {"no_chicken": "true"},
      "tag_key": "flavor",
      "tag_value": "chicken"
    },
    {
      "attr_prefix": "dont_want_",
      "when_attr_value": "true",
      "tag_key": "category",
      "attr_suffix_as_tag_value": true
    }
  ],
  "match_weights": [
    {
      "attr_key": "species",
      "tag_key": "species",
      "tag_equals_attr": true,
      "weight": 3,
      "reason": "compatível com a espécie do pet"
    },
    {
      "attr_key": "size",
      "tag_key": "size",
      "tag_equals_attr": true,
      "weight": 2,
      "reason": "adequado ao porte {attr}"
    },
    {
      "when_attr": {"no_chicken": "true"},
      "tag_key": "allergy_safe",
      "tag_value": "chicken",
      "weight": 4,
      "reason": "alternativa segura porque o tutor indicou restrição a frango"
    },
    {
      "when_attr": {"destructive": "true"},
      "tag_key": "behavior",
      "tag_value": "destructive",
      "weight": 5,
      "reason": "brinquedo resistente porque o perfil indicou alta destrutividade"
    },
    {
      "when_attr": {"energy": "high"},
      "tag_key": "energy",
      "tag_value": "high",
      "weight": 3,
      "reason": "combina com alto nível de energia"
    },
    {
      "when_attr": {"energy": "low"},
      "tag_key": "energy",
      "tag_value": "low",
      "weight": 2,
      "reason": "combina com perfil mais calmo"
    },
    {
      "when_attr": {"age_band": "puppy"},
      "tag_key": "age",
      "tag_value": "puppy",
      "weight": 3,
      "reason": "indicado para filhotes"
    },
    {
      "when_attr": {"want_brinquedo": "true"},
      "tag_key": "category",
      "tag_value": "brinquedo",
      "weight": 2,
      "reason": "categoria desejada pelo tutor: brinquedo"
    },
    {
      "when_attr": {"want_petisco": "true"},
      "tag_key": "category",
      "tag_value": "petisco",
      "weight": 2,
      "reason": "categoria desejada pelo tutor: petisco"
    },
    {
      "when_attr": {"want_higiene": "true"},
      "tag_key": "category",
      "tag_value": "higiene",
      "weight": 2,
      "reason": "categoria desejada pelo tutor: higiene"
    },
    {
      "when_attr": {"want_acessorio": "true"},
      "tag_key": "category",
      "tag_value": "acessorio",
      "weight": 2,
      "reason": "categoria desejada pelo tutor: acessorio"
    },
    {
      "when_attr": {"interest_walk": "true"},
      "tag_key": "interest",
      "tag_value": "walk",
      "weight": 2,
      "reason": "interesse em itens de passeio"
    },
    {
      "when_attr": {"interest_higiene": "true"},
      "tag_key": "category",
      "tag_value": "higiene",
      "weight": 2,
      "reason": "interesse em higiene"
    },
    {
      "attr_key": "ambiente",
      "tag_key": "ambiente",
      "tag_equals_attr": true,
      "weight": 2,
      "reason": "adequado ao ambiente do pet"
    },
    {
      "attr_key": "play_freq",
      "tag_key": "play_freq",
      "tag_equals_attr": true,
      "weight": 1,
      "reason": "combina com a frequência de brincadeiras"
    },
    {
      "attr_key": "toy_size",
      "tag_key": "toy_size",
      "tag_equals_attr": true,
      "weight": 2,
      "reason": "tamanho de brinquedo alinhado"
    },
    {
      "attr_prefix": "toy_material_",
      "when_attr_value": "true",
      "tag_key": "material",
      "attr_suffix_as_tag_value": true,
      "weight": 2,
      "reason": "material preferido: {value}"
    }
  ]
}' AS JSON),
    1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `recommendation_rule_sets` WHERE `code` = 'rules-v1' LIMIT 1);

UPDATE `recommendation_rule_sets`
SET
    `title` = 'Rules engine v1 (mock parity + quiz enrich)',
    `is_active` = 1
WHERE `code` = 'rules-v1';
