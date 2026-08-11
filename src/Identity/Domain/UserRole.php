<?php

declare(strict_types=1);

namespace ClubinhoPet\Identity\Domain;

enum UserRole: string
{
    case Admin = 'admin';
    case Operacao = 'operacao';
    case Montagem = 'montagem';
    case Atendimento = 'atendimento';
    case Estoque = 'estoque';
    case Financeiro = 'financeiro';

    /** @return list<string> */
    public function permissions(): array
    {
        return match ($this) {
            self::Admin => [
                'dashboard', 'customers', 'pets', 'quizzes', 'recommendations', 'recommendations.fulfill',
                'products', 'products.write', 'stock', 'boxes', 'boxes.ship', 'subscriptions',
                'contacts', 'leads', 'blog', 'content', 'payments', 'outbox', 'settings', 'audit', 'plans',
                'users',
            ],
            self::Operacao => [
                'dashboard', 'customers', 'pets', 'subscriptions', 'boxes', 'boxes.ship',
                'recommendations', 'outbox', 'audit', 'plans',
            ],
            self::Montagem => [
                'dashboard', 'recommendations', 'recommendations.fulfill', 'products', 'boxes',
            ],
            self::Atendimento => [
                'dashboard', 'customers', 'pets', 'quizzes', 'contacts', 'leads', 'outbox',
            ],
            self::Estoque => [
                'dashboard', 'products', 'products.write', 'stock',
            ],
            self::Financeiro => [
                'dashboard', 'payments', 'subscriptions', 'audit',
            ],
        };
    }

    public function can(string $permission): bool
    {
        return in_array($permission, $this->permissions(), true);
    }

    /** @return list<self> */
    public static function adminAccessRoles(): array
    {
        return self::cases();
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $r): string => $r->value, self::cases());
    }
}
