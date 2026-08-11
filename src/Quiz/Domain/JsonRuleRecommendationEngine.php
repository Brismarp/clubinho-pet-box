<?php

declare(strict_types=1);

namespace ClubinhoPet\Quiz\Domain;

use PDO;

/**
 * Scores products using an active recommendation_rule_sets.rules_json document.
 * Builtin gates (species/size/exclude_if/no_chicken/dont_want) harden eligibility
 * even if the JSON omits them; match_weights / excludes refine scoring.
 */
final class JsonRuleRecommendationEngine implements RecommendationEngine
{
    private ?array $active = null;
    private bool $loaded = false;

    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @param array<string, string> $attrs
     * @param list<array{id:int,name:string,sku:?string,category:string,stock_qty:int,is_available:bool,tags:list<array{tag_key:string,tag_value:string}>}> $products
     * @return list<array{product_id:int,score:int,justification:string,is_selected:bool}>
     */
    public function recommend(array $attrs, array $products, int $limit = 8): array
    {
        $ruleset = $this->activeRuleset();
        if ($ruleset === null) {
            return (new MockRecommendationEngine())->recommend($attrs, $products, $limit);
        }

        /** @var array<string, mixed> $rules */
        $rules = $ruleset['rules'];
        $builtins = is_array($rules['builtins'] ?? null) ? $rules['builtins'] : [];
        $matchWeights = is_array($rules['match_weights'] ?? null) ? $rules['match_weights'] : [];
        $excludes = is_array($rules['excludes'] ?? null) ? $rules['excludes'] : [];
        $minScore = (int) ($builtins['min_score'] ?? 2);

        $scored = [];
        foreach ($products as $product) {
            if (!empty($builtins['require_available']) && !(bool) ($product['is_available'] ?? true)) {
                continue;
            }
            if (!empty($builtins['require_stock']) && (int) ($product['stock_qty'] ?? 0) <= 0) {
                continue;
            }

            $tags = $this->tagMap($product['tags'] ?? []);
            if ($this->isExcludedByBuiltins($attrs, $tags, $builtins)) {
                continue;
            }
            if ($this->isExcludedByRules($attrs, $tags, $excludes)) {
                continue;
            }

            $reasons = [];
            $score = 0;

            foreach ($matchWeights as $rule) {
                if (!is_array($rule)) {
                    continue;
                }
                $hit = $this->matchWeight($attrs, $tags, $rule);
                if ($hit === null) {
                    continue;
                }
                $score += $hit['weight'];
                if ($hit['reason'] !== '') {
                    $reasons[] = $hit['reason'];
                }
            }

            if ($score < $minScore && $reasons === []) {
                continue;
            }
            if ($reasons === []) {
                $reasons[] = 'produto elegível com estoque disponível';
            }

            $scored[] = [
                'product_id' => (int) $product['id'],
                'score' => $score,
                'justification' => implode('; ', array_unique($reasons)),
                'is_selected' => true,
            ];
        }

        usort($scored, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, $limit);
    }

    public function ruleSetId(): ?int
    {
        $active = $this->activeRuleset();

        return $active !== null ? (int) $active['id'] : null;
    }

    /** @return array{id:int,code:string,title:string,rules:array<string,mixed>}|null */
    public function activeRuleset(): ?array
    {
        if (!$this->loaded) {
            $this->loaded = true;
            try {
                $stmt = $this->pdo->query(
                    'SELECT id, code, title, rules_json FROM recommendation_rule_sets
                     WHERE is_active = 1 ORDER BY id DESC LIMIT 1'
                );
                $row = $stmt ? $stmt->fetch() : false;
                if ($row !== false) {
                    $decoded = json_decode((string) $row['rules_json'], true);
                    if (is_array($decoded)) {
                        $this->active = [
                            'id' => (int) $row['id'],
                            'code' => (string) $row['code'],
                            'title' => (string) $row['title'],
                            'rules' => $decoded,
                        ];
                    }
                }
            } catch (\Throwable) {
                $this->active = null;
            }
        }

        return $this->active;
    }

