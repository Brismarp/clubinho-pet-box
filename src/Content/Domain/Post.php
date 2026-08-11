<?php

declare(strict_types=1);

namespace ClubinhoPet\Content\Domain;

use ClubinhoPet\Shared\Domain\DomainException;

final class Post
{
    public function __construct(
        private ?int $id,
        private string $slug,
        private string $title,
        private string $body,
        private string $status = 'draft',
        private ?string $excerpt = null,
        private ?string $coverImage = null,
        private ?string $publishedAt = null,
        private ?string $seoTitle = null,
        private ?string $seoDescription = null,
        private ?string $createdAt = null,
        private ?string $updatedAt = null,
    ) {
        if (trim($slug) === '' || trim($title) === '' || trim($body) === '') {
            throw new DomainException('Slug, title and body are required.');
        }
        if (!in_array($status, ['draft', 'published'], true)) {
            throw new DomainException('Invalid post status.');
        }
    }

    /** @param array<string, mixed> $row */
    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (string) $row['slug'],
            (string) $row['title'],
            (string) $row['body'],
            (string) ($row['status'] ?? 'draft'),
            isset($row['excerpt']) ? (string) $row['excerpt'] : null,
            isset($row['cover_image']) && $row['cover_image'] !== '' ? (string) $row['cover_image'] : null,
            isset($row['published_at']) && $row['published_at'] !== '' ? (string) $row['published_at'] : null,
            isset($row['seo_title']) && $row['seo_title'] !== '' ? (string) $row['seo_title'] : null,
            isset($row['seo_description']) && $row['seo_description'] !== '' ? (string) $row['seo_description'] : null,
            isset($row['created_at']) ? (string) $row['created_at'] : null,
            isset($row['updated_at']) ? (string) $row['updated_at'] : null,
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function excerpt(): ?string
    {
        return $this->excerpt;
    }

    public function coverImage(): ?string
    {
        return $this->coverImage;
    }

    public function publishedAt(): ?string
    {
        return $this->publishedAt;
    }

    public function seoTitle(): ?string
    {
        return $this->seoTitle;
    }

    public function seoDescription(): ?string
    {
        return $this->seoDescription;
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function apply(
        string $slug,
        string $title,
        string $body,
        string $status,
        ?string $excerpt,
        ?string $coverImage,
        ?string $publishedAt,
        ?string $seoTitle,
        ?string $seoDescription,
    ): void {
        if (trim($slug) === '' || trim($title) === '' || trim($body) === '') {
            throw new DomainException('Slug, title and body are required.');
        }
        if (!in_array($status, ['draft', 'published'], true)) {
            throw new DomainException('Invalid post status.');
        }
        $this->slug = $slug;
        $this->title = $title;
        $this->body = $body;
        $this->status = $status;
        $this->excerpt = $excerpt;
        $this->coverImage = $coverImage;
        $this->publishedAt = $publishedAt;
        $this->seoTitle = $seoTitle;
        $this->seoDescription = $seoDescription;
        if ($status === 'published' && ($this->publishedAt === null || $this->publishedAt === '')) {
            $this->publishedAt = date('Y-m-d H:i:s');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'cover_image' => $this->coverImage,
            'status' => $this->status,
            'published_at' => $this->publishedAt,
            'seo_title' => $this->seoTitle,
            'seo_description' => $this->seoDescription,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
