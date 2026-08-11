<?php

declare(strict_types=1);

namespace ClubinhoPet\Catalog\Domain;

interface ProductRepository
{
    /** @return list<Product> */
    public function findAvailable(): array;
}
