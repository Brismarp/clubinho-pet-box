<?php

declare(strict_types=1);

namespace ClubinhoPet\Content\Application;

use ClubinhoPet\Content\Domain\PostRepository;
use ClubinhoPet\Shared\Domain\DomainException;

final class GetAdminPostHandler
{
    public function __construct(private PostRepository $posts)
    {
    }

    /** @return array<string, mixed> */
    public function handle(int $id): array
    {
        $post = $this->posts->findById($id);
        if ($post === null) {
            throw new DomainException('Post not found.');
        }

        return $post->toArray();
    }
}
