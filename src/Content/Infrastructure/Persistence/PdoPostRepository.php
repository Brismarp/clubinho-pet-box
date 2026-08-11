<?php

declare(strict_types=1);

namespace ClubinhoPet\Content\Infrastructure\Persistence;

use ClubinhoPet\Content\Domain\Post;
use ClubinhoPet\Content\Domain\PostRepository;
use PDO;

final class PdoPostRepository implements PostRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findPublished(int $limit = 50): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM blog_posts
             WHERE status = 'published'
             ORDER BY COALESCE(published_at, created_at) DESC
             LIMIT :limit"
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(static fn (array $row) => Post::fromRow($row), $stmt->fetchAll() ?: []);
    }

    public function findPublishedBySlug(string $slug): ?Post
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM blog_posts WHERE slug = :slug AND status = 'published' LIMIT 1"
        );
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();

        return $row ? Post::fromRow($row) : null;
    }

    public function findById(int $id): ?Post
    {
        $stmt = $this->pdo->prepare('SELECT * FROM blog_posts WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Post::fromRow($row) : null;
    }

    public function findBySlug(string $slug): ?Post
    {
        $stmt = $this->pdo->prepare('SELECT * FROM blog_posts WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch();

        return $row ? Post::fromRow($row) : null;
    }

    public function findAll(int $limit = 100): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM blog_posts ORDER BY updated_at DESC LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(static fn (array $row) => Post::fromRow($row), $stmt->fetchAll() ?: []);
    }

    public function save(Post $post): void
    {
        $data = [
            'slug' => $post->slug(),
            'title' => $post->title(),
            'excerpt' => $post->excerpt(),
            'body' => $post->body(),
            'cover_image' => $post->coverImage(),
            'status' => $post->status(),
            'published_at' => $post->publishedAt(),
            'seo_title' => $post->seoTitle(),
            'seo_description' => $post->seoDescription(),
        ];

        if ($post->id() === null) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO blog_posts
                (slug, title, excerpt, body, cover_image, status, published_at, seo_title, seo_description)
                VALUES
                (:slug, :title, :excerpt, :body, :cover_image, :status, :published_at, :seo_title, :seo_description)'
            );
            $stmt->execute($data);
            $post->assignId((int) $this->pdo->lastInsertId());

            return;
        }

        $data['id'] = $post->id();
        $stmt = $this->pdo->prepare(
            'UPDATE blog_posts SET
                slug = :slug,
                title = :title,
                excerpt = :excerpt,
                body = :body,
                cover_image = :cover_image,
                status = :status,
                published_at = :published_at,
                seo_title = :seo_title,
                seo_description = :seo_description,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $stmt->execute($data);
    }
}
