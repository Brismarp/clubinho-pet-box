<?php

declare(strict_types=1);

namespace ClubinhoPet\Fulfillment\Application;

use ClubinhoPet\Fulfillment\Domain\Box;
use ClubinhoPet\Fulfillment\Domain\BoxRepository;
use ClubinhoPet\Shared\Domain\DomainException;

final class CreateBoxHandler
{
    public function __construct(private BoxRepository $boxes)
    {
    }

    /** @return array<string, mixed> */
    public function handle(int $subscriptionId, ?int $boxNumber = null, ?string $notes = null): array
    {
        if ($subscriptionId <= 0) {
            throw new DomainException('Invalid subscription id.');
        }

        $box = Box::create($subscriptionId, $boxNumber, $notes);
        $this->boxes->save($box);

        return $box->toArray();
    }
}
