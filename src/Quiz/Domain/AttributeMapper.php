<?php

declare(strict_types=1);

namespace ClubinhoPet\Quiz\Domain;

final class AttributeMapper
{
    /**
     * @param array<string, array<string, mixed>> $answersByStep  step_key => question_key => decoded value
     * @return array<string, string>
     */
    public function map(array $answersByStep): array
    {
        $get = static function (string $step, string $key, mixed $default = null) use ($answersByStep): mixed {
            return $answersByStep[$step][$key] ?? $default;
        };

        $attrs = [];
        $species = (string) ($get('pet', 'species') ?? $get('pet', 'type') ?? 'dog');
        $attrs['species'] = $species === 'cat' ? 'cat' : 'dog';
        $size = (string) ($get('pet', 'size') ?? 'medium');
        $attrs['size'] = $size;
        $ageYears = (int) ($get('pet', 'age_years') ?? 0);
        $attrs['age_band'] = $ageYears < 1 ? 'puppy' : ($ageYears >= 8 ? 'senior' : 'adult');
        $sex = (string) ($get('pet', 'sex') ?? '');
        if ($sex !== '') {
            $attrs['sex'] = $sex;
        }

        $energy = (string) ($get('routine', 'energy') ?? 'medium');
        $attrs['energy'] = $energy;
        $alone = (string) ($get('routine', 'alone_time') ?? '');
        if ($alone !== '') {
            $attrs['alone_time'] = $alone;
        }

        $traits = $get('personality', 'traits', []);
        if (!is_array($traits)) {
            $traits = $traits !== null && $traits !== '' ? [$traits] : [];
        }
        foreach ($traits as $trait) {
            $t = (string) $trait;
            if ($t === '') {
                continue;
            }
            $attrs['behavior_' . $t] = 'true';
            if (in_array($t, ['destructive', 'destruidor'], true)) {
                $attrs['destructive'] = 'true';
            }
        }

        $toyPrefs = $get('toys', 'preferred', []);
        if (!is_array($toyPrefs)) {
            $toyPrefs = $toyPrefs ? [$toyPrefs] : [];
        }
        foreach ($toyPrefs as $pref) {
            $attrs['toy_' . (string) $pref] = 'true';
        }
        if (($get('toys', 'destroys') ?? false) === true || (string) ($get('toys', 'destroys') ?? '') === 'yes') {
            $attrs['destructive'] = 'true';
        }

        $flavors = $get('food', 'flavors', []);
        if (is_array($flavors)) {
            foreach ($flavors as $f) {
                $attrs['flavor_' . (string) $f] = 'true';
            }
        }
        $avoid = $get('food', 'avoid', []);
        if (!is_array($avoid)) {
            $avoid = $avoid ? [$avoid] : [];
        }
        foreach ($avoid as $a) {
            $key = (string) $a;
            $attrs['no_' . $key] = 'true';
            if ($key === 'chicken' || $key === 'frango') {
                $attrs['no_chicken'] = 'true';
            }
        }

        $allergies = $get('restrictions', 'allergies', []);
        if (!is_array($allergies)) {
            $allergies = $allergies ? [$allergies] : [];
        }
        foreach ($allergies as $al) {
            $attrs['allergy_' . (string) $al] = 'true';
            if ((string) $al === 'chicken' || (string) $al === 'frango') {
                $attrs['no_chicken'] = 'true';
            }
        }
        $attrs['restrictions_note'] = trim((string) ($get('restrictions', 'notes') ?? '')) !== '' ? 'true' : 'false';

        $hygiene = $get('care', 'interests', []);
        if (!is_array($hygiene)) {
            $hygiene = $hygiene ? [$hygiene] : [];
        }
        foreach ($hygiene as $h) {
            $attrs['interest_' . (string) $h] = 'true';
        }

        $want = $get('preferences', 'want', []);
        if (is_array($want)) {
            foreach ($want as $w) {
                $attrs['want_' . (string) $w] = 'true';
            }
        }
        $dont = $get('preferences', 'dont_want', []);
        if (is_array($dont)) {
            foreach ($dont as $d) {
                $attrs['dont_want_' . (string) $d] = 'true';
            }
        }
        $variety = (string) ($get('preferences', 'variety') ?? 'medium');
        $attrs['variety'] = $variety;

        return $attrs;
    }

    /** @param array<string, string> $attrs */
    public function suggestPlan(array $attrs): string
    {
        $score = 0;
        if (($attrs['energy'] ?? '') === 'high') {
            $score += 2;
        }
        if (($attrs['destructive'] ?? '') === 'true') {
            $score += 2;
        }
        if (($attrs['variety'] ?? '') === 'high') {
            $score += 2;
        }
        if (($attrs['want_deluxe'] ?? '') === 'true' || ($attrs['want_premium'] ?? '') === 'true') {
            $score += 3;
        }
        if ($score >= 5) {
            return 'deluxe';
        }
        if ($score >= 2) {
            return 'love';
        }

        return 'happy';
    }
}
