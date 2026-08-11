<?php

declare(strict_types=1);

namespace ClubinhoPet\Crm\Application;

use PDO;

final class CustomerCrmHandlers
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return list<array<string, mixed>> */
    public function listCustomers(?string $q = null): array
    {
        if ($q !== null && trim($q) !== '') {
            $like = '%' . trim($q) . '%';
            $stmt = $this->pdo->prepare(
                'SELECT c.*,
                        (SELECT COUNT(*) FROM pets p WHERE p.customer_id = c.id) AS pets_count,
                        (SELECT COUNT(*) FROM quiz_sessions qs WHERE qs.customer_id = c.id AND qs.status = \'completed\') AS quizzes_count
                 FROM customers c
                 WHERE c.name LIKE ? OR c.email LIKE ? OR IFNULL(c.phone, \'\') LIKE ?
                 ORDER BY c.id DESC
                 LIMIT 200'
            );
            $stmt->execute([$like, $like, $like]);
        } else {
            $stmt = $this->pdo->query(
                'SELECT c.*,
                        (SELECT COUNT(*) FROM pets p WHERE p.customer_id = c.id) AS pets_count,
                        (SELECT COUNT(*) FROM quiz_sessions qs WHERE qs.customer_id = c.id AND qs.status = \'completed\') AS quizzes_count
                 FROM customers c
                 ORDER BY c.id DESC
                 LIMIT 200'
            );
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** @return array<string, mixed> */
    public function getCustomer(int $id): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM customers WHERE id = ?');
        $stmt->execute([$id]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($customer === false) {
            throw new \ClubinhoPet\Shared\Domain\DomainException('Customer not found.');
        }

        $pets = $this->pdo->prepare('SELECT * FROM pets WHERE customer_id = ? ORDER BY id DESC');
        $pets->execute([$id]);

        $quizzes = $this->pdo->prepare(
            'SELECT qs.*, p.name AS pet_name FROM quiz_sessions qs
             LEFT JOIN pets p ON p.id = qs.pet_id
             WHERE qs.customer_id = ?
             ORDER BY qs.id DESC LIMIT 50'
        );
        $quizzes->execute([$id]);

        $subs = $this->pdo->prepare(
            'SELECT s.*, pl.name AS plan_name FROM subscriptions s
             LEFT JOIN plans pl ON pl.id = s.plan_id
             WHERE s.email = ?
             ORDER BY s.id DESC LIMIT 50'
        );
        $subs->execute([(string) $customer['email']]);

        $boxes = $this->pdo->prepare(
            'SELECT b.* FROM boxes b
             INNER JOIN subscriptions s ON s.id = b.subscription_id
             WHERE s.email = ?
             ORDER BY b.id DESC LIMIT 50'
        );
        $boxes->execute([(string) $customer['email']]);

        $payments = $this->pdo->prepare(
            'SELECT * FROM payments WHERE customer_email = ? ORDER BY id DESC LIMIT 50'
        );
        $payments->execute([(string) $customer['email']]);

        return [
            'customer' => $customer,
            'pets' => $pets->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'quizzes' => $quizzes->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'subscriptions' => $subs->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'boxes' => $boxes->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'payments' => $payments->fetchAll(PDO::FETCH_ASSOC) ?: [],
        ];
    }

    /** @return list<array<string, mixed>> */
    public function listPets(?string $q = null): array
    {
        if ($q !== null && trim($q) !== '') {
            $like = '%' . trim($q) . '%';
            $stmt = $this->pdo->prepare(
                'SELECT p.*, c.name AS customer_name, c.email AS customer_email
                 FROM pets p
                 LEFT JOIN customers c ON c.id = p.customer_id
                 WHERE p.name LIKE ? OR c.name LIKE ? OR c.email LIKE ?
                 ORDER BY p.id DESC LIMIT 200'
            );
            $stmt->execute([$like, $like, $like]);
        } else {
            $stmt = $this->pdo->query(
                'SELECT p.*, c.name AS customer_name, c.email AS customer_email
                 FROM pets p
                 LEFT JOIN customers c ON c.id = p.customer_id
                 ORDER BY p.id DESC LIMIT 200'
            );
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** @return array<string, mixed> */
    public function getPet(int $id): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.*, c.name AS customer_name, c.email AS customer_email, c.id AS customer_id_full
             FROM pets p
             LEFT JOIN customers c ON c.id = p.customer_id
             WHERE p.id = ?'
        );
        $stmt->execute([$id]);
        $pet = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($pet === false) {
            throw new \ClubinhoPet\Shared\Domain\DomainException('Pet not found.');
        }

        $quizzes = $this->pdo->prepare(
            'SELECT * FROM quiz_sessions WHERE pet_id = ? ORDER BY id DESC LIMIT 30'
        );
        $quizzes->execute([$id]);

        $attrs = [];
        $latest = $this->pdo->prepare(
            'SELECT id FROM quiz_sessions WHERE pet_id = ? AND status = \'completed\' ORDER BY id DESC LIMIT 1'
        );
        $latest->execute([$id]);
        $sid = $latest->fetchColumn();
        if ($sid) {
            $a = $this->pdo->prepare('SELECT attr_key, attr_value FROM quiz_attributes WHERE session_id = ?');
            $a->execute([(int) $sid]);
            foreach ($a->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
                $attrs[(string) $row['attr_key']] = (string) $row['attr_value'];
            }
        }

        $recs = $this->pdo->prepare(
            'SELECT * FROM box_recommendations WHERE pet_id = ? ORDER BY id DESC LIMIT 20'
        );
        $recs->execute([$id]);

        return [
            'pet' => $pet,
            'quizzes' => $quizzes->fetchAll(PDO::FETCH_ASSOC) ?: [],
            'attributes' => $attrs,
            'recommendations' => $recs->fetchAll(PDO::FETCH_ASSOC) ?: [],
        ];
    }
}
