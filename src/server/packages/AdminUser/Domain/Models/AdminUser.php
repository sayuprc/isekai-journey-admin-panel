<?php

declare(strict_types=1);

namespace AdminUser\Domain\Models;

readonly class AdminUser
{
    public function __construct(
        public AdminUserId $userId,
        public Email $email,
        public HashedPassword $hashedPassword,
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
        string $hashedPassword,
        int $role,
        array $permissions,
    ): self {
        return new self(
            AdminUserId::reconstruct($userId),
            Email::reconstruct($email),
            HashedPassword::reconstruct($hashedPassword),
            Role::from($role),
            Permissions::reconstruct($permissions),
        );
    }

    /**
     * @return array{user_id: string, email: string, hashed_password: string, role: value-of<Role>, permissions: list<string>}
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId->value,
            'email' => $this->email->value,
            'hashed_password' => $this->hashedPassword->value,
            'role' => $this->role->value,
            'permissions' => $this->permissions->toArray(),
        ];
    }
}
