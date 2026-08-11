<?php

declare(strict_types=1);

namespace ClubinhoPet\Content\Application;

use ClubinhoPet\Content\Domain\PostRepository;
use ClubinhoPet\Shared\Domain\DomainException;

final class GetPublishedPostBySlugHandler
{
    public function __construct(private PostRepository $posts)
    {
    }

    /** @return array<string, mixed> */
    public function handle(string $slug): array
    {
        $post = $this->posts->findPublishedBySlug($slug);
        if ($post === null) {
            throw new DomainException('Post not found.');
        }

        return $post->toArray();
    }
}
