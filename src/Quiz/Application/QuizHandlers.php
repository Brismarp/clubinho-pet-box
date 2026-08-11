<?php

declare(strict_types=1);

namespace ClubinhoPet\Quiz\Application;

use ClubinhoPet\Quiz\Domain\AttributeMapper;
use ClubinhoPet\Quiz\Domain\MockRecommendationEngine;
use ClubinhoPet\Quiz\Infrastructure\Persistence\PdoQuizRepository;
use ClubinhoPet\Integrations\Domain\Notifier;
use ClubinhoPet\Platform\Domain\ActivityLogRepository;
use ClubinhoPet\Shared\Domain\DomainException;

final class QuizHandlers
{
    public function __construct(
        private PdoQuizRepository $repo,
        private AttributeMapper $mapper,
        private MockRecommendationEngine $engine,
        private ?Notifier $notifier = null,
        private ?ActivityLogRepository $logs = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function start(?string $existingToken = null): array
    {
        if ($existingToken) {
            $session = $this->repo->findSessionByToken($existingToken);
            if ($session !== null && ($session['status'] ?? '') === 'draft') {
                return $this->sessionPayload($session);
            }
        }
        $token = bin2hex(random_bytes(16));
        $versionId = $this->repo->activeVersionId();
        $this->repo->createSession($token, $versionId);
        $session = $this->repo->findSessionByToken($token);
        if ($session === null) {
            throw new DomainException('Failed to start quiz.');
        }

        return $this->sessionPayload($session);
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function saveStep(string $token, array $input): array
    {
        $session = $this->requireDraft($token);
        $step = (int) ($input['step'] ?? $session['current_step'] ?? 1);
        $stepKey = (string) ($input['step_key'] ?? '');
        $answers = $input['answers'] ?? [];
        if ($stepKey === '' || !is_array($answers)) {
            throw new DomainException('step_key and answers are required.');
        }
        foreach ($answers as $questionKey => $value) {
            $this->repo->upsertAnswer((int) $session['id'], $stepKey, (string) $questionKey, $value);
        }
        $this->repo->updateSessionStep((int) $session['id'], max(1, min(10, $step)));
        $fresh = $this->repo->findSessionByToken($token);

        return $this->sessionPayload($fresh ?? $session);
    }

    /** @return array<string, mixed> */
    public function getSession(string $token): array
    {
        $session = $this->repo->findSessionByToken($token);
        if ($session === null) {
            throw new DomainException('Quiz session not found.');
        }

        return $this->sessionPayload($session);
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function complete(string $token, array $input = []): array
    {
        $session = $this->requireDraft($token);
        $grouped = $this->repo->answersGrouped((int) $session['id']);

        // Merge final step answers if provided
        if (!empty($input['answers']) && is_array($input['answers'])) {
            $stepKey = (string) ($input['step_key'] ?? 'confirm');
            foreach ($input['answers'] as $q => $v) {
                $this->repo->upsertAnswer((int) $session['id'], $stepKey, (string) $q, $v);
                $grouped[$stepKey][(string) $q] = $v;
            }
        }

        $tutor = $grouped['tutor'] ?? [];
        $name = trim((string) ($tutor['name'] ?? ''));
        $email = trim((string) ($tutor['email'] ?? ''));
        $phone = trim((string) ($tutor['phone'] ?? ''));
        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException('Tutor name and valid email are required.');
        }

        $consentLgpd = !empty($grouped['confirm']['consent_lgpd']) || !empty($input['consent_lgpd']);
        if (!$consentLgpd) {
            throw new DomainException('LGPD consent is required.');
        }
        $consentMarketing = !empty($grouped['confirm']['consent_marketing']) || !empty($input['consent_marketing']);

        $customerId = $this->repo->findOrCreateCustomer(
            $name,
            $email,
            $phone !== '' ? $phone : null,
            $consentMarketing,
            true,
        );

        $petData = $grouped['pet'] ?? [];
        if (trim((string) ($petData['name'] ?? '')) === '') {
            throw new DomainException('Pet name is required.');
        }
        // attach restrictions into pet notes
        $restrictions = $grouped['restrictions'] ?? [];
        if (!empty($restrictions['notes'])) {
            $petData['health_notes'] = (string) $restrictions['notes'];
        }
        $avoid = $grouped['food']['avoid'] ?? [];
        if (is_array($avoid) && $avoid !== []) {
            $petData['dietary_restrictions'] = implode(', ', array_map('strval', $avoid));
        }
        $petId = $this->repo->createPet($customerId, $petData);

        $attrs = $this->mapper->map($grouped);
        $plan = $this->mapper->suggestPlan($attrs);
        $this->repo->replaceAttributes((int) $session['id'], $attrs);
        $this->repo->completeSession((int) $session['id'], $customerId, $petId, $plan);

        $products = $this->repo->productsWithTags();
        $items = $this->engine->recommend($attrs, $products, 8);
        $recId = $this->repo->createRecommendation((int) $session['id'], $petId, $plan, $items);

        $this->logs?->log(null, 'quiz.completed', 'quiz_sessions', (int) $session['id'], null, [
            'customer_id' => $customerId,
            'pet_id' => $petId,
            'plan' => $plan,
            'recommendation_id' => $recId,
        ]);
        $this->notifier?->send('email', $email, 'quiz.completed', [
            'plan' => $plan,
            'pet' => $petData['name'] ?? null,
            'session_id' => (int) $session['id'],
        ]);

        $fresh = $this->repo->findSessionByToken($token);

        return [
            'session' => $this->sessionPayload($fresh ?? $session),
            'suggested_plan' => $plan,
            'recommendation_id' => $recId,
            'items' => $items,
            'attributes' => $attrs,
            'checkout_url' => '/checkout?plan=' . rawurlencode($plan) . '&billing=monthly',
        ];
    }

    /** @return list<array<string, mixed>> */
    public function adminListSessions(): array
    {
        return $this->repo->listSessions();
    }

    /** @return array<string, mixed> */
    public function adminSessionDetail(int $id): array
    {
        $row = $this->repo->sessionDetail($id);
        if ($row === null) {
            throw new DomainException('Quiz not found.');
        }
        $row['answers'] = $this->repo->answersGrouped($id);
        $row['attributes'] = $this->repo->attributesForSession($id);
        $recs = array_values(array_filter(
            $this->repo->listRecommendations(),
            static fn (array $r): bool => (int) $r['session_id'] === $id,
        ));
        $row['recommendations'] = $recs;

        return $row;
    }

    /** @return list<array<string, mixed>> */
    public function adminListRecommendations(?string $status = null): array
    {
        return $this->repo->listRecommendations($status);
    }

    /** @return array<string, mixed> */
    public function adminRecommendationDetail(int $id): array
    {
        $row = $this->repo->recommendationDetail($id);
        if ($row === null) {
            throw new DomainException('Recommendation not found.');
        }

        return $row;
    }

    public function adminSetRecommendationStatus(int $id, string $status, ?int $actorUserId = null): void
    {
        if (!in_array($status, ['pending_review', 'approved', 'rejected'], true)) {
            throw new DomainException('Invalid status.');
        }
        $this->repo->setRecommendationStatus($id, $status);
        $this->logs?->log($actorUserId, 'recommendation.' . $status, 'box_recommendations', $id, null, [
            'status' => $status,
        ]);
        if ($status === 'approved') {
            $this->notifier?->send('log', 'ops@clubinhopetbox.local', 'recommendation.approved', [
                'recommendation_id' => $id,
            ]);
        }
    }

    public function adminToggleItem(int $itemId, bool $selected): void
    {
        $this->repo->setItemSelected($itemId, $selected);
    }

    /** @return list<array<string, mixed>> */
    public function adminListProducts(): array
    {
        return $this->repo->listProductsAdmin();
    }

    /** @param array<string, mixed> $data */
    public function adminSaveProduct(array $data, ?int $id = null): int
    {
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            throw new DomainException('Product name required.');
        }
        $data['name'] = $name;

        return $this->repo->saveProduct($data, $id);
    }

    /** @return array<string, mixed>|null */
    public function adminGetProduct(int $id): ?array
    {
        return $this->repo->findProduct($id);
    }

    /** @param array<string, mixed> $session */
    private function sessionPayload(array $session): array
    {
        $id = (int) $session['id'];

        return [
            'token' => (string) $session['public_token'],
            'status' => (string) $session['status'],
            'current_step' => (int) $session['current_step'],
            'suggested_plan_slug' => $session['suggested_plan_slug'] ?? null,
            'answers' => $this->repo->answersGrouped($id),
        ];
    }

    /** @return array<string, mixed> */
    private function requireDraft(string $token): array
    {
        $session = $this->repo->findSessionByToken($token);
        if ($session === null) {
            throw new DomainException('Quiz session not found.');
        }
        if (($session['status'] ?? '') !== 'draft') {
            throw new DomainException('Quiz already completed.');
        }

        return $session;
    }
}
