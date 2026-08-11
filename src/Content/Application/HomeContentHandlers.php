<?php

declare(strict_types=1);

namespace ClubinhoPet\Content\Application;

use ClubinhoPet\Content\Domain\FaqItem;
use ClubinhoPet\Content\Domain\FaqItemRepository;
use ClubinhoPet\Content\Domain\HappyMedia;
use ClubinhoPet\Content\Domain\HappyMediaRepository;
use ClubinhoPet\Content\Domain\Testimonial;
use ClubinhoPet\Content\Domain\TestimonialRepository;
use ClubinhoPet\Shared\Domain\DomainException;

final class HomeContentHandlers
{
    public function __construct(
        private TestimonialRepository $testimonials,
        private HappyMediaRepository $happyMedia,
        private FaqItemRepository $faqs,
    ) {
    }

    /** @return list<array<string, mixed>> */
    public function publishedTestimonials(): array
    {
        return array_map(static fn ($t) => $t->toArray(), $this->testimonials->findPublished());
    }

    /** @return list<array<string, mixed>> */
    public function allTestimonials(): array
    {
        return array_map(static fn ($t) => $t->toArray(), $this->testimonials->findAll());
    }

    /** @return list<array<string, mixed>> */
    public function approvedHappyMedia(): array
    {
        return array_map(static fn ($t) => $t->toArray(), $this->happyMedia->findApproved());
    }

    /** @return list<array<string, mixed>> */
    public function allHappyMedia(?string $status = null): array
    {
        $items = $status !== null && $status !== ''
            ? $this->happyMedia->findByStatus($status)
            : $this->happyMedia->findAll();

        return array_map(static fn ($t) => $t->toArray(), $items);
    }

    public function setHappyMediaStatus(int $id, string $status): void
    {
        if (!in_array($status, ['pending', 'approved', 'rejected'], true)) {
            throw new DomainException('Invalid media status.');
        }
        $item = $this->happyMedia->findById($id);
        if ($item === null) {
            throw new DomainException('Media not found.');
        }
        $item->apply(
            $item->type(),
            $item->petName(),
            $status,
            $item->mediaPath(),
            $item->mediaUrl(),
            $item->tutorHandle(),
            $item->orientation(),
            $item->displayOrder(),
        );
        $this->happyMedia->save($item);
    }

    /** @return list<array<string, mixed>> */
    public function publishedFaqs(): array
    {
        return array_map(static fn ($t) => $t->toArray(), $this->faqs->findPublished());
    }

    /** @return list<array<string, mixed>> */
    public function allFaqs(): array
    {
        return array_map(static fn ($t) => $t->toArray(), $this->faqs->findAll());
    }

    /** @param array<string, mixed> $input */
    public function saveTestimonial(array $input, ?int $id = null): array
    {
        $name = trim((string) ($input['name'] ?? ''));
        $quote = trim((string) ($input['quote'] ?? ''));
        $status = (string) ($input['status'] ?? 'pending');
        $pet = $this->nullable($input['pet_name'] ?? null);
        $photo = $this->nullable($input['photo_path'] ?? null);
        $order = (int) ($input['display_order'] ?? 0);

        if ($id !== null) {
            $item = $this->testimonials->findById($id);
            if ($item === null) {
                throw new DomainException('Testimonial not found.');
            }
            $item->apply($name, $quote, $status, $pet, $photo, $order);
            $this->testimonials->save($item);
            return $item->toArray();
        }

        $item = new Testimonial(null, $name, $quote, $status, $pet, $photo, $order);
        $this->testimonials->save($item);
        return $item->toArray();
    }

    /** @param array<string, mixed> $input */
    public function saveHappyMedia(array $input, ?int $id = null): array
    {
        $type = (string) ($input['type'] ?? 'image');
        $pet = trim((string) ($input['pet_name'] ?? ''));
        $status = (string) ($input['status'] ?? 'pending');
        $path = $this->nullable($input['media_path'] ?? null);
        $url = $this->nullable($input['media_url'] ?? null);
        $handle = $this->nullable($input['tutor_handle'] ?? null);
        $orientation = (string) ($input['orientation'] ?? 'landscape');
        $order = (int) ($input['display_order'] ?? 0);

        if ($id !== null) {
            $item = $this->happyMedia->findById($id);
            if ($item === null) {
                throw new DomainException('Media not found.');
            }
            $item->apply($type, $pet, $status, $path, $url, $handle, $orientation, $order);
            $this->happyMedia->save($item);
            return $item->toArray();
        }

        $item = new HappyMedia(null, $type, $pet, $status, $path, $url, $handle, $orientation, $order);
        $this->happyMedia->save($item);
        return $item->toArray();
    }

    /** @param array<string, mixed> $input */
    public function saveFaq(array $input, ?int $id = null): array
    {
        $q = trim((string) ($input['question'] ?? ''));
        $a = trim((string) ($input['answer'] ?? ''));
        $order = (int) ($input['display_order'] ?? 0);
        $published = isset($input['is_published']) && (string) $input['is_published'] !== '0';

        if ($id !== null) {
            $item = $this->faqs->findById($id);
            if ($item === null) {
                throw new DomainException('FAQ not found.');
            }
            $item->apply($q, $a, $order, $published);
            $this->faqs->save($item);
            return $item->toArray();
        }

        $item = new FaqItem(null, $q, $a, $order, $published);
        $this->faqs->save($item);
        return $item->toArray();
    }

    private function nullable(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $s = trim((string) $value);
        return $s === '' ? null : $s;
    }
}
