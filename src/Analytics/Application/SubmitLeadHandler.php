<?php

declare(strict_types=1);

namespace ClubinhoPet\Analytics\Application;

use ClubinhoPet\Analytics\Domain\Lead;
use ClubinhoPet\Analytics\Domain\LeadRepository;
use ClubinhoPet\Shared\Domain\Email;

final class SubmitLeadHandler
{
    public function __construct(private LeadRepository $leads)
    {
    }

    /**
     * @return array{id: int|null, email: string}
     */
    public function handle(
        string $name,
        string $email,
        string $phone,
        string $petType,
        ?string $city = null,
        ?string $visitorId = null,
        bool $acceptNewsletter = false,
    ): array {
        $lead = Lead::capture(
            $name,
            Email::fromString($email),
            $phone,
            $petType,
            $city,
            $visitorId,
            $acceptNewsletter,
        );
        $this->leads->save($lead);

        return ['id' => $lead->id(), 'email' => $lead->email()->value()];
    }
}
