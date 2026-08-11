-- Seed products + tags for recommendation mock (skip if products already seeded with sku CPB-)
INSERT INTO products (name, sku, description, category, price, cost, is_available, stock_qty)
SELECT * FROM (
    SELECT 'Mordedor resistente nylon' AS name, 'CPB-TOY-01' AS sku, 'Para pets destrutivos' AS description, 'brinquedo' AS category, 39.90 AS price, 18.00 AS cost, 1 AS is_available, 25 AS stock_qty
    UNION ALL SELECT 'Pelúcia macia S', 'CPB-TOY-02', 'Pelúcia delicada porte pequeno', 'brinquedo', 29.90, 12.00, 1, 30
    UNION ALL SELECT 'Brinquedo interativo puzzle', 'CPB-TOY-03', 'Estimula mente e energia alta', 'brinquedo', 49.90, 22.00, 1, 15
    UNION ALL SELECT 'Bolinha tênis M', 'CPB-TOY-04', 'Porte médio', 'brinquedo', 19.90, 7.00, 1, 40
    UNION ALL SELECT 'Mordedor filhote latex', 'CPB-TOY-05', 'Textura suave filhotes', 'brinquedo', 24.90, 9.00, 1, 20
    UNION ALL SELECT 'Petisco frango soft', 'CPB-TRE-01', 'Petisco sabor frango', 'petisco', 22.90, 8.00, 1, 50
    UNION ALL SELECT 'Petisco peixe hipoalergênico', 'CPB-TRE-02', 'Sem frango; peixe', 'petisco', 27.90, 11.00, 1, 18
    UNION ALL SELECT 'Biscoito integral veggie', 'CPB-TRE-03', 'Sem proteína animal principal', 'petisco', 18.90, 6.00, 1, 0
    UNION ALL SELECT 'Dental stick M', 'CPB-TRE-04', 'Higiene bucal porte médio', 'petisco', 32.90, 14.00, 1, 22
    UNION ALL SELECT 'Shampoo neutro pets', 'CPB-HYG-01', 'Higiene uso geral', 'higiene', 34.90, 15.00, 1, 28
    UNION ALL SELECT 'Lenço umedecido pet', 'CPB-HYG-02', 'Limpeza rápida', 'higiene', 16.90, 5.00, 1, 35
    UNION ALL SELECT 'Escova pelo curto', 'CPB-HYG-03', 'Cuidados pelo', 'higiene', 28.90, 10.00, 1, 12
    UNION ALL SELECT 'Guia passeio conforto', 'CPB-ACC-01', 'Acessório passeio', 'acessorio', 45.90, 20.00, 1, 16
    UNION ALL SELECT 'Comedouro antiformiga', 'CPB-ACC-02', 'Acessório alimentação', 'acessorio', 39.90, 17.00, 1, 10
    UNION ALL SELECT 'Brinquedo gato pena', 'CPB-CAT-01', 'Espécie gato', 'brinquedo', 21.90, 8.00, 1, 14
    UNION ALL SELECT 'Arranhador mini', 'CPB-CAT-02', 'Gato', 'acessorio', 59.90, 25.00, 1, 8
) AS seed WHERE NOT EXISTS (SELECT 1 FROM products WHERE sku LIKE 'CPB-%' LIMIT 1);

-- Tags (only if product_tags empty for these)
INSERT INTO product_tags (product_id, tag_key, tag_value)
SELECT p.id, t.tag_key, t.tag_value
FROM (
    SELECT 'CPB-TOY-01' sku, 'species' k, 'dog' v UNION ALL SELECT 'CPB-TOY-01','size','medium' UNION ALL SELECT 'CPB-TOY-01','size','large'
    UNION ALL SELECT 'CPB-TOY-01','behavior','destructive' UNION ALL SELECT 'CPB-TOY-01','category','brinquedo' UNION ALL SELECT 'CPB-TOY-01','energy','high'
    UNION ALL SELECT 'CPB-TOY-02','species','dog' UNION ALL SELECT 'CPB-TOY-02','size','small' UNION ALL SELECT 'CPB-TOY-02','behavior','calm'
    UNION ALL SELECT 'CPB-TOY-02','category','brinquedo' UNION ALL SELECT 'CPB-TOY-02','energy','low'
    UNION ALL SELECT 'CPB-TOY-03','species','dog' UNION ALL SELECT 'CPB-TOY-03','energy','high' UNION ALL SELECT 'CPB-TOY-03','category','brinquedo'
    UNION ALL SELECT 'CPB-TOY-03','behavior','curious' UNION ALL SELECT 'CPB-TOY-03','size','medium'
    UNION ALL SELECT 'CPB-TOY-04','species','dog' UNION ALL SELECT 'CPB-TOY-04','size','medium' UNION ALL SELECT 'CPB-TOY-04','category','brinquedo'
    UNION ALL SELECT 'CPB-TOY-05','species','dog' UNION ALL SELECT 'CPB-TOY-05','age','puppy' UNION ALL SELECT 'CPB-TOY-05','category','brinquedo'
    UNION ALL SELECT 'CPB-TRE-01','species','dog' UNION ALL SELECT 'CPB-TRE-01','flavor','chicken' UNION ALL SELECT 'CPB-TRE-01','category','petisco'
    UNION ALL SELECT 'CPB-TRE-01','exclude_if','no_chicken'
    UNION ALL SELECT 'CPB-TRE-02','species','dog' UNION ALL SELECT 'CPB-TRE-02','flavor','fish' UNION ALL SELECT 'CPB-TRE-02','category','petisco'
    UNION ALL SELECT 'CPB-TRE-02','allergy_safe','chicken'
    UNION ALL SELECT 'CPB-TRE-03','species','dog' UNION ALL SELECT 'CPB-TRE-03','category','petisco' UNION ALL SELECT 'CPB-TRE-03','diet','natural'
    UNION ALL SELECT 'CPB-TRE-04','species','dog' UNION ALL SELECT 'CPB-TRE-04','size','medium' UNION ALL SELECT 'CPB-TRE-04','category','petisco'
    UNION ALL SELECT 'CPB-HYG-01','species','dog' UNION ALL SELECT 'CPB-HYG-01','species','cat' UNION ALL SELECT 'CPB-HYG-01','category','higiene'
    UNION ALL SELECT 'CPB-HYG-02','species','dog' UNION ALL SELECT 'CPB-HYG-02','species','cat' UNION ALL SELECT 'CPB-HYG-02','category','higiene'
    UNION ALL SELECT 'CPB-HYG-03','species','dog' UNION ALL SELECT 'CPB-HYG-03','category','higiene'
    UNION ALL SELECT 'CPB-ACC-01','species','dog' UNION ALL SELECT 'CPB-ACC-01','category','acessorio' UNION ALL SELECT 'CPB-ACC-01','interest','walk'
    UNION ALL SELECT 'CPB-ACC-02','species','dog' UNION ALL SELECT 'CPB-ACC-02','species','cat' UNION ALL SELECT 'CPB-ACC-02','category','acessorio'
    UNION ALL SELECT 'CPB-CAT-01','species','cat' UNION ALL SELECT 'CPB-CAT-01','category','brinquedo'
    UNION ALL SELECT 'CPB-CAT-02','species','cat' UNION ALL SELECT 'CPB-CAT-02','category','acessorio'
) t
JOIN products p ON p.sku = t.sku
WHERE NOT EXISTS (SELECT 1 FROM product_tags LIMIT 1);
