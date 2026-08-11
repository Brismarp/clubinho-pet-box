<?php

declare(strict_types=1);

namespace ClubinhoPet\Identity\Application;

use ClubinhoPet\Identity\Domain\User;
use ClubinhoPet\Identity\Domain\UserRepository;
use ClubinhoPet\Identity\Domain\UserRole;
use ClubinhoPet\Platform\Domain\ActivityLogRepository;
use ClubinhoPet\Shared\Domain\DomainException;
use ClubinhoPet\Shared\Domain\Email;

final class ManageAdminUsersHandler
{
    public function __construct(
        private UserRepository $users,
        private ActivityLogRepository $logs,
    ) {
    }

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        $out = [];
        foreach ($this->users->listAll() as $user) {
            $out[] = $this->toArray($user);
        }

        return $out;
    }

    /** @return array<string, mixed> */
    public function get(int $id): array
    {
        $user = $this->users->findById($id);
        if ($user === null) {
            throw new DomainException('User not found.');
        }

        return $this->toArray($user);
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function save(array $input, ?int $id, ?int $actorId): array
    {
        $name = trim((string) ($input['name'] ?? ''));
        $emailRaw = trim((string) ($input['email'] ?? ''));
        $roleRaw = (string) ($input['role'] ?? 'atendimento');
        $active = !isset($input['is_active']) || (string) $input['is_active'] === '1' || $input['is_active'] === true || $input['is_active'] === 1;
        $password = (string) ($input['password'] ?? '');

        $role = UserRole::tryFrom($roleRaw);
        if ($role === null) {
            throw new DomainException('Invalid role.');
        }

        $email = Email::fromString($emailRaw);
        $existingEmail = $this->users->findByEmail($email);

        if ($id === null) {
            if ($existingEmail !== null) {
                throw new DomainException('E-mail already in use.');
            }
            if (strlen($password) < 8) {
                throw new DomainException('Password must be at least 8 characters.');
            }
            $user = User::create($name, $email, password_hash($password, PASSWORD_DEFAULT), $role, $active);
            $this->users->save($user);
            $this->logs->log($actorId, 'user.create', 'user', $user->id(), null, [
                'email' => $email->value(),
                'role' => $role->value,
            ]);

            return $this->toArray($user);
        }

        $user = $this->users->findById($id);
        if ($user === null) {
            throw new DomainException('User not found.');
        }
        if ($existingEmail !== null && $existingEmail->id() !== $id) {
            throw new DomainException('E-mail already in use.');
        }

        $old = $this->toArray($user);
        $wasAdminActive = $user->role() === UserRole::Admin && $user->isActive();

        $user->rename($name);
        $user->changeEmail($email);
        $user->changeRole($role);

        if ($password !== '') {
            if (strlen($password) < 8) {
                throw new DomainException('Password must be at least 8 characters.');
            }
            $user->setPasswordHash(password_hash($password, PASSWORD_DEFAULT));
        }

        $willBeAdminActive = $role === UserRole::Admin && $active;
        if ($wasAdminActive && !$willBeAdminActive && $this->users->countActiveAdmins() <= 1) {
            throw new DomainException('Cannot remove or deactivate the last active admin.');
        }

        $user->setActive($active);
        $this->users->save($user);
        $this->logs->log($actorId, 'user.update', 'user', $user->id(), $old, $this->toArray($user));

        return $this->toArray($user);
    }

    public function setActive(int $id, bool $active, ?int $actorId): void
    {
        $user = $this->users->findById($id);
        if ($user === null) {
            throw new DomainException('User not found.');
        }
        if ($user->role() === UserRole::Admin && $user->isActive() && !$active
            && $this->users->countActiveAdmins() <= 1) {
            throw new DomainException('Cannot deactivate the last active admin.');
        }
        if ($actorId !== null && $actorId === $id && !$active) {
            throw new DomainException('You cannot deactivate your own account.');
        }
        $old = $this->toArray($user);
        $user->setActive($active);
        $this->users->save($user);
        $this->logs->log($actorId, 'user.set_active', 'user', $id, $old, $this->toArray($user));
    }

    /** @return array<string, mixed> */
    private function toArray(User $user): array
    {
        return [
            'id' => $user->id(),
            'name' => $user->name(),
            'email' => $user->email()->value(),
            'role' => $user->role()->value,
            'is_active' => $user->isActive(),
        ];
    }
}
