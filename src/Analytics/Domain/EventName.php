<?php

declare(strict_types=1);

namespace ClubinhoPet\Analytics\Domain;

final class EventName
{
    public const ALLOWED = [
        'page_view',
        'cta_click',
        'quiz_start',
        'quiz_step',
        'quiz_complete',
        'checkout_click',
        'subscribe_submit',
        'lead_submit',
        'contact_submit',
    ];

    public static function isAllowed(string $name): bool
    {
        return in_array($name, self::ALLOWED, true);
    }
}
