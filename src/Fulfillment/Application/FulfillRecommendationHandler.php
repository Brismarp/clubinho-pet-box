<?php

declare(strict_types=1);

namespace ClubinhoPet\Fulfillment\Application;

use ClubinhoPet\Inventory\Application\StockLedger;
use ClubinhoPet\Platform\Domain\ActivityLogRepository;
use ClubinhoPet\Shared\Domain\DomainException;
use PDO;

final class FulfillRecommendationHandler
{
    public function __construct(
        private PDO $pdo,
        private StockLedger $stock,
        private ?ActivityLogRepository $logs = null,
    ) {
    }

    /** @return array{box_id:int,subscription_id:int,items:int} */
    public function handle(int $recommendationId, ?int $actorUserId = null): array
    {
        $recStmt = $this->pdo->prepare(
            'SELECT r.*, qs.customer_id, qs.suggested_plan_slug AS session_plan,
                    p.name AS pet_name, p.species AS pet_species, p.subscription_id AS pet_subscription_id,
                    c.email AS customer_email, c.name AS customer_name
             FROM box_recommendations r
             INNER JOIN quiz_sessions qs ON qs.id = r.session_id
             LEFT JOIN pets p ON p.id = r.pet_id
             LEFT JOIN customers c ON c.id = qs.customer_id
             WHERE r.id = ?'
        );
        $recStmt->execute([$recommendationId]);
        $rec = $recStmt->fetch(PDO::FETCH_ASSOC);
        if ($rec === false) {
            throw new DomainException('Recommendation not found.');
        }
        if ((string) $rec['status'] !== 'approved') {
            throw new DomainException('Recommendation must be approved before generating a box.');
        }

        $existing = $this->pdo->prepare('SELECT id FROM boxes WHERE recommendation_id = ? LIMIT 1');
        $existing->execute([$recommendationId]);
        if ($existing->fetchColumn()) {
            throw new DomainException('A box was already generated for this recommendation.');
        }

        $itemsStmt = $this->pdo->prepare(
            'SELECT i.*, pr.name AS product_name, pr.stock_qty
             FROM box_recommendation_items i
             INNER JOIN products pr ON pr.id = i.product_id
             WHERE i.recommendation_id = ? AND i.is_selected = 1'
        );
        $itemsStmt->execute([$recommendationId]);
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        if ($items === []) {
            throw new DomainException('No selected products to fulfill.');
        }

        foreach ($items as $item) {
            if ((int) $item['stock_qty'] < 1) {
                throw new DomainException('Insufficient stock for product #' . (int) $item['product_id']);
            }
        }

        $subscriptionId = $this->resolveSubscriptionId($rec);

        $this->pdo->beginTransaction();
        try {
            $insBox = $this->pdo->prepare(
                'INSERT INTO boxes (subscription_id, recommendation_id, box_number, send_date, status, contents, notes)
                 VALUES (?, ?, NULL, CURDATE(), \'preparing\', ?, ?)'
            );
            $contents = array_map(static fn (array $i): array => [
                'product_id' => (int) $i['product_id'],
                'name' => (string) $i['product_name'],
                'qty' => 1,
                'justification' => (string) ($i['justification'] ?? ''),
            ], $items);
            $insBox->execute([
                $subscriptionId,
                $recommendationId,
                json_encode($contents, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'Generated from recommendation #' . $recommendationId,
            ]);
            $boxId = (int) $this->pdo->lastInsertId();

            $insItem = $this->pdo->prepare(
                'INSERT INTO box_items (box_id, product_id, qty, justification, source)
                 VALUES (?, ?, 1, ?, \'recommendation\')'
            );
            foreach ($items as $item) {
                $insItem->execute([
                    $boxId,
                    (int) $item['product_id'],
                    (string) ($item['justification'] ?? ''),
                ]);
            }

            $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e instanceof DomainException ? $e : new DomainException($e->getMessage());
        }

        // Stock reserves outside the box transaction (ledger has its own txn)
        foreach ($items as $item) {
            $this->stock->move(
                (int) $item['product_id'],
                -1,
                'reserve',
                $actorUserId,
                'boxes',
                $boxId,
                'reserve for recommendation #' . $recommendationId,
            );
        }

        $this->logs?->log($actorUserId, 'recommendation.fulfill', 'boxes', $boxId, null, [
            'recommendation_id' => $recommendationId,
            'subscription_id' => $subscriptionId,
            'items' => count($items),
        ]);

        return [
            'box_id' => $boxId,
            'subscription_id' => $subscriptionId,
            'items' => count($items),
        ];
    }

    /** @param array<string, mixed> $rec */
    private function resolveSubscriptionId(array $rec): int
    {
        if (!empty($rec['pet_subscription_id'])) {
            return (int) $rec['pet_subscription_id'];
        }

        $email = (string) ($rec['customer_email'] ?? '');
        if ($email !== '') {
            $find = $this->pdo->prepare(
                'SELECT id FROM subscriptions WHERE email = ? ORDER BY id DESC LIMIT 1'
            );
            $find->execute([$email]);
            $id = $find->fetchColumn();
            if ($id) {
                return (int) $id;
            }
        }

        $planSlug = (string) ($rec['suggested_plan_slug'] ?: $rec['session_plan'] ?: 'love');
        $plan = $this->pdo->prepare('SELECT id FROM plans WHERE slug = ? LIMIT 1');
        $plan->execute([$planSlug]);
        $planId = $plan->fetchColumn();
        if (!$planId) {
            $planId = $this->pdo->query('SELECT id FROM plans ORDER BY id ASC LIMIT 1')->fetchColumn();
        }
        if (!$planId) {
            throw new DomainException('No plan available to create subscription stub.');
        }

        $ins = $this->pdo->prepare(
            'INSERT INTO subscriptions (plan_id, email, pet_name, pet_type, status, start_date, notes)
             VALUES (?, ?, ?, ?, \'pending\', CURDATE(), ?)'
        );
        $ins->execute([
            (int) $planId,
            $email !== '' ? $email : 'unknown@clubinhopetbox.local',
            (string) ($rec['pet_name'] ?? 'Pet'),
            (string) ($rec['pet_species'] ?? 'dog'),
            'Stub from recommendation #' . (int) $rec['id'],
        ]);
        $subId = (int) $this->pdo->lastInsertId();

        if (!empty($rec['pet_id'])) {
            $this->pdo->prepare('UPDATE pets SET subscription_id = ? WHERE id = ? AND subscription_id IS NULL')
                ->execute([$subId, (int) $rec['pet_id']]);
        }

        return $subId;
    }
}
