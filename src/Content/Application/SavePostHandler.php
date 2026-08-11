<?php

declare(strict_types=1);

namespace ClubinhoPet\Content\Application;

use ClubinhoPet\Content\Domain\Post;
use ClubinhoPet\Content\Domain\PostRepository;
use ClubinhoPet\Shared\Domain\DomainException;

final class SavePostHandler
{
    public function __construct(private PostRepository $posts)
    {
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function handle(array $input, ?int $id = null): array
    {
        $slug = self::slugify((string) ($input['slug'] ?? $input['title'] ?? ''));
        $title = trim((string) ($input['title'] ?? ''));
        $body = trim((string) ($input['body'] ?? ''));
        $status = (string) ($input['status'] ?? 'draft');
        $excerpt = self::nullable($input['excerpt'] ?? null);
        $cover = self::nullable($input['cover_image'] ?? null);
        $seoTitle = self::nullable($input['seo_title'] ?? null);
        $seoDescription = self::nullable($input['seo_description'] ?? null);
        $publishedAt = self::nullable($input['published_at'] ?? null);

        if ($id !== null) {
            $post = $this->posts->findById($id);
            if ($post === null) {
                throw new DomainException('Post not found.');
            }
            $existing = $this->posts->findBySlug($slug);
            if ($existing !== null && $existing->id() !== $id) {
                throw new DomainException('Slug already in use.');
            }
            $post->apply($slug, $title, $body, $status, $excerpt, $cover, $publishedAt, $seoTitle, $seoDescription);
            $this->posts->save($post);

            return $post->toArray();
        }

        if ($this->posts->findBySlug($slug) !== null) {
            throw new DomainException('Slug already in use.');
        }

        if ($status === 'published' && ($publishedAt === null || $publishedAt === '')) {
            $publishedAt = date('Y-m-d H:i:s');
        }

        $post = new Post(
            null,
            $slug,
            $title,
            $body,
            $status,
            $excerpt,
            $cover,
            $publishedAt,
            $seoTitle,
            $seoDescription,
        );
        $this->posts->save($post);

        return $post->toArray();
    }

    public static function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
        $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
        $text = trim($text, '-');

        return $text !== '' ? $text : 'post';
    }

    private static function nullable(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $s = trim((string) $value);

        return $s === '' ? null : $s;
    }
}
