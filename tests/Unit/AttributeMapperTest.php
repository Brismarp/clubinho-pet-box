<?php

declare(strict_types=1);

use ClubinhoPet\Quiz\Domain\AttributeMapper;

it('maps ambiente, play_freq, materials and toy size', function () {
    $mapper = new AttributeMapper();
    $attrs = $mapper->map([
        'pet' => ['species' => 'dog', 'size' => 'medium', 'age_years' => 3],
        'routine' => [
            'energy' => 'high',
            'ambiente' => 'apartment',
            'play_freq' => 'daily',
        ],
        'toys' => [
            'preferred' => ['chew'],
            'materials' => ['rubber', 'rope'],
            'size' => 'small',
        ],
        'preferences' => ['variety' => 'medium'],
        'restrictions' => [],
        'care' => [],
        'food' => [],
        'personality' => [],
    ]);

    expect($attrs['ambiente'])->toBe('apartment')
        ->and($attrs['play_freq'])->toBe('daily')
        ->and($attrs['toy_material_rubber'])->toBe('true')
        ->and($attrs['toy_material_rope'])->toBe('true')
        ->and($attrs['toy_size'])->toBe('small');
});
