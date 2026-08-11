<?php

declare(strict_types=1);

namespace ClubinhoPet\Subscription\Application;

use ClubinhoPet\Platform\Domain\ActivityLogRepository;
use ClubinhoPet\Shared\Domain\DomainException;
use ClubinhoPet\Subscription\Domain\SubscriptionRepository;

final class ChangeSubscriptionStatusHandler
{
    public function __construct(
        private SubscriptionRepository $subscriptions,
        private ActivityLogRepository $logs,
    ) {
    }

    /**
     * @return array{id:int,status:string}
     */
    public function handle(int $id, string $action, ?int $actorId = null, ?string $confirm = null): array
    {
        $sub = $this->subscriptions->findById($id);
        if ($sub === null) {
            throw new DomainException('Subscription not found.');
        }
        $before = $sub->status()->value;
        $action = strtolower(trim($action));

        match ($action) {
            'pause' => $sub->pause(),
            'cancel' => $this->cancel($sub, $confirm),
            'reactivate' => $sub->reactivate(),
            default => throw new DomainException('Invalid subscription action.'),
        };

        $this->subscriptions->updateStatus($sub);
        $this->logs->log($actorId, 'subscription.' . $action, 'subscription', $id, [
            'status' => $before,
        ], [
            'status' => $sub->status()->value,
        ]);

        return ['id' => $id, 'status' => $sub->status()->value];
    }

    private function cancel(\ClubinhoPet\Subscription\Domain\Subscription $sub, ?string $confirm): void
    {
        if (strtoupper(trim((string) $confirm)) !== 'CANCELAR') {
            throw new DomainException('Digite CANCELAR para confirmar o cancelamento.');
        }
        $sub->cancel();
    }
}