    /** @return list<array<string, mixed>> */
    public function listRuleSets(): array
    {
        try {
            $stmt = $this->pdo->query(
                'SELECT id, code, title, is_active, created_at,
                        CHAR_LENGTH(rules_json) AS rules_bytes
                 FROM recommendation_rule_sets ORDER BY id DESC'
            );

            return $stmt ? ($stmt->fetchAll() ?: []) : [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @param array<string, string> $attrs
     * @param array<string, list<string>> $tags
     * @param array<string, mixed> $builtins
     */
    private function isExcludedByBuiltins(array $attrs, array $tags, array $builtins): bool
    {
        $species = $attrs['species'] ?? 'dog';
        $size = $attrs['size'] ?? 'medium';
        $noChicken = ($attrs['no_chicken'] ?? '') === 'true';

        if (!empty($builtins['species_gate']) && isset($tags['species'])) {
            if (!in_array($species, $tags['species'], true)) {
                return true;
            }
        }

        if (!empty($builtins['size_gate']) && isset($tags['size'])
            && !in_array('all', $tags['size'], true)
            && !in_array($size, $tags['size'], true)
            && count($tags['size']) > 0
        ) {
            return true;
        }

        if (!empty($builtins['exclude_if_tag']) && isset($tags['exclude_if'])) {
            foreach ($tags['exclude_if'] as $ex) {
                if ($ex === 'no_chicken' && $noChicken) {
                    return true;
                }
                if (($attrs[$ex] ?? '') === 'true') {
                    return true;
                }
            }
        }

        if (!empty($builtins['no_chicken_flavor']) && $noChicken
            && in_array('chicken', $tags['flavor'] ?? [], true)
        ) {
            return true;
        }

        if (!empty($builtins['dont_want_category'])) {
            foreach (['brinquedo', 'petisco', 'higiene', 'acessorio'] as $cat) {
                if (($attrs['dont_want_' . $cat] ?? '') === 'true'
                    && in_array($cat, $tags['category'] ?? [], true)
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param array<string, string> $attrs
     * @param array<string, list<string>> $tags
     * @param list<mixed> $excludes
     */
    private function isExcludedByRules(array $attrs, array $tags, array $excludes): bool
    {
        foreach ($excludes as $rule) {
            if (!is_array($rule)) {
                continue;
            }
            if (!$this->whenAttrsMatch($attrs, $rule)) {
                continue;
            }
            $tagKey = (string) ($rule['tag_key'] ?? '');
            if ($tagKey === '') {
                continue;
            }
            $values = $tags[$tagKey] ?? [];
            if (!empty($rule['attr_suffix_as_tag_value']) && isset($rule['attr_prefix'])) {
                $prefix = (string) $rule['attr_prefix'];
                $want = (string) ($rule['when_attr_value'] ?? 'true');
                foreach ($attrs as $ak => $av) {
                    if (!str_starts_with($ak, $prefix) || (string) $av !== $want) {
                        continue;
                    }
                    $suffix = substr($ak, strlen($prefix));
                    if ($suffix !== '' && in_array($suffix, $values, true)) {
                        return true;
                    }
                }
                continue;
            }
            $tagValue = (string) ($rule['tag_value'] ?? '');
            if ($tagValue !== '' && in_array($tagValue, $values, true)) {
                return true;
            }
            if (!empty($rule['tag_equals_attr']) && isset($rule['attr_key'])) {
                $attrVal = (string) ($attrs[(string) $rule['attr_key']] ?? '');
                if ($attrVal !== '' && in_array($attrVal, $values, true)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param array<string, string> $attrs
     * @param array<string, list<string>> $tags
     * @param array<string, mixed> $rule
     * @return array{weight:int,reason:string}|null
     */
    private function matchWeight(array $attrs, array $tags, array $rule): ?array
    {
        if (!$this->whenAttrsMatch($attrs, $rule)) {
            return null;
        }

        $weight = (int) ($rule['weight'] ?? 0);
        $reason = (string) ($rule['reason'] ?? '');
        $tagKey = (string) ($rule['tag_key'] ?? '');
        $values = $tagKey !== '' ? ($tags[$tagKey] ?? []) : [];

        if (!empty($rule['attr_suffix_as_tag_value']) && isset($rule['attr_prefix'])) {
            $prefix = (string) $rule['attr_prefix'];
            $want = (string) ($rule['when_attr_value'] ?? 'true');
            $matched = [];
            foreach ($attrs as $ak => $av) {
                if (!str_starts_with($ak, $prefix) || (string) $av !== $want) {
                    continue;
                }
                $suffix = substr($ak, strlen($prefix));
                if ($suffix !== '' && in_array($suffix, $values, true)) {
                    $matched[] = $suffix;
                }
            }
            if ($matched === []) {
                return null;
            }
            $reason = str_replace('{value}', implode(', ', $matched), $reason);

            return ['weight' => $weight * count($matched), 'reason' => $reason];
        }

        if (!empty($rule['tag_equals_attr']) && isset($rule['attr_key'])) {
            $attrKey = (string) $rule['attr_key'];
            $attrVal = (string) ($attrs[$attrKey] ?? '');
            if ($attrVal === '' || !in_array($attrVal, $values, true)) {
                return null;
            }
            $reason = str_replace(['{attr}', '{value}'], [$attrVal, $attrVal], $reason);

            return ['weight' => $weight, 'reason' => $reason];
        }

        if (isset($rule['tag_value'])) {
            if (!in_array((string) $rule['tag_value'], $values, true)) {
                return null;
            }

            return ['weight' => $weight, 'reason' => $reason];
        }

        // Fixed bonus when only attr conditions matched (no tag check)
        if ($tagKey === '') {
            return ['weight' => $weight, 'reason' => $reason];
        }

        return null;
    }

    /**
     * @param array<string, string> $attrs
     * @param array<string, mixed> $rule
     */
    private function whenAttrsMatch(array $attrs, array $rule): bool
    {
        if (isset($rule['when_attr']) && is_array($rule['when_attr'])) {
            foreach ($rule['when_attr'] as $k => $v) {
                if (($attrs[(string) $k] ?? '') !== (string) $v) {
                    return false;
                }
            }
        }
        if (isset($rule['attr_equals']) && is_array($rule['attr_equals'])) {
            foreach ($rule['attr_equals'] as $k => $v) {
                if (($attrs[(string) $k] ?? '') !== (string) $v) {
                    return false;
                }
            }
        }
        if (!empty($rule['tag_equals_attr']) && isset($rule['attr_key'])) {
            $attrVal = (string) ($attrs[(string) $rule['attr_key']] ?? '');
            if ($attrVal === '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<array{tag_key:string,tag_value:string}> $tags
     * @return array<string, list<string>>
     */
    private function tagMap(array $tags): array
    {
        $map = [];
        foreach ($tags as $tag) {
            $k = (string) ($tag['tag_key'] ?? '');
            $v = (string) ($tag['tag_value'] ?? '');
            if ($k === '' || $v === '') {
                continue;
            }
            $map[$k][] = $v;
        }

        return $map;
    }
}
