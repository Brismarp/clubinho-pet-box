<?php

declare(strict_types=1);

namespace ClubinhoPet\Fulfillment\Application;

use ClubinhoPet\Fulfillment\Domain\BoxRepository;
use ClubinhoPet\Shared\Domain\DomainException;

final class ShipBoxHandler
{
    public function __construct(private BoxRepository $boxes)
    {
    }

    /** @return array<string, mixed> */
    public function handle(int $boxId, ?string $tracking = null): array
    {
        $box = $this->boxes->findById($boxId);
        if ($box === null) {
            throw new DomainException('Box not found.');
        }
        $box->markShipped($tracking);
        $this->boxes->save($box);

        return $box->toArray();
    }
}
