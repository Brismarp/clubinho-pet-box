<?php

declare(strict_types=1);

namespace ClubinhoPet\Content\Domain;

interface PostRepository
{
    /** @return list<Post> */
    public function findPublished(int $limit = 50): array;

    public function findPublishedBySlug(string $slug): ?Post;

    public function findById(int $id): ?Post;

    public function findBySlug(string $slug): ?Post;

    /** @return list<Post> */
    public function findAll(int $limit = 100): array;

    public function save(Post $post): void;
}
