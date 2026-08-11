<?php

declare(strict_types=1);

namespace ClubinhoPet\Quiz\Infrastructure\Persistence;

use PDO;

final class PdoQuizRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function activeVersionId(): int
    {
        $stmt = $this->pdo->query("SELECT id FROM quiz_versions WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
        $id = $stmt->fetchColumn();
        if ($id === false) {
            throw new \RuntimeException('No active quiz version.');
        }

        return (int) $id;
    }

    public function createSession(string $token, int $versionId): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO quiz_sessions (public_token, version_id, status, current_step) VALUES (?, ?, \'draft\', 1)'
        );
        $stmt->execute([$token, $versionId]);

        return (int) $this->pdo->lastInsertId();
    }

    /** @return array<string, mixed>|null */
    public function findSessionByToken(string $token): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM quiz_sessions WHERE public_token = ? LIMIT 1');
        $stmt->execute([$token]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    public function updateSessionStep(int $sessionId, int $step): void
    {
        $stmt = $this->pdo->prepare('UPDATE quiz_sessions SET current_step = ? WHERE id = ? AND status = \'draft\'');
        $stmt->execute([$step, $sessionId]);
    }

    public function upsertAnswer(int $sessionId, string $stepKey, string $questionKey, mixed $value): void
    {
        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $stmt = $this->pdo->prepare(
            'INSERT INTO quiz_answers (session_id, step_key, question_key, value_json)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE value_json = VALUES(value_json), updated_at = CURRENT_TIMESTAMP'
        );
        $stmt->execute([$sessionId, $stepKey, $questionKey, $json]);
    }

    /** @return list<array<string, mixed>> */
    public function answersForSession(int $sessionId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT step_key, question_key, value_json FROM quiz_answers WHERE session_id = ? ORDER BY id ASC'
        );
        $stmt->execute([$sessionId]);

        return $stmt->fetchAll() ?: [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function answersGrouped(int $sessionId): array
    {
        $grouped = [];
        foreach ($this->answersForSession($sessionId) as $row) {
            $val = json_decode((string) $row['value_json'], true);
            $grouped[(string) $row['step_key']][(string) $row['question_key']] = $val;
        }

        return $grouped;
    }

    public function findOrCreateCustomer(string $name, string $email, ?string $phone, bool $consentMarketing, bool $consentLgpd): int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM customers WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $id = $stmt->fetchColumn();
        if ($id !== false) {
            $upd = $this->pdo->prepare(
                'UPDATE customers SET name = ?, phone = ?, consent_marketing = ?,
                 consent_lgpd_at = IF(? = 1, COALESCE(consent_lgpd_at, NOW()), consent_lgpd_at) WHERE id = ?'
            );
            $upd->execute([$name, $phone, $consentMarketing ? 1 : 0, $consentLgpd ? 1 : 0, (int) $id]);

            return (int) $id;
        }
        $ins = $this->pdo->prepare(
            'INSERT INTO customers (name, email, phone, consent_marketing, consent_lgpd_at)
             VALUES (?, ?, ?, ?, ?)'
        );
        $ins->execute([
            $name,
            $email,
            $phone,
            $consentMarketing ? 1 : 0,
            $consentLgpd ? date('Y-m-d H:i:s') : null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /** @param array<string, mixed> $pet */
    public function createPet(int $customerId, array $pet): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO pets (customer_id, subscription_id, name, type, species, breed, age, age_months, sex, size, dietary_restrictions, health_notes)
             VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $species = (string) ($pet['species'] ?? 'dog');
        $ageYears = isset($pet['age_years']) ? (int) $pet['age_years'] : null;
        $stmt->execute([
            $customerId,
            (string) ($pet['name'] ?? 'Pet'),
            $species,
            $species,
            $pet['breed'] ?? null,
            $ageYears,
            $ageYears !== null ? $ageYears * 12 : null,
            $pet['sex'] ?? null,
            $pet['size'] ?? null,
            $pet['dietary_restrictions'] ?? null,
            $pet['health_notes'] ?? null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /** @param array<string, string> $attrs */
    public function replaceAttributes(int $sessionId, array $attrs): void
    {
        $this->pdo->prepare('DELETE FROM quiz_attributes WHERE session_id = ?')->execute([$sessionId]);
        $stmt = $this->pdo->prepare(
            'INSERT INTO quiz_attributes (session_id, attr_key, attr_value) VALUES (?, ?, ?)'
        );
        foreach ($attrs as $k => $v) {
            $stmt->execute([$sessionId, $k, $v]);
        }
    }

    public function completeSession(int $sessionId, ?int $customerId, ?int $petId, string $planSlug): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE quiz_sessions SET status = \'completed\', customer_id = ?, pet_id = ?,
             suggested_plan_slug = ?, completed_at = NOW() WHERE id = ?'
        );
        $stmt->execute([$customerId, $petId, $planSlug, $sessionId]);
    }

    /** @return list<array<string, mixed>> */
    public function listSessions(int $limit = 100): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT s.*, c.name AS customer_name, c.email AS customer_email, p.name AS pet_name
             FROM quiz_sessions s
             LEFT JOIN customers c ON c.id = s.customer_id
             LEFT JOIN pets p ON p.id = s.pet_id
             ORDER BY s.id DESC LIMIT ?'
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    /** @return array<string, mixed>|null */
    public function sessionDetail(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT s.*, c.name AS customer_name, c.email AS customer_email, c.phone AS customer_phone,
                    p.name AS pet_name, p.species AS pet_species, p.size AS pet_size
             FROM quiz_sessions s
             LEFT JOIN customers c ON c.id = s.customer_id
             LEFT JOIN pets p ON p.id = s.pet_id
             WHERE s.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /** @return list<array<string, mixed>> */
    public function attributesForSession(int $sessionId): array
    {
        $stmt = $this->pdo->prepare('SELECT attr_key, attr_value FROM quiz_attributes WHERE session_id = ?');
        $stmt->execute([$sessionId]);

        return $stmt->fetchAll() ?: [];
    }

    /** @return list<array<string, mixed>> */
    public function productsWithTags(): array
    {
        $products = $this->pdo->query(
            'SELECT id, name, sku, category, stock_qty, is_available, price FROM products ORDER BY name ASC'
        )->fetchAll() ?: [];
        $tagStmt = $this->pdo->query('SELECT product_id, tag_key, tag_value FROM product_tags');
        $tagsByProduct = [];
        foreach ($tagStmt->fetchAll() ?: [] as $tag) {
            $tagsByProduct[(int) $tag['product_id']][] = [
                'tag_key' => (string) $tag['tag_key'],
                'tag_value' => (string) $tag['tag_value'],
            ];
        }
        foreach ($products as &$p) {
            $p['id'] = (int) $p['id'];
            $p['stock_qty'] = (int) $p['stock_qty'];
            $p['is_available'] = (bool) $p['is_available'];
            $p['tags'] = $tagsByProduct[$p['id']] ?? [];
        }

        return $products;
    }

    /**
     * @param list<array{product_id:int,score:int,justification:string,is_selected:bool}> $items
     */
    public function createRecommendation(int $sessionId, ?int $petId, string $planSlug, array $items): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO box_recommendations (session_id, pet_id, status, suggested_plan_slug)
             VALUES (?, ?, \'pending_review\', ?)'
        );
        $stmt->execute([$sessionId, $petId, $planSlug]);
        $recId = (int) $this->pdo->lastInsertId();
        $itemStmt = $this->pdo->prepare(
            'INSERT INTO box_recommendation_items (recommendation_id, product_id, score, justification, is_selected)
             VALUES (?, ?, ?, ?, ?)'
        );
        foreach ($items as $item) {
            $itemStmt->execute([
                $recId,
                $item['product_id'],
                $item['score'],
                $item['justification'],
                !empty($item['is_selected']) ? 1 : 0,
            ]);
        }

        return $recId;
    }

    /** @return list<array<string, mixed>> */
    public function listRecommendations(?string $status = null): array
    {
        if ($status) {
            $stmt = $this->pdo->prepare(
                'SELECT r.*, c.name AS customer_name, p.name AS pet_name, s.public_token
                 FROM box_recommendations r
                 JOIN quiz_sessions s ON s.id = r.session_id
                 LEFT JOIN customers c ON c.id = s.customer_id
                 LEFT JOIN pets p ON p.id = r.pet_id
                 WHERE r.status = ?
                 ORDER BY r.id DESC'
            );
            $stmt->execute([$status]);
        } else {
            $stmt = $this->pdo->query(
                'SELECT r.*, c.name AS customer_name, p.name AS pet_name, s.public_token
                 FROM box_recommendations r
                 JOIN quiz_sessions s ON s.id = r.session_id
                 LEFT JOIN customers c ON c.id = s.customer_id
                 LEFT JOIN pets p ON p.id = r.pet_id
                 ORDER BY r.id DESC'
            );
        }

        return $stmt->fetchAll() ?: [];
    }

    /** @return array<string, mixed>|null */
    public function recommendationDetail(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*, c.name AS customer_name, c.email AS customer_email, p.name AS pet_name,
                    p.species, p.size, s.id AS session_id
             FROM box_recommendations r
             JOIN quiz_sessions s ON s.id = r.session_id
             LEFT JOIN customers c ON c.id = s.customer_id
             LEFT JOIN pets p ON p.id = r.pet_id
             WHERE r.id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }
        $items = $this->pdo->prepare(
            'SELECT i.*, pr.name AS product_name, pr.sku, pr.stock_qty, pr.category
             FROM box_recommendation_items i
             JOIN products pr ON pr.id = i.product_id
             WHERE i.recommendation_id = ?
             ORDER BY i.score DESC'
        );
        $items->execute([$id]);
        $row['items'] = $items->fetchAll() ?: [];

        return $row;
    }

    public function setRecommendationStatus(int $id, string $status): void
    {
        $stmt = $this->pdo->prepare('UPDATE box_recommendations SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    public function setItemSelected(int $itemId, bool $selected): void
    {
        $stmt = $this->pdo->prepare('UPDATE box_recommendation_items SET is_selected = ? WHERE id = ?');
        $stmt->execute([$selected ? 1 : 0, $itemId]);
    }

    /** @return list<array<string, mixed>> */
    public function listProductsAdmin(): array
    {
        return $this->productsWithTags();
    }

    /** @param array<string, mixed> $data */
    public function saveProduct(array $data, ?int $id = null): int
    {
        if ($id !== null) {
            $stmt = $this->pdo->prepare(
                'UPDATE products SET name=?, sku=?, description=?, category=?, price=?, stock_qty=?, is_available=? WHERE id=?'
            );
            $stmt->execute([
                $data['name'],
                $data['sku'] ?: null,
                $data['description'] ?? '',
                $data['category'] ?? '',
                $data['price'] !== '' ? $data['price'] : null,
                (int) ($data['stock_qty'] ?? 0),
                !empty($data['is_available']) ? 1 : 0,
                $id,
            ]);
            $productId = $id;
        } else {
            $stmt = $this->pdo->prepare(
                'INSERT INTO products (name, sku, description, category, price, stock_qty, is_available)
                 VALUES (?,?,?,?,?,?,?)'
            );
            $stmt->execute([
                $data['name'],
                $data['sku'] ?: null,
                $data['description'] ?? '',
                $data['category'] ?? '',
                $data['price'] !== '' && $data['price'] !== null ? $data['price'] : null,
                (int) ($data['stock_qty'] ?? 0),
                !empty($data['is_available']) ? 1 : 0,
            ]);
            $productId = (int) $this->pdo->lastInsertId();
        }

        if (isset($data['tags_text'])) {
            $this->pdo->prepare('DELETE FROM product_tags WHERE product_id = ?')->execute([$productId]);
            $ins = $this->pdo->prepare(
                'INSERT INTO product_tags (product_id, tag_key, tag_value) VALUES (?, ?, ?)'
            );
            foreach (preg_split('/\r\n|\r|\n/', (string) $data['tags_text']) ?: [] as $line) {
                $line = trim($line);
                if ($line === '' || !str_contains($line, '=')) {
                    continue;
                }
                [$k, $v] = array_map('trim', explode('=', $line, 2));
                if ($k !== '' && $v !== '') {
                    $ins->execute([$productId, $k, $v]);
                }
            }
        }

        return $productId;
    }

    /** @return array<string, mixed>|null */
    public function findProduct(int $id): ?array
    {
        foreach ($this->productsWithTags() as $p) {
            if ((int) $p['id'] === $id) {
                return $p;
            }
        }

        return null;
    }
}
