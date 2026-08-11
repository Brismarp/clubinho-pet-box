<?php

declare(strict_types=1);

namespace ClubinhoPet\Content\Domain;

interface TestimonialRepository
{
    /** @return list<Testimonial> */
    public function findPublished(): array;

    /** @return list<Testimonial> */
    public function findAll(): array;

    public function findById(int $id): ?Testimonial;

    public function save(Testimonial $item): void;
}
