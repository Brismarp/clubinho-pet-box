<?php

declare(strict_types=1);

namespace ClubinhoPet\Quiz\Domain;

final class MockRecommendationEngine
{
    /**
     * @param array<string, string> $attrs
     * @param list<array{id:int,name:string,sku:?string,category:string,stock_qty:int,is_available:bool,tags:list<array{tag_key:string,tag_value:string}>}> $products
     * @return list<array{product_id:int,score:int,justification:string,is_selected:bool}>
     */
    public function recommend(array $attrs, array $products, int $limit = 8): array
    {
        $species = $attrs['species'] ?? 'dog';
        $size = $attrs['size'] ?? 'medium';
        $energy = $attrs['energy'] ?? 'medium';
        $destructive = ($attrs['destructive'] ?? '') === 'true';
        $noChicken = ($attrs['no_chicken'] ?? '') === 'true';
        $ageBand = $attrs['age_band'] ?? 'adult';

        $scored = [];
        foreach ($products as $product) {
            if (!(bool) ($product['is_available'] ?? true)) {
                continue;
            }
            if ((int) ($product['stock_qty'] ?? 0) <= 0) {
                continue;
            }

            $tags = $this->tagMap($product['tags'] ?? []);
            $reasons = [];
            $score = 0;

            // Species must match if product declares species
            if (isset($tags['species'])) {
                if (!in_array($species, $tags['species'], true)) {
                    continue;
                }
                $reasons[] = 'compatível com a espécie do pet';
                $score += 3;
            }

            if (isset($tags['size']) && !in_array($size, $tags['size'], true) && !in_array('all', $tags['size'], true)) {
                // soft miss for size unless exclusive
                if (count($tags['size']) > 0 && !in_array($size, $tags['size'], true)) {
                    continue;
                }
            } elseif (isset($tags['size']) && in_array($size, $tags['size'], true)) {
                $reasons[] = 'adequado ao porte ' . $size;
                $score += 2;
            }

            if (isset($tags['exclude_if'])) {
                $blocked = false;
                foreach ($tags['exclude_if'] as $ex) {
                    if ($ex === 'no_chicken' && $noChicken) {
                        $blocked = true;
                        break;
                    }
                    if (($attrs[$ex] ?? '') === 'true') {
                        $blocked = true;
                        break;
                    }
                }
                if ($blocked) {
                    continue;
                }
            }

            if ($noChicken && in_array('chicken', $tags['flavor'] ?? [], true)) {
                continue;
            }
            if ($noChicken && in_array('chicken', $tags['allergy_safe'] ?? [], true)) {
                $reasons[] = 'alternativa segura porque o tutor indicou restrição a frango';
                $score += 4;
            }

            if ($destructive && in_array('destructive', $tags['behavior'] ?? [], true)) {
                $reasons[] = 'brinquedo resistente porque o perfil indicou alta destrutividade';
                $score += 5;
            }

            if ($energy === 'high' && in_array('high', $tags['energy'] ?? [], true)) {
                $reasons[] = 'combina com alto nível de energia';
                $score += 3;
            }
            if ($energy === 'low' && in_array('low', $tags['energy'] ?? [], true)) {
                $reasons[] = 'combina com perfil mais calmo';
                $score += 2;
            }

            if ($ageBand === 'puppy' && in_array('puppy', $tags['age'] ?? [], true)) {
                $reasons[] = 'indicado para filhotes';
                $score += 3;
            }

            foreach (['brinquedo', 'petisco', 'higiene', 'acessorio'] as $cat) {
                if (($attrs['want_' . $cat] ?? '') === 'true' && in_array($cat, $tags['category'] ?? [], true)) {
                    $reasons[] = 'categoria desejada pelo tutor: ' . $cat;
                    $score += 2;
                }
                if (($attrs['dont_want_' . $cat] ?? '') === 'true' && in_array($cat, $tags['category'] ?? [], true)) {
                    continue 2;
                }
            }

            if (($attrs['interest_walk'] ?? '') === 'true' && in_array('walk', $tags['interest'] ?? [], true)) {
                $reasons[] = 'interesse em itens de passeio';
                $score += 2;
            }
            if (($attrs['interest_higiene'] ?? '') === 'true' && in_array('higiene', $tags['category'] ?? [], true)) {
                $reasons[] = 'interesse em higiene';
                $score += 2;
            }

            if ($score < 2) {
                // still allow generic matches with base species hit
                if ($reasons === []) {
                    continue;
                }
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
        $top = array_slice($scored, 0, $limit);
        // diversify categories lightly by keeping order after sort
        return $top;
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
