<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models;

use DateTimeImmutable;

readonly class AdminUser
{
    public function __construct(
        public AdminUserId $userId,
        public Email $email,
        public CreatedAt $createdAt,
        public Role $role,
        public Permissions $permissions,
    ) {
    }

    public function can(Permission $permission): bool
    {
        return $this->role->isPrivilege() || $this->permissions->has($permission);
    }

    /**
     * @param list<string> $permissions
     */
    public static function reconstruct(
        string $userId,
        string $email,
        DateTimeImmutable $createdAt,
        int $role,
        array $permissions,
    ): self {
        return new self(
            AdminUserId::reconstruct($userId),
            Email::reconstruct($email),
            CreatedAt::reconstruct($createdAt),
            Role::from($role),
            Permissions::reconstruct($permissions),
        );
    }

    /**
     * @return array{user_id: string, email: string, created_at: string, role: value-of<Role>, permissions: list<string>}
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId->value,
            'email' => $this->email->value,
            'created_at' => $this->createdAt->value->format('Y-m-d H:i:s'),
            'role' => $this->role->value,
            'permissions' => $this->permissions->toArray(),
        ];
    }
}
