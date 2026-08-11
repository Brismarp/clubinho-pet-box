-- Quiz product seed tags (fixed aliases). Products insert is in 007b_seed_products.sql
INSERT INTO product_tags (product_id, tag_key, tag_value)
SELECT p.id, t.k, t.v
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
