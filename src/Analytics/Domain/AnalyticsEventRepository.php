<?php

declare(strict_types=1);

namespace ClubinhoPet\Analytics\Domain;

interface AnalyticsEventRepository
{
    /** @param list<AnalyticsEvent> $events */
    public function insertMany(array $events): int;

    /**
     * @return array{counts: array<string, int>, unique_visitors: int}
     */
    public function funnelSince(\DateTimeInterface $since): array;

    /**
     * @return list<array{path: string, views: int}>
     */
    public function topPathsSince(\DateTimeInterface $since, int $limit = 8): array;

    /**
     * @return list<array{plan_slug: string, clicks: int}>
     */
    public function checkoutByPlanSince(\DateTimeInterface $since): array;

    public function countEventSince(string $eventName, \DateTimeInterface $since): int;
}
