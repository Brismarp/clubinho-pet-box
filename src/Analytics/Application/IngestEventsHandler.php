<?php

declare(strict_types=1);

namespace ClubinhoPet\Analytics\Application;

use ClubinhoPet\Analytics\Domain\AnalyticsEvent;
use ClubinhoPet\Analytics\Domain\AnalyticsEventRepository;
use ClubinhoPet\Analytics\Domain\EventName;
use ClubinhoPet\Shared\Domain\DomainException;

final class IngestEventsHandler
{
    private const MAX_BATCH = 40;

    public function __construct(private AnalyticsEventRepository $events)
    {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{accepted: int, skipped: int}
     */
    public function handle(array $payload, ?string $ipHash = null, ?string $userAgent = null): array
    {
        $visitorId = trim((string) ($payload['visitor_id'] ?? ''));
        $sessionId = trim((string) ($payload['session_id'] ?? ''));
        $rawEvents = $payload['events'] ?? null;

        if ($visitorId === '' || $sessionId === '' || !is_array($rawEvents)) {
            throw new DomainException('visitor_id, session_id and events[] are required.');
        }
        if (strlen($visitorId) > 64 || strlen($sessionId) > 64) {
            throw new DomainException('Invalid visitor or session id.');
        }
        if (count($rawEvents) > self::MAX_BATCH) {
            throw new DomainException('Too many events in batch.');
        }

        $batch = [];
        $skipped = 0;
        foreach ($rawEvents as $item) {
            if (!is_array($item)) {
                $skipped++;
                continue;
            }
            $name = (string) ($item['event_name'] ?? $item['name'] ?? '');
            if (!EventName::isAllowed($name)) {
                $skipped++;
                continue;
            }

            $props = $item['properties'] ?? null;
            if ($props !== null && !is_array($props)) {
                $props = null;
            }

            $batch[] = AnalyticsEvent::create(
                visitorId: $visitorId,
                sessionId: $sessionId,
                eventName: $name,
                path: $this->nullableString($item['path'] ?? null, 500),
                planSlug: $this->nullableString($item['plan_slug'] ?? null, 100),
                email: $this->nullableString($item['email'] ?? null, 255),
                properties: $props,
                referrer: $this->nullableString($item['referrer'] ?? null, 1000),
                utmSource: $this->nullableString($item['utm_source'] ?? null, 255),
                utmMedium: $this->nullableString($item['utm_medium'] ?? null, 255),
                utmCampaign: $this->nullableString($item['utm_campaign'] ?? null, 255),
                ipHash: $ipHash,
                userAgent: $userAgent !== null ? substr($userAgent, 0, 500) : null,
            );
        }

        return [
            'accepted' => $this->events->insertMany($batch),
            'skipped' => $skipped,
        ];
    }

    /**
     * @param array<string, mixed>|null $properties
     */
    public function recordServer(
        string $eventName,
        ?string $path = null,
        ?string $planSlug = null,
        ?string $email = null,
        ?array $properties = null,
        ?string $visitorId = null,
        ?string $sessionId = null,
        ?string $ipHash = null,
        ?string $userAgent = null,
    ): void {
        if (!EventName::isAllowed($eventName)) {
            return;
        }
        $this->events->insertMany([
            AnalyticsEvent::create(
                visitorId: $visitorId ?: ('srv-' . bin2hex(random_bytes(8))),
                sessionId: $sessionId ?: ('srv-' . bin2hex(random_bytes(8))),
                eventName: $eventName,
                path: $path,
                planSlug: $planSlug,
                email: $email,
                properties: $properties,
                ipHash: $ipHash,
                userAgent: $userAgent !== null ? substr($userAgent, 0, 500) : null,
            ),
        ]);
    }

    private function nullableString(mixed $value, int $max): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $s = trim((string) $value);

        return $s === '' ? null : substr($s, 0, $max);
    }
}
