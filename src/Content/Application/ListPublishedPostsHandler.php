<?php

declare(strict_types=1);

namespace ClubinhoPet\Content\Application;

use ClubinhoPet\Content\Domain\PostRepository;

final class ListPublishedPostsHandler
{
    public function __construct(private PostRepository $posts)
    {
    }

    /** @return list<array<string, mixed>> */
    public function handle(int $limit = 50): array
    {
        return array_map(static fn ($p) => $p->toArray(), $this->posts->findPublished($limit));
    }
}
