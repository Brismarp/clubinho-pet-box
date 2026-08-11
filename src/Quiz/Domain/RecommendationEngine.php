<?php

declare(strict_types=1);

namespace ClubinhoPet\Quiz\Domain;

interface RecommendationEngine
{
    /**
     * @param array<string, string> $attrs
     * @param list<array{id:int,name:string,sku:?string,category:string,stock_qty:int,is_available:bool,tags:list<array{tag_key:string,tag_value:string}>}> $products
     * @return list<array{product_id:int,score:int,justification:string,is_selected:bool}>
     */
    public function recommend(array $attrs, array $products, int $limit = 8): array;

    /** Active ruleset id when the engine is JSON-backed; null for mock/fallback. */
    public function ruleSetId(): ?int;
}
