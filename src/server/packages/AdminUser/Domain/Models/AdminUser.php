<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models;

use DateTimeImmutable;

readonly class AdminUser
{
    public function __construct(
        public AdminUserId $userId,
        public AdminUserName $adminUserName,
        public Email $email,
        public CreatedAt $createdAt,
        public Role $role,
        public Permissions $permissions,
    ) {
    }

    /**
     * @param list<string> $permissions
     */
    public static function reconstruct(
        string $userId,
        string $adminUserName,
        string $email,
        DateTimeImmutable $createdAt,
        int $role,
        array $permissions,
    ): self {
        return new self(
            AdminUserId::reconstruct($userId),
            AdminUserName::reconstruct($adminUserName),
            Email::reconstruct($email),
            CreatedAt::reconstruct($createdAt),
            Role::from($role),
            Permissions::reconstruct($permissions),
        );
    }

    /**
     * @return array{user_id: string, admin_user_name: string, email: string, created_at: string, role: value-of<Role>, permissions: list<string>}
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId->value,
            'admin_user_name' => $this->adminUserName->value,
            'email' => $this->email->value,
            'created_at' => $this->createdAt->value->format('Y-m-d H:i:s'),
            'role' => $this->role->value,
            'permissions' => $this->permissions->toArray(),
        ];
    }

    public function equals(self $other): bool
    {
        return $this->userId->equals($other->userId);
    }

    public function can(Permission $permission): bool
    {
        return $this->role->isPrivilege() || $this->permissions->has($permission);
    }
}
