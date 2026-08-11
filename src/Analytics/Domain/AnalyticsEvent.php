<?php

declare(strict_types=1);

namespace ClubinhoPet\Analytics\Domain;

final class AnalyticsEvent
{
    /**
     * @param array<string, mixed>|null $properties
     */
    public function __construct(
        private ?int $id,
        private string $visitorId,
        private string $sessionId,
        private string $eventName,
        private ?string $path = null,
        private ?string $planSlug = null,
        private ?string $email = null,
        private ?array $properties = null,
        private ?string $referrer = null,
        private ?string $utmSource = null,
        private ?string $utmMedium = null,
        private ?string $utmCampaign = null,
        private ?string $ipHash = null,
        private ?string $userAgent = null,
        private ?string $createdAt = null,
    ) {
    }

    /**
     * @param array<string, mixed>|null $properties
     */
    public static function create(
        string $visitorId,
        string $sessionId,
        string $eventName,
        ?string $path = null,
        ?string $planSlug = null,
        ?string $email = null,
        ?array $properties = null,
        ?string $referrer = null,
        ?string $utmSource = null,
        ?string $utmMedium = null,
        ?string $utmCampaign = null,
        ?string $ipHash = null,
        ?string $userAgent = null,
    ): self {
        return new self(
            null,
            $visitorId,
            $sessionId,
            $eventName,
            $path,
            $planSlug,
            $email,
            $properties,
            $referrer,
            $utmSource,
            $utmMedium,
            $utmCampaign,
            $ipHash,
            $userAgent,
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function visitorId(): string
    {
        return $this->visitorId;
    }

    public function sessionId(): string
    {
        return $this->sessionId;
    }

    public function eventName(): string
    {
        return $this->eventName;
    }

    public function path(): ?string
    {
        return $this->path;
    }

    public function planSlug(): ?string
    {
        return $this->planSlug;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    /** @return array<string, mixed>|null */
    public function properties(): ?array
    {
        return $this->properties;
    }

    public function referrer(): ?string
    {
        return $this->referrer;
    }

    public function utmSource(): ?string
    {
        return $this->utmSource;
    }

    public function utmMedium(): ?string
    {
        return $this->utmMedium;
    }

    public function utmCampaign(): ?string
    {
        return $this->utmCampaign;
    }

    public function ipHash(): ?string
    {
        return $this->ipHash;
    }

    public function userAgent(): ?string
    {
        return $this->userAgent;
    }

    public function createdAt(): ?string
    {
        return $this->createdAt;
    }
}
